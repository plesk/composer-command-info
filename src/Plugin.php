<?php
// Copyright 1999-2019. Plesk International GmbH. All rights reserved.

namespace Plesk\ComposerCommandInfo;

use Composer\Composer;
use Composer\DependencyResolver\Operation\InstallOperation;
use Composer\DependencyResolver\Operation\OperationInterface;
use Composer\DependencyResolver\Operation\UninstallOperation;
use Composer\DependencyResolver\Operation\UpdateOperation;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\Installer\InstallerEvent;
use Composer\Installer\InstallerEvents;
use Composer\Installer\PackageEvent;
use Composer\IO\IOInterface;
use Composer\Plugin\PluginInterface;
use Composer\Plugin\CommandEvent;
use Composer\Plugin\PluginEvents;
use Plesk\ComposerCommandInfo\Command\CommandInfoInterface;
use Plesk\ComposerCommandInfo\Command\CommandInfoFactory;
use Plesk\ComposerCommandInfo\Operation\BaseOperation;
use Plesk\ComposerCommandInfo\Operation\DependenciesSolvingOperation;

/**
 * Class Plugin
 */
class Plugin implements PluginInterface, EventSubscriberInterface
{
    /**
     * @var CommandInfoFactory
     */
    private $commandInfoFactory;

    /**
     * @var CommandInfoInterface|null
     */
    private $commandInfo;

    /**
     * {@inheritDoc}
     */
    public function activate(Composer $composer, IOInterface $io)
    {
        $this->commandInfoFactory = new CommandInfoFactory($composer);
        $this->commandInfo = $this->commandInfoFactory->getNullCommandInfo();
    }

    public function deactivate(Composer $composer, IOInterface $io)
    {
    }

    public function uninstall(Composer $composer, IOInterface $io)
    {
    }

    /**
     * {@inheritDoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            PluginEvents::COMMAND => [
                ['onCommand']
            ],
            InstallerEvents::PRE_OPERATIONS_EXEC => [
                ['onPostDependenciesSolving'],
            ],
            'pre-package-install' => [
                ['onPrePackageInstall'],
            ],
            'post-package-install' => [
                ['onPostPackageInstall'],
            ],
            'pre-package-update' => [
                ['onPrePackageUpdate'],
            ],
            'post-package-update' => [
                ['onPostPackageUpdate'],
            ],
            'pre-package-uninstall' => [
                ['onPrePackageUninstall'],
            ],
            'post-package-uninstall' => [
                ['onPostPackageUninstall'],
            ],
        ];
    }

    /**
     * @param CommandEvent $event
     */
    public function onCommand(CommandEvent $event)
    {
        $this->commandInfo = $this->commandInfoFactory->getCommandInfo($event);
        $this->initDependencySolvingTask();
    }

    private function initDependencySolvingTask()
    {
        $operation = new DependenciesSolvingOperation();
        $this->commandInfo
            ->setOperations([$operation])
            ->setCurrentOperation($operation)
            ->save();
    }

    /**
     * @param InstallerEvent $event
     */
    public function onPostDependenciesSolving(InstallerEvent $event)
    {
        /**
         * @var OperationInterface[] $operations
         */
        $operations = $event->getTransaction()->getOperations();
        $operations = array_filter($operations, function ($operation) {
            return $operation instanceof InstallOperation
                || $operation instanceof UpdateOperation
                || $operation instanceof UninstallOperation;
        });
        $operations = array_map(function ($operation) {
            return new BaseOperation($operation);
        }, $operations);

        $this->commandInfo
            ->onPostDependenciesSolving()
            ->setOperations($operations)
            ->setOperationsInitialized()
            ->completeOperation(new DependenciesSolvingOperation())
            ->save();
    }

    /**
     * @param PackageEvent $event
     */
    public function onPrePackageInstall(PackageEvent $event)
    {
        /**
         * @var InstallOperation $operation
         */
        $operation = $event->getOperation();
        $this->commandInfo
            ->setCurrentOperation(new BaseOperation($operation))
            ->save();
    }

    /**
     * @param PackageEvent $event
     */
    public function onPostPackageInstall(PackageEvent $event)
    {
        /**
         * @var InstallOperation $operation
         */
        $operation = $event->getOperation();
        $this->commandInfo
            ->completeOperation(new BaseOperation($operation))
            ->save();
    }

    /**
     * @param PackageEvent $event
     */
    public function onPrePackageUpdate(PackageEvent $event)
    {
        /**
         * @var UpdateOperation $operation
         */
        $operation = $event->getOperation();
        $this->commandInfo
            ->setCurrentOperation(new BaseOperation($operation))
            ->save();
    }

    /**
     * @param PackageEvent $event
     */
    public function onPostPackageUpdate(PackageEvent $event)
    {
        /**
         * @var UpdateOperation $operation
         */
        $operation = $event->getOperation();
        $this->commandInfo
            ->completeOperation(new BaseOperation($operation))
            ->save();
    }

    /**
     * @param PackageEvent $event
     */
    public function onPrePackageUninstall(PackageEvent $event)
    {
        /**
         * @var UninstallOperation $operation
         */
        $operation = $event->getOperation();
        $this->commandInfo
            ->setCurrentOperation(new BaseOperation($operation))
            ->save();
    }

    /**
     * @param PackageEvent $event
     */
    public function onPostPackageUninstall(PackageEvent $event)
    {
        /**
         * @var UninstallOperation $operation
         */
        $operation = $event->getOperation();
        $this->commandInfo
            ->completeOperation(new BaseOperation($operation))
            ->save();
    }
}

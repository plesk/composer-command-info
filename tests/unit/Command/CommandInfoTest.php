<?php

namespace Test\Command;

use Codeception\Test\Unit;
use Composer\Composer;
use Composer\DependencyResolver\Operation\InstallOperation;
use Composer\Package\Package;
use Plesk\ComposerCommandInfo\Command\CommandInfo;
use Plesk\ComposerCommandInfo\Operation\BaseOperation;
use Plesk\ComposerCommandInfo\Operation\DependenciesSolvingOperation;

/**
 * Class CommandInfoTest
 */
class CommandInfoTest extends Unit
{
    /**
     * @var Composer
     */
    private $composer;

    /**
     * @var array
     */
    private $operations;

    /**
     * {@inheritDoc}
     * 
     * @throws \Exception
     */
    public function _before()
    {
        $this->composer = \Codeception\Stub::make(Composer::class);
        $this->operations = $this->getOperations();
    }

    /**
     * @throws \Exception
     */
    public function testGetInfoOnUpdate()
    {
        $command = 'update';
        /**
         * @var CommandInfo $commandInfo
         */
        $commandInfo = $this->getCommandInfo($command, false);

        $commandInfo->setCurrentOperation($this->operations[0]);
        $this->checkInfo($command, $commandInfo->getInfo(), 0, 0);

        $commandInfo
            ->onPostDependenciesSolving()
            ->setOperationsInitialized()
            ->completeOperation($this->operations[0]);
        $this->checkInfo($command, $commandInfo->getInfo(), 0, 0);

        $commandInfo->setCurrentOperation($this->operations[1]);
        $this->checkInfo($command, $commandInfo->getInfo(), 0, 1);

        $commandInfo->completeOperation($this->operations[1]);
        $this->checkInfo($command, $commandInfo->getInfo(), 0, 1);

        $commandInfo->onPostDependenciesSolving();
        $this->checkGetInfoAfterResolving($command, $commandInfo);
    }

    /**
     * @throws \Exception
     */
    public function testGetInfoOnInstall()
    {
        $command = 'install';
        /**
         * @var CommandInfo $commandInfo
         */
        $commandInfo = $this->getCommandInfo($command, true);

        $commandInfo->setCurrentOperation($this->operations[0]);
        $this->checkInfo($command, $commandInfo->getInfo(), 0, 0);

        $commandInfo
            ->onPostDependenciesSolving()
            ->setOperationsInitialized()
            ->completeOperation($this->operations[0]);
        $this->checkInfo($command, $commandInfo->getInfo(), 20, 0);

        $commandInfo->setCurrentOperation($this->operations[1]);
        $this->checkInfo($command, $commandInfo->getInfo(), 20, 1);

        $commandInfo->completeOperation($this->operations[1]);
        $this->checkInfo($command, $commandInfo->getInfo(), 40, 1);

        $this->checkGetInfoAfterResolving($command, $commandInfo);
    }

    /**
     * @param string $command
     * @param CommandInfo $commandInfo
     */
    private function checkGetInfoAfterResolving($command, $commandInfo)
    {
        $commandInfo->onPostDependenciesSolving();
        $commandInfo->setCurrentOperation($this->operations[2]);
        $this->checkInfo($command, $commandInfo->getInfo(), 40, 2);

        $commandInfo->completeOperation($this->operations[2]);
        $this->checkInfo($command, $commandInfo->getInfo(), 60, 2);

        $commandInfo->setCurrentOperation($this->operations[3]);
        $this->checkInfo($command, $commandInfo->getInfo(), 60, 3);

        $commandInfo->completeOperation($this->operations[3]);
        $this->checkInfo($command, $commandInfo->getInfo(), 80, 3);

        $commandInfo->setCurrentOperation($this->operations[4]);
        $this->checkInfo($command, $commandInfo->getInfo(), 80, 4);

        $commandInfo->completeOperation($this->operations[4]);
        $this->checkInfo($command, $commandInfo->getInfo(), 100, 4, true);

        $this->assertEquals(true, $commandInfo->isCompleted());
    }

    /**
     * @param string $command
     * @param array $info
     * @param int $progress
     * @param int $index
     * @param bool $isCurrentOperationNull
     */
    private function checkInfo($command, $info, $progress, $index, $isCurrentOperationNull = false)
    {
        $this->assertEquals([
            'command' => $command,
            'currentOperation' => $isCurrentOperationNull ? null : (string)$this->operations[$index],
            'progress' => $progress,
            'history' => array_slice($this->operations, 0, $index + 1),
        ], $info);
    }

    /**
     * @return array
     */
    private function getOperations()
    {
        $packages = [
            [
                'name' => 'package1',
                'version' => '1.1.1',
                'prettyVersion' => '1.1.1',
            ],
            [
                'name' => 'package2',
                'version' => '2.1.1',
                'prettyVersion' => '2.1.1',
            ],
            [
                'name' => 'package3',
                'version' => '3.1.1',
                'prettyVersion' => '3.1.1',
            ],
            [
                'name' => 'package4',
                'version' => '3.2.1',
                'prettyVersion' => '3.2.1',
            ],
        ];

        $operations = array_map(function ($package) {
            $operation = new InstallOperation(new Package($package['name'], $package['version'], $package['prettyVersion']));
            return new BaseOperation($operation);
        }, $packages);

        return array_merge([new DependenciesSolvingOperation()], $operations);
    }

    /**
     * @param string $command
     * @param bool $isDevMode
     * @return CommandInfo
     * @throws \Exception
     */
    private function getCommandInfo($command, $isDevMode)
    {
        /**
         * @var CommandInfo $commandInfo
         */
        $commandInfo = \Codeception\Stub::construct(CommandInfo::class, [$this->composer, $command]);
        $commandInfo->setDevMode($isDevMode)->setOperations($this->operations);

        return $commandInfo;
    }
}

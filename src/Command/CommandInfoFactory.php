<?php
// Copyright 1999-2019. Plesk International GmbH. All rights reserved.

namespace Plesk\ComposerCommandInfo\Command;

use Composer\Composer;
use Composer\Plugin\CommandEvent;

/**
 * Class CommandInfoFactory
 */
class CommandInfoFactory
{
    /**
     * @var array
     */
    private $supportedCommands = [
        CommandInfoInterface::INSTALL_COMMAND,
        CommandInfoInterface::UPDATE_COMMAND,
    ];

    /**
     * @var Composer
     */
    private $composer;

    /**
     * CommandInfoFactory constructor.
     *
     * @param Composer $composer
     */
    public function __construct(Composer $composer)
    {
        $this->composer = $composer;
    }

    /**
     * @param CommandEvent $event
     * @return CommandInfo|NullCommandInfo
     */
    public function getCommandInfo(CommandEvent $event)
    {
        $operation = $event->getCommandName();

        if (!in_array($operation, $this->supportedCommands)) {
            return $this->getNullCommandInfo();
        }

        $input = $event->getInput();
        $commandInfo = new CommandInfo($this->composer, $operation);
        $commandInfo->setDevMode(!$input->getOption('no-dev'));

        return $commandInfo;
    }

    /**
     * @return NullCommandInfo
     */
    public function getNullCommandInfo()
    {
        return new NullCommandInfo();
    }
}

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
    const SUPPORTED_COMMANDS = [
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

        if (!in_array($operation, self::SUPPORTED_COMMANDS)) {
            return new NullCommandInfo();
        }

        $input = $event->getInput();
        $commandInfo = new CommandInfo($this->composer, $operation);
        $commandInfo->setDevMode(!$input->getOption('no-dev'));

        return $commandInfo;
    }
}

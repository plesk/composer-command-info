<?php

namespace Test\Command;

use Codeception\Test\Unit;
use Composer\Composer;
use Composer\Plugin\CommandEvent;
use Plesk\ComposerCommandInfo\Command\CommandInfo;
use Plesk\ComposerCommandInfo\Command\CommandInfoFactory;
use Plesk\ComposerCommandInfo\Command\CommandInfoInterface;
use Plesk\ComposerCommandInfo\Command\NullCommandInfo;
use Symfony\Component\Console\Input\InputInterface;

/**
 * Class CommandInfoFactoryTest
 */
class CommandInfoFactoryTest extends Unit
{
    /**
     * @var CommandInfoFactory
     */
    private $commandInfoFactory;

    /**
     * {@inheritDoc}
     *
     * @throws \Exception
     */
    public function _before()
    {
        $composer = \Codeception\Stub::make(Composer::class);
        $this->commandInfoFactory = new CommandInfoFactory($composer);
    }

    /**
     * @throws \Exception
     */
    public function testGetCommandInfoNotSupportedCommand()
    {
        $commandEvent = $this->getCommandEventMock('show');
        $commandInfo = $this->commandInfoFactory->getCommandInfo($commandEvent);

        $this->assertInstanceOf(NullCommandInfo::class, $commandInfo);
    }

    /**
     * @throws \Exception
     */
    public function testGetCommandInfoSupportedCommand()
    {
        $commandEvent = $this->getCommandEventMock(CommandInfoInterface::UPDATE_COMMAND);
        $commandInfo = $this->commandInfoFactory->getCommandInfo($commandEvent);

        $this->assertInstanceOf(CommandInfo::class, $commandInfo);
    }

    /**
     * @param string $commandName
     * @param bool $isDev
     * @return object
     * @throws \Exception
     */
    private function getCommandEventMock($commandName, $isDev = false)
    {
        $input = \Codeception\Stub::makeEmpty(InputInterface::class, [
            'getOption' => $isDev,
        ]);

        return \Codeception\Stub::make(CommandEvent::class, [
            'getCommandName' => $commandName,
            'getInput' => $input,
        ]);
    }
}

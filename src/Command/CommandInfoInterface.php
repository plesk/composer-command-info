<?php
// Copyright 1999-2019. Plesk International GmbH. All rights reserved.

namespace Plesk\ComposerCommandInfo\Command;

use Plesk\ComposerCommandInfo\Operation\OperationInterface;

/**
 * Interface CommandInfoInterface
 */
interface CommandInfoInterface
{
    const INSTALL_COMMAND = 'install';
    const UPDATE_COMMAND = 'update';

    /**
     * @param OperationInterface[] $operations
     * @return CommandInfoInterface
     */
    public function setOperations(array $operations);

    /**
     * @return CommandInfoInterface
     */
    public function setOperationsInitialized();

    /**
     * @param bool $value
     * @return CommandInfoInterface
     */
    public function setDevMode($value);

    /**
     * @param OperationInterface $operation
     * @return CommandInfoInterface
     */
    public function setCurrentOperation($operation);

    /**
     * @param OperationInterface $operation
     * @return CommandInfoInterface
     */
    public function completeOperation($operation);

    /**
     * @return mixed
     */
    public function save();

    /**
     * @return bool
     */
    public function isCompleted();

    /**
     * @return array
     */
    public function getInfo();

    /**
     * @return CommandInfoInterface
     */
    public function onPostDependenciesSolving();
}

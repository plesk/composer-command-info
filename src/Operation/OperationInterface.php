<?php
// Copyright 1999-2019. Plesk International GmbH. All rights reserved.

namespace Plesk\ComposerCommandInfo\Operation;

/**
 * Interface OperationInterface
 */
interface OperationInterface
{
    const INSTALL_OPERATION = 'install';
    const UNINSTALL_OPERATION = 'uninstall';
    const UPDATE_OPERATION = 'update';
    const DEPENDENCIES_SOLVING_OPERATION = 'dependencies-solving';

    /**
     * @return string
     */
    public function getId();

    /**
     * @param bool $value
     * @return OperationInterface
     */
    public function setCompleted($value = true);

    /**
     * @return bool
     */
    public function isCompleted();

    /**
     * @return string
     */
    public function getJobType();

    /**
     * @return string
     */
    public function __toString();
}

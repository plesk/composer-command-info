<?php
// Copyright 1999-2019. Plesk International GmbH. All rights reserved.

namespace Plesk\ComposerCommandInfo\Command;

/**
 * Class NullOperationInfo
 */
class NullCommandInfo implements CommandInfoInterface
{
    /**
     * {@inheritDoc}
     */
    public function setOperations(array $operations)
    {
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function setOperationsInitialized()
    {
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function setDevMode($value)
    {
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function setCurrentOperation($operation)
    {
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function completeOperation($operation)
    {
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function save()
    {
    }

    /**
     * {@inheritDoc}
     */
    public function isCompleted()
    {
        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function getInfo()
    {
        return [];
    }

    /**
     * {@inheritDoc}
     */
    public function onPostDependenciesSolving()
    {
        return $this;
    }
}

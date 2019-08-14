<?php
// Copyright 1999-2019. Plesk International GmbH. All rights reserved.

namespace Plesk\ComposerCommandInfo\Operation;

/**
 * Class DependencyResolveOperation
 */
class DependenciesSolvingOperation implements OperationInterface
{
    use OperationStatusTrait;

    /**
     * {@inheritDoc}
     */
    public function getId()
    {
        return base64_encode($this->getJobType());
    }

    /**
     * {@inheritDoc}
     */
    public function getJobType()
    {
        return self::DEPENDENCIES_SOLVING_OPERATION;
    }

    /**
     * {@inheritDoc}
     */
    public function __toString()
    {
        $operationString = 'Dependencies solving';

        if ($this->isCompleted()) {
            return "{$operationString} ( completed )";
        }

        return $operationString;
    }
}

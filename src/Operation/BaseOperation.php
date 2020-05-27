<?php
// Copyright 1999-2019. Plesk International GmbH. All rights reserved.

namespace Plesk\ComposerCommandInfo\Operation;

use Composer\DependencyResolver\Operation\InstallOperation;
use Composer\DependencyResolver\Operation\UninstallOperation;
use Composer\DependencyResolver\Operation\UpdateOperation;

/**
 * Class BaseOperation
 */
class BaseOperation implements OperationInterface
{
    use OperationStatusTrait;

    /**
     * @var InstallOperation|UpdateOperation|UninstallOperation
     */
    private $operation;

    /**
     * BaseOperation constructor.
     *
     * @param InstallOperation|UpdateOperation|UninstallOperation $operation
     */
    public function __construct($operation)
    {
        $this->operation = $operation;
    }

    /**
     * @return string
     */
    public function getId()
    {
        if ($this->operation instanceof UpdateOperation) {
            $package = $this->operation->getTargetPackage();
        } else {
            $package = $this->operation->getPackage();
        }

        return base64_encode($package->getUniqueName());
    }

    /**
     * @return string
     */
    public function getJobType()
    {
        return $this->operation->getOperationType();
    }

    /**
     * @return string
     */
    public function __toString()
    {
        $operationString = (string)$this->operation;

        if (!$this->isCompleted()) {
            return $operationString;
        }

        return "{$operationString} ( completed )";
    }
}

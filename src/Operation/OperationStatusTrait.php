<?php
// Copyright 1999-2019. Plesk International GmbH. All rights reserved.

namespace Plesk\ComposerCommandInfo\Operation;

/**
 * Trait OperationStatusTrait
 */
trait OperationStatusTrait
{
    /**
     * @var bool
     */
    private $completed = false;

    /**
     * @return bool
     */
    public function isCompleted()
    {
        return $this->completed;
    }

    /**
     * @param bool $value
     * @return $this
     */
    public function setCompleted($value = true)
    {
        $this->completed = $value;

        return $this;
    }
}

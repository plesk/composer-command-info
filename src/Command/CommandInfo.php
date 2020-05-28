<?php
// Copyright 1999-2019. Plesk International GmbH. All rights reserved.

namespace Plesk\ComposerCommandInfo\Command;

use Composer\Composer;
use Plesk\ComposerCommandInfo\Operation\OperationInterface;

/**
 * Class CommandInfo
 */
class CommandInfo implements CommandInfoInterface
{
    /**
     * @var Composer
     */
    private $composer;

    /**
     * @var string
     */
    private $command;

    /**
     * @var bool
     */
    private $isDevMode = false;

    /**
     * @var bool
     */
    private $isMainDependenciesSolved = false;

    /**
     * @var bool
     */
    private $isDevDependenciesSolved = false;

    /**
     * @var OperationInterface|null
     */
    private $currentOperation;

    /**
     * @var OperationInterface[]
     */
    private $operations = [];

    /**
     * @var bool
     */
    private $operationsInitialized = false;

    /**
     * @var OperationInterface[]
     */
    private $completedOperations = [];

    /**
     * @var bool
     */
    private $startProgress = false;

    /**
     * CommandInfo constructor.
     *
     * @param Composer $composer
     * @param string $command
     */
    public function __construct(Composer $composer, $command)
    {
        $this->composer = $composer;
        $this->command = $command;
    }

    /**
     * {@inheritDoc}
     */
    public function setOperations(array $operations)
    {
        if (
            $this->operationsInitialized
            && !$this->shouldFilterDevPackagesOperations()
        ) {
            return $this;
        }

        if ($this->shouldFilterDevPackagesOperations()) {
            $operations = $this->filterDevPackageOperations($operations);
        }

        foreach ($operations as $operation) {
            /**
             * @var OperationInterface $operation
             */
            if (
                $this->getOperationById($operation->getId())
                && !$operation->isCompleted()
            ) {
                $this->removeCompletedOperation($operation);
            }

            $this->setOperation($operation);
        }

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function setOperationsInitialized()
    {
        $this->operationsInitialized = true;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function setDevMode($value)
    {
        $this->isDevMode = $value;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function setCurrentOperation($operation)
    {
        $this->currentOperation = $operation;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function completeOperation($operation)
    {
        /**
         * @var OperationInterface $operation
         */
        $existedOperation = $this->getOperationById($operation->getId());

        if (!$existedOperation || !$existedOperation->isCompleted()) {
            $operation->setCompleted();
            $this
                ->setOperation($operation)
                ->setCompletedOperation($operation);
        }

        if (
            $this->currentOperation
            && $this->currentOperation->getId() === $operation->getId()
            && $this->currentOperation->getJobType() === $operation->getJobType()
        ) {
            $this->setCurrentOperation($operation);
        }

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getInfo()
    {
        return [
            'command' => $this->command,
            'currentOperation' => $this->isCompleted() ? null : (string)$this->currentOperation,
            'progress' => $this->getProgress(),
            'history' => $this->getHistory(),
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function save()
    {
        if (!$info = json_encode($this->getInfo())) {
            return;
        }

        try {
            $fileObject = new \SplFileObject($this->getOutputFile(), 'c');
            $fileObject->fwrite($info);
            $fileObject->fflush();
            $fileObject->ftruncate($fileObject->ftell());
        } catch (\Exception $e) {
        }
    }

    /**
     * {@inheritDoc}
     */
    public function isCompleted()
    {
        return $this->getProgress() == 100;
    }

    /**
     * {@inheritDoc}
     */
    public function onPostDependenciesSolving()
    {
        if ($this->isMainDependenciesSolved) {
            $this->isDevDependenciesSolved = true;
        }

        $this->isMainDependenciesSolved = true;
        $this->startProgress = $this->isUpdateCommand()
            ? $this->isDevDependenciesSolved
            : $this->isMainDependenciesSolved;

        return $this;
    }

    /**
     * @return array
     */
    private function getHistory()
    {
        $operations = array_map(function (OperationInterface $operation) {
            return (string)$operation;
        }, array_values($this->completedOperations));

        if (
            $this->isCompleted()
            || !$this->currentOperation
            || $this->currentOperation->isCompleted()
        ) {
            return $operations;
        }

        return array_merge($operations, [(string)$this->currentOperation]);
    }

    /**
     * @return float
     */
    private function getProgress()
    {
        if (!$this->startProgress) {
            return 0;
        }

        $total = count($this->operations);
        $completed = count($this->completedOperations);

        return $total ? ceil(100 * $completed / $total) : 100;
    }

    /**
     * @return string
     */
    private function getOutputDir()
    {
        $homeDir = $this->composer->getConfig()->get('home');
        $targetDir = $homeDir . DIRECTORY_SEPARATOR . 'plesk';

        if (!is_dir($targetDir)) {
            @mkdir($targetDir, 0755);
        }

        return $targetDir;
    }

    /**
     * @return string
     */
    private function getOutputFile()
    {
        $workingDir = getcwd();
        return $this->getOutputDir() . DIRECTORY_SEPARATOR . base64_encode("{$this->command}-{$workingDir}");
    }

    /**
     * @param $id
     * @return OperationInterface|null
     */
    private function getOperationById($id)
    {
        return isset($this->operations[$id])
            ? $this->operations[$id]
            : null;
    }

    /**
     * @param OperationInterface $operation
     * @return $this
     */
    private function setOperation($operation)
    {
        $this->operations[$operation->getId()] = $operation;

        return $this;
    }

    /**
     * @param OperationInterface $operation
     */
    private function removeOperation($operation)
    {
        unset($this->operations[$operation->getId()]);
    }

    /**
     * @param OperationInterface $operation
     * @return $this
     */
    private function setCompletedOperation($operation)
    {
        $this->completedOperations[$operation->getId()] = $operation;

        return $this;
    }

    /**
     * @param OperationInterface $operation
     */
    private function removeCompletedOperation($operation)
    {
        unset($this->completedOperations[$operation->getId()]);
    }

    /**
     * @param OperationInterface[] $operations
     * @return OperationInterface[]
     */
    private function filterDevPackageOperations($operations)
    {
        $fOperations = [];

        foreach ($operations as $operation) {
            $existedOperation = $this->getOperationById($operation->getId());
            /**
             * @var OperationInterface $operation
             */
            if (
                $operation->getJobType() === OperationInterface::UNINSTALL_OPERATION
                && $existedOperation
                && $existedOperation->getJobType() !== OperationInterface::UNINSTALL_OPERATION
            ) {
                $this->removeOperation($operation);
                continue;
            }

            $fOperations[] = $operation;
        }

        return $fOperations;
    }

    /**
     * @return bool
     */
    private function shouldFilterDevPackagesOperations()
    {
        return $this->isUpdateCommand() && !$this->isDevMode;
    }

    /**
     * @return bool
     */
    private function isUpdateCommand()
    {
        return ($this->command === self::UPDATE_COMMAND)
            || ($this->composer->getLocker() && !$this->composer->getLocker()->isLocked() && $this->command === self::INSTALL_COMMAND);
    }
}

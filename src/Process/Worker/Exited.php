<?php

namespace lucatume\WPBrowser\Process\Worker;

use lucatume\WPBrowser\Process\Worker\Running;
use lucatume\WPBrowser\Process\Worker\WorkerInterface;
use lucatume\WPBrowser\Process\WorkerException;

class Exited implements WorkerInterface
{
    public function __construct(
        private int $exitCode,
        private string $id,
        private mixed $returnValue,
        private string $stdout,
        private string $stderr
    ) {
    }

    /**
     * @throws WorkerException
     */
    public static function fromRunningWorker(Running $runningWorker): Exited
    {
        if ($runningWorker->isRunning()) {
            throw new WorkerException('Worker is still running.');
        }

        return new self(
            $runningWorker->getExitCode(),
            $runningWorker->getId(),
            $runningWorker->getReturnValue(),
            $runningWorker->getStdoutBuffer(),
            $runningWorker->getStderrBuffer()
        );
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getExitCode(): int
    {
        return $this->exitCode;
    }

    public function getReturnValue(): mixed
    {
        return $this->returnValue;
    }

    public function getStdout(): string
    {
        return $this->stdout;
    }

    public function getStderr(): string
    {
        return $this->stderr;
    }
}

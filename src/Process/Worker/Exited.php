<?php

namespace lucatume\WPBrowser\Process\Worker;

use lucatume\WPBrowser\Process\Worker\Running;
use lucatume\WPBrowser\Process\Worker\WorkerInterface;
use lucatume\WPBrowser\Process\WorkerException;

class Exited implements WorkerInterface
{
    private string $id;
    private int $exitCode;
    private mixed $returnValue;
    private string $stdout;
    private string $stderr;

    public function __construct(int $exitCode, string $id, mixed $returnValue, string $stdout, string $stderr)
    {
        $this->exitCode = $exitCode;
        $this->id = $id;
        $this->returnValue = $returnValue;
        $this->stdout = $stdout;
        $this->stderr = $stderr;
    }

    public static function fromRunningWorker(Running $runningWorker): Exited
    {
        if ($runningWorker->isRunning()) {
            throw new WorkerException('Worker is still running.');
        }

        return new static(
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

<?php

namespace lucatume\WPBrowser\Process\Worker;

use lucatume\WPBrowser\Process\WorkerException;

class Exited implements WorkerInterface
{
    /**
     * @var int
     */
    private $exitCode;
    /**
     * @var string
     */
    private $id;
    /**
     * @var mixed
     */
    private $returnValue;
    /**
     * @var string
     */
    private $stdout;
    /**
     * @var string
     */
    private $stderr;
    /**
     * @param mixed $returnValue
     */
    public function __construct(int $exitCode, string $id, $returnValue, string $stdout, string $stderr)
    {
        $this->exitCode = $exitCode;
        $this->id = $id;
        $this->returnValue = $returnValue;
        $this->stdout = $stdout;
        $this->stderr = $stderr;
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

    /**
     * @return mixed
     */
    public function getReturnValue()
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

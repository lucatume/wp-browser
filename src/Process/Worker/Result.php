<?php

namespace lucatume\WPBrowser\Process\Worker;

use lucatume\WPBrowser\Process\MemoryUsage;

class Result
{
    use MemoryUsage;

    public function __construct(
        private string $id,
        private int $exitCode,
        private string $stdoutBuffer = '',
        private string $stderrBuffer = '',
        private mixed $returnValue = null,
        int $memoryUsage = null
    ) {
        $this->memoryUsage = $memoryUsage;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getStdoutBuffer(): string
    {
        return $this->stdoutBuffer;
    }

    public function getStderrBuffer(): string
    {
        return $this->stderrBuffer;
    }

    /**
     * @return mixed|null
     */
    public function getReturnValue(): mixed
    {
        return $this->returnValue;
    }

    public function getExitCode(): int
    {
        return $this->exitCode;
    }
}

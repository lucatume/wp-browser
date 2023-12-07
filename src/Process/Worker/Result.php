<?php

namespace lucatume\WPBrowser\Process\Worker;

use lucatume\WPBrowser\Process\MemoryUsage;

class Result
{
    /**
     * @var string
     */
    private $id;
    /**
     * @var int
     */
    private $exitCode;
    /**
     * @var string
     */
    private $stdoutBuffer = '';
    /**
     * @var string
     */
    private $stderrBuffer = '';
    /**
     * @var mixed
     */
    private $returnValue = null;
    use MemoryUsage;

    /**
     * @param mixed $returnValue
     */
    public function __construct(
        string $id,
        int $exitCode,
        string $stdoutBuffer = '',
        string $stderrBuffer = '',
        $returnValue = null,
        int $memoryUsage = null
    ) {
        $this->id = $id;
        $this->exitCode = $exitCode;
        $this->stdoutBuffer = $stdoutBuffer;
        $this->stderrBuffer = $stderrBuffer;
        $this->returnValue = $returnValue;
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
    public function getReturnValue()
    {
        return $this->returnValue;
    }

    public function getExitCode(): int
    {
        return $this->exitCode;
    }
}

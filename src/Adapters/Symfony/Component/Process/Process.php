<?php

namespace lucatume\WPBrowser\Adapters\Symfony\Component\Process;

use Symfony\Component\Process\Exception\LogicException;
use Symfony\Component\Process\Process as SymfonyProcess;

class Process extends SymfonyProcess
{
    /**
     * @param string[] $command
     * @param array<string,mixed>|null $env
     * @param array<string,mixed>|null $options
     */
    public function __construct(
        array $command,
        string $cwd = null,
        array $env = null,
        mixed $input = null,
        ?float $timeout = 60,
        array $options = null
    ) {
        if (method_exists($this, 'inheritEnvironmentVariables')) {
            parent::__construct($command, $cwd, $env, $input, $timeout, $options); //@phpstan-ignore-line
            $this->inheritEnvironmentVariables(true);
        }

        parent::__construct($command, $cwd, $env, $input, $timeout);
    }

    public function getStartTime(): float
    {
        if (method_exists(parent::class, 'getStartTime')) {
            return parent::getStartTime();
        }

        if (!$this->isStarted()) {
            throw new LogicException('Start time is only available after process start.');
        }

        $startTimeReflectionProperty = new \ReflectionProperty(SymfonyProcess::class, 'starttime');
        $startTimeReflectionProperty->setAccessible(true);
        /** @var float $startTime */
        $startTime = $startTimeReflectionProperty->getValue($this);

        return $startTime;
    }
}
<?php

namespace lucatume\WPBrowser\Adapters\Symfony\Component\Process;

use Symfony\Component\Process\Exception\LogicException;
use Symfony\Component\Process\Process as SymfonyProcess;

class Process extends SymfonyProcess
{
    public function __construct(
        $commandline,
        $cwd = null,
        array $env = null,
        $input = null,
        $timeout = 60,
        array $options = null
    ) {
        parent::__construct($commandline, $cwd, $env, $input, $timeout, $options);

        if (method_exists($this, 'inheritEnvironmentVariables')) {
            $this->inheritEnvironmentVariables(true);
        }
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

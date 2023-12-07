<?php

namespace lucatume\WPBrowser\Adapters\Symfony\Component\Process;

use Symfony\Component\Process\Exception\LogicException;
use Symfony\Component\Process\Process as SymfonyProcess;

class Process extends SymfonyProcess
{
    /**
     * @var array<string,mixed>
     */
    private $options = [];
    /**
     * @param string[] $command
     * @param array<string,mixed>|null $env
     * @param array<string,mixed>|null $options
     * @param mixed $input
     */
    public function __construct(
        array $command,
        string $cwd = null,
        array $env = null,
        $input = null,
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

    /**
     * @param string $name
     * @param array<mixed> $arguments
     * @return void
     */
    public function __call(string $name, array $arguments)
    {
        if ($name === 'setOptions') {
            $this->options = $arguments[0] ?? [];
        }
        return;
    }

    public function __destruct()
    {
        if (($this->options['create_new_console'] ?? false) || method_exists($this, 'setOptions')) {
            parent::__destruct();
            return;
        }

        $closeMethodReflection = new \ReflectionMethod(SymfonyProcess::class, 'close');
        $closeMethodReflection->setAccessible(true);
        $closeMethodReflection->invoke($this);
    }
}

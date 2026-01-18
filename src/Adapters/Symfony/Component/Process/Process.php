<?php

namespace lucatume\WPBrowser\Adapters\Symfony\Component\Process;

use ReflectionMethod;
use ReflectionProperty;
use Symfony\Component\Process\Exception\LogicException;
use Symfony\Component\Process\Pipes\PipesInterface;
use Symfony\Component\Process\Process as SymfonyProcess;

class Process extends SymfonyProcess
{
    private static ?bool $inheritEnvironmentVariables = null;
    private bool $createNewConsole = false;

    /**
     * @param string[] $command
     * @param array<string,mixed>|null $env
     * @param array<string,mixed>|null $options
     */
    public function __construct(
        array $command,
        ?string $cwd = null,
        ?array $env = null,
        mixed $input = null,
        ?float $timeout = 60,
        ?array $options = null
    ) {
        parent::__construct($command, $cwd, $env, $input, $timeout, $options); //@phpstan-ignore-line

        if (self::$inheritEnvironmentVariables === null) {
            self::$inheritEnvironmentVariables = method_exists($this, 'inheritEnvironmentVariables')
                && !str_contains(
                    (string)(new ReflectionMethod($this, 'inheritEnvironmentVariables'))->getDocComment(),
                    '@deprecated'
                );
        }

        if (self::$inheritEnvironmentVariables) {
            // @phpstan-ignore-next-line
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

        $startTimeReflectionProperty = new ReflectionProperty(SymfonyProcess::class, 'starttime');
        if(!version_compare(PHP_VERSION, '8.5', '>=')){
            $startTimeReflectionProperty->setAccessible(true);
        }

        /** @var float $startTime */
        $startTime = $startTimeReflectionProperty->getValue($this);

        return $startTime;
    }

    public function __destruct()
    {
        if ($this->createNewConsole) {
            $processPipesProperty = new ReflectionProperty(SymfonyProcess::class, 'processPipes');
            if(!version_compare(PHP_VERSION, '8.5', '>=')){
                $processPipesProperty->setAccessible(true);
            }
            /** @var PipesInterface $processPipes */
            $processPipes = $processPipesProperty->getValue($this);
            $processPipes->close();

            return;
        }

        $this->stop(0);
    }

    public function createNewConsole(): void
    {
        $this->createNewConsole = true;

        $optionsReflectionProperty = new ReflectionProperty(SymfonyProcess::class, 'options');
        if(!version_compare(PHP_VERSION, '8.5', '>=')){
            $optionsReflectionProperty->setAccessible(true);
        }
        $options = $optionsReflectionProperty->getValue($this);
        $options = is_array($options) ? $options : [];
        $options['create_new_console'] = true;
        $options['bypass_shell'] = true;
        $optionsReflectionProperty->setValue($this, $options);
    }

    /**
     * @param array<mixed> $arguments
     */
    public static function __callStatic(string $name, array $arguments):mixed
    {
        if ($name === 'fromShellCommandline') {
            $command = array_shift($arguments);
            $process = new self([], ...$arguments); // @phpstan-ignore-line
            $processCommandLineProperty = new ReflectionProperty(SymfonyProcess::class, 'commandline');
            if(!version_compare(PHP_VERSION, '8.5', '>=')){
                $processCommandLineProperty->setAccessible(true);
            }
            $processCommandLineProperty->setValue($process, $command);

            return $process;
        }

        return null;
    }
}

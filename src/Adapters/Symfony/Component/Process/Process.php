<?php

namespace lucatume\WPBrowser\Adapters\Symfony\Component\Process;

use Symfony\Component\Process\Exception\LogicException;
use Symfony\Component\Process\Pipes\PipesInterface;
use Symfony\Component\Process\Process as SymfonyProcess;

class Process extends SymfonyProcess
{
    /**
<<<<<<< Updated upstream
=======
     * @var bool|null
     */
private static $inheritEnvironmentVariables;
    /**
>>>>>>> Stashed changes
     * @var bool
     */
    private $createNewConsole = false;

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
    <<<<<<< Updated upstream
    if (method_exists($this, 'inheritEnvironmentVariables')) {
        parent::__construct($command, $cwd, $env, $input, $timeout, $options); //@phpstan-ignore-line
        =======
        parent::__construct($command, $cwd, $env, $input, $timeout, $options); //@phpstan-ignore-line

        if (self::$inheritEnvironmentVariables === null) {
            self::$inheritEnvironmentVariables = method_exists($this, 'inheritEnvironmentVariables')
                && strpos((string)(new ReflectionMethod($this, 'inheritEnvironmentVariables'))->getDocComment(), '@deprecated') === false;
        }

        if (self::$inheritEnvironmentVariables) {
            // @phpstan-ignore-next-line
            >>>>>>> Stashed changes
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

    public function __destruct()
    {
        if ($this->createNewConsole) {
            $processPipesProperty = new \ReflectionProperty(SymfonyProcess::class, 'processPipes');
            $processPipesProperty->setAccessible(true);
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

        $optionsReflectionProperty = new \ReflectionProperty(SymfonyProcess::class, 'options');
        $optionsReflectionProperty->setAccessible(true);
        $options = $optionsReflectionProperty->getValue($this);
        $options = is_array($options) ? $options : [];
        $options['create_new_console'] = true;
        $options['bypass_shell'] = true;
        $optionsReflectionProperty->setValue($this, $options);
    }

    /**
     * @param array<mixed> $arguments
     * @return mixed
     */
    public static function __callStatic(string $name, array $arguments)
    {
        if ($name === 'fromShellCommandline') {
            $command = array_shift($arguments);
            $process = new self([], ...$arguments); // @phpstan-ignore-line
            $processCommandLineProperty = new \ReflectionProperty(SymfonyProcess::class, 'commandline');
            $processCommandLineProperty->setAccessible(true);
            $processCommandLineProperty->setValue($process, $command);

            return $process;
        }

        return null;
    }
}

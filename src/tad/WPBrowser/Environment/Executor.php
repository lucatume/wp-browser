<?php

namespace tad\WPBrowser\Environment;

use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

/**
 * Class Executor
 *
 * Handles execution of stand-alone processes.
 *
 * @package tad\WPBrowser\Environment
 */
class Executor
{
    const DEFAULT_TIMEOUT = 60;

    /**
     * The timeout that will be applied to each process.
     *
     * @var int|float|null
     */
    protected $timeout;

    /**
     * The current process the executor is using.
     *
     * @var Process
     */
    protected $process;

    /**
     * Executor constructor.
     */
    public function __construct()
    {
        $this->timeout = static::DEFAULT_TIMEOUT;
    }

    /**
     * Wraps the `exec` functions with some added debug information.
     *
     * Differently from PHP defaults `exec` function this method will return
     * the command exit status and not the last line of output.
     *
     * @param string $command The command to execute.
     * @param array|null $output An array to store the output.
     *
     * @return int string The return value of the command, if any.
     * @see exec()
     *
     */
    public function exec($command, array &$output = null)
    {
        list($output, $return_var) = $this->realExec($command);

        return $return_var;
    }

    /**
     * A common method to wrap the execution calls.
     *
     * @param string $command The command to execute.
     *
     * @return array An array containing the output and the exit status of the command.
     */
    protected function realExec($command)
    {
        $process = new Process($command);
        $this->process = $process;
        $process->setTimeout($this->timeout);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        codecept_debug($process->getOutput());

        return array($process->getOutput(), $process->getStatus());
    }

    /**
     * Executes a command and outputs the command output.
     *
     * @param string $command The command to execute.
     * @param $return_var |null The return value of the command.
     *
     * @return mixed The command output, if any.
     */
    public function execAndOutput($command, &$return_var)
    {
        list($output, $return_var) = $this->realExec($command);

        return $output;
    }

    /**
     * Sets the process timeout for the execution of each command (max. runtime).
     *
     * To disable the timeout, set this value to `null`.
     *
     * @param int|float|null $timeout The timeout in seconds.
     */
    public function setTimeout($timeout)
    {
        if ($timeout < 0 || (is_string($timeout) && !is_numeric($timeout))) {
            throw new \InvalidArgumentException(
                'Allowed `timeout` values are positive integer and floats, '
                . 'or `false`, `null` and `0` to disable the timeout.'
            );
        }

        $this->timeout = $timeout;
    }

    /**
     * Returns the process instance the executor is using, if any.
     *
     * @return Process|null
     */
    public function getProcess()
    {
        return $this->process;
    }
}

<?php
/**
 * Extends the `mikehaertl/php-shellcommand\Command` class to add API to it to wrap it in a Symfony/Process like
 * API.
 *
 * @since TBD
 *
 * @package tad\WPBrowser\Process
 */

namespace tad\WPBrowser\Process;

use mikehaertl\shellcommand\Command;

class Process extends Command
{
    /**
     * Whether the process should inherit the current `$_ENV` or not.
     *
     * @var bool
     */
    protected $inheritEnvironmentVariables;

    /**
     * Sets the process output.
     *
     * @param int|null $timeout The process timeout in seconds.
     *
     * @return void
     */
    public function setTimeout($timeout)
    {
        // @phpstan-ignore-next-line
        $this->timeout = $timeout === null ? $timeout : abs($timeout);
    }

    /**
     * Sets whether the process should inherit the current `$_ENV` or not.
     *
     * @param bool $inheritEnvironmentVariables Whether the process should inherit the current `$_ENV` or not.
     *
     * @return void
     */
    public function inheritEnvironmentVariables($inheritEnvironmentVariables)
    {
        $this->inheritEnvironmentVariables = (bool)$inheritEnvironmentVariables;
    }

    /**
     * Runs the process, throwing an exception if the process fails.
     *
     * @return Process The process that successfully ran.
     *
     * @throws ProcessFailedException If the process execution fails.
     */
    public function mustRun()
    {
        if ($this->execute() === false) {
            throw new ProcessFailedException($this);
        }

        return $this;
    }

    /**
     * Clones the current instance and returns a new one for the specified command.
     *
     * @param string|array<string> $command The command to run.
     *
     * @return Process A cloned instance of this process to run the specified command.
     */
    public function withCommand($command)
    {
        if (is_array($command)) {
            $command = implode(' ', $command);
        }
        $clone = clone $this;

        return $clone->setCommand($command);
    }

    /**
     * Returns an instance of this process with the modified environment.
     *
     * @param array<string,mixed> $env The new process environment.
     *
     * @return Process A clone of the current process with the set environment.
     */
    public function withEnv(array $env = [])
    {
        $clone = clone $this;
        $clone->procEnv = $env;
        return $clone;
    }

    /**
     * Returns the current process environment variables.
     *
     * @return array<string,mixed>A The current Process environment variables.
     */
    public function getEnv()
    {
        return (array)$this->procEnv;
    }

    /**
     * Builds and returns a new instance of the process with the specified working directory.
     *
     * @param string $cwd The process working directory.
     *
     * @return Process A clone of the current process with the specified working directory set.
     */
    public function withCwd($cwd)
    {
        $clone = clone $this;
        $clone->procCwd = $cwd;

        return $clone;
    }

    /**
     * Returns whether the process was successful (exit 0) or not.
     *
     * @return bool Whether the process was successful (exit 0) or not.
     */
    public function isSuccessful()
    {
        return (int)$this->getExitCode() === 0;
    }

    /**
     * Returns the process current working directory.
     *
     * @return string|null The process current working directory.
     */
    public function getWorkingDirectory()
    {
        return $this->procCwd;
    }
}

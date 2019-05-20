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
    /**
     * Wraps the `exec` functions with some added debug information.
     *
     * Differently from PHP defaults `exec` function this method will return
     * the command exit status and not the last line of output.
     *
     * @see exec()
     *
     * @param string $command
     * @param array $output
     *
     * @return int string
     */
    public function exec($command, array &$output = null)
    {
        list($output, $return_var) = $this->realExec($command);

        return $return_var;
    }

    public function execAndOutput($command, &$return_var)
    {
        list($output, $return_var) = $this->realExec($command);

        return $output;
    }

    /**
     * @param $command
     * @return array
     */
    protected function realExec($command)
    {
        $process = new Process($command);
        $process->run();

        if (! $process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        codecept_debug($process->getOutput());

        return array( $process->getOutput(), $process->getStatus() );
    }
}

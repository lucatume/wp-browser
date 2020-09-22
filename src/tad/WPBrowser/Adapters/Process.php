<?php
/**
 * Wraps the Symfony process class to provide an injectable instance.
 *
 * @package tad\WPBrowser\Adapters
 */

namespace tad\WPBrowser\Adapters;

/**
 * Class Process
 *
 * @package tad\WPBrowser\Adapters
 */
class Process
{

    /**
     * Builds a Symfony process for a command.
     *
     * @param array<string>|string $command The components of the command to run.
     * @param string|null          $cwd     The current working directory to set for the process, if any.
     *
     * @return \Symfony\Component\Process\Process<string,string> The built, and ready to run, process handler.
     */
    public function forCommand($command, $cwd = null)
    {
        return new \Symfony\Component\Process\Process((array)$command, $cwd);
    }
}

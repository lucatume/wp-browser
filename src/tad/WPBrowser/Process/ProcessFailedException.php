<?php
/**
 * An exception thrown by a process that failed.
 *
 * @package tad\WPBrowser\Process
 */

namespace tad\WPBrowser\Process;

/**
 * Class ProcessFailedException
 *
 * @package tad\WPBrowser\Process
 */
class ProcessFailedException extends \RuntimeException
{
    /**
     * ProcessFailedException constructor.
     *
     * @param Process $process The process instance the exception originated from.
     */
    public function __construct(Process $process)
    {
        $message = sprintf(
            'Command "%s" failed (%d) with error: %s',
            $process->getCommand(),
            $process->getExitCode(),
            $process->getError()
        );
        parent::__construct($message, (int)$process->getExitCode());
    }
}

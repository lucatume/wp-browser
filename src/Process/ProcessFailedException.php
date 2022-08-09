<?php
/**
 * An exception thrown by a process that failed.
 *
 * @package lucatume\WPBrowser\Process
 */

namespace lucatume\WPBrowser\Process;

/**
 * Class ProcessFailedException
 *
 * @package lucatume\WPBrowser\Process
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

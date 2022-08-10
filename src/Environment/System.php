<?php

namespace lucatume\WPBrowser\Environment;

/**
 * Class System
 *
 * Wraps and abstract system operations.
 *
 * @package lucatume\WPBrowser\Environment
 */
class System
{

    /**
     * Returns the output of a command executed using the `system` function.
     *
     * @param string   $command    The command to execute.
     * @param int|null $returnVar  If the return_var argument is present, then the return status of the executed command
     *                             will be written to this variable.
     *
     * @return string|false The last line of the command output on success, and `false` on failure.
     */
    public function system($command, &$returnVar = null): string|false
    {
        ob_start();
        $output = system($command, $returnVar);
        codecept_debug(static::class . " executed command:\n\t{$command}\nwith output:\n\t$" . ob_get_clean());

        return $output;
    }
}

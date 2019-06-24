<?php

namespace tad\WPBrowser\Environment;

/**
 * Class System
 *
 * Wraps and abstract system operations.
 *
 * @package tad\WPBrowser\Environment
 */
class System
{

    public function system($command, &$returnVar = null)
    {
        ob_start();
        $output = system($command, $returnVar);
        codecept_debug(static::class . " executed command:\n\t{$command}\nwith output:\n\t$" . ob_get_clean());

        return $output;
    }
}

<?php
/**
 * Deprecated functions.
 *
 * @package tad\WPBrowser
 */

if (! function_exists('rrmdir')) {
    /**
     * Recursively removes a directory and all its content.
     *
     * @see        tad\WPBrowser\rrmdir() for the replacement function.
     * @deprecated Since 2.3; moved to the `\tad\WPBrowser` namespace.
     *
     * @param string $src The absolute path to the directory to remove.
     *
     * @return bool Whether the directory, and all its contents, were correctly removed or not.
     */
    function rrmdir($src)
    {
        return tad\WPBrowser\rrmdir($src);
    }
}

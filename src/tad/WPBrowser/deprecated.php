<?php
/**
 * Deprecated functions.
 *
 * @package tad\WPBrowser
 */

/**
 * Recursively removes a directory and all its content.
 *
 * @param string $src The absolute path to the directory to remove.
 *
 * @deprecated Since 2.3; moved to the `\tad\WPBrowser` namespace.
 *
 * @see tad\WPBrowser\rrmdir() for the replacement function.
 */
function rrmdir($src)
{
    tad\WPBrowser\rrmdir($src);
}

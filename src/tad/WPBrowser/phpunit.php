<?php
/**
 * Funcions related to PHPUnit.
 *
 * @package lucatume\WPBrowser
 */

namespace tad\WPBrowser;

/**
 * Returns the PHPUnit version currently installed.
 *
 * Falls back on version 5 if none can be found.
 *
 * @return string The current PHPUnit version.
 */
function phpunitVersion()
{
    if (class_exists('PHPUnit\Runner\Version')) {
        return \PHPUnit\Runner\Version::series();
    }

    if (class_exists('PHPUnit_Runner_Version')) {
        return PHPUnit_Runner_Version::series();
    }

    return '5.0';
}

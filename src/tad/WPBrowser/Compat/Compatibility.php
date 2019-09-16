<?php
/**
 * Handles the compatibility with different PHP, PHPUnit, Codeception and PHPUnit wrappers.
 *
 * @package tad\WPBrowser\Compat
 */

namespace tad\WPBrowser\Compat;

/**
 * Class Compatibility
 *
 * @package tad\WPBrowser\Compat
 */
class Compatibility
{

    /**
     * Returns the first existing setUp method for a base test case class.
     *
     * This method is required to handle with different PHP, PHPUnit, Codeception and Codeception PHPUnit wrapper
     * versions.
     *
     * @param string $class The fully-qualified name of the class to return the set up method for.
     *
     * @return string The class setup method name; default to the PHPUnit default `setUp` if not found.
     */
    public static function setupMethodFor($class)
    {
        return method_exists($class, '_setUp') ? '_setUp' : 'setUp';
    }

    /**
     * Returns the PHPUnit version currently installed.
     *
     * Falls back on version 5 if none can be found.
     *
     * @return string The current PHPUnit version.
     */
    public function phpunitVersion()
    {
        if (class_exists('PHPUnit\Runner\Version')) {
            return \PHPUnit\Runner\Version::series();
        }

        if (class_exists('PHPUnit_Runner_Version')) {
            return PHPUnit_Runner_Version::series();
        }

        return '5.0';
    }
}

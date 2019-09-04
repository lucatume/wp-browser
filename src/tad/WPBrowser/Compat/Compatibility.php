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

    public static function setupMethodFor($class)
    {
        return method_exists($class, '_setUp') ? '_setup': 'setUp';
    }
}

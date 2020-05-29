<?php
/**
 * Functions dedicate to WordPress interaction.
 *
 * @package tad\WPBrowser
 */

// phpcs:ignoreFile

/*
 * This will not be defined when NOT in separate process context, but it will be required to run tests in separate
 * process. So let's define it if not already defined.
 */

if (! defined('PHPUNIT_COMPOSER_INSTALL')) {
    if (isset($GLOBALS['composerAutoload'], $GLOBALS['phar'])) {
        /*
         * Yet if the two vars above are defined, then it means this code in running in the context of the PHPUnit
         * separate process script, so it should not be re-defined as it will be defined by the script.
         */
        return;
    }

    define('PHPUNIT_COMPOSER_INSTALL', tad\WPBrowser\vendorDir('autoload.php'));
}

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

    try {
        $autoloadPath = isset($GLOBALS['_composer_autoload_path']) ?
            $GLOBALS['_composer_autoload_path']
            : tad\WPBrowser\vendorDir('autoload.php');
        define('PHPUNIT_COMPOSER_INSTALL', $autoloadPath);
    } catch (Exception $e) {
        // No-op: we might be running in a context that will not allow it to be found.
    }
}

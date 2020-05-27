<?php
/**
 * Functions dedicate to WordPress interaction.
 *
 * @package tad\WPBrowser
 */

namespace tad\WPBrowser;

// When tests run in isolation PHPUnit will look for this constant to find the Composer-managed autoloader.
if (! defined('PHPUNIT_COMPOSER_INSTALL')) {
    define('PHPUNIT_COMPOSER_INSTALL', vendorDir('autoload.php'));
}

<?php
/**
 * Provides methods to detect the correct configuration of separate process testing in tests.
 *
 * A different version of the trait is loaded depending on the PHP version.
 *
 * @package tad\WPBrowser\Traits
 */

//phpcs:ignoreFile

namespace tad\WPBrowser\Traits;

if ( PHP_VERSION_ID >= 70000 ) {
	require_once __DIR__ . '/_WithSeparateProcessChecksPHP7.php';
} else {
	require_once __DIR__ . '/_WithSeparateProcessChecksPHP56.php';
}

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

use function tad\WPBrowser\phpunitVersion;

if ( version_compare(phpunitVersion(),'7.0.0','>=') ) {
	// Load the version compatible with PHPUnit >= 7.0.0.
	require_once __DIR__ . '/_WithSeparateProcessChecksPHPUnitGte7.php';
} else {
	// Load the version compatible with PHPUnit < 7.0.0.
	require_once __DIR__ . '/_WithSeparateProcessChecksPHPUnitLt7.php';
}

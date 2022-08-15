<?php
/**
 * This file is loaded by the `wp-tests-config.php` file to stub the Yoast PHPUnit polyfills
 * files the Core test suite will look for. The polyfills are not required since Codeception
 * 5 requires PHPUnit v9.
 *
 * Thanks Yoast team for your work on the Core test suite.
 */

namespace Yoast\PHPUnitPolyfills\TestCases;

if (class_exists(TestCase::class)) {
    return;
}

/**
 * Since Codeception 5 requires PHPUnit v9, the polyfill test case is
 * just an extension of the base PHPUnit test case.
 */
class TestCase extends \PHPUnit\Framework\TestCase
{
}

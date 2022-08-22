<?php
/**
 * This file is loaded by the `wp-tests-config.php` file to stub the Yoast PHPUnit polyfills
 * files the Core test suite will look for. The polyfills are not required since Codeception
 * 5 requires PHPUnit v9.
 *
 * Thanks Yoast team for your work on the Core test suite.
 */

namespace Yoast\PHPUnitPolyfills;

if (class_exists(Autoload::class)) {
    return;
}

class Autoload
{
    // The specific required version will change in Core files, set it to a high number to be fine.
    public const VERSION = '10.0.0';
}

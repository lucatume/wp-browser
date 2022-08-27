<?php

/**
 * This file is loaded by the `wp-tests-config.php` file to stub the Yoast PHPUnit polyfills
 * files the Core test suite will look for. The polyfills are not required since Codeception
 * 5 requires PHPUnit v9.
 *
 * Thanks Yoast team for your work on the Core test suite.
 */

namespace Yoast\PHPUnitPolyfills\TestCases;

class TestCase extends \PHPUnit\Framework\TestCase
{
    public static function set_up_before_class()
    {
    }

    protected function set_up()
    {
    }

    protected function assert_pre_conditions()
    {
    }

    protected function assert_post_conditions()
    {
    }

    protected function tear_down()
    {
    }

    public static function tear_down_after_class()
    {
    }
}

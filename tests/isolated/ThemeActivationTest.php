<?php

use function tad\WPBrowser\db;

class ThemeActivationTest extends \Codeception\TestCase\WPTestCase
{
    public function test_theme_is_loaded()
    {
        $this->assertTrue(function_exists('isolated_theme_canary'));
    }
}

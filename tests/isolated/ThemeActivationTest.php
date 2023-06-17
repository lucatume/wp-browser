<?php

use lucatume\WPBrowser\TestCase\WPTestCase;

class ThemeActivationTest extends WPTestCase
{
    public function test_theme_is_loaded()
    {
        $this->assertTrue(function_exists('isolated_theme_canary'));
    }
}

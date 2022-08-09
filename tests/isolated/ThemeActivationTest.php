<?php

class ThemeActivationTest extends \lucatume\WPBrowser\TestCase\WPTestCase
{
    public function test_theme_is_loaded()
    {
        $this->assertTrue(function_exists('isolated_theme_canary'));
    }
}

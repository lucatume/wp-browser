<?php

use lucatume\WPBrowser\TestCase\WPTestCase;

class IsolatedThemeTest extends WPTestCase
{
    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function test_theme_is_loaded()
    {
        $this->assertTrue(function_exists('isolated_theme_canary'));
    }
}

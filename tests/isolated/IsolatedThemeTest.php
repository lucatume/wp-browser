<?php

class IsolatedThemeTest extends \lucatume\WPBrowser\TestCase\WPTestCase
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

<?php

use function tad\WPBrowser\db;

class IsolatedThemeTest extends \Codeception\TestCase\WPTestCase
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

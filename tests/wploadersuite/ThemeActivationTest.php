<?php

use lucatume\WPBrowser\TestCase\WPTestCase;

class ThemeActivationTest extends WPTestCase
{
    /**
     * @test
     * it should call switch_theme during setup
     */
    public function it_should_call_switch_theme_during_setup()
    {
        $this->assertEquals('dummy', strtolower(get_option('dummy_switch_theme_called')));
    }

    /**
     * @test
     * it should load the theme during setup
     */
    public function it_should_load_the_theme_during_setup()
    {
        $this->assertTrue(wp_get_theme()->get_stylesheet() === 'dummy');
        $this->assertTrue(function_exists('dummy_theme_function'));
    }
}

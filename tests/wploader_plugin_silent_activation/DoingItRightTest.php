<?php

class DoingItRightTest extends \Codeception\TestCase\WPTestCase
{
    /**
     * It should have correctly activated the plugin
     *
     * @test
     */
    public function should_have_correctly_activated_the_plugin()
    {
        $this->assertTrue(is_plugin_active('doing-it-right/plugin.php'));
        $this->assertEquals('activated', get_option('doing_it_right_activation'));
    }

    /**
     * It should correctly load the plugin
     *
     * @test
     */
    public function should_correctly_load_the_plugin()
    {
        global $doing_it_right_plugin_loaded;
        $this->assertTrue($doing_it_right_plugin_loaded);
    }
}

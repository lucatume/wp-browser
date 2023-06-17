<?php

use lucatume\WPBrowser\TestCase\WPTestCase;

class PluginActivationTest extends WPTestCase
{
    public function test_plugin_is_loaded()
    {
        $this->assertTrue(function_exists('isolated_test_canary_function'));
    }

    public function test_table_existence()
    {
        $id = isolated_test_write('foo-bar-baz');
        $read = isolated_test_read($id);

        $this->assertEquals('foo-bar-baz', $read);
    }

    public function test_table_cleanup()
    {
        $read = isolated_test_read_all();

        $this->assertEmpty($read);
    }

    public function test_plugin_two_is_loaded()
    {
        $this->assertTrue(function_exists('isolated_test_two_canary_function'));
    }

    public function test_table_two_existence()
    {
        $id = isolated_test_two_write('foo-bar-baz');
        $read = isolated_test_two_read($id);

        $this->assertEquals('foo-bar-baz', $read);
    }

    public function test_table_two_cleanup()
    {
        $read = isolated_test_two_read_all();

        $this->assertEmpty($read);
    }
}

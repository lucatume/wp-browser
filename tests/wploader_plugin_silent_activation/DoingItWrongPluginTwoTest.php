<?php

class DoingItWrongPluginTwoTest extends \Codeception\TestCase\WPTestCase {
	/**
	 * It should have activated plugin two
	 *
	 * @test
	 */
	public function should_have_activated_plugin_two() {
		$this->assertTrue( is_plugin_active( 'doing-it-wrong-2/plugin.php' ) );
	}

	/**
	 * It should have not set the option during the plugin activation
	 *
	 * @test
	 */
	public function should_have_set_not_the_option_during_the_plugin_activation() {
		$this->assertEquals( '', get_option( 'doing_it_wrong_2_activation' ) );
	}

	/**
	 * It should correctly load the plugin
	 *
	 * @test
	 */
	public function should_correctly_load_the_plugin() {
		global $doing_it_wrong_2_plugin_loaded;
		$this->assertTrue( $doing_it_wrong_2_plugin_loaded );
	}
}

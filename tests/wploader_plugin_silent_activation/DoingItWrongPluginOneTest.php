<?php

class DoingItWrongPluginOneTest extends \Codeception\TestCase\WPTestCase {
	/**
	 * It should have activated plugin one
	 *
	 * @test
	 */
	public function should_have_activated_plugin_one() {
		$this->assertTrue( is_plugin_active( 'doing-it-wrong-1/plugin.php' ) );
	}

	/**
	 * It should not have set the option during the plugin activation
	 *
	 * @test
	 */
	public function should_have_not_set_the_option_during_the_plugin_activation(){
		$this->assertEquals( '', get_option( 'doing_it_wrong_1_activation' ) );
	}

	/**
	 * It should correctly load the plugin
	 *
	 * @test
	 */
	public function should_correctly_load_the_plugin() {
		global $doing_it_wrong_1_plugin_loaded;
		$this->assertTrue( $doing_it_wrong_1_plugin_loaded );
	}
}

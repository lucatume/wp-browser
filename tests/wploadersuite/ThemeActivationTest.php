<?php

class ThemeActivationTest extends \Codeception\TestCase\WPTestCase
{
	public function setUp()
	{
		// before
		parent::setUp();

		// your set up methods here
	}

	public function tearDown()
	{
		// your tear down methods here

		// then
		parent::tearDown();
	}

	/**
	 * @test
	 * it should switch to the theme during installation
	 */
	public function it_should_switch_to_the_theme_during_installation()
	{
		$this->assertEquals('dummy', get_option('dummy_after_switch_theme_called'));
	}

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

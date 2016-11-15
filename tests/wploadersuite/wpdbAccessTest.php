<?php


class wpdbAccessTest extends \Codeception\TestCase\WPTestCase
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
	 * it should allow accessing the wpdb instance in tests
	 */
	public function it_should_allow_accessing_the_wpdb_instance_in_tests()
	{
		/** @var \wpdb $wpdb */
		global $wpdb;

		$this->assertNotEmpty($wpdb);
		$this->assertInstanceOf('wpdb', $wpdb);
	}

	/**
	 * @test
	 * it should allow running queries using wpdb
	 */
	public function it_should_allow_running_queries_using_wpdb()
	{
		/** @var \wpdb $wpdb */
		global $wpdb;

		$wpdb->get_results("select * from {$wpdb->posts}");
	}
}
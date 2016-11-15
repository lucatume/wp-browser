<?php
namespace Codeception\Module;

use Codeception\Lib\ModuleContainer;
use tad\WPBrowser\Environment\Constants;

class WPQueriesTest extends \Codeception\TestCase\Test
{
	protected $backupGlobals = false;
	/**
	 * @var \UnitTester
	 */
	protected $tester;

	/**
	 * @var ModuleContainer
	 */
	protected $moduleContainer;

	/**
	 * @var array
	 */
	protected $config = [];

	/**
	 * @var Constants
	 */
	protected $constants;

	/**
	 * @var \wpdb
	 */
	protected $wpdb;

	/**
	 * @test
	 * it should be instantiatable
	 */
	public function it_should_be_instantiatable()
	{
		$sut = $this->make_instance();

		$this->assertInstanceOf('Codeception\Module\WPQueries', $sut);
	}

	/**
	 * @return WPQueries
	 */
	private function make_instance()
	{
		return new WPQueries($this->moduleContainer->reveal(), $this->config, $this->constants->reveal(), $this->wpdb);
	}

	/**
	 * @test
	 * it should throw if WPLoader and WPBootstrapper modules are not loaded in module container
	 */
	public function it_should_throw_if_wploader_and_wpbootstrapper_modules_are_not_loaded_in_module_container()
	{
		$this->expectException('Codeception\Exception\ModuleException');

		$this->moduleContainer->hasModule('WPLoader')->willReturn(false);
		$this->moduleContainer->hasModule('WPBootstrapper')->willReturn(false);

		$this->make_instance()->_initialize();
	}

	/**
	 * @test
	 * it should define the SAVEQUERIES constant if not defined already
	 */
	public function it_should_define_the_savequeries_constant_if_not_defined_already()
	{
		$this->constants->defineIfUndefined('SAVEQUERIES', true)->shouldBeCalled();
		$sut = $this->make_instance();
		$sut->_initialize();
	}

	/**
	 * @test
	 * it should filter setUp and tearDown queries by default
	 */
	public function it_should_filter_set_up_and_tear_down_queries_by_default()
	{
		$this->wpdb->queries = [
			[
				'first SQL statement',
				'some ms timing',
				'a stack trace including Codeception\TestCase\WPTestCase->setUp'
			],
			[
				'second SQL statement',
				'some ms timing',
				'a stack trace including Codeception\TestCase\WPTestCase->setUp'
			],
			[
				'third SQL statement',
				'some ms timing',
				'a stack trace calling Acme\MyPlugin->someMethod'
			],
			[
				'fourth SQL statement',
				'some ms timing',
				'a stack trace including WP_UnitTest_Factory_For_Thing->create'
			],
			[
				'fifth SQL statement',
				'some ms timing',
				'a stack trace including WP_UnitTest_Factory_For_Thing->create'
			],
			[
				'sixth SQL statement',
				'some ms timing',
				'a stack trace calling Acme\MyPlugin->someMethod'
			],
			[
				'seventh SQL statement',
				'some ms timing',
				'a stack trace including Codeception\TestCase\WPTestCase->tearDown'
			],
		];

		$sut = $this->make_instance();
		$iterator = $sut->_getFilteredQueriesIterator($this->wpdb);

		$items = [];

		foreach ($iterator as $item) {
			$items[] = $item;
		}

		$this->assertCount(2, $items);
		$this->assertEquals('third SQL statement', $items[0][0]);
		$this->assertEquals('sixth SQL statement', $items[1][0]);
	}

	/**
	 * @test
	 * it should return false if asserting queries and there were no queries
	 */
	public function it_should_return_false_if_asserting_queries_and_there_were_no_queries()
	{
		$this->wpdb->queries = [
			[
				'first SQL statement',
				'some ms timing',
				'a stack trace including Codeception\TestCase\WPTestCase->setUp'
			],
			[
				'second SQL statement',
				'some ms timing',
				'a stack trace including Codeception\TestCase\WPTestCase->setUp'
			],
			[
				'fourth SQL statement',
				'some ms timing',
				'a stack trace including WP_UnitTest_Factory_For_Thing->create'
			],
			[
				'fifth SQL statement',
				'some ms timing',
				'a stack trace including WP_UnitTest_Factory_For_Thing->create'
			],
			[
				'seventh SQL statement',
				'some ms timing',
				'a stack trace including Codeception\TestCase\WPTestCase->tearDown'
			],
		];

		$this->expectException('PHPUnit_Framework_AssertionFailedError');

		$sut = $this->make_instance();
		$sut->assertQueries();
	}

	/**
	 * @test
	 * it should not fail if asserting queries and there were queries
	 */
	public function it_should_not_fail_if_asserting_queries_and_there_were_queries()
	{
		$this->wpdb->queries = [
			[
				'first SQL statement',
				'some ms timing',
				'a stack trace including Codeception\TestCase\WPTestCase->setUp'
			],
			[
				'second SQL statement',
				'some ms timing',
				'a stack trace including Acme\MyPlugin->someMethod'
			],
			[
				'seventh SQL statement',
				'some ms timing',
				'a stack trace including Codeception\TestCase\WPTestCase->tearDown'
			],
		];

		$sut = $this->make_instance();
		$sut->assertQueries();
	}

	/**
	 * @test
	 * it should fail if asserting no queries but queries were made
	 */
	public function it_should_fail_if_asserting_no_queries_but_queries_were_made()
	{
		$this->wpdb->queries = [
			[
				'first SQL statement',
				'some ms timing',
				'a stack trace including Codeception\TestCase\WPTestCase->setUp'
			],
			[
				'second SQL statement',
				'some ms timing',
				'a stack trace including Acme\MyPlugin->someMethod'
			],
			[
				'seventh SQL statement',
				'some ms timing',
				'a stack trace including Codeception\TestCase\WPTestCase->tearDown'
			],
		];

		$this->expectException('PHPUnit_Framework_AssertionFailedError');

		$sut = $this->make_instance();
		$sut->assertNotQueries();
	}

	/**
	 * @test
	 * it should succeed if asserting no queries and no queries were made
	 */
	public function it_should_succeed_if_asserting_no_queries_and_no_queries_were_made()
	{
		$this->wpdb->queries = [
			[
				'first SQL statement',
				'some ms timing',
				'a stack trace including Codeception\TestCase\WPTestCase->setUp'
			],
			[
				'seventh SQL statement',
				'some ms timing',
				'a stack trace including Codeception\TestCase\WPTestCase->tearDown'
			],
		];

		$sut = $this->make_instance();
		$sut->assertNotQueries();
	}

	/**
	 * @test
	 * it should allow counting the queries
	 */
	public function it_should_allow_counting_the_queries()
	{
		$this->wpdb->queries = [
			[
				'SQL statement',
				'some ms timing',
				'a stack trace including Codeception\TestCase\WPTestCase->setUp'
			],
			[
				'SQL statement',
				'some ms timing',
				'a stack trace including Acme\MyPlugin->someMethod'
			],
			[
				'SQL statement',
				'some ms timing',
				'a stack trace including Acme\MyPlugin->someMethod'
			],
			[
				'SQL statement',
				'some ms timing',
				'a stack trace including Codeception\TestCase\WPTestCase->tearDown'
			],
		];

		$sut = $this->make_instance();
		$sut->assertCountQueries(2);
	}

	/**
	 * @test
	 * it should fail if asserting wrong queries count
	 */
	public function it_should_fail_if_asserting_wrong_queries_count()
	{
		$this->wpdb->queries = [
			[
				'SQL statement',
				'some ms timing',
				'a stack trace including Codeception\TestCase\WPTestCase->setUp'
			],
			[
				'SQL statement',
				'some ms timing',
				'a stack trace including Acme\MyPlugin->someMethod'
			],
			[
				'SQL statement',
				'some ms timing',
				'a stack trace including Acme\MyPlugin->someMethod'
			],
			[
				'SQL statement',
				'some ms timing',
				'a stack trace including Codeception\TestCase\WPTestCase->tearDown'
			],
		];

		$this->expectException('PHPUnit_Framework_AssertionFailedError');

		$sut = $this->make_instance();
		$sut->assertCountQueries(1);
	}

	/**
	 * @test
	 * it should allow asserting queries count by statement
	 */
	public function it_should_allow_asserting_queries_by_statement()
	{
		$this->wpdb->queries = [
			[
				'INSERT INTO ... (SELECT * ...)',
				'some ms timing',
				'a stack trace including Acme\MyPlugin->someMethod'
			],
			[
				'SELECT ID FROM ... (SELECT...)',
				'some ms timing',
				'a stack trace including Acme\MyPlugin->someMethod'
			],
			[
				'SELECT * FROM ... INSERT',
				'some ms timing',
				'a stack trace including Acme\MyPlugin->someMethod'
			],
			[
				'UPDATE some_table... (SELECT',
				'some ms timing',
				'a stack trace including Acme\MyPlugin->someMethod'
			],
		];

		$sut = $this->make_instance();

		$sut->assertQueriesByStatement('SELECT');
		$sut->assertQueriesCountByStatement(2, 'SELECT');

		$this->expectException('PHPUnit_Framework_AssertionFailedError');
		$sut->assertQueriesByStatement('DELETE');
		$sut->assertNotQueriesByStatement('DELETE');

		$this->expectException('PHPUnit_Framework_AssertionFailedError');
		$sut->assertQueriesCountByStatement(1, 'SELECT');

		$this->expectException('PHPUnit_Framework_AssertionFailedError');
		$sut->assertQueriesCountByStatement(3, 'SELECT');

		$sut->assertQueriesByStatement('UPDATE');
		$sut->assertQueriesCountByStatement(1, 'UPDATE');

		$this->expectException('PHPUnit_Framework_AssertionFailedError');
		$sut->assertQueriesCountByStatement(2, 'UPDATE');
	}

	/**
	 * @test
	 * it should allow asserting queries by class method
	 */
	public function it_should_allow_asserting_queries_by_class_method()
	{
		$this->wpdb->queries = [
			[
				'INSERT INTO ... (SELECT * ...)',
				'some ms timing',
				'a stack trace including Acme\MyPlugin->methodOne'
			],
			[
				'SELECT ID FROM ... (SELECT...)',
				'some ms timing',
				'a stack trace including Acme\MyPlugin->methodTwo'
			],
			[
				'SELECT * FROM ... INSERT',
				'some ms timing',
				'a stack trace including Acme\MyPlugin->methodTwo'
			],
			[
				'UPDATE some_table... (SELECT',
				'some ms timing',
				'a stack trace including Acme\MyPlugin->methodThree'
			],
		];

		$sut = $this->make_instance();

		$sut->assertQueriesByMethod('Acme\MyPlugin', 'methodOne');
		$sut->assertQueriesByMethod('\Acme\MyPlugin', 'methodOne');
		$sut->assertQueriesCountByMethod(2, 'Acme\MyPlugin', 'methodTwo');
		$sut->assertNotQueriesByMethod('Acme\MyPlugin', 'someMethod');

		$this->expectException('PHPUnit_Framework_AssertionFailedError');
		$sut->assertQueriesByMethod('Acme\MyPlugin', 'methodFour');

		$this->expectException('PHPUnit_Framework_AssertionFailedError');
		$sut->assertQueriesByMethod('\Acme\MyPlugin', 'methodFour');

		$this->expectException('PHPUnit_Framework_AssertionFailedError');
		$sut->assertQueriesCountByMethod(3, 'Acme\MyPlugin', 'methodTwo');

		$this->expectException('PHPUnit_Framework_AssertionFailedError');
		$sut->assertNotQueriesByMethod('Acme\MyPlugin', 'methodTwo');
	}

	/**
	 * @test
	 * it should allow asserting queries by function
	 */
	public function it_should_allow_asserting_queries_by_function()
	{
		$this->wpdb->queries = [
			[
				'INSERT INTO ... (SELECT * ...)',
				'some ms timing',
				'a stack trace including functionOne'
			],
			[
				'SELECT ID FROM ... (SELECT...)',
				'some ms timing',
				'a stack trace including functionTwo'
			],
			[
				'SELECT * FROM ... INSERT',
				'some ms timing',
				'a stack trace including functionTwo'
			],
			[
				'UPDATE some_table... (SELECT',
				'some ms timing',
				'a stack trace including functionThree'
			],
		];

		$sut = $this->make_instance();

		$sut->assertQueriesByFunction('functionOne');
		$sut->assertQueriesCountByFunction(2, 'functionTwo');
		$sut->assertNotQueriesByFunction('someFunction');

		$this->expectException('PHPUnit_Framework_AssertionFailedError');
		$sut->assertQueriesByFunction('functionFour');

		$this->expectException('PHPUnit_Framework_AssertionFailedError');
		$sut->assertQueriesByFunction('functionFour');

		$this->expectException('PHPUnit_Framework_AssertionFailedError');
		$sut->assertQueriesCountByFunction(3, 'functionTwo');

		$this->expectException('PHPUnit_Framework_AssertionFailedError');
		$sut->assertNotQueriesByFunction('functionTwo');
	}

	/**
	 * @test
	 * it should allow asserting queries by class method and statement
	 */
	public function it_should_allow_asserting_queries_by_class_method_and_statement()
	{
		$this->wpdb->queries = [
			[
				'INSERT INTO ... (SELECT * ...)',
				'some ms timing',
				'a stack trace including Acme\MyPlugin->methodOne'
			],
			[
				'SELECT ID FROM ... (SELECT...)',
				'some ms timing',
				'a stack trace including Acme\MyPlugin->methodTwo'
			],
			[
				'SELECT * FROM ... INSERT',
				'some ms timing',
				'a stack trace including Acme\MyPlugin->methodTwo'
			],
			[
				'UPDATE some_table... (SELECT',
				'some ms timing',
				'a stack trace including Acme\MyPlugin->methodThree'
			],
		];

		$sut = $this->make_instance();

		$sut->assertQueriesByStatementAndMethod('INSERT', 'Acme\MyPlugin', 'methodOne');
		$sut->assertQueriesCountByStatementAndMethod(2, 'SELECT', 'Acme\MyPlugin', 'methodTwo');
		$sut->assertNotQueriesByStatementAndMethod('UPDATE', 'Acme\MyPlugin', 'methodOne');

		$this->expectException('PHPUnit_Framework_AssertionFailedError');
		$sut->assertQueriesByStatementAndMethod('UPDATE', 'Acme\MyPlugin', 'methodOne');

		$this->expectException('PHPUnit_Framework_AssertionFailedError');
		$sut->assertQueriesCountByStatementAndMethod(3, 'UPDATE', 'Acme\MyPlugin', 'methodThree');

		$this->expectException('PHPUnit_Framework_AssertionFailedError');
		$sut->assertNotQueriesByStatementAndMethod('SELECT', 'Acme\MyPlugin', 'methodOne');
	}

	/**
	 * @test
	 * it should allow asserting queries by function and statement
	 */
	public function it_should_allow_asserting_queries_by_function_and_statement()
	{
		$this->wpdb->queries = [
			[
				'INSERT INTO ... (SELECT * ...)',
				'some ms timing',
				'a stack trace including functionOne'
			],
			[
				'SELECT ID FROM ... (SELECT...)',
				'some ms timing',
				'a stack trace including functionTwo'
			],
			[
				'SELECT * FROM ... INSERT',
				'some ms timing',
				'a stack trace including functionTwo'
			],
			[
				'UPDATE some_table... (SELECT',
				'some ms timing',
				'a stack trace including functionThree'
			],
		];

		$sut = $this->make_instance();

		$sut->assertQueriesByStatementAndFunction('INSERT', 'functionOne');
		$sut->assertQueriesCountByStatementAndFunction(2, 'SELECT', 'functionTwo');
		$sut->assertNotQueriesByStatementAndFunction('UPDATE', 'functionOne');

		$this->expectException('PHPUnit_Framework_AssertionFailedError');
		$sut->assertQueriesByStatementAndFunction('UPDATE', 'functionOne');

		$this->expectException('PHPUnit_Framework_AssertionFailedError');
		$sut->assertQueriesCountByStatementAndFunction(3, 'UPDATE', 'functionThree');

		$this->expectException('PHPUnit_Framework_AssertionFailedError');
		$sut->assertNotQueriesByStatementAndFunction('SELECT', 'functionOne');
	}

	/**
	 * @test
	 * it should allow asserting queries by action
	 */
	public function it_should_allow_asserting_queries_by_action()
	{
		$this->wpdb->queries = [
			[
				'INSERT INTO ... (SELECT * ...)',
				'some ms timing',
				"a stack trace including do_action('actionOne')"
			],
			[
				'SELECT ID FROM ... (SELECT...)',
				'some ms timing',
				"a stack trace including do_action('actionTwo')"
			],
			[
				'SELECT * FROM ... INSERT',
				'some ms timing',
				"a stack trace including do_action('actionTwo')"
			],
			[
				'UPDATE some_table... (SELECT',
				'some ms timing',
				"a stack trace including do_action('actionThree')"
			],
		];

		$sut = $this->make_instance();

		$sut->assertQueriesByAction('actionOne');
		$sut->assertQueriesCountByAction(2, 'actionTwo');
		$sut->assertNotQueriesByAction('someAction');

		$this->expectException('PHPUnit_Framework_AssertionFailedError');
		$sut->assertQueriesByAction('actionFour');

		$this->expectException('PHPUnit_Framework_AssertionFailedError');
		$sut->assertQueriesCountByAction(3, 'actionTwo');

		$this->expectException('PHPUnit_Framework_AssertionFailedError');
		$sut->assertNotQueriesByAction('actionTwo');
	}

	/**
	 * @test
	 * it should allow asserting queries by action and statement
	 */
	public function it_should_allow_asserting_queries_by_action_and_statement()
	{
		$this->wpdb->queries = [
			[
				'INSERT INTO ... (SELECT * ...)',
				'some ms timing',
				"a stack trace including do_action('actionOne')"
			],
			[
				'SELECT ID FROM ... (SELECT...)',
				'some ms timing',
				"a stack trace including do_action('actionTwo')"
			],
			[
				'SELECT * FROM ... INSERT',
				'some ms timing',
				"a stack trace including do_action('actionTwo')"
			],
			[
				'UPDATE some_table... (SELECT',
				'some ms timing',
				"a stack trace including do_action('actionThree')"
			],
		];

		$sut = $this->make_instance();

		$sut->assertQueriesByStatementAndAction('INSERT', 'actionOne');
		$sut->assertQueriesCountByStatementAndAction(2, 'SELECT', 'actionTwo');
		$sut->assertNotQueriesByStatementAndAction('UPDATE', 'actionOne');

		$this->expectException('PHPUnit_Framework_AssertionFailedError');
		$sut->assertQueriesByStatementAndAction('UPDATE', 'actionOne');

		$this->expectException('PHPUnit_Framework_AssertionFailedError');
		$sut->assertQueriesCountByStatementAndAction(3, 'UPDATE', 'actionThree');

		$this->expectException('PHPUnit_Framework_AssertionFailedError');
		$sut->assertNotQueriesByStatementAndAction('SELECT', 'actionOne');
	}

	/**
	 * @test
	 * it should allow asserting queries by filter
	 */
	public function it_should_allow_asserting_queries_by_filter()
	{
		$this->wpdb->queries = [
			[
				'INSERT INTO ... (SELECT * ...)',
				'some ms timing',
				"a stack trace including apply_filters('filterOne')"
			],
			[
				'SELECT ID FROM ... (SELECT...)',
				'some ms timing',
				"a stack trace including apply_filters('filterTwo')"
			],
			[
				'SELECT * FROM ... INSERT',
				'some ms timing',
				"a stack trace including apply_filters('filterTwo')"
			],
			[
				'UPDATE some_table... (SELECT',
				'some ms timing',
				"a stack trace including apply_filters('filterThree')"
			],
		];

		$sut = $this->make_instance();

		$sut->assertQueriesByFilter('filterOne');
		$sut->assertQueriesCountByFilter(2, 'filterTwo');
		$sut->assertNotQueriesByFilter('someFilter');

		$this->expectException('PHPUnit_Framework_AssertionFailedError');
		$sut->assertQueriesByFilter('filterFour');

		$this->expectException('PHPUnit_Framework_AssertionFailedError');
		$sut->assertQueriesCountByFilter(3, 'filterTwo');

		$this->expectException('PHPUnit_Framework_AssertionFailedError');
		$sut->assertNotQueriesByFilter('filterTwo');
	}

	/**
	 * @test
	 * it should allow asserting queries by filter and statement
	 */
	public function it_should_allow_asserting_queries_by_filter_and_statement()
	{
		$this->wpdb->queries = [
			[
				'INSERT INTO ... (SELECT * ...)',
				'some ms timing',
				"a stack trace including apply_filters('filterOne')"
			],
			[
				'SELECT ID FROM ... (SELECT...)',
				'some ms timing',
				"a stack trace including apply_filters('filterTwo')"
			],
			[
				'SELECT * FROM ... INSERT',
				'some ms timing',
				"a stack trace including apply_filters('filterTwo')"
			],
			[
				'UPDATE some_table... (SELECT',
				'some ms timing',
				"a stack trace including apply_filters('filterThree')"
			],
		];

		$sut = $this->make_instance();

		$sut->assertQueriesByStatementAndFilter('INSERT', 'filterOne');
		$sut->assertQueriesCountByStatementAndFilter(2, 'SELECT', 'filterTwo');
		$sut->assertNotQueriesByStatementAndFilter('UPDATE', 'filterOne');

		$this->expectException('PHPUnit_Framework_AssertionFailedError');
		$sut->assertQueriesByStatementAndFilter('UPDATE', 'filterOne');

		$this->expectException('PHPUnit_Framework_AssertionFailedError');
		$sut->assertQueriesCountByStatementAndFilter(3, 'UPDATE', 'filterThree');

		$this->expectException('PHPUnit_Framework_AssertionFailedError');
		$sut->assertNotQueriesByStatementAndFilter('SELECT', 'filterOne');
	}

	/**
	 * @test
	 * it should allow using regexes when asserting queries by statement
	 */
	public function it_should_allow_using_regexes_when_asserting_queries_by_statement()
	{
		$this->wpdb->queries = [
			[
				"SELECT * FROM wp_posts p JOIN wp_postmeta pm ON p.ID = pm.post_id WHERE p.post_type = 'some_type' AND pm.meta_key = 'some_key'",
				'some ms timing',
				'a stack trace including Acme\MyPlugin->someMethod'
			],
			[
				'SELECT ID FROM ... (SELECT...)',
				'some ms timing',
				'a stack trace including Acme\MyPlugin->someMethod'
			],
			[
				'SELECT * FROM ... INSERT',
				'some ms timing',
				'a stack trace including Acme\MyPlugin->someMethod'
			],
			[
				'UPDATE some_table... (SELECT',
				'some ms timing',
				'a stack trace including Acme\MyPlugin->someMethod'
			],
		];

		$sut = $this->make_instance();

		$sut->assertQueriesCountByStatement(1, "/SELECT .* AND pm.meta_key = 'some_key'/");
		$sut->assertQueriesCountByStatement(3, 'SELECT');

		$sut->assertNotQueriesByStatement("/SELECT .* FROM wp_postmeta/");
	}

	protected function _before()
	{
		$this->moduleContainer = $this->prophesize('Codeception\Lib\ModuleContainer');
		$this->moduleContainer->hasModule('WPLoader')->willReturn(true);
		$this->moduleContainer->hasModule('WPBootstrapper')->willReturn(true);
		$this->constants = $this->prophesize('tad\WPBrowser\Environment\Constants');
		$this->wpdb = (object)['queries' => []];
	}
}
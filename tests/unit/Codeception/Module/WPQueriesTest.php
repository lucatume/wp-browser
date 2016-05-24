<?php
namespace Codeception\Module;

require_once codecept_data_dir('classes/wpdb.php');

use Codeception\Exception\ModuleException;
use Codeception\Lib\ModuleContainer;
use tad\WPBrowser\Environment\Constants;

class WPQueriesTest extends \Codeception\TestCase\Test
{
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

    protected function _before()
    {
        $this->moduleContainer = $this->prophesize('Codeception\Lib\ModuleContainer');
        $wploader = $this->prophesize('Codeception\Module\WPLoader');
        $this->moduleContainer->getModule('WPLoader')->willReturn($wploader->reveal());
        $this->constants = $this->prophesize('tad\WPBrowser\Environment\Constants');
    }

    protected function _after()
    {
    }

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
     * @test
     * it should throw if WPLoader module is not loaded in module container
     */
    public function it_should_throw_if_wp_loader_module_is_not_loaded_in_module_container()
    {
        $this->expectException('Codeception\Exception\ModuleException');

        $this->moduleContainer->getModule('WPLoader')->willThrow(new ModuleException('foo', 'bar'));

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
        $sut = $this->make_instance();
        $wpdb = new \wpdb();
        $wpdb->queries = [
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

        $iterator = $sut->_getFilteredQueriesIterator($wpdb);

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
        global $wpdb;
        $wpdb = new \wpdb();
        $wpdb->queries = [
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
        global $wpdb;
        $wpdb = new \wpdb();
        $wpdb->queries = [
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
        global $wpdb;
        $wpdb = new \wpdb();
        $wpdb->queries = [
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
        global $wpdb;
        $wpdb = new \wpdb();
        $wpdb->queries = [
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
        global $wpdb;
        $wpdb = new \wpdb();
        $wpdb->queries = [
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
        global $wpdb;
        $wpdb = new \wpdb();
        $wpdb->queries = [
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
        global $wpdb;
        $wpdb = new \wpdb();
        $wpdb->queries = [
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
        $this->markTestIncomplete();
    }

    /**
     * @test
     * it should allow asserting queries by function
     */
    public function it_should_allow_asserting_queries_by_function()
    {
        $this->markTestIncomplete();
    }

    /**
     * @test
     * it should allow asserting queries by class method and statement
     */
    public function it_should_allow_asserting_queries_by_class_method_and_statement()
    {
        $this->markTestIncomplete();
    } 
    
    /**
     * @test
     * it should allow asserting queries by function and statement
     */
    public function it_should_allow_asserting_queries_by_function_and_statement()
    {
        $this->markTestIncomplete();
    }
    
    /**
     * @return WPQueries
     */
    private function make_instance()
    {
        return new WPQueries($this->moduleContainer->reveal(), $this->config, $this->constants->reveal());
    }
}
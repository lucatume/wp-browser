<?php

namespace lucatume\WPBrowser\Module;

use Codeception\Exception\ModuleException;
use Codeception\Lib\Di;
use Codeception\Lib\ModuleContainer;
use Codeception\Test\Unit;
use lucatume\WPBrowser\Tests\Traits\TmpFilesCleanup;
use lucatume\WPBrowser\Tests\Traits\UopzFunctions;
use lucatume\WPBrowser\Utils\Env;
use lucatume\WPBrowser\Utils\Filesystem as FS;
use lucatume\WPBrowser\Utils\Random;
use lucatume\WPBrowser\WordPress\Installation;
use PHPUnit\Framework\AssertionFailedError;
use wpdb;

class WPQueriesTest extends Unit
{
    use UopzFunctions;
    use TmpFilesCleanup;

    private static ?string $wpRootDir = null;
    protected $backupGlobals = false;
    private array $config = [];
    private ?wpdb $wpdb;

    private function makeInstance(): WPQueries
    {
        if (self::$wpRootDir === null) {
            self::$wpRootDir = FS::tmpDir('wpqueries_');
            Installation::scaffold(self::$wpRootDir, '6.1.1');
        }

        $moduleContainer = new ModuleContainer(new Di,
            [
                'modules' => [
                    'config' => [
                        WPLoader::class => [
                            'wpRootFolder' => self::$wpRootDir,
                            'dbName' => Random::dbName(),
                            'dbUser' => Env::get('WORDPRESS_DB_USER'),
                            'dbPassword' => Env::get('WORDPRESS_DB_PASSWORD'),
                            'dbHost' => Env::get('WORDPRESS_DB_HOST'),
                        ]
                    ]
                ]
            ]
        );
        $moduleContainer->create(WPLoader::class);
        $wpQueries = new WPQueries($moduleContainer, $this->config, $this->wpdb);

        return $wpQueries;
    }

    /**
     * It should throw if wpdb cannot be found at initialize time
     *
     * @test
     */
    public function should_throw_if_wpdb_not_cannot_be_found_at_initialize_time(): void
    {
        $this->expectException(ModuleException::class);

        $this->wpdb = null;
        unset($GLOBALS['wpdb']);
        $this->makeInstance()->_initialize();
    }

    /**
     * It should use the globally available instance of wpdb if none provided
     *
     * @test
     */
    public function should_use_the_globally_available_instance_of_wpdb_if_none_provided(): void
    {
        $globalWpdb = $this->getWpdb();
        $GLOBALS['wpdb'] = $globalWpdb;
        $this->wpdb = null;
        $wpQueries = $this->makeInstance();

        $this->assertSame($globalWpdb, $wpQueries->_getWpdb());
    }

    /**
     * @test
     * it should define the SAVEQUERIES constant if not defined already
     */
    public function it_should_define_the_savequeries_constant_if_not_defined_already(): void
    {
        $this->uopzUndefineConstant('SAVEQUERIES');
        $this->assertFalse(defined('SAVEQUERIES'));

        $this->wpdb = $this->getWpdb();
        $wpQueries = $this->makeInstance();
        $wpQueries->_initialize();

        $this->assertTrue(defined('SAVEQUERIES'));
    }

    /**
     * It should throw if SAVEQUERIES defined and false
     *
     * @test
     */
    public function should_throw_if_savequeries_defined_and_false(): void
    {
        $this->uopzRedefineConstant('SAVEQUERIES', false);
        $this->assertTrue(defined('SAVEQUERIES'));
        $this->assertFalse(SAVEQUERIES);

        $this->expectException(ModuleException::class);

        $this->wpdb = $this->getWpdb();
        $wpQueries = $this->makeInstance();
        $wpQueries->_initialize();
    }

    /**
     * @test
     * it should filter setUp and tearDown queries by default
     */
    public function it_should_filter_set_up_and_tear_down_queries_by_default(): void
    {
        $this->wpdb = $this->getWpdb();
        $this->wpdb->queries = [
            [
                'query 1',
                123, // ms timing.
                'trace including lucatume\WPBrowser\TestCase\WPTestCase->setUp',
                time(),
                ['custom' => 'data']
            ],
            [
                'query 2',
                34, // ms timing.
                'trace including lucatume\WPBrowser\TestCase\WPTestCase->setUp',
                time(),
            ],
            [
                'query 3',
                14, // ms timing.
                'trace calling Acme\MyPlugin->someMethod',
                time(),
                ['custom' => 'data']
            ],
            [
                'query 4',
                34, // ms timing.
                'trace including WP_UnitTest_Factory_For_Thing->create',
                time(),
            ],
            [
                'query 5',
                4, // ms timing.
                'trace including WP_UnitTest_Factory_For_Thing->create',
                time(),
            ],
            [
                'query 6',
                1343, // ms timing.
                'trace calling Acme\MyPlugin->someMethod',
                time(),
                ['custom' => 'data']
            ],
            [
                'query 7',
                234, // ms timing.
                'trace including lucatume\WPBrowser\TestCase\WPTestCase->tearDown',
                time(),
            ],
        ];

        $wpQueries = $this->makeInstance();

        $queries = $wpQueries->getQueries();
        $this->assertCount(2, $queries);
        $this->assertEquals('query 3', $queries[0][0]);
        $this->assertEquals('query 6', $queries[1][0]);
    }

    /**
     * @test
     * it should return false if asserting queries and there were no queries
     */
    public function it_should_return_false_if_asserting_queries_and_there_were_no_queries(): void
    {
        $this->wpdb = $this->getWpdb();
        $this->wpdb->queries = [
            [
                'first SQL statement',
                2,
                'a stack trace including lucatume\WPBrowser\TestCase\WPTestCase->setUp',
                time()
            ],
            [
                'second SQL statement',
                2,
                'a stack trace including lucatume\WPBrowser\TestCase\WPTestCase->setUp',
                time()
            ],
            [
                'fourth SQL statement',
                2,
                'a stack trace including WP_UnitTest_Factory_For_Thing->create',
                time()
            ],
            [
                'fifth SQL statement',
                2,
                'a stack trace including WP_UnitTest_Factory_For_Thing->create',
                time()
            ],
            [
                'seventh SQL statement',
                2,
                'a stack trace including lucatume\WPBrowser\TestCase\WPTestCase->tearDown',
                time()
            ],
        ];

        $this->expectException(AssertionFailedError::class);

        $sut = $this->makeInstance();
        $sut->assertQueries();
    }

    /**
     * @test
     * it should not fail if asserting queries and there were queries
     */
    public function it_should_not_fail_if_asserting_queries_and_there_were_queries(): void
    {
        $this->wpdb = $this->getWpdb();
        $this->wpdb->queries = [
            [
                'first SQL statement',
                2,
                'a stack trace including lucatume\WPBrowser\TestCase\WPTestCase->setUp',
                time()
            ],
            [
                'second SQL statement',
                2,
                'a stack trace including Acme\MyPlugin->someMethod',
                time()
            ],
            [
                'seventh SQL statement',
                2,
                'a stack trace including lucatume\WPBrowser\TestCase\WPTestCase->tearDown',
                time()
            ],
        ];

        $sut = $this->makeInstance();
        $sut->assertQueries();
    }

    /**
     * @test
     * it should fail if asserting no queries but queries were made
     */
    public function it_should_fail_if_asserting_no_queries_but_queries_were_made(): void
    {
        $this->wpdb = $this->getWpdb();
        $this->wpdb->queries = [
            [
                'first SQL statement',
                2,
                'a stack trace including lucatume\WPBrowser\TestCase\WPTestCase->setUp',
                time()
            ],
            [
                'second SQL statement',
                2,
                'a stack trace including Acme\MyPlugin->someMethod',
                time()
            ],
            [
                'seventh SQL statement',
                2,
                'a stack trace including lucatume\WPBrowser\TestCase\WPTestCase->tearDown',
                time()
            ],
        ];

        $this->expectException(AssertionFailedError::class);

        $sut = $this->makeInstance();
        $sut->assertNotQueries();
    }

    /**
     * @test
     * it should succeed if asserting no queries and no queries were made
     */
    public function it_should_succeed_if_asserting_no_queries_and_no_queries_were_made(): void
    {
        $this->wpdb = $this->getWpdb();
        $this->wpdb->queries = [
            [
                'first SQL statement',
                2,
                'a stack trace including lucatume\WPBrowser\TestCase\WPTestCase->setUp',
                time()
            ],
            [
                'seventh SQL statement',
                2,
                'a stack trace including lucatume\WPBrowser\TestCase\WPTestCase->tearDown',
                time()
            ],
        ];

        $sut = $this->makeInstance();
        $sut->assertNotQueries();
    }

    /**
     * @test
     * it should allow counting the queries
     */
    public function it_should_allow_counting_the_queries(): void
    {
        $this->wpdb = $this->getWpdb();
        $this->wpdb->queries = [
            [
                'SQL statement',
                2,
                'a stack trace including lucatume\WPBrowser\TestCase\WPTestCase->setUp',
                time()
            ],
            [
                'SQL statement',
                2,
                'a stack trace including Acme\MyPlugin->someMethod',
                time()
            ],
            [
                'SQL statement',
                2,
                'a stack trace including Acme\MyPlugin->someMethod',
                time()
            ],
            [
                'SQL statement',
                2,
                'a stack trace including lucatume\WPBrowser\TestCase\WPTestCase->tearDown',
                time()
            ],
        ];

        $sut = $this->makeInstance();
        $sut->assertCountQueries(2);
    }

    /**
     * @test
     * it should fail if asserting wrong queries count
     */
    public function it_should_fail_if_asserting_wrong_queries_count(): void
    {
        $this->wpdb = $this->getWpdb();
        $this->wpdb->queries = [
            [
                'SQL statement',
                2,
                'a stack trace including lucatume\WPBrowser\TestCase\WPTestCase->setUp',
                time()
            ],
            [
                'SQL statement',
                2,
                'a stack trace including Acme\MyPlugin->someMethod',
                time()
            ],
            [
                'SQL statement',
                2,
                'a stack trace including Acme\MyPlugin->someMethod',
                time()
            ],
            [
                'SQL statement',
                2,
                'a stack trace including lucatume\WPBrowser\TestCase\WPTestCase->tearDown',
                time()
            ],
        ];

        $this->expectException(AssertionFailedError::class);

        $sut = $this->makeInstance();
        $sut->assertCountQueries(1);
    }

    /**
     * @test
     * it should allow asserting queries count by statement
     */
    public function it_should_allow_asserting_queries_by_statement(): void
    {
        $this->wpdb = $this->getWpdb();
        $this->wpdb->queries = [
            [
                'INSERT INTO ... (SELECT * ...)',
                2,
                'a stack trace including Acme\MyPlugin->someMethod',
                time()
            ],
            [
                'SELECT ID FROM ... (SELECT...)',
                2,
                'a stack trace including Acme\MyPlugin->someMethod',
                time()
            ],
            [
                'SELECT * FROM ... INSERT',
                2,
                'a stack trace including Acme\MyPlugin->someMethod',
                time()
            ],
            [
                'UPDATE some_table... (SELECT',
                2,
                'a stack trace including Acme\MyPlugin->someMethod',
                time()
            ],
        ];

        $sut = $this->makeInstance();

        $sut->assertQueriesByStatement('SELECT');
        $sut->assertQueriesCountByStatement(2, 'SELECT');

        $this->expectException(AssertionFailedError::class);
        $sut->assertQueriesByStatement('DELETE');
        $sut->assertNotQueriesByStatement('DELETE');

        $this->expectException(AssertionFailedError::class);
        $sut->assertQueriesCountByStatement(1, 'SELECT');

        $this->expectException(AssertionFailedError::class);
        $sut->assertQueriesCountByStatement(3, 'SELECT');

        $sut->assertQueriesByStatement('UPDATE');
        $sut->assertQueriesCountByStatement(1, 'UPDATE');

        $this->expectException(AssertionFailedError::class);
        $sut->assertQueriesCountByStatement(2, 'UPDATE');
    }

    /**
     * @test
     * it should allow asserting queries by class method
     */
    public function it_should_allow_asserting_queries_by_class_method(): void
    {
        $this->wpdb = $this->getWpdb();
        $this->wpdb->queries = [
            [
                'INSERT INTO ... (SELECT * ...)',
                2,
                'a stack trace including Acme\MyPlugin->methodOne',
                time()
            ],
            [
                'SELECT ID FROM ... (SELECT...)',
                2,
                'a stack trace including Acme\MyPlugin->methodTwo',
                time()
            ],
            [
                'SELECT * FROM ... INSERT',
                2,
                'a stack trace including Acme\MyPlugin->methodTwo',
                time()
            ],
            [
                'UPDATE some_table... (SELECT',
                2,
                'a stack trace including Acme\MyPlugin->methodThree',
                time()
            ],
        ];

        $sut = $this->makeInstance();

        $sut->assertQueriesByMethod('Acme\MyPlugin', 'methodOne');
        $sut->assertQueriesByMethod('\Acme\MyPlugin', 'methodOne');
        $sut->assertQueriesCountByMethod(2, 'Acme\MyPlugin', 'methodTwo');
        $sut->assertNotQueriesByMethod('Acme\MyPlugin', 'someMethod');

        $this->expectException(AssertionFailedError::class);
        $sut->assertQueriesByMethod('Acme\MyPlugin', 'methodFour');

        $this->expectException(AssertionFailedError::class);
        $sut->assertQueriesByMethod('\Acme\MyPlugin', 'methodFour');

        $this->expectException(AssertionFailedError::class);
        $sut->assertQueriesCountByMethod(3, 'Acme\MyPlugin', 'methodTwo');

        $this->expectException(AssertionFailedError::class);
        $sut->assertNotQueriesByMethod('Acme\MyPlugin', 'methodTwo');
    }

    /**
     * @test
     * it should allow asserting queries by function
     */
    public function it_should_allow_asserting_queries_by_function(): void
    {
        $this->wpdb = $this->getWpdb();
        $this->wpdb->queries = [
            [
                'INSERT INTO ... (SELECT * ...)',
                2,
                'a stack trace including functionOne',
                time()
            ],
            [
                'SELECT ID FROM ... (SELECT...)',
                2,
                'a stack trace including functionTwo',
                time()
            ],
            [
                'SELECT * FROM ... INSERT',
                2,
                'a stack trace including functionTwo',
                time()
            ],
            [
                'UPDATE some_table... (SELECT',
                2,
                'a stack trace including functionThree',
                time()
            ],
        ];

        $sut = $this->makeInstance();

        $sut->assertQueriesByFunction('functionOne');
        $sut->assertQueriesCountByFunction(2, 'functionTwo');
        $sut->assertNotQueriesByFunction('someFunction');

        $this->expectException(AssertionFailedError::class);
        $sut->assertQueriesByFunction('functionFour');

        $this->expectException(AssertionFailedError::class);
        $sut->assertQueriesByFunction('functionFour');

        $this->expectException(AssertionFailedError::class);
        $sut->assertQueriesCountByFunction(3, 'functionTwo');

        $this->expectException(AssertionFailedError::class);
        $sut->assertNotQueriesByFunction('functionTwo');
    }

    /**
     * @test
     * it should allow asserting queries by class method and statement
     */
    public function it_should_allow_asserting_queries_by_class_method_and_statement(): void
    {
        $this->wpdb = $this->getWpdb();
        $this->wpdb->queries = [
            [
                'INSERT INTO ... (SELECT * ...)',
                2,
                'a stack trace including Acme\MyPlugin->methodOne',
                time()
            ],
            [
                'SELECT ID FROM ... (SELECT...)',
                2,
                'a stack trace including Acme\MyPlugin->methodTwo',
                time()
            ],
            [
                'SELECT * FROM ... INSERT',
                2,
                'a stack trace including Acme\MyPlugin->methodTwo',
                time()
            ],
            [
                'UPDATE some_table... (SELECT',
                2,
                'a stack trace including Acme\MyPlugin->methodThree',
                time()
            ],
        ];

        $sut = $this->makeInstance();

        $sut->assertQueriesByStatementAndMethod('INSERT', 'Acme\MyPlugin', 'methodOne');
        $sut->assertQueriesCountByStatementAndMethod(2, 'SELECT', 'Acme\MyPlugin', 'methodTwo');
        $sut->assertNotQueriesByStatementAndMethod('UPDATE', 'Acme\MyPlugin', 'methodOne');

        $this->expectException(AssertionFailedError::class);
        $sut->assertQueriesByStatementAndMethod('UPDATE', 'Acme\MyPlugin', 'methodOne');

        $this->expectException(AssertionFailedError::class);
        $sut->assertQueriesCountByStatementAndMethod(3, 'UPDATE', 'Acme\MyPlugin', 'methodThree');

        $this->expectException(AssertionFailedError::class);
        $sut->assertNotQueriesByStatementAndMethod('SELECT', 'Acme\MyPlugin', 'methodOne');
    }

    /**
     * @test
     * it should allow asserting queries by function and statement
     */
    public function it_should_allow_asserting_queries_by_function_and_statement(): void
    {
        $this->wpdb = $this->getWpdb();
        $this->wpdb->queries = [
            [
                'INSERT INTO ... (SELECT * ...)',
                2,
                'a stack trace including functionOne',
                time()
            ],
            [
                'SELECT ID FROM ... (SELECT...)',
                2,
                'a stack trace including functionTwo',
                time()
            ],
            [
                'SELECT * FROM ... INSERT',
                2,
                'a stack trace including functionTwo',
                time()
            ],
            [
                'UPDATE some_table... (SELECT',
                2,
                'a stack trace including functionThree',
                time()
            ],
        ];

        $sut = $this->makeInstance();

        $sut->assertQueriesByStatementAndFunction('INSERT', 'functionOne');
        $sut->assertQueriesCountByStatementAndFunction(2, 'SELECT', 'functionTwo');
        $sut->assertNotQueriesByStatementAndFunction('UPDATE', 'functionOne');

        $this->expectException(AssertionFailedError::class);
        $sut->assertQueriesByStatementAndFunction('UPDATE', 'functionOne');

        $this->expectException(AssertionFailedError::class);
        $sut->assertQueriesCountByStatementAndFunction(3, 'UPDATE', 'functionThree');

        $this->expectException(AssertionFailedError::class);
        $sut->assertNotQueriesByStatementAndFunction('SELECT', 'functionOne');
    }

    /**
     * @test
     * it should allow asserting queries by action
     */
    public function it_should_allow_asserting_queries_by_action(): void
    {
        $this->wpdb = $this->getWpdb();
        $this->wpdb->queries = [
            [
                'INSERT INTO ... (SELECT * ...)',
                2,
                "a stack trace including do_action('actionOne')",
                time()
            ],
            [
                'SELECT ID FROM ... (SELECT...)',
                2,
                "a stack trace including do_action('actionTwo')",
                time()
            ],
            [
                'SELECT * FROM ... INSERT',
                2,
                "a stack trace including do_action('actionTwo')",
                time()
            ],
            [
                'UPDATE some_table... (SELECT',
                2,
                "a stack trace including do_action('actionThree')",
                time()
            ],
        ];

        $sut = $this->makeInstance();

        $sut->assertQueriesByAction('actionOne');
        $sut->assertQueriesCountByAction(2, 'actionTwo');
        $sut->assertNotQueriesByAction('someAction');

        $this->expectException(AssertionFailedError::class);
        $sut->assertQueriesByAction('actionFour');

        $this->expectException(AssertionFailedError::class);
        $sut->assertQueriesCountByAction(3, 'actionTwo');

        $this->expectException(AssertionFailedError::class);
        $sut->assertNotQueriesByAction('actionTwo');
    }

    /**
     * @test
     * it should allow asserting queries by action and statement
     */
    public function it_should_allow_asserting_queries_by_action_and_statement(): void
    {
        $this->wpdb = $this->getWpdb();
        $this->wpdb->queries = [
            [
                'INSERT INTO ... (SELECT * ...)',
                2,
                "a stack trace including do_action('actionOne')",
                time()
            ],
            [
                'SELECT ID FROM ... (SELECT...)',
                2,
                "a stack trace including do_action('actionTwo')",
            time()
            ],
            [
                'SELECT * FROM ... INSERT',
                2,
                "a stack trace including do_action('actionTwo')",
            time()
            ],
            [
                'UPDATE some_table... (SELECT',
                2,
                "a stack trace including do_action('actionThree')",
            time()
            ],
        ];

        $sut = $this->makeInstance();

        $sut->assertQueriesByStatementAndAction('INSERT', 'actionOne');
        $sut->assertQueriesCountByStatementAndAction(2, 'SELECT', 'actionTwo');
        $sut->assertNotQueriesByStatementAndAction('UPDATE', 'actionOne');

        $this->expectException(AssertionFailedError::class);
        $sut->assertQueriesByStatementAndAction('UPDATE', 'actionOne');

        $this->expectException(AssertionFailedError::class);
        $sut->assertQueriesCountByStatementAndAction(3, 'UPDATE', 'actionThree');

        $this->expectException(AssertionFailedError::class);
        $sut->assertNotQueriesByStatementAndAction('SELECT', 'actionOne');
    }

    /**
     * @test
     * it should allow asserting queries by filter
     */
    public function it_should_allow_asserting_queries_by_filter(): void
    {
        $this->wpdb = $this->getWpdb();
        $this->wpdb->queries = [
            [
                'INSERT INTO ... (SELECT * ...)',
                2,
                "a stack trace including apply_filters('filterOne')",
            time()
            ],
            [
                'SELECT ID FROM ... (SELECT...)',
                2,
                "a stack trace including apply_filters('filterTwo')",
                time()
            ],
            [
                'SELECT * FROM ... INSERT',
                2,
                "a stack trace including apply_filters('filterTwo')",
            time()
            ],
            [
                'UPDATE some_table... (SELECT',
                2,
                "a stack trace including apply_filters('filterThree')",
                time()
            ],
        ];

        $sut = $this->makeInstance();

        $sut->assertQueriesByFilter('filterOne');
        $sut->assertQueriesCountByFilter(2, 'filterTwo');
        $sut->assertNotQueriesByFilter('someFilter');

        $this->expectException(AssertionFailedError::class);
        $sut->assertQueriesByFilter('filterFour');

        $this->expectException(AssertionFailedError::class);
        $sut->assertQueriesCountByFilter(3, 'filterTwo');

        $this->expectException(AssertionFailedError::class);
        $sut->assertNotQueriesByFilter('filterTwo');
    }

    /**
     * @test
     * it should allow asserting queries by filter and statement
     */
    public function it_should_allow_asserting_queries_by_filter_and_statement(): void
    {
        $this->wpdb = $this->getWpdb();
        $this->wpdb->queries = [
            [
                'INSERT INTO ... (SELECT * ...)',
                2,
                "a stack trace including apply_filters('filterOne')",
                time()
            ],
            [
                'SELECT ID FROM ... (SELECT...)',
                2,
                "a stack trace including apply_filters('filterTwo')",
            time()
            ],
            [
                'SELECT * FROM ... INSERT',
                2,
                "a stack trace including apply_filters('filterTwo')",
            time()
            ],
            [
                'UPDATE some_table... (SELECT',
                2,
                "a stack trace including apply_filters('filterThree')",
            time()
            ],
        ];

        $sut = $this->makeInstance();

        $sut->assertQueriesByStatementAndFilter('INSERT', 'filterOne');
        $sut->assertQueriesCountByStatementAndFilter(2, 'SELECT', 'filterTwo');
        $sut->assertNotQueriesByStatementAndFilter('UPDATE', 'filterOne');

        $this->expectException(AssertionFailedError::class);
        $sut->assertQueriesByStatementAndFilter('UPDATE', 'filterOne');

        $this->expectException(AssertionFailedError::class);
        $sut->assertQueriesCountByStatementAndFilter(3, 'UPDATE', 'filterThree');

        $this->expectException(AssertionFailedError::class);
        $sut->assertNotQueriesByStatementAndFilter('SELECT', 'filterOne');
    }

    /**
     * @test
     * it should allow using regexes when asserting queries by statement
     */
    public function it_should_allow_using_regexes_when_asserting_queries_by_statement(): void
    {
        $this->wpdb = $this->getWpdb();
        $this->wpdb->queries = [
            [
                "SELECT * FROM wp_posts p JOIN wp_postmeta pm ON p.ID = pm.post_id WHERE p.post_type = 'some_type' AND pm.meta_key = 'some_key'",
                2,
                'a stack trace including Acme\MyPlugin->someMethod',
                time()
            ],
            [
                'SELECT ID FROM ... (SELECT...)',
                2,
                'a stack trace including Acme\MyPlugin->someMethod',
                time()
            ],
            [
                'SELECT * FROM ... INSERT',
                2,
                'a stack trace including Acme\MyPlugin->someMethod',
                time()
            ],
            [
                'UPDATE some_table... (SELECT',
                2,
                'a stack trace including Acme\MyPlugin->someMethod',
                time()
            ],
        ];

        $sut = $this->makeInstance();

        $sut->assertQueriesCountByStatement(1, "/SELECT .* AND pm.meta_key = 'some_key'/");
        $sut->assertQueriesCountByStatement(3, 'SELECT');

        $sut->assertNotQueriesByStatement("/SELECT .* FROM wp_postmeta/");
    }

    /**
     * It should allow getting the count of the queries
     *
     * @test
     */
    public function should_allow_getting_the_count_of_the_queries(): void
    {
        $this->wpdb = $this->getWpdb();
        $this->wpdb->queries = [
            [
                "SELECT * FROM wp_posts p JOIN wp_postmeta pm ON p.ID = pm.post_id WHERE p.post_type = 'some_type' AND pm.meta_key = 'some_key'",
                2,
                'a stack trace including Acme\MyPlugin->someMethod',
                time()
            ],
            [
                'SELECT ID FROM ... (SELECT...)',
                2,
                'a stack trace including Acme\MyPlugin->someMethod',
            time()
            ],
            [
                'SELECT * FROM ... INSERT',
                2,
                'a stack trace including Acme\MyPlugin->someMethod',
                time()
            ],
            [
                'UPDATE some_table... (SELECT',
                2,
                'a stack trace including Acme\MyPlugin->someMethod',
                time()
            ],
        ];

        $sut = $this->makeInstance();

        $this->assertEquals(4, $sut->countQueries());
    }

    /**
     * It should allow getting the queries
     *
     * @test
     */
    public function should_allow_getting_the_queries(): void
    {
        $this->wpdb = $this->getWpdb();
        $this->wpdb->queries = [
            [
                "SELECT * FROM wp_posts p JOIN wp_postmeta pm ON p.ID = pm.post_id WHERE p.post_type = 'some_type' AND pm.meta_key = 'some_key'",
                2,
                'a stack trace including Acme\MyPlugin->someMethod',
                time()
            ],
            [
                'SELECT ID FROM ... (SELECT...)',
                2,
                'a stack trace including Acme\MyPlugin->someMethod',
            time()
            ],
            [
                'SELECT * FROM ... INSERT',
                2,
                'a stack trace including Acme\MyPlugin->someMethod',
                time()
            ],
            [
                'UPDATE some_table... (SELECT',
                2,
                'a stack trace including Acme\MyPlugin->someMethod',
                time()
            ],
        ];

        $sut = $this->makeInstance();

        $this->assertEquals($this->wpdb->queries, $sut->getQueries());
    }

    /**
     * ${CARET}
     *
     * @return wpdb
     * @since TBD
     *
     */
    protected function getWpdb(): wpdb
    {
        $user = Env::get('WORDPRESS_DB_USER');
        $password = Env::get('WORDPRESS_DB_PASSWORD');
        $name = Env::get('WORDPRESS_DB_NAME');
        $host = Env::get('WORDPRESS_DB_HOST');
        $wpdb = new wpdb($user, $password, $name, $host);
        return $wpdb;
    }
}

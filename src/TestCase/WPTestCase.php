<?php

namespace lucatume\WPBrowser\TestCase;

use Codeception\Test\Unit;
use lucatume\WPBrowser\Module\WPQueries;
use ReflectionException;
use ReflectionMethod;
use WP_UnitTestCase;

class WPTestCase extends Unit
{
    // Backup, and reset, globals between tests.
    protected $backupGlobals = true;

    // A list of globals that should not be backed up: they are handled by the Core test case.
    protected $backupGlobalsExcludeList = [
        'wpdb',
        'wp_query',
        'wp',
        'post',
        'id',
        'authordata',
        'currentday',
        'currentmonth',
        'page',
        'pages',
        'multipage',
        'more',
        'numpages',
        'current_screen',
        'taxnow',
        'typenow',
        'wp_actions',
        'wp_current_filter',
        'wp_filter',
        'wp_object_cache',
        'wp_meta_keys'
    ];

    // Backup, and reset, static class attributes between tests.
    protected $backupStaticAttributes = true;

    /**
     * @var array<string,WP_UnitTestCase>
     */
    private static array $coreTestCaseMap = [];

    private static function getCoreTestCase(): WP_UnitTestCase
    {
        if (isset(self::$coreTestCaseMap[static::class])) {
            return self::$coreTestCaseMap[static::class];
        }

        $coreTestCase = new class extends WP_UnitTestCase {
        };
        $coreTestCase->setCalledClass(static::class);
        self::$coreTestCaseMap[static::class] = $coreTestCase;

        return $coreTestCase;
    }

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        self::getCoreTestCase()->set_up_before_class();
    }


    protected function setUp(): void
    {
        parent::setUp();
        self::getCoreTestCase()->set_up();
    }

    protected function tearDown(): void
    {
        self::getCoreTestCase()->tear_down();
        parent::tearDown();
    }


    public static function tearDownAfterClass(): void
    {
        self::getCoreTestCase()->tear_down_after_class();
        parent::tearDownAfterClass();
    }

    protected function assertPostConditions(): void
    {
        parent::assertPostConditions();
        self::getCoreTestCase()->assert_post_conditions();
    }

    public function __destruct()
    {
        // Allow garbage collection of the core test case instance.
        unset(self::$coreTestCaseMap[static::class]);
    }

    /**
     * @param array<string,mixed> $arguments
     * @throws ReflectionException
     */
    public static function __callStatic(string $name, array $arguments): mixed
    {
        $coreTestCase = self::getCoreTestCase();
        $reflectionMethod = new ReflectionMethod($coreTestCase, $name);
        $reflectionMethod->setAccessible(true);
        return $reflectionMethod->invokeArgs(null, $arguments);
    }

    /**
     * @param array<string,mixed> $arguments
     * @throws ReflectionException
     */
    public function __call(string $name, array $arguments): mixed
    {
        $coreTestCase = self::getCoreTestCase();
        $reflectionMethod = new ReflectionMethod($coreTestCase, $name);
        $reflectionMethod->setAccessible(true);
        return $reflectionMethod->invokeArgs($coreTestCase, $arguments);
    }

    protected function queries(): WPQueries
    {
        /** @var WPQueries $wpQueries */
        $wpQueries = $this->getModule(WPQueries::class);
        return $wpQueries;
    }
}

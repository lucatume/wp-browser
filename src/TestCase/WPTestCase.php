<?php

namespace lucatume\WPBrowser\TestCase;

use Codeception\Test\Unit;
use lucatume\WPBrowser\Module\WPLoader;
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
        'wp_meta_keys',
        // WooCommerce.
        'woocommerce',
        // Additional globals.
        '_wp_registered_theme_features',
        // wp-browser
        '_wpTestsBackupGlobals',
        '_wpTestsBackupGlobalsExcludeList',
        '_wpTestsBackupStaticAttributes',
        '_wpTestsBackupStaticAttributesExcludeList'
    ];

    // Backup, and reset, static class attributes between tests.
    protected $backupStaticAttributes = true;

    // A list of static attributes that should not be backed up as they are wired to explode when doing so.
    protected $backupStaticAttributesExcludeList = [
        // WordPress
        'WP_Block_Type_Registry' => ['instance'],
        // wp-browser
        'lucatume\WPBrowser\Events\Dispatcher' => ['eventDispatcher'],
        self::class => ['coreTestCaseMap'],
        // Codeception
        'Codeception\Util\Annotation' => ['reflectedClasses'],
        // WooCommerce.
        'WooCommerce' => ['_instance'],
        'Automattic\WooCommerce\Internal\Admin\FeaturePlugin' => ['instance'],
        'Automattic\WooCommerce\RestApi\Server' => ['instance']
    ];

    /**
     * @param array<mixed> $data
     */
    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        global $_wpTestsBackupGlobals,
               $_wpTestsBackupGlobalsExcludeList,
               $_wpTestsBackupStaticAttributes,
               $_wpTestsBackupStaticAttributesExcludeList;

        $backupGlobalsReflectionProperty = new \ReflectionProperty($this, 'backupGlobals');
        $isDefinedInThis = $backupGlobalsReflectionProperty->getDeclaringClass()->getName() !== WPTestCase::class;
        if (!$isDefinedInThis && isset($_wpTestsBackupGlobals) && is_bool($_wpTestsBackupGlobals)) {
            $this->backupGlobals = $_wpTestsBackupGlobals;
        }

        $backupGlobalsExcludeListReflectionProperty = new \ReflectionProperty($this, 'backupGlobalsExcludeList');
        $isDefinedInThis = $backupGlobalsExcludeListReflectionProperty->getDeclaringClass()
                ->getName() !== WPTestCase::class;
        if (!$isDefinedInThis
            && isset($_wpTestsBackupGlobalsExcludeList)
            && is_array($_wpTestsBackupGlobalsExcludeList)
        ) {
            $this->backupGlobalsExcludeList = array_merge(
                $this->backupGlobalsExcludeList,
                $_wpTestsBackupGlobalsExcludeList
            );
        }

        $backupStaticAttributesReflectionProperty = new \ReflectionProperty($this, 'backupStaticAttributes');
        $isDefinedInThis = $backupStaticAttributesReflectionProperty->getDeclaringClass()
                ->getName() !== WPTestCase::class;
        if (!$isDefinedInThis && isset($_wpTestsBackupStaticAttributes) && is_bool($_wpTestsBackupStaticAttributes)) {
            $this->backupStaticAttributes = $_wpTestsBackupStaticAttributes;
        }

        $backupStaticAttributesExcludeListReflectionProperty = new \ReflectionProperty(
            $this,
            'backupStaticAttributesExcludeList'
        );
        $isDefinedInThis = $backupStaticAttributesExcludeListReflectionProperty->getDeclaringClass()
                ->getName() !== WPTestCase::class;
        if (!$isDefinedInThis
            && isset($_wpTestsBackupStaticAttributesExcludeList)
            && is_array($_wpTestsBackupStaticAttributesExcludeList)
        ) {
            $this->backupStaticAttributesExcludeList = array_merge_recursive(
                $this->backupStaticAttributesExcludeList,
                $_wpTestsBackupStaticAttributesExcludeList
            );
        }

        parent::__construct($name, $data, $dataName);
    }

    /**
     * @var array<string,mixed>
     */
    protected array $additionalGlobalsBackup = [];

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

    protected function backupAdditionalGlobals(): void
    {
        foreach ([
                '_wp_registered_theme_features'
            ] as $key
        ) {
            if (isset($GLOBALS[$key])) {
                $this->additionalGlobalsBackup = $GLOBALS[$key];
            }
        }
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->set_up(); //@phpstan-ignore-line magic __call
        $this->backupAdditionalGlobals();
    }

    protected function restoreAdditionalGlobals(): void
    {
        foreach ($this->additionalGlobalsBackup as $key => $value) {
            $GLOBALS[$key] = $value;
            unset($this->additionalGlobalsBackup[$key]);
        }
    }

    protected function tearDown(): void
    {
        $this->restoreAdditionalGlobals();
        $this->tear_down(); //@phpstan-ignore-line magic __call
        parent::tearDown();
    }


    public static function tearDownAfterClass(): void
    {
        static::tear_down_after_class();  //@phpstan-ignore-line magic __callStatic
        parent::tearDownAfterClass();
    }

    protected function assertPostConditions(): void
    {
        parent::assertPostConditions();
        static::assert_post_conditions(); //@phpstan-ignore-line magic __callStatic
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

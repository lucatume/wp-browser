<?php

namespace lucatume\WPBrowser\TestCase;

use AllowDynamicProperties;
use Codeception\Actor;
use Codeception\Exception\ModuleException;
use Codeception\Module;
use Codeception\Test\Unit;
use lucatume\WPBrowser\Module\WPQueries;
use ReflectionException;
use ReflectionMethod;
use ReflectionProperty;
use WP_UnitTestCase;

/**
 * @method static commit_transaction()
 * @method static delete_user($user_id)
 * @method static factory()
 * @method static flush_cache()
 * @method static forceTicket($ticket)
 * @method static get_called_class()
 * @method static set_up_before_class()
 * @method static tear_down_after_class()
 * @method static text_array_to_dataprovider($input)
 * @method static touch($file)
 * @method _create_temporary_tables($query)
 * @method _drop_temporary_tables($query)
 * @method _make_attachment($upload, $parent_post_id = 0)
 * @method assertDiscardWhitespace($expected, $actual, $message = '')
 * @method assertEqualFields($actual, $fields, $message = '')
 * @method assertEqualSets($expected, $actual, $message = '')
 * @method assertEqualSetsWithIndex($expected, $actual, $message = '')
 * @method assertEqualsIgnoreEOL($expected, $actual, $message = '')
 * @method assertIXRError($actual, $message = '')
 * @method assertNonEmptyMultidimensionalArray($actual, $message = '')
 * @method assertNotIXRError($actual, $message = '')
 * @method assertNotWPError($actual, $message = '')
 * @method assertQueryTrue($prop)
 * @method assertSameIgnoreEOL($expected, $actual, $message = '')
 * @method assertSameSets($expected, $actual, $message = '')
 * @method assertSameSetsWithIndex($expected, $actual, $message = '')
 * @method assertWPError($actual, $message = '')
 * @method assert_post_conditions()
 * @method clean_up_global_scope()
 * @method delete_folders($path)
 * @method deprecated_function_run($function_name, $replacement, $version, $message = '')
 * @method doing_it_wrong_run($function_name, $message, $version)
 * @method expectDeprecated()
 * @method expectedDeprecated()
 * @method files_in_dir($dir)
 * @method get_wp_die_handler($handler)
 * @method go_to($url)
 * @method knownPluginBug($ticket_id)
 * @method knownUTBug($ticket_id)
 * @method knownWPBug($ticket_id)
 * @method remove_added_uploads()
 * @method rmdir($path)
 * @method scan_user_uploads()
 * @method scandir($dir)
 * @method setExpectedDeprecated($deprecated)
 * @method setExpectedException($exception, $message = '', $code = NULL)
 * @method setExpectedIncorrectUsage($doing_it_wrong)
 * @method set_permalink_structure($structure = '')
 * @method set_up()
 * @method skipOnAutomatedBranches()
 * @method skipTestOnTimeout($response)
 * @method skipWithMultisite()
 * @method skipWithoutMultisite()
 * @method start_transaction()
 * @method tear_down()
 * @method temp_filename()
 * @method unlink($file)
 * @method unregister_all_meta_keys()
 * @method void setCalledClass(string $class)
 * @method wp_die_handler($message, $title, $args)
 */
#[AllowDynamicProperties]
class WPTestCase extends Unit
{
    use WPTestCasePHPUnitMethodsTrait;

    /**
     * @var string[]|null
     */
    private array|null $coreTestCaseProperties = null;

    /**
     * @var Actor
     */
    protected $tester;

    // Backup, and reset, globals between tests.
    protected $backupGlobals = false;

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
        '_wpBrowserWorkerClosure',
        '_wpTestsBackupGlobals',
        '_wpTestsBackupGlobalsExcludeList',
        '_wpTestsBackupStaticAttributes',
        '_wpTestsBackupStaticAttributesExcludeList'
    ];

    // Backup, and reset, static class attributes between tests.
    protected $backupStaticAttributes = false;

    // A list of static attributes that should not be backed up as they are wired to explode when doing so.
    protected $backupStaticAttributesExcludeList = [
        // WordPress
        'WP_Block_Type_Registry' => ['instance'],
        'WP_Block_Bindings_Registry' => ['instance'],
        // wp-browser
        'lucatume\WPBrowser\Events\Dispatcher' => ['eventDispatcher'],
        self::class => ['coreTestCaseMap'],
        // Codeception
        'Codeception\Util\Annotation' => ['reflectedClasses'],
        // WooCommerce.
        'WooCommerce' => ['_instance'],
        'Automattic\WooCommerce\Internal\Admin\FeaturePlugin' => ['instance'],
        'Automattic\WooCommerce\RestApi\Server' => ['instance'],
        'WC_Payment_Gateways' => ['_instance'],
    ];

    private ?float $requestTimeFloat = null;
    private ?int $requestTime = null;

    /**
     * @param array<mixed> $data
     */
    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        global $_wpTestsBackupGlobals,
               $_wpTestsBackupGlobalsExcludeList,
               $_wpTestsBackupStaticAttributes,
               $_wpTestsBackupStaticAttributesExcludeList;

        $backupGlobalsReflectionProperty = new ReflectionProperty($this, 'backupGlobals');
        $backupGlobalsReflectionProperty->setAccessible(true);
        $isDefinedInThis = $backupGlobalsReflectionProperty->getDeclaringClass()->getName() !== WPTestCase::class;
        if (!$isDefinedInThis && isset($_wpTestsBackupGlobals) && is_bool($_wpTestsBackupGlobals)) {
            $this->backupGlobals = $_wpTestsBackupGlobals;
        }

        if (property_exists($this, 'backupGlobalsExcludeList')) {
            $backupGlobalsExcludeListReflectionProperty = new ReflectionProperty($this, 'backupGlobalsExcludeList');
        } else {
            // Older versions of PHPUnit.
            $backupGlobalsExcludeListReflectionProperty = new ReflectionProperty($this, 'backupGlobalsBlacklist');
        }
        $backupGlobalsExcludeListReflectionProperty->setAccessible(true);
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

        $backupStaticAttributesReflectionProperty = new ReflectionProperty($this, 'backupStaticAttributes');
        $backupStaticAttributesReflectionProperty->setAccessible(true);
        $isDefinedInThis = $backupStaticAttributesReflectionProperty->getDeclaringClass()
                ->getName() !== WPTestCase::class;
        if (!$isDefinedInThis && isset($_wpTestsBackupStaticAttributes) && is_bool($_wpTestsBackupStaticAttributes)) {
            $this->backupStaticAttributes = $_wpTestsBackupStaticAttributes;
        }

        if (property_exists($this, 'backupStaticAttributesExcludeList')) {
            $backupStaticAttributesExcludeListReflectionProperty = new ReflectionProperty(
                $this,
                'backupStaticAttributesExcludeList'
            );
        } else {
            // Older versions of PHPUnit.
            $backupStaticAttributesExcludeListReflectionProperty = new ReflectionProperty(
                $this,
                'backupStaticAttributesBlacklist'
            );
        }
        $backupStaticAttributesExcludeListReflectionProperty->setAccessible(true);
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
            use WPUnitTestCasePolyfillsTrait;
        };
        $coreTestCase->setCalledClass(static::class);
        self::$coreTestCaseMap[static::class] = $coreTestCase;

        return $coreTestCase;
    }

    protected function backupAdditionalGlobals(): void
    {
        if (isset($GLOBALS['_wp_registered_theme_features'])) {
            $this->additionalGlobalsBackup = $GLOBALS['_wp_registered_theme_features'];
        }
        if (isset($_SERVER['REQUEST_TIME_FLOAT'])) {
            $this->requestTimeFloat = $_SERVER['REQUEST_TIME_FLOAT'];
        }
        if (isset($_SERVER['REQUEST_TIME'])) {
            $this->requestTime = $_SERVER['REQUEST_TIME'];
        }
    }

    protected function restoreAdditionalGlobals(): void
    {
        foreach ($this->additionalGlobalsBackup as $key => $value) {
            $GLOBALS[$key] = $value;
            unset($this->additionalGlobalsBackup[$key]);
        }
        if (isset($this->requestTimeFloat)) {
            $_SERVER['REQUEST_TIME_FLOAT'] = $this->requestTimeFloat;
        }
        if (isset($this->requestTime)) {
            $_SERVER['REQUEST_TIME'] = $this->requestTime;
        }
    }

    protected function assertPostConditions(): void
    {
        parent::assertPostConditions();
        static::assert_post_conditions();
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
        $name = match ($name) {
            '_setUpBeforeClass' => 'setUpBeforeClass',
            '_tearDownAfterClass' => 'tearDownAfterClass',
            default => $name
        };
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

    /**
     * @throws ModuleException If the WPQueries module is not available under any name.
     */
    protected function queries(): WPQueries
    {
        /** @var array<string,Module> $modules */
        $modules = $this->getMetadata()->getCurrent('modules');
        $module  = isset($modules['WPQueries']) ? 'WPQueries' : WPQueries::class;
        /** @var WPQueries $wpQueries */
        $wpQueries = $this->getModule($module);

        return $wpQueries;
    }

    private function isCoreTestCaseProperty(string $name): bool
    {
        if ($this->coreTestCaseProperties === null) {
            $this->coreTestCaseProperties = array_map(
                static fn(ReflectionProperty $p) => $p->getName(),
                (new \ReflectionClass(self::getCoreTestCase()))->getProperties()
            );
        }

        return in_array($name, $this->coreTestCaseProperties, true);
    }

    /**
     * @throws ReflectionException
     */
    public function __get(string $name): mixed
    {
        if (!$this->isCoreTestCaseProperty($name)) {
            return $this->{$name} ?? null;
        }

        $coreTestCase = self::getCoreTestCase();
        $reflectionProperty = new ReflectionProperty($coreTestCase, $name);
        $reflectionProperty->setAccessible(true);
        $value = $reflectionProperty->getValue($coreTestCase);

//        if (is_array($value)) {
//            return new ArrayReflectionPropertyAccessor($reflectionProperty, $coreTestCase);
//        }

        return $value;
    }

    /**
     * @throws ReflectionException
     */
    public function __set(string $name, mixed $value): void
    {
        if (!$this->isCoreTestCaseProperty($name)) {
            // Just set a dynamic property on the test case.
            $this->{$name} = $value;
            return;
        }

        $coreTestCase = self::getCoreTestCase();
        $reflectionProperty = new ReflectionProperty($coreTestCase, $name);
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($coreTestCase, $value);
    }

    /**
     * @throws ReflectionException
     */
    public function __isset(string $name): bool
    {
        if (!$this->isCoreTestCaseProperty($name)) {
            return isset($this->{$name});
        }

        $coreTestCase = self::getCoreTestCase();
        $reflectionProperty = new ReflectionProperty($coreTestCase, $name);
        $reflectionProperty->setAccessible(true);
        return $reflectionProperty->isInitialized($coreTestCase);
    }
}

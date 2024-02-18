<?php

namespace lucatume\WPBrowser\TestCase;

use Codeception\Test\Unit;
use lucatume\WPBrowser\Module\WPLoader;
use lucatume\WPBrowser\Module\WPQueries;
use ReflectionException;
use ReflectionMethod;
use WP_UnitTestCase;

<<<<<<< Updated upstream
class WPTestCase extends Unit
{
    // Backup, and reset, globals between tests.
    protected $backupGlobals = true;

=======
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
class WPTestCase extends Unit
{
    use WPTestCasePHPUnitMethodsTrait;
    /**
     * @var string[]|null
     */
    private $coreTestCaseProperties = null;
    /**
     * @var Actor
     */
    protected $tester;
    // Backup, and reset, globals between tests.
    protected $backupGlobals = false;
    >>>>>>> Stashed changes
    // A list of globals that should not be backed up: they are handled by the Core test case.
    protected $backupGlobalsBlacklist = [
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
    <<<<<<< Updated upstream
    protected $backupStaticAttributes = true;

    =======
    protected $backupStaticAttributes = false;
    >>>>>>> Stashed changes
    // A list of static attributes that should not be backed up as they are wired to explode when doing so.
    protected $backupStaticAttributesBlacklist = [
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
        $backupGlobalsReflectionProperty->setAccessible(true);
        $isDefinedInThis = $backupGlobalsReflectionProperty->getDeclaringClass()->getName() !== WPTestCase::class;
        if (!$isDefinedInThis && isset($_wpTestsBackupGlobals) && is_bool($_wpTestsBackupGlobals)) {
            $this->backupGlobals = $_wpTestsBackupGlobals;
        }

        if (property_exists($this, 'backupGlobalsExcludeList')) {
            <<<<<<< Updated upstream
            $backupGlobalsExcludeListReflectionProperty = new \ReflectionProperty($this, 'backupGlobalsExcludeList');
            $backupGlobalsExcludeListReflectionProperty->setAccessible(true);
        } else {
            // Older versions of PHPUnit.
            $backupGlobalsExcludeListReflectionProperty = new \ReflectionProperty($this, 'backupGlobalsBlacklist');
            =======
            $backupGlobalsExcludeListReflectionProperty = new ReflectionProperty($this, 'backupGlobalsExcludeList');
            $backupGlobalsExcludeListReflectionProperty->setAccessible(true);
        } else {
            // Older versions of PHPUnit.
            $backupGlobalsExcludeListReflectionProperty = new ReflectionProperty($this, 'backupGlobalsBlacklist');
            >>>>>>> Stashed changes
            $backupGlobalsExcludeListReflectionProperty->setAccessible(true);
        }
        $backupGlobalsExcludeListReflectionProperty->setAccessible(true);
        $isDefinedInThis = $backupGlobalsExcludeListReflectionProperty->getDeclaringClass()
                ->getName() !== WPTestCase::class;
        if (!$isDefinedInThis
            && isset($_wpTestsBackupGlobalsExcludeList)
            && is_array($_wpTestsBackupGlobalsExcludeList)
        ) {
            $this->backupGlobalsBlacklist = array_merge(
                $this->backupGlobalsBlacklist,
                $_wpTestsBackupGlobalsExcludeList
            );
        }

        $backupStaticAttributesReflectionProperty = new \ReflectionProperty($this, 'backupStaticAttributes');
        $backupStaticAttributesReflectionProperty->setAccessible(true);
        $isDefinedInThis = $backupStaticAttributesReflectionProperty->getDeclaringClass()
                ->getName() !== WPTestCase::class;
        if (!$isDefinedInThis && isset($_wpTestsBackupStaticAttributes) && is_bool($_wpTestsBackupStaticAttributes)) {
            $this->backupStaticAttributes = $_wpTestsBackupStaticAttributes;
        }

        if (property_exists($this, 'backupStaticAttributesExcludeList')) {
            $backupStaticAttributesExcludeListReflectionProperty = new \ReflectionProperty(
                $this,
                'backupStaticAttributesExcludeList'
            );
            $backupStaticAttributesExcludeListReflectionProperty->setAccessible(true);
        } else {
            // Older versions of PHPUnit.
            $backupStaticAttributesExcludeListReflectionProperty = new \ReflectionProperty(
                $this,
                'backupStaticAttributesBlacklist'
            );
            $backupStaticAttributesExcludeListReflectionProperty->setAccessible(true);
        }
        $backupStaticAttributesExcludeListReflectionProperty->setAccessible(true);
        $isDefinedInThis = $backupStaticAttributesExcludeListReflectionProperty->getDeclaringClass()
                ->getName() !== WPTestCase::class;
        if (!$isDefinedInThis
            && isset($_wpTestsBackupStaticAttributesExcludeList)
            && is_array($_wpTestsBackupStaticAttributesExcludeList)
        ) {
            $this->backupStaticAttributesBlacklist = array_merge_recursive(
                $this->backupStaticAttributesBlacklist,
                $_wpTestsBackupStaticAttributesExcludeList
            );
        }

        parent::__construct($name, $data, $dataName);
    }
    /**
     * @var array<string,mixed>
     */
    protected $additionalGlobalsBackup = [];
    <<<<<<< Updated upstream

    =======
    >>>>>>> Stashed changes
    /**
     * @var array<string,WP_UnitTestCase>
     */
    private static $coreTestCaseMap = [];
    <<<<<<< Updated upstream

    =======
    >>>>>>> Stashed changes
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
    <<<<<<< Updated upstream

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        self::getCoreTestCase()->set_up_before_class();
    }

    =======
    >>>>>>> Stashed changes
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
    <<<<<<< Updated upstream

    protected function setUp(): void
    {
        parent::setUp();
        $this->set_up(); //@phpstan-ignore-line magic __call
        $this->backupAdditionalGlobals();
    }

    =======
    >>>>>>> Stashed changes
    protected function restoreAdditionalGlobals(): void
    {
        foreach ($this->additionalGlobalsBackup as $key => $value) {
            $GLOBALS[$key] = $value;
            unset($this->additionalGlobalsBackup[$key]);
        }
    }
    <<<<<<< Updated upstream

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

    =======
    >>>>>>> Stashed changes
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
     * @return mixed
     */
    public static function __callStatic(string $name, array $arguments)
    {
        <<<<<<< Updated upstream
        =======
        switch ($name) {
            case '_setUpBeforeClass':
                $name = 'setUpBeforeClass';
                break;
            case '_tearDownAfterClass':
                $name = 'tearDownAfterClass';
                break;
            default:
                $name = $name;
                break;
        }
        >>>>>>> Stashed changes
        $coreTestCase = self::getCoreTestCase();
        $reflectionMethod = new ReflectionMethod($coreTestCase, $name);
        $reflectionMethod->setAccessible(true);
        return $reflectionMethod->invokeArgs(null, $arguments);
    }
    /**
     * @param array<string,mixed> $arguments
     * @throws ReflectionException
     * @return mixed
     */
    public function __call(string $name, array $arguments)
    {
        $coreTestCase = self::getCoreTestCase();
        $reflectionMethod = new ReflectionMethod($coreTestCase, $name);
        $reflectionMethod->setAccessible(true);
        return $reflectionMethod->invokeArgs($coreTestCase, $arguments);
    }
    <<<<<<< Updated upstream

    =======
    /**
     * @throws ModuleException If the WPQueries module is not available under any name.
     */
    >>>>>>> Stashed changes
    protected function queries(): WPQueries
    {
        /** @var WPQueries $wpQueries */
        $wpQueries = $this->getModule(WPQueries::class);
        return $wpQueries;
    }
    <<<<<<< Updated upstream
    =======
    private function isCoreTestCaseProperty(string $name): bool
    {
        if ($this->coreTestCaseProperties === null) {
            $this->coreTestCaseProperties = array_map(
                static function (ReflectionProperty $p) {
                    return $p->getName();
                },
                (new \ReflectionClass(self::getCoreTestCase()))->getProperties()
            );
        }

        return in_array($name, $this->coreTestCaseProperties, true);
    }
    /**
     * @throws ReflectionException
     * @return mixed
     */
    public function __get(string $name)
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
     * @param mixed $value
     */
    public function __set(string $name, $value): void
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
    >>>>>>> Stashed changes
}

<?php

namespace lucatume\WPBrowser\Module;

use Codeception\Event\SuiteEvent;
use Codeception\Events;
use Codeception\Exception\ModuleConfigException;
use Codeception\Exception\ModuleException;
use Codeception\Lib\Di;
use Codeception\Lib\ModuleContainer;
use Codeception\Test\Unit;
use Codeception\Util\Debug;
use Exception;
use Generator;
use lucatume\WPBrowser\Events\Dispatcher;
use lucatume\WPBrowser\Module\WPLoader\FactoryStore;
use lucatume\WPBrowser\Tests\FSTemplates\BedrockProject;
use lucatume\WPBrowser\Tests\Traits\DatabaseAssertions;
use lucatume\WPBrowser\Tests\Traits\FastScaffold;
use lucatume\WPBrowser\Tests\Traits\LoopIsolation;
use lucatume\WPBrowser\Tests\Traits\MainInstallationAccess;
use lucatume\WPBrowser\Tests\Traits\PhaseTimer;
use lucatume\WPBrowser\Tests\Traits\TmpFilesCleanup;
use lucatume\WPBrowser\Utils\Env;
use lucatume\WPBrowser\Utils\Filesystem as FS;
use lucatume\WPBrowser\Utils\Random;
use lucatume\WPBrowser\WordPress\Assert as WPAssert;
use lucatume\WPBrowser\WordPress\Database\MysqlDatabase;
use lucatume\WPBrowser\WordPress\Database\SQLiteDatabase;
use lucatume\WPBrowser\WordPress\Installation;
use lucatume\WPBrowser\WordPress\InstallationException;
use lucatume\WPBrowser\WordPress\InstallationState\InstallationStateInterface;
use lucatume\WPBrowser\WordPress\InstallationState\Scaffolded;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestResult;
use PHPUnit\Runner\Version as PHPUnitVersion;
use PHPUnit\TextUI\Configuration\Registry as ConfigurationRegistry;
use stdClass;
use Symfony\Component\VarDumper\VarDumper;
use tad\Codeception\SnapshotAssertions\SnapshotAssertions;
use UnitTester;
use WP_Theme;
use lucatume\WPBrowser\Tests\Traits\WPLoaderSetup;

use const ABSPATH;
use const WP_DEBUG;

/**
 * @group isolated-1
 */
class WPLoaderThemeBackupTest extends \Codeception\Test\Unit
{
    use \tad\Codeception\SnapshotAssertions\SnapshotAssertions;
    use \lucatume\WPBrowser\Tests\Traits\DatabaseAssertions;
    use \lucatume\WPBrowser\Tests\Traits\LoopIsolation;
    use \lucatume\WPBrowser\Tests\Traits\TmpFilesCleanup;
    use \lucatume\WPBrowser\Tests\Traits\MainInstallationAccess;
    use \lucatume\WPBrowser\Tests\Traits\PhaseTimer;
    use \lucatume\WPBrowser\Tests\Traits\FastScaffold;
    use \lucatume\WPBrowser\Tests\Traits\WPLoaderSetup;

    protected $backupGlobals = false;

    /** @var \UnitTester */
    protected $tester;


    /**
     * It should correctly activate child theme in single installation
     *
     * @test
     * @group slow
     * @group requires-mysql-server
     */
    public function should_correctly_activate_child_theme_in_single_installation(): void
    {
        $wpRootDir = $this->phase('FS::tmpDir', fn() => FS::tmpDir('wploader_'));
        $dbName = Random::dbName();
        $dbHost = Env::get('WORDPRESS_DB_HOST');
        $dbUser = Env::get('WORDPRESS_DB_USER');
        $dbPassword = Env::get('WORDPRESS_DB_PASSWORD');
        $this->config = [
            'wpRootFolder' => $wpRootDir,
            'dbName' => $dbName,
            'dbHost' => $dbHost,
            'dbUser' => $dbUser,
            'dbPassword' => $dbPassword,
            'configFile' => [
                codecept_data_dir('files/test_file_001.php'),
                codecept_data_dir('files/test_file_002.php'),
            ],
            'plugins' => [
                'akismet/akismet.php',
                'hello-dolly/hello.php'
            ],
            'theme' => 'some-child-theme'
        ];
        if (PHP_VERSION >= 7.4) {
            // WooCommerce has a minimum PHP version of 7.4.0 required.
            $this->config['plugins'][] = 'woocommerce/woocommerce.php';
        }
        $db = $this->phase('MysqlDatabase::create', fn() => (new MysqlDatabase($dbName, $dbUser, $dbPassword, $dbHost))->create());
        $scaffolded = $this->phase('Installation::scaffold', fn() => $this->fastScaffold($wpRootDir, 'latest'));
        $configured = $this->phase('configure (mysql)', fn() => $scaffolded->configure($db));
        $installation = $this->phase('install (mysql)', fn() => $configured->install(
            'https://wp.local',
            'admin',
            'password',
            'admin@wp.local',
            'Test'
        ));
        $this->phase('copyOverContentFromTheMainInstallation', fn() => $this->copyOverContentFromTheMainInstallation($installation));
        // Create a twentytwenty-child theme.
        $this->phase('wp-cli scaffold child-theme', fn() => $installation->runWpCliCommandOrThrow([
            'scaffold',
            'child-theme',
            'some-child-theme',
            '--parent_theme=twentytwenty',
            '--theme_name=some-child-theme',
            '--force'
        ]));

        $wpLoader = $this->module();
        $isolationClosure = static function () use ($wpLoader, $wpRootDir) {
            $wpLoader->_initialize();

            Assert::assertEquals('twentytwenty', get_option('template'));
            Assert::assertEquals('some-child-theme', get_option('stylesheet'));

            return [
                'bootstrapOutput' => $wpLoader->_getBootstrapOutput(),
                'installationOutput' => $wpLoader->_getInstallationOutput(),
            ];
        };
        $installationOutput = $this->phase(
            'assertInIsolation (WPLoader init)',
            fn() => $this->assertInIsolation($isolationClosure)
        );
    }


    /**
     * It should correctly activate child theme in multisite installation
     *
     * @test
     * @group slow
     * @group requires-mysql-server
     */
    public function should_correctly_activate_child_theme_in_multisite_installation(): void
    {
        $wpRootDir = FS::tmpDir('wploader_');
        $dbName = Random::dbName();
        $dbHost = Env::get('WORDPRESS_DB_HOST');
        $dbUser = Env::get('WORDPRESS_DB_USER');
        $dbPassword = Env::get('WORDPRESS_DB_PASSWORD');
        $this->config = [
            'wpRootFolder' => $wpRootDir,
            'dbName' => $dbName,
            'dbHost' => $dbHost,
            'dbUser' => $dbUser,
            'dbPassword' => $dbPassword,
            'configFile' => [
                codecept_data_dir('files/test_file_001.php'),
                codecept_data_dir('files/test_file_002.php'),
            ],
            'plugins' => [
                'akismet/akismet.php',
                'hello-dolly/hello.php'
            ],
            'theme' => 'twentytwenty-child',
            'multisite' => true,
        ];
        if (PHP_VERSION >= 7.4) {
            // WooCommerce has a minimum PHP version of 7.4.0 required.
            $this->config['plugins'][] = 'woocommerce/woocommerce.php';
        }
        $db = (new MysqlDatabase($dbName, $dbUser, $dbPassword, $dbHost))->create();
        $installation = $this->fastScaffold($wpRootDir, 'latest')
            ->configure($db, InstallationStateInterface::MULTISITE_SUBFOLDER);
        $installation->install(
            'https://wp.local',
            'admin',
            'password',
            'admin@wp.local',
            'Test'
        );
        $this->copyOverContentFromTheMainInstallation($installation);
        // Create a twentytwenty-child theme.
        $installation->runWpCliCommandOrThrow([
            'scaffold',
            'child-theme',
            'twentytwenty-child',
            '--parent_theme=twentytwenty',
            '--theme_name=twentytwenty-child',
            '--force'
        ]);

        $wpLoader = $this->module();
        $installationOutput = $this->assertInIsolation(static function () use ($wpLoader, $wpRootDir) {
            $wpLoader->_initialize();

            Assert::assertEquals('twentytwenty', get_option('template'));
            Assert::assertEquals('twentytwenty-child', get_option('stylesheet'));

            return [
                'bootstrapOutput' => $wpLoader->_getBootstrapOutput(),
                'installationOutput' => $wpLoader->_getInstallationOutput(),
            ];
        });
    }


    /**
     * It should allow controlling the backup of global variables in the WPTestCase
     *
     * @test
     * @group backup-globals
     * @group slow
     * @group requires-mysql-server
     */
    public function should_allow_controlling_the_backup_of_global_variables_in_the_wp_test_case(): void
    {
        $wpRootDir = FS::tmpDir('wploader_');
        $installation = $this->fastScaffold($wpRootDir);
        $dbName = Random::dbName();
        $dbHost = Env::get('WORDPRESS_DB_HOST');
        $dbUser = Env::get('WORDPRESS_DB_USER');
        $dbPassword = Env::get('WORDPRESS_DB_PASSWORD');
        $db = new MysqlDatabase($dbName, $dbUser, $dbPassword, $dbHost, 'test_');
        $db->drop();
        $installation->configure($db);
        $installation->install(
            'https://wp.local',
            'admin',
            'password',
            'admin@wp.local',
            'Test'
        );
        $testcaseFile = codecept_data_dir('files/BackupControlTestCase.php');
        $overridingTestCaseFile = codecept_data_dir('files/BackupControlTestCaseOverridingTestCase.php');
        $serializedPhpunitConfiguration = (int)PHPUnitVersion::series() >= 10 ?
            serialize(ConfigurationRegistry::get())
            : null;

        // Set`WPLoader.backupGlobals` to `false`.
        $this->config = [
            'wpRootFolder' => $wpRootDir,
            'dbUrl' => $db->getDbUrl(),
            'backupGlobals' => false,
        ];
        $wpLoader = $this->module();

        $this->assertInIsolation(static function () use ($wpLoader, $testcaseFile, $serializedPhpunitConfiguration) {
            if ((int)PHPUnitVersion::series() >= 10) {
                $reflector = new \ReflectionClass(ConfigurationRegistry::class);
                $instanceProp = $reflector->getProperty('instance');
                PHP_VERSION_ID < 80100 && $instanceProp->setAccessible(true);
                $instanceProp->setValue(unserialize($serializedPhpunitConfiguration));
            }

            $wpLoader->_initialize();

            Assert::assertTrue(function_exists('do_action'));

            require_once $testcaseFile;

            $testCase = new \BackupControlTestCase('testBackupGlobalsIsFalse');
            if ((int)PHPUnitVersion::series() >= 10) {
                $testCase->run();
                $status = $testCase->status();
                Assert::assertTrue($status->isSuccess());
            } else {
                /** @var TestResult $result */
                $result = $testCase->run();
                Assert::assertTrue($result->wasSuccessful());
            }
        });

        // Set `WPLoader.backupGlobals` to `true`.
        $this->config = [
            'wpRootFolder' => $wpRootDir,
            'dbUrl' => $db->getDbUrl(),
            'backupGlobals' => true,
        ];
        $wpLoader = $this->module();

        $this->assertInIsolation(static function () use ($wpLoader, $testcaseFile, $serializedPhpunitConfiguration) {
            if ((int)PHPUnitVersion::series() >= 10) {
                $reflector = new \ReflectionClass(ConfigurationRegistry::class);
                $instanceProp = $reflector->getProperty('instance');
                PHP_VERSION_ID < 80100 && $instanceProp->setAccessible(true);
                $instanceProp->setValue(unserialize($serializedPhpunitConfiguration));
            }
            $wpLoader->_initialize();

            Assert::assertTrue(function_exists('do_action'));

            require_once $testcaseFile;

            $testCase = new \BackupControlTestCase('testBackupGlobalsIsTrue');
            if ((int)PHPUnitVersion::series() >= 10) {
                $testCase->run();
                $status = $testCase->status();
                Assert::assertTrue($status->isSuccess());
            } else {
                /** @var TestResult $result */
                $result = $testCase->run();
                Assert::assertTrue($result->wasSuccessful());
            }
        });

        // Do not set `WPLoader.backupGlobals`, but use the default value of `false`.
        $this->config = [
            'wpRootFolder' => $wpRootDir,
            'dbUrl' => $db->getDbUrl(),
        ];
        $wpLoader = $this->module();

        $this->assertInIsolation(static function () use ($wpLoader, $testcaseFile, $serializedPhpunitConfiguration) {
            if ((int)PHPUnitVersion::series() >= 10) {
                $reflector = new \ReflectionClass(ConfigurationRegistry::class);
                $instanceProp = $reflector->getProperty('instance');
                PHP_VERSION_ID < 80100 && $instanceProp->setAccessible(true);
                $instanceProp->setValue(unserialize($serializedPhpunitConfiguration));
            }
            $wpLoader->_initialize();

            Assert::assertTrue(function_exists('do_action'));

            require_once $testcaseFile;

            $testCase = new \BackupControlTestCase('testBackupGlobalsIsFalse');

            if ((int)PHPUnitVersion::series() >= 10) {
                $testCase->run();
                $status = $testCase->status();
                Assert::assertTrue($status->isSuccess());
            } else {
                /** @var TestResult $result */
                $result = $testCase->run();
                Assert::assertTrue($result->wasSuccessful());
            }
        });

        // Set `WPLoader.backupGlobals` to `true`, but use a use-case that sets it explicitly to `false`.
        $this->config = [
            'wpRootFolder' => $wpRootDir,
            'dbUrl' => $db->getDbUrl(),
            'backupGlobals' => true,
        ];
        $wpLoader = $this->module();

        $this->assertInIsolation(static function () use ($wpLoader, $overridingTestCaseFile, $serializedPhpunitConfiguration) {
            if ((int)PHPUnitVersion::series() >= 10) {
                $reflector = new \ReflectionClass(ConfigurationRegistry::class);
                $instanceProp = $reflector->getProperty('instance');
                PHP_VERSION_ID < 80100 && $instanceProp->setAccessible(true);
                $instanceProp->setValue(unserialize($serializedPhpunitConfiguration));
            }
            $wpLoader->_initialize();

            Assert::assertTrue(function_exists('do_action'));

            require_once $overridingTestCaseFile;

            $testCase = new \BackupControlTestCaseOverridingTestCase('testBackupGlobalsIsFalse');

            if ((int)PHPUnitVersion::series() >= 10) {
                $testCase->run();
                $status = $testCase->status();
                Assert::assertTrue($status->isSuccess());
            } else {
                /** @var TestResult $result */
                $result = $testCase->run();
                Assert::assertTrue($result->wasSuccessful());
            }
        });

        $this->config = [
            'wpRootFolder' => $wpRootDir,
            'dbUrl' => $db->getDbUrl(),
        ];
        $wpLoader = $this->module();

        // Test that globals defined before the test runs should not be backed up by default.
        $this->assertInIsolation(static function () use ($wpLoader, $testcaseFile, $serializedPhpunitConfiguration) {
            if ((int)PHPUnitVersion::series() >= 10) {
                $reflector = new \ReflectionClass(ConfigurationRegistry::class);
                $instanceProp = $reflector->getProperty('instance');
                PHP_VERSION_ID < 80100 && $instanceProp->setAccessible(true);
                $instanceProp->setValue(unserialize($serializedPhpunitConfiguration));
            }
            $wpLoader->_initialize();

            Assert::assertTrue(function_exists('do_action'));

            // Set the initial value of the global variable.
            global $_wpbrowser_test_global_var;
            $_wpbrowser_test_global_var = 'initial_value';

            require_once $testcaseFile;

            $testCase = new \BackupControlTestCase('testWillUpdateTheValueOfGlobalVar');

            if ((int)PHPUnitVersion::series() >= 10) {
                $testCase->run();
                $status = $testCase->status();
                Assert::assertTrue($status->isSuccess());
            } else {
                /** @var TestResult $result */
                $result = $testCase->run();
                Assert::assertTrue($result->wasSuccessful());
            }

            // Check that the value of the global variable has been updated.
            Assert::assertEquals('updated_value', $_wpbrowser_test_global_var);
        });

        $this->config = [
            'wpRootFolder' => $wpRootDir,
            'dbUrl' => $db->getDbUrl(),
            'backupGlobalsExcludeList' => ['_wpbrowser_test_global_var'],
        ];
        $wpLoader = $this->module();

        // Test that adding a global to the list of `backupGlobalsExcludeList` will not back it up.
        $this->assertInIsolation(static function () use ($wpLoader, $testcaseFile, $serializedPhpunitConfiguration) {
            if ((int)PHPUnitVersion::series() >= 10) {
                $reflector = new \ReflectionClass(ConfigurationRegistry::class);
                $instanceProp = $reflector->getProperty('instance');
                PHP_VERSION_ID < 80100 && $instanceProp->setAccessible(true);
                $instanceProp->setValue(unserialize($serializedPhpunitConfiguration));
            }
            $wpLoader->_initialize();

            Assert::assertTrue(function_exists('do_action'));

            // Set the initial value of the global variable.
            global $_wpbrowser_test_global_var;
            $_wpbrowser_test_global_var = 'initial_value';

            require_once $testcaseFile;

            $testCase = new \BackupControlTestCase('testWillUpdateTheValueOfGlobalVar');

            if ((int)PHPUnitVersion::series() >= 10) {
                $testCase->run();
                $status = $testCase->status();
                Assert::assertTrue($status->isSuccess());
            } else {
                /** @var TestResult $result */
                $result = $testCase->run();
                Assert::assertTrue($result->wasSuccessful());
            }

            // Check that the value of the global variable has been updated.
            Assert::assertEquals('updated_value', $_wpbrowser_test_global_var);
        });
    }


    /**
     * It should allow controlling the backup of static attributes in the WPTestCase
     *
     * @test
     * @group backup-globals
     * @group slow
     * @group requires-mysql-server
     */
    public function should_allow_controlling_the_backup_of_static_attributes_in_the_wp_test_case(): void
    {
        $wpRootDir = FS::tmpDir('wploader_');
        $installation = $this->fastScaffold($wpRootDir);
        $dbName = Random::dbName();
        $dbHost = Env::get('WORDPRESS_DB_HOST');
        $dbUser = Env::get('WORDPRESS_DB_USER');
        $dbPassword = Env::get('WORDPRESS_DB_PASSWORD');
        $db = new MysqlDatabase($dbName, $dbUser, $dbPassword, $dbHost, 'test_');
        $db->drop();
        $installation->configure($db);
        $installation->install(
            'https://wp.local',
            'admin',
            'password',
            'admin@wp.local',
            'Test'
        );
        $testcaseFile = codecept_data_dir('files/BackupControlTestCase.php');
        if ((int)PHPUnitVersion::series() >= 10) {
            $overridingTestCaseFile = codecept_data_dir('files/BackupControlTestCaseOverridingTestCasePHPUnit10.php');
        } else {
            $overridingTestCaseFile = codecept_data_dir('files/BackupControlTestCaseOverridingTestCase.php');
        }

        // Set`WPLoader.backupStaticAttributes` to `false`.
        $this->config = [
            'wpRootFolder' => $wpRootDir,
            'dbUrl' => $db->getDbUrl(),
            'backupStaticAttributes' => false,
        ];
        $wpLoader = $this->module();
        $serializedPhpunitConfiguration = (int)PHPUnitVersion::series() >= 10 ?
            serialize(ConfigurationRegistry::get())
            : null;

        $this->assertInIsolation(static function () use ($wpLoader, $testcaseFile, $serializedPhpunitConfiguration) {
            if ((int)PHPUnitVersion::series() >= 10) {
                $reflector = new \ReflectionClass(ConfigurationRegistry::class);
                $instanceProp = $reflector->getProperty('instance');
                PHP_VERSION_ID < 80100 && $instanceProp->setAccessible(true);
                $instanceProp->setValue(unserialize($serializedPhpunitConfiguration));
            }
            $wpLoader->_initialize();

            Assert::assertTrue(function_exists('do_action'));

            require_once $testcaseFile;

            $testCase = new \BackupControlTestCase('testWillAlterStoreStaticAttribute');

            if ((int)PHPUnitVersion::series() >= 10) {
                $testCase->run();
                $status = $testCase->status();
                Assert::assertTrue($status->isSuccess());
            } else {
                /** @var TestResult $result */
                $result = $testCase->run();
                Assert::assertTrue($result->wasSuccessful());
            }

            Assert::assertEquals('updated_value', \BackupControlTestCaseStore::$staticAttribute);
        });

        // Don't set`WPLoader.backupStaticAttributes`, it should be `false` by default.
        $this->config = [
            'wpRootFolder' => $wpRootDir,
            'dbUrl' => $db->getDbUrl()
        ];
        $wpLoader = $this->module();

        $this->assertInIsolation(static function () use ($wpLoader, $testcaseFile, $serializedPhpunitConfiguration) {
            if ((int)PHPUnitVersion::series() >= 10) {
                $reflector = new \ReflectionClass(ConfigurationRegistry::class);
                $instanceProp = $reflector->getProperty('instance');
                PHP_VERSION_ID < 80100 && $instanceProp->setAccessible(true);
                $instanceProp->setValue(unserialize($serializedPhpunitConfiguration));
            }
            $wpLoader->_initialize();

            Assert::assertTrue(function_exists('do_action'));

            require_once $testcaseFile;

            $testCase = new \BackupControlTestCase('testWillAlterStoreStaticAttribute');

            if ((int)PHPUnitVersion::series() >= 10) {
                $testCase->run();
                $status = $testCase->status();
                Assert::assertTrue($status->isSuccess());
            } else {
                /** @var TestResult $result */
                $result = $testCase->run();
                Assert::assertTrue($result->wasSuccessful());
            }

            Assert::assertEquals('updated_value', \BackupControlTestCaseStore::$staticAttribute);
        });

        // Set the value of `WPLoader.backupStaticAttributes` to `true`, but use a use-case that sets it explicitly to `false`.
        $this->config = [
            'wpRootFolder' => $wpRootDir,
            'dbUrl' => $db->getDbUrl(),
            'backupStaticAttributes' => true,
        ];
        $wpLoader = $this->module();

        $this->assertInIsolation(static function () use ($wpLoader, $overridingTestCaseFile, $serializedPhpunitConfiguration) {
            if ((int)PHPUnitVersion::series() >= 10) {
                $reflector = new \ReflectionClass(ConfigurationRegistry::class);
                $instanceProp = $reflector->getProperty('instance');
                PHP_VERSION_ID < 80100 && $instanceProp->setAccessible(true);
                $instanceProp->setValue(unserialize($serializedPhpunitConfiguration));
            }
            $wpLoader->_initialize();

            Assert::assertTrue(function_exists('do_action'));

            require_once $overridingTestCaseFile;

            $testCase = new \BackupControlTestCaseOverridingTestCase('testWillAlterStoreStaticAttribute');

            if ((int)PHPUnitVersion::series() >= 10) {
                $testCase->run();
                $status = $testCase->status();
                Assert::assertTrue($status->isSuccess());
            } else {
                /** @var TestResult $result */
                $result = $testCase->run();
                Assert::assertTrue($result->wasSuccessful());
            }

            Assert::assertEquals('updated_value', \BackupControlTestCaseOverridingStore::$staticAttribute);
        });

        // Set the value of the `WPLoader.backupStaticAttributesExcludeList` to not back up the static attribute.
        $this->config = [
            'wpRootFolder' => $wpRootDir,
            'dbUrl' => $db->getDbUrl(),
            'backupStaticAttributes' => true,
            'backupStaticAttributesExcludeList' => [
                \BackupControlTestCaseStore::class => ['staticAttribute', 'staticAttributeThree'],
                \BackupControlTestCaseStoreTwo::class => ['staticAttributeFour'],
            ]
        ];
        $wpLoader = $this->module();

        $this->assertInIsolation(
            static function () use ($wpLoader, $testcaseFile, $serializedPhpunitConfiguration) {
                if ((int)PHPUnitVersion::series() >= 10) {
                    $reflector = new \ReflectionClass(ConfigurationRegistry::class);
                    $instanceProp = $reflector->getProperty('instance');
                    PHP_VERSION_ID < 80100 && $instanceProp->setAccessible(true);
                    $instanceProp->setValue(unserialize($serializedPhpunitConfiguration));
                }
                $wpLoader->_initialize();

                Assert::assertTrue(function_exists('do_action'));

                require_once $testcaseFile;

                $testCase = new \BackupControlTestCase('testWillAlterStoreStaticAttribute');

                if ((int)PHPUnitVersion::series() >= 10) {
                    $testCase->run();
                    $status = $testCase->status();
                    Assert::assertTrue($status->isSuccess());
                } else {
                    /** @var TestResult $result */
                    $result = $testCase->run();
                    Assert::assertTrue($result->wasSuccessful());
                }

                Assert::assertEquals('updated_value', \BackupControlTestCaseStore::$staticAttribute);
                Assert::assertEquals('initial_value', \BackupControlTestCaseStore::$staticAttributeTwo);
                Assert::assertEquals('updated_value', \BackupControlTestCaseStore::$staticAttributeThree);
                Assert::assertEquals('initial_value', \BackupControlTestCaseStore::$staticAttributeFour);
                Assert::assertEquals('initial_value', \BackupControlTestCaseStoreTwo::$staticAttribute);
                Assert::assertEquals('initial_value', \BackupControlTestCaseStoreTwo::$staticAttributeTwo);
                Assert::assertEquals('initial_value', \BackupControlTestCaseStoreTwo::$staticAttributeThree);
                Assert::assertEquals('updated_value', \BackupControlTestCaseStoreTwo::$staticAttributeFour);
            }
        );
    }

}

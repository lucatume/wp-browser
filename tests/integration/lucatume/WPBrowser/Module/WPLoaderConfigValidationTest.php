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
class WPLoaderConfigValidationTest extends \Codeception\Test\Unit
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
     * It should throw if cannot connect to the database
     *
     * @test
     * @group fast
     */
    public function should_throw_if_cannot_connect_to_the_database(): void
    {
        $dbName = Random::dbName();
        $this->config = [
            'wpRootFolder' => FS::tmpDir('wploader_'),
            'dbName' => $dbName,
            'dbHost' => 'some-non-existing-db-host',
            'dbUser' => Env::get('WORDPRESS_DB_USER'),
            'dbPassword' => Env::get('WORDPRESS_DB_PASSWORD'),
        ];

        $this->expectException(ModuleConfigException::class);

        $this->module()->_initialize();
    }


    /**
     * It should throw if wpRootFolder is not valid
     *
     * @test
     * @group fast
     */
    public function should_throw_if_wp_root_folder_is_not_valid(): void
    {
        $dbName = Random::dbName();
        $this->config = [
            'wpRootFolder' => '/not/a/valid/path',
            'dbName' => $dbName,
            'dbHost' => Env::get('WORDPRESS_DB_HOST'),
            'dbUser' => Env::get('WORDPRESS_DB_USER'),
            'dbPassword' => Env::get('WORDPRESS_DB_PASSWORD'),
        ];

        $this->expectException(ModuleConfigException::class);

        $this->module()->_initialize();
    }


    /**
     * It should throw if configFile not found
     *
     * @test
     * @group slow
     */
    public function should_throw_if_config_file_not_found(): void
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
            'configFile' => __DIR__ . '/some-file-that-does-not-exist.php',
        ];
        $this->fastScaffold($wpRootDir, 'latest');

        $wpLoader = $this->module();

        $this->expectException(ModuleConfigException::class);

        $this->assertInIsolation(static function () use ($wpLoader, $wpRootDir) {
            $wpLoader->_initialize();
        });
    }


    /**
     * It should throw if configFiles do not exist
     *
     * @test
     * @group slow
     */
    public function should_throw_if_config_files_do_not_exist(): void
    {
        $wpRootDir = FS::tmpDir('wploader_');
        $this->config = [
            'wpRootFolder' => $wpRootDir,
            'dbName' => Random::dbName(),
            'dbHost' => Env::get('WORDPRESS_DB_HOST'),
            'dbUser' => Env::get('WORDPRESS_DB_USER'),
            'dbPassword' => Env::get('WORDPRESS_DB_PASSWORD'),
            'configFile' => __DIR__ . '/nonexistent.php'
        ];

        $wpLoader = $this->module();

        $this->assertInIsolation(static function () use ($wpLoader) {
            $captured = false;
            try {
                $wpLoader->_initialize();
            } catch (Exception $e) {
                Assert::assertInstanceOf(ModuleConfigException::class, $e);
                $captured = true;
            }
            Assert::assertTrue($captured);
        });

        $this->config = [
            'wpRootFolder' => $wpRootDir,
            'dbName' => Random::dbName(),
            'dbHost' => Env::get('WORDPRESS_DB_HOST'),
            'dbUser' => Env::get('WORDPRESS_DB_USER'),
            'dbPassword' => Env::get('WORDPRESS_DB_PASSWORD'),
            'configFile' => [
                codecept_data_dir('files/test_file_002.php'),
                __DIR__ . '/nonexistent.php'
            ]
        ];

        $wpLoader = $this->module();

        $this->expectException(ModuleConfigException::class);

        $this->assertInIsolation(static function () use ($wpLoader) {
            $wpLoader->_initialize();
        });
    }


    /**
     * It should throw if dbUrl not set and db credentials are not provided
     *
     * @test
     * @group slow
     */
    public function should_throw_if_db_url_not_set_and_db_credentials_are_not_provided(): void
    {
        $wpRootDir = FS::tmpDir('wploader_');
        $this->fastScaffold($wpRootDir);
        $this->config = ['wpRootFolder' => $wpRootDir];

        $this->expectException(ModuleConfigException::class);
        $message = "The `dbUrl` configuration parameter must be set or the `dbPassword`, `dbHost`, `dbName` and " .
            "`dbUser` parameters must be set.";
        $this->expectExceptionMessage($message);

        $this->module();
    }


    /**
     * It should throw if loadOnly and installation empty
     *
     * @test
     * @group slow
     */
    public function should_throw_if_load_only_and_installation_empty(): void
    {
        $wpRootDir = FS::tmpDir('wploader_');

        $this->config = [
            'wpRootFolder' => $wpRootDir,
            'dbName' => Random::dbName(),
            'dbHost' => Env::get('WORDPRESS_DB_HOST'),
            'dbUser' => Env::get('WORDPRESS_DB_USER'),
            'dbPassword' => Env::get('WORDPRESS_DB_PASSWORD'),
            'loadOnly' => true
        ];

        $wpLoader = $this->module();

        $this->expectException(ModuleException::class);

        $this->assertInIsolation(static function () use ($wpLoader) {
            $wpLoader->_initialize();
        });
    }


    /**
     * It should throw if loadOnly and installation scaffolded
     *
     * @test
     * @group slow
     */
    public function should_throw_if_load_only_and_installation_scaffolded(): void
    {
        $wpRootDir = FS::tmpDir('wploader_');
        $this->fastScaffold($wpRootDir, '6.1.1');

        $this->config = [
            'wpRootFolder' => $wpRootDir,
            'dbName' => Random::dbName(),
            'dbHost' => Env::get('WORDPRESS_DB_HOST'),
            'dbUser' => Env::get('WORDPRESS_DB_USER'),
            'dbPassword' => Env::get('WORDPRESS_DB_PASSWORD'),
            'loadOnly' => true
        ];

        $wpLoader = $this->module();

        $this->expectException(ModuleException::class);

        $this->assertInIsolation(static function () use ($wpLoader) {
            $wpLoader->_initialize();
        });
    }


    /**
     * It should throw if loadOnly and domain empty
     *
     * @test
     * @group slow
     * @group requires-mysql-server
     */
    public function should_throw_if_load_only_and_domain_empty(): void
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
            'loadOnly' => true,
            'domain' => ''
        ];
        $db = new MysqlDatabase($dbName, $dbUser, $dbPassword, $dbHost, 'wp_');
        $this->fastScaffold($wpRootDir, '6.1.1')
            ->configure($db);

        $this->expectException(ModuleConfigException::class);

        $wpLoader = $this->module();
        $wpLoader->_initialize();
    }


    /**
     * It should throw if loadOnly and WordPress not installed
     *
     * @test
     * @group slow
     * @group requires-mysql-server
     */
    public function should_throw_if_load_only_and_word_press_not_installed(): void
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
            'loadOnly' => true,
            'domain' => 'wordpress.test'
        ];
        $db = new MysqlDatabase($dbName, $dbUser, $dbPassword, $dbHost, 'wp_');
        $this->fastScaffold($wpRootDir, '6.1.1')
            ->configure($db);

        $wpLoader = $this->module();

        $this->expectException(InstallationException::class);
        $this->expectExceptionMessage(InstallationException::becauseWordPressIsNotInstalled()->getMessage());

        $this->assertInIsolation(static function () use ($wpLoader) {
            $wpLoader->_initialize();
            $wpLoader->_loadWordPress();
        });
    }


    /**
     * It should throw if specified dump file does not exist
     *
     * @test
     * @group slow
     */
    public function should_throw_if_specified_dump_file_does_not_exist(): void
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
            'dump' => 'not-really-existing.sql'
        ];
        $this->fastScaffold($wpRootDir);

        $this->expectException(ModuleConfigException::class);

        $wpLoader = $this->module();
        $wpLoader->_initialize();
    }


    /**
     * It should throw if any dump file specified does not exist
     *
     * @test
     * @group slow
     */
    public function should_throw_if_any_dump_file_specified_does_not_exist(): void
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
            'dump' => [
                codecept_data_dir('files/test-dump-001.sql'),
                codecept_data_dir('files/test-dump-002.sql'),
                'not-really-existing.sql',
            ]
        ];
        $this->fastScaffold($wpRootDir);

        $this->expectException(ModuleConfigException::class);

        $wpLoader = $this->module();
        $wpLoader->_initialize();
    }


    /**
     * It should rethrow on failure to load a dump file
     *
     * @test
     * @group slow
     */
    public function should_rethrow_on_failure_to_load_a_dump_file(): void
    {
        $wpRootDir = FS::tmpDir('wploader_');
        $dbName = Random::dbName();
        $dbHost = Env::get('WORDPRESS_DB_HOST');
        $dbUser = Env::get('WORDPRESS_DB_USER');
        $dbPassword = Env::get('WORDPRESS_DB_PASSWORD');
        $dumpFiles = [
            codecept_data_dir('files/test-dump-001.sql'),
            codecept_data_dir('files/test-dump-002.sql'),
        ];
        $this->config = [
            'wpRootFolder' => $wpRootDir,
            'dbName' => $dbName,
            'dbHost' => $dbHost,
            'dbUser' => $dbUser,
            'dbPassword' => $dbPassword,
            'dump' => $dumpFiles
        ];
        $this->fastScaffold($wpRootDir);

        $wpLoader = $this->module();

        $this->expectException(ModuleException::class);

        $this->assertInIsolation(static function () use ($wpLoader, $dumpFiles) {
            uopz_set_return('fopen', function (string $file, ...$args) use ($dumpFiles) {
                return in_array($file, $dumpFiles, true) ? false : fopen($file, ...$args);
            }, true);
            $wpLoader->_initialize();
        });
    }


    /**
     * It should throw module exception on error during bootstrap
     *
     * @test
     * @group slow
     */
    public function should_throw_module_exception_on_error_during_bootstrap(): void
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
            'dbPassword' => $dbPassword
        ];
        $this->fastScaffold($wpRootDir, 'latest');

        $this->expectException(ModuleException::class);
        $this->expectExceptionMessageRegExp('/WordPress bootstrap failed/');

        $wpLoader = $this->module();
        $this->assertInIsolation(static function () use ($wpLoader, $wpRootDir) {
            // This will cause an exit 1 during bootstrap.
            uopz_set_return('tests_get_phpunit_version', '5.0.0');
            $wpLoader->_initialize();
        });
    }


    /**
     * It should throw if there is an error while activating a plugin
     *
     * @test
     * @group slow
     */
    public function should_throw_if_there_is_an_error_while_activating_a_plugin(): void
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
                'some-plugin/some-plugin.php',
            ]
        ];
        $this->fastScaffold($wpRootDir, 'latest');

        $this->expectException(ModuleException::class);
        $this->expectExceptionMessage(
            'Failed to activate plugin some-plugin/some-plugin.php. Plugin file some-plugin/some-plugin.php does not exist.'
        );

        $wpLoader = $this->module();
        $this->assertInIsolation(static function () use ($wpLoader) {
            $wpLoader->_initialize();
        });
    }


    /**
     * It should throw if there is an error while activating a plugin in multisite
     *
     * @test
     * @group slow
     */
    public function should_throw_if_there_is_an_error_while_activating_a_plugin_in_multisite(): void
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
                'some-plugin/some-plugin.php',
            ],
            'multisite' => true,
        ];
        $this->fastScaffold($wpRootDir, 'latest');

        $this->expectException(ModuleException::class);
        $this->expectExceptionMessage(
            'Failed to activate plugin some-plugin/some-plugin.php. Plugin file some-plugin/some-plugin.php does not exist.'
        );

        $wpLoader = $this->module();
        $this->assertInIsolation(static function () use ($wpLoader) {
            $wpLoader->_initialize();
        });
    }


    /**
     * It should throw if there is an error while switching theme
     *
     * @test
     * @group slow
     */
    public function should_throw_if_there_is_an_error_while_switching_theme(): void
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
            'theme' => 'some-theme',
        ];
        $this->fastScaffold($wpRootDir, 'latest');

        $this->expectException(ModuleException::class);
        $this->expectExceptionMessage('The theme directory "some-theme" does not exist');

        $wpLoader = $this->module();
        $this->assertInIsolation(static function () use ($wpLoader) {
            $wpLoader->_initialize();
        });
    }


    /**
     * It should throw if there is an error while switching theme in multisite
     *
     * @test
     * @group slow
     */
    public function should_throw_if_there_is_an_error_while_switching_theme_in_multisite(): void
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
            'theme' => 'some-theme',
            'multisite' => true,
        ];
        $this->fastScaffold($wpRootDir, 'latest');

        $this->expectException(ModuleException::class);
        $this->expectExceptionMessage('The theme directory "some-theme" does not exist');

        $wpLoader = $this->module();
        $this->assertInIsolation(static function () use ($wpLoader) {
            $wpLoader->_initialize();
        });
    }


    public function notABooleanProvider(): array
    {
        return [
            'string' => ['string'],
            'integer' => [1],
            'float' => [1.1],
            'array' => [[]],
            'object' => [new stdClass()],
        ];
    }


    /**
     * It should throw if backupGlobals is not a boolean
     *
     * @test
     * @dataProvider notABooleanProvider
     * @group backup-globals
     */
    public function should_throw_if_backup_globals_is_not_a_boolean($notABoolean): void
    {
        $wpRootDir = Env::get('WORDPRESS_ROOT_DIR');
        $this->config = [
            'wpRootFolder' => $wpRootDir,
            'dbUrl' => Env::get('WORDPRESS_DB_URL'),
            'backupGlobals' => $notABoolean,
        ];

        $this->expectException(ModuleConfigException::class);

        $this->module();
    }


    public function notArrayOfStringsProvider(): array
    {
        return [
            'string' => ['string'],
            'integer' => [1],
            'float' => [1.1],
            'object' => [new stdClass()],
            'array of integers' => [[1, 2, 3]],
            'array of floats' => [[1.1, 2.2, 3.3]],
            'array of objects' => [[new stdClass(), new stdClass(), new stdClass()]],
            'array of arrays' => [[[1, 2, 3], [4, 5, 6], [7, 8, 9]]],
            'array of mixed' => [[1, 2.2, new stdClass(), [1, 2, 3]]],
        ];
    }


    /**
     * It should throw if backupGlobalsExcludeList is not an array of strings
     *
     * @test
     * @dataProvider notArrayOfStringsProvider
     * @group backup-globals
     */
    public function should_throw_if_backup_globals_exclude_list_is_not_an_array_of_strings($input): void
    {
        $wpRootDir = Env::get('WORDPRESS_ROOT_DIR');
        $this->config = [
            'wpRootFolder' => $wpRootDir,
            'dbUrl' => Env::get('WORDPRESS_DB_URL'),
            'backupGlobalsExcludeList' => $input,
        ];

        $this->expectException(ModuleConfigException::class);

        $this->module();
    }


    /**
     * It should throw if backupStaticAttributes is not a boolean
     *
     * @test
     * @dataProvider notABooleanProvider
     * @group backup-globals
     */
    public function should_throw_if_backup_static_attributes_is_not_a_boolean($notABoolean): void
    {
        $wpRootDir = Env::get('WORDPRESS_ROOT_DIR');
        $this->config = [
            'wpRootFolder' => $wpRootDir,
            'dbUrl' => Env::get('WORDPRESS_DB_URL'),
            'backupStaticAttributes' => $notABoolean,
        ];

        $this->expectException(ModuleConfigException::class);

        $this->module();
    }


    public function notStaticAttributeExcludeListProvider(): array
    {
        return [
            'string' => ['string'],
            'integer' => [1],
            'float' => [1.1],
            'object' => [new stdClass()],
            'array of integers' => [[1, 2, 3]],
            'array of floats' => [[1.1, 2.2, 3.3]],
            'array of objects' => [[new stdClass(), new stdClass(), new stdClass()]],
            'array of arrays' => [[[1, 2, 3], [4, 5, 6], [7, 8, 9]]],
            'array of mixed' => [[1, 2.2, new stdClass(), [1, 2, 3]]],
        ];
    }


    /**
     * It should throw if backupStaticAttributesExcludeList is not in the correct format
     *
     * @test
     * @dataProvider notStaticAttributeExcludeListProvider
     * @group backup-globals
     */
    public function should_throw_if_backup_static_attributes_exclude_list_is_not_in_the_correct_format($input): void
    {
        $wpRootDir = Env::get('WORDPRESS_ROOT_DIR');
        $this->config = [
            'wpRootFolder' => $wpRootDir,
            'dbUrl' => Env::get('WORDPRESS_DB_URL'),
            'backupStaticAttributesExcludeList' => $input,
        ];

        $this->expectException(ModuleConfigException::class);

        $this->module();
    }


    /**
     * It should throw if skipInstall is not a boolean
     *
     * @test
     * @dataProvider notABooleanProvider
     */
    public function should_throw_if_skip_install_is_not_a_boolean($input): void
    {
        $wpRootDir = Env::get('WORDPRESS_ROOT_DIR');
        $this->config = [
            'wpRootFolder' => $wpRootDir,
            'dbUrl' => Env::get('WORDPRESS_DB_URL'),
            'skipInstall' => $input,
        ];

        $this->expectException(ModuleConfigException::class);

        $this->module();
    }


    /**
     * It should throw if silentlyActivatePlugins config parameter is not a list of strings
     *
     * @test
     * @dataProvider notArrayOfStringsProvider
     */
    public function should_throw_if_silently_activate_plugins_config_parameter_is_not_a_list_of_strings($input): void
    {
        $wpRootDir = Env::get('WORDPRESS_ROOT_DIR');
        $this->config = [
            'wpRootFolder' => $wpRootDir,
            'dbUrl' => Env::get('WORDPRESS_DB_URL'),
            'silentlyActivatePlugins' => $input,
        ];

        $this->expectException(ModuleConfigException::class);

        $this->module();
    }


    /**
     * It should throw if plugin appears in both plugins and silentlyActivatePlugins config parameters
     *
     * @test
     */
    public function should_throw_if_plugin_appears_in_both_plugins_and_silently_activate_plugins_config_parameters(
    ): void
    {
        $wpRootDir = Env::get('WORDPRESS_ROOT_DIR');
        $this->config = [
            'wpRootFolder' => $wpRootDir,
            'dbUrl' => Env::get('WORDPRESS_DB_URL'),
            'plugins' => ['woocommerce/woocommerce.php', 'my-plugin/plugin.php'],
            'silentlyActivatePlugins' => ['foo-plugin/plugin.php', 'woocommerce/woocommerce.php'],
        ];

        $this->expectException(ModuleConfigException::class);

        $this->module();
    }

}

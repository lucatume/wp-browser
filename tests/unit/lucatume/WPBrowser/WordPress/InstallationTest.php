<?php


namespace lucatume\WPBrowser\WordPress;

use Codeception\Test\Unit;
use lucatume\WPBrowser\Tests\Traits\MainInstallationAccess;
use lucatume\WPBrowser\Tests\Traits\TmpFilesCleanup;
use lucatume\WPBrowser\Tests\Traits\UopzFunctions;
use lucatume\WPBrowser\Utils\Env;
use lucatume\WPBrowser\Utils\Filesystem as FS;
use lucatume\WPBrowser\Utils\Random;
use lucatume\WPBrowser\WordPress\Database\MysqlDatabase;
use lucatume\WPBrowser\WordPress\Database\SQLiteDatabase;
use lucatume\WPBrowser\WordPress\InstallationState\InstallationStateInterface;
use lucatume\WPBrowser\WordPress\InstallationState\Multisite;
use lucatume\WPBrowser\WordPress\InstallationState\Single;
use RuntimeException;

class InstallationTest extends Unit
{
    use UopzFunctions;
    use TmpFilesCleanup;
    use MainInstallationAccess;

    /**
     * It should throw when buiding on non-existing root directory
     *
     * @test
     */
    public function should_throw_when_buiding_on_non_existing_root_directory(): void
    {
        $this->expectException(InstallationException::class);
        $this->expectExceptionCode(InstallationException::ROOT_DIR_NOT_FOUND);

        new Installation(__DIR__ . '/non-existing-dir');
    }

    /**
     * It should throw when building on non-writable root directory
     *
     * @test
     */
    public function should_throw_when_building_on_non_writable_root_directory(): void
    {
        $tmpDir = FS::tmpDir('installation_');
        $this->uopzSetFunctionReturn('is_writable',
            fn(string $dir) => !($dir === $tmpDir . '/') && is_writable($dir),
            true
        );

        $this->expectException(InstallationException::class);
        $this->expectExceptionCode(InstallationException::ROOT_DIR_NOT_RW);

        new Installation($tmpDir);
    }

    /**
     * It should throw when building on non-readable root directory
     *
     * @test
     */
    public function should_throw_when_building_on_non_readable_root_directory()
    {
        $tmpDir = FS::tmpDir('installation_');
        $this->uopzSetFunctionReturn('is_readable',
            fn(string $dir) => !($dir === $tmpDir . '/') && is_readable($dir),
            true
        );

        $this->expectException(InstallationException::class);
        $this->expectExceptionCode(InstallationException::ROOT_DIR_NOT_RW);

        new Installation($tmpDir);
    }

    /**
     * It should identify empty installation correctly
     *
     * @test
     */
    public function should_identify_empty_installation_correctly()
    {
        $wpRootDir = FS::tmpDir('installation_');

        $installation = new Installation($wpRootDir);

        $this->assertTrue($installation->isEmpty());
    }

    /**
     * It should read version from files
     *
     * @test
     */
    public function should_read_version_from_files(): void
    {
        $wpRoot = FS::tmpDir('installation_');

        Installation::scaffold($wpRoot, '4.9.8');

        $installation = new Installation($wpRoot);

        $this->assertEquals([
            'wpVersion' => '4.9.8',
            'wpDbVersion' => '38590',
            'tinymceVersion' => '4800-20180716',
            'requiredPhpVersion' => '5.2.4',
            'requiredMySqlVersion' => '5.0'
        ], $installation->getVersion()->toArray());
    }

    /**
     * It should read multisite from files
     *
     * @test
     */
    public function should_read_multisite_from_files(): void
    {
        $dbName = Random::dbName();
        $dbHost = Env::get('WORDPRESS_DB_HOST');
        $dbUser = Env::get('WORDPRESS_DB_USER');
        $dbPassword = Env::get('WORDPRESS_DB_PASSWORD');
        $wpRoot = FS::tmpDir('installation_');

        $installation = Installation::scaffold($wpRoot, '4.9.8', true, false);

        $this->expectException(InstallationException::class);
        $this->expectExceptionCode(InstallationException::STATE_SCAFFOLDED);

        $installation->isMultisite();

        $installation->configure(new MysqlDatabase($dbName, $dbUser, $dbPassword, $dbHost));

        $installation->convertToMultisite();

        $this->assertTrue($installation->isMultisite());

        $installation = new Installation($wpRoot);

        $this->assertTrue($installation->isMultisite());
    }

    /**
     * It should read the table prefix from files
     *
     * @test
     */
    public function should_read_the_table_prefix_from_files(): void
    {
        $dbName = Random::dbName();
        $dbHost = Env::get('WORDPRESS_DB_HOST');
        $dbUser = Env::get('WORDPRESS_DB_USER');
        $dbPassword = Env::get('WORDPRESS_DB_PASSWORD');
        $wpRoot = FS::tmpDir('installation_');

        $installation = Installation::scaffold($wpRoot, '4.9.8', true, false);

        $this->expectException(InstallationException::class);
        $this->expectExceptionCode(InstallationException::STATE_SCAFFOLDED);

        $installation->getDb();

        $installation->configure(new MysqlDatabase($dbName, $dbUser, $dbPassword, $dbHost, 'test_'));

        $installation = new Installation($wpRoot);

        $this->assertEquals('test_', $installation->getDb()->getTablePrefix());
    }

    /**
     * It should allow getting the wp-config.php file path
     *
     * @test
     */
    public function should_allow_getting_the_wp_config_php_file_path(): void
    {
        $dbName = Random::dbName();
        $dbHost = Env::get('WORDPRESS_DB_HOST');
        $dbUser = Env::get('WORDPRESS_DB_USER');
        $dbPassword = Env::get('WORDPRESS_DB_PASSWORD');
        $wpRoot = FS::tmpDir('installation_');

        $installation = Installation::scaffold($wpRoot, '4.9.8', true, false)
            ->configure(new MysqlDatabase($dbName, $dbUser, $dbPassword, $dbHost));

        $this->assertEquals($wpRoot . '/wp-config.php', $installation->getWpConfigFilePath());
    }

    /**
     * It should allow getting the wp-config.php file path when placed out of root
     *
     * @test
     */
    public function should_allow_getting_the_wp_config_php_file_path_when_placed_out_of_root(): void
    {
        $dbName = Random::dbName();
        $dbHost = Env::get('WORDPRESS_DB_HOST');
        $dbUser = Env::get('WORDPRESS_DB_USER');
        $dbPassword = Env::get('WORDPRESS_DB_PASSWORD');
        $dir = FS::tmpDir('installation_', ['public' => []]);
        $wpRoot = $dir . '/public';

        $setupInstallation = Installation::scaffold($wpRoot, '4.9.8', true, false)
            ->configure(new MysqlDatabase($dbName, $dbUser, $dbPassword, $dbHost));
        if (!rename($wpRoot . '/wp-config.php', $dir . '/wp-config.php')) {
            throw new RuntimeException('Could not move wp-config.php up.');
        }

        // Update the ABSPATH to point to the /public directory.
        $wpConfigFileContents = file_get_contents($dir . '/wp-config.php');
        $updatedWPConfigFileContents = preg_replace(
            '/define\\s*?\\(\\s*?["\']ABSPATH.*?$/um',
            "define('ABSPATH', dirname(__FILE__). '/public/');",
            $wpConfigFileContents
        );
        file_put_contents($dir . '/wp-config.php', $updatedWPConfigFileContents);

        $installation = new Installation($wpRoot);

        $this->assertEquals($dir . '/wp-config.php', $installation->getWpConfigFilePath());
    }

    /**
     * It should allow runing wp-cli command on empty installation
     *
     * @test
     */
    public function should_allow_runing_wp_cli_command_on_empty_installation(): void
    {
        $wpRoot = FS::tmpDir('installation_');

        $installation = new Installation($wpRoot);

        $installation->runWpCliCommandOrThrow(['cli', 'info']);
    }

    /**
     * It should allow running wp-cli command on scaffolded installation
     *
     * @test
     */
    public function should_allow_running_wp_cli_command_on_scaffolded_installation(): void
    {
        $wpRoot = FS::tmpDir('installation_');

        Installation::scaffold($wpRoot, '4.9.8');

        $installation = new Installation($wpRoot);

        $installation->runWpCliCommandOrThrow(['core', 'version']);
    }

    /**
     * It should allow running wp-cli command on configured installation
     *
     * @test
     */
    public function should_allow_running_wp_cli_command_on_configured_installation(): void
    {
        $dbName = Random::dbName();
        $dbHost = Env::get('WORDPRESS_DB_HOST');
        $dbUser = Env::get('WORDPRESS_DB_USER');
        $dbPassword = Env::get('WORDPRESS_DB_PASSWORD');
        $wpRoot = FS::tmpDir('installation_');

        $installation = Installation::scaffold($wpRoot, '4.9.8', true, false)
            ->configure(new MysqlDatabase($dbName, $dbUser, $dbPassword, $dbHost));

        $this->assertEquals(
            '4.9.8',
            trim($installation->runWpCliCommandOrThrow(['core', 'version'])->getOutput())
        );
        $this->assertFileExists(codecept_output_dir('bin/wp-cli.phar'));
    }

    /**
     * It should allow running wp-cli command on single installation
     *
     * @test
     */
    public function should_allow_running_wp_cli_command_on_single_installation(): void
    {
        $dbName = Random::dbName();
        $dbHost = Env::get('WORDPRESS_DB_HOST');
        $dbUser = Env::get('WORDPRESS_DB_USER');
        $dbPassword = Env::get('WORDPRESS_DB_PASSWORD');
        $wpRoot = FS::tmpDir('installation_');

        $installation = Installation::scaffold($wpRoot, '6.1')
            ->configure(new MysqlDatabase($dbName, $dbUser, $dbPassword, $dbHost))
            ->install(
                'https://wp.local',
                'admin',
                'password',
                'admin@wp.local',
                'Test'
            );

        $this->assertEquals('6.1', trim($installation->runWpCliCommandOrThrow(['core', 'version'])->getOutput()));
    }

    /**
     * It should allow running wp-cli command on multisite installation
     *
     * @test
     */
    public function should_allow_running_wp_cli_command_on_multisite_installation(): void
    {
        $dbName = Random::dbName();
        $dbHost = Env::get('WORDPRESS_DB_HOST');
        $dbUser = Env::get('WORDPRESS_DB_USER');
        $dbPassword = Env::get('WORDPRESS_DB_PASSWORD');
        $wpRoot = FS::tmpDir('installation_');

        $installation = Installation::scaffold($wpRoot, '6.1')
            ->configure(new MysqlDatabase($dbName, $dbUser, $dbPassword, $dbHost),
                InstallationStateInterface::MULTISITE_SUBFOLDER)
            ->install(
                'https://wp.local',
                'admin',
                'password',
                'admin@wp.local',
                'Test'
            );

        $this->assertEquals('6.1', trim($installation->runWpCliCommandOrThrow(['core', 'version'])->getOutput()));
    }

    /**
     * It should support SQLite database during configuration
     *
     * @test
     */
    public function should_support_sq_lite_database_during_configuration(): void
    {
        $wpRoot = FS::tmpDir('installation_');
        $db = new SQLiteDatabase($wpRoot, 'db.sqlite');
        $installation = Installation::scaffold($wpRoot, '6.1.1')
            ->configure($db);

        $this->assertFileExists($wpRoot . '/wp-content/db.php');
        $this->assertTrue($installation->usesSqlite());
        $this->assertFalse($installation->usesMysql());

        $testInstallation = new Installation($wpRoot);
        $this->assertTrue($testInstallation->usesSqlite());
        $this->assertFalse($testInstallation->usesMysql());
    }

    /**
     * It should support SQLite in single installation
     *
     * @test
     */
    public function should_support_sq_lite_in_single_installation(): void
    {
        $wpRoot = FS::tmpDir('installation_');
        $db = new SQLiteDatabase($wpRoot, 'db.sqlite');
        $installation = Installation::scaffold($wpRoot, '6.1.1')
            ->configure($db)
            ->install(
                'https://localhost:2389',
                'admin',
                'password',
                'admin@wp.local',
                'Test'
            );

        $this->assertFalse($installation->usesMysql());
        $this->assertTrue($installation->usesSqlite());
        $this->assertInstanceOf(Single::class, $installation->getState());
    }

    /**
     * It should support sqlite in multisite subdomain installation
     *
     * @test
     */
    public function should_support_sqlite_in_multisite_subdomain_installation(): void
    {
        $wpRoot = FS::tmpDir('installation_');
        $db = new SQLiteDatabase($wpRoot, 'db.sqlite');
        $installation = Installation::scaffold($wpRoot, '6.1.1')
            ->configure($db, InstallationStateInterface::MULTISITE_SUBDOMAIN)
            ->install(
                'https://wordpress.test',
                'admin',
                'password',
                'admin@wp.local',
                'Test'
            );

        $this->assertFalse($installation->usesMysql());
        $this->assertTrue($installation->usesSqlite());
        $this->assertInstanceOf(Multisite::class, $installation->getState());
    }

    /**
     * It should support sqlite in multisite subfolder installation
     *
     * @test
     */
    public function should_support_sqlite_in_multisite_subfolder_installation(): void
    {
        $wpRoot = FS::tmpDir('installation_');
        $db = new SQLiteDatabase($wpRoot, 'db.sqlite');
        $installation = Installation::scaffold($wpRoot, '6.1.1')
            ->configure($db, InstallationStateInterface::MULTISITE_SUBFOLDER)
            ->install(
                'https://wordpress.test',
                'admin',
                'password',
                'admin@wp.local',
                'Test'
            );

        $this->assertFalse($installation->usesMysql());
        $this->assertTrue($installation->usesSqlite());
        $this->assertInstanceOf(Multisite::class, $installation->getState());
    }

    /**
     * It should support wp-cli commands when using sqlite
     *
     * @test
     */
    public function should_support_wp_cli_commands_when_using_sqlite(): void
    {
        $wpRoot = FS::tmpDir('installation_');
        $db = new SQLiteDatabase($wpRoot, 'db.sqlite');
        $installation = Installation::scaffold($wpRoot, '6.1.1')
            ->configure($db)
            ->install(
                'https://localhost:2389',
                'admin',
                'password',
                'admin@wp.local',
                'Test'
            );

        $installation->runWpCliCommandOrThrow(['plugin', 'list', '--status=active']);
    }

    /**
     * It should support complex plugin load in sqlite context
     *
     * @test
     */
    public function should_support_complex_plugin_load_in_sqlite_context(): void
    {
        $wpRoot = FS::tmpDir('installation_');
        $db = new SQLiteDatabase($wpRoot, 'db.sqlite');
        $installation = Installation::scaffold($wpRoot, '6.1.1')
            ->configure($db)
            ->install(
                'https://localhost:2389',
                'admin',
                'password',
                'admin@wp.local',
                'Test'
            );
        $this->copyOverContentFromTheMainInstallation($installation);

        $wooCommerceActivationProcess = $installation->runWpCliCommandOrThrow(['plugin', 'activate', 'woocommerce']);
        $this->assertEquals(
            0,
            $wooCommerceActivationProcess->getExitCode()
        );
        codecept_debug($wooCommerceActivationProcess->getOutput());
    }
}

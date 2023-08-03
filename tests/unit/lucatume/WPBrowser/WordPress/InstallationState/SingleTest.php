<?php


namespace lucatume\WPBrowser\WordPress\InstallationState;

use Codeception\Test\Unit;
use lucatume\WPBrowser\Exceptions\InvalidArgumentException;
use lucatume\WPBrowser\Tests\Traits\TmpFilesCleanup;
use lucatume\WPBrowser\Tests\Traits\UopzFunctions;
use lucatume\WPBrowser\Utils\Env;
use lucatume\WPBrowser\Utils\Filesystem as FS;
use lucatume\WPBrowser\Utils\Random;
use lucatume\WPBrowser\WordPress\ConfigurationData;
use lucatume\WPBrowser\WordPress\Database\MysqlDatabase;
use lucatume\WPBrowser\WordPress\Database\SQLiteDatabase;
use lucatume\WPBrowser\WordPress\Installation;
use lucatume\WPBrowser\WordPress\InstallationException;

class SingleTest extends Unit
{
    use UopzFunctions;
    use TmpFilesCleanup;

    /**
     * It should throw when building on non existing root directory
     *
     * @test
     */
    public function should_throw_when_building_on_non_existing_root_directory(): void
    {
        $dbName = Random::dbName();
        $dbHost = Env::get('WORDPRESS_DB_HOST');
        $dbUser = Env::get('WORDPRESS_DB_USER');
        $dbPassword = Env::get('WORDPRESS_DB_PASSWORD');
        $db = new MysqlDatabase($dbName, $dbUser, $dbPassword, $dbHost);

        $this->expectException(InstallationException::class);
        $this->expectExceptionCode(InstallationException::ROOT_DIR_NOT_FOUND);

        new Single('/non-existing-dir', '/non-existing-dir/wp-config.php');
    }

    /**
     * It should throw if specified wp-config.php file is not found
     *
     * @test
     */
    public function should_throw_if_specified_wp_config_php_file_is_not_found(): void
    {
        $dbName = Random::dbName();
        $dbHost = Env::get('WORDPRESS_DB_HOST');
        $dbUser = Env::get('WORDPRESS_DB_USER');
        $dbPassword = Env::get('WORDPRESS_DB_PASSWORD');
        $db = new MysqlDatabase($dbName, $dbUser, $dbPassword, $dbHost);
        $wpRootDir = FS::tmpDir('single_');
        Installation::scaffold($wpRootDir, '6.1.1')
            ->configure($db)
            ->install(
                'https://wp.local',
                'admin',
                'password',
                'admin@wp.local',
                'Test'
            );
        unlink($wpRootDir . '/wp-config.php');

        $this->expectException(InstallationException::class);
        $this->expectExceptionCode(InstallationException::WP_CONFIG_FILE_NOT_FOUND);

        new Single($wpRootDir, $wpRootDir . '/wp-config.php');
    }

    /**
     * It should throw if built on root directory missing wp-load.php file
     *
     * @test
     */
    public function should_throw_if_built_on_root_directory_missing_wp_load_php_file(): void
    {
        $dbName = Random::dbName();
        $dbHost = Env::get('WORDPRESS_DB_HOST');
        $dbUser = Env::get('WORDPRESS_DB_USER');
        $dbPassword = Env::get('WORDPRESS_DB_PASSWORD');
        $db = new MysqlDatabase($dbName, $dbUser, $dbPassword, $dbHost);
        $wpRootDir = FS::tmpDir('single_');
        Installation::scaffold($wpRootDir, '6.1.1')
            ->configure($db)
            ->install(
                'https://wp.local',
                'admin',
                'password',
                'admin@wp.local',
                'Test'
            );
        unlink($wpRootDir . '/wp-load.php');

        $this->expectException(InstallationException::class);
        $this->expectExceptionCode(InstallationException::STATE_EMPTY);

        new Single($wpRootDir, $wpRootDir . '/wp-config.php');
    }

    /**
     * It should throw if installation configured but not installed
     *
     * @test
     */
    public function should_throw_if_installation_configured_but_not_installed(): void
    {
        $dbName = Random::dbName();
        $dbHost = Env::get('WORDPRESS_DB_HOST');
        $dbUser = Env::get('WORDPRESS_DB_USER');
        $dbPassword = Env::get('WORDPRESS_DB_PASSWORD');
        $db = new MysqlDatabase($dbName, $dbUser, $dbPassword, $dbHost);
        $wpRootDir = FS::tmpDir('single_');
        Installation::scaffold($wpRootDir, '6.1.1')->configure($db);

        $this->expectException(InstallationException::class);
        $this->expectExceptionCode(InstallationException::STATE_CONFIGURED);

        new Single($wpRootDir, $wpRootDir . '/wp-config.php');
    }

    /**
     * It should throw if building on installed and configured multisite installation
     *
     * @test
     */
    public function should_throw_if_building_on_installed_and_configured_multisite_installation(): void
    {
        $dbName = Random::dbName();
        $dbHost = Env::get('WORDPRESS_DB_HOST');
        $dbUser = Env::get('WORDPRESS_DB_USER');
        $dbPassword = Env::get('WORDPRESS_DB_PASSWORD');
        $db = new MysqlDatabase($dbName, $dbUser, $dbPassword, $dbHost);
        $wpRootDir = FS::tmpDir('single_');
        Installation::scaffold($wpRootDir, '6.1.1')
            ->configure($db)
            ->install(
                'https://wp.local',
                'admin',
                'password',
                'admin@wp.local',
                'Test'
            )
            ->convertToMultisite(false);

        $this->expectException(InstallationException::class);
        $this->expectExceptionCode(InstallationException::STATE_MULTISITE);

        new Single($wpRootDir, $wpRootDir . '/wp-config.php');
    }

    /**
     * It should throw if trying to install again
     *
     * @test
     */
    public function should_throw_if_trying_to_install_again(): void
    {
        $dbName = Random::dbName();
        $dbHost = Env::get('WORDPRESS_DB_HOST');
        $dbUser = Env::get('WORDPRESS_DB_USER');
        $dbPassword = Env::get('WORDPRESS_DB_PASSWORD');
        $db = new MysqlDatabase($dbName, $dbUser, $dbPassword, $dbHost);
        $wpRootDir = FS::tmpDir('single_');
        Installation::scaffold($wpRootDir, '6.1.1')->configure($db)->install(
            'https://wp.local',
            'admin',
            'password',
            'admin@wp.local',
            'Test'
        );

        $single = new Single($wpRootDir, $wpRootDir . '/wp-config.php');

        $this->expectException(InstallationException::class);
        $this->expectExceptionCode(InstallationException::STATE_SINGLE);

        $single->install(
            'https://wp.local',
            'admin',
            'password',
            'admin@wp.local',
            'Test'
        );
    }

    /**
     * It should throw if trying to scaffold
     *
     * @test
     */
    public function should_throw_if_trying_to_scaffold(): void
    {
        $dbName = Random::dbName();
        $dbHost = Env::get('WORDPRESS_DB_HOST');
        $dbUser = Env::get('WORDPRESS_DB_USER');
        $dbPassword = Env::get('WORDPRESS_DB_PASSWORD');
        $db = new MysqlDatabase($dbName, $dbUser, $dbPassword, $dbHost);
        $wpRootDir = FS::tmpDir('single_');
        Installation::scaffold($wpRootDir, '6.1.1')->configure($db)->install(
            'https://wp.local',
            'admin',
            'password',
            'admin@wp.local',
            'Test'
        );

        $single = new Single($wpRootDir, $wpRootDir . '/wp-config.php');

        $this->expectException(InstallationException::class);
        $this->expectExceptionCode(InstallationException::STATE_SINGLE);

        $single->scaffold();
    }

    /**
     * It should throw if wp-config.php file contents cannot be read during multsite conversion
     *
     * @test
     */
    public function should_throw_if_wp_config_php_file_contents_cannot_be_read_during_multsite_conversion(): void
    {
        $dbName = Random::dbName();
        $dbHost = Env::get('WORDPRESS_DB_HOST');
        $dbUser = Env::get('WORDPRESS_DB_USER');
        $dbPassword = Env::get('WORDPRESS_DB_PASSWORD');
        $db = new MysqlDatabase($dbName, $dbUser, $dbPassword, $dbHost);
        $wpRootDir = FS::tmpDir('single_');
        Installation::scaffold($wpRootDir, '6.1.1')->configure($db)->install(
            'https://wp.local',
            'admin',
            'password',
            'admin@wp.local',
            'Test'
        );

        $single = new Single($wpRootDir, $wpRootDir . '/wp-config.php');

        $this->uopzSetFunctionReturn('file_get_contents', false);

        $this->expectException(InstallationException::class);
        $this->expectExceptionCode(InstallationException::WP_CONFIG_FILE_NOT_FOUND);

        $single->convertToMultisite();
    }

    /**
     * It should throw if the placeholder is not found in the wp-config.php file during multisite conversion
     *
     * @test
     */
    public function should_throw_if_the_placeholder_is_not_found_in_the_wp_config_php_file_during_multisite_conversion(
    ): void
    {
        $dbName = Random::dbName();
        $dbHost = Env::get('WORDPRESS_DB_HOST');
        $dbUser = Env::get('WORDPRESS_DB_USER');
        $dbPassword = Env::get('WORDPRESS_DB_PASSWORD');
        $db = new MysqlDatabase($dbName, $dbUser, $dbPassword, $dbHost);
        $wpRootDir = FS::tmpDir('single_');
        Installation::scaffold($wpRootDir, '6.1.1')->configure($db)->install(
            'https://wp.local',
            'admin',
            'password',
            'admin@wp.local',
            'Test'
        );
        $wpConfigFilePath = $wpRootDir . '/wp-config.php';

        $single = new Single($wpRootDir, $wpConfigFilePath);


        $this->uopzSetFunctionReturn('file_get_contents', function (string $file) use ($wpConfigFilePath) {
            if ($file === $wpConfigFilePath) {
                return '<?php echo "Not a wp-config.php file"';
            }
            return file_get_contents($file);
        }, true);

        $this->expectException(InstallationException::class);
        $this->expectExceptionCode(InstallationException::WP_CONFIG_FILE_MISSING_PLACEHOLDER);

        $single->convertToMultisite();
    }

    /**
     * It should throw if wp-config.php file cannot be written during multisite conversion
     *
     * @test
     */
    public function should_throw_if_wp_config_php_file_cannot_be_written_during_multisite_conversion(): void
    {
        $dbName = Random::dbName();
        $dbHost = Env::get('WORDPRESS_DB_HOST');
        $dbUser = Env::get('WORDPRESS_DB_USER');
        $dbPassword = Env::get('WORDPRESS_DB_PASSWORD');
        $db = new MysqlDatabase($dbName, $dbUser, $dbPassword, $dbHost);
        $wpRootDir = FS::tmpDir('single_');
        Installation::scaffold($wpRootDir, '6.1.1')->configure($db)->install(
            'https://wp.local',
            'admin',
            'password',
            'admin@wp.local',
            'Test'
        );
        $wpConfigFilePath = $wpRootDir . '/wp-config.php';

        $single = new Single($wpRootDir, $wpConfigFilePath);


        $this->uopzSetFunctionReturn('file_put_contents', false);

        $this->expectException(InstallationException::class);
        $this->expectExceptionCode(InstallationException::WRITE_ERROR);

        $single->convertToMultisite();
    }

    /**
     * It should allow converting the installation to multisite subdir installation
     *
     * @test
     */
    public function should_allow_converting_the_installation_to_multisite_subdir_installation(): void
    {
        $dbName = Random::dbName();
        $dbHost = Env::get('WORDPRESS_DB_HOST');
        $dbUser = Env::get('WORDPRESS_DB_USER');
        $dbPassword = Env::get('WORDPRESS_DB_PASSWORD');
        $db = new MysqlDatabase($dbName, $dbUser, $dbPassword, $dbHost);
        $wpRootDir = FS::tmpDir('single_');
        Installation::scaffold($wpRootDir, '6.1.1')->configure($db)->install(
            'https://wp.local',
            'admin',
            'password',
            'admin@wp.local',
            'Test'
        );

        $single = new Single($wpRootDir, $wpRootDir . '/wp-config.php');

        $multisite = $single->convertToMultisite(false);

        $this->assertInstanceOf(Multisite::class, $multisite);
    }

    /**
     * It should allow converting the installation to multisite subdomain installation
     *
     * @test
     */
    public function should_allow_converting_the_installation_to_multisite_subdomain_installation(): void
    {
        $dbName = Random::dbName();
        $dbHost = Env::get('WORDPRESS_DB_HOST');
        $dbUser = Env::get('WORDPRESS_DB_USER');
        $dbPassword = Env::get('WORDPRESS_DB_PASSWORD');
        $db = new MysqlDatabase($dbName, $dbUser, $dbPassword, $dbHost);
        $wpRootDir = FS::tmpDir('single_');
        Installation::scaffold($wpRootDir, '6.1.1')->configure($db)->install(
            'https://wp.local',
            'admin',
            'password',
            'admin@wp.local',
            'Test'
        );

        $single = new Single($wpRootDir, $wpRootDir . '/wp-config.php');

        $multisite = $single->convertToMultisite(true);

        $this->assertInstanceOf(Multisite::class, $multisite);
    }

    /**
     * It should allow getting information about the installation
     *
     * @test
     */
    public function should_allow_getting_information_about_the_installation(): void
    {
        $wpRootDir = FS::tmpDir('single_j');
        $dbName = Random::dbName();
        $dbHost = Env::get('WORDPRESS_DB_HOST');
        $dbUser = Env::get('WORDPRESS_DB_USER');
        $dbPassword = Env::get('WORDPRESS_DB_PASSWORD');
        $db = new MysqlDatabase($dbName, $dbUser, $dbPassword, $dbHost, 'test_');
        Installation::scaffold($wpRootDir, '6.1.1')->configure($db)->install(
            'https://wp.local',
            'admin',
            'password',
            'admin@wp.local',
            'Test'
        );

        $single = new Single($wpRootDir, $wpRootDir . '/wp-config.php');

        $this->assertFalse($single->isMultisite());
        $this->assertEquals($wpRootDir . '/', $single->getWpRootDir());
        $this->assertEquals($wpRootDir . '/wp-config.php', $single->getWpRootDir('wp-config.php'));
        $this->assertEquals($wpRootDir . '/wp-config.php', $single->getWpRootDir('/wp-config.php'));
        $this->assertEquals($wpRootDir . '/wp-config.php', $single->getWpConfigPath());
        $this->assertTrue(strlen($single->getAuthKey()) === 64 && $single->getAuthKey() !== $single->getSecureAuthKey());
        $this->assertTrue(strlen($single->getSecureAuthKey()) === 64 && $single->getSecureAuthKey() !== $single->getLoggedInKey());
        $this->assertTrue(strlen($single->getLoggedInKey()) === 64 && $single->getLoggedInKey() !== $single->getNonceKey());
        $this->assertTrue(strlen($single->getNonceKey()) === 64 && $single->getNonceKey() !== $single->getAuthSalt());
        $this->assertTrue(strlen($single->getAuthSalt()) === 64 && $single->getAuthSalt() !== $single->getSecureAuthSalt());
        $this->assertTrue(strlen($single->getSecureAuthSalt()) === 64 && $single->getSecureAuthSalt() !== $single->getLoggedInSalt());
        $this->assertTrue(strlen($single->getLoggedInSalt()) === 64 && $single->getLoggedInSalt() !== $single->getNonceSalt());
        $this->assertSame(64, strlen($single->getNonceSalt()));
        $this->assertEquals('test_', $single->getTablePrefix());
        $this->assertTrue($single->isConfigured());
        $this->assertEquals([
            'AUTH_KEY' => $single->getAuthKey(),
            'SECURE_AUTH_KEY' => $single->getSecureAuthKey(),
            'LOGGED_IN_KEY' => $single->getLoggedInKey(),
            'NONCE_KEY' => $single->getNonceKey(),
            'AUTH_SALT' => $single->getAuthSalt(),
            'SECURE_AUTH_SALT' => $single->getSecureAuthSalt(),
            'LOGGED_IN_SALT' => $single->getLoggedInSalt(),
            'NONCE_SALT' => $single->getNonceSalt(),
        ], $single->getSalts());
    }

    /**
     * It should allow getting the db
     *
     * @test
     */
    public function should_allow_getting_the_db(): void
    {
        $wpRootDir = FS::tmpDir('single_k');
        $dbName = Random::dbName();
        $dbHost = Env::get('WORDPRESS_DB_HOST');
        $dbUser = Env::get('WORDPRESS_DB_USER');
        $dbPassword = Env::get('WORDPRESS_DB_PASSWORD');
        $db = new MysqlDatabase($dbName, $dbUser, $dbPassword, $dbHost, 'test_');
        Installation::scaffold($wpRootDir, '6.1.1')->configure($db)->install(
            'https://wp.local',
            'admin',
            'password',
            'admin@wp.local',
            'Test');

        $single = new Single($wpRootDir, $wpRootDir . '/wp-config.php');

        $this->assertEquals($dbName, $single->getDb()->getDbName());
        $this->assertEquals($dbHost, $single->getDb()->getDbHost());
        $this->assertEquals($dbUser, $single->getDb()->getDbUser());
        $this->assertEquals($dbPassword, $single->getDb()->getDbPassword());
    }

    /**
     * It should allow getting the site constants
     *
     * @test
     */
    public function should_allow_getting_the_site_constants(): void
    {
        $wpRootDir = FS::tmpDir('single_');
        $dbName = Random::dbName();
        $dbHost = Env::get('WORDPRESS_DB_HOST');
        $dbUser = Env::get('WORDPRESS_DB_USER');
        $dbPassword = Env::get('WORDPRESS_DB_PASSWORD');
        $db = new MysqlDatabase($dbName, $dbUser, $dbPassword, $dbHost, 'test_');
        Installation::scaffold($wpRootDir, '6.1.1')->configure($db)->install(
            'https://wp.local',
            'admin',
            'password',
            'admin@wp.local',
            'Test');

        $single = new Single($wpRootDir, $wpRootDir . '/wp-config.php');
        $constants = $single->getConstants();

        $expected = [
            'DB_NAME' => $dbName,
            'DB_USER' => $dbUser,
            'DB_PASSWORD' => $dbPassword,
            'DB_HOST' => $dbHost,
            'DB_CHARSET' => 'utf8',
            'DB_COLLATE' => '',
            'AUTH_KEY' => $single->getAuthKey(),
            'SECURE_AUTH_KEY' => $single->getSecureAuthKey(),
            'LOGGED_IN_KEY' => $single->getLoggedInKey(),
            'NONCE_KEY' => $single->getNonceKey(),
            'AUTH_SALT' => $single->getAuthSalt(),
            'SECURE_AUTH_SALT' => $single->getSecureAuthSalt(),
            'LOGGED_IN_SALT' => $single->getLoggedInSalt(),
            'NONCE_SALT' => $single->getNonceSalt(),
            'WP_DEBUG' => false,
            'ABSPATH' => $wpRootDir
        ];
        $this->assertCount(count($expected), $constants);
        foreach ($expected as $key => $expectedValue) {
            $this->assertArrayHasKey($key, $constants);
        }
    }

    /**
     * It should allow getting the site globals
     *
     * @test
     */
    public function should_allow_getting_the_site_globals(): void
    {
        $wpRootDir = FS::tmpDir('single_');
        $dbName = Random::dbName();
        $dbHost = Env::get('WORDPRESS_DB_HOST');
        $dbUser = Env::get('WORDPRESS_DB_USER');
        $dbPassword = Env::get('WORDPRESS_DB_PASSWORD');
        $db = new MysqlDatabase($dbName, $dbUser, $dbPassword, $dbHost, 'test_');
        Installation::scaffold($wpRootDir, '6.1.1')->configure($db)->install(
            'https://wp.local',
            'admin',
            'password',
            'admin@wp.local',
            'Test');

        $single = new Single($wpRootDir, $wpRootDir . '/wp-config.php');
        $globals = $single->getGlobals();

        $expected = [
            'table_prefix' => 'test_',
        ];
        $this->assertCount(count($expected), $globals);
        foreach ($expected as $key => $expectedValue) {
            $this->assertArrayHasKey($key, $globals);
        }
    }

    /**
     * It should return plugins directory
     *
     * @test
     */
    public function should_return_plugins_directory(): void
    {
        $wpRootDir = FS::tmpDir('single_');
        $dbName = Random::dbName();
        $dbHost = Env::get('WORDPRESS_DB_HOST');
        $dbUser = Env::get('WORDPRESS_DB_USER');
        $dbPassword = Env::get('WORDPRESS_DB_PASSWORD');
        $db = new MysqlDatabase($dbName, $dbUser, $dbPassword, $dbHost, 'test_');
        Installation::scaffold($wpRootDir, '6.1.1')
            ->configure($db)
            ->install(
                'https://wp.local',
                'admin',
                'password',
                'admin@wp.local',
                'Test');

        $single = new Single($wpRootDir, $wpRootDir . '/wp-config.php');

        $this->assertEquals($wpRootDir . '/wp-content/plugins', $single->getPluginsDir());
    }

    /**
     * It should return plugins directory built from WP_CONTENT_DIR if set
     *
     * @test
     */
    public function should_return_plugins_directory_built_from_wp_content_dir_if_set(): void
    {
        $wpRootDir = FS::tmpDir('single_');
        $dbName = Random::dbName();
        $dbHost = Env::get('WORDPRESS_DB_HOST');
        $dbUser = Env::get('WORDPRESS_DB_USER');
        $dbPassword = Env::get('WORDPRESS_DB_PASSWORD');
        $db = new MysqlDatabase($dbName, $dbUser, $dbPassword, $dbHost, 'test_');
        $configurationData = new ConfigurationData();
        $configurationData->setConst('WP_CONTENT_DIR', $wpRootDir . '/site-content');
        Installation::scaffold($wpRootDir, '6.1.1')
            ->configure($db, InstallationStateInterface::SINGLE_SITE, $configurationData)
            ->install(
                'https://wp.local',
                'admin',
                'password',
                'admin@wp.local',
                'Test');

        $single = new Single($wpRootDir, $wpRootDir . '/wp-config.php');

        $this->assertEquals($wpRootDir . '/site-content/plugins', $single->getPluginsDir());
    }

    /**
     * It should return plugins directory built from WP_PLUGIN_DIR if set
     *
     * @test
     */
    public function should_return_plugins_directory_built_from_wp_plugin_dir_if_set(): void
    {
        $wpRootDir = FS::tmpDir('single_');
        $dbName = Random::dbName();
        $dbHost = Env::get('WORDPRESS_DB_HOST');
        $dbUser = Env::get('WORDPRESS_DB_USER');
        $dbPassword = Env::get('WORDPRESS_DB_PASSWORD');
        $db = new MysqlDatabase($dbName, $dbUser, $dbPassword, $dbHost, 'test_');
        $configurationData = new ConfigurationData();
        $configurationData->setConst('WP_PLUGIN_DIR', $wpRootDir . '/site-plugins');
        Installation::scaffold($wpRootDir, '6.1.1')
            ->configure($db, InstallationStateInterface::SINGLE_SITE, $configurationData)
            ->install(
                'https://wp.local',
                'admin',
                'password',
                'admin@wp.local',
                'Test');

        $single = new Single($wpRootDir, $wpRootDir . '/wp-config.php');

        $this->assertEquals($wpRootDir . '/site-plugins', $single->getPluginsDir());
        $this->assertEquals($wpRootDir . '/site-plugins/plugin-1.php', $single->getPluginsDir('plugin-1.php'));
        $this->assertEquals($wpRootDir . '/site-plugins/test-plugin', $single->getPluginsDir('test-plugin'));
    }

    /**
     * It should return mu-plugins directory
     *
     * @test
     */
    public function should_return_mu_plugins_directory(): void
    {
        $wpRootDir = FS::tmpDir('single_');
        $dbName = Random::dbName();
        $dbHost = Env::get('WORDPRESS_DB_HOST');
        $dbUser = Env::get('WORDPRESS_DB_USER');
        $dbPassword = Env::get('WORDPRESS_DB_PASSWORD');
        $db = new MysqlDatabase($dbName, $dbUser, $dbPassword, $dbHost, 'test_');
        Installation::scaffold($wpRootDir, '6.1.1')
            ->configure($db)
            ->install(
                'https://wp.local',
                'admin',
                'password',
                'admin@wp.local',
                'Test');

        $single = new Single($wpRootDir, $wpRootDir . '/wp-config.php');

        $this->assertEquals($wpRootDir . '/wp-content/mu-plugins', $single->getMuPluginsDir());
    }

    /**
     * It should return mu-plugins directory built from WP_CONTENT_DIR if set
     *
     * @test
     */
    public function should_return_mu_plugins_directory_built_from_wp_content_dir_if_set(): void
    {
        $wpRootDir = FS::tmpDir('single_');
        $dbName = Random::dbName();
        $dbHost = Env::get('WORDPRESS_DB_HOST');
        $dbUser = Env::get('WORDPRESS_DB_USER');
        $dbPassword = Env::get('WORDPRESS_DB_PASSWORD');
        $db = new MysqlDatabase($dbName, $dbUser, $dbPassword, $dbHost, 'test_');
        $configurationData = new ConfigurationData();
        $configurationData->setConst('WP_CONTENT_DIR', $wpRootDir . '/site-content');
        Installation::scaffold($wpRootDir, '6.1.1')
            ->configure($db, InstallationStateInterface::SINGLE_SITE, $configurationData)
            ->install(
                'https://wp.local',
                'admin',
                'password',
                'admin@wp.local',
                'Test');

        $single = new Single($wpRootDir, $wpRootDir . '/wp-config.php');

        $this->assertEquals($wpRootDir . '/site-content/mu-plugins', $single->getMuPluginsDir());
    }

    /**
     * It should return mu-plugins directory built from WP_PLUGIN_DIR if set
     *
     * @test
     */
    public function should_return_mu_plugins_directory_built_from_wp_plugin_dir_if_set(): void
    {
        $wpRootDir = FS::tmpDir('single_');
        $dbName = Random::dbName();
        $dbHost = Env::get('WORDPRESS_DB_HOST');
        $dbUser = Env::get('WORDPRESS_DB_USER');
        $dbPassword = Env::get('WORDPRESS_DB_PASSWORD');
        $db = new MysqlDatabase($dbName, $dbUser, $dbPassword, $dbHost, 'test_');
        $configurationData = new ConfigurationData();
        $configurationData->setConst('WPMU_PLUGIN_DIR', $wpRootDir . '/site-mu-plugins');
        Installation::scaffold($wpRootDir, '6.1.1')
            ->configure($db, InstallationStateInterface::SINGLE_SITE, $configurationData)
            ->install(
                'https://wp.local',
                'admin',
                'password',
                'admin@wp.local',
                'Test');

        $single = new Single($wpRootDir, $wpRootDir . '/wp-config.php');

        $this->assertEquals($wpRootDir . '/site-mu-plugins', $single->getMuPluginsDir());
        $this->assertEquals($wpRootDir . '/site-mu-plugins/plugin-1.php', $single->getMuPluginsDir('plugin-1.php'));
        $this->assertEquals($wpRootDir . '/site-mu-plugins/test-plugin', $single->getMuPluginsDir('test-plugin'));
    }

    /**
     * It should return the themes directory
     *
     * @test
     */
    public function should_return_the_themes_directory(): void
    {
        $wpRootDir = FS::tmpDir('single_');
        $dbName = Random::dbName();
        $dbHost = Env::get('WORDPRESS_DB_HOST');
        $dbUser = Env::get('WORDPRESS_DB_USER');
        $dbPassword = Env::get('WORDPRESS_DB_PASSWORD');
        $db = new MysqlDatabase($dbName, $dbUser, $dbPassword, $dbHost, 'test_');
        $configurationData = new ConfigurationData();
        Installation::scaffold($wpRootDir, '6.1.1')
            ->configure($db, InstallationStateInterface::SINGLE_SITE, $configurationData)
            ->install(
                'https://wp.local',
                'admin',
                'password',
                'admin@wp.local',
                'Test');

        $single = new Single($wpRootDir, $wpRootDir . '/wp-config.php');

        $this->assertEquals($wpRootDir . '/wp-content/themes', $single->getThemesDir());
        $this->assertEquals($wpRootDir . '/wp-content/themes/some-file.php', $single->getThemesDir('some-file.php'));
        $this->assertEquals($wpRootDir . '/wp-content/themes/some-theme', $single->getThemesDir('some-theme'));
    }

    /**
     * It should return the themes directory built from WP_CONTENT_DIR if set
     *
     * @test
     */
    public function should_return_the_themes_directory_built_from_wp_content_dir_if_set(): void
    {
        $wpRootDir = FS::tmpDir('single_');
        $dbName = Random::dbName();
        $dbHost = Env::get('WORDPRESS_DB_HOST');
        $dbUser = Env::get('WORDPRESS_DB_USER');
        $dbPassword = Env::get('WORDPRESS_DB_PASSWORD');
        $db = new MysqlDatabase($dbName, $dbUser, $dbPassword, $dbHost, 'test_');
        $configurationData = new ConfigurationData();
        $configurationData->setConst('WP_CONTENT_DIR', $wpRootDir . '/site-content');
        Installation::scaffold($wpRootDir, '6.1.1')
            ->configure($db, InstallationStateInterface::SINGLE_SITE, $configurationData)
            ->install(
                'https://wp.local',
                'admin',
                'password',
                'admin@wp.local',
                'Test');

        $single = new Single($wpRootDir, $wpRootDir . '/wp-config.php');

        $this->assertEquals($wpRootDir . '/site-content/themes', $single->getThemesDir());
        $this->assertEquals($wpRootDir . '/site-content/themes/some-file.php', $single->getThemesDir('some-file.php'));
        $this->assertEquals($wpRootDir . '/site-content/themes/some-theme', $single->getThemesDir('some-theme'));
    }

    /**
     * It should return content directory
     *
     * @test
     */
    public function should_return_content_directory(): void
    {
        $wpRootDir = FS::tmpDir('single_');
        $dbName = Random::dbName();
        $dbHost = Env::get('WORDPRESS_DB_HOST');
        $dbUser = Env::get('WORDPRESS_DB_USER');
        $dbPassword = Env::get('WORDPRESS_DB_PASSWORD');
        $db = new MysqlDatabase($dbName, $dbUser, $dbPassword, $dbHost, 'test_');
        $configurationData = new ConfigurationData();
        Installation::scaffold($wpRootDir, '6.1.1')
            ->configure($db, InstallationStateInterface::SINGLE_SITE, $configurationData)
            ->install(
                'https://wp.local',
                'admin',
                'password',
                'admin@wp.local',
                'Test');

        $single = new Single($wpRootDir, $wpRootDir . '/wp-config.php');

        $this->assertEquals($wpRootDir . '/wp-content', $single->getContentDir());
        $this->assertEquals($wpRootDir . '/wp-content/some-file.php', $single->getContentDir('some-file.php'));
        $this->assertEquals($wpRootDir . '/wp-content/some/path', $single->getContentDir('/some/path'));
    }

    /**
     * It should return content directory built from WP_CONTENT_DIR if set
     *
     * @test
     */
    public function should_return_content_directory_built_from_wp_content_dir_if_set(): void
    {
        $wpRootDir = FS::tmpDir('single_');
        $dbName = Random::dbName();
        $dbHost = Env::get('WORDPRESS_DB_HOST');
        $dbUser = Env::get('WORDPRESS_DB_USER');
        $dbPassword = Env::get('WORDPRESS_DB_PASSWORD');
        $db = new MysqlDatabase($dbName, $dbUser, $dbPassword, $dbHost, 'test_');
        $configurationData = new ConfigurationData();
        $configurationData->setConst('WP_CONTENT_DIR', $wpRootDir . '/site-content');
        Installation::scaffold($wpRootDir, '6.1.1')
            ->configure($db, InstallationStateInterface::SINGLE_SITE, $configurationData)
            ->install(
                'https://wp.local',
                'admin',
                'password',
                'admin@wp.local',
                'Test');

        $single = new Single($wpRootDir, $wpRootDir . '/wp-config.php');

        $this->assertEquals($wpRootDir . '/site-content', $single->getContentDir());
        $this->assertEquals($wpRootDir . '/site-content/some-file.php', $single->getContentDir('some-file.php'));
        $this->assertEquals($wpRootDir . '/site-content/some/path', $single->getContentDir('/some/path'));
    }

    /**
     * It should allow working with options
     *
     * @test
     */
    public function should_allow_working_with_options(): void
    {
        $wpRootDir = FS::tmpDir('single_');
        $dbName = Random::dbName();
        $dbHost = Env::get('WORDPRESS_DB_HOST');
        $dbUser = Env::get('WORDPRESS_DB_USER');
        $dbPassword = Env::get('WORDPRESS_DB_PASSWORD');
        $db = new MysqlDatabase($dbName, $dbUser, $dbPassword, $dbHost, 'test_');
        Installation::scaffold($wpRootDir, '6.1.1')
            ->configure($db, InstallationStateInterface::SINGLE_SITE)
            ->install(
                'https://wp.local',
                'admin',
                'password',
                'admin@wp.local',
                'Test');

        $single = new Single($wpRootDir, $wpRootDir . '/wp-config.php');

        $this->assertEquals(1, $single->updateOption('foo', 'bar'));
        $this->assertEquals('bar', $db->getOption('foo'));
    }

    /**
     * It should throw if siteurl cannot be fetched in constructor
     *
     * @test
     */
    public function should_throw_if_siteurl_cannot_be_fetched_in_constructor(): void
    {
        $dbName = Random::dbName();
        $dbHost = Env::get('WORDPRESS_DB_HOST');
        $dbUser = Env::get('WORDPRESS_DB_USER');
        $dbPassword = Env::get('WORDPRESS_DB_PASSWORD');
        $db = new MysqlDatabase($dbName, $dbUser, $dbPassword, $dbHost);
        $wpRootDir = FS::tmpDir('single_');
        Installation::scaffold($wpRootDir, '6.1.1')
            ->configure($db)
            ->install(
                'https://wp.local',
                'admin',
                'password',
                'admin@wp.local',
                'Test'
            );
        // Delete the siteurl option.
        $db->query("DELETE FROM {$db->getTablePrefix()}options WHERE option_name = 'siteurl'");

        $this->expectException(InstallationException::class);
        $this->expectExceptionCode(InstallationException::STATE_CONFIGURED);

        new Single($wpRootDir, $wpRootDir . '/wp-config.php');
    }

    /**
     * It should throw if no admin user can be found while converting to multisite
     *
     * @test
     */
    public function should_throw_if_no_admin_user_can_be_found_while_converting_to_multisite(): void
    {
        $dbName = Random::dbName();
        $dbHost = Env::get('WORDPRESS_DB_HOST');
        $dbUser = Env::get('WORDPRESS_DB_USER');
        $dbPassword = Env::get('WORDPRESS_DB_PASSWORD');
        $db = new MysqlDatabase($dbName, $dbUser, $dbPassword, $dbHost);
        $wpRootDir = FS::tmpDir('single_');
        Installation::scaffold($wpRootDir, '6.1.1')
            ->configure($db)
            ->install(
                'https://wp.local',
                'admin',
                'password',
                'admin@wp.local',
                'Test'
            );
        $single = new Single($wpRootDir, $wpRootDir . '/wp-config.php');

        // Delete all users from the database.
        $db->query('DELETE FROM wp_users');

        $this->expectException(InstallationException::class);
        $this->expectExceptionCode(InstallationException::NO_ADMIN_USER_FOUND);
        $single->convertToMultisite(false);
    }

    /**
     * It should throw if siteurl cannot be found while converting to multisite
     *
     * @test
     */
    public function should_throw_if_siteurl_cannot_be_found_while_converting_to_multisite(): void
    {
        $dbName = Random::dbName();
        $dbHost = Env::get('WORDPRESS_DB_HOST');
        $dbUser = Env::get('WORDPRESS_DB_USER');
        $dbPassword = Env::get('WORDPRESS_DB_PASSWORD');
        $db = new MysqlDatabase($dbName, $dbUser, $dbPassword, $dbHost);
        $wpRootDir = FS::tmpDir('single_');
        Installation::scaffold($wpRootDir, '6.1.1')
            ->configure($db)
            ->install(
                'https://wp.local',
                'admin',
                'password',
                'admin@wp.local',
                'Test'
            )
            ->convertToMultisite(false);
    }

    /**
     * It should throw if trying to execute non static Closure in WordPress
     *
     * @test
     */
    public function should_throw_if_trying_to_execute_non_static_closure_in_word_press(): void
    {
        $dbName = Random::dbName();
        $dbHost = Env::get('WORDPRESS_DB_HOST');
        $dbUser = Env::get('WORDPRESS_DB_USER');
        $dbPassword = Env::get('WORDPRESS_DB_PASSWORD');
        $db = new MysqlDatabase($dbName, $dbUser, $dbPassword, $dbHost);
        $wpRootDir = FS::tmpDir('single_');
        Installation::scaffold($wpRootDir, '6.1.1')
            ->configure($db)
            ->install(
                'https://wp.local',
                'admin',
                'password',
                'admin@wp.local',
                'Test'
            );
        $single = new Single($wpRootDir, $wpRootDir . '/wp-config.php');

        $this->expectException(InvalidArgumentException::class);

        $this->assertEquals('https://wp.local',
            $single->executeClosureInWordPress(function () {
                return get_option('siteurl');
            }));
    }

    /**
     * It should allow executing a Closure in WordPress
     *
     * @test
     */
    public function should_allow_executing_a_closure_in_word_press(): void
    {
        $dbName = Random::dbName();
        $dbHost = Env::get('WORDPRESS_DB_HOST');
        $dbUser = Env::get('WORDPRESS_DB_USER');
        $dbPassword = Env::get('WORDPRESS_DB_PASSWORD');
        $db = new MysqlDatabase($dbName, $dbUser, $dbPassword, $dbHost);
        $wpRootDir = FS::tmpDir('single_');
        Installation::scaffold($wpRootDir, '6.1.1')
            ->configure($db)
            ->install(
                'https://wp.local',
                'admin',
                'password',
                'admin@wp.local',
                'Test'
            );
        $single = new Single($wpRootDir, $wpRootDir . '/wp-config.php');

        $this->assertEquals('https://wp.local',
            $single->executeClosureInWordPress(static function () {
                return get_option('siteurl');
            }));
    }

    /**
     * It should allow setting the db
     *
     * @test
     */
    public function should_allow_setting_the_db(): void
    {
        $dbName = Random::dbName();
        $dbHost = Env::get('WORDPRESS_DB_HOST');
        $dbUser = Env::get('WORDPRESS_DB_USER');
        $dbPassword = Env::get('WORDPRESS_DB_PASSWORD');
        $db = new MysqlDatabase($dbName, $dbUser, $dbPassword, $dbHost);
        $wpRootDir = FS::tmpDir('single_');
        Installation::scaffold($wpRootDir, '6.1.1')
            ->configure($db)
            ->install(
                'https://wp.local',
                'admin',
                'password',
                'admin@wp.local',
                'Test'
            );
        $single = new Single($wpRootDir, $wpRootDir . '/wp-config.php');
        $dumpFile = $wpRootDir .'/dump.sql';
        $db->dump($dumpFile);

        // Create a new database and import the dump: still installed.
        $dbName2 = Random::dbName();
        $db2 = new MysqlDatabase($dbName2, $dbUser, $dbPassword, $dbHost);
        $db2->import($dumpFile);

        $withInstalledDb = $single->setDb($db2);

        $this->assertInstanceOf(Single::class, $withInstalledDb);
        $this->assertSame($db2, $withInstalledDb->getDb());

        Installation::placeSqliteMuPlugin($wpRootDir . '/wp-content/mu-plugins', $wpRootDir . '/wp-content');

        $sqliteDb = new SqliteDatabase($wpRootDir . '/wp-content', 'test.db');

        $withSqliteDb = $single->setDb($sqliteDb);

        $this->assertInstanceOf(Configured::class, $withSqliteDb);
        $this->assertSame($sqliteDb, $withSqliteDb->getDb());

        // Install using SQLite.
        $installedOnSqlite = $withSqliteDb->install(
            'https://wp.local',
            'admin',
            'password',
            'admin@wp.local',
            'Test'
        );

        $this->assertInstanceOf(Single::class, $installedOnSqlite);

        // NOT USING DB_DIR, DB_FILE!
        $sqliteDb->dump($dumpFile);

        $dumpContents = file_get_contents($dumpFile);

        $sqliteDb2 = new SqliteDatabase($wpRootDir . '/wp-content', 'test2.db');
        $sqliteDb2->import($dumpFile);

        $withSqliteDb2 = $withSqliteDb->setDb($sqliteDb2);

        $this->assertInstanceOf(Single::class, $withSqliteDb2);
        $this->assertSame($sqliteDb2, $withSqliteDb2->getDb());
    }
}

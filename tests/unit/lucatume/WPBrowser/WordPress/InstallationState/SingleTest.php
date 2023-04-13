<?php


namespace lucatume\WPBrowser\WordPress\InstallationState;

use lucatume\WPBrowser\Tests\Traits\UopzFunctions;
use lucatume\WPBrowser\Utils\Env;
use lucatume\WPBrowser\Utils\Random;
use lucatume\WPBrowser\WordPress\ConfigurationData;
use lucatume\WPBrowser\WordPress\Db;
use lucatume\WPBrowser\WordPress\Installation;
use lucatume\WPBrowser\WordPress\InstallationException;
use lucatume\WPBrowser\Utils\Filesystem as FS;

class SingleTest extends \Codeception\Test\Unit
{
    use UopzFunctions;

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
        $db = new Db($dbName, $dbUser, $dbPassword, $dbHost);

        $this->expectException(InstallationException::class);
        $this->expectExceptionCode(InstallationException::ROOT_DIR_NOT_FOUND);

        new Single('/non-existing-dir', '/non-existing-dir/wp-config.php', $db);
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
        $db = new Db($dbName, $dbUser, $dbPassword, $dbHost);
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

        new Single($wpRootDir, $wpRootDir . '/wp-config.php', $db);
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
        $db = new Db($dbName, $dbUser, $dbPassword, $dbHost);
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

        new Single($wpRootDir, $wpRootDir . '/wp-config.php', $db);
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
        $db = new Db($dbName, $dbUser, $dbPassword, $dbHost);
        $wpRootDir = FS::tmpDir('single_');
        Installation::scaffold($wpRootDir, '6.1.1')->configure($db);

        $this->expectException(InstallationException::class);
        $this->expectExceptionCode(InstallationException::STATE_CONFIGURED);

        new Single($wpRootDir, $wpRootDir . '/wp-config.php', $db);
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
        $db = new Db($dbName, $dbUser, $dbPassword, $dbHost);
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

        new Single($wpRootDir, $wpRootDir . '/wp-config.php', $db);
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
        $db = new Db($dbName, $dbUser, $dbPassword, $dbHost);
        $wpRootDir = FS::tmpDir('single_');
        Installation::scaffold($wpRootDir, '6.1.1')->configure($db)->install(
            'https://wp.local',
            'admin',
            'password',
            'admin@wp.local',
            'Test'
        );

        $single = new Single($wpRootDir, $wpRootDir . '/wp-config.php', $db);

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
        $db = new Db($dbName, $dbUser, $dbPassword, $dbHost);
        $wpRootDir = FS::tmpDir('single_');
        Installation::scaffold($wpRootDir, '6.1.1')->configure($db)->install(
            'https://wp.local',
            'admin',
            'password',
            'admin@wp.local',
            'Test'
        );

        $single = new Single($wpRootDir, $wpRootDir . '/wp-config.php', $db);

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
        $db = new Db($dbName, $dbUser, $dbPassword, $dbHost);
        $wpRootDir = FS::tmpDir('single_');
        Installation::scaffold($wpRootDir, '6.1.1')->configure($db)->install(
            'https://wp.local',
            'admin',
            'password',
            'admin@wp.local',
            'Test'
        );

        $single = new Single($wpRootDir, $wpRootDir . '/wp-config.php', $db);

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
        $db = new Db($dbName, $dbUser, $dbPassword, $dbHost);
        $wpRootDir = FS::tmpDir('single_');
        Installation::scaffold($wpRootDir, '6.1.1')->configure($db)->install(
            'https://wp.local',
            'admin',
            'password',
            'admin@wp.local',
            'Test'
        );
        $wpConfigFilePath = $wpRootDir . '/wp-config.php';

        $single = new Single($wpRootDir, $wpConfigFilePath, $db);


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
        $db = new Db($dbName, $dbUser, $dbPassword, $dbHost);
        $wpRootDir = FS::tmpDir('single_');
        Installation::scaffold($wpRootDir, '6.1.1')->configure($db)->install(
            'https://wp.local',
            'admin',
            'password',
            'admin@wp.local',
            'Test'
        );
        $wpConfigFilePath = $wpRootDir . '/wp-config.php';

        $single = new Single($wpRootDir, $wpConfigFilePath, $db);


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
        $db = new Db($dbName, $dbUser, $dbPassword, $dbHost);
        $wpRootDir = FS::tmpDir('single_');
        Installation::scaffold($wpRootDir, '6.1.1')->configure($db)->install(
            'https://wp.local',
            'admin',
            'password',
            'admin@wp.local',
            'Test'
        );

        $single = new Single($wpRootDir, $wpRootDir . '/wp-config.php', $db);

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
        $db = new Db($dbName, $dbUser, $dbPassword, $dbHost);
        $wpRootDir = FS::tmpDir('single_');
        Installation::scaffold($wpRootDir, '6.1.1')->configure($db)->install(
            'https://wp.local',
            'admin',
            'password',
            'admin@wp.local',
            'Test'
        );

        $single = new Single($wpRootDir, $wpRootDir . '/wp-config.php', $db);

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
        $db = new Db($dbName, $dbUser, $dbPassword, $dbHost, 'test_');
        Installation::scaffold($wpRootDir, '6.1.1')->configure($db)->install(
            'https://wp.local',
            'admin',
            'password',
            'admin@wp.local',
            'Test'
        );

        $single = new Single($wpRootDir, $wpRootDir . '/wp-config.php', $db);

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
        $db = new Db($dbName, $dbUser, $dbPassword, $dbHost, 'test_');
        Installation::scaffold($wpRootDir, '6.1.1')->configure($db)->install(
            'https://wp.local',
            'admin',
            'password',
            'admin@wp.local',
            'Test');

        $single = new Single($wpRootDir, $wpRootDir . '/wp-config.php', $db);

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
        $db = new Db($dbName, $dbUser, $dbPassword, $dbHost, 'test_');
        Installation::scaffold($wpRootDir, '6.1.1')->configure($db)->install(
            'https://wp.local',
            'admin',
            'password',
            'admin@wp.local',
            'Test');

        $single = new Single($wpRootDir, $wpRootDir . '/wp-config.php', $db);
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
        $db = new Db($dbName, $dbUser, $dbPassword, $dbHost, 'test_');
        Installation::scaffold($wpRootDir, '6.1.1')->configure($db)->install(
            'https://wp.local',
            'admin',
            'password',
            'admin@wp.local',
            'Test');

        $single = new Single($wpRootDir, $wpRootDir . '/wp-config.php', $db);
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
        $db = new Db($dbName, $dbUser, $dbPassword, $dbHost, 'test_');
        Installation::scaffold($wpRootDir, '6.1.1')
            ->configure($db)
            ->install(
                'https://wp.local',
                'admin',
                'password',
                'admin@wp.local',
                'Test');

        $single = new Single($wpRootDir, $wpRootDir . '/wp-config.php', $db);

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
        $db = new Db($dbName, $dbUser, $dbPassword, $dbHost, 'test_');
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

        $single = new Single($wpRootDir, $wpRootDir . '/wp-config.php', $db);

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
        $db = new Db($dbName, $dbUser, $dbPassword, $dbHost, 'test_');
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

        $single = new Single($wpRootDir, $wpRootDir . '/wp-config.php', $db);

        $this->assertEquals($wpRootDir . '/site-plugins', $single->getPluginsDir());
        $this->assertEquals($wpRootDir . '/site-plugins/plugin-1.php', $single->getPluginsDir('plugin-1.php'));
        $this->assertEquals($wpRootDir . '/site-plugins/test-plugin', $single->getPluginsDir('test-plugin'));
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
        $db = new Db($dbName, $dbUser, $dbPassword, $dbHost, 'test_');
        $configurationData = new ConfigurationData();
        Installation::scaffold($wpRootDir, '6.1.1')
            ->configure($db, InstallationStateInterface::SINGLE_SITE, $configurationData)
            ->install(
                'https://wp.local',
                'admin',
                'password',
                'admin@wp.local',
                'Test');

        $single = new Single($wpRootDir, $wpRootDir . '/wp-config.php', $db);

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
        $db = new Db($dbName, $dbUser, $dbPassword, $dbHost, 'test_');
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

        $single = new Single($wpRootDir, $wpRootDir . '/wp-config.php', $db);

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
        $db = new Db($dbName, $dbUser, $dbPassword, $dbHost, 'test_');
        $configurationData = new ConfigurationData();
        Installation::scaffold($wpRootDir, '6.1.1')
            ->configure($db, InstallationStateInterface::SINGLE_SITE, $configurationData)
            ->install(
                'https://wp.local',
                'admin',
                'password',
                'admin@wp.local',
                'Test');

        $single = new Single($wpRootDir, $wpRootDir . '/wp-config.php', $db);

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
        $db = new Db($dbName, $dbUser, $dbPassword, $dbHost, 'test_');
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

        $single = new Single($wpRootDir, $wpRootDir . '/wp-config.php', $db);

        $this->assertEquals($wpRootDir . '/site-content', $single->getContentDir());
        $this->assertEquals($wpRootDir . '/site-content/some-file.php', $single->getContentDir('some-file.php'));
        $this->assertEquals($wpRootDir . '/site-content/some/path', $single->getContentDir('/some/path'));
    }
}

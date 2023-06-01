<?php


namespace lucatume\WPBrowser\WordPress\InstallationState;

use Codeception\Test\Unit;
use lucatume\WPBrowser\Exceptions\InvalidArgumentException;
use lucatume\WPBrowser\Tests\Traits\UopzFunctions;
use lucatume\WPBrowser\Utils\Env;
use lucatume\WPBrowser\Utils\Random;
use lucatume\WPBrowser\WordPress\ConfigurationData;
use lucatume\WPBrowser\WordPress\Db;
use lucatume\WPBrowser\WordPress\Installation;
use lucatume\WPBrowser\WordPress\InstallationException;
use lucatume\WPBrowser\Utils\Filesystem as FS;

class MultisiteTest extends Unit
{
    use UopzFunctions;

    /**
     * It should throw if trying to build on missing root directory
     *
     * @test
     */
    public function should_throw_if_trying_to_build_on_missing_root_directory(): void
    {
        $dbName = Random::dbName();
        $dbHost = Env::get('WORDPRESS_DB_HOST');
        $dbUser = Env::get('WORDPRESS_DB_USER');
        $dbPassword = Env::get('WORDPRESS_DB_PASSWORD');
        $db = new Db($dbName, $dbUser, $dbPassword, $dbHost);

        $this->expectException(InstallationException::class);
        $this->expectExceptionCode(InstallationException::ROOT_DIR_NOT_FOUND);

        new Multisite('/non-existing-dir', '/non-existing-dir/wp-config.php');
    }

    /**
     * It should throw if trying to build on empty root directory
     *
     * @test
     */
    public function should_throw_if_trying_to_build_on_empty_root_directory(): void
    {
        $dbName = Random::dbName(10);
        $dbHost = Env::get('WORDPRESS_DB_HOST');
        $dbUser = Env::get('WORDPRESS_DB_USER');
        $dbPassword = Env::get('WORDPRESS_DB_PASSWORD');
        $db = new Db($dbName, $dbUser, $dbPassword, $dbHost);
        $wpRootDir = FS::tmpDir('multisite_');

        $this->expectException(InstallationException::class);
        $this->expectExceptionCode(InstallationException::STATE_EMPTY);

        new Multisite($wpRootDir, $wpRootDir . '/wp-config.php');
    }

    /**
     * It should throw if building specified wp-config.php file does not exist
     *
     * @test
     */
    public function should_throw_if_building_specified_wp_config_php_file_does_not_exist(): void
    {
        $dbName = Random::dbName(10);
        $dbHost = Env::get('WORDPRESS_DB_HOST');
        $dbUser = Env::get('WORDPRESS_DB_USER');
        $dbPassword = Env::get('WORDPRESS_DB_PASSWORD');
        $db = new Db($dbName, $dbUser, $dbPassword, $dbHost);
        $wpRootDir = FS::tmpDir('multisite_');
        Installation::scaffold($wpRootDir, '6.1.1')
            ->configure($db)
            ->install(
                'https://wp.local',
                'admin',
                'password',
                'admin@wp.local',
                'Test'
            )
            ->convertToMultisite();
        unlink($wpRootDir . '/wp-config.php');

        $this->expectException(InstallationException::class);
        $this->expectExceptionCode(InstallationException::WP_CONFIG_FILE_NOT_FOUND);

        new Multisite($wpRootDir, $wpRootDir . '/wp-config.php');
    }

    /**
     * It should throw if trying to build on site not installed as multisite
     *
     * @test
     */
    public function should_throw_if_trying_to_build_on_site_not_installed_as_multisite()
    {
        $dbName = Random::dbName(10);
        $dbHost = Env::get('WORDPRESS_DB_HOST');
        $dbUser = Env::get('WORDPRESS_DB_USER');
        $dbPassword = Env::get('WORDPRESS_DB_PASSWORD');
        $db = new Db($dbName, $dbUser, $dbPassword, $dbHost);
        $wpRootDir = FS::tmpDir('multisite_');
        $installation = Installation::scaffold($wpRootDir, '6.1.1')
            ->configure($db)
            ->install(
                'https://wp.local',
                'admin',
                'password',
                'admin@wp.local',
                'Test'
            );

        $this->expectException(InstallationException::class);
        $this->expectExceptionCode(InstallationException::STATE_SINGLE);

        new Multisite($wpRootDir, $wpRootDir . '/wp-config.php');
    }

    /**
     * It should throw if building on not installed multisite
     *
     * @test
     */
    public function should_throw_if_building_on_not_installed_multisite(): void
    {
        $dbName = Random::dbName(10);
        $dbHost = Env::get('WORDPRESS_DB_HOST');
        $dbUser = Env::get('WORDPRESS_DB_USER');
        $dbPassword = Env::get('WORDPRESS_DB_PASSWORD');
        $db = new Db($dbName, $dbUser, $dbPassword, $dbHost);
        $wpRootDir = FS::tmpDir('multisite_');

        Installation::scaffold($wpRootDir, '6.1.1')
            ->configure($db, InstallationStateInterface::MULTISITE_SUBDOMAIN)
            ->install(
                'https://wp.local',
                'admin',
                'password',
                'admin@wp.local',
                'Test'
            );

        // Remove the blogs table from the database.
        $db->query("DROP TABLE {$db->getTablePrefix()}blogs");

        $this->expectException(InstallationException::class);
        $this->expectExceptionCode(InstallationException::NOT_INSTALLED);

        new Multisite($wpRootDir, $wpRootDir . '/wp-config.php');
    }

    /**
     * It should throw if trying to scaffold, install, configure and convert to multisite
     *
     * @test
     */
    public function should_throw_if_trying_to_scaffold_install_configure_and_convert_to_multisite(): void
    {
        $dbName = Random::dbName(10);
        $dbHost = Env::get('WORDPRESS_DB_HOST');
        $dbUser = Env::get('WORDPRESS_DB_USER');
        $dbPassword = Env::get('WORDPRESS_DB_PASSWORD');
        $db = new Db($dbName, $dbUser, $dbPassword, $dbHost);
        $wpRootDir = FS::tmpDir('multisite_');
        Installation::scaffold($wpRootDir, '6.1.1')
            ->configure($db)
            ->install(
                'https://wp.local',
                'admin',
                'password',
                'admin@wp.local',
                'Test'
            )
            ->convertToMultisite();

        $multisite = new Multisite($wpRootDir, $wpRootDir . '/wp-config.php');

        $this->expectException(InstallationException::class);
        $this->expectExceptionCode(InstallationException::STATE_MULTISITE);

        $multisite->scaffold('6.1.1');

        $this->expectException(InstallationException::class);
        $this->expectExceptionCode(InstallationException::STATE_MULTISITE);

        $multisite->configure($db);

        $this->expectException(InstallationException::class);
        $this->expectExceptionCode(InstallationException::STATE_MULTISITE);

        $multisite->install(
            'https://wp.local',
            'admin',
            'password',
            'admin@wp.local',
            'Test'
        );

        $this->expectException(InstallationException::class);
        $this->expectExceptionCode(InstallationException::STATE_MULTISITE);

        $multisite->convertToMultisite();
    }

    /**
     * It should allow fetching information from the installation
     *
     * @test
     */
    public function should_allow_fetching_information_from_the_installation(): void
    {
        $wpRootDir = FS::tmpDir('multisite_');
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
        )->convertToMultisite();

        $multisite = new Multisite($wpRootDir, $wpRootDir . '/wp-config.php');

        $this->assertTrue($multisite->isMultisite());
        $this->assertEquals($wpRootDir . '/', $multisite->getWpRootDir());
        $this->assertEquals($wpRootDir . '/wp-config.php', $multisite->getWpRootDir('wp-config.php'));
        $this->assertEquals($wpRootDir . '/wp-config.php', $multisite->getWpRootDir('/wp-config.php'));
        $this->assertEquals($wpRootDir . '/wp-config.php', $multisite->getWpConfigPath());
        $this->assertTrue(strlen($multisite->getAuthKey()) === 64 && $multisite->getAuthKey() !== $multisite->getSecureAuthKey());
        $this->assertTrue(strlen($multisite->getSecureAuthKey()) === 64 && $multisite->getSecureAuthKey() !== $multisite->getLoggedInKey());
        $this->assertTrue(strlen($multisite->getLoggedInKey()) === 64 && $multisite->getLoggedInKey() !== $multisite->getNonceKey());
        $this->assertTrue(strlen($multisite->getNonceKey()) === 64 && $multisite->getNonceKey() !== $multisite->getAuthSalt());
        $this->assertTrue(strlen($multisite->getAuthSalt()) === 64 && $multisite->getAuthSalt() !== $multisite->getSecureAuthSalt());
        $this->assertTrue(strlen($multisite->getSecureAuthSalt()) === 64 && $multisite->getSecureAuthSalt() !== $multisite->getLoggedInSalt());
        $this->assertTrue(strlen($multisite->getLoggedInSalt()) === 64 && $multisite->getLoggedInSalt() !== $multisite->getNonceSalt());
        $this->assertSame(64, strlen($multisite->getNonceSalt()));
        $this->assertEquals('test_', $multisite->getTablePrefix());
        $this->assertTrue($multisite->isConfigured());
        $this->assertEquals([
            'AUTH_KEY' => $multisite->getAuthKey(),
            'SECURE_AUTH_KEY' => $multisite->getSecureAuthKey(),
            'LOGGED_IN_KEY' => $multisite->getLoggedInKey(),
            'NONCE_KEY' => $multisite->getNonceKey(),
            'AUTH_SALT' => $multisite->getAuthSalt(),
            'SECURE_AUTH_SALT' => $multisite->getSecureAuthSalt(),
            'LOGGED_IN_SALT' => $multisite->getLoggedInSalt(),
            'NONCE_SALT' => $multisite->getNonceSalt(),
        ], $multisite->getSalts());
    }

    /**
     * It should allow getting the db
     *
     * @test
     */
    public function should_allow_getting_the_db(): void
    {
        $wpRootDir = FS::tmpDir('multisite_');
        $dbName = Random::dbName();
        $dbHost = Env::get('WORDPRESS_DB_HOST');
        $dbUser = Env::get('WORDPRESS_DB_USER');
        $dbPassword = Env::get('WORDPRESS_DB_PASSWORD');
        $db = new Db($dbName, $dbUser, $dbPassword, $dbHost, 'test_');
        Installation::scaffold($wpRootDir, '6.1.1')->configure(
            $db,
            InstallationStateInterface::MULTISITE_SUBFOLDER
        )->install(
            'https://wp.local',
            'admin',
            'password',
            'admin@wp.local',
            'Test'
        );

        $multisite = new Multisite($wpRootDir, $wpRootDir . '/wp-config.php');

        $this->assertEquals($dbName, $multisite->getDb()->getDbName());
        $this->assertEquals($dbHost, $multisite->getDb()->getDbHost());
        $this->assertEquals($dbUser, $multisite->getDb()->getDbUser());
        $this->assertEquals($dbPassword, $multisite->getDb()->getDbPassword());
    }

    /**
     * It should allow getting the site constants
     *
     * @test
     */
    public function should_allow_getting_the_site_constants(): void
    {
        $wpRootDir = FS::tmpDir('multisite_');
        $dbName = Random::dbName();
        $dbHost = Env::get('WORDPRESS_DB_HOST');
        $dbUser = Env::get('WORDPRESS_DB_USER');
        $dbPassword = Env::get('WORDPRESS_DB_PASSWORD');
        $db = new Db($dbName, $dbUser, $dbPassword, $dbHost, 'test_');
        Installation::scaffold($wpRootDir, '6.1.1')->configure(
            $db,
            InstallationStateInterface::MULTISITE_SUBFOLDER
        )->install(
            'https://wp.local',
            'admin',
            'password',
            'admin@wp.local',
            'Test'
        );

        $multisite = new Multisite($wpRootDir, $wpRootDir . '/wp-config.php');
        $constants = $multisite->getConstants();

        $expected = [
            'DB_NAME' => $dbName,
            'DB_USER' => $dbUser,
            'DB_PASSWORD' => $dbPassword,
            'DB_HOST' => $dbHost,
            'DB_CHARSET' => 'utf8',
            'DB_COLLATE' => '',
            'AUTH_KEY' => $multisite->getAuthKey(),
            'SECURE_AUTH_KEY' => $multisite->getSecureAuthKey(),
            'LOGGED_IN_KEY' => $multisite->getLoggedInKey(),
            'NONCE_KEY' => $multisite->getNonceKey(),
            'AUTH_SALT' => $multisite->getAuthSalt(),
            'SECURE_AUTH_SALT' => $multisite->getSecureAuthSalt(),
            'LOGGED_IN_SALT' => $multisite->getLoggedInSalt(),
            'NONCE_SALT' => $multisite->getNonceSalt(),
            'WP_DEBUG' => false,
            'WP_ALLOW_MULTISITE' => true,
            'MULTISITE' => true,
            'SUBDOMAIN_INSTALL' => false,
            'DOMAIN_CURRENT_SITE' => null,
            'PATH_CURRENT_SITE' => '/',
            'SITE_ID_CURRENT_SITE' => 1,
            'BLOG_ID_CURRENT_SITE' => 1,
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
        $wpRootDir = FS::tmpDir('multisite_');
        $dbName = Random::dbName();
        $dbHost = Env::get('WORDPRESS_DB_HOST');
        $dbUser = Env::get('WORDPRESS_DB_USER');
        $dbPassword = Env::get('WORDPRESS_DB_PASSWORD');
        $db = new Db($dbName, $dbUser, $dbPassword, $dbHost, 'test_');
        Installation::scaffold($wpRootDir, '6.1.1')->configure(
            $db,
            InstallationStateInterface::MULTISITE_SUBFOLDER
        )->install(
            'https://wp.local',
            'admin',
            'password',
            'admin@wp.local',
            'Test'
        );

        $multisite = new Multisite($wpRootDir, $wpRootDir . '/wp-config.php');
        $globals = $multisite->getGlobals();

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
        $wpRootDir = FS::tmpDir('multisite_');
        $dbName = Random::dbName();
        $dbHost = Env::get('WORDPRESS_DB_HOST');
        $dbUser = Env::get('WORDPRESS_DB_USER');
        $dbPassword = Env::get('WORDPRESS_DB_PASSWORD');
        $db = new Db($dbName, $dbUser, $dbPassword, $dbHost, 'test_');
        Installation::scaffold($wpRootDir, '6.1.1')->configure(
            $db,
            InstallationStateInterface::MULTISITE_SUBFOLDER
        )->install(
            'https://wp.local',
            'admin',
            'password',
            'admin@wp.local',
            'Test'
        );

        $multisite = new Multisite($wpRootDir, $wpRootDir . '/wp-config.php');

        $this->assertEquals($wpRootDir . '/wp-content/plugins', $multisite->getPluginsDir());
    }

    /**
     * It should return plugin directory build from WP_CONTENT_DIR if set
     *
     * @test
     */
    public function should_return_plugin_directory_build_from_wp_content_dir_if_set(): void
    {
        $wpRootDir = FS::tmpDir('multisite_');
        $dbName = Random::dbName();
        $dbHost = Env::get('WORDPRESS_DB_HOST');
        $dbUser = Env::get('WORDPRESS_DB_USER');
        $dbPassword = Env::get('WORDPRESS_DB_PASSWORD');
        $db = new Db($dbName, $dbUser, $dbPassword, $dbHost, 'test_');
        $configurationData = (new ConfigurationData())
            ->setConst('WP_CONTENT_DIR', $wpRootDir . '/site-content');
        Installation::scaffold($wpRootDir, '6.1.1')->configure(
            $db,
            InstallationStateInterface::MULTISITE_SUBFOLDER,
            $configurationData
        )->install(
            'https://wp.local',
            'admin',
            'password',
            'admin@wp.local',
            'Test'
        );

        $multisite = new Multisite($wpRootDir, $wpRootDir . '/wp-config.php');

        $this->assertEquals($wpRootDir . '/site-content/plugins', $multisite->getPluginsDir());
    }

    /**
     * It should return plugin directory build from WP_PLUGIN_DIR if set
     *
     * @test
     */
    public function should_return_plugin_directory_build_from_wp_plugin_dir_if_set(): void
    {
        $wpRootDir = FS::tmpDir('multisite_');
        $dbName = Random::dbName();
        $dbHost = Env::get('WORDPRESS_DB_HOST');
        $dbUser = Env::get('WORDPRESS_DB_USER');
        $dbPassword = Env::get('WORDPRESS_DB_PASSWORD');
        $db = new Db($dbName, $dbUser, $dbPassword, $dbHost, 'test_');
        $configurationData = (new ConfigurationData())
            ->setConst('WP_PLUGIN_DIR', $wpRootDir . '/site-plugins');
        Installation::scaffold($wpRootDir, '6.1.1')->configure(
            $db,
            InstallationStateInterface::MULTISITE_SUBFOLDER,
            $configurationData
        )->install(
            'https://wp.local',
            'admin',
            'password',
            'admin@wp.local',
            'Test'
        );

        $multisite = new Multisite($wpRootDir, $wpRootDir . '/wp-config.php');

        $this->assertEquals($wpRootDir . '/site-plugins', $multisite->getPluginsDir());
        $this->assertEquals($wpRootDir . '/site-plugins/plugin-1.php', $multisite->getPluginsDir('plugin-1.php'));
        $this->assertEquals($wpRootDir . '/site-plugins/test-plugin', $multisite->getPluginsDir('test-plugin'));
    }

    /**
     * It should return themes directory
     *
     * @test
     */
    public function should_return_themes_directory(): void
    {
        $wpRootDir = FS::tmpDir('multisite_');
        $dbName = Random::dbName();
        $dbHost = Env::get('WORDPRESS_DB_HOST');
        $dbUser = Env::get('WORDPRESS_DB_USER');
        $dbPassword = Env::get('WORDPRESS_DB_PASSWORD');
        $db = new Db($dbName, $dbUser, $dbPassword, $dbHost, 'test_');
        Installation::scaffold($wpRootDir, '6.1.1')->configure(
            $db,
            InstallationStateInterface::MULTISITE_SUBFOLDER
        )->install(
            'https://wp.local',
            'admin',
            'password',
            'admin@wp.local',
            'Test'
        );

        $multisite = new Multisite($wpRootDir, $wpRootDir . '/wp-config.php');

        $this->assertEquals($wpRootDir . '/wp-content/themes', $multisite->getThemesDir());
        $this->assertEquals(
            $wpRootDir . '/wp-content/themes/some-file.php',
            $multisite->getThemesDir('some-file.php')
        );
        $this->assertEquals($wpRootDir . '/wp-content/themes/some-theme', $multisite->getThemesDir('some-theme'));
    }

    /**
     * It should return themes directory built from WP_CONTENT_DIR if set
     *
     * @test
     */
    public function should_return_themes_directory_built_from_wp_content_dir_if_set(): void
    {
        $wpRootDir = FS::tmpDir('multisite_');
        $dbName = Random::dbName();
        $dbHost = Env::get('WORDPRESS_DB_HOST');
        $dbUser = Env::get('WORDPRESS_DB_USER');
        $dbPassword = Env::get('WORDPRESS_DB_PASSWORD');
        $db = new Db($dbName, $dbUser, $dbPassword, $dbHost, 'test_');
        $configurationData = (new ConfigurationData())
            ->setConst('WP_CONTENT_DIR', $wpRootDir . '/site-content');
        Installation::scaffold($wpRootDir, '6.1.1')->configure(
            $db,
            InstallationStateInterface::MULTISITE_SUBFOLDER,
            $configurationData
        )->install(
            'https://wp.local',
            'admin',
            'password',
            'admin@wp.local',
            'Test'
        );

        $multisite = new Multisite($wpRootDir, $wpRootDir . '/wp-config.php');

        $this->assertEquals($wpRootDir . '/site-content/themes', $multisite->getThemesDir());
        $this->assertEquals(
            $wpRootDir . '/site-content/themes/some-file.php',
            $multisite->getThemesDir('some-file.php')
        );
        $this->assertEquals($wpRootDir . '/site-content/themes/some-theme', $multisite->getThemesDir('some-theme'));
    }

    /**
     * It should return content dir
     *
     * @test
     */
    public function should_return_content_dir(): void
    {
        $wpRootDir = FS::tmpDir('multisite_');
        $dbName = Random::dbName();
        $dbHost = Env::get('WORDPRESS_DB_HOST');
        $dbUser = Env::get('WORDPRESS_DB_USER');
        $dbPassword = Env::get('WORDPRESS_DB_PASSWORD');
        $db = new Db($dbName, $dbUser, $dbPassword, $dbHost, 'test_');
        $configurationData = (new ConfigurationData());
        Installation::scaffold($wpRootDir, '6.1.1')->configure(
            $db,
            InstallationStateInterface::MULTISITE_SUBFOLDER,
            $configurationData
        )->install(
            'https://wp.local',
            'admin',
            'password',
            'admin@wp.local',
            'Test'
        );

        $multisite = new Multisite($wpRootDir, $wpRootDir . '/wp-config.php');

        $this->assertEquals($wpRootDir . '/wp-content', $multisite->getContentDir());
        $this->assertEquals(
            $wpRootDir . '/wp-content/some-file.php',
            $multisite->getContentDir('some-file.php')
        );
        $this->assertEquals($wpRootDir . '/wp-content/some/path', $multisite->getContentDir('some/path'));
    }

    /**
     * It should return content directory build from the WP_CONTENT if set
     *
     * @test
     */
    public function should_return_content_directory_build_from_the_wp_content_if_set(): void
    {
        $wpRootDir = FS::tmpDir('multisite_');
        $dbName = Random::dbName();
        $dbHost = Env::get('WORDPRESS_DB_HOST');
        $dbUser = Env::get('WORDPRESS_DB_USER');
        $dbPassword = Env::get('WORDPRESS_DB_PASSWORD');
        $db = new Db($dbName, $dbUser, $dbPassword, $dbHost, 'test_');
        $configurationData = (new ConfigurationData())
            ->setConst('WP_CONTENT_DIR', $wpRootDir . '/site-content');
        Installation::scaffold($wpRootDir, '6.1.1')->configure(
            $db,
            InstallationStateInterface::MULTISITE_SUBFOLDER,
            $configurationData
        )->install(
            'https://wp.local',
            'admin',
            'password',
            'admin@wp.local',
            'Test'
        );

        $multisite = new Multisite($wpRootDir, $wpRootDir . '/wp-config.php');

        $this->assertEquals($wpRootDir . '/site-content', $multisite->getContentDir());
        $this->assertEquals(
            $wpRootDir . '/site-content/some-file.php',
            $multisite->getContentDir('some-file.php')
        );
        $this->assertEquals($wpRootDir . '/site-content/some/path', $multisite->getContentDir('some/path'));
    }

    /**
     * It should allow working with options
     *
     * @test
     */
    public function should_allow_working_with_options(): void
    {
        $wpRootDir = FS::tmpDir('multisite_');
        $dbName = Random::dbName();
        $dbHost = Env::get('WORDPRESS_DB_HOST');
        $dbUser = Env::get('WORDPRESS_DB_USER');
        $dbPassword = Env::get('WORDPRESS_DB_PASSWORD');
        $db = new Db($dbName, $dbUser, $dbPassword, $dbHost, 'test_');
        Installation::scaffold($wpRootDir, '6.1.1')->configure(
            $db,
            InstallationStateInterface::MULTISITE_SUBFOLDER
        )
            ->install(
                'https://wp.local',
                'admin',
                'password',
                'admin@wp.local',
                'Test'
            );

        $multisite = new Multisite($wpRootDir, $wpRootDir . '/wp-config.php');

        $this->assertEquals(1, $multisite->updateOption('foo', 'bar'));
        $this->assertEquals('bar', $db->getOption('foo'));
    }

    /**
     * It should throw if trying to execute a non static Closure in WordPress
     *
     * @test
     */
    public function should_throw_if_trying_to_execute_a_non_static_closure_in_word_press(): void
    {
        $wpRootDir = FS::tmpDir('multisite_');
        $dbName = Random::dbName();
        $dbHost = Env::get('WORDPRESS_DB_HOST');
        $dbUser = Env::get('WORDPRESS_DB_USER');
        $dbPassword = Env::get('WORDPRESS_DB_PASSWORD');
        $db = new Db($dbName, $dbUser, $dbPassword, $dbHost, 'test_');
        Installation::scaffold($wpRootDir, '6.1.1')->configure(
            $db,
            InstallationStateInterface::MULTISITE_SUBFOLDER
        )
            ->install(
                'https://wp.local',
                'admin',
                'password',
                'admin@wp.local',
                'Test'
            );

        $multisite = new Multisite($wpRootDir, $wpRootDir . '/wp-config.php');

        $this->expectException(InvalidArgumentException::class);

        $this->assertEquals('https://wp.local', $multisite->executeClosureInWordPress(function () {
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
        $wpRootDir = FS::tmpDir('multisite_');
        $dbName = Random::dbName();
        $dbHost = Env::get('WORDPRESS_DB_HOST');
        $dbUser = Env::get('WORDPRESS_DB_USER');
        $dbPassword = Env::get('WORDPRESS_DB_PASSWORD');
        $db = new Db($dbName, $dbUser, $dbPassword, $dbHost, 'test_');
        Installation::scaffold($wpRootDir, '6.1.1')->configure(
            $db,
            InstallationStateInterface::MULTISITE_SUBFOLDER
        )
            ->install(
                'https://wp.local',
                'admin',
                'password',
                'admin@wp.local',
                'Test'
            );

        $multisite = new Multisite($wpRootDir, $wpRootDir . '/wp-config.php');

        $this->assertEquals('https://wp.local', $multisite->executeClosureInWordPress(static function () {
            return get_option('siteurl');
        }));
    }
}

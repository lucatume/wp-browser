<?php


namespace lucatume\WPBrowser\WordPress\InstallationState;

use Codeception\Test\Unit;
use Exception;
use lucatume\WPBrowser\Tests\Traits\ClassStubs;
use lucatume\WPBrowser\Tests\Traits\TmpFilesCleanup;
use lucatume\WPBrowser\Tests\Traits\UopzFunctions;
use lucatume\WPBrowser\Utils\Env;
use lucatume\WPBrowser\Utils\Filesystem as FS;
use lucatume\WPBrowser\Utils\Random;
use lucatume\WPBrowser\WordPress\CodeExecution\CodeExecutionFactory;
use lucatume\WPBrowser\WordPress\CodeExecution\ExitAction;
use lucatume\WPBrowser\WordPress\CodeExecution\ThrowAction;
use lucatume\WPBrowser\WordPress\ConfigurationData;
use lucatume\WPBrowser\WordPress\Database\MysqlDatabase;
use lucatume\WPBrowser\WordPress\Installation;
use lucatume\WPBrowser\WordPress\InstallationException;

class ConfiguredTest extends Unit
{
    use UopzFunctions;
    use ClassStubs;
    use TmpFilesCleanup;

    /**
     * It should throw when building on non existing root directory
     *
     * @test
     */
    public function should_throw_when_building_on_non_existing_root_directory(): void
    {
        $this->expectException(InstallationException::class);
        $this->expectExceptionCode(InstallationException::ROOT_DIR_NOT_FOUND);

        new Configured('/non-existing-dir', '/non-existing-dir/wp-config.php');
    }

    /**
     * It should throw when building on empty root directory
     *
     * @test
     */
    public function should_throw_when_building_on_empty_root_directory(): void
    {
        $wpRootDir = Fs::tmpDir('configured_',);

        $this->expectException(InstallationException::class);
        $this->expectExceptionCode(InstallationException::STATE_EMPTY);

        new Configured($wpRootDir, $wpRootDir . '/wp-config.php');
    }

    /**
     * It should throw when building on scaffolded root directory
     *
     * @test
     */
    public function should_throw_when_building_on_scaffolded_root_directory(): void
    {
        $wpRootDir = Fs::tmpDir('configured_', [
            'wp-load.php' => '<?php echo "Hello there!";',
            'wp-settings.php' => '<?php echo "Hello there!";',
            'wp-config-sample.php' => '<?php echo "Hello there!";',
        ]);

        $this->expectException(InstallationException::class);
        $this->expectExceptionCode(InstallationException::WP_CONFIG_FILE_NOT_FOUND);

        new Configured($wpRootDir, $wpRootDir . '/wp-config.php');
    }

    /**
     * It should throw if wp-config.php file path does not point to wp-config.php file
     *
     * @test
     */
    public function should_throw_if_wp_config_php_file_path_does_not_point_to_wp_config_php_file(): void
    {

        $wpRootDir = Fs::tmpDir('configured_', [
            'wp-load.php' => '<?php echo "Hello there!";',
            'wp-settings.php' => '<?php echo "Hello there!";',
            'wp-config-sample.php' => '<?php echo "Hello there!";',
        ]);

        $this->expectException(InstallationException::class);
        $this->expectExceptionCode(InstallationException::WP_CONFIG_FILE_NOT_FOUND);

        new Configured($wpRootDir, dirname($wpRootDir) . '/wp-config.php');
    }

    /**
     * It should allow assessing multisite status from files
     *
     * @test
     */
    public function should_allow_assessing_multisite_status_from_files(): void
    {

        $singleRootDir = FS::tmpDir('configured_');
        $dbName = Random::dbName();
        $dbHost = Env::get('WORDPRESS_DB_HOST');
        $dbUser = Env::get('WORDPRESS_DB_USER');
        $dbPassword = Env::get('WORDPRESS_DB_PASSWORD');
        $db = new MysqlDatabase($dbName, $dbUser, $dbPassword, $dbHost);
        Installation::scaffold($singleRootDir)->configure($db);

        $configured = new Configured($singleRootDir, $singleRootDir . '/wp-config.php');

        $this->assertFalse($configured->isMultisite());

        $multisiteRootDir = Fs::tmpDir('configured_');
        Installation::scaffold($multisiteRootDir)->configure($db, InstallationStateInterface::MULTISITE_SUBDOMAIN);

        $configured = new Configured($multisiteRootDir, $multisiteRootDir . '/wp-config.php');

        $this->assertTrue($configured->isMultisite());
    }

    /**
     * It should throw when building on root directory missing wp-load.php file
     *
     * @test
     */
    public function should_throw_when_building_on_root_directory_missing_wp_load_php_file(): void
    {
        $wpRootDir = Fs::tmpDir('configured_', [
            'wp-settings.php' => '<?php echo "Hello there!";',
            'wp-config.php' => '<?php echo "Hello there!";',
        ]);
        $dbName = Random::dbName();
        $dbHost = Env::get('WORDPRESS_DB_HOST');
        $dbUser = Env::get('WORDPRESS_DB_USER');
        $dbPassword = Env::get('WORDPRESS_DB_PASSWORD');
        $db = new MysqlDatabase($dbName, $dbUser, $dbPassword, $dbHost);

        $this->expectException(InstallationException::class);
        $this->expectExceptionCode(InstallationException::STATE_EMPTY);

        new Configured($wpRootDir, dirname($wpRootDir) . '/wp-config.php');
    }

    /**
     * It should throw if trying to configure already configured installation
     *
     * @test
     */
    public function should_throw_if_trying_to_configure_already_configured_installation(): void
    {
        $wpRootDir = Fs::tmpDir('configured_');
        $dbName = Random::dbName();
        $dbHost = Env::get('WORDPRESS_DB_HOST');
        $dbUser = Env::get('WORDPRESS_DB_USER');
        $dbPassword = Env::get('WORDPRESS_DB_PASSWORD');
        $db = new MysqlDatabase($dbName, $dbUser, $dbPassword, $dbHost);
        Installation::scaffold($wpRootDir)->configure($db);

        $configured = new Configured($wpRootDir, $wpRootDir . '/wp-config.php');

        $this->expectException(InstallationException::class);
        $this->expectExceptionCode(InstallationException::STATE_CONFIGURED);

        $configured->configure($db);
    }

    /**
     * It should allow reading variables and constants defined in the wp-config.php file
     *
     * @test
     */
    public function should_allow_reading_variables_and_constants_defined_in_the_wp_config_php_file(): void
    {
        $wpRootDir = Fs::tmpDir('configured_');
        $dbName = Random::dbName();
        $dbHost = Env::get('WORDPRESS_DB_HOST');
        $dbUser = Env::get('WORDPRESS_DB_USER');
        $dbPassword = Env::get('WORDPRESS_DB_PASSWORD');
        $db = new MysqlDatabase($dbName, $dbUser, $dbPassword, $dbHost, 'test_');
        $configurationData = ConfigurationData::fromArray([
            'AUTH_KEY' => 'auth-key-salt',
            'SECURE_AUTH_KEY' => 'secure-auth-key-salt',
            'LOGGED_IN_KEY' => 'logged-in-key-salt',
            'NONCE_KEY' => 'nonce-key-salt',
            'AUTH_SALT' => 'auth-salt-salt',
            'SECURE_AUTH_SALT' => 'secure-auth-salt',
            'LOGGED_IN_SALT' => 'logged-in-salt',
            'NONCE_SALT' => 'nonce-salt-salt'
        ]);
        Installation::scaffold($wpRootDir)->configure($db, InstallationStateInterface::SINGLE_SITE, $configurationData);

        $configured = new Configured($wpRootDir, $wpRootDir . '/wp-config.php');

        $this->assertEquals('auth-key-salt', $configured->getAuthKey());
        $this->assertEquals('secure-auth-key-salt', $configured->getSecureAuthKey());
        $this->assertEquals('logged-in-key-salt', $configured->getLoggedInKey());
        $this->assertEquals('nonce-key-salt', $configured->getNonceKey());
        $this->assertEquals('auth-salt-salt', $configured->getAuthSalt());
        $this->assertEquals('secure-auth-salt', $configured->getSecureAuthSalt());
        $this->assertEquals('logged-in-salt', $configured->getLoggedInSalt());
        $this->assertEquals('nonce-salt-salt', $configured->getNonceSalt());
        $this->assertEquals('test_', $configured->getTablePrefix());
    }

    /**
     * It should throw when installation parameters are invalid
     *
     * @test
     */
    public function should_throw_when_installation_parameters_are_invalid(): void
    {
        $wpRootDir = Fs::tmpDir('configured_');
        $dbName = Random::dbName();
        $dbHost = Env::get('WORDPRESS_DB_HOST');
        $dbUser = Env::get('WORDPRESS_DB_USER');
        $dbPassword = Env::get('WORDPRESS_DB_PASSWORD');
        $db = new MysqlDatabase($dbName, $dbUser, $dbPassword, $dbHost, 'test_');
        Installation::scaffold($wpRootDir)->configure($db);

        $configured = new Configured($wpRootDir, $wpRootDir . '/wp-config.php');
        $defaultInstallationParameters = [
            'url' => 'https://wp.local',
            'adminUser' => 'admin',
            'adminPassword' => 'password',
            'adminEmail' => 'admin@wp.local',
            'title' => 'WP Local Installation'
        ];

        $badInputs = [
            'empty URL' => [['url' => ''], InstallationException::INVALID_URL],
            'bad URL' => [['url' => 'foo.bar:2389'], InstallationException::INVALID_URL],
            'not a URL, just a string' => [['url' => 'lorem dolor'], InstallationException::INVALID_URL],
            'empty admin username' => [['adminUser' => ''], InstallationException::INVALID_ADMIN_USERNAME],
            'admin username with quotes' => [
                ['adminUser' => '"theAdmin"'],
                InstallationException::INVALID_ADMIN_USERNAME
            ],
            'admin username with spaces' => [
                ['adminUser' => 'the admin'],
                InstallationException::INVALID_ADMIN_USERNAME
            ],
            'empty admin password' => [['adminPassword' => ''], InstallationException::INVALID_ADMIN_PASSWORD],
            'empty admin email' => [['adminEmail' => ''], InstallationException::INVALID_ADMIN_EMAIL],
            'not an email' => [['adminEmail' => 'not_an_email'], InstallationException::INVALID_ADMIN_EMAIL],
            'missing email domain' => [['adminEmail' => 'luca@'], InstallationException::INVALID_ADMIN_EMAIL],
            'missing email name' => [
                ['adminEmail' => '@theAverageDev.com'],
                InstallationException::INVALID_ADMIN_EMAIL
            ],
            'empty title' => [['title' => ''], InstallationException::INVALID_TITLE],
        ];

        foreach ($badInputs as [$badInput, $expectedExceptionCode]) {
            $installationParameters = array_values(array_replace($defaultInstallationParameters, $badInput));

            $this->expectException(InstallationException::class);
            $this->expectExceptionCode($expectedExceptionCode);

            $configured->install(...$installationParameters);
        }
    }

    /**
     * It should allow installing single site installation
     *
     * @test
     */
    public function should_allow_installing_single_site_installation(): void
    {
        $wpRootDir = Fs::tmpDir('configured_');
        $dbName = Random::dbName();
        $dbHost = Env::get('WORDPRESS_DB_HOST');
        $dbUser = Env::get('WORDPRESS_DB_USER');
        $dbPassword = Env::get('WORDPRESS_DB_PASSWORD');
        $db = new MysqlDatabase($dbName, $dbUser, $dbPassword, $dbHost, 'test_');
        Installation::scaffold($wpRootDir)->configure($db);

        $configured = new Configured($wpRootDir, $wpRootDir . '/wp-config.php');
        $installed = $configured->install('https://wp.local',
            'admin',
            'password',
            'admin@wp.local',
            'WP Local Installation');

        $this->assertInstanceOf(Single::class, $installed);
    }

    /**
     * It should allow installing multisite installation
     *
     * @test
     */
    public function should_allow_installing_multisite_installation(): void
    {
        $wpRootDir = Fs::tmpDir('configured_');
        $dbName = Random::dbName();
        $dbHost = Env::get('WORDPRESS_DB_HOST');
        $dbUser = Env::get('WORDPRESS_DB_USER');
        $dbPassword = Env::get('WORDPRESS_DB_PASSWORD');
        $db = new MysqlDatabase($dbName, $dbUser, $dbPassword, $dbHost, 'test_');
        Installation::scaffold($wpRootDir)->configure($db, InstallationStateInterface::MULTISITE_SUBFOLDER);

        $configured = new Configured($wpRootDir, $wpRootDir . '/wp-config.php');
        $installed = $configured->install('https://wp.local',
            'admin',
            'password',
            'admin@wp.local',
            'WP Local Installation');

        $this->assertInstanceOf(Multisite::class, $installed);
    }

    /**
     * It should throw if installation request fails with output
     *
     * @test
     */
    public function should_throw_if_installation_request_fails_with_output(): void
    {
        $wpRootDir = Fs::tmpDir('configured_');
        $dbName = Random::dbName();
        $dbHost = Env::get('WORDPRESS_DB_HOST');
        $dbUser = Env::get('WORDPRESS_DB_USER');
        $dbPassword = Env::get('WORDPRESS_DB_PASSWORD');
        $db = new MysqlDatabase($dbName, $dbUser, $dbPassword, $dbHost, 'test_');
        Installation::scaffold($wpRootDir)->configure($db);

        $configured = new Configured($wpRootDir, $wpRootDir . '/wp-config.php');

        $this->uopzSetStaticMethodReturn(CodeExecutionFactory::class,
            'toInstallWordPress',
            (new ExitAction(1, 'errors occurred'))->getClosure());

        $this->expectException(InstallationException::class);
        $this->expectExceptionCode(InstallationException::INSTALLATION_FAIL);
        $this->expectExceptionMessageMatches('/errors occurred/');

        $configured->install('https://wp.local',
            'admin',
            'password',
            'admin@wp.local',
            'WP Local Installation');
    }

    /**
     * It should throw if installation request fails with throwable
     *
     * @test
     */
    public function should_throw_if_installation_request_fails_with_throwable(): void
    {
        $wpRootDir = Fs::tmpDir('configured_');
        $dbName = Random::dbName();
        $dbHost = Env::get('WORDPRESS_DB_HOST');
        $dbUser = Env::get('WORDPRESS_DB_USER');
        $dbPassword = Env::get('WORDPRESS_DB_PASSWORD');
        $db = new MysqlDatabase($dbName, $dbUser, $dbPassword, $dbHost, 'test_');
        Installation::scaffold($wpRootDir)->configure($db);

        $configured = new Configured($wpRootDir, $wpRootDir . '/wp-config.php');

        $this->uopzSetStaticMethodReturn(CodeExecutionFactory::class,
            'toInstallWordPress',
            (new ThrowAction(new Exception('Something is amiss')))->getClosure());

        $this->expectException(InstallationException::class);
        $this->expectExceptionCode(InstallationException::INSTALLATION_FAIL);
        $this->expectExceptionMessageMatches('/Something is amiss/');

        $configured->install('https://wp.local',
            'admin',
            'password',
            'admin@wp.local',
            'test');
    }

    /**
     * It should throw if trying to convert to multisite
     *
     * @test
     */
    public function should_throw_if_trying_to_convert_to_multisite(): void
    {
        $wpRootDir = FS::tmpDir('configured_');
        $dbName = Random::dbName();
        $dbHost = Env::get('WORDPRESS_DB_HOST');
        $dbUser = Env::get('WORDPRESS_DB_USER');
        $dbPassword = Env::get('WORDPRESS_DB_PASSWORD');
        $db = new MysqlDatabase($dbName, $dbUser, $dbPassword, $dbHost, 'test_');
        Installation::scaffold($wpRootDir)->configure($db);

        $configured = new Configured($wpRootDir, $wpRootDir . '/wp-config.php');

        $this->expectException(InstallationException::class);
        $this->expectExceptionCode(InstallationException::STATE_CONFIGURED);

        $configured->convertToMultisite();
    }

    /**
     * It should throw if trying to scaffold
     *
     * @test
     */
    public function should_throw_if_trying_to_scaffold(): void
    {
        $wpRootDir = FS::tmpDir('configured_');
        $dbName = Random::dbName();
        $dbHost = Env::get('WORDPRESS_DB_HOST');
        $dbUser = Env::get('WORDPRESS_DB_USER');
        $dbPassword = Env::get('WORDPRESS_DB_PASSWORD');
        $db = new MysqlDatabase($dbName, $dbUser, $dbPassword, $dbHost, 'test_');
        Installation::scaffold($wpRootDir)->configure($db);

        $configured = new Configured($wpRootDir, $wpRootDir . '/wp-config.php');

        $this->expectException(InstallationException::class);
        $this->expectExceptionCode(InstallationException::STATE_CONFIGURED);

        $configured->scaffold();
    }

    /**
     * It should allow getting information about the installation
     *
     * @test
     */
    public function should_allow_getting_information_about_the_installation(): void
    {
        $wpRootDir = FS::tmpDir('configured_');
        $dbName = Random::dbName();
        $dbHost = Env::get('WORDPRESS_DB_HOST');
        $dbUser = Env::get('WORDPRESS_DB_USER');
        $dbPassword = Env::get('WORDPRESS_DB_PASSWORD');
        $db = new MysqlDatabase($dbName, $dbUser, $dbPassword, $dbHost, 'test_');
        Installation::scaffold($wpRootDir)->configure($db);

        $configured = new Configured($wpRootDir, $wpRootDir . '/wp-config.php');

        $this->assertEquals($wpRootDir . '/', $configured->getWpRootDir());
        $this->assertEquals($wpRootDir . '/wp-config.php', $configured->getWpRootDir('wp-config.php'));
        $this->assertEquals($wpRootDir . '/wp-config.php', $configured->getWpRootDir('/wp-config.php'));
        $this->assertEquals($wpRootDir . '/wp-config.php', $configured->getWpConfigPath());
        $this->assertTrue(strlen($configured->getAuthKey()) === 64 && $configured->getAuthKey() !== $configured->getSecureAuthKey());
        $this->assertTrue(strlen($configured->getSecureAuthKey()) === 64 && $configured->getSecureAuthKey() !== $configured->getLoggedInKey());
        $this->assertTrue(strlen($configured->getLoggedInKey()) === 64 && $configured->getLoggedInKey() !== $configured->getNonceKey());
        $this->assertTrue(strlen($configured->getNonceKey()) === 64 && $configured->getNonceKey() !== $configured->getAuthSalt());
        $this->assertTrue(strlen($configured->getAuthSalt()) === 64 && $configured->getAuthSalt() !== $configured->getSecureAuthSalt());
        $this->assertTrue(strlen($configured->getSecureAuthSalt()) === 64 && $configured->getSecureAuthSalt() !== $configured->getLoggedInSalt());
        $this->assertTrue(strlen($configured->getLoggedInSalt()) === 64 && $configured->getLoggedInSalt() !== $configured->getNonceSalt());
        $this->assertSame(64, strlen($configured->getNonceSalt()));
        $this->assertEquals('test_', $configured->getTablePrefix());
        $this->assertTrue($configured->isConfigured());
        $this->assertEquals([
            'AUTH_KEY' => $configured->getAuthKey(),
            'SECURE_AUTH_KEY' => $configured->getSecureAuthKey(),
            'LOGGED_IN_KEY' => $configured->getLoggedInKey(),
            'NONCE_KEY' => $configured->getNonceKey(),
            'AUTH_SALT' => $configured->getAuthSalt(),
            'SECURE_AUTH_SALT' => $configured->getSecureAuthSalt(),
            'LOGGED_IN_SALT' => $configured->getLoggedInSalt(),
            'NONCE_SALT' => $configured->getNonceSalt(),
        ], $configured->getSalts());
        $this->assertEquals($dbName, $configured->getConstant('DB_NAME'));
        $this->assertEquals($dbHost, $configured->getConstant('DB_HOST'));
        $this->assertEquals($dbUser, $configured->getConstant('DB_USER'));
        $this->assertEquals($dbPassword, $configured->getConstant('DB_PASSWORD'));
    }

    /**
     * It should allow getting the db
     *
     * @test
     */
    public function should_allow_getting_the_db(): void
    {
        $wpRootDir = FS::tmpDir('configured_');
        $dbName = Random::dbName();
        $dbHost = Env::get('WORDPRESS_DB_HOST');
        $dbUser = Env::get('WORDPRESS_DB_USER');
        $dbPassword = Env::get('WORDPRESS_DB_PASSWORD');
        $db = new MysqlDatabase($dbName, $dbUser, $dbPassword, $dbHost, 'test_');
        Installation::scaffold($wpRootDir)->configure($db);

        $configured = new Configured($wpRootDir, $wpRootDir . '/wp-config.php');

        $this->assertEquals($dbName, $configured->getDb()->getDbName());
        $this->assertEquals($dbHost, $configured->getDb()->getDbHost());
        $this->assertEquals($dbUser, $configured->getDb()->getDbUser());
        $this->assertEquals($dbPassword, $configured->getDb()->getDbPassword());
    }

    /**
     * It should allow getting the site constants
     *
     * @test
     */
    public function should_allow_getting_the_site_constants(): void
    {
        $wpRootDir = FS::tmpDir('configured_');
        $dbName = Random::dbName();
        $dbHost = Env::get('WORDPRESS_DB_HOST');
        $dbUser = Env::get('WORDPRESS_DB_USER');
        $dbPassword = Env::get('WORDPRESS_DB_PASSWORD');
        $db = new MysqlDatabase($dbName, $dbUser, $dbPassword, $dbHost, 'test_');
        Installation::scaffold($wpRootDir)->configure($db);

        $configured = new Configured($wpRootDir, $wpRootDir . '/wp-config.php');
        $constants = $configured->getConstants();

        $expected = [
            'DB_NAME' => $dbName,
            'DB_USER' => $dbUser,
            'DB_PASSWORD' => $dbPassword,
            'DB_HOST' => $dbHost,
            'DB_CHARSET' => 'utf8',
            'DB_COLLATE' => '',
            'AUTH_KEY' => $configured->getAuthKey(),
            'SECURE_AUTH_KEY' => $configured->getSecureAuthKey(),
            'LOGGED_IN_KEY' => $configured->getLoggedInKey(),
            'NONCE_KEY' => $configured->getNonceKey(),
            'AUTH_SALT' => $configured->getAuthSalt(),
            'SECURE_AUTH_SALT' => $configured->getSecureAuthSalt(),
            'LOGGED_IN_SALT' => $configured->getLoggedInSalt(),
            'NONCE_SALT' => $configured->getNonceSalt(),
            'WP_DEBUG' => false,
            'ABSPATH' => $wpRootDir
        ];
        $this->assertCount(count($expected), $constants);
        foreach ($expected as $key => $expectedValue) {
            $this->assertArrayHasKey($key, $constants);
        }
    }

    /**
     * It should allow getting the installation globals
     *
     * @test
     */
    public function should_allow_getting_the_installation_globals(): void
    {
        $wpRootDir = FS::tmpDir('configured_');
        $dbName = Random::dbName();
        $dbHost = Env::get('WORDPRESS_DB_HOST');
        $dbUser = Env::get('WORDPRESS_DB_USER');
        $dbPassword = Env::get('WORDPRESS_DB_PASSWORD');
        $db = new MysqlDatabase($dbName, $dbUser, $dbPassword, $dbHost, 'test_');
        Installation::scaffold($wpRootDir)->configure($db);

        $configured = new Configured($wpRootDir, $wpRootDir . '/wp-config.php');
        $globals = $configured->getGlobals();

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
        $wpRootDir = FS::tmpDir('configured_');
        $dbName = Random::dbName();
        $dbHost = Env::get('WORDPRESS_DB_HOST');
        $dbUser = Env::get('WORDPRESS_DB_USER');
        $dbPassword = Env::get('WORDPRESS_DB_PASSWORD');
        $db = new MysqlDatabase($dbName, $dbUser, $dbPassword, $dbHost, 'test_');
        Installation::scaffold($wpRootDir)->configure($db);

        $configured = new Configured($wpRootDir, $wpRootDir . '/wp-config.php');
        $this->assertEquals($wpRootDir . '/wp-content/plugins', $configured->getPluginsDir());
    }

    /**
     * It should return plugins directory built from WP_CONTENT_DIR if set
     *
     * @test
     */
    public function should_return_plugins_directory_built_from_wp_content_dir_if_set(): void
    {
        $wpRootDir = FS::tmpDir('configured_');
        $dbName = Random::dbName();
        $dbHost = Env::get('WORDPRESS_DB_HOST');
        $dbUser = Env::get('WORDPRESS_DB_USER');
        $dbPassword = Env::get('WORDPRESS_DB_PASSWORD');
        $db = new MysqlDatabase($dbName, $dbUser, $dbPassword, $dbHost, 'test_');
        Installation::scaffold($wpRootDir)->configure($db,
            InstallationStateInterface::SINGLE_SITE,
            (new ConfigurationData())->setConst('WP_CONTENT_DIR', $wpRootDir . '/site-content'));

        $configured = new Configured($wpRootDir, $wpRootDir . '/wp-config.php');
        $this->assertEquals($wpRootDir . '/site-content/plugins', $configured->getPluginsDir());

    }

    /**
     * It should return plugins directory built from WP_PLUGIN_DIR if set
     *
     * @test
     */
    public function should_return_plugins_directory_built_from_wp_plugins_dir_if_set(): void
    {
        $wpRootDir = FS::tmpDir('configured_');
        $dbName = Random::dbName();
        $dbHost = Env::get('WORDPRESS_DB_HOST');
        $dbUser = Env::get('WORDPRESS_DB_USER');
        $dbPassword = Env::get('WORDPRESS_DB_PASSWORD');
        $db = new MysqlDatabase($dbName, $dbUser, $dbPassword, $dbHost, 'test_');
        Installation::scaffold($wpRootDir)->configure($db,
            InstallationStateInterface::SINGLE_SITE,
            (new ConfigurationData())->setConst('WP_PLUGIN_DIR', $wpRootDir . '/plugins'));

        $configured = new Configured($wpRootDir, $wpRootDir . '/wp-config.php');
        $this->assertEquals($wpRootDir . '/plugins', $configured->getPluginsDir());
    }

    /**
     * It should return themes directory
     *
     * @test
     */
    public function should_return_themes_directory(): void
    {
        $wpRootDir = FS::tmpDir('configured_');
        $dbName = Random::dbName();
        $dbHost = Env::get('WORDPRESS_DB_HOST');
        $dbUser = Env::get('WORDPRESS_DB_USER');
        $dbPassword = Env::get('WORDPRESS_DB_PASSWORD');
        $db = new MysqlDatabase($dbName, $dbUser, $dbPassword, $dbHost, 'test_');
        Installation::scaffold($wpRootDir)->configure($db);

        $configured = new Configured($wpRootDir, $wpRootDir . '/wp-config.php');
        $this->assertEquals($wpRootDir . '/wp-content/themes', $configured->getThemesDir());
        $this->assertEquals($wpRootDir . '/wp-content/themes/some-theme', $configured->getThemesDir('some-theme'));
    }

    /**
     * It should return themes directory build from WP_CONTENT_DIR if set
     *
     * @test
     */
    public function should_return_themes_directory_build_from_wp_content_dir_if_set(): void
    {
        $wpRootDir = FS::tmpDir('configured_');
        $dbName = Random::dbName();
        $dbHost = Env::get('WORDPRESS_DB_HOST');
        $dbUser = Env::get('WORDPRESS_DB_USER');
        $dbPassword = Env::get('WORDPRESS_DB_PASSWORD');
        $db = new MysqlDatabase($dbName, $dbUser, $dbPassword, $dbHost, 'test_');
        Installation::scaffold($wpRootDir)->configure($db,
            InstallationStateInterface::SINGLE_SITE,
            (new ConfigurationData())->setConst('WP_CONTENT_DIR', $wpRootDir . '/site-content'));

        $configured = new Configured($wpRootDir, $wpRootDir . '/wp-config.php');
        $this->assertEquals($wpRootDir . '/site-content/themes', $configured->getThemesDir());
        $this->assertEquals($wpRootDir . '/site-content/themes/some-theme', $configured->getThemesDir('some-theme'));
    }

    /**
     * It should return content directory
     *
     * @test
     */
    public function should_return_content_directory(): void
    {
        $wpRootDir = FS::tmpDir('configured_');
        $dbName = Random::dbName();
        $dbHost = Env::get('WORDPRESS_DB_HOST');
        $dbUser = Env::get('WORDPRESS_DB_USER');
        $dbPassword = Env::get('WORDPRESS_DB_PASSWORD');
        $db = new MysqlDatabase($dbName, $dbUser, $dbPassword, $dbHost, 'test_');
        Installation::scaffold($wpRootDir)->configure($db);

        $configured = new Configured($wpRootDir, $wpRootDir . '/wp-config.php');
        $this->assertEquals($wpRootDir . '/wp-content', $configured->getContentDir());
        $this->assertEquals($wpRootDir . '/wp-content/some/path', $configured->getContentDir('/some/path'));
        $this->assertEquals($wpRootDir . '/wp-content/some/file.php', $configured->getContentDir('/some/file.php'));
    }

    /**
     * It should return content directory build from WP_CONTENT_DIR if set
     *
     * @test
     */
    public function should_return_content_directory_build_from_wp_content_dir_if_set(): void
    {
        $wpRootDir = FS::tmpDir('configured_');
        $dbName = Random::dbName();
        $dbHost = Env::get('WORDPRESS_DB_HOST');
        $dbUser = Env::get('WORDPRESS_DB_USER');
        $dbPassword = Env::get('WORDPRESS_DB_PASSWORD');
        $db = new MysqlDatabase($dbName, $dbUser, $dbPassword, $dbHost, 'test_');
        Installation::scaffold($wpRootDir)->configure($db,
            InstallationStateInterface::SINGLE_SITE,
            (new ConfigurationData())->setConst('WP_CONTENT_DIR', $wpRootDir . '/site-content'));

        $configured = new Configured($wpRootDir, $wpRootDir . '/wp-config.php');
        $this->assertEquals($wpRootDir . '/site-content', $configured->getContentDir());
        $this->assertEquals($wpRootDir . '/site-content/some/path', $configured->getContentDir('/some/path'));
        $this->assertEquals($wpRootDir . '/site-content/some/file.php', $configured->getContentDir('/some/file.php'));
    }

    /**
     * It should throw if trying to update option
     *
     * @test
     */
    public function should_throw_if_trying_to_update_option(): void
    {
        $wpRootDir = FS::tmpDir('configured_');
        $dbName = Random::dbName();
        $dbHost = Env::get('WORDPRESS_DB_HOST');
        $dbUser = Env::get('WORDPRESS_DB_USER');
        $dbPassword = Env::get('WORDPRESS_DB_PASSWORD');
        $db = new MysqlDatabase($dbName, $dbUser, $dbPassword, $dbHost, 'test_');
        Installation::scaffold($wpRootDir)->configure($db);

        $configured = new Configured($wpRootDir, $wpRootDir . '/wp-config.php');

        $this->expectException(InstallationException::class);
        $this->expectExceptionCode(InstallationException::STATE_CONFIGURED);

        $configured->updateOption('foo', 'bar');
    }

    /**
     * It should throw if trying to execute Closure in WordPress
     *
     * @test
     */
    public function should_throw_if_trying_to_execute_closure_in_word_press(): void
    {
        $wpRootDir = FS::tmpDir('configured_');
        $dbName = Random::dbName();
        $dbHost = Env::get('WORDPRESS_DB_HOST');
        $dbUser = Env::get('WORDPRESS_DB_USER');
        $dbPassword = Env::get('WORDPRESS_DB_PASSWORD');
        $db = new MysqlDatabase($dbName, $dbUser, $dbPassword, $dbHost, 'test_');
        Installation::scaffold($wpRootDir)->configure($db);

        $configured = new Configured($wpRootDir, $wpRootDir . '/wp-config.php');

        $this->expectException(InstallationException::class);
        $this->expectExceptionCode(InstallationException::STATE_CONFIGURED);

        $configured->executeClosureInWordPress(function () {
            return 'foo';
        });
    }

    /**
     * It should return mu-plugins directory
     *
     * @test
     */
    public function should_return_mu_plugins_directory(): void
    {
        $wpRootDir = FS::tmpDir('configured_');
        $dbName = Random::dbName();
        $dbHost = Env::get('WORDPRESS_DB_HOST');
        $dbUser = Env::get('WORDPRESS_DB_USER');
        $dbPassword = Env::get('WORDPRESS_DB_PASSWORD');
        $db = new MysqlDatabase($dbName, $dbUser, $dbPassword, $dbHost, 'test_');
        Installation::scaffold($wpRootDir)->configure($db);

        $configured = new Configured($wpRootDir, $wpRootDir . '/wp-config.php');
        $this->assertEquals($wpRootDir . '/wp-content/mu-plugins', $configured->getMuPluginsDir());
    }

    /**
     * It should return mu-plugins directory built from WP_CONTENT_DIR if set
     *
     * @test
     */
    public function should_return_mu_plugins_directory_built_from_wp_content_dir_if_set(): void
    {
        $wpRootDir = FS::tmpDir('configured_');
        $dbName = Random::dbName();
        $dbHost = Env::get('WORDPRESS_DB_HOST');
        $dbUser = Env::get('WORDPRESS_DB_USER');
        $dbPassword = Env::get('WORDPRESS_DB_PASSWORD');
        $db = new MysqlDatabase($dbName, $dbUser, $dbPassword, $dbHost, 'test_');
        Installation::scaffold($wpRootDir)->configure($db,
            InstallationStateInterface::SINGLE_SITE,
            (new ConfigurationData())->setConst('WP_CONTENT_DIR', $wpRootDir . '/site-content'));

        $configured = new Configured($wpRootDir, $wpRootDir . '/wp-config.php');
        $this->assertEquals($wpRootDir . '/site-content/mu-plugins', $configured->getMuPluginsDir());

    }

    /**
     * It should return mu-plugins directory built from WP_PLUGIN_DIR if set
     *
     * @test
     */
    public function should_return_mu_plugins_directory_built_from_wp_plugins_dir_if_set(): void
    {
        $wpRootDir = FS::tmpDir('configured_');
        $dbName = Random::dbName();
        $dbHost = Env::get('WORDPRESS_DB_HOST');
        $dbUser = Env::get('WORDPRESS_DB_USER');
        $dbPassword = Env::get('WORDPRESS_DB_PASSWORD');
        $db = new MysqlDatabase($dbName, $dbUser, $dbPassword, $dbHost, 'test_');
        Installation::scaffold($wpRootDir)->configure($db,
            InstallationStateInterface::SINGLE_SITE,
            (new ConfigurationData())->setConst('WPMU_PLUGIN_DIR', $wpRootDir . '/mu-plugins'));

        $configured = new Configured($wpRootDir, $wpRootDir . '/wp-config.php');
        $this->assertEquals($wpRootDir . '/mu-plugins', $configured->getMuPluginsDir());
    }
}

<?php


namespace lucatume\WPBrowser\WordPress\InstallationState;

use lucatume\WPBrowser\Tests\Traits\UopzFunctions;
use lucatume\WPBrowser\Utils\Env;
use lucatume\WPBrowser\Utils\Random;
use lucatume\WPBrowser\WordPress\Db;
use lucatume\WPBrowser\WordPress\Installation;
use lucatume\WPBrowser\WordPress\InstallationException;
use lucatume\WPBrowser\Utils\Filesystem as FS;

class MultisiteTest extends \Codeception\Test\Unit
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

        new Multisite('/non-existing-dir', '/non-existing-dir/wp-config.php', $db);
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

        new Multisite($wpRootDir, $wpRootDir . '/wp-config.php', $db);
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

        new Multisite($wpRootDir, $wpRootDir . '/wp-config.php', $db);
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
        Installation::scaffold($wpRootDir, '6.1.1')
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

        new Multisite($wpRootDir, $wpRootDir . '/wp-config.php', $db);
    }

    /**
     * It should throw if trying to scaffold, install, configure and convert to multisitej
     *
     * @test
     */
    public function should_throw_if_trying_to_scaffold_install_configure_and_convert_to_multisitej(): void
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

        $multisite = new Multisite($wpRootDir, $wpRootDir . '/wp-config.php', $db);

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

        $multisite = new Multisite($wpRootDir, $wpRootDir . '/wp-config.php', $db);

        $this->assertTrue($multisite->isMultisite());
        $this->assertEquals($wpRootDir . '/', $multisite->getWpRootDir());
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
            'authKey' => $multisite->getAuthKey(),
            'secureAuthKey' => $multisite->getSecureAuthKey(),
            'loggedInKey' => $multisite->getLoggedInKey(),
            'nonceKey' => $multisite->getNonceKey(),
            'authSalt' => $multisite->getAuthSalt(),
            'secureAuthSalt' => $multisite->getSecureAuthSalt(),
            'loggedInSalt' => $multisite->getLoggedInSalt(),
            'nonceSalt' => $multisite->getNonceSalt(),
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
        Installation::scaffold($wpRootDir, '6.1.1')->configure($db,
            InstallationStateInterface::MULTISITE_SUBFOLDER)->install('https://wp.local',
            'admin',
            'password',
            'admin@wp.local',
            'Test');

        $multisite = new Multisite($wpRootDir, $wpRootDir . '/wp-config.php', $db);

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
        $wpRootDir = FS::tmpDir('configured_');
        $dbName = Random::dbName();
        $dbHost = Env::get('WORDPRESS_DB_HOST');
        $dbUser = Env::get('WORDPRESS_DB_USER');
        $dbPassword = Env::get('WORDPRESS_DB_PASSWORD');
        $db = new Db($dbName, $dbUser, $dbPassword, $dbHost, 'test_');
        Installation::scaffold($wpRootDir, '6.1.1')->configure($db,
            InstallationStateInterface::MULTISITE_SUBFOLDER)->install('https://wp.local',
            'admin',
            'password',
            'admin@wp.local',
            'Test');

        $multisite = new Multisite($wpRootDir, $wpRootDir . '/wp-config.php', $db);
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
        $wpRootDir = FS::tmpDir('configured_');
        $dbName = Random::dbName();
        $dbHost = Env::get('WORDPRESS_DB_HOST');
        $dbUser = Env::get('WORDPRESS_DB_USER');
        $dbPassword = Env::get('WORDPRESS_DB_PASSWORD');
        $db = new Db($dbName, $dbUser, $dbPassword, $dbHost, 'test_');
        Installation::scaffold($wpRootDir, '6.1.1')->configure($db,
            InstallationStateInterface::MULTISITE_SUBFOLDER)->install('https://wp.local',
            'admin',
            'password',
            'admin@wp.local',
            'Test');

        $multisite = new Multisite($wpRootDir, $wpRootDir . '/wp-config.php', $db);
        $globals = $multisite->getGlobals();

        $expected = [
            'table_prefix' => 'test_',
        ];
        $this->assertCount(count($expected), $globals);
        foreach ($expected as $key => $expectedValue) {
            $this->assertArrayHasKey($key, $globals);
        }
    }
}

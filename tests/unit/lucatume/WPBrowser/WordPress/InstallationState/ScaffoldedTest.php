<?php


namespace lucatume\WPBrowser\WordPress\InstallationState;

use Codeception\Test\Unit;
use lucatume\WPBrowser\Tests\Traits\TmpFilesCleanup;
use lucatume\WPBrowser\Utils\Env;
use lucatume\WPBrowser\Utils\Filesystem as FS;
use lucatume\WPBrowser\Utils\Random;
use lucatume\WPBrowser\WordPress\ConfigurationData;
use lucatume\WPBrowser\WordPress\Db;
use lucatume\WPBrowser\WordPress\Installation;
use lucatume\WPBrowser\WordPress\InstallationException;
use tad\Codeception\SnapshotAssertions\SnapshotAssertions;

class ScaffoldedTest extends Unit
{
    use SnapshotAssertions;
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

        new Scaffolded('/non-existing-dir');
    }

    /**
     * It should throw when built on non root directory
     *
     * @test
     */
    public function should_throw_when_built_on_empty_root_directory(): void
    {
        $wpRootDir = FS::tmpDir('scaffolded_');
        $this->expectException(InstallationException::class);
        $this->expectExceptionCode(InstallationException::STATE_EMPTY);

        new Scaffolded($wpRootDir);
    }

    /**
     * It should throw when built on configured root directory
     *
     * @test
     */
    public function should_throw_when_built_on_configured_root_directory(): void
    {
        $wpRootDir = FS::tmpDir('scaffolded_', [
            'wp-load.php' => '<?php echo "Hello there!";',
            'wp-settings.php' => '<?php echo "Hello there!";',
            'wp-config.php' => '<?php echo "Hello there!";'
        ]);
        $this->expectException(InstallationException::class);
        $this->expectExceptionCode(InstallationException::STATE_CONFIGURED);

        new Scaffolded($wpRootDir);
    }

    /**
     * It should throw when built on root directory missing wp-load.php file
     *
     * @test
     */
    public function should_throw_when_built_on_root_directory_missing_wp_load_php_file(): void
    {
        $wpRootDir = FS::tmpDir('scaffolded_', [
            'wp-settings.php' => '<?php echo "Hello there!";',
            'wp-config.php' => '<?php echo "Hello there!";'
        ]);

        $this->expectException(InstallationException::class);
        $this->expectExceptionCode(InstallationException::STATE_EMPTY);

        new Scaffolded($wpRootDir);
    }

    /**
     * It should allow getting information from the installation
     *
     * @test
     */
    public function should_allow_getting_information_from_the_installation(): void
    {
        $wpRootDir = FS::tmpDir('scaffolded_');
        Installation::scaffold($wpRootDir, '6.1.1');

        $state = new Scaffolded($wpRootDir);

        $this->assertEquals($wpRootDir . '/', $state->getWpRootDir());
        $this->assertEquals($wpRootDir . '/wp-config.php', $state->getWpRootDir('wp-config.php'));
        $this->assertEquals($wpRootDir . '/wp-config.php', $state->getWpRootDir('/wp-config.php'));
        $this->assertEquals('6.1.1', $state->getVersion()->getWpVersion());
    }

    /**
     * It should throw if trying to assess multisite configuration
     *
     * @test
     */
    public function should_throw_if_trying_to_assess_multisite_configuration(): void
    {
        $wpRootDir = FS::tmpDir('scaffolded_');
        Installation::scaffold($wpRootDir, '6.1.1');

        $state = new Scaffolded($wpRootDir);

        $this->expectException(InstallationException::class);
        $this->expectExceptionCode(InstallationException::STATE_SCAFFOLDED);
        $this->assertEquals($wpRootDir . '/', $state->isMultisite());
    }

    /**
     * It should throw if wp-config-sample.php file is not found during configuration
     *
     * @test
     */
    public function should_throw_if_wp_config_sample_php_file_is_not_found_during_configuration(): void
    {
        $wpRootDir = FS::tmpDir('scaffolded_');
        Installation::scaffold($wpRootDir, '6.1.1');
        $dbName = Random::dbName();
        $dbHost = Env::get('WORDPRESS_DB_HOST');
        $dbUser = Env::get('WORDPRESS_DB_USER');
        $dbPassword = Env::get('WORDPRESS_DB_PASSWORD');
        $db = new Db($dbName, $dbUser, $dbPassword, $dbHost);

        $scaffolded = new Scaffolded($wpRootDir);
        unlink($wpRootDir . '/wp-config-sample.php');

        $this->expectException(InstallationException::class);
        $this->expectExceptionCode(InstallationException::WP_CONFIG_SAMPLE_FILE_NOT_FOUND);

        $scaffolded->configure($db);
    }

    /**
     * It should allow configuring an installation
     *
     * @test
     */
    public function should_allow_configuring_an_installation(): void
    {
        $wpRootDir = FS::tmpDir('scaffolded_');
        Installation::scaffold($wpRootDir, '6.1.1');
        $dbName = Random::dbName();
        $dbHost = Env::get('WORDPRESS_DB_HOST');
        $dbUser = Env::get('WORDPRESS_DB_USER');
        $dbPassword = Env::get('WORDPRESS_DB_PASSWORD');
        $db = new Db($dbName, $dbUser, $dbPassword, $dbHost);

        $scaffolded = new Scaffolded($wpRootDir);
        $configured = $scaffolded->configure($db);

        $this->assertInstanceOf(Configured::class, $configured);
        $this->assertFalse($configured->isMultisite());
    }

    /**
     * It should allow configuring a multisite subdomain installation
     *
     * @test
     */
    public function should_allow_configuring_a_multisite_subdomain_installation(): void
    {
        $wpRootDir = FS::tmpDir('scaffolded_');
        Installation::scaffold($wpRootDir, '6.1.1');
        $dbName = Random::dbName();
        $dbHost = Env::get('WORDPRESS_DB_HOST');
        $dbUser = Env::get('WORDPRESS_DB_USER');
        $dbPassword = Env::get('WORDPRESS_DB_PASSWORD');
        $db = new Db($dbName, $dbUser, $dbPassword, $dbHost);

        $scaffolded = new Scaffolded($wpRootDir);
        $configured = $scaffolded->configure($db, InstallationStateInterface::MULTISITE_SUBDOMAIN);

        $this->assertInstanceOf(Configured::class, $configured);
        $this->assertTrue($configured->isMultisite());
        $this->assertTrue($configured->isSubdomainMultisite());
    }

    /**
     * It should allow configuring a multisite subfolder installation
     *
     * @test
     */
    public function should_allow_configuring_a_multisite_subfolder_installation(): void
    {
        $wpRootDir = FS::tmpDir('scaffolded_');
        Installation::scaffold($wpRootDir, '6.1.1');
        $dbName = Random::dbName();
        $dbHost = Env::get('WORDPRESS_DB_HOST');
        $dbUser = Env::get('WORDPRESS_DB_USER');
        $dbPassword = Env::get('WORDPRESS_DB_PASSWORD');
        $db = new Db($dbName, $dbUser, $dbPassword, $dbHost);

        $scaffolded = new Scaffolded($wpRootDir);
        $configured = $scaffolded->configure($db, InstallationStateInterface::MULTISITE_SUBFOLDER);

        $this->assertInstanceOf(Configured::class, $configured);
        $this->assertTrue($configured->isMultisite());
        $this->assertFalse($configured->isSubdomainMultisite());
    }

    /**
     * It should allow configuring an installation using custom configuration
     *
     * @test
     */
    public function should_allow_configuring_an_installation_using_custom_configuration(): void
    {
        $wpRootDir = FS::tmpDir('scaffolded_');
        Installation::scaffold($wpRootDir, '6.1.1');
        $dbName = 'fixed';
        $dbHost = Env::get('WORDPRESS_DB_HOST');
        $dbUser = Env::get('WORDPRESS_DB_USER');
        $dbPassword = Env::get('WORDPRESS_DB_PASSWORD');
        $db = new Db($dbName, $dbUser, $dbPassword, $dbHost);

        $scaffolded = new Scaffolded($wpRootDir);
        $extraPHP = <<< PHP
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_LOG_DISPLAY', true);
define('WP_HOME', 'https://' . \$_SERVER['HTTP_HOST']);
define('WP_SITEURL', 'https://' . \$_SERVER['HTTP_HOST']);
PHP;
        $configurationData = (new ConfigurationData())
            ->setAuthKey('auth-key-salt')
            ->setSecureAuthKey('secure-auth-key-salt')
            ->setLoggedInKey('logged-in-key-salt')
            ->setNonceKey('nonce-key-salt')
            ->setAuthSalt('auth-salt')
            ->setSecureAuthSalt('secure-auth-salt')
            ->setLoggedInSalt('logged-in-salt')
            ->setNonceSalt('nonce-salt')
            ->setExtraPHP($extraPHP);
        $configured = $scaffolded->configure($db, false, $configurationData);

        $this->assertInstanceOf(Configured::class, $configured);
        $this->assertEquals('auth-key-salt', $configured->getAuthKey());
        $this->assertEquals('secure-auth-key-salt', $configured->getSecureAuthKey());
        $this->assertEquals('logged-in-key-salt', $configured->getLoggedInKey());
        $this->assertEquals('nonce-key-salt', $configured->getNonceKey());
        $this->assertEquals('auth-salt', $configured->getAuthSalt());
        $this->assertEquals('secure-auth-salt', $configured->getSecureAuthSalt());
        $this->assertEquals('logged-in-salt', $configured->getLoggedInSalt());
        $this->assertEquals('nonce-salt', $configured->getNonceSalt());
        $this->assertFalse($configured->isMultisite());
        $this->assertMatchesCodeSnapshot(file_get_contents($configured->getWpConfigPath()));
    }

    /**
     * It should throw when trying to get salts
     *
     * @test
     */
    public function should_throw_when_trying_to_get_salts()
    {
        $wpRootDir = FS::tmpDir('empty-dir_');
        Installation::scaffold($wpRootDir, '6.1.1');

        $scaffolded = new Scaffolded($wpRootDir);

        $this->expectException(InstallationException::class);
        $this->expectExceptionCode(InstallationException::STATE_SCAFFOLDED);

        $scaffolded->getAuthKey();

        $this->expectException(InstallationException::class);
        $this->expectExceptionCode(InstallationException::STATE_SCAFFOLDED);

        $scaffolded->getSecureAuthKey();

        $this->expectException(InstallationException::class);
        $this->expectExceptionCode(InstallationException::STATE_SCAFFOLDED);

        $scaffolded->getLoggedInKey();

        $this->expectException(InstallationException::class);
        $this->expectExceptionCode(InstallationException::STATE_SCAFFOLDED);

        $scaffolded->getNonceKey();

        $this->expectException(InstallationException::class);
        $this->expectExceptionCode(InstallationException::STATE_SCAFFOLDED);

        $scaffolded->getAuthSalt();

        $this->expectException(InstallationException::class);
        $this->expectExceptionCode(InstallationException::STATE_SCAFFOLDED);

        $scaffolded->getSecureAuthSalt();

        $this->expectException(InstallationException::class);
        $this->expectExceptionCode(InstallationException::STATE_SCAFFOLDED);

        $scaffolded->getLoggedInSalt();

        $this->expectException(InstallationException::class);
        $this->expectExceptionCode(InstallationException::STATE_SCAFFOLDED);

        $scaffolded->getNonceSalt();

        $this->expectedException(InstallationException::class);
        $this->expectExceptionCode(InstallationException::STATE_SCAFFOLDED);

        $scaffolded->getSalts();
    }

    /**
     * It should throw when trying to get table prefix
     *
     * @test
     */
    public function should_throw_when_trying_to_get_table_prefix(): void
    {
        $wpRootDir = FS::tmpDir('scaffolded_');
        Installation::scaffold($wpRootDir, '6.1.1');

        $scaffolded = new Scaffolded($wpRootDir);

        $this->expectException(InstallationException::class);
        $this->expectExceptionCode(InstallationException::STATE_SCAFFOLDED);

        $scaffolded->getTablePrefix();
    }

    /**
     * It should throw if trying to install
     *
     * @test
     */
    public function should_throw_if_trying_to_install()
    {
        $wpRootDir = FS::tmpDir('scaffolded_');
        Installation::scaffold($wpRootDir, '6.1.1');

        $scaffolded = new Scaffolded($wpRootDir);

        $this->expectException(InstallationException::class);
        $this->expectExceptionCode(InstallationException::STATE_SCAFFOLDED);

        $scaffolded->install('http://wp.local', 'admin', 'password', 'admin@wp.local', 'Test');
    }

    /**
     * It should throw if trying to convert to multisite
     *
     * @test
     */
    public function should_throw_if_trying_to_convert_to_multisite(): void
    {
        $wpRootDir = FS::tmpDir('scaffolded_');
        Installation::scaffold($wpRootDir, '6.1.1');

        $scaffolded = new Scaffolded($wpRootDir);

        $this->expectException(InstallationException::class);
        $this->expectExceptionCode(InstallationException::STATE_SCAFFOLDED);

        $scaffolded->convertToMultisite();
    }

    /**
     * It should throw if tryng to scaffold again
     *
     * @test
     */
    public function should_throw_if_tryng_to_scaffold_again(): void
    {
        $wpRootDir = FS::tmpDir('scaffolded_');
        Installation::scaffold($wpRootDir, '6.1.1');

        $scaffolded = new Scaffolded($wpRootDir);

        $this->expectException(InstallationException::class);
        $this->expectExceptionCode(InstallationException::STATE_SCAFFOLDED);

        $scaffolded->scaffold();
    }

    /**
     * It should throw if trying to get the wp-config.php file path
     *
     * @test
     */
    public function should_throw_if_trying_to_get_the_wp_config_php_file_path(): void
    {
        $wpRootDir = FS::tmpDir('scaffolded_');
        Installation::scaffold($wpRootDir, '6.1.1');

        $scaffolded = new Scaffolded($wpRootDir);

        $this->expectException(InstallationException::class);
        $this->expectExceptionCode(InstallationException::STATE_SCAFFOLDED);

        $scaffolded->getWpConfigPath();
    }

    /**
     * It should not be configured
     *
     * @test
     */
    public function should_not_be_configured(): void
    {
        $wpRootDir = FS::tmpDir('scaffolded_');
        Installation::scaffold($wpRootDir, '6.1.1');

        $scaffolded = new Scaffolded($wpRootDir);

        $this->assertFalse($scaffolded->isConfigured());
    }

    /**
     * It should throw if trying to get a constant
     *
     * @test
     */
    public function should_throw_if_trying_to_get_a_constant(): void
    {
        $wpRootDir = FS::tmpDir('scaffolded_');
        Installation::scaffold($wpRootDir, '6.1.1');

        $scaffolded = new Scaffolded($wpRootDir);

        $this->expectException(InstallationException::class);
        $this->expectExceptionCode(InstallationException::STATE_SCAFFOLDED);

        $scaffolded->getConstant('TEST_CONST');
    }

    /**
     * It should throw if trying to get the db
     *
     * @test
     */
    public function should_throw_if_trying_to_get_the_db(): void
    {
        $wpRootDir = FS::tmpDir('scaffolded_');
        Installation::scaffold($wpRootDir, '6.1.1');

        $scaffolded = new Scaffolded($wpRootDir);

        $this->expectException(InstallationException::class);
        $this->expectExceptionCode(InstallationException::STATE_SCAFFOLDED);

        $scaffolded->getDb();
    }

    /**
     * It should allow getting the installation constants
     *
     * @test
     */
    public function should_allow_getting_the_installation_constants(): void
    {
        $wpRootDir = FS::tmpDir('scaffolded_');
        Installation::scaffold($wpRootDir, '6.1.1');

        $scaffolded = new Scaffolded($wpRootDir);

        $constants = $scaffolded->getConstants();

        $this->assertCount(1, $constants);
        $this->assertEquals($scaffolded->getWpRootDir(), $constants['ABSPATH']);
    }

    /**
     * It should allow getting the installation globals
     *
     * @test
     */
    public function should_allow_getting_the_installation_globals(): void
    {
        $wpRootDir = FS::tmpDir('scaffolded_');
        Installation::scaffold($wpRootDir, '6.1.1');

        $scaffolded = new Scaffolded($wpRootDir);

        $globals = $scaffolded->getGlobals();

        $this->assertCount(1, $globals);
        $this->assertEquals('wp_', $globals['table_prefix']);
    }

    /**
     * It should return plugins directory
     *
     * @test
     */
    public function should_return_plugins_directory(): void
    {
        $wpRootDir = FS::tmpDir('scaffolded_');
        Installation::scaffold($wpRootDir, '6.1.1');

        $scaffolded = new Scaffolded($wpRootDir);

        $this->assertEquals($wpRootDir . '/wp-content/plugins/', $scaffolded->getPluginsDir());
        $this->assertEquals($wpRootDir . '/wp-content/plugins/plugin-1.php', $scaffolded->getPluginsDir('plugin-1.php'));
        $this->assertEquals($wpRootDir . '/wp-content/plugins/test-plugin', $scaffolded->getPluginsDir('test-plugin'));
    }

    /**
     * It should return themes directory
     *
     * @test
     */
    public function should_return_themes_directory(): void
    {
        $wpRootDir = FS::tmpDir('scaffolded_');
        Installation::scaffold($wpRootDir, '6.1.1');

        $scaffolded = new Scaffolded($wpRootDir);

        $this->assertEquals($wpRootDir . '/wp-content/themes/', $scaffolded->getThemesDir());
        $this->assertEquals($wpRootDir . '/wp-content/themes/theme-1.php', $scaffolded->getThemesDir('theme-1.php'));
        $this->assertEquals($wpRootDir . '/wp-content/themes/test-theme', $scaffolded->getThemesDir('test-theme'));
    }

    /**
     * It should allow getting the content dir path
     *
     * @test
     */
    public function should_allow_getting_the_content_dir_path(): void
    {
        $wpRootDir = FS::tmpDir('scaffolded_');
        Installation::scaffold($wpRootDir, '6.1.1');

        $scaffolded = new Scaffolded($wpRootDir);

        $this->assertEquals($wpRootDir . '/wp-content/', $scaffolded->getContentDir());
        $this->assertEquals($wpRootDir . '/wp-content/some/path', $scaffolded->getContentDir('/some/path'));
        $this->assertEquals($wpRootDir . '/wp-content/some/file.php', $scaffolded->getContentDir('some/file.php'));
    }

    /**
     * It should throw if trying to update option
     *
     * @test
     */
    public function should_throw_if_trying_to_update_option(): void
    {
        $wpRootDir = FS::tmpDir('scaffolded_');
        Installation::scaffold($wpRootDir, '6.1.1');

        $scaffolded = new Scaffolded($wpRootDir);

        $this->expectException(InstallationException::class);
        $this->expectExceptionCode(InstallationException::STATE_SCAFFOLDED);

        $scaffolded->updateOption('test', 'value');
    }

    /**
     * It should throw if trying to execute Closure in WordPress
     *
     * @test
     */
    public function should_throw_if_trying_to_execute_closure_in_word_press(): void
    {
        $wpRootDir = FS::tmpDir('scaffolded_');
        Installation::scaffold($wpRootDir, '6.1.1');

        $scaffolded = new Scaffolded($wpRootDir);

        $this->expectException(InstallationException::class);
        $this->expectExceptionCode(InstallationException::STATE_SCAFFOLDED);

        $scaffolded->executeClosureInWordPress(function () {
            return 'test';
        });
    }
}

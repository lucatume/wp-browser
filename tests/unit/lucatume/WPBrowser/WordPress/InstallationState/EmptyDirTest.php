<?php


namespace lucatume\WPBrowser\WordPress\InstallationState;

use lucatume\WPBrowser\Tests\Traits\UopzFunctions;
use lucatume\WPBrowser\Utils\Env;
use lucatume\WPBrowser\Utils\Random;
use lucatume\WPBrowser\WordPress\Db;
use lucatume\WPBrowser\WordPress\InstallationException;
use lucatume\WPBrowser\Utils\Filesystem as FS;

class EmptyDirTest extends \Codeception\Test\Unit
{
    use UopzFunctions;

    /**
     * It should throw when building on non existing root directory
     *
     * @test
     */
    public function should_throw_when_building_on_non_existing_root_directory(): void
    {
        $this->expectException(InstallationException::class);
        $this->expectExceptionCode(InstallationException::ROOT_DIR_NOT_FOUND);

        new EmptyDir('/non-existing-dir');
    }

    /**
     * It should throw when built on non-empty root directory
     *
     * @test
     */
    public function should_throw_when_built_on_non_empty_root_directory(): void
    {
        $wpRootDir = FS::tmpDir('empty-dir_', [
            'wp-load.php' => '<?php echo "Hello there!";',
            'wp-settings.php' => '<?php echo "Hello there!";'
        ]);
        $this->expectException(InstallationException::class);
        $this->expectExceptionCode(InstallationException::STATE_SCAFFOLDED);

        new EmptyDir($wpRootDir);
    }

    /**
     * It should allow getting root directory path
     *
     * @test
     */
    public function should_allow_getting_root_directory_path(): void
    {
        $wpRootDir = FS::tmpDir('empty-dir_');

        $state = new EmptyDir($wpRootDir);

        $this->assertEquals($wpRootDir . '/', $state->getWpRootDir());
    }

    /**
     * It should throw if trying to assess multisite configuration
     *
     * @test
     */
    public function should_throw_if_trying_to_assess_multisite_configuration(): void
    {
        $wpRootDir = FS::tmpDir('empty-dir_');

        $emptyDir = new EmptyDir($wpRootDir);

        $this->expectException(InstallationException::class);
        $this->expectExceptionCode(InstallationException::STATE_EMPTY);
        $this->assertEquals($wpRootDir . '/', $emptyDir->isMultisite());
    }

    /**
     * It should throw when trying to configure installation
     *
     * @test
     */
    public function should_throw_when_trying_to_configure_installation(): void
    {
        $wpRootDir = FS::tmpDir('empty-dir_');

        $emptyDir = new EmptyDir($wpRootDir);
        $dbName = Random::dbName();
        $dbHost = Env::get('WORDPRESS_DB_HOST');
        $dbUser = Env::get('WORDPRESS_DB_USER');
        $dbPassword = Env::get('WORDPRESS_DB_PASSWORD');
        $db = new Db($dbName, $dbUser, $dbPassword, $dbHost);

        $this->expectException(InstallationException::class);
        $this->expectExceptionCode(InstallationException::STATE_EMPTY);

        $emptyDir->configure($db);
    }

    /**
     * It should throw when trying to get salts
     *
     * @test
     */
    public function should_throw_when_trying_to_get_salts(): void
    {
        $wpRootDir = FS::tmpDir('empty-dir_');

        $emptyDir = new EmptyDir($wpRootDir);

        $this->expectException(InstallationException::class);
        $this->expectExceptionCode(InstallationException::STATE_EMPTY);

        $emptyDir->getAuthKey();

        $this->expectException(InstallationException::class);
        $this->expectExceptionCode(InstallationException::STATE_EMPTY);

        $emptyDir->getSecureAuthKey();

        $this->expectException(InstallationException::class);
        $this->expectExceptionCode(InstallationException::STATE_EMPTY);

        $emptyDir->getLoggedInKey();

        $this->expectException(InstallationException::class);
        $this->expectExceptionCode(InstallationException::STATE_EMPTY);

        $emptyDir->getNonceKey();

        $this->expectException(InstallationException::class);
        $this->expectExceptionCode(InstallationException::STATE_EMPTY);

        $emptyDir->getAuthSalt();

        $this->expectException(InstallationException::class);
        $this->expectExceptionCode(InstallationException::STATE_EMPTY);

        $emptyDir->getSecureAuthSalt();

        $this->expectException(InstallationException::class);
        $this->expectExceptionCode(InstallationException::STATE_EMPTY);

        $emptyDir->getLoggedInSalt();

        $this->expectException(InstallationException::class);
        $this->expectExceptionCode(InstallationException::STATE_EMPTY);

        $emptyDir->getNonceSalt();

        $this->expectException(InstallationException::class);
        $this->expectExceptionCode(InstallationException::STATE_EMPTY);

        $emptyDir->getSalts();
    }

    /**
     * It should throw when trying to get table prefix
     *
     * @test
     */
    public function should_throw_when_trying_to_get_table_prefix(): void
    {
        $wpRootDir = FS::tmpDir('empty-dir_');

        $emptyDir = new EmptyDir($wpRootDir);

        $this->expectException(InstallationException::class);
        $this->expectExceptionCode(InstallationException::STATE_EMPTY);

        $emptyDir->getTablePrefix();
    }

    /**
     * It should throw if trying to install
     *
     * @test
     */
    public function should_throw_if_trying_to_install(): void
    {
        $wpRootDir = FS::tmpDir('empty-dir_');

        $emptyDir = new EmptyDir($wpRootDir);

        $this->expectException(InstallationException::class);
        $this->expectExceptionCode(InstallationException::STATE_EMPTY);

        $emptyDir->install('http://wp.local', 'admin', 'password', 'admin@wp.local', 'Test');
    }

    /**
     * It should throw if trying to convert to multisite
     *
     * @test
     */
    public function should_throw_if_trying_to_convert_to_multisite(): void
    {
        $wpRootDir = FS::tmpDir('empty-dir_');

        $emptyDir = new EmptyDir($wpRootDir);

        $this->expectException(InstallationException::class);
        $this->expectExceptionCode(InstallationException::STATE_EMPTY);

        $emptyDir->convertToMultisite();
    }

    /**
     * It should allow scaffolding an installation
     *
     * @test
     */
    public function should_allow_scaffolding_an_installation(): void
    {
        $wpRootDir = FS::tmpDir('empty-dir_');

        $emptyDir = new EmptyDir($wpRootDir);

        $this->assertInstanceOf(Scaffolded::class, $emptyDir->scaffold('6.1.1'));
    }

    /**
     * It should throw if scaffolding fails due to file copy
     *
     * @test
     */
    public function should_throw_if_scaffolding_fails_due_to_file_copy(): void
    {
        $this->uopzSetStaticMethodReturn(FS::class, 'recurseCopy', false);

        $wpRootDir = FS::tmpDir('empty-dir_');

        $emptyDir = new EmptyDir($wpRootDir);

        $this->expectException(InstallationException::class);
        $this->expectExceptionCode(InstallationException::WRITE_ERROR);

        $emptyDir->scaffold('6.1.1');
    }

    /**
     * It should throw if trying to get the wp-config.php file path
     *
     * @test
     */
    public function should_throw_if_trying_to_get_the_wp_config_php_file_path(): void
    {
        $wpRootDir = FS::tmpDir('empty-dir_');

        $emptyDir = new EmptyDir($wpRootDir);

        $this->expectException(InstallationException::class);
        $this->expectExceptionCode(InstallationException::STATE_EMPTY);

        $emptyDir->getWpConfigPath();
    }

    /**
     * It should not be configured
     *
     * @test
     */
    public function should_not_be_configured(): void
    {
        $wpRootDir = FS::tmpDir('empty-dir_');

        $emptyDir = new EmptyDir($wpRootDir);

        $this->assertFalse($emptyDir->isConfigured());
    }

    /**
     * It should throw when trying to get the version
     *
     * @test
     */
    public function should_throw_when_trying_to_get_the_version(): void
    {
        $wpRootDir = FS::tmpDir('empty-dir_');

        $emptyDir = new EmptyDir($wpRootDir);

        $this->expectException(InstallationException::class);
        $this->expectExceptionCode(InstallationException::STATE_EMPTY);

        $emptyDir->getVersion();
    }

    /**
     * It should throw if trying to get a constant
     *
     * @test
     */
    public function should_throw_if_trying_to_get_a_constant(): void
    {
        $wpRootDir = FS::tmpDir('empty-dir_');

        $emptyDir = new EmptyDir($wpRootDir);

        $this->expectException(InstallationException::class);
        $this->expectExceptionCode(InstallationException::STATE_EMPTY);

        $emptyDir->getConstant('WP_DEBUG');
    }

    /**
     * It should throw if trying to get the db
     *
     * @test
     */
    public function should_throw_if_trying_to_get_the_db(): void
    {
        $wpRootDir = FS::tmpDir('empty-dir_');

        $emptyDir = new EmptyDir($wpRootDir);

        $this->expectException(InstallationException::class);
        $this->expectExceptionCode(InstallationException::STATE_EMPTY);

        $emptyDir->getDb();
    }

    /**
     * It should throw when trying to get constants
     *
     * @test
     */
    public function should_throw_when_trying_to_get_constants(): void
    {
        $wpRootDir = FS::tmpDir('empty-dir_');

        $emptyDir = new EmptyDir($wpRootDir);

        $this->expectException(InstallationException::class);
        $this->expectExceptionCode(InstallationException::STATE_EMPTY);

        $emptyDir->getConstants();
    }

    /**
     * It should throw if trying to get globals
     *
     * @test
     */
    public function should_throw_if_trying_to_get_globals(): void
    {
        $wpRootDir = FS::tmpDir('empty-dir_');

        $emptyDir = new EmptyDir($wpRootDir);

        $this->expectException(InstallationException::class);
        $this->expectExceptionCode(InstallationException::STATE_EMPTY);

        $emptyDir->getGlobals();
    }

    /**
     * It should throw if trying to get plugin directory
     *
     * @test
     */
    public function should_throw_if_trying_to_get_plugin_directory(): void
    {
        $wpRootDir = FS::tmpDir('empty-dir_');

        $emptyDir = new EmptyDir($wpRootDir);

        $this->expectException(InstallationException::class);
        $this->expectExceptionCode(InstallationException::STATE_EMPTY);

        $emptyDir->getPluginDir();
    }
}

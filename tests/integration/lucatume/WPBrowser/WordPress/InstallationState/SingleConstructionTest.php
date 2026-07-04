<?php


namespace lucatume\WPBrowser\WordPress\InstallationState;

use Codeception\Test\Unit;
use lucatume\WPBrowser\Exceptions\InvalidArgumentException;
use lucatume\WPBrowser\Tests\Traits\FastScaffold;
use lucatume\WPBrowser\Tests\Traits\TmpFilesCleanup;
use lucatume\WPBrowser\Traits\UopzFunctions;
use lucatume\WPBrowser\Utils\Env;
use lucatume\WPBrowser\Utils\Filesystem as FS;
use lucatume\WPBrowser\Utils\Random;
use lucatume\WPBrowser\WordPress\ConfigurationData;
use lucatume\WPBrowser\WordPress\Database\MysqlDatabase;
use lucatume\WPBrowser\WordPress\Database\SQLiteDatabase;
use lucatume\WPBrowser\WordPress\Installation;
use lucatume\WPBrowser\WordPress\InstallationException;


class SingleConstructionTest extends \Codeception\Test\Unit
{
    use \lucatume\WPBrowser\Traits\UopzFunctions;
    use \lucatume\WPBrowser\Tests\Traits\TmpFilesCleanup;
    use \lucatume\WPBrowser\Tests\Traits\FastScaffold;

    /**
     * It should throw when building on non existing root directory
     *
     * @test
     * @group fast
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
     * @group slow
     * @group requires-mysql-server
     */
    public function should_throw_if_specified_wp_config_php_file_is_not_found(): void
    {
        $dbName = Random::dbName();
        $dbHost = Env::get('WORDPRESS_DB_HOST');
        $dbUser = Env::get('WORDPRESS_DB_USER');
        $dbPassword = Env::get('WORDPRESS_DB_PASSWORD');
        $db = new MysqlDatabase($dbName, $dbUser, $dbPassword, $dbHost);
        $wpRootDir = FS::tmpDir('single_');
        $this->fastScaffold($wpRootDir, '6.1.1')
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
     * @group slow
     * @group requires-mysql-server
     */
    public function should_throw_if_built_on_root_directory_missing_wp_load_php_file(): void
    {
        $dbName = Random::dbName();
        $dbHost = Env::get('WORDPRESS_DB_HOST');
        $dbUser = Env::get('WORDPRESS_DB_USER');
        $dbPassword = Env::get('WORDPRESS_DB_PASSWORD');
        $db = new MysqlDatabase($dbName, $dbUser, $dbPassword, $dbHost);
        $wpRootDir = FS::tmpDir('single_');
        $this->fastScaffold($wpRootDir, '6.1.1')
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
     * @group slow
     * @group requires-mysql-server
     */
    public function should_throw_if_installation_configured_but_not_installed(): void
    {
        $dbName = Random::dbName();
        $dbHost = Env::get('WORDPRESS_DB_HOST');
        $dbUser = Env::get('WORDPRESS_DB_USER');
        $dbPassword = Env::get('WORDPRESS_DB_PASSWORD');
        $db = new MysqlDatabase($dbName, $dbUser, $dbPassword, $dbHost);
        $wpRootDir = FS::tmpDir('single_');
        $this->fastScaffold($wpRootDir, '6.1.1')->configure($db);

        $this->expectException(InstallationException::class);
        $this->expectExceptionCode(InstallationException::STATE_CONFIGURED);

        new Single($wpRootDir, $wpRootDir . '/wp-config.php');
    }

    /**
     * It should throw if building on installed and configured multisite installation
     *
     * @test
     * @group slow
     * @group requires-mysql-server
     */
    public function should_throw_if_building_on_installed_and_configured_multisite_installation(): void
    {
        $dbName = Random::dbName();
        $dbHost = Env::get('WORDPRESS_DB_HOST');
        $dbUser = Env::get('WORDPRESS_DB_USER');
        $dbPassword = Env::get('WORDPRESS_DB_PASSWORD');
        $db = new MysqlDatabase($dbName, $dbUser, $dbPassword, $dbHost);
        $wpRootDir = FS::tmpDir('single_');
        $this->fastScaffold($wpRootDir, '6.1.1')
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
     * @group slow
     * @group requires-mysql-server
     */
    public function should_throw_if_trying_to_install_again(): void
    {
        $dbName = Random::dbName();
        $dbHost = Env::get('WORDPRESS_DB_HOST');
        $dbUser = Env::get('WORDPRESS_DB_USER');
        $dbPassword = Env::get('WORDPRESS_DB_PASSWORD');
        $db = new MysqlDatabase($dbName, $dbUser, $dbPassword, $dbHost);
        $wpRootDir = FS::tmpDir('single_');
        $this->fastScaffold($wpRootDir, '6.1.1')->configure($db)->install(
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
     * @group slow
     * @group requires-mysql-server
     */
    public function should_throw_if_trying_to_scaffold(): void
    {
        $dbName = Random::dbName();
        $dbHost = Env::get('WORDPRESS_DB_HOST');
        $dbUser = Env::get('WORDPRESS_DB_USER');
        $dbPassword = Env::get('WORDPRESS_DB_PASSWORD');
        $db = new MysqlDatabase($dbName, $dbUser, $dbPassword, $dbHost);
        $wpRootDir = FS::tmpDir('single_');
        $this->fastScaffold($wpRootDir, '6.1.1')->configure($db)->install(
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
}

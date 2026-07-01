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


class SingleMultisiteConversionTest extends \Codeception\Test\Unit
{
    use \lucatume\WPBrowser\Traits\UopzFunctions;
    use \lucatume\WPBrowser\Tests\Traits\TmpFilesCleanup;
    use \lucatume\WPBrowser\Tests\Traits\FastScaffold;

    /**
     * It should throw if wp-config.php file contents cannot be read during multsite conversion
     *
     * @test
     * @group slow
     * @group requires-mysql-server
     */
    public function should_throw_if_wp_config_php_file_contents_cannot_be_read_during_multsite_conversion(): void
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

        $wpConfigFilePath = $wpRootDir . '/wp-config.php';
        $single = new Single($wpRootDir, $wpConfigFilePath);

        $this->setFunctionReturn('file_get_contents', function (string $file) use ($wpConfigFilePath) {
            if ($file === $wpConfigFilePath) {
                return false;
            }
            return file_get_contents($file);
        }, true);

        $this->expectException(InstallationException::class);
        $this->expectExceptionCode(InstallationException::WP_CONFIG_FILE_NOT_FOUND);

        $single->convertToMultisite();
    }

    /**
     * It should throw if the placeholder is not found in the wp-config.php file during multisite conversion
     *
     * @test
     * @group slow
     * @group requires-mysql-server
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
        $this->fastScaffold($wpRootDir, '6.1.1')->configure($db)->install(
            'https://wp.local',
            'admin',
            'password',
            'admin@wp.local',
            'Test'
        );
        $wpConfigFilePath = $wpRootDir . '/wp-config.php';

        $single = new Single($wpRootDir, $wpConfigFilePath);


        $this->setFunctionReturn('file_get_contents', function (string $file) use ($wpConfigFilePath) {
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
     * @group slow
     * @group requires-mysql-server
     */
    public function should_throw_if_wp_config_php_file_cannot_be_written_during_multisite_conversion(): void
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
        $wpConfigFilePath = $wpRootDir . '/wp-config.php';

        $single = new Single($wpRootDir, $wpConfigFilePath);


        $this->setFunctionReturn('file_put_contents', false);

        $this->expectException(InstallationException::class);
        $this->expectExceptionCode(InstallationException::WRITE_ERROR);

        $single->convertToMultisite();
    }

    /**
     * It should allow converting the installation to multisite subdir installation
     *
     * @test
     * @group slow
     * @group requires-mysql-server
     */
    public function should_allow_converting_the_installation_to_multisite_subdir_installation(): void
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

        $multisite = $single->convertToMultisite(false);

        $this->assertInstanceOf(Multisite::class, $multisite);
    }

    /**
     * It should allow converting the installation to multisite subdomain installation
     *
     * @test
     * @group slow
     * @group requires-mysql-server
     */
    public function should_allow_converting_the_installation_to_multisite_subdomain_installation(): void
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

        $multisite = $single->convertToMultisite(true);

        $this->assertInstanceOf(Multisite::class, $multisite);
    }

    /**
     * It should throw if no admin user can be found while converting to multisite
     *
     * @test
     * @group slow
     * @group requires-mysql-server
     */
    public function should_throw_if_no_admin_user_can_be_found_while_converting_to_multisite(): void
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
        $single = new Single($wpRootDir, $wpRootDir . '/wp-config.php');

        // Delete all users from the database.
        $db->query('DELETE FROM wp_users');

        $this->expectException(InstallationException::class);
        $this->expectExceptionCode(InstallationException::NO_ADMIN_USER_FOUND);
        $single->convertToMultisite(false);
    }

    /**
     * It should throw if siteurl cannot be fetched in constructor
     *
     * @test
     * @group slow
     * @group requires-mysql-server
     */
    public function should_throw_if_siteurl_cannot_be_fetched_in_constructor(): void
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
        // Delete the siteurl option.
        $db->query("DELETE FROM {$db->getTablePrefix()}options WHERE option_name = 'siteurl'");

        $this->expectException(InstallationException::class);
        $this->expectExceptionCode(InstallationException::STATE_CONFIGURED);

        new Single($wpRootDir, $wpRootDir . '/wp-config.php');
    }

    /**
     * It should throw if siteurl cannot be found while converting to multisite
     *
     * @test
     * @group slow
     * @group requires-mysql-server
     */
    public function should_throw_if_siteurl_cannot_be_found_while_converting_to_multisite(): void
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
    }
}

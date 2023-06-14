<?php

namespace lucatume\WPBrowser\WordPress;

use Codeception\Test\Unit;
use lucatume\WPBrowser\Tests\Traits\TmpFilesCleanup;
use lucatume\WPBrowser\Tests\Traits\UopzFunctions;
use lucatume\WPBrowser\Utils\Env;
use lucatume\WPBrowser\Utils\Filesystem as FS;
use tad\Codeception\SnapshotAssertions\SnapshotAssertions;

class WpConfigFileGeneratorTest extends Unit
{
    use UopzFunctions;
    use SnapshotAssertions;
    use TmpFilesCleanup;

    /**
     * It should throw if building on non existing root directory
     *
     * @test
     */
    public function should_throw_if_building_on_non_existing_root_directory(): void
    {
        $this->expectException(InstallationException::class);
        $this->expectExceptionCode(InstallationException::ROOT_DIR_NOT_FOUND);

        new WpConfigFileGenerator('/non-existing-dir');
    }

    /**
     * It should throw if wp-config-sample.php file not found in root directory
     *
     * @test
     */
    public function should_throw_if_wp_config_sample_php_file_not_found_in_root_directory(): void
    {
        $wpRootDir = FS::tmpDir('wp-config_', [
            'wp-settings.php' => '<?php echo "Hello there!";'
        ]);

        $this->expectException(InstallationException::class);
        $this->expectExceptionCode(InstallationException::WP_CONFIG_SAMPLE_FILE_NOT_FOUND);

        new WpConfigFileGenerator($wpRootDir);
    }

    /**
     * It should throw if wp-config-sample.php file cannot be read
     *
     * @test
     */
    public function should_throw_if_wp_config_sample_php_file_cannot_be_read(): void
    {
        $wpRootDir = FS::tmpDir('wp-config_', [
            'wp-settings.php' => '<?php echo "Hello there!";',
            'wp-config-sample.php' => '<?php echo "Hello there!";'
        ]);
        $this->uopzSetFunctionReturn('file_get_contents', static function ($file) use ($wpRootDir) {
            return $file === $wpRootDir . '/wp-config-sample.php' ? false : file_get_contents($file);
        }, true);

        $this->expectException(InstallationException::class);
        $this->expectExceptionCode(InstallationException::WP_CONFIG_SAMPLE_FILE_NOT_FOUND);

        new WpConfigFileGenerator($wpRootDir);
    }

    /**
     * It should correctly produce a wp-config.php file contents provided db and configuration data
     *
     * @test
     */
    public function should_correctly_produce_a_wp_config_php_file_contents_provided_db_and_configuration_data(): void
    {
        $wpRootDir = FS::tmpDir('wp-config_');
        Installation::scaffold($wpRootDir, '6.1.1');
        $dbName = 'test_123';
        $dbHost = Env::get('WORDPRESS_DB_HOST');
        $dbUser = Env::get('WORDPRESS_DB_USER');
        $dbPassword = Env::get('WORDPRESS_DB_PASSWORD');
        $db = new Db($dbName, $dbUser, $dbPassword, $dbHost);
        $configurationData = new ConfigurationData();
        $configurationData->setAuthKey('auth-key');
        $configurationData->setSecureAuthKey('secure-auth-key');
        $configurationData->setLoggedInKey('logged-in-key');
        $configurationData->setNonceKey('nonce-key');
        $configurationData->setAuthSalt('auth-salt');
        $configurationData->setSecureAuthSalt('secure-auth-salt');
        $configurationData->setLoggedInSalt('logged-in-salt');
        $configurationData->setNonceSalt('nonce-salt');

        $generator = new WpConfigFileGenerator($wpRootDir);
        $produced = $generator->produce($db, $configurationData);

        $this->assertMatchesCodeSnapshot($produced);
    }

    /**
     * It should correctly produce a wp-config.php file with custom constants
     *
     * @test
     */
    public function should_correctly_produce_a_wp_config_php_file_with_custom_constants(): void
    {
        $wpRootDir = FS::tmpDir('wp-config_');
        Installation::scaffold($wpRootDir, '6.1.1');
        $dbName = 'test_123';
        $dbHost = Env::get('WORDPRESS_DB_HOST');
        $dbUser = Env::get('WORDPRESS_DB_USER');
        $dbPassword = Env::get('WORDPRESS_DB_PASSWORD');
        $db = new Db($dbName, $dbUser, $dbPassword, $dbHost);
        $configurationData = new ConfigurationData();
        $configurationData->setAuthKey('auth-key');
        $configurationData->setSecureAuthKey('secure-auth-key');
        $configurationData->setLoggedInKey('logged-in-key');
        $configurationData->setNonceKey('nonce-key');
        $configurationData->setAuthSalt('auth-salt');
        $configurationData->setSecureAuthSalt('secure-auth-salt');
        $configurationData->setLoggedInSalt('logged-in-salt');
        $configurationData->setNonceSalt('nonce-salt');
        $configurationData->setConst('FOO','BAR');
        $configurationData->setConst('BAR',23);
        $configurationData->setConst('BAZ',23.89);
        $configurationData->setConst('BAZBAR',true);
        $configurationData->setConst('BAZBARFOO',false);
        $configurationData->setConst('NULL_CONST',null);

        $generator = new WpConfigFileGenerator($wpRootDir);
        $produced = $generator->produce($db, $configurationData);

        $this->assertMatchesCodeSnapshot($produced);
    }
}

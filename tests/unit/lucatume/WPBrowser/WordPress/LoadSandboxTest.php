<?php


namespace Unit\lucatume\WPBrowser\WordPress;

use Codeception\Test\Unit;
use Exception;
use lucatume\WPBrowser\Tests\Traits\LoopIsolation;
use lucatume\WPBrowser\Tests\Traits\TmpFilesCleanup;
use lucatume\WPBrowser\Utils\Env;
use lucatume\WPBrowser\Utils\Filesystem as FS;
use lucatume\WPBrowser\Utils\Random;
use lucatume\WPBrowser\WordPress\Database\MysqlDatabase;
use lucatume\WPBrowser\WordPress\Installation;
use lucatume\WPBrowser\WordPress\InstallationException;
use lucatume\WPBrowser\WordPress\InstallationState\InstallationStateInterface;
use lucatume\WPBrowser\WordPress\LoadSandbox;
use PHPUnit\Framework\Assert;

class LoadSandboxTest extends Unit
{
    use LoopIsolation;
    use TmpFilesCleanup;

    /**
     * It should correctly load installed WordPress
     *
     * @test
     */
    public function should_correctly_load_installed_wordpress(): void
    {
        $wpRootDir = FS::tmpDir('sandbox_');
        $dbName = Random::dbName();
        $dbHost = Env::get('WORDPRESS_DB_HOST');
        $dbUser = Env::get('WORDPRESS_DB_USER');
        $dbPassword = Env::get('WORDPRESS_DB_PASSWORD');
        $db = new MysqlDatabase($dbName, $dbUser, $dbPassword, $dbHost, 'wp_');
        Installation::scaffold($wpRootDir, '6.1.1')
            ->configure($db)
            ->install(
                'http://wordpress.test',
                'admin',
                'admin',
                'admin@wordpress.test',
                'Sandbox'
            );

        $loadSandbox = new LoadSandbox($wpRootDir, 'wordpress.test');

        $this->assertInIsolation(static function () use ($loadSandbox) {
            $loadSandbox->load();
            Assert::assertEquals('HTTP/1.1', $_SERVER['SERVER_PROTOCOL']);
            Assert::assertEquals('wordpress.test', $_SERVER['HTTP_HOST']);
            Assert::assertTrue(defined('ABSPATH'));
            Assert::assertSame(1, did_action('wp_loaded'));
            Assert::assertEquals('', $loadSandbox->getBufferedOutput());
        });
    }

    /**
     * It should correctly load installed multisite subdomain_WordPress
     *
     * @test
     */
    public function should_correctly_load_installed_multisite_subdomain_wordpress(): void
    {
        $wpRootDir = FS::tmpDir('sandbox_');
        $dbName = Random::dbName();
        $dbHost = Env::get('WORDPRESS_DB_HOST');
        $dbUser = Env::get('WORDPRESS_DB_USER');
        $dbPassword = Env::get('WORDPRESS_DB_PASSWORD');
        $db = new MysqlDatabase($dbName, $dbUser, $dbPassword, $dbHost, 'wp_');
        Installation::scaffold($wpRootDir, '6.1.1')
            ->configure($db, InstallationStateInterface::MULTISITE_SUBDOMAIN)
            ->install(
                'http://wordpress.test',
                'admin',
                'admin',
                'admin@wordpress.test',
                'Sandbox'
            );

        $loadSandbox = new LoadSandbox($wpRootDir, 'wordpress.test');

        $this->assertInIsolation(static function () use ($loadSandbox) {
            $loadSandbox->load();
            Assert::assertEquals('HTTP/1.1', $_SERVER['SERVER_PROTOCOL']);
            Assert::assertEquals('wordpress.test', $_SERVER['HTTP_HOST']);
            Assert::assertTrue(defined('ABSPATH'));
            Assert::assertSame(1, did_action('wp_loaded'));
            Assert::assertEquals('', $loadSandbox->getBufferedOutput());
            Assert::assertTrue(is_multisite());
            Assert::assertTrue(is_subdomain_install());
        });
    }

    /**
     * It should correctly load installed multisite subfolder WordPress
     *
     * @test
     */
    public function should_correctly_load_installed_multisite_subfolder_word_press(): void
    {
        $wpRootDir = FS::tmpDir('sandbox_');
        $dbName = Random::dbName();
        $dbHost = Env::get('WORDPRESS_DB_HOST');
        $dbUser = Env::get('WORDPRESS_DB_USER');
        $dbPassword = Env::get('WORDPRESS_DB_PASSWORD');
        $db = new MysqlDatabase($dbName, $dbUser, $dbPassword, $dbHost, 'wp_');
        Installation::scaffold($wpRootDir, '6.1.1')
            ->configure($db, InstallationStateInterface::MULTISITE_SUBFOLDER)
            ->install(
                'http://wordpress.test',
                'admin',
                'admin',
                'admin@wordpress.test',
                'Sandbox'
            );

        $loadSandbox = new LoadSandbox($wpRootDir, 'wordpress.test');

        $this->assertInIsolation(static function () use ($loadSandbox) {
            $loadSandbox->load();
            Assert::assertEquals('HTTP/1.1', $_SERVER['SERVER_PROTOCOL']);
            Assert::assertEquals('wordpress.test', $_SERVER['HTTP_HOST']);
            Assert::assertTrue(defined('ABSPATH'));
            Assert::assertSame(1, did_action('wp_loaded'));
            Assert::assertEquals('', $loadSandbox->getBufferedOutput());
            Assert::assertTrue(is_multisite());
            Assert::assertFalse(is_subdomain_install());
        });
    }

    /**
     * It should handle missing database
     *
     * @test
     */
    public function should_handle_missing_database(): void
    {
        $wpRootDir = FS::tmpDir('sandbox_');
        $dbName = Random::dbName();
        $dbHost = Env::get('WORDPRESS_DB_HOST');
        $dbUser = Env::get('WORDPRESS_DB_USER');
        $dbPassword = Env::get('WORDPRESS_DB_PASSWORD');
        $db = new MysqlDatabase($dbName, $dbUser, $dbPassword, $dbHost, 'wp_');
        Installation::scaffold($wpRootDir, '6.1.1')
            ->configure($db);

        $this->expectException(InstallationException::class);
        $this->expectExceptionMessage(
            InstallationException::becauseWordPressFailedToLoad(
                'error establishing a database connection'
            )->getMessage()
        );

        $loadSandbox = new LoadSandbox($wpRootDir, 'wordpress.test');

        $this->assertInIsolation(static function () use ($loadSandbox) {
            $loadSandbox->load();
        });
    }

    /**
     * It should handle not installed
     *
     * @test
     */
    public function should_handle_not_installed(): void
    {
        $wpRootDir = FS::tmpDir('sandbox_');
        $dbName = Random::dbName();
        $dbHost = Env::get('WORDPRESS_DB_HOST');
        $dbUser = Env::get('WORDPRESS_DB_USER');
        $dbPassword = Env::get('WORDPRESS_DB_PASSWORD');
        $db = new MysqlDatabase($dbName, $dbUser, $dbPassword, $dbHost, 'wp_');
        Installation::scaffold($wpRootDir, '6.1.1')
            ->configure($db);
        $db->create();

        $this->expectException(InstallationException::class);
        $this->expectExceptionMessage(
            InstallationException::becauseWordPressIsNotInstalled()->getMessage()
        );

        $loadSandbox = new LoadSandbox($wpRootDir, 'wordpress.test');

        $this->assertInIsolation(static function () use ($loadSandbox) {
            $loadSandbox->load();
        });
    }

    /**
     * It should handle not installed multisite subdomain
     *
     * @test
     */
    public function should_handle_not_installed_multisite_subdomain(): void
    {
        $wpRootDir = FS::tmpDir('sandbox_');
        $dbName = Random::dbName();
        $dbHost = Env::get('WORDPRESS_DB_HOST');
        $dbUser = Env::get('WORDPRESS_DB_USER');
        $dbPassword = Env::get('WORDPRESS_DB_PASSWORD');
        $db = new MysqlDatabase($dbName, $dbUser, $dbPassword, $dbHost, 'wp_');
        Installation::scaffold($wpRootDir, '6.1.1')
            ->configure($db, InstallationStateInterface::MULTISITE_SUBDOMAIN);
        $db->create();

        $this->expectException(InstallationException::class);
        $this->expectExceptionMessage(
            InstallationException::becauseWordPressMultsiteIsNotInstalled(true)->getMessage()
        );

        $loadSandbox = new LoadSandbox($wpRootDir, 'wordpress.test');

        $this->assertInIsolation(static function () use ($loadSandbox) {
            $loadSandbox->load();
        });
    }

    /**
     * It should handle not installed multisite subfolder
     *
     * @test
     */
    public function should_handle_not_installed_multisite_subfolder(): void
    {
        $wpRootDir = FS::tmpDir('sandbox_');
        $dbName = Random::dbName();
        $dbHost = Env::get('WORDPRESS_DB_HOST');
        $dbUser = Env::get('WORDPRESS_DB_USER');
        $dbPassword = Env::get('WORDPRESS_DB_PASSWORD');
        $db = new MysqlDatabase($dbName, $dbUser, $dbPassword, $dbHost, 'wp_');
        Installation::scaffold($wpRootDir, '6.1.1')
            ->configure($db, InstallationStateInterface::MULTISITE_SUBFOLDER);
        $db->create();

        $this->expectException(InstallationException::class);
        $this->expectExceptionMessage(
            InstallationException::becauseWordPressMultsiteIsNotInstalled(false)->getMessage()
        );

        $loadSandbox = new LoadSandbox($wpRootDir, 'wordpress.test');

        $this->assertInIsolation(static function () use ($loadSandbox) {
            $loadSandbox->load();
        });
    }

    /**
     * It should not handle exception thrown during loading
     *
     * @test
     */
    public function should_not_handle_exception_thrown_during_loading(): void
    {
        $wpRootDir = FS::tmpDir('sandbox_');
        $dbName = Random::dbName();
        $dbHost = Env::get('WORDPRESS_DB_HOST');
        $dbUser = Env::get('WORDPRESS_DB_USER');
        $dbPassword = Env::get('WORDPRESS_DB_PASSWORD');
        $db = new MysqlDatabase($dbName, $dbUser, $dbPassword, $dbHost, 'wp_');
        $installation = Installation::scaffold($wpRootDir, '6.1.1')
            ->configure($db)
            ->install(
                'http://wordpress.test',
                'admin',
                'admin',
                'admin@wordpress.test',
                'Sandbox'
            );
        $throwingPluginCode = <<<'PHP'
<?php
/**
 * Plugin Name: Bad Plugin
 */
add_action('wp_loaded', function () {
    throw new Exception('Exception thrown during loading');
});
PHP;

        file_put_contents($installation->getPluginsDir('test-plugin.php'), $throwingPluginCode);
        $installation->getDb()->updateOption('active_plugins', ['test-plugin.php']);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Exception thrown during loading');

        $loadSandbox = new LoadSandbox($wpRootDir, 'wordpress.test');

        $this->assertInIsolation(static function () use ($loadSandbox) {
            $loadSandbox->load();
        });
    }

    /**
     * It should handle wp_die called during loading
     *
     * @test
     */
    public function should_handle_wp_die_called_during_loading(): void
    {
        $wpRootDir = FS::tmpDir('sandbox_');
        $dbName = Random::dbName();
        $dbHost = Env::get('WORDPRESS_DB_HOST');
        $dbUser = Env::get('WORDPRESS_DB_USER');
        $dbPassword = Env::get('WORDPRESS_DB_PASSWORD');
        $db = new MysqlDatabase($dbName, $dbUser, $dbPassword, $dbHost, 'wp_');
        // Setup, but do not install WordPress.
        Installation::scaffold($wpRootDir, '6.1.1')->configure($db);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('WordPress failed to load for the following reason: ' .
            'error establishing a database connection.');

        $loadSandbox = new LoadSandbox($wpRootDir, 'wordpress.test');

        $this->assertInIsolation(static function () use ($loadSandbox) {
            $loadSandbox->load();
        });
    }
}

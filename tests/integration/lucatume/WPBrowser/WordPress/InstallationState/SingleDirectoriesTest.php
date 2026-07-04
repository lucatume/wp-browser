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


class SingleDirectoriesTest extends \Codeception\Test\Unit
{
    use \lucatume\WPBrowser\Traits\UopzFunctions;
    use \lucatume\WPBrowser\Tests\Traits\TmpFilesCleanup;
    use \lucatume\WPBrowser\Tests\Traits\FastScaffold;

    /**
     * It should return plugins directory
     *
     * @test
     * @group slow
     * @group requires-mysql-server
     */
    public function should_return_plugins_directory(): void
    {
        $wpRootDir = FS::tmpDir('single_');
        $dbName = Random::dbName();
        $dbHost = Env::get('WORDPRESS_DB_HOST');
        $dbUser = Env::get('WORDPRESS_DB_USER');
        $dbPassword = Env::get('WORDPRESS_DB_PASSWORD');
        $db = new MysqlDatabase($dbName, $dbUser, $dbPassword, $dbHost, 'test_');
        $this->fastScaffold($wpRootDir, '6.1.1')
            ->configure($db)
            ->install(
                'https://wp.local',
                'admin',
                'password',
                'admin@wp.local',
                'Test');

        $single = new Single($wpRootDir, $wpRootDir . '/wp-config.php');

        $this->assertEquals($wpRootDir . '/wp-content/plugins', $single->getPluginsDir());
    }

    /**
     * It should return plugins directory built from WP_CONTENT_DIR if set
     *
     * @test
     * @group slow
     * @group requires-mysql-server
     */
    public function should_return_plugins_directory_built_from_wp_content_dir_if_set(): void
    {
        $wpRootDir = FS::tmpDir('single_');
        $dbName = Random::dbName();
        $dbHost = Env::get('WORDPRESS_DB_HOST');
        $dbUser = Env::get('WORDPRESS_DB_USER');
        $dbPassword = Env::get('WORDPRESS_DB_PASSWORD');
        $db = new MysqlDatabase($dbName, $dbUser, $dbPassword, $dbHost, 'test_');
        $configurationData = new ConfigurationData();
        $configurationData->setConst('WP_CONTENT_DIR', $wpRootDir . '/site-content');
        $this->fastScaffold($wpRootDir, '6.1.1')
            ->configure($db, InstallationStateInterface::SINGLE_SITE, $configurationData)
            ->install(
                'https://wp.local',
                'admin',
                'password',
                'admin@wp.local',
                'Test');

        $single = new Single($wpRootDir, $wpRootDir . '/wp-config.php');

        $this->assertEquals($wpRootDir . '/site-content/plugins', $single->getPluginsDir());
    }

    /**
     * It should return plugins directory built from WP_PLUGIN_DIR if set
     *
     * @test
     * @group slow
     * @group requires-mysql-server
     */
    public function should_return_plugins_directory_built_from_wp_plugin_dir_if_set(): void
    {
        $wpRootDir = FS::tmpDir('single_');
        $dbName = Random::dbName();
        $dbHost = Env::get('WORDPRESS_DB_HOST');
        $dbUser = Env::get('WORDPRESS_DB_USER');
        $dbPassword = Env::get('WORDPRESS_DB_PASSWORD');
        $db = new MysqlDatabase($dbName, $dbUser, $dbPassword, $dbHost, 'test_');
        $configurationData = new ConfigurationData();
        $configurationData->setConst('WP_PLUGIN_DIR', $wpRootDir . '/site-plugins');
        $this->fastScaffold($wpRootDir, '6.1.1')
            ->configure($db, InstallationStateInterface::SINGLE_SITE, $configurationData)
            ->install(
                'https://wp.local',
                'admin',
                'password',
                'admin@wp.local',
                'Test');

        $single = new Single($wpRootDir, $wpRootDir . '/wp-config.php');

        $this->assertEquals($wpRootDir . '/site-plugins', $single->getPluginsDir());
        $this->assertEquals($wpRootDir . '/site-plugins/plugin-1.php', $single->getPluginsDir('plugin-1.php'));
        $this->assertEquals($wpRootDir . '/site-plugins/test-plugin', $single->getPluginsDir('test-plugin'));
    }

    /**
     * It should return mu-plugins directory
     *
     * @test
     * @group slow
     * @group requires-mysql-server
     */
    public function should_return_mu_plugins_directory(): void
    {
        $wpRootDir = FS::tmpDir('single_');
        $dbName = Random::dbName();
        $dbHost = Env::get('WORDPRESS_DB_HOST');
        $dbUser = Env::get('WORDPRESS_DB_USER');
        $dbPassword = Env::get('WORDPRESS_DB_PASSWORD');
        $db = new MysqlDatabase($dbName, $dbUser, $dbPassword, $dbHost, 'test_');
        $this->fastScaffold($wpRootDir, '6.1.1')
            ->configure($db)
            ->install(
                'https://wp.local',
                'admin',
                'password',
                'admin@wp.local',
                'Test');

        $single = new Single($wpRootDir, $wpRootDir . '/wp-config.php');

        $this->assertEquals($wpRootDir . '/wp-content/mu-plugins', $single->getMuPluginsDir());
    }

    /**
     * It should return mu-plugins directory built from WP_CONTENT_DIR if set
     *
     * @test
     * @group slow
     * @group requires-mysql-server
     */
    public function should_return_mu_plugins_directory_built_from_wp_content_dir_if_set(): void
    {
        $wpRootDir = FS::tmpDir('single_');
        $dbName = Random::dbName();
        $dbHost = Env::get('WORDPRESS_DB_HOST');
        $dbUser = Env::get('WORDPRESS_DB_USER');
        $dbPassword = Env::get('WORDPRESS_DB_PASSWORD');
        $db = new MysqlDatabase($dbName, $dbUser, $dbPassword, $dbHost, 'test_');
        $configurationData = new ConfigurationData();
        $configurationData->setConst('WP_CONTENT_DIR', $wpRootDir . '/site-content');
        $this->fastScaffold($wpRootDir, '6.1.1')
            ->configure($db, InstallationStateInterface::SINGLE_SITE, $configurationData)
            ->install(
                'https://wp.local',
                'admin',
                'password',
                'admin@wp.local',
                'Test');

        $single = new Single($wpRootDir, $wpRootDir . '/wp-config.php');

        $this->assertEquals($wpRootDir . '/site-content/mu-plugins', $single->getMuPluginsDir());
    }

    /**
     * It should return mu-plugins directory built from WP_PLUGIN_DIR if set
     *
     * @test
     * @group slow
     * @group requires-mysql-server
     */
    public function should_return_mu_plugins_directory_built_from_wp_plugin_dir_if_set(): void
    {
        $wpRootDir = FS::tmpDir('single_');
        $dbName = Random::dbName();
        $dbHost = Env::get('WORDPRESS_DB_HOST');
        $dbUser = Env::get('WORDPRESS_DB_USER');
        $dbPassword = Env::get('WORDPRESS_DB_PASSWORD');
        $db = new MysqlDatabase($dbName, $dbUser, $dbPassword, $dbHost, 'test_');
        $configurationData = new ConfigurationData();
        $configurationData->setConst('WPMU_PLUGIN_DIR', $wpRootDir . '/site-mu-plugins');
        $this->fastScaffold($wpRootDir, '6.1.1')
            ->configure($db, InstallationStateInterface::SINGLE_SITE, $configurationData)
            ->install(
                'https://wp.local',
                'admin',
                'password',
                'admin@wp.local',
                'Test');

        $single = new Single($wpRootDir, $wpRootDir . '/wp-config.php');

        $this->assertEquals($wpRootDir . '/site-mu-plugins', $single->getMuPluginsDir());
        $this->assertEquals($wpRootDir . '/site-mu-plugins/plugin-1.php', $single->getMuPluginsDir('plugin-1.php'));
        $this->assertEquals($wpRootDir . '/site-mu-plugins/test-plugin', $single->getMuPluginsDir('test-plugin'));
    }

    /**
     * It should return the themes directory
     *
     * @test
     * @group slow
     * @group requires-mysql-server
     */
    public function should_return_the_themes_directory(): void
    {
        $wpRootDir = FS::tmpDir('single_');
        $dbName = Random::dbName();
        $dbHost = Env::get('WORDPRESS_DB_HOST');
        $dbUser = Env::get('WORDPRESS_DB_USER');
        $dbPassword = Env::get('WORDPRESS_DB_PASSWORD');
        $db = new MysqlDatabase($dbName, $dbUser, $dbPassword, $dbHost, 'test_');
        $configurationData = new ConfigurationData();
        $this->fastScaffold($wpRootDir, '6.1.1')
            ->configure($db, InstallationStateInterface::SINGLE_SITE, $configurationData)
            ->install(
                'https://wp.local',
                'admin',
                'password',
                'admin@wp.local',
                'Test');

        $single = new Single($wpRootDir, $wpRootDir . '/wp-config.php');

        $this->assertEquals($wpRootDir . '/wp-content/themes', $single->getThemesDir());
        $this->assertEquals($wpRootDir . '/wp-content/themes/some-file.php', $single->getThemesDir('some-file.php'));
        $this->assertEquals($wpRootDir . '/wp-content/themes/some-theme', $single->getThemesDir('some-theme'));
    }

    /**
     * It should return the themes directory built from WP_CONTENT_DIR if set
     *
     * @test
     * @group slow
     * @group requires-mysql-server
     */
    public function should_return_the_themes_directory_built_from_wp_content_dir_if_set(): void
    {
        $wpRootDir = FS::tmpDir('single_');
        $dbName = Random::dbName();
        $dbHost = Env::get('WORDPRESS_DB_HOST');
        $dbUser = Env::get('WORDPRESS_DB_USER');
        $dbPassword = Env::get('WORDPRESS_DB_PASSWORD');
        $db = new MysqlDatabase($dbName, $dbUser, $dbPassword, $dbHost, 'test_');
        $configurationData = new ConfigurationData();
        $configurationData->setConst('WP_CONTENT_DIR', $wpRootDir . '/site-content');
        $this->fastScaffold($wpRootDir, '6.1.1')
            ->configure($db, InstallationStateInterface::SINGLE_SITE, $configurationData)
            ->install(
                'https://wp.local',
                'admin',
                'password',
                'admin@wp.local',
                'Test');

        $single = new Single($wpRootDir, $wpRootDir . '/wp-config.php');

        $this->assertEquals($wpRootDir . '/site-content/themes', $single->getThemesDir());
        $this->assertEquals($wpRootDir . '/site-content/themes/some-file.php', $single->getThemesDir('some-file.php'));
        $this->assertEquals($wpRootDir . '/site-content/themes/some-theme', $single->getThemesDir('some-theme'));
    }

    /**
     * It should return content directory
     *
     * @test
     * @group slow
     * @group requires-mysql-server
     */
    public function should_return_content_directory(): void
    {
        $wpRootDir = FS::tmpDir('single_');
        $dbName = Random::dbName();
        $dbHost = Env::get('WORDPRESS_DB_HOST');
        $dbUser = Env::get('WORDPRESS_DB_USER');
        $dbPassword = Env::get('WORDPRESS_DB_PASSWORD');
        $db = new MysqlDatabase($dbName, $dbUser, $dbPassword, $dbHost, 'test_');
        $configurationData = new ConfigurationData();
        $this->fastScaffold($wpRootDir, '6.1.1')
            ->configure($db, InstallationStateInterface::SINGLE_SITE, $configurationData)
            ->install(
                'https://wp.local',
                'admin',
                'password',
                'admin@wp.local',
                'Test');

        $single = new Single($wpRootDir, $wpRootDir . '/wp-config.php');

        $this->assertEquals($wpRootDir . '/wp-content', $single->getContentDir());
        $this->assertEquals($wpRootDir . '/wp-content/some-file.php', $single->getContentDir('some-file.php'));
        $this->assertEquals($wpRootDir . '/wp-content/some/path', $single->getContentDir('/some/path'));
    }

    /**
     * It should return content directory built from WP_CONTENT_DIR if set
     *
     * @test
     * @group slow
     * @group requires-mysql-server
     */
    public function should_return_content_directory_built_from_wp_content_dir_if_set(): void
    {
        $wpRootDir = FS::tmpDir('single_');
        $dbName = Random::dbName();
        $dbHost = Env::get('WORDPRESS_DB_HOST');
        $dbUser = Env::get('WORDPRESS_DB_USER');
        $dbPassword = Env::get('WORDPRESS_DB_PASSWORD');
        $db = new MysqlDatabase($dbName, $dbUser, $dbPassword, $dbHost, 'test_');
        $configurationData = new ConfigurationData();
        $configurationData->setConst('WP_CONTENT_DIR', $wpRootDir . '/site-content');
        $this->fastScaffold($wpRootDir, '6.1.1')
            ->configure($db, InstallationStateInterface::SINGLE_SITE, $configurationData)
            ->install(
                'https://wp.local',
                'admin',
                'password',
                'admin@wp.local',
                'Test');

        $single = new Single($wpRootDir, $wpRootDir . '/wp-config.php');

        $this->assertEquals($wpRootDir . '/site-content', $single->getContentDir());
        $this->assertEquals($wpRootDir . '/site-content/some-file.php', $single->getContentDir('some-file.php'));
        $this->assertEquals($wpRootDir . '/site-content/some/path', $single->getContentDir('/some/path'));
    }
}

<?php


namespace lucatume\WPBrowser\WordPress;

use lucatume\WPBrowser\Utils\Env;
use lucatume\WPBrowser\Utils\Filesystem as FS;
use lucatume\WPBrowser\Utils\Random;
use lucatume\WPBrowser\WordPress\InstallationState\InstallationStateInterface;
use lucatume\WPBrowser\WordPress\InstallationState\Single;
use PDO;

class DbTest extends \Codeception\Test\Unit
{
    /**
     * It should throw when building with invalid db name
     *
     * @test
     */
    public function should_throw_when_building_with_invalid_db_name(): void
    {
        $this->expectException(DbException::class);
        $this->expectExceptionCode(DbException::INVALID_DB_NAME);

        new Db('!invalid~db-name', 'root', 'root', 'localhost');
    }

    /**
     * It should allow getting the db credentials and DSN
     *
     * @test
     */
    public function should_allow_getting_the_db_credentials_and_dsn(): void
    {
        $db = new Db('test', 'bob', 'secret', '192.1.2.3:4415', 'test_');

        $this->assertEquals('test', $db->getDbName());
        $this->assertEquals('bob', $db->getDbUser());
        $this->assertEquals('secret', $db->getDbPassword());
        $this->assertEquals('192.1.2.3:4415', $db->getDbHost());
        $this->assertEquals('test_', $db->getTablePrefix());
        $this->assertEquals('mysql:host=192.1.2.3;port=4415', $db->getDsn());
        $this->assertEquals('mysql://bob:secret@192.1.2.3:4415/test',
            $db->getDbUrl());
    }

    /**
     * It should build correctly from wp-config file
     *
     * @test
     */
    public function should_build_correctly_from_wp_config_file(): void
    {
        $wpRootDir = FS::tmpDir('db_', [
            'wp-settings.php' => '<?php ',
            'wp-config.php' => file_get_contents(codecept_data_dir('files/test-wp-config_001.php'))
        ]);
        $wpConfigFile = new WPConfigFile($wpRootDir, $wpRootDir . '/wp-config.php');

        $db = Db::fromWpConfigFile($wpConfigFile);

        $this->assertEquals('test', $db->getDbName());
        $this->assertEquals('bob', $db->getDbUser());
        $this->assertEquals('secret', $db->getDbPassword());
        $this->assertEquals('192.1.2.3:4415', $db->getDbHost());
        $this->assertEquals('test_', $db->getTablePrefix());
        $this->assertEquals('mysql:host=192.1.2.3;port=4415', $db->getDsn());
        $this->assertEquals('mysql://bob:secret@192.1.2.3:4415/test',
            $db->getDbUrl());
    }

    /**
     * It should allow db operations
     *
     * @test
     */
    public function should_allow_db_operations(): void
    {
        $dbName = Random::dbName();
        $dbHost = Env::get('WORDPRESS_DB_HOST');
        $dbUser = Env::get('WORDPRESS_DB_USER');
        $dbPassword = Env::get('WORDPRESS_DB_PASSWORD');
        $db = new Db($dbName, $dbUser, $dbPassword, $dbHost, 'test_');

        $this->assertInstanceOf(PDO::class, $db->create()->getPDO());
        $this->assertTrue($db->exists());
        $this->assertFalse($db->drop()->exists());
        $this->assertEquals(1, $db->query('CREATE DATABASE ' . $dbName));
        $this->assertEquals(0, $db->useDb($dbName)->query('CREATE TABLE table_1 (id INT)'));
        $this->assertEquals(1, $db->query('INSERT INTO table_1 (id) VALUES (:id)', ['id' => 1]));
        $this->assertEquals(1, $db->query('INSERT INTO table_1 (id) VALUES (:id)', ['id' => 2]));
        $this->assertEquals(1, $db->query('DROP DATABASE ' . $dbName));
    }

    /**
     * It should allow options operations
     *
     * @test
     */
    public function should_allow_options_operations(): void
    {
        $wpRootDir = FS::tmpDir('single_');
        $dbName = Random::dbName();
        $dbHost = Env::get('WORDPRESS_DB_HOST');
        $dbUser = Env::get('WORDPRESS_DB_USER');
        $dbPassword = Env::get('WORDPRESS_DB_PASSWORD');
        $db = new Db($dbName, $dbUser, $dbPassword, $dbHost, 'test_');
        $configurationData = new ConfigurationData();
        $configurationData->setConst('WP_PLUGIN_DIR', $wpRootDir . '/site-plugins');
        Installation::scaffold($wpRootDir, '6.1.1')
            ->configure($db, InstallationStateInterface::SINGLE_SITE, $configurationData)
            ->install(
                'https://wp.local',
                'admin',
                'password',
                'admin@wp.local',
                'Test');

        new Single($wpRootDir, $wpRootDir . '/wp-config.php', $db);

        $this->assertEquals('lorem', $db->getOption('non-existent-option', 'lorem'));
        foreach ([
                     'foo' => 'bar',
                     'bar' => 2389,
                     'object' => (object)['foo' => 'bar'],
                     'array' => ['foo' => 'bar'],
                     'associative array' => ['foo' => 'bar', 'bar' => 'foo'],
                     'null' => null,
                     'true' => true,
                     'false' => false,
                 ] as $name => $value) {
            $this->assertEquals(1, $db->updateOption($name, $value));
            $this->assertEquals($value, $db->getOption($name));
        }
    }
}

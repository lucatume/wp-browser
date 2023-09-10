<?php

namespace lucatume\WPBrowser\Module;

use Codeception\Exception\ModuleConfigException;
use Codeception\Lib\Di;
use Codeception\Lib\ModuleContainer;
use Codeception\Test\Unit;
use lucatume\WPBrowser\Module\Support\DbDump;
use lucatume\WPBrowser\Tests\Traits\LoopIsolation;
use lucatume\WPBrowser\Tests\Traits\UopzFunctions;
use lucatume\WPBrowser\Utils\Env;
use lucatume\WPBrowser\Utils\Filesystem as FS;
use lucatume\WPBrowser\WordPress\Database\SQLiteDatabase;
use lucatume\WPBrowser\WordPress\Installation;
use lucatume\WPBrowser\WordPress\InstallationState\InstallationStateInterface;
use PDO;
use RuntimeException;

class WPDbTest extends Unit
{
    use UopzFunctions;
    use LoopIsolation;

    protected $backupGlobals = false;
    private array $config = [
    ];
    private static ?PDO $pdo;

    /**
     * @before
     */
    public static function createTestDatabase(): void
    {
        $dbName = 'wpdb_module_test_db';
        $dbHost = Env::get('WORDPRESS_DB_HOST');
        $dbUser = Env::get('WORDPRESS_DB_USER');
        $dbPassword = Env::get('WORDPRESS_DB_PASSWORD');
        $pdo = new PDO("mysql:host=$dbHost", $dbUser, $dbPassword);
        if ($pdo->exec("DROP DATABASE IF EXISTS `" . $dbName . "`") === false) {
            throw new RuntimeException("Could not drop database $dbName");
        }
        if ($pdo->exec("CREATE DATABASE `" . $dbName . "`") === false) {
            throw new RuntimeException("Could not create database $dbName");
        }
        if ($pdo->exec('USE `' . $dbName . '`') === false) {
            throw new RuntimeException("Could not use database $dbName");
        }
        self::$pdo = $pdo;
    }

    /**
     * @after
     */
    public static function dropTestDatabase(): void
    {
        if (self::$pdo->exec("DROP DATABASE IF EXISTS `wpdb_module_test_db`") === false) {
            throw new RuntimeException("Could not drop database wpdb_module_test_db");
        }
    }


    /**
     * @return WPDb
     */
    private function module(): WPDb
    {
        $this->config = array_merge([
            'dsn' => 'mysql:host=' . Env::get('WORDPRESS_DB_HOST') . ';dbname=wpdb_module_test_db',
            'user' => Env::get('WORDPRESS_DB_USER'),
            'password' => Env::get('WORDPRESS_DB_PASSWORD'),
            'url' => 'https://some-wp.dev',
        ], $this->config);
        return new WPDb(new ModuleContainer(new Di, []), $this->config, new DbDump());
    }

    /**
     * It should allow specifying a dump file to import
     *
     * @test
     */
    public function it_should_allow_specifying_a_dump_file_to_import(): void
    {
        // The test SQL will drop and create the 'test_table' table.
        $sql = <<< SQL
DROP TABLE IF EXISTS `test_table`;
CREATE TABLE `test_table` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
--Add some entries in the table.
INSERT INTO `test_table` (`id`, `name`) VALUES
(1, 'test1'),
(2, 'test2'),
(3, 'test3');
SQL;

        $root = FS::tmpDir('wpdb_', ['dump.sql' => $sql]);
        $path = $root . '/dump.sql';

        $wpdb = $this->module();
        $wpdb->_initialize();
        $wpdb->importSqlDumpFile($path);

        $this->assertEquals(
            ['test_table'],
            self::$pdo->query("SHOW TABLES LIKE 'test_table'")->fetchAll(PDO::FETCH_COLUMN)
        );
    }

    /**
     * It should throw if specified dump file does not exist
     *
     * @test
     */
    public function it_should_throw_if_specified_dump_file_does_not_exist(): void
    {
        $path = __DIR__ . '/dump.sql';

        $this->expectException(ModuleConfigException::class);

        $sut = $this->module();
        $sut->_initialize();

        $sut->importSqlDumpFile($path);
    }

    /**
     * It should throw is specified dump file is not readable
     *
     * @test
     */
    public function it_should_throw_is_specified_dump_file_is_not_readable(): void
    {
        $root = FS::tmpDir('wpdb_', ['dump.sql' => 'SELECT 1']);
        $filepath = $root . '/dump.sql';
        $this->uopzSetFunctionReturn('is_readable', static function (string $file) use ($filepath) {
            return $file !== $filepath && is_readable($file);
        }, true);

        $this->expectException(ModuleConfigException::class);

        $sut = $this->module();
        $sut->_initialize();

        $sut->importSqlDumpFile($filepath);
    }

    /**
     * It should not try to replace the site url in the dump if url replacement is false
     *
     * @test
     */
    public function should_not_try_to_replace_the_site_url_in_the_dump_if_url_replacement_is_false(): void
    {
        $sql = <<< SQL
--Drop and recreate the wp_options table.
DROP TABLE IF EXISTS `wp_options`;
CREATE TABLE `wp_options` (
  `option_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `option_name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `option_value` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `autoload` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'yes',
  PRIMARY KEY (`option_id`),
  UNIQUE KEY `option_name` (`option_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
--Insert the siteurl option in the wp_options table.
INSERT INTO `wp_options` (`option_id`, `option_name`, `option_value`, `autoload`) VALUES
(1, 'siteurl', 'https://some-other-site.dev', 'yes');
--Insert the home option in the wp_options table.
INSERT INTO `wp_options` (`option_id`, `option_name`, `option_value`, `autoload`) VALUES
(2, 'home', 'https://some-other-site.dev/home', 'yes');
--Drop and recreate the test table
DROP TABLE IF EXISTS `test_urls`;
CREATE TABLE `test_urls` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `url` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
--Add some entries in the table.
INSERT INTO `test_urls` (`id`, `url`) VALUES
(1, 'https://some-wp.dev'),
(2, 'https://some-other-site.dev'),
(3, 'https://localhost:8080');
SQL;

        $root = FS::tmpDir('wpdb_', ['dump.sql' => $sql]);
        $this->config = [
            'url' => 'https://some-wp.dev',
            'urlReplacement' => false,
            'dump' => FS::relativePath(codecept_root_dir(), $root . '/dump.sql'),
            'populate' => true,
        ];

        $sut = $this->module();
        $sut->_initialize();
        $sut->_beforeSuite();

        $this->assertEquals('https://some-other-site.dev',
            $sut->grabFromDatabase('wp_options', 'option_value', ['option_name' => 'siteurl']));
        $this->assertEquals('https://some-other-site.dev/home',
            $sut->grabFromDatabase('wp_options', 'option_value', ['option_name' => 'home']));
        $this->assertEquals('https://some-wp.dev',
            self::$pdo->query("SELECT url FROM test_urls WHERE id = 1")->fetchColumn()
        );
        $this->assertEquals('https://some-other-site.dev',
            self::$pdo->query("SELECT url FROM test_urls WHERE id = 2")->fetchColumn()
        );
        $this->assertEquals('https://localhost:8080',
            self::$pdo->query("SELECT url FROM test_urls WHERE id = 3")->fetchColumn()
        );
    }

    /**
     * It should support using DB url to set up module
     *
     * @test
     */
    public function should_support_using_db_url_to_set_up_module(): void
    {
        $config = [
            'url' => 'https://some-wp.dev',
            'dbUrl' => 'mysql://User:secret!@localhost:3306/wordpress_test',
        ];
        $wpdb = new WPDb(new ModuleContainer(new Di, []), $config);

        $this->assertEquals('mysql:host=localhost;port=3306;dbname=wordpress_test', $wpdb->_getConfig('dsn'));
        $this->assertEquals('User', $wpdb->_getConfig('user'));
        $this->assertEquals('secret!', $wpdb->_getConfig('password'));
    }

    /**
     * It should throw throw if dbUrl not set and credentials are missing
     *
     * @test
     */
    public function should_throw_throw_if_db_url_not_set_and_credentials_are_missing(): void
    {
        $config = [
            'dbUrl' => 'mysql://User:secret!@localhost:3306/wordpress_test',
        ];

        $this->expectException(ModuleConfigException::class);

        $wpdb = new WPDb(new ModuleContainer(new Di, []), $config);

    }

    /**
     * It should throw if using Salite database and URL replacement function
     *
     * @test
     * @group sqlite
     */
    public function should_throw_if_using_salite_database_and_url_replacement_function(): void
    {
        $dbFile = codecept_output_dir(uniqid('wpdb_', true) . '.sqlite');
        $config = [
            'url' => 'http://example.com',
            'dbUrl' => 'sqlite://' . $dbFile,
            'createIfNotExists' => true,
            'dump' => 'tests/_data/sqlite/single-installation.sql',
            'populate' => true,
            'cleanup' => true,
        ];

        $this->expectException(ModuleConfigException::class);
        $wpdb = new WPDb(new ModuleContainer(new Di, []), $config);
    }

    /**
     * It should initialize correctly on non-existing sqlite file
     *
     * @test
     * @group sqlite
     */
    public function should_initialize_correctly_on_non_existing_sqlite_file(): void
    {
        $dbFile = codecept_output_dir(uniqid('wpdb_', true) . '.sqlite');
        $config = [
            'url' => 'http://example.com',
            'dbUrl' => 'sqlite://' . $dbFile,
            'urlReplacement' => false,
        ];

        $wpdb = new WPDb(new ModuleContainer(new Di, []), $config);
        $this->assertInIsolation(static function () use ($wpdb): void {
            $wpdb->_initialize();
        });
    }

    /**
     * It should initialize correctly on existing sqlite file
     *
     * @test
     * @group sqlite
     */
    public function should_initialize_correctly_on_existing_sqlite_file(): void
    {
        $dbFile = codecept_output_dir(uniqid('wpdb_', true) . '.sqlite');
        touch($dbFile);
        $config = [
            'url' => 'http://example.com',
            'dbUrl' => 'sqlite://' . $dbFile,
            'urlReplacement' => false,
        ];

        $wpdb = new WPDb(new ModuleContainer(new Di, []), $config);
        $wpdb->_initialize();
    }

    /**
     * It should initialize correctly with sqlite db and crateIfNotExists flag
     *
     * @test
     * @group sqlite
     */
    public function should_initialize_correctly_with_sqlite_db_and_crate_if_not_exists_flag(): void
    {
        $dbFile = codecept_output_dir(uniqid('wpdb_', true) . '.sqlite');
        $config = [
            'url' => 'http://example.com',
            'dbUrl' => 'sqlite://' . $dbFile,
            'createIfNotExists' => true,
            'urlReplacement' => false,
        ];

        $wpdb = new WPDb(new ModuleContainer(new Di, []), $config);
        $wpdb->_initialize();
    }

    /**
     * It should support post operations with sqlite db
     *
     * @test
     * @group sqlite
     */
    public function should_support_post_operations_with_sqlite_db(): void
    {
        $dbFile = codecept_output_dir(uniqid('wpdb_', true) . '.sqlite');
        $config = [
            'url' => 'http://example.com',
            'dbUrl' => 'sqlite://' . $dbFile,
            'createIfNotExists' => true,
            'dump' => 'tests/_data/sqlite/single-installation.sql',
            'populate' => true,
            'cleanup' => true,
            'urlReplacement' => false,
        ];

        $wpdb = new WPDb(new ModuleContainer(new Di, []), $config);
        $wpdb->_initialize();
        $wpdb->_beforeSuite();

        $postID = $wpdb->havePostInDatabase();

        $this->assertIsInt($postID);
        $this->assertGreaterThan(0, $postID);

        $wpdb->havePostmetaInDatabase($postID, 'test_meta', 'test_value');

        $this->assertEquals('test_value', $wpdb->grabPostmetaFromDatabase($postID, 'test_meta', true));

        $postIDs = $wpdb->haveManyPostsInDatabase(3);

        $this->assertCount(3, $postIDs);
        $this->assertContainsOnly('int', $postIDs);
        $this->assertGreaterThan(0, $postIDs[0]);
        $this->assertGreaterThan(0, $postIDs[1]);
        $this->assertGreaterThan(0, $postIDs[2]);
    }

    /**
     * It should support user operations with sqlite
     *
     * @test
     * @group sqlite
     */
    public function should_support_user_operations_with_sqlite(): void
    {
        $dbFile = codecept_output_dir(uniqid('wpdb_', true) . '.sqlite');
        $config = [
            'url' => 'http://example.com',
            'dbUrl' => 'sqlite://' . $dbFile,
            'createIfNotExists' => true,
            'dump' => 'tests/_data/sqlite/single-installation.sql',
            'populate' => true,
            'cleanup' => true,
            'urlReplacement' => false,
        ];

        $wpdb = new WPDb(new ModuleContainer(new Di, []), $config);
        $wpdb->_initialize();
        $wpdb->_beforeSuite();

        $userID = $wpdb->haveUserInDatabase('luca', 'contributor');

        $this->assertIsInt($userID);
        $this->assertGreaterThan(0, $userID);

        $wpdb->haveUserMetaInDatabase($userID, 'lorem', 'dolor');

        $this->assertEquals('dolor', $wpdb->grabUserMetaFromDatabase($userID, 'lorem', true));
    }

    /**
     * It should support blog operations with sqlite
     *
     * @test
     * @group sqlite
     */
    public function should_support_blog_operations_with_sqlite(): void
    {
        $dbFile = codecept_output_dir(uniqid('wpdb_', true) . '.sqlite');
        $config = [
            'url' => 'http://example.com',
            'dbUrl' => 'sqlite://' . $dbFile,
            'createIfNotExists' => true,
            'dump' => 'tests/_data/sqlite/multisite-subfolder-installation.sql',
            'populate' => true,
            'cleanup' => true,
            'urlReplacement' => false,
        ];

        $wpdb = new WPDb(new ModuleContainer(new Di, []), $config);
        $wpdb->_initialize();
        $wpdb->_beforeSuite();

        $blogId = $wpdb->haveBlogInDatabase('example.com/blog1', [], false);

        $this->assertIsInt($blogId);

        $wpdb->haveSiteMetaInDatabase($blogId, 'lorem', 'dolor');
        $wpdb->haveSiteMetaInDatabase($blogId, 'lorem', 'sit');

        $this->assertEquals('dolor', $wpdb->grabSiteMetaFromDatabase($blogId, 'lorem', true));
        $this->assertEquals(['dolor', 'sit'], $wpdb->grabSiteMetaFromDatabase($blogId, 'lorem', false));

        $wpdb->haveSiteOptionInDatabase('some_site_option', 'some_value');
        $this->assertEquals('some_value', $wpdb->grabSiteOptionFromDatabase('some_site_option'));

        $wpdb->useBlog($blogId);
        $title = 'Post ' . uniqid('test_', true);
        $postID = $wpdb->havePostInDatabase(['post_title' => $title]);

        $this->assertIsInt($postID);
        $this->assertGreaterThan(0, $postID);
        $this->assertEquals(
            $title,
            $wpdb->grabFromDatabase($wpdb->grabPrefixedTableNameFor('posts'), 'post_title', ['ID' => $postID])
        );
    }

    /**
     * It should support custom post operations with sqlite
     *
     * @test
     * @group sqlite
     */
    public function should_support_custom_post_operations_with_sqlite(): void
    {
        $dbFile = codecept_output_dir(uniqid('wpdb_', true) . '.sqlite');
        $config = [
            'url' => 'http://example.com',
            'dbUrl' => 'sqlite://' . $dbFile,
            'createIfNotExists' => true,
            'dump' => 'tests/_data/sqlite/multisite-subfolder-installation.sql',
            'populate' => true,
            'cleanup' => true,
            'urlReplacement' => false,
        ];

        $wpdb = new WPDb(new ModuleContainer(new Di, []), $config);
        $wpdb->_initialize();
        $wpdb->_beforeSuite();

        $postID = $wpdb->havePostInDatabase(['post_type' => 'book', 'post_title' => 'Alice in Wonderland']);

        $this->assertIsInt($postID);
        $this->assertGreaterThan(0, $postID);
        $this->assertEquals('Alice in Wonderland', $wpdb->grabPostFieldFromDatabase($postID, 'post_title'));
        $this->assertEquals('book', $wpdb->grabPostFieldFromDatabase($postID, 'post_type'));
    }
}

<?php

namespace lucatume\WPBrowser\WordPress\Database;

use lucatume\WPBrowser\Tests\Traits\TmpFilesCleanup;
use lucatume\WPBrowser\Traits\UopzFunctions;
use lucatume\WPBrowser\Utils\Filesystem as FS;
use lucatume\WPBrowser\WordPress\DbException;
use lucatume\WPBrowser\WordPress\Installation;
use lucatume\WPBrowser\WordPress\WPConfigFile;

class SqliteDatabaseTest extends \Codeception\Test\Unit
{
    use UopzFunctions;
    use TmpFilesCleanup;

    /**
     * It should throw if building on non existing directory
     *
     * @test
     */
    public function should_throw_if_building_on_non_existing_directory(): void
    {
        $this->expectException(DbException::class);
        $this->expectExceptionCode(SQLiteDatabase::ERR_DIR_NOT_FOUND);
        new SQLiteDatabase('/not-a-dir');
    }

    /**
     * It should throw if building on non writable directory
     *
     * @test
     */
    public function should_throw_if_building_on_non_writable_directory(): void
    {
        $this->setFunctionReturn('is_writable', false);
        $this->expectException(DbException::class);
        $this->expectExceptionCode(SQLiteDatabase::ERR_DIR_NOT_FOUND);
        new SQLiteDatabase(__DIR__);
    }

    /**
     * It should create Sqlite file when getting PDO
     *
     * @test
     */
    public function should_create_sqlite_file_when_getting_pdo(): void
    {
        $dir = FS::tmpDir('sqlite_');
        $file = '/db.sqlite';
        $db = new SQLiteDatabase($dir, $file);
        $db->getPDO();
        $this->assertFileExists($dir . '/' . $file);
        $this->assertEquals('wp_', $db->getTablePrefix());
    }

    /**
     * It should build PDO only once
     *
     * @test
     */
    public function should_build_pdo_only_once(): void
    {
        $dir = FS::tmpDir('sqlite_');
        $file = '/db.sqlite';
        $db = new SQLiteDatabase($dir, $file);
        $pdo = $db->getPDO();
        $this->assertSame($pdo, $db->getPDO());
        $this->assertSame($pdo, $db->getPDO());
    }

    /**
     * It should create db on create
     *
     * @test
     */
    public function should_create_db_on_create(): void
    {
        $dir = FS::tmpDir('sqlite_');
        $file = '/db.sqlite';
        $db = new SQLiteDatabase($dir, $file);
        $db->create();
        $this->assertFileExists($dir . '/' . $file);
    }

    /**
     * It should return empty strings for host, user, password
     *
     * @test
     */
    public function should_return_empty_strings_for_host_user_password(): void
    {
        $dir = FS::tmpDir('sqlite_');
        $file = '/db.sqlite';
        $db = new SQLiteDatabase($dir, $file);
        $this->assertSame('', $db->getDbHost());
        $this->assertSame('', $db->getDbUser());
        $this->assertSame('', $db->getDbPassword());
    }

    /**
     * It should return the file name as db name
     *
     * @test
     */
    public function should_return_the_file_name_as_db_name(): void
    {
        $dir = FS::tmpDir('sqlite_');
        $file = '/my-db.sqlite';
        $db = new SQLiteDatabase($dir, $file);
        $this->assertSame('my-db.sqlite', $db->getDbName());
    }

    /**
     * It should delete the db file on drop
     *
     * @test
     */
    public function should_delete_the_db_file_on_drop(): void
    {
        $dir = FS::tmpDir('sqlite_');
        $file = '/db.sqlite';
        $db = new SQLiteDatabase($dir, $file);
        $db->create();
        $this->assertTrue($db->exists());
        $this->assertFileExists($dir . '/' . $file);
        $db->drop();
        $this->assertFalse($db->exists());
        $this->assertFileDoesNotExist($dir . '/' . $file);
    }

    /**
     * It should throw if file cannot be unlinked during drop
     *
     * @test
     */
    public function should_throw_if_file_cannot_be_unlinked_during_drop(): void
    {
        $dir = FS::tmpDir('sqlite_');
        $file = '/db.sqlite';
        $db = new SQLiteDatabase($dir, $file);
        $db->create();
        $this->setFunctionReturn('unlink', false);
        $this->expectException(DbException::class);
        $this->expectExceptionCode(SQLiteDatabase::ERR_DROP_DB_FAILED);
        $db->drop();
    }

    /**
     * It should throw if trying to change database
     *
     * @test
     */
    public function should_throw_if_trying_to_change_database(): void
    {
        $dir = FS::tmpDir('sqlite_');
        $file = '/db.sqlite';
        $db = new SQLiteDatabase($dir, $file);
        $this->expectException(DbException::class);
        $db->useDb('new-db');
    }

    /**
     * It should return dbURL and DSN correctly
     *
     * @test
     */
    public function should_return_db_url_and_dsn_correctly(): void
    {
        $dir = FS::tmpDir('sqlite_');
        $file = 'db.sqlite';
        $db = new SQLiteDatabase($dir, $file);
        $this->assertSame('sqlite://' . $dir . '/' . $file, $db->getDbURL());
        $this->assertSame('sqlite:' . $dir . '/' . $file, $db->getDSN());
    }

    /**
     * It should run queries correctly
     *
     * @test
     */
    public function should_run_queries_correctly(): void
    {
        $dir = FS::tmpDir('sqlite_');
        $file = 'db.sqlite';
        $db = new SQLiteDatabase($dir, $file);
        $db->create();
        $this->assertEquals(0,
            $db->query('CREATE TABLE wp_options (option_id INTEGER PRIMARY KEY, option_name TEXT NOT NULL, option_value TEXT NOT NULL, autoload TEXT NOT NULL)'));
        $this->assertEquals(1,
            $db->query('INSERT INTO wp_options (option_name, option_value, autoload) VALUES ("siteurl", "http://localhost", "yes")'));
        $this->assertEquals(1,
            $db->query('INSERT INTO wp_options (option_name, option_value, autoload) VALUES ("home", "http://localhost", "yes")'));
        $this->assertEquals(0, $db->query('SELECT * FROM wp_options'));
        $this->assertEquals('http://localhost', $db->getoption('siteurl'));
        $this->assertEquals('http://localhost', $db->getoption('home'));
        $this->assertEquals('test-test-test', $db->getoption('some-option', 'test-test-test'));
        $this->assertEquals(1, $db->updateOption('some-option', 'some-value'));
        $this->assertEquals('some-value', $db->getoption('some-option', 'test-test-test'));
    }

    /**
     * It should allow importing Sqlite dump
     *
     * @test
     */
    public function should_allow_importing_sqlite_dump(): void
    {
        $dump = codecept_data_dir('dump.sqlite');
        $dir = FS::tmpDir('sqlite_');
        $file = 'db.sqlite';
        $db = new SQLiteDatabase($dir, $file);
        $db->create();
        $db->import($dump);
        $this->assertEquals('http://example.com', $db->getOption('siteurl'));
        $this->assertEquals('http://example.com', $db->getOption('home'));
        $this->assertEquals('Example', $db->getOption('blogname'));
        $this->assertEquals('0', $db->getOption('users_can_register'));
        $this->assertEquals('hello@wordpress.test', $db->getOption('admin_email'));
    }

    /**
     * It should throw if trying to import from non-existing file
     *
     * @test
     */
    public function should_throw_if_trying_to_import_from_non_existing_file(): void
    {
        $dump = codecept_data_dir('not-existing.sqlite');
        $dir = FS::tmpDir('sqlite_');
        $file = 'db.sqlite';

        $this->expectException(DbException::class);
        $this->expectExceptionCode(DbException::DUMP_FILE_NOT_EXIST);

        $db = new SQLiteDatabase($dir, $file);
        $db->import($dump);
    }

    /**
     * It should throw if trying to import import non-readable file
     *
     * @test
     */
    public function should_throw_if_trying_to_import_import_non_readable_file(): void
    {
        $dump = codecept_data_dir('dump.sqlite');
        $dir = FS::tmpDir('sqlite_');
        $file = 'db.sqlite';
        $this->setFunctionReturn('file_get_contents', false);

        $this->expectException(DbException::class);
        $this->expectExceptionCode(DbException::DUMP_FILE_NOT_READABLE);

        $db = new SQLiteDatabase($dir, $file);
        $db->import($dump);
    }

    /**
     * It should allow getting the db directory and file
     *
     * @test
     */
    public function should_allow_getting_the_db_directory_and_file(): void
    {
        $dir = FS::tmpDir('sqlite_');
        $file = '\db.sqlite';
        $db = new SQLiteDatabase($dir . '/', $file);
        $this->assertSame($dir, $db->getDbDir());
        $this->assertSame('db.sqlite', $db->getDbFile());
    }

    /**
     * It should allow building from wp-config.file
     *
     * @test
     */
    public function should_allow_building_from_wp_config_file(): void
    {
        $wpRootDir = FS::tmpDir('sqlite_');
        $createDb = new SQLiteDatabase($wpRootDir, 'db.sqlite');
        $installation = Installation::scaffold($wpRootDir, '6.1.1');
        $installation->configure($createDb);
        $this->assertTrue($installation->usesSqlite());

        $dbFromConfig = SQLiteDatabase::fromWpConfigFile(new WPConfigFile($wpRootDir, $wpRootDir . '/wp-config.php'));
        $this->assertSame($createDb->getDbURL(), $dbFromConfig->getDbURL());
        $this->assertSame($createDb->getDSN(), $dbFromConfig->getDSN());
    }

    /**
     * It should allow importing and exporting the database
     *
     * @test
     */
    public function should_allow_importing_and_exporting_the_database(): void
    {
        $dump = codecept_data_dir('dump.sqlite');
        $dir = FS::tmpDir('sqlite_');
        $file = 'db.sqlite';
        $db = new SQLiteDatabase($dir, $file);
        $db->import($dump);

        $this->assertEquals('http://example.com', $db->getOption('siteurl'));
        $this->assertEquals('http://example.com', $db->getOption('home'));
        $this->assertEquals('Example', $db->getOption('blogname'));
        $this->assertEquals('0', $db->getOption('users_can_register'));
        $this->assertEquals('hello@wordpress.test', $db->getOption('admin_email'));

        $dumpFile = tempnam(sys_get_temp_dir(), 'sqlite_');
        $db->dump($dumpFile);

        $this->assertFileExists($dumpFile);

        $checkDb = new SQLiteDatabase($dir, 'checkdb.sqlite');
        $checkDb->import($dumpFile);
        $this->assertEquals('http://example.com', $checkDb->getOption('siteurl'));
        $this->assertEquals('http://example.com', $checkDb->getOption('home'));
        $this->assertEquals('Example', $checkDb->getOption('blogname'));
        $this->assertEquals('0', $checkDb->getOption('users_can_register'));
        $this->assertEquals('hello@wordpress.test', $checkDb->getOption('admin_email'));
    }

    /**
     * It should throw if database dump file cannot be written
     *
     * @test
     */
    public function should_throw_if_database_dump_file_cannot_be_written(): void
    {
        $dump = codecept_data_dir('dump.sqlite');
        $dir = FS::tmpDir('sqlite_');
        $file = 'db.sqlite';
        $db = new SQLiteDatabase($dir, $file);
        $db->import($dump);

        $this->setFunctionReturn('file_put_contents', false);

        $this->expectException(DbException::class);
        $this->expectExceptionCode(DbException::FAILED_DUMP);

        $dumpFile = tempnam(sys_get_temp_dir(), 'sqlite_');
        $db->dump($dumpFile);
    }
}

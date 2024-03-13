<?php


namespace lucatume\WPBrowser\Command;

use lucatume\WPBrowser\Command\DbExport;
use lucatume\WPBrowser\Exceptions\InvalidArgumentException;
use lucatume\WPBrowser\Exceptions\RuntimeException;
use lucatume\WPBrowser\Tests\Traits\TmpFilesCleanup;
use lucatume\WPBrowser\Utils\Env;
use lucatume\WPBrowser\Utils\Filesystem;
use lucatume\WPBrowser\Utils\Random;
use lucatume\WPBrowser\WordPress\Database\MysqlDatabase;
use lucatume\WPBrowser\WordPress\Database\SQLiteDatabase;
use lucatume\WPBrowser\WordPress\DbException;
use lucatume\WPBrowser\WordPress\Installation;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\BufferedOutput;
use \UnitTester;

/**
 * @group slow
 */
class DbExportTest extends \Codeception\Test\Unit
{
    use TmpFilesCleanup;

    /**
     * It should throw if path does not point to installation directory
     *
     * @test
     */
    public function should_throw_if_path_does_not_point_to_installation_directory(): void
    {
        $path = Filesystem::tmpDir('dbexport_');
        $input = new StringInput("$path $path/dump.sql");
        $output = new BufferedOutput();

        $command = new DbExport();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionCode(DbExport::INVALID_PATH);

        $command->run($input, $output);
    }

    /**
     * It should throw if dump dir does not exist
     *
     * @test
     */
    public function should_throw_if_dump_dir_does_not_exist(): void
    {
        $path = Filesystem::tmpDir('dbexport_');
        Installation::scaffold($path);
        $input = new StringInput("$path $path/dumps/dump.sql");
        $output = new BufferedOutput();

        $command = new DbExport();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionCode(DbExport::DUMP_DIR_NOT_FOUND);

        $command->run($input, $output);
    }

    /**
     * It should throw if installation db cannot be found
     *
     * @test
     */
    public function should_throw_if_installation_db_cannot_be_found(): void
    {
        $path = Filesystem::tmpDir('dbexport_');
        $dbName = Random::dbName();
        $dbHost = Env::get('WORDPRESS_DB_HOST');
        $dbUser = Env::get('WORDPRESS_DB_USER');
        $dbPassword = Env::get('WORDPRESS_DB_PASSWORD');
        $db = new MysqlDatabase($dbName, $dbUser, $dbPassword, $dbHost, 'test_');
        Installation::scaffold($path)
            ->configure($db);
        $input = new StringInput("$path $path/dump.sql");
        $output = new BufferedOutput();

        $command = new DbExport();

        $this->expectException(DbException::class);
        $this->expectExceptionCode(DbException::FAILED_DUMP);

        $command->run($input, $output);
    }

    /**
     * It should correctly dump db
     *
     * @test
     */
    public function should_correctly_dump_db(): void
    {
        $path = Filesystem::tmpDir('dbexport_');
        $dbName = Random::dbName();
        $dbHost = Env::get('WORDPRESS_DB_HOST');
        $dbUser = Env::get('WORDPRESS_DB_USER');
        $dbPassword = Env::get('WORDPRESS_DB_PASSWORD');
        $db = new MysqlDatabase($dbName, $dbUser, $dbPassword, $dbHost, 'test_');
        Installation::scaffold($path)
            ->configure($db)
            ->install(
                'http://wordpress.local',
                'admin',
                'admin',
                'admin@wordpress.local',
                'Test'
            );
        $input = new StringInput("$path $path/dump.sql");
        $output = new BufferedOutput();

        $command = new DbExport();

        $exit = $command->run($input, $output);

        $this->assertEquals(0, $exit);
        $this->assertFileExists("$path/dump.sql");
    }

    /**
     * It should correctly dump sqlite db
     *
     * @test
     */
    public function should_correctly_dump_sqlite_db(): void
    {
        $path = Filesystem::tmpDir('dbexport_');
        $db = new SQLiteDatabase($path, 'db.sqlite');
        Installation::scaffold($path)
            ->configure($db)
            ->install(
                'http://wordpress.local',
                'admin',
                'admin',
                'admin@wordpress.local',
                'Test'
            );
        $input = new StringInput("$path $path/dump.sql");
        $output = new BufferedOutput();

        $command = new DbExport();

        $exit = $command->run($input, $output);

        $this->assertEquals(0, $exit);
        $this->assertFileExists("$path/dump.sql");
    }
}

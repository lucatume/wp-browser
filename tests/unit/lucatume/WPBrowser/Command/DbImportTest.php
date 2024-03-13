<?php


namespace lucatume\WPBrowser\Command;

use lucatume\WPBrowser\Exceptions\InvalidArgumentException;
use lucatume\WPBrowser\Tests\Traits\TmpFilesCleanup;
use lucatume\WPBrowser\Utils\Env;
use lucatume\WPBrowser\Utils\Filesystem;
use lucatume\WPBrowser\Utils\Random;
use lucatume\WPBrowser\WordPress\Database\MysqlDatabase;
use lucatume\WPBrowser\WordPress\Database\SQLiteDatabase;
use lucatume\WPBrowser\WordPress\DbException;
use lucatume\WPBrowser\WordPress\Installation;
use lucatume\WPBrowser\WordPress\InstallationException;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\BufferedOutput;

/**
 * @group slow
 */
class DbImportTest extends \Codeception\Test\Unit
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

        $command = new DbImport();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionCode(DbImport::INVALID_PATH);

        $command->run($input, $output);
    }

    /**
     * It should throw if dump file does not exist
     *
     * @test
     */
    public function should_throw_if_dump_file_does_not_exist(): void
    {
        $path = Filesystem::tmpDir('dbexport_');
        Installation::scaffold($path);
        $input = new StringInput("$path $path/dump.sql");
        $output = new BufferedOutput();

        $command = new DbImport();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionCode(DbImport::DUMP_FILE_NOT_FOUND);

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
        touch("$path/dump.sql");
        Installation::scaffold($path);
        $input = new StringInput("$path $path/dump.sql");
        $output = new BufferedOutput();

        $command = new DbImport();

        $this->expectException(InstallationException::class);
        $this->expectExceptionCode(InstallationException::STATE_SCAFFOLDED);

        $command->run($input, $output);
    }

    /**
     * It should correctly import db
     *
     * @test
     */
    public function should_correctly_import_db(): void
    {
        $path = Filesystem::tmpDir('dbexport_');
        $dbName = Random::dbName();
        $dbHost = Env::get('WORDPRESS_DB_HOST');
        $dbUser = Env::get('WORDPRESS_DB_USER');
        $dbPassword = Env::get('WORDPRESS_DB_PASSWORD');
        $db = new MysqlDatabase($dbName, $dbUser, $dbPassword, $dbHost, 'test_');
        $installation = Installation::scaffold($path)
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

        (new DbExport())->run($input, $output);
        $installation->runWpCliCommandOrThrow(['db', 'reset', '--yes']);

        $this->assertFileExists("$path/dump.sql");

        $input = new StringInput("$path $path/dump.sql");
        $output = new BufferedOutput();

        $command = new DbImport();
        $exit = $command->run($input, $output);

        $this->assertEquals(0, $exit);
    }

    /**
     * It should correctly import sqlite db
     *
     * @test
     */
    public function should_correctly_import_sqlite_db(): void
    {
        $path = Filesystem::tmpDir('dbexport_');
        $db = new SQLiteDatabase($path, 'db.sqlite');
        $installation = Installation::scaffold($path)
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

        (new DbExport())->run($input, $output);
        // Drop the database by deleting it.
        if (!unlink("$path/db.sqlite")) {
            throw new \RuntimeException("Could not delete sqlite db file.");
        }

        $this->assertFileExists("$path/dump.sql");

        $input = new StringInput("$path $path/dump.sql");
        $output = new BufferedOutput();

        $command = new DbImport();
        $exit = $command->run($input, $output);

        $this->assertEquals(0, $exit);
    }
}

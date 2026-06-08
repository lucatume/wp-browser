<?php

namespace lucatume\WPBrowser\WordPress\Database;

use lucatume\WPBrowser\Tests\Traits\FastScaffold;
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
    use FastScaffold;

    /**
     * It should throw if building on non existing directory
     *
     * @test
     * @group fast
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
     * @group fast
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
     * @group fast
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
     * @group fast
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
     * @group fast
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
     * @group fast
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
     * @group fast
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
     * @group fast
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
     * @group fast
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
     * @group fast
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
     * @group fast
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
     * @group fast
     */
    public function should_run_queries_correctly(): void
    {
        $dir = FS::tmpDir('sqlite_');
        $file = 'db.sqlite';
        $db = new SQLiteDatabase($dir, $file);
        $db->create();
        $this->assertEquals(
            0,
            $db->query('CREATE TABLE wp_options (option_id INTEGER PRIMARY KEY, option_name TEXT NOT NULL, option_value TEXT NOT NULL, autoload TEXT NOT NULL)')
        );
        $this->assertEquals(
            1,
            $db->query('INSERT INTO wp_options (option_name, option_value, autoload) VALUES ("siteurl", "http://localhost", "yes")')
        );
        $this->assertEquals(
            1,
            $db->query('INSERT INTO wp_options (option_name, option_value, autoload) VALUES ("home", "http://localhost", "yes")')
        );
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
     * @group fast
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
     * @group fast
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
     * @group fast
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
     * @group fast
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
     * @group slow
     */
    public function should_allow_building_from_wp_config_file(): void
    {
        $wpRootDir = FS::tmpDir('sqlite_');
        $createDb = new SQLiteDatabase($wpRootDir, 'db.sqlite');
        $installation = $this->fastScaffold($wpRootDir, '6.1.1');
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
     * @group fast
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
     * It should export values with more newlines than the SQLite expression depth limit
     *
     * @test
     * @group fast
     */
    public function should_export_values_with_many_newlines_into_an_importable_dump(): void
    {
        $dump = codecept_data_dir('dump.sqlite');
        $dir = FS::tmpDir('sqlite_');
        $db = new SQLiteDatabase($dir, 'db.sqlite');
        $db->import($dump);

        // SQLite caps expression-tree depth at 1000; a value with more newlines than that
        // would overflow a `'...' || char(10) || '...'` concatenation chain on import.
        $manyLines = str_repeat("line\n", 2000);
        $db->updateOption('big_multiline', $manyLines);

        $dumpFile = tempnam(sys_get_temp_dir(), 'sqlite_');
        $db->dump($dumpFile);

        $checkDb = new SQLiteDatabase($dir, 'checkdb.sqlite');
        $checkDb->import($dumpFile);

        $this->assertEquals($manyLines, $checkDb->getOption('big_multiline'));
    }

    /**
     * It should round-trip fuzzed values through export, import and export again
     *
     * @test
     * @group fast
     */
    public function should_round_trip_fuzzed_values_through_export_import_export(): void
    {
        // Fixed seed so any failure reproduces identically. NUL bytes are excluded on purpose:
        // the ext-sqlite3 reader truncates TEXT at NUL, which is a separate, known limitation.
        mt_srand(0x5EED);
        $dir = FS::tmpDir('sqlite_fuzz_');

        for ($iteration = 0; $iteration < 100; $iteration++) {
            $values = [];
            for ($id = 1, $count = mt_rand(1, 15); $id <= $count; $id++) {
                $values[$id] = $this->fuzzValue();
            }

            $srcDb = new SQLiteDatabase($dir, "src_$iteration.sqlite");
            $srcDb->query('CREATE TABLE fuzz (id INTEGER PRIMARY KEY, val TEXT NOT NULL)');
            foreach ($values as $id => $value) {
                $srcDb->query('INSERT INTO fuzz (id, val) VALUES (:id, :val)', [':id' => $id, ':val' => $value]);
            }

            $dumpA = "$dir/dump_a_$iteration.sql";
            $srcDb->dump($dumpA);

            $dstDb = new SQLiteDatabase($dir, "dst_$iteration.sqlite");
            $dstDb->import($dumpA);

            $dumpB = "$dir/dump_b_$iteration.sql";
            $dstDb->dump($dumpB);

            // Export/import/export is stable: re-dumping the imported database yields the same SQL.
            $this->assertSame(
                file_get_contents($dumpA),
                file_get_contents($dumpB),
                "Dump is not idempotent across export/import/export at iteration $iteration."
            );

            // The data itself survived the round-trip byte-for-byte.
            $readBack = (new \PDO($dstDb->getDsn()))
                ->query('SELECT val FROM fuzz ORDER BY id')
                ->fetchAll(\PDO::FETCH_COLUMN);

            $this->assertSame(
                array_values($values),
                $readBack,
                "Imported values do not match the originals at iteration $iteration."
            );
        }
    }

    /**
     * It should throw if database dump file cannot be written
     *
     * @test
     * @group fast
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

    public static function optionsDataProvider(): array
    {
        return [
            'string option' => ['http://example.com', 'http://example.com'],
            'int option' => [23, '23'],
            'boolean true option' => [true, '1'],
            'boolean false option' => [false, ''],
            'array option' => [[1, 2, 3], [1, 2, 3]],
            'object option' => [(object) ['a' => 'b'], (object) ['a' => 'b']],
            'null option' => [null, null],
        ];
    }

    /**
     * @test
     * @dataProvider optionsDataProvider
     * @group fast
     */
    public function should_read_and_write_options_correctly(mixed $optionValue, mixed $expectedOptionValue): void
    {
        $dump = codecept_data_dir('dump.sqlite');
        $dir = FS::tmpDir('sqlite_');
        $file = 'db.sqlite';
        $db = new SQLiteDatabase($dir, $file);
        $db->import($dump);

        $db->updateOption('test', $optionValue);

        $this->assertEquals($expectedOptionValue, $db->getOption('test'));
    }

    private function fuzzValue(): string
    {
        switch (mt_rand(0, 8)) {
            case 0: // Printable ASCII.
                return $this->randomBytes(mt_rand(0, 80), 0x20, 0x7e);
            case 1: // Quote and backslash heavy.
                $parts = ["'", '"', '\\', "''", "\\'", '`', '%', '_'];
                $value = '';
                for ($i = 0, $n = mt_rand(1, 30); $i < $n; $i++) {
                    $value .= $parts[mt_rand(0, count($parts) - 1)] . $this->randomBytes(mt_rand(0, 4), 0x61, 0x7a);
                }
                return $value;
            case 2: // More newlines than the SQLite expression-depth limit.
                $value = '';
                for ($i = 0, $n = mt_rand(1, 1500); $i < $n; $i++) {
                    $value .= 'l' . mt_rand(0, 9) . "\n";
                }
                return $value;
            case 3: // CR / LF / tab mix.
                $whitespace = ["\n", "\r", "\r\n", "\t", ' '];
                $value = '';
                for ($i = 0, $n = mt_rand(1, 200); $i < $n; $i++) {
                    $value .= $whitespace[mt_rand(0, count($whitespace) - 1)] . $this->randomBytes(mt_rand(0, 3), 0x61, 0x7a);
                }
                return $value;
            case 4: // Control characters, no NUL.
                return $this->randomBytes(mt_rand(0, 64), 0x01, 0x1f);
            case 5: // Multibyte UTF-8.
                $pool = ['é', 'ñ', 'ü', '中', '文', 'Ω', 'λ', '—', '„', '🎉', '😀', 'ζ'];
                $value = '';
                for ($i = 0, $n = mt_rand(0, 40); $i < $n; $i++) {
                    $value .= $pool[mt_rand(0, count($pool) - 1)];
                }
                return $value;
            case 6: // Arbitrary binary, no NUL; usually invalid UTF-8.
                return $this->randomBytes(mt_rand(0, 96), 0x01, 0xff);
            case 7: // SQL tokens and the encoder's own markers.
                $pool = [
                    "' || char(10) || '",
                    "CAST(X'6162' AS TEXT)",
                    "'); DROP TABLE fuzz; --",
                    ";\nSELECT 1;",
                    "PRAGMA foreign_keys = OFF;",
                    'a:1:{s:3:"key";s:5:"value";}',
                    'N;',
                ];
                $value = '';
                for ($i = 0, $n = mt_rand(1, 6); $i < $n; $i++) {
                    $value .= $pool[mt_rand(0, count($pool) - 1)];
                }
                return $value;
            default: // Empty or whitespace.
                return mt_rand(0, 1) === 0 ? '' : str_repeat(" \t", mt_rand(0, 12));
        }
    }

    private function randomBytes(int $length, int $min, int $max): string
    {
        $bytes = '';
        for ($i = 0; $i < $length; $i++) {
            $bytes .= chr(mt_rand($min, $max));
        }
        return $bytes;
    }
}

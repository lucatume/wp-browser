<?php

namespace unit\lucatume\WPBrowser\ManagedProcess;

use Closure;
use Codeception\Test\Unit;
use Generator;
use lucatume\WPBrowser\Adapters\Symfony\Component\Process\Process;
use lucatume\WPBrowser\Exceptions\RuntimeException;
use lucatume\WPBrowser\ManagedProcess\MysqlServer;
use lucatume\WPBrowser\Tests\Traits\ClassStubs;
use lucatume\WPBrowser\Tests\Traits\TmpFilesCleanup;
use lucatume\WPBrowser\Traits\UopzFunctions;
use lucatume\WPBrowser\Utils\Download;
use lucatume\WPBrowser\Utils\Filesystem as FS;
use lucatume\WPBrowser\Utils\MachineInformation;
use PDO;
use PharData;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\AssertionFailedError;

class MysqlServerTest extends Unit
{
    use UopzFunctions;
    use TmpFilesCleanup;
    use ClassStubs;

    private Closure $unsetMkdirFunctionReturn;

    public function _before(): void
    {
        $this->setFunctionReturn('file_put_contents', function (string $file): void {
            throw new AssertionFAiledError('Unexpected file_put_contents call for ' . $file);
        });

        $this->setClassMock(Process::class, $this->makeEmptyClass(Process::class, [
            '__construct' => function (array $command) {
                throw new AssertionFAiledError('Unexpected Process::__construct call for ' . print_r($command, true));
            }
        ]));

        $this->setClassMock(Download::class, $this->makeEmpty(Download::class, [
            'fileFromUrl' => function (string $url, string $file): void {
                throw new AssertionFAiledError("Unexpected Download::fileFromUrl call for URL $url and file $file");
            }
        ]));

        $server = (new MysqlServer());
        $pidFile = $server->getPidFile();
        $this->setFunctionReturn('is_file', function (string $file) use ($pidFile): bool {
            return $file === $pidFile ? false : is_file($file);
        }, true);

        $directory = $server->getDirectory();
        $this->unsetMkdirFunctionReturn = $this->setFunctionReturn(
            'mkdir',
            function (string $dir, ...$rest) use ($directory): bool {
                if ($dir === $directory) {
                    return mkdir($dir, ...$rest);
                }

                throw new AssertionFailedError('Unexpected mkdir call for directory ' . $dir);
            },
            true
        );
    }

    public function osAndArchDataProvider(): array
    {
        return [
            'windows x86_64' => [
                MachineInformation::OS_WINDOWS,
                MachineInformation::ARCH_X86_64,
                FS::cacheDir() . '/mysql-server/mysql-8.4.3-winx64',
                FS::cacheDir() . '/mysql-server/mysql-8.4.3-winx64/bin/mysqld.exe',
            ],
            'linux x86_64' => [
                MachineInformation::OS_LINUX,
                MachineInformation::ARCH_X86_64,
                FS::cacheDir() . '/mysql-server/mysql-8.4.3-linux-glibc2.17-x86_64-minimal',
                FS::cacheDir() . '/mysql-server/mysql-8.4.3-linux-glibc2.17-x86_64-minimal/bin/mysqld',
            ],
            'linux arm64' => [
                MachineInformation::OS_LINUX,
                MachineInformation::ARCH_ARM64,
                FS::cacheDir() . '/mysql-server/mysql-8.4.3-linux-glibc2.17-aarch64-minimal',
                FS::cacheDir() . '/mysql-server/mysql-8.4.3-linux-glibc2.17-aarch64-minimal/bin/mysqld',
            ],
            'darwin x86_64' => [
                MachineInformation::OS_DARWIN,
                MachineInformation::ARCH_X86_64,
                FS::cacheDir() . '/mysql-server/mysql-8.4.3-macos14-x86_64',
                FS::cacheDir() . '/mysql-server/mysql-8.4.3-macos14-x86_64/bin/mysqld',
            ],
            'darwin arm64' => [
                MachineInformation::OS_DARWIN,
                MachineInformation::ARCH_ARM64,
                FS::cacheDir() . '/mysql-server/mysql-8.4.3-macos14-arm64',
                FS::cacheDir() . '/mysql-server/mysql-8.4.3-macos14-arm64/bin/mysqld',
            ]
        ];
    }

    /**
     * @dataProvider osAndArchDataProvider
     */
    public
    function testConstructorWithDefaults(
        string $os,
        string $arch,
        string $expectedExtractedPath,
        string $expectedBinaryPath
    ): void {
        $mysqlServer = new MysqlServer();
        $machineInformation = new MachineInformation($os, $arch);
        $mysqlServer->setMachineInformation($machineInformation);
        $directory = FS::cacheDir() . '/mysql-server';
        $notNormalizedDirectory = $machineInformation->isWindows() ?
            str_replace('/', '\\', $directory)
            : $directory;
        $this->assertEquals($notNormalizedDirectory, $mysqlServer->getDirectory());
        $this->assertEquals($directory, $mysqlServer->getDirectory(true));
        $this->assertEquals(MysqlServer::PORT_DEFAULT, $mysqlServer->getPort());
        $this->assertEquals('wordpress', $mysqlServer->getDatabase());
        $this->assertEquals('wordpress', $mysqlServer->getUser());
        $this->assertEquals('wordpress', $mysqlServer->getPassword());
        $this->assertEquals('', $mysqlServer->getRootPassword());
        $notNormalizedBinaryPath = $machineInformation->isWindows() ?
            str_replace('/', '\\', $expectedBinaryPath)
            : $expectedBinaryPath;
        $this->assertEquals($notNormalizedBinaryPath, $mysqlServer->getBinaryPath());
        $this->assertEquals($expectedBinaryPath, $mysqlServer->getBinaryPath(true));
        $pidFilePath = codecept_output_dir(MysqlServer::PID_FILE_NAME);
        $notNormalizedPidFilePath = $machineInformation->isWindows() ?
            str_replace('/', '\\', $pidFilePath)
            : $pidFilePath;
        $this->assertEquals($notNormalizedPidFilePath, $mysqlServer->getPidFilePath());
        $this->assertEquals($pidFilePath, $mysqlServer->getPidFilePath(true));
        $dataDir = FS::cacheDir() . '/mysql-server/data';
        $notNormalizedDataDir = $machineInformation->isWindows() ?
            str_replace('/', '\\', $dataDir)
            : $dataDir;
        $this->assertEquals($notNormalizedDataDir, $mysqlServer->getDataDir());
        $this->assertEquals($dataDir, $mysqlServer->getDataDir(true));
        $notNormalizedExtractedPath = $machineInformation->isWindows() ?
            str_replace('/', '\\', $expectedExtractedPath)
            : $expectedExtractedPath;
        $this->assertEquals($notNormalizedExtractedPath, $mysqlServer->getExtractedPath());
        $this->assertEquals($expectedExtractedPath, $mysqlServer->getExtractedPath(true));
        $shareDir = $expectedExtractedPath . '/share';
        $notNormalizedShareDir = $machineInformation->isWindows() ?
            str_replace('/', '\\', $shareDir)
            : $shareDir;
        $this->assertEquals($notNormalizedShareDir, $mysqlServer->getShareDir());
        $this->assertEquals($shareDir, $mysqlServer->getShareDir(true));
        $this->assertFalse($mysqlServer->isUsingCustomBinary());
        $socketPath = $directory . '/mysql.sock';
        $notNormalizedSocketPath = $machineInformation->isWindows() ?
            str_replace('/', '\\', $socketPath)
            : $socketPath;
        $this->assertEquals($notNormalizedSocketPath, $mysqlServer->getSocketPath());
        $this->assertEquals($socketPath, $mysqlServer->getSocketPath(true));
    }

    public function testConstructorCustomValues(): void
    {
        $mysqlServer = new MysqlServer(__DIR__, 2389, 'test', 'luca', 'secret');
        $this->assertEquals(2389, $mysqlServer->getPort());
        $this->assertEquals('test', $mysqlServer->getDatabase());
        $this->assertEquals('luca', $mysqlServer->getUser());
        $this->assertEquals('secret', $mysqlServer->getPassword());
        $this->assertEquals('', $mysqlServer->getRootPassword());
    }

    public function testConstructorWithRootUser(): void
    {
        $mysqlServer = new MysqlServer(__DIR__, 2389, 'test', 'root', 'secret');
        $this->assertEquals(2389, $mysqlServer->getPort());
        $this->assertEquals('test', $mysqlServer->getDatabase());
        $this->assertEquals('root', $mysqlServer->getUser());
        $this->assertEquals('secret', $mysqlServer->getPassword());
        $this->assertEquals('secret', $mysqlServer->getRootPassword());
    }

    public function testConstructorCreatesDirectoryIfNotExists(): void
    {
        ($this->unsetMkdirFunctionReturn)();
        $dir = FS::tmpDir('mysql-server_');
        $mysqlServer = new MysqlServer($dir);
        $this->assertDirectoryExists($dir);
    }

    /**
     * @dataProvider osAndArchDataProvider
     */
    public function testConstructorWithCustomBinary(string $os, string $arch): void
    {
        $this->setFunctionReturn('is_executable', function (string $file): bool {
            return $file === '/usr/bin/mysqld' ? true : is_executable($file);
        }, true);
        $this->setFunctionReturn('is_dir', function (string $dir): bool {
            return $dir === '/some/share/dir' ? true : is_dir($dir);
        }, true);
        $mysqlServer = new MysqlServer(
            __DIR__,
            2389,
            'test',
            'root',
            'secret',
            '/usr/bin/mysqld',
            '/some/share/dir'
        );
        $machineInformation = new MachineInformation($os, $arch);
        $mysqlServer->setMachineInformation($machineInformation);
        $directory = __DIR__;
        $notNormalizedDirectory = $machineInformation->isWindows() ?
            str_replace('/', '\\', $directory)
            : $directory;
        $this->assertEquals($notNormalizedDirectory, $mysqlServer->getDirectory());
        $this->assertEquals($directory, $mysqlServer->getDirectory(true));
        $this->assertEquals(2389, $mysqlServer->getPort());
        $this->assertEquals('test', $mysqlServer->getDatabase());
        $this->assertEquals('root', $mysqlServer->getUser());
        $this->assertEquals('secret', $mysqlServer->getPassword());
        $this->assertEquals('secret', $mysqlServer->getRootPassword());
        $this->assertTrue($mysqlServer->isUsingCustomBinary());
        $notNormalizedBinaryPath = $machineInformation->isWindows() ? '\\usr\\bin\\mysqld' : '/usr/bin/mysqld';
        $this->assertEquals($notNormalizedBinaryPath, $mysqlServer->getBinaryPath());
        $this->assertEquals('/usr/bin/mysqld', $mysqlServer->getBinaryPath(true));
        $dataDir = __DIR__ . '/data';
        $notNormalizedDataDir = $machineInformation->isWindows() ?
            str_replace('/', '\\', $dataDir)
            : $dataDir;
        $this->assertEquals($notNormalizedDataDir, $mysqlServer->getDataDir());
        $this->assertEquals($dataDir, $mysqlServer->getDataDir(true));
    }

    public function testGetExtractedPathThrowsForCustomBinary(): void
    {
        $this->setFunctionReturn('is_executable', function (string $file): bool {
            return $file === '/usr/bin/mysqld' ? true : is_executable($file);
        }, true);
        $this->setFunctionReturn('is_dir', function (string $dir): bool {
            return $dir === '/some/share/dir' ? true : is_dir($dir);
        }, true);
        $mysqlServer = new MysqlServer(
            __DIR__,
            2389,
            'test',
            'root',
            'secret',
            '/usr/bin/mysqld',
            '/some/share/dir'
        );
        $this->expectException(RuntimeException::class);
        $this->expectExceptionCode(MysqlServer::ERR_CUSTOM_BINARY_EXTRACTED_PATH);
        $mysqlServer->getExtractedPath();
    }

    public function testConstructorThrowsIfShareDirNotSetForCustomBinary(): void
    {
        $this->setFunctionReturn('is_executable', function (string $file): bool {
            return $file === '/usr/bin/mysqld' ? true : is_executable($file);
        }, true);
        $this->setFunctionReturn('is_dir', function (string $dir): bool {
            return $dir === '/some/share/dir' ? true : is_dir($dir);
        }, true);
        $this->expectException(RuntimeException::class);
        $this->expectExceptionCode(MysqlServer::ERR_CUSTOM_BINARY_SHARE_DIR_PATH);
        $mysqlServer = new MysqlServer(
            __DIR__,
            2389,
            'test',
            'root',
            'secret',
            '/usr/bin/mysqld'
        );
    }

    public function testGetShareDireForCustomBinaryAndSetCustomShareDir(): void
    {
        $this->setFunctionReturn('is_executable', function (string $file): bool {
            return $file === '/usr/bin/mysqld' ? true : is_executable($file);
        }, true);
        $this->setFunctionReturn('is_dir', function (string $dir): bool {
            return $dir === '/some/share/dir' ? true : is_dir($dir);
        }, true);
        $mysqlServer = new MysqlServer(
            __DIR__,
            2389,
            'test',
            'root',
            'secret',
            '/usr/bin/mysqld',
            '/some/share/dir'
        );
        $shareDir = $mysqlServer->getShareDir();
        $this->assertEquals('/some/share/dir', $shareDir);
    }

    public function testConstructorThrowsIfDirectoryCannotBeCreated(): void
    {
        $this->setFunctionReturn('mkdir', function (string $dir, ...$rest): bool {
            return false;
        }, true);
        $this->expectException(RuntimeException::class);
        $this->expectExceptionCode(MysqlServer::ERR_MYSQL_DIR_NOT_CREATED);
        new MysqlServer('/my-data-dir');
    }

    public function startWithCustomParametersDataProvider(): Generator
    {
        foreach ($this->osAndArchDataProvider() as [$os, $arch]) {
            yield "{$os}_{$arch}_default_parameters" => [
                $os,
                $arch,
                [],
                [
                    'CREATE DATABASE IF NOT EXISTS `wordpress`',
                    "CREATE USER IF NOT EXISTS 'wordpress'@'%' IDENTIFIED BY 'wordpress'",
                    "GRANT ALL PRIVILEGES ON `wordpress`.* TO 'wordpress'@'%'",
                    'FLUSH PRIVILEGES',
                ]
            ];

            yield "{$os}_{$arch}_custom_parameters" => [
                $os,
                $arch,
                [
                    12345,
                    'someDatabase',
                    'someUser',
                    'password'
                ],
                [
                    'CREATE DATABASE IF NOT EXISTS `someDatabase`',
                    "CREATE USER IF NOT EXISTS 'someUser'@'%' IDENTIFIED BY 'password'",
                    "GRANT ALL PRIVILEGES ON `someDatabase`.* TO 'someUser'@'%'",
                    'FLUSH PRIVILEGES',
                ]
            ];
        }
    }

    /**
     * @dataProvider startWithCustomParametersDataProvider
     */
    public function testStartAndStop(string $os, string $arch, array $params, array $expectedQueries): void
    {
        ($this->unsetMkdirFunctionReturn)();
        $dir = FS::tmpDir('mysql-server_');
        $mysqlServer = new MysqlServer($dir, ...$params);
        $machineInformation = new MachineInformation($os, $arch);
        $mysqlServer->setMachineInformation($machineInformation);

        // Mock the download of the archive.
        $this->setMethodReturn(
            Download::class,
            'fileFromUrl',
            function (string $url, string $file) use ($mysqlServer): void {
                Assert::assertEquals($mysqlServer->getArchiveUrl(), $url);
                Assert::assertEquals($mysqlServer->getArchivePath(true), $file);
                $archiveBasename = basename($mysqlServer->getArchiveUrl());
                copy(codecept_data_dir('mysql-server/mock-archives/' . $archiveBasename), $file);
            },
            true
        );

        // Mock the extraction of the archive on Windows.
        if ($machineInformation->isWindows()) {
            $this->setClassMock(
                PharData::class,
                $this->makeEmptyClass(PharData::class, [
                    'extractTo' => function (string $directory, ?array $files = null, bool $overwrite = false) use (
                        $mysqlServer
                    ): bool {
                        Assert::assertEquals($mysqlServer->getDirectory(true), $directory);
                        Assert::assertNull($files);
                        Assert::assertTrue($overwrite);
                        $extractedPath = $mysqlServer->getExtractedPath(true);
                        mkdir($extractedPath . '/share', 0777, true);
                        mkdir($extractedPath . '/bin', 0777, true);
                        touch($extractedPath . '/bin/mysqld.exe');
                        chmod($extractedPath . '/bin/mysqld.exe', 0777);
                        return true;
                    },
                ])
            );
        }

        // Mock the processes to initialize and start the server.
        $mockProcessStep = $machineInformation->isWindows() ? 'init' : 'extract';
        $this->setClassMock(
            Process::class,
            $this->makeEmptyClass(
                Process::class,
                [
                    '__construct' => function (array $command) use (&$mockProcessStep, $mysqlServer) {
                        $archivePath = $mysqlServer->getArchivePath();
                        $extension = pathinfo($archivePath, PATHINFO_EXTENSION);
                        $tarFlags = $extension === 'xz' ? '-xf' : '-xzf';
                        if ($mockProcessStep === 'extract') {
                            Assert::assertEquals([
                                'tar',
                                $tarFlags,
                                $mysqlServer->getArchivePath(),
                                '-C',
                                $mysqlServer->getDirectory(),
                            ], $command);
                            $mockProcessStep = 'init';
                            $extractedPath = $mysqlServer->getExtractedPath(true);
                            mkdir($extractedPath . '/share', 0777, true);
                            mkdir($extractedPath . '/bin', 0777, true);
                            touch($extractedPath . '/bin/mysqld');
                            chmod($extractedPath . '/bin/mysqld', 0777);
                            return;
                        }

                        if ($mockProcessStep === 'init') {
                            Assert::assertEquals([
                                $mysqlServer->getBinaryPath(),
                                '--no-defaults',
                                '--initialize-insecure',
                                '--innodb-flush-method=nosync',
                                '--datadir=' . $mysqlServer->getDataDir(),
                                '--pid-file=' . $mysqlServer->getPidFilePath(),
                            ], $command);
                            $mockProcessStep = 'start';
                            return;
                        }

                        if ($mockProcessStep === 'start') {
                            Assert::assertEquals([
                                $mysqlServer->getBinaryPath(),
                                '--datadir=' . $mysqlServer->getDataDir(),
                                '--skip-mysqlx',
                                '--default-time-zone=+00:00',
                                '--innodb-flush-method=nosync',
                                '--innodb-flush-log-at-trx-commit=0',
                                '--innodb-doublewrite=0',
                                '--bind-address=localhost',
                                '--lc-messages-dir=' . $mysqlServer->getShareDir(),
                                '--socket=' . $mysqlServer->getSocketPath(),
                                '--log-error=' . $mysqlServer->getErrorLogPath(),
                                '--port=' . $mysqlServer->getPort(),
                                '--pid-file=' . $mysqlServer->getPidFilePath()
                            ], $command);
                            $mockProcessStep = 'started';
                            return;
                        }

                        throw new AssertionFailedError(
                            'Unexpected Process::__construct call for ' . print_r($command, true)
                        );
                    },
                    'mustRun' => '__itself',
                    'isRunning' => function () use (&$mockProcessStep): bool {
                        return $mockProcessStep === 'started';
                    },
                    'getPid' => 2389,
                    'stop' => 0
                ]
            )
        );

        // Mock the PDO connection.
        $queries = [];
        $this->setClassMock(PDO::class, $this->makeEmptyClass(PDO::class, [
            '__construct' => function (
                string $dsn,
                string $user,
                string $password
            ) use ($mysqlServer): void {
                Assert::assertEquals('mysql:host=127.0.0.1;port=' . $mysqlServer->getPort(), $dsn);
                Assert::assertEquals('root', $user);
                Assert::assertEquals($mysqlServer->getRootPassword(), $password);
            },
            'exec' => function (string $query) use (&$queries): int|false {
                $queries[] = $query;
                return 1;
            }
        ]));

        // Mock the PID file write.
        $pidFile = MysqlServer::getPidFile();
        $this->setFunctionReturn('file_put_contents', function (string $file, $pid) use ($pidFile): bool {
            Assert::assertEquals($pidFile, $file);
            Assert::assertEquals(2389, $pid);
            return true;
        }, true);
        $this->setFunctionReturn('file_get_contents', function (string $file) use ($pidFile): bool {
            Assert::assertEquals($pidFile, $file);
            return 2389;
        }, true);

        $mysqlServer->start();

        $this->assertEquals($expectedQueries, $queries);

        $mysqlServer->stop();
    }

    /**
     * @dataProvider osAndArchDataProvider
     */
    public function testStartWithRootUser(string $os, string $arch): void
    {
        ($this->unsetMkdirFunctionReturn)();
        $dir = FS::tmpDir('mysql-server_');
        $mysqlServer = new MysqlServer($dir, 12345, 'someDatabase', 'root', 'secret');
        $machineInformation = new MachineInformation($os, $arch);
        $mysqlServer->setMachineInformation($machineInformation);

        // Mock the download of the archive.
        $this->setMethodReturn(
            Download::class,
            'fileFromUrl',
            function (string $url, string $file) use ($mysqlServer): void {
                Assert::assertEquals($mysqlServer->getArchiveUrl(), $url);
                Assert::assertEquals($mysqlServer->getArchivePath(true), $file);
                $archiveBasename = basename($mysqlServer->getArchiveUrl());
                copy(codecept_data_dir('mysql-server/mock-archives/' . $archiveBasename), $file);
            },
            true
        );

        // Mock the extraction of the archive on Windows.
        if ($machineInformation->isWindows()) {
            $this->setClassMock(
                PharData::class,
                $this->makeEmptyClass(PharData::class, [
                    'extractTo' => function (string $directory, ?array $files = null, bool $overwrite = false) use (
                        $mysqlServer
                    ): bool {
                        Assert::assertEquals($mysqlServer->getDirectory(true), $directory);
                        Assert::assertNull($files);
                        Assert::assertTrue($overwrite);
                        $extractedPath = $mysqlServer->getExtractedPath(true);
                        mkdir($extractedPath . '/share', 0777, true);
                        mkdir($extractedPath . '/bin', 0777, true);
                        touch($extractedPath . '/bin/mysqld.exe');
                        chmod($extractedPath . '/bin/mysqld.exe', 0777);
                        return true;
                    },
                ])
            );
        }

        // Mock the processes to initialize and start the server.
        $mockProcessStep = $machineInformation->isWindows() ? 'init' : 'extract';
        $this->setClassMock(
            Process::class,
            $this->makeEmptyClass(
                Process::class,
                [
                    '__construct' => function (array $command) use (&$mockProcessStep, $mysqlServer) {
                        $archivePath = $mysqlServer->getArchivePath();
                        $extension = pathinfo($archivePath, PATHINFO_EXTENSION);
                        $tarFlags = $extension === 'xz' ? '-xf' : '-xzf';
                        if ($mockProcessStep === 'extract') {
                            Assert::assertEquals([
                                'tar',
                                $tarFlags,
                                $mysqlServer->getArchivePath(),
                                '-C',
                                $mysqlServer->getDirectory(),
                            ], $command);
                            $mockProcessStep = 'init';
                            $extractedPath = $mysqlServer->getExtractedPath(true);
                            mkdir($extractedPath . '/share', 0777, true);
                            mkdir($extractedPath . '/bin', 0777, true);
                            touch($extractedPath . '/bin/mysqld');
                            chmod($extractedPath . '/bin/mysqld', 0777);
                            return;
                        }

                        if ($mockProcessStep === 'init') {
                            Assert::assertEquals([
                                $mysqlServer->getBinaryPath(),
                                '--no-defaults',
                                '--initialize-insecure',
                                '--innodb-flush-method=nosync',
                                '--datadir=' . $mysqlServer->getDataDir(),
                                '--pid-file=' . $mysqlServer->getPidFilePath(),
                            ], $command);
                            $mockProcessStep = 'start';
                            return;
                        }

                        if ($mockProcessStep === 'start') {
                            Assert::assertEquals([
                                $mysqlServer->getBinaryPath(),
                                '--datadir=' . $mysqlServer->getDataDir(),
                                '--skip-mysqlx',
                                '--default-time-zone=+00:00',
                                '--innodb-flush-method=nosync',
                                '--innodb-flush-log-at-trx-commit=0',
                                '--innodb-doublewrite=0',
                                '--bind-address=localhost',
                                '--lc-messages-dir=' . $mysqlServer->getShareDir(),
                                '--socket=' . $mysqlServer->getSocketPath(),
                                '--log-error=' . $mysqlServer->getErrorLogPath(),
                                '--port=' . $mysqlServer->getPort(),
                                '--pid-file=' . $mysqlServer->getPidFilePath()
                            ], $command);
                            $mockProcessStep = 'started';
                            return;
                        }

                        throw new AssertionFailedError(
                            'Unexpected Process::__construct call for ' . print_r($command, true)
                        );
                    },
                    'mustRun' => '__itself',
                    'isRunning' => function () use (&$mockProcessStep): bool {
                        return $mockProcessStep === 'started';
                    },
                    'getPid' => 2389
                ]
            )
        );

        // Mock the PDO connection.
        $queries = [];
        $calls = 0;
        $this->setClassMock(PDO::class, $this->makeEmptyClass(PDO::class, [
            '__construct' => function (
                string $dsn,
                string $user,
                string $password
            ) use ($mysqlServer, &$calls): void {
                if ($calls === 0) {
                    // The first call with the not-yet set root password will fail.
                    Assert::assertEquals('mysql:host=127.0.0.1;port=' . $mysqlServer->getPort(), $dsn);
                    Assert::assertEquals('root', $user);
                    Assert::assertEquals($mysqlServer->getRootPassword(), $password);
                    ++$calls;
                    throw new \PDOException('Error');
                }

                if ($calls === 1) {
                    // Second call is done with the empty root password.
                    Assert::assertEquals('mysql:host=127.0.0.1;port=' . $mysqlServer->getPort(), $dsn);
                    Assert::assertEquals('root', $user);
                    Assert::assertEquals('', $password);
                    ++$calls;
                } else {
                    // Further calls should be done with the now set correct root password.
                    Assert::assertEquals('mysql:host=127.0.0.1;port=' . $mysqlServer->getPort(), $dsn);
                    Assert::assertEquals('root', $user);
                    Assert::assertEquals($mysqlServer->getRootPassword(), $password);
                    ++$calls;
                }
            },
            'exec' => function (string $query) use (&$queries): int|false {
                $queries[] = $query;
                return 1;
            }
        ]));

        // Mock the PID file write.
        $pidFile = MysqlServer::getPidFile();
        $this->setFunctionReturn('file_put_contents', function (string $file, $pid) use ($pidFile): bool {
            Assert::assertEquals($pidFile, $file);
            Assert::assertEquals(2389, $pid);
            return true;
        }, true);
        $this->setFunctionReturn('file_get_contents', function (string $file) use ($pidFile): bool {
            Assert::assertEquals($pidFile, $file);
            return 2389;
        }, true);

        $mysqlServer->start();

        $this->assertEquals(
            [
                "ALTER USER 'root'@'localhost' IDENTIFIED BY 'secret'",
                'CREATE DATABASE IF NOT EXISTS `someDatabase`',
                'FLUSH PRIVILEGES',
            ],
            $queries
        );
    }

    /**
     * @dataProvider osAndArchDataProvider
     */
    public function testStartServerWithCustomBinary(string $os, string $arch): void
    {
        ($this->unsetMkdirFunctionReturn)();
        $dir = FS::tmpDir('mysql-server_');
        $machineInformation = new MachineInformation($os, $arch);

        // The custom binary exists and is executable.
        if ($machineInformation->isWindows()) {
            $this->setFunctionReturn('is_executable', function (string $file): bool {
                return $file === 'C:/usr/bin/mysqld.exe' ? true : is_executable($file);
            }, true);
        } else {
            $this->setFunctionReturn('is_executable', function (string $file): bool {
                return $file === '/usr/bin/mysqld' ? true : is_executable($file);
            }, true);
        }

        // The custom share directory exists.
        if ($machineInformation->isWindows()) {
            $this->setFunctionReturn('is_dir', function (string $dir): bool {
                return $dir === 'C:\\some\\share\\dir' ? true : is_dir($dir);
            }, true);
        } else {
            $this->setFunctionReturn('is_dir', function (string $dir): bool {
                return $dir === '/some/share/dir' ? true : is_dir($dir);
            }, true);
        }

        $mysqlServer = new MysqlServer(
            $dir,
            12345,
            'someDatabase',
            'someUser',
            'password',
            $machineInformation->isWindows() ? 'C:\\usr\\bin\\mysqld.exe' : '/usr/bin/mysqld',
            $machineInformation->isWindows() ? 'C:\\some\\share\\dir' : '/some/share/dir'
        );
        $mysqlServer->setMachineInformation($machineInformation);

        // Mock the download of the archive.
        $this->setMethodReturn(
            Download::class,
            'fileFromUrl',
            function (string $url, string $file) use ($mysqlServer): void {
                throw new AssertionFailedError('No file should be downloaded.');
            },
            true
        );

        // Mock the extraction of the archive on Windows.
        if ($machineInformation->isWindows()) {
            $this->setClassMock(
                PharData::class,
                $this->makeEmptyClass(PharData::class, [
                    'extractTo' => fn() => throw new AssertionFailedError(
                        'No extraction should be performed on Windows.'
                    )
                ])
            );
        }

        // Mock the processes to initialize and start the server.
        $mockProcessStep = 'init';
        $this->setClassMock(
            Process::class,
            $this->makeEmptyClass(
                Process::class,
                [
                    '__construct' => function (array $command) use (&$mockProcessStep, $mysqlServer) {
                        if ($mockProcessStep === 'init') {
                            Assert::assertEquals([
                                $mysqlServer->getBinaryPath(),
                                '--no-defaults',
                                '--initialize-insecure',
                                '--innodb-flush-method=nosync',
                                '--datadir=' . $mysqlServer->getDataDir(),
                                '--pid-file=' . $mysqlServer->getPidFilePath(),
                            ], $command);
                            $mockProcessStep = 'start';
                            return;
                        }

                        if ($mockProcessStep === 'start') {
                            Assert::assertEquals([
                                $mysqlServer->getBinaryPath(),
                                '--datadir=' . $mysqlServer->getDataDir(),
                                '--skip-mysqlx',
                                '--default-time-zone=+00:00',
                                '--innodb-flush-method=nosync',
                                '--innodb-flush-log-at-trx-commit=0',
                                '--innodb-doublewrite=0',
                                '--bind-address=localhost',
                                '--lc-messages-dir=' . $mysqlServer->getShareDir(),
                                '--socket=' . $mysqlServer->getSocketPath(),
                                '--log-error=' . $mysqlServer->getErrorLogPath(),
                                '--port=' . $mysqlServer->getPort(),
                                '--pid-file=' . $mysqlServer->getPidFilePath()
                            ], $command);
                            $mockProcessStep = 'started';
                            return;
                        }

                        throw new AssertionFailedError(
                            'Unexpected Process::__construct call for ' . print_r($command, true)
                        );
                    },
                    'mustRun' => '__itself',
                    'isRunning' => function () use (&$mockProcessStep): bool {
                        return $mockProcessStep === 'started';
                    },
                    'getPid' => 2389
                ]
            )
        );

        // Mock the PDO connection.
        $queries = [];
        $this->setClassMock(PDO::class, $this->makeEmptyClass(PDO::class, [
            '__construct' => function (
                string $dsn,
                string $user,
                string $password
            ) use ($mysqlServer): void {
                Assert::assertEquals('mysql:host=127.0.0.1;port=' . $mysqlServer->getPort(), $dsn);
                Assert::assertEquals('root', $user);
                Assert::assertEquals($mysqlServer->getRootPassword(), $password);
            },
            'exec' => function (string $query) use (&$queries): int|false {
                $queries[] = $query;
                return 1;
            }
        ]));

        // Mock the PID file write.
        $pidFile = MysqlServer::getPidFile();
        $this->setFunctionReturn('file_put_contents', function (string $file, $pid) use ($pidFile): bool {
            Assert::assertEquals($pidFile, $file);
            Assert::assertEquals(2389, $pid);
            return true;
        }, true);
        $this->setFunctionReturn('file_get_contents', function (string $file) use ($pidFile): bool {
            Assert::assertEquals($pidFile, $file);
            return 2389;
        }, true);

        $mysqlServer->start();

        $this->assertEquals(
            [
                'CREATE DATABASE IF NOT EXISTS `someDatabase`',
                "CREATE USER IF NOT EXISTS 'someUser'@'%' IDENTIFIED BY 'password'",
                "GRANT ALL PRIVILEGES ON `someDatabase`.* TO 'someUser'@'%'",
                'FLUSH PRIVILEGES',
            ],
            $queries
        );
    }

    /**
     * @dataProvider osAndArchDataProvider
     */
    public function testStartWhenAlreadyRunning(string $os, string $arch): void
    {
        $pidFile = MysqlServer::getPidFile();

        // The PID file exists.
        $this->setFunctionReturn('is_file', function (string $file) use ($pidFile): bool {
            return $file === $pidFile ? true : is_file($file);
        }, true);

        $this->setClassMock(Process::class, $this->makeEmptyClass(Process::class, [
            '__construct' => fn() => throw new AssertionFailedError('No process should be started.'),
        ]));

        $this->setClassMock(PDO::class, $this->makeEmptyClass(PDO::class, [
            '__construct' => fn() => throw new AssertionFailedError('No PDO connection should be made.'),
        ]));

        $machineInformation = new MachineInformation($os, $arch);
        $mysqlServer = new MysqlServer(__DIR__);
        $mysqlServer->setMachineInformation($machineInformation);
    }

    public function testStopThrowsIfNotRunning(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('MySQL Server not started.');

        $mysqlServer = new MysqlServer(__DIR__);
        $mysqlServer->stop();
    }

    public function testStopThrowsIfPidFileCannotBeUnlinked(): void
    {
        ($this->unsetMkdirFunctionReturn)();
        $dir = FS::tmpDir('mysql-server_');
        // The custom binary exists and is executable.
        $this->setFunctionReturn('is_executable', function (string $file): bool {
            return $file === '/usr/bin/mysqld' ? true : is_executable($file);
        }, true);

        // The custom share directory exists.
        $this->setFunctionReturn('is_dir', function (string $dir): bool {
            return $dir === '/some/share/dir' ? true : is_dir($dir);
        }, true);

        // Mock the processes to initialize and start the server.
        $this->setClassMock(
            Process::class,
            $this->makeEmptyClass(
                Process::class,
                [
                    'mustRun' => '__itself',
                    'getPid' => 2389,
                    'stop' => 0,
                    'isRunning' => true,
                ]
            )
        );

        $pidFile = MysqlServer::getPidFile();

        // Mock the PID file write.
        $pidFileExists = false;
        $this->setFunctionReturn(
            'file_put_contents',
            function (string $file, $pid) use ($pidFile, &$pidFileExists): bool {
                Assert::assertEquals($pidFile, $file);
                Assert::assertEquals(2389, $pid);
                $pidFileExists = true;
                return true;
            },
            true
        );

        // The PID file exists.
        $this->setFunctionReturn('is_file', function (string $file) use (&$pidFileExists, $pidFile): bool {
            return $file === $pidFile ? $pidFileExists : is_file($file);
        }, true);

        // The PID file cannot be unlinked.
        $unlinked = false;
        $this->setFunctionReturn('unlink', function (string $file) use (&$pidFile): bool {
            return $file === $pidFile ? false : unlink($file);
        }, true);

        // Mock the PDO constructor.
        $pdoConstructorCalledWithCorrectArgs = false;
        $this->setClassMock(PDO::class, $this->makeEmptyClass(PDO::class, [
            'exec' => 1
        ]));

        $mysqlServer = new MysqlServer(
            $dir,
            12345,
            'someDatabase',
            'root',
            'secret',
            '/usr/bin/mysqld',
            '/some/share/dir'
        );
        $mysqlServer->start();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage("Could not remove PID file {$pidFile}.");

        $mysqlServer->stop();
    }

    public function testStartThrowsIfServerIsNotAvailable(): void
    {
        ($this->unsetMkdirFunctionReturn)();
        $dir = FS::tmpDir('mysql-server_');
        $mysqlServer = new MysqlServer($dir);
        $mysqlServer->setStartWaitTime(.01);
        $machineInformation = new MachineInformation(MachineInformation::OS_LINUX, MachineInformation::ARCH_X86_64);
        $mysqlServer->setMachineInformation($machineInformation);

        // Mock the download of the archive.
        $this->setMethodReturn(
            Download::class,
            'fileFromUrl',
            function (string $url, string $file) use ($mysqlServer): void {
                Assert::assertEquals($mysqlServer->getArchiveUrl(), $url);
                Assert::assertEquals($mysqlServer->getArchivePath(true), $file);
                $archiveBasename = basename($mysqlServer->getArchiveUrl());
                copy(codecept_data_dir('mysql-server/mock-archives/' . $archiveBasename), $file);
            },
            true
        );

        // Mock the processes to initialize and start the server.
        $mockProcessStep = $machineInformation->isWindows() ? 'init' : 'extract';
        $this->setClassMock(
            Process::class,
            $this->makeEmptyClass(
                Process::class,
                [
                    '__construct' => function (array $command) use (&$mockProcessStep, $mysqlServer) {
                        if ($mockProcessStep === 'extract') {
                            Assert::assertEquals([
                                'tar',
                                '-xf',
                                $mysqlServer->getArchivePath(),
                                '-C',
                                $mysqlServer->getDirectory(),
                            ], $command);
                            $mockProcessStep = 'init';
                            $extractedPath = $mysqlServer->getExtractedPath(true);
                            mkdir($extractedPath . '/share', 0777, true);
                            mkdir($extractedPath . '/bin', 0777, true);
                            touch($extractedPath . '/bin/mysqld');
                            chmod($extractedPath . '/bin/mysqld', 0777);
                            return;
                        }

                        if ($mockProcessStep === 'init') {
                            Assert::assertEquals([
                                $mysqlServer->getBinaryPath(),
                                '--no-defaults',
                                '--initialize-insecure',
                                '--innodb-flush-method=nosync',
                                '--datadir=' . $mysqlServer->getDataDir(),
                                '--pid-file=' . $mysqlServer->getPidFilePath(),
                            ], $command);
                            $mockProcessStep = 'start';
                            return;
                        }

                        if ($mockProcessStep === 'start') {
                            Assert::assertEquals([
                                $mysqlServer->getBinaryPath(),
                                '--datadir=' . $mysqlServer->getDataDir(),
                                '--skip-mysqlx',
                                '--default-time-zone=+00:00',
                                '--innodb-flush-method=nosync',
                                '--innodb-flush-log-at-trx-commit=0',
                                '--innodb-doublewrite=0',
                                '--bind-address=localhost',
                                '--lc-messages-dir=' . $mysqlServer->getShareDir(),
                                '--socket=' . $mysqlServer->getSocketPath(),
                                '--log-error=' . $mysqlServer->getErrorLogPath(),
                                '--port=' . $mysqlServer->getPort(),
                                '--pid-file=' . $mysqlServer->getPidFilePath()
                            ], $command);
                            $mockProcessStep = 'started';
                            return;
                        }

                        throw new AssertionFailedError(
                            'Unexpected Process::__construct call for ' . print_r($command, true)
                        );
                    },
                    'mustRun' => '__itself',
                    'isRunning' => function () use (&$mockProcessStep): bool {
                        return $mockProcessStep === 'started';
                    },
                    'getPid' => 2389,
                    'stop' => 0
                ]
            )
        );

        // Mock the PDO connection.
        $queries = [];
        $this->setClassMock(PDO::class, $this->makeEmptyClass(PDO::class, [
            '__construct' => function () {
                throw new \PDOException('Cannot connect to MySQL server');
            },
            'exec' => function (string $query) use (&$queries): int|false {
                $queries[] = $query;
                return 1;
            }
        ]));

        $this->expectException(RuntimeException::class);
        $this->expectExceptionCode(MysqlServer::ERR_MYSQL_SERVER_NEVER_BECAME_AVAILABLE);

        $mysqlServer->start();
    }
}

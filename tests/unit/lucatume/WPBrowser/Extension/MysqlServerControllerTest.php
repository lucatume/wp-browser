<?php


namespace lucatume\WPBrowser\Extension;

use Codeception\Exception\ExtensionException;
use lucatume\WPBrowser\Adapters\Symfony\Component\Process\Process;
use lucatume\WPBrowser\ManagedProcess\MysqlServer;
use lucatume\WPBrowser\Tests\Traits\ClassStubs;
use lucatume\WPBrowser\Traits\UopzFunctions;
use lucatume\WPBrowser\Utils\Download;
use lucatume\WPBrowser\Utils\Filesystem as FS;
use PHPUnit\Framework\AssertionFailedError;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\NullOutput;

class MysqlServerControllerTest extends \Codeception\Test\Unit
{
    use UopzFunctions;
    use ClassStubs;

    protected function _before()
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

        $pidFile = (new MysqlServerController([], []))->getPidFile();
        $this->setFunctionReturn('is_file', function (string $file) use ($pidFile): bool {
            return $file === $pidFile ? false : is_file($file);
        }, true);
    }

    public function invalidPortDataProvider(): array
    {
        return [
            'string' => ['string'],
            'float' => [1.1],
            'negative' => [-1],
            'zero' => [0],
            'empty string' => [''],
        ];
    }

    /**
     * @dataProvider invalidPortDataProvider
     */
    public function testStartThrowsForInvalidPort($invalidPort):void{
       $config = ['port' => $invalidPort];
       $options = [];

       $this->expectException(ExtensionException::class);
       $this->expectExceptionMessage('The "port" configuration option must be an integer greater than 0.');

       $controller = new MysqlServerController($config, $options);
       $controller->start(new NullOutput());
    }

    public function notAStringDataProvider(): array
    {
        return [
            'float' => [1.1],
            'negative' => [-1],
            'zero' => [0],
            'empty string' => [''],
        ];
    }

    /**
     * @dataProvider notAStringDataProvider
     */
    public function testStartThrowsForInvalidDatabase($invalidDatabase):void{
        $config = ['database' => $invalidDatabase];
        $options = [];

        $this->expectException(ExtensionException::class);
        $this->expectExceptionMessage('The "database" configuration option must be a string.');

        $controller = new MysqlServerController($config, $options);
        $controller->start(new NullOutput());
    }

    /**
     * @dataProvider notAStringDataProvider
     */
    public function testThrowsForInvalidUser($invalidUser):void{
        $config = ['user' => $invalidUser];
        $options = [];

        $this->expectException(ExtensionException::class);
        $this->expectExceptionMessage('The "user" configuration option must be a string.');

        $controller = new MysqlServerController($config, $options);
        $controller->start(new NullOutput());
    }

    public function invalidPasswordDataProvider(): array
    {
        return [
            'array' => [[]],
            'float' => [1.1],
        ];
    }

    /**
     * @dataProvider invalidPasswordDataProvider
     */
    public function testThrowsForInvalidPassword($invalidPassword):void{
        $config = ['password' => $invalidPassword];
        $options = [];

        $this->expectException(ExtensionException::class);
        $this->expectExceptionMessage('The "password" configuration option must be a string.');

        $controller = new MysqlServerController($config, $options);
        $controller->start(new NullOutput());
    }

    public function testStartWithDefaults(): void
    {
        $config = [];
        $options = [];
        $output = new BufferedOutput();
        $this->setClassMock(
            MysqlServer::class,
            $this->makeEmptyClass(MysqlServer::class, [
                '__construct' => function (...$args) {
                    $this->assertEquals([
                        codecept_output_dir('_mysql_server'),
                        MysqlServer::PORT_DEFAULT,
                        'wordpress',
                        'wordpress',
                        'wordpress',
                        null,
                        null
                    ], $args);
                },
                'start' => null
            ])
        );

        $controller = new MysqlServerController($config, $options);
        $controller->start($output);
        $port = MysqlServer::PORT_DEFAULT;
        $this->assertEquals($port, $controller->getPort());
        $this->assertEquals("Starting MySQL server on port {$port} ... ok\n", $output->fetch());
        $this->assertEquals(codecept_output_dir('mysql-server.pid'), $controller->getPidFile());
    }

    public function testStartWithCustomPort(): void
    {
        $config = ['port' => 2389];
        $options = [];
        $output = new BufferedOutput();
        $this->setClassMock(
            MysqlServer::class,
            $this->makeEmptyClass(MysqlServer::class, [
                '__construct' => function (...$args) {
                    $this->assertEquals([
                        codecept_output_dir('_mysql_server'),
                        2389,
                        'wordpress',
                        'wordpress',
                        'wordpress',
                        null,
                        null
                    ], $args);
                },
                'start' => null
            ])
        );

        $controller = new MysqlServerController($config, $options);
        $controller->start($output);
        $this->assertEquals(2389, $controller->getPort());
        $this->assertEquals("Starting MySQL server on port 2389 ... ok\n", $output->fetch());
    }

    public function testStartWithCustomDatabaseUserNamePassword(): void
    {
        $config = ['database' => 'test', 'user' => 'luca', 'password' => 'secret'];
        $options = [];
        $output = new BufferedOutput();
        $this->setClassMock(
            MysqlServer::class,
            $this->makeEmptyClass(MysqlServer::class, [
                '__construct' => function (...$args) {
                    $this->assertEquals([
                        codecept_output_dir('_mysql_server'),
                        MysqlServer::PORT_DEFAULT,
                        'test',
                        'luca',
                        'secret',
                        null,
                        null
                    ], $args);
                },
                'start' => null
            ])
        );

        $controller = new MysqlServerController($config, $options);
        $controller->start($output);
        $port = MysqlServer::PORT_DEFAULT;
        $this->assertEquals("Starting MySQL server on port {$port} ... ok\n", $output->fetch());
    }

    public function testWithCustomBinary(): void
    {
        $config = ['binary' => '/usr/bin/mysqld', 'shareDir' => '/some/share/dir'];
        $options = [];
        $output = new BufferedOutput();
        $this->setFunctionReturn('is_executable', function (string $file): bool {
            return $file === '/usr/bin/mysqld' ? true : is_executable($file);
        }, true);
        $this->setFunctionReturn('is_dir', function (string $dir): bool {
            return $dir === '/some/share/dir' ? true : is_dir($dir);
        }, true);
        $this->setClassMock(
            MysqlServer::class,
            $this->makeEmptyClass(MysqlServer::class, [
                '__construct' => function (...$args) {
                    $this->assertEquals([
                        codecept_output_dir('_mysql_server'),
                        MysqlServer::PORT_DEFAULT,
                        'wordpress',
                        'wordpress',
                        'wordpress',
                        '/usr/bin/mysqld',
                        '/some/share/dir'
                    ], $args);
                },
                'start' => null
            ])
        );
        $controller = new MysqlServerController($config, $options);
        $controller->start($output);
    }

    public function testThrowsIfCustomBinaryDoesNotExist(): void{
        $config = ['binary' => '/usr/bin/mysqld'];
        $options = [];
        $this->setFunctionReturn('is_executable', function (string $file): bool {
            return $file === '/usr/bin/mysqld' ? false : is_executable($file);
        }, true);

        $this->expectException(ExtensionException::class);
        $this->expectExceptionMessage('The "binary" configuration option must be an executable file.');

        $controller = new MysqlServerController($config, $options);
        $controller->start(new NullOutput());
    }

    public function testThrowsIfUsingCustomBinaryAndShareDirNotSet(): void
    {
        $config = ['binary' => '/usr/bin/mysqld'];
        $options = [];
        $this->setFunctionReturn('is_executable', function (string $file): bool {
            return $file === '/usr/bin/mysqld' ? true : is_executable($file);
        }, true);

        $this->expectException(ExtensionException::class);
        $this->expectExceptionMessage('The "shareDir" configuration option must be set when using a custom binary.');

        $controller = new MysqlServerController($config, $options);
        $controller->start(new NullOutput());
    }

    public function testThrowsIfShareDirNotADirectory(): void
    {
        $config = ['binary' => '/usr/bin/mysqld', 'shareDir' => '/some/share/dir'];
        $options = [];
        $this->setFunctionReturn('is_executable', function (string $file): bool {
            return $file === '/usr/bin/mysqld' ? true : is_executable($file);
        }, true);
        $this->setFunctionReturn('is_dir', function (string $dir): bool {
            return $dir === '/some/share/dir' ? false : is_dir($dir);
        }, true);

        $this->expectException(ExtensionException::class);
        $this->expectExceptionMessage('The "shareDir" configuration option must be a directory.');

        $controller = new MysqlServerController($config, $options);
        $controller->start(new NullOutput());
    }

    public function tesWithRootUserAndPassword(): void
    {
        $config = ['user' => 'root', 'password' => 'password'];
        $options = [];
        $output = new BufferedOutput();
        $this->setClassMock(
            MysqlServer::class,
            $this->makeEmptyClass(MysqlServer::class, [
                '__construct' => function (...$args) {
                    $this->assertEquals([
                        codecept_output_dir('_mysql_server'),
                        MysqlServer::PORT_DEFAULT,
                        'wordpress',
                        'root',
                        'password',
                        null
                    ], $args);
                },
                'start' => null
            ])
        );
        $controller = new MysqlServerController($config, $options);
        $controller->start(new NullOutput());
    }

    public function testWithRootUserAndEmptyPassword(): void
    {
        $config = ['user' => 'root', 'password' => ''];
        $options = [];
        $output = new BufferedOutput();
        $this->setClassMock(
            MysqlServer::class,
            $this->makeEmptyClass(MysqlServer::class, [
                '__construct' => function (...$args) {
                    $this->assertEquals([
                        codecept_output_dir('_mysql_server'),
                        MysqlServer::PORT_DEFAULT,
                        'wordpress',
                        'root',
                        '',
                        null,
                        null
                    ], $args);
                },
                'start' => null
            ])
        );
        $controller = new MysqlServerController($config, $options);
        $controller->start(new NullOutput());
    }

    public function testCatchesMysqlServerExceptionDuringStart(): void
    {
        $config = [];
        $options = [];
        $output = new BufferedOutput();
        $this->setClassMock(
            MysqlServer::class,
            $this->makeEmptyClass(MysqlServer::class, [
                'start' => function () {
                    throw new \Exception('Something went wrong');
                }
            ])
        );

        $controller = new MysqlServerController($config, $options);
        $this->expectException(ExtensionException::class);
        $this->expectExceptionMessage('Error while starting MySQL server. Something went wrong');
        $controller->start($output);
    }

    public function testWillNotRestartIfAlreadyRunning(): void
    {
        // Mock the PID file existence.
        $pidFile = (new MysqlServerController([], []))->getPidFile();
        $this->setFunctionReturn('is_file', function (string $file) use ($pidFile): bool {
            return $file === $pidFile ? true : is_file($file);
        }, true);
        $this->setClassMock(MysqlServer::class, $this->makeEmptyClass(MysqlServer::class, [
            '__construct' => function () {
                throw new AssertionFailedError(
                    'The MysqlServer constructor should not be called.'
                );
            },
        ]));

        $controller = new MysqlServerController([], []);
        $controller->start(new NullOutput);
    }

    public function testGetPort(): void
    {
        $controller = new MysqlServerController([
            'port' => 12345,
        ], []);

        $this->assertEquals(12345, $controller->getPort());
    }

    public function testStopRunningMysqlServer(): void
    {
        $config = [];
        $options = [];
        $output = new BufferedOutput();
        $this->setClassMock(
            MysqlServer::class,
            $this->makeEmptyClass(MysqlServer::class, [
                'start' => null
            ])
        );
        $pidFile = (new MysqlServerController([], []))->getPidFile();
        $this->setFunctionReturn('file_get_contents', function (string $file) use ($pidFile): string|false {
            if ($file === $pidFile) {
                return '12345';
            }
            return file_get_contents($file);
        }, true);
        $this->setFunctionReturn('exec', function (string $command): string|false {
            if ($command !== 'kill 12345 2>&1 > /dev/null') {
                throw new AssertionFailedError('Unexpected exec command call: ' . $command);
            }
            return '';
        }, true);
        $this->setFunctionReturn('unlink', function (string $file) use ($pidFile): bool {
            if ($file === $pidFile) {
                return true;
            }
            return unlink($file);
        }, true);

        $controller = new MysqlServerController($config, $options);
        $controller->start($output);
        $controller->stop($output);
        $port = MysqlServer::PORT_DEFAULT;
        $this->assertEquals(
            "Starting MySQL server on port {$port} ... ok\nStopping MySQL server with PID 12345 ... ok\n",
            $output->fetch()
        );
    }

    public function testStopWhenPidFileDoesNotExist(): void
    {
        $config = [];
        $options = [];
        $output = new BufferedOutput();
        $this->setClassMock(
            MysqlServer::class,
            $this->makeEmptyClass(MysqlServer::class, [
                'start' => null
            ])
        );
        $pidFile = (new MysqlServerController([], []))->getPidFile();
        $this->setFunctionReturn('file_get_contents', function (string $file) use ($pidFile): string|false {
            if ($file === $pidFile) {
                return false;
            }
            return file_get_contents($file);
        }, true);
        $this->setFunctionReturn('exec', function (string $command): string|false {
            throw new AssertionFailedError('Unexpected exec command call: ' . $command);
        }, true);
        $this->setFunctionReturn('unlink', function (string $file) use ($pidFile): bool {
            throw new AssertionFailedError('Unexpected unlink call for file: ' . $file);
        }, true);

        $controller = new MysqlServerController($config, $options);
        $controller->start($output);
        $controller->stop($output);
        $port = MysqlServer::PORT_DEFAULT;
        $this->assertEquals(
            "Starting MySQL server on port {$port} ... ok\nMySQL server not running.\n",
            $output->fetch()
        );
    }

    public function testStopThrowsIfPidFileCannotBeUnlinked(): void
    {
        $config = [];
        $options = [];
        $output = new BufferedOutput();
        $this->setClassMock(
            MysqlServer::class,
            $this->makeEmptyClass(MysqlServer::class, [
                'start' => null
            ])
        );
        $pidFile = (new MysqlServerController([], []))->getPidFile();
        $this->setFunctionReturn('file_get_contents', function (string $file) use ($pidFile): string|false {
            if ($file === $pidFile) {
                return '12345';
            }
            return file_get_contents($file);
        }, true);
        $this->setFunctionReturn('exec', function (string $command): string|false {
            return '';
        }, true);
        $this->setFunctionReturn('unlink', function (string $file) use ($pidFile): bool {
            return false;
        }, true);

        $controller = new MysqlServerController($config, $options);
        $controller->start($output);
        $this->expectException(ExtensionException::class);
        $this->expectExceptionMessage("Could not delete PID file '$pidFile'.");
        $controller->stop($output);
    }

    public function testPrettyName(): void
    {
        $controller = new MysqlServerController([], []);
        $this->assertEquals('MySQL Community Server', $controller->getPrettyName());
    }

    public function testGetInfoWithDefaults(): void
    {
        $controller = new MysqlServerController([], []);
        $pidFile = $controller->getPidFile();
        $this->setFunctionReturn('is_file', function (string $file) use ($pidFile): bool {
            return $file === $pidFile ? false : is_file($file);
        }, true);

        $port = MysqlServer::PORT_DEFAULT;
        $this->assertEquals([
            'running' => 'no',
            'pidFile' => FS::relativePath(codecept_root_dir(), $controller->getPidFile()),
            'host' => '127.0.0.1',
            'port' => $port,
            'user' => 'wordpress',
            'password' => 'wordpress',
            'root user' => 'root',
            'root password' => '',
        ], $controller->getInfo());

        $this->setFunctionReturn('is_file', function (string $file) use ($pidFile): bool {
            return $file === $pidFile ? true : is_file($file);
        }, true);

        $this->assertEquals([
            'running' => 'yes',
            'pidFile' => FS::relativePath(codecept_root_dir(), $controller->getPidFile()),
            'host' => '127.0.0.1',
            'port' => $port,
            'user' => 'wordpress',
            'password' => 'wordpress',
            'root user' => 'root',
            'root password' => '',
            'mysql command' => "mysql -h 127.0.0.1 -P {$port} -u wordpress -p'wordpress'",
            'mysql root command' => "mysql -h 127.0.0.1 -P {$port} -u root"
        ], $controller->getInfo());
    }

    public function testGetInfoWithCustomConfig(): void
    {
        $controller = new MysqlServerController([
            'port' => 12345,
            'database' => 'test',
            'user' => 'luca',
            'password' => 'secret',
        ], []);
        $pidFile = $controller->getPidFile();
        $this->setFunctionReturn('is_file', function (string $file) use ($pidFile): bool {
            return $file === $pidFile ? false : is_file($file);
        }, true);

        $port = 12345;
        $this->assertEquals([
            'running' => 'no',
            'pidFile' => FS::relativePath(codecept_root_dir(), $controller->getPidFile()),
            'host' => '127.0.0.1',
            'port' => $port,
            'user' => 'luca',
            'password' => 'secret',
            'root user' => 'root',
            'root password' => '',
        ], $controller->getInfo());

        $this->setFunctionReturn('is_file', function (string $file) use ($pidFile): bool {
            return $file === $pidFile ? true : is_file($file);
        }, true);
        $this->assertEquals([
            'running' => 'yes',
            'pidFile' => FS::relativePath(codecept_root_dir(), $controller->getPidFile()),
            'host' => '127.0.0.1',
            'port' => $port,
            'user' => 'luca',
            'password' => 'secret',
            'root user' => 'root',
            'root password' => '',
            'mysql command' => "mysql -h 127.0.0.1 -P {$port} -u luca -p'secret'",
            'mysql root command' => "mysql -h 127.0.0.1 -P {$port} -u root"
        ], $controller->getInfo());
    }

    public function testGetInfoUsingRootUser(): void
    {
        $controller = new MysqlServerController([
            'port' => 12345,
            'database' => 'test',
            'user' => 'root',
            'password' => 'secret',
        ], []);
        $pidFile = $controller->getPidFile();
        $this->setFunctionReturn('is_file', function (string $file) use ($pidFile): bool {
            return $file === $pidFile ? false : is_file($file);
        }, true);

        $port = 12345;
        $this->assertEquals([
            'running' => 'no',
            'pidFile' => FS::relativePath(codecept_root_dir(), $controller->getPidFile()),
            'host' => '127.0.0.1',
            'port' => $port,
            'user' => 'root',
            'password' => 'secret',
            'root user' => 'root',
            'root password' => 'secret',
        ], $controller->getInfo());

        $this->setFunctionReturn('is_file', function (string $file) use ($pidFile): bool {
            return $file === $pidFile ? true : is_file($file);
        }, true);
        $this->assertEquals([
            'running' => 'yes',
            'pidFile' => FS::relativePath(codecept_root_dir(), $controller->getPidFile()),
            'host' => '127.0.0.1',
            'port' => $port,
            'user' => 'root',
            'password' => 'secret',
            'root user' => 'root',
            'root password' => 'secret',
            'mysql command' => "mysql -h 127.0.0.1 -P {$port} -u root -p'secret'",
            'mysql root command' => "mysql -h 127.0.0.1 -P {$port} -u root -p'secret'"
        ], $controller->getInfo());
    }
}

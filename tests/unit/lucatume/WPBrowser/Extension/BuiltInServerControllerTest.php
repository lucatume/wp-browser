<?php


namespace Unit\lucatume\WPBrowser\Extension;

use Codeception\Event\SuiteEvent;
use Codeception\Exception\ExtensionException;
use Codeception\Lib\Console\Output;
use Codeception\Suite;
use Codeception\Test\Unit;
use lucatume\WPBrowser\Extension\BuiltInServerController;
use lucatume\WPBrowser\ManagedProcess\PhpBuiltInServer;
use lucatume\WPBrowser\Tests\Traits\UopzFunctions;
use lucatume\WPBrowser\Utils\Composer;
use lucatume\WPBrowser\Utils\Random;
use stdClass;
use tad\Codeception\SnapshotAssertions\SnapshotAssertions;

class PhpBuiltInServerMock extends PhpBuiltInServer
{
    public static array $lastInstanceArgs;

    public function __construct(string $docRoot, int $port = 0, array $env = [])
    {
        static::$lastInstanceArgs = ['docroot' => $docRoot, 'port' => $port, 'env' => $env];
    }

    public function start(): void
    {
    }

    public function stop(): ?int
    {
        return 0;
    }
}

class BuiltInServerControllerTest extends Unit
{
    use UopzFunctions;
    use SnapshotAssertions;

    private Output $output;

    /**
     * @beforeClass
     */
    public static function backupPidFile():void{
        $pidFile = PhpBuiltInServer::getPidFile();

        if (is_file($pidFile)) {
            rename($pidFile, $pidFile.'.bak');
        }
    }

    /**
     * @afterClass
     */
    public static function restorePidFile():void{
        $pidFile = PhpBuiltInServer::getPidFile();
        $pidFileBackup = $pidFile .'.bak';

        if (is_file($pidFileBackup)) {
            rename($pidFileBackup, $pidFile);
        }
    }

    /**
     * @before
     * @after
     */
    public function removePidFiles(): void
    {
        $pidFile = PhpBuiltInServer::getPidFile();
        if (is_file($pidFile)) {
            unlink($pidFile);
        }
    }

    public function _before()
    {
        // Mock the binary.
        $bin = codecept_data_dir('/bins/php-built-in-server-mock');
        $this->uopzSetStaticMethodReturn(Composer::class, 'binDir', $bin);
        // Silence output.
        $this->output = new Output(['verbosity' => Output::VERBOSITY_QUIET]);
        $this->uopzSetMock(Output::class, $this->output);
    }

    public function notArrayOfStringsProvider(): array
    {
        return [
            'string' => ['string'],
            'int' => [1],
            'float' => [1.1],
            'bool' => [true],
            'object' => [new stdClass()],
            'array of integers' => [[1, 2, 3]],
            'array of mixed values' => [[1, 'string', true, new stdClass()]],
        ];
    }

    /**
     * It should throw if suite configuration parameter is not array of strings
     *
     * @test
     * @dataProvider notArrayOfStringsProvider
     */
    public function should_throw_if_suite_configuration_parameter_is_not_array_of_strings(mixed $suites): void
    {
        $config = ['suites' => $suites];
        $options = [];

        $extension = new BuiltInServerController($config, $options);

        $this->expectException(ExtensionException::class);
        $this->expectExceptionMessage('The "suites" configuration option must be an array.');

        $mockSuite = $this->make(Suite::class, ['getName' => 'end2end']);
        $extension->onModuleInit($this->make(SuiteEvent::class, ['getSuite' => $mockSuite]));
    }

    public function notIntGreaterThanZeroProvider(): array
    {
        return [
            'string' => ['string'],
            'int less than 1' => [0],
            'bool' => [true],
            'object' => [new stdClass()],
            'array of integers' => [[1, 2, 3]],
            'array of mixed values' => [[1, 'string', true, new stdClass()]],
        ];
    }

    /**
     * It should throw if config port is not int greater than 0
     *
     * @test
     * @dataProvider notIntGreaterThanZeroProvider
     */
    public function should_throw_if_config_port_is_not_int_greater_than_0(mixed $port): void
    {
        $config = [
            'docroot' => __DIR__,
            'port' => $port
        ];
        $options = [];

        $extension = new BuiltInServerController($config, $options);

        $this->expectException(ExtensionException::class);
        $this->expectExceptionMessage('The "port" configuration option must be an integer greater than 0.');

        $mockSuite = $this->make(Suite::class, ['getName' => 'end2end']);
        $extension->onModuleInit($this->make(SuiteEvent::class, ['getSuite' => $mockSuite]));
    }

    public function notValidDirectoryProvider(): array
    {
        return [
            'string' => ['string'],
            'int' => [1],
            'float' => [1.1],
            'bool' => [true],
            'object' => [new stdClass()],
            'array of integers' => [[1, 2, 3]],
            'array of mixed values' => [[1, 'string', true, new stdClass()]],
        ];
    }

    /**
     * It should throw if config docroot is not existing directory
     *
     * @test
     * @dataProvider notValidDirectoryProvider
     */
    public function should_throw_if_config_docroot_is_not_existing_directory(mixed $docroot): void
    {
        $config = ['docroot' => $docroot];
        $options = [];

        $extension = new BuiltInServerController($config, $options);

        $this->expectException(ExtensionException::class);
        $this->expectExceptionMessage('The "docroot" configuration option must be a valid directory.');

        $mockSuite = $this->make(Suite::class, ['getName' => 'end2end']);
        $extension->onModuleInit($this->make(SuiteEvent::class, ['getSuite' => $mockSuite]));
    }

    /**
     * It should throw if config workers is not int greater than 0
     *
     * @test
     * @dataProvider notIntGreaterThanZeroProvider
     */
    public function should_throw_if_config_workers_is_not_int_greater_than_0(mixed $workers): void
    {
        $config = [
            'docroot' => __DIR__,
            'workers' => $workers
        ];
        $options = [];

        $extension = new BuiltInServerController($config, $options);

        $this->expectException(ExtensionException::class);
        $this->expectExceptionMessage('The "workers" configuration option must be an integer greater than 0.');

        $mockSuite = $this->make(Suite::class, ['getName' => 'end2end']);
        $extension->onModuleInit($this->make(SuiteEvent::class, ['getSuite' => $mockSuite]));
    }

    public function notAssociativeArrayWithStringsProvider(): array
    {
        return [
            'string' => ['string'],
            'int' => [1],
            'float' => [1.1],
            'bool' => [true],
            'object' => [new stdClass()],
            'array of integers' => [[1, 2, 3]],
            'array of mixed values' => [[1, 'string', true, new stdClass()]],
            'array of arrays' => [[['string'], ['string']]],
            'array of mixed values' => [[['string'], ['string']]],
        ];
    }

    /**
     * It should throw if config env is not associative array with string keys
     *
     * @test
     * @dataProvider notAssociativeArrayWithStringsProvider
     */
    public function should_throw_if_config_env_is_not_associative_array_with_string_keys(mixed $env): void
    {
        $config = [
            'docroot' => __DIR__,
            'env' => $env
        ];
        $options = [];

        $extension = new BuiltInServerController($config, $options);

        $this->expectException(ExtensionException::class);
        $this->expectExceptionMessage('The "env" configuration option must be an associative array.');

        $mockSuite = $this->make(Suite::class, ['getName' => 'end2end']);
        $extension->onModuleInit($this->make(SuiteEvent::class, ['getSuite' => $mockSuite]));
    }

    /**
     * It should replace CC root dir placeholder in env array
     *
     * @test
     */
    public function should_replace_cc_root_dir_placeholder_in_env_array(): void
    {
        $this->uopzSetMock(PhpBuiltInServer::class, PhpBuiltInServerMock::class);
        $config = [
            'docroot' => __DIR__,
            'env' => [
                'SOME_KEY' => '%codecept_root_dir%/some/path'
            ]
        ];
        $options = [];

        $extension = new BuiltInServerController($config, $options);

        $mockSuite = $this->make(Suite::class, ['getName' => 'end2end']);
        $extension->onModuleInit($this->make(SuiteEvent::class, ['getSuite' => $mockSuite]));

        $this->assertEquals(codecept_root_dir('some/path'), PhpBuiltInServerMock::$lastInstanceArgs['env']['SOME_KEY']);
    }

    /**
     * It should handle PHP built-in server lifecycle
     *
     * @test
     */
    public function should_handle_php_built_in_server_lifecycle(): void
    {
        $config = ['docroot' => __DIR__, 'port' => Random::openLocalhostPort()];
        $options = [];
        $extension = new BuiltInServerController($config, $options);

        $mockSuite = $this->make(Suite::class, ['getName' => 'end2end']);
        $extension->onModuleInit($this->make(SuiteEvent::class, ['getSuite' => $mockSuite]));

        $this->assertFileExists(PhpBuiltInServer::getPidFile());

        $extension->stop($this->output);

        $this->assertFileNotExists(PhpBuiltInServer::getPidFile());

        $extension->stop($this->output);
    }

    /**
     * It should throw if pid file is not readable
     *
     * @test
     */
    public function should_throw_if_pid_file_is_not_readable(): void
    {
        file_put_contents(PhpBuiltInServer::getPidFile(), '1233');
        $this->uopzSetFunctionReturn('file_get_contents', function (string $file): bool {
            if ($file === PhpBuiltInServer::getPidFile()) {
                return false;
            }
            return file_get_contents($file);
        }, true);

        $config = ['docroot' => __DIR__, 'port' => Random::openLocalhostPort()];
        $options = [];

        $extension = new BuiltInServerController($config, $options);

        $mockSuite = $this->make(Suite::class, ['getName' => 'end2end']);
        $extension->onModuleInit($this->make(SuiteEvent::class, ['getSuite' => $mockSuite]));

        $this->expectException(ExtensionException::class);
        $this->expectExceptionMessage('Could not read the PHP built-in server PID file.');

        $extension->stop($this->output);
    }

    /**
     * It should correctly produce information
     *
     * @test
     */
    public function should_correctly_produce_information(): void
    {
        $this->assertFileNotExists(PhpBuiltInServer::getPidFile());

        $config = ['docroot' => __DIR__, 'port' => 8923];
        $options = [];

        $extension = new BuiltInServerController($config, $options);

        $mockSuite = $this->make(Suite::class, ['getName' => 'end2end']);
        $extension->onModuleInit($this->make(SuiteEvent::class, ['getSuite' => $mockSuite]));

        $this->assertFileExists(PhpBuiltInServer::getPidFile());

        $this->assertEquals([
            'running' => 'yes',
            'pidFile' => 'var/_output/php-built-in-server.pid',
            'port' => 8923,
            'docroot' => ltrim(str_replace(getcwd(), '', __DIR__),DIRECTORY_SEPARATOR),
            'workers' => 5,
            'url' => 'http://localhost:8923/',
            'env' => [],
        ], $extension->getInfo());

        $extension->stop($this->output);

        $this->assertEquals([
            'running' => 'no',
            'pidFile' => 'var/_output/php-built-in-server.pid',
            'port' => 8923,
            'docroot' => ltrim(str_replace(getcwd(), '', __DIR__), DIRECTORY_SEPARATOR),
            'workers' => 5,
            'url' => 'http://localhost:8923/',
            'env' => [],
        ], $extension->getInfo());
    }
}

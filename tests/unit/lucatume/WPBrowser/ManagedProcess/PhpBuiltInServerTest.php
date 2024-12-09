<?php


namespace lucatume\WPBrowser\ManagedProcess;

use Codeception\Test\Unit;
use lucatume\WPBrowser\Adapters\Symfony\Component\Process\Process;
use lucatume\WPBrowser\Exceptions\RuntimeException;
use lucatume\WPBrowser\Tests\Traits\TmpFilesCleanup;
use lucatume\WPBrowser\Traits\UopzFunctions;
use lucatume\WPBrowser\Utils\Random;

class PhpBuiltinServerProcessMock extends Process
{
    /**
     * @var mixed[]
     */
    public static $instances = [];

    /**
     * @param mixed $input
     */
    public function __construct(
        array $command,
        ?string $cwd = null,
        ?array $env = null,
        $input = null,
        ?float $timeout = 60
    ) {
        parent::__construct($command, $cwd, $env, $input, $timeout);
        self::$instances[] = [
            'command' => $command,
            'cwd' => $cwd,
            'env' => $env,
            'input' => $input,
            'timeout' => $timeout,
            'object' => $this
        ];
    }
}

class PhpBuiltInServerTest extends Unit
{
    use TmpFilesCleanup;
    use UopzFunctions;

    /**
     * @before
     */
    public function backupPidFiles(): void
    {
        $pidFile = PhpBuiltInServer::getPidFile();
        if (is_file($pidFile)) {
            rename($pidFile, $pidFile . '.bak');
        }
    }

    /**
     * @before
     */
    public function resetPhpBuiltinServerProcessMockInstances():void{
        PhpBuiltinServerProcessMock::$instances = [];
    }

    /**
     * @after
     */
    public function restorePidFiles(): void
    {
        $pidFile = PhpBuiltInServer::getPidFile();
        if (is_file($pidFile . '.bak')) {
            rename($pidFile . '.bak', $pidFile);
        }
    }

    /**
     * It should throw if document root does not exist
     *
     * @test
     */
    public function should_throw_if_document_root_does_not_exist(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionCode(PhpBuiltInServer::ERR_DOC_ROOT_NOT_FOUND);

        new PhpBuiltInServer(__DIR__ . '/not-a-dir');
    }

    public function notAssociativeArrayProvider(): array
    {
        return [
            'integer keys' => [[0 => 'foo', 1 => 'bar']],
            'implicit integer keys' => [['foo', 'bar']],
            'mixed keys' => [['foo' => 'bar', 1 => 'baz']],
        ];
    }

    /**
     * It should throw if env is not associative array
     *
     * @test
     * @dataProvider notAssociativeArrayProvider
     * @param mixed $env
     */
    public function should_throw_if_env_is_not_associative_array($env): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionCode(PhpBuiltInServer::ERR_ENV);

        new PhpBuiltInServer(__DIR__, 2389, $env);
    }

    /**
     * It should start PHP built-in server with specified workers
     *
     * @test
     */
    public function should_start_php_built_in_server_with_specified_workers(): void
    {
        $port = Random::openLocalhostPort();
        $this->setClassMock(Process::class, PhpBuiltinServerProcessMock::class);

        $server = new PhpBuiltInServer(__DIR__, $port, [
            'PHP_CLI_SERVER_WORKERS' => 3,
        ]);
        $server->start();

        $this->assertCount(1, PhpBuiltinServerProcessMock::$instances);
        $this->assertEquals(['PHP_CLI_SERVER_WORKERS' => 3], PhpBuiltinServerProcessMock::$instances[0]['env']);
    }

    /**
     * It should start on random port if not specified
     *
     * @test
     */
    public function should_start_on_random_port_if_not_specified(): void
    {
        $port = Random::openLocalhostPort();
        $this->setClassMock(Process::class, PhpBuiltinServerProcessMock::class);

        $server = new PhpBuiltInServer(__DIR__, $port);
        $server->start();

        $this->assertCount(1, PhpBuiltinServerProcessMock::$instances);
        $this->assertIsInt($server->getPort());
    }

    /**
     * It should throw if specified port already in use
     *
     * @test
     */
    public function should_throw_if_specified_port_already_in_use(): void
    {
        $port = Random::openLocalhostPort();

        $previousServer = new PhpBuiltInServer(__DIR__, $port);
        $previousServer->start();

        // Remove the PID file to make sure the second server will attempt a start.
        if (!unlink(PhpBuiltInServer::getPidFile())) {
            throw new \RuntimeException('Could not delete PID file.');
        }

        $server = new PhpBuiltInServer(__DIR__, $port);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionCode(PhpBuiltInServer::ERR_PORT_ALREADY_IN_USE);

        $server->start();
    }
}

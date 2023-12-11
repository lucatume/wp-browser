<?php

namespace lucatume\WPBrowser\ManagedProcess;

use CurlHandle;
use lucatume\WPBrowser\Adapters\Symfony\Component\Process\Process;
use lucatume\WPBrowser\Exceptions\RuntimeException;
use lucatume\WPBrowser\Utils\Arr;
use lucatume\WPBrowser\Utils\Filesystem;
use lucatume\WPBrowser\Utils\Ports;

class PhpBuiltInServer implements ManagedProcessInterface
{
    /**
     * @var string
     */
    private $docRoot;
    /**
     * @var int
     */
    private $port = 0;
    /**
     * @var array<string, mixed>
     */
    private $env = [];
    use ManagedProcessTrait;

    public const ERR_DOC_ROOT_NOT_FOUND = 1;
    public const ERR_PORT_ALREADY_IN_USE = 2;
    public const ERR_ENV = 3;
    public const ERR_CHECK = 4;
    public const PID_FILE_NAME = 'php-built-in-server.pid';
    /**
     * @var string
     */
    private $prettyName = 'PHP Built-in Server';

    /**
     * @param array<string,mixed> $env
     */
    public function __construct(string $docRoot, int $port = 0, array $env = [])
    {
        $this->docRoot = $docRoot;
        $this->port = $port;
        $this->env = $env;
        if (!(is_dir($docRoot) && is_readable($docRoot))) {
            throw new RuntimeException(
                "Document root directory '$docRoot' not found.",
                self::ERR_DOC_ROOT_NOT_FOUND
            );
        }

        if (!Arr::isAssociative($this->env)) {
            throw new RuntimeException(
                "Environment variables must be an associative array.",
                self::ERR_ENV
            );
        }

        if (!isset($this->env['PHP_CLI_SERVER_WORKERS'])) {
            $this->env['PHP_CLI_SERVER_WORKERS'] = 5;
        }
    }

    /**
     * @throws RuntimeException
     */
    public function doStart(): void
    {
        $routerPathname = dirname(__DIR__, 2) . '/includes/cli-server/router.php';
        $command = [
            PHP_BINARY,
            '-S',
            "localhost:$this->port",
            '-t',
            Filesystem::realpath($this->docRoot) ?: $this->docRoot,
            $routerPathname
        ];

        if (Ports::isPortOccupied($this->port)) {
            throw new RuntimeException(
                'Port ' . $this->port . ' is already in use.',
                self::ERR_PORT_ALREADY_IN_USE
            );
        }

        $process = new Process(
            $command,
            $this->docRoot,
            $this->env
        );
        $process->createNewConsole();
        $process->start();
        if (!$this->confirmServerRunningOnPort($process)) {
            $error = new RuntimeException(
                'Could not start PHP Built-in server: ' . $process->getErrorOutput(),
                ManagedProcessInterface::ERR_START
            );
            $this->stop();
            throw $error;
        }
        $this->process = $process;
    }

    public function getPort(): int
    {
        return $this->port;
    }

    private function confirmServerRunningOnPort(Process $process): bool
    {
        $attempts = 0;
        do {
            if ($process->getExitCode() !== null) {
                return false;
            }

            if ($process->isRunning() && Ports::isPortOccupied($this->port)) {
                return true;
            }

            usleep(100000);
        } while ($attempts++ < 30);

        return false;
    }
}

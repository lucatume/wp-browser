<?php

namespace lucatume\WPBrowser\ManagedProcess;

use lucatume\WPBrowser\Exceptions\RuntimeException;
use Symfony\Component\Process\Process;

class PhpBuiltInServer implements ManagedProcessinterface
{
    use ManagedProcessTrait;

    public const ERR_DOC_ROOT_NOT_FOUND = 10;
    public const ERR_PORT_ALREADY_IN_USE = 11;
    public const PID_FILE_NAME = 'php-built-in-server.pid';
    private string $prettyName = 'PHP built-in server';

    /**
     * @param array<string,mixed> $env
     */
    public function __construct(private string $docRoot, private int $port = 0, private array $env = [])
    {
        if (!(is_dir($docRoot) && is_readable($docRoot))) {
            throw new RuntimeException(
                "Document root directory '$docRoot' not found.",
                self::ERR_DOC_ROOT_NOT_FOUND
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
	    $routerPathname = dirname( __DIR__, 2 ) . '/includes/cli-server/router.php';
	    $process        = new Process(
		    [ PHP_BINARY, '-S', "localhost:$this->port", $routerPathname ],
		    $this->docRoot,
		    $this->env
	    );
        $process->setOptions(['create_new_console' => true]);
        $process->start();
        $confirmPort = $this->readPortFromProcessOutput($process);
        if ($confirmPort === null || !(is_numeric($confirmPort) && (int)$confirmPort > 0)) {
            $error = new RuntimeException(
                'Could not start PHP Built-in server: ' . $process->getErrorOutput(),
                ManagedProcessinterface::ERR_START
            );
            $this->stop();
            throw $error;
        }
        $this->port = (int)$confirmPort;
        $this->process = $process;
    }

    public function getPort(): int
    {
        return $this->port;
    }

    private function readPortFromProcessOutput(Process $process): ?int
    {
        for ($attempts = 0; $attempts < 30; $attempts++) {
            // The Server log is written to STDERR.
            $output = $process->getErrorOutput();
            $matches = [];
            preg_match('/^.*localhost:(?<port>\d+).*$/m', $output, $matches);

            if (!isset($matches['port'])) {
                usleep(100000);
                continue;
            }

            if ($process->getExitCode() !== null) {
                throw new RuntimeException(
                    "PHP Built-in server could not start: port already in use.",
                    self::ERR_PORT_ALREADY_IN_USE
                );
            }

            return (int)$matches['port'];
        }

        return null;
    }
}

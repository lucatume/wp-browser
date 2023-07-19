<?php

namespace lucatume\WPBrowser\Extension;

use Codeception\Exception\ExtensionException;
use lucatume\WPBrowser\ManagedProcess\PhpBuiltInServer;
use Symfony\Component\Console\Output\OutputInterface;

class BuiltInServerController extends ServiceExtension
{
    use PidBasedController;

    /**
     * @throws ExtensionException
     */
    public function start(OutputInterface $output): void
    {
        $pidFile = $this->getPidFile();

        if (is_file($pidFile)) {
            $output->writeln('PHP built-in server already running.');
            return;
        }

        [$port, $docRoot, $workers] = $this->parseConfig();

        $output->write("Starting PHP built-in server on port $port to serve $docRoot ...");
        $phpBuiltInServer = new PhpBuiltInServer($docRoot, $port, ['PHP_CLI_SERVER_WORKERS' => $workers]);
        $phpBuiltInServer->start();
        $output->write(' ok', true);
    }

    public function stop(OutputInterface $output): void
    {
        $pidFile = $this->getPidFile();

        if (!is_file($pidFile)) {
            return;
        }

        $read = file_get_contents($pidFile);

        if ($read === false) {
            throw new ExtensionException(
                $this,
                'Failed to read the PHP built-in server PID file.'
            );
        }

        $pid = (int)$read;

        $output->write("Stopping PHP built-in server with PID $pid ...", false);
        $this->kill($pid, false);
        $this->removePidFile($pidFile);
        $output->write(' ok', true);
    }

    public function getPrettyName(): string
    {
        return 'PHP built-in server';
    }

    /**
     * @return array{
     *     running: string,
     *     port: int,
     *     docroot: string,
     *     workers: int,
     *     pid: int|null
     * }
     * @throws ExtensionException
     */
    public function getInfo(): array
    {
        [$port, $docRoot, $workers] = $this->parseConfig();
        $pidFile = $this->getPidFile();

        return [
            'running' => is_file($pidFile) ? 'yes' : 'no',
            'pid' => is_file($pidFile) ? (int)file_get_contents($pidFile) : null,
            'port' => $port,
            'docroot' => $docRoot,
            'workers' => $workers,
        ];
    }

    /**
     * @return array{
     *     0: int,
     *     1: string,
     *     2: int
     * }
     * @throws ExtensionException
     */
    private function parseConfig(): array
    {
        $config = $this->config;
        if (isset($config['port']) && !(is_numeric($config['port']) && $config['port'] > 0)) {
            throw new ExtensionException(
                $this,
                'The "port" configuration option must be an integer greater than 0.'
            );
        }
        /** @var array{port?: number} $config */
        $port = (int)($config['port'] ?? 2389);

        if (!(isset($config['docroot']) && is_string($config['docroot']) && is_dir($config['docroot']))) {
            throw new ExtensionException(
                $this,
                'The "docroot" configuration option must be a valid directory.'
            );
        }
        /** @var array{docroot: string} $config */
        $docRoot = $config['docroot'];

        if (isset($config['workers']) && !(is_numeric($config['workers']) && $config['workers'] > 0)) {
            throw new ExtensionException(
                $this,
                'The "workers" configuration option must be an integer greater than 0.'
            );
        }
        /** @var array{workers?: number} $config */
        $workers = (int)($config['workers'] ?? 5);
        return array($port, $docRoot, $workers);
    }

    private function getPidFile(): string
    {
        return codecept_output_dir(PhpBuiltInServer::PID_FILE_NAME);
    }
}

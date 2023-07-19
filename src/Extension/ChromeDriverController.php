<?php

namespace lucatume\WPBrowser\Extension;

use Codeception\Exception\ExtensionException;
use lucatume\WPBrowser\ManagedProcess\ChromeDriver;
use Symfony\Component\Console\Output\OutputInterface;

class ChromeDriverController extends ServiceExtension
{
    use PidBasedController;

    /**
     * @throws ExtensionException
     */
    public function start(OutputInterface $output): void
    {
        $pidFile = $this->getPidFile();

        if (is_file($pidFile)) {
            $output->writeln('ChromeDriver already running.');
            return;
        }

        $port = $this->getPort();

        $output->write("Starting ChromeDriver on port $port ...");
        (new ChromeDriver($port))->start();
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
                'Failed to read the ChromeDriver PID file.'
            );
        }

        $pid = (int)$read;

        $output->write("Stopping ChromeDriver with PID $pid ...");
        $this->kill($pid);
        $this->removePidFile($pidFile);
        $output->write(' ok', true);
    }

    public function getPrettyName(): string
    {
        return 'ChromeDriver';
    }

    /**
     * @return array{
     *     running: string,
     *     port: int,
     *     pidFile: string
     * }
     * @throws ExtensionException
     */
    public function getInfo(): array
    {
        $pidFile = $this->getPidFile();
        $port = $this->getPort();

        return [
            'running' => is_file($pidFile) ? 'yes' : 'no',
            'pidFile' => $pidFile,
            'port' => $port,
        ];
    }

    private function getPidFile(): string
    {
        $pidFile = codecept_output_dir(ChromeDriver::PID_FILE_NAME);
        return $pidFile;
    }

    /**
     * @throws ExtensionException
     */
    private function getPort(): int
    {
        $config = $this->config;
        if (isset($config['port']) && !is_numeric($config['port']) && $config['port'] > 0) {
            throw new ExtensionException(
                $this,
                'The "port" configuration option must be an integer greater than 0.'
            );
        }
        /** @var array{port?: number} $config */
        $port = (int)($config['port'] ?? 4444);
        return $port;
    }
}

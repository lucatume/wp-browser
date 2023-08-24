<?php

namespace lucatume\WPBrowser\Extension;

use Codeception\Exception\ExtensionException;
use lucatume\WPBrowser\ManagedProcess\ChromeDriver;
use lucatume\WPBrowser\Utils\ChromedriverInstaller;
use lucatume\WPBrowser\Utils\Composer;
use lucatume\WPBrowser\Utils\Filesystem;
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
        $binary = $this->getBinary();

        $output->write("Starting ChromeDriver on port $port ...");
        (new ChromeDriver($port, [], $binary))->start();
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
                'Could not read the ChromeDriver PID file.'
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
     *     pidFile: string,
     *     port: int
     * }
     * @throws ExtensionException
     */
    public function getInfo(): array
    {
        return [
            'running' => is_file($this->getPidFile()) ? 'yes' : 'no',
            'pidFile' => Filesystem::relativePath(codecept_root_dir(), $this->getPidFile()),
            'port' => $this->getPort(),
        ];
    }

    private function getPidFile(): string
    {
        return ChromeDriver::getPidFile();
    }

    /**
     * @throws ExtensionException
     */
    private function getPort(): int
    {
        $config = $this->config;
        if (isset($config['port']) && !(is_numeric($config['port']) && $config['port'] > 0)) {
            throw new ExtensionException(
                $this,
                'The "port" configuration option must be an integer greater than 0.'
            );
        }

        /** @var array{port?: number} $config */
        return (int)($config['port'] ?? 4444);
    }

    private function getBinary(): ?string
    {
        $config = $this->config;
        if (isset($config['binary']) && !(is_string($config['binary']) && is_executable($config['binary']))) {
            throw new ExtensionException(
                $this,
                'The "binary" configuration option must be an executable file.'
            );
        }

        /** @var array{binary?: string} $config */
        return ($config['binary'] ?? null);
    }
}

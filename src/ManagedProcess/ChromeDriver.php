<?php

namespace lucatume\WPBrowser\ManagedProcess;

use lucatume\WPBrowser\Exceptions\RuntimeException;
use lucatume\WPBrowser\Utils\Composer;
use Symfony\Component\Process\Process;

class ChromeDriver implements ManagedProcessInterface
{
    use ManagedProcessTrait;

    public const PID_FILE_NAME = 'chromedriver.pid';
    public const PORT_DEFAULT = 9515;

    private string $binary;
    private string $prettyName = 'ChromeDriver';

    /**
     * @param array<string> $arguments
     *
     * @throws RuntimeException
     */
    public function __construct(
        private int $port = self::PORT_DEFAULT,
        private array $arguments = ['--url-base=/wd/hub'],
        ?string $binary = null
    ) {
        $binary = $binary ?? Composer::binDir('/chromedriver');

        if (!(file_exists($binary) && is_executable($binary))) {
            throw new RuntimeException(
                "ChromeDriver binary $binary does not exist.",
                ManagedProcessinterface::ERR_BINARY_NOT_FOUND
            );
        }

        $this->binary = $binary;
    }

    /**
     * @throws RuntimeException
     */
    public function doStart(): void
    {
        $command = [$this->binary, '--port=' . $this->port, ...$this->arguments];
        $process = new Process($command);
        $process->setOptions(['create_new_console' => true]);
        $process->start();
        $this->confirmStart($process);
        $this->pid = $process->getPid();
        $this->process = $process;
    }

    /**
     * @throws RuntimeException
     */
    private function confirmStart(Process $process): void
    {
        $start = time();
        $output = $process->getOutput();
        while (time() < $start + 30) {
            if (str_contains($output, 'ChromeDriver was started successfully.')) {
                return;
            }
            if ($process->getExitCode() !== null) {
                break;
            }
            usleep(10000);
            $output .= $process->getOutput();
        }

        throw new RuntimeException(
            'Could not start ChromeDriver: ' . $process->getOutput(),
            ManagedProcessinterface::ERR_START
        );
    }
}

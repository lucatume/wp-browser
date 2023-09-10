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

    private string $chromeDriverBinary;
    private string $prettyName = 'ChromeDriver';

    /**
     * @param array<string> $arguments
     *
     * @throws RuntimeException
     */
    public function __construct(
        private int $port = self::PORT_DEFAULT,
        private array $arguments = ['--url-base=/wd/hub'],
        ?string $chromeDriverBinary = null
    ) {
        $chromeDriverBinary = $chromeDriverBinary ?? Composer::binDir('/chromedriver');

        if (!(file_exists($chromeDriverBinary) && is_executable($chromeDriverBinary))) {
            throw new RuntimeException(
                "ChromeDriver binary $chromeDriverBinary does not exist.",
                ManagedProcessInterface::ERR_BINARY_NOT_FOUND
            );
        }

        $this->chromeDriverBinary = $chromeDriverBinary;
    }

    /**
     * @throws RuntimeException
     */
    public function doStart(): void
    {
        $command = [$this->chromeDriverBinary, '--port=' . $this->port, ...$this->arguments];
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
            ManagedProcessInterface::ERR_START
        );
    }
}

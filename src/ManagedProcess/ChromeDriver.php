<?php

namespace lucatume\WPBrowser\ManagedProcess;

use lucatume\WPBrowser\Adapters\Symfony\Component\Process\Process;
use lucatume\WPBrowser\Exceptions\RuntimeException;
use lucatume\WPBrowser\Utils\Composer;

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
        if ($chromeDriverBinary === null) {
            $chromedriverBinaryFile = DIRECTORY_SEPARATOR === '\\' ? 'chromedriver.exe' : 'chromedriver';
            $chromeDriverBinary = Composer::binDir($chromedriverBinaryFile);
        }

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
    private function doStart(): void
    {
        $command = [$this->chromeDriverBinary, '--port=' . $this->port, ...$this->arguments];
        $process = new Process($command);
        $process->createNewConsole();
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

<?php

namespace lucatume\WPBrowser\ManagedProcess;

use lucatume\WPBrowser\Adapters\Symfony\Component\Process\Process;
use lucatume\WPBrowser\Exceptions\RuntimeException;
use lucatume\WPBrowser\Utils\Composer;

class ChromeDriver implements ManagedProcessInterface
{
    /**
     * @var int
     */
    private $port = self::PORT_DEFAULT;
    /**
     * @var array<string>
     */
    private $arguments = ['--url-base=/wd/hub'];
    use ManagedProcessTrait;

    public const PID_FILE_NAME = 'chromedriver.pid';
    public const PORT_DEFAULT = 9515;

    /**
     * @var string
     */
    private $chromeDriverBinary;
    /**
     * @var string
     */
    private $prettyName = 'ChromeDriver';

    /**
     * @param array<string> $arguments
     *
     * @throws RuntimeException
     */
    public function __construct(
        int $port = self::PORT_DEFAULT,
        array $arguments = ['--url-base=/wd/hub'],
        ?string $chromeDriverBinary = null
    ) {
        $this->port = $port;
        $this->arguments = $arguments;
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
    public function doStart(): void
    {
        $command = array_merge([$this->chromeDriverBinary, '--port=' . $this->port], $this->arguments);
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
            if (strpos($output, 'ChromeDriver was started successfully.') !== false) {
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

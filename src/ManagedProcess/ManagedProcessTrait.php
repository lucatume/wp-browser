<?php

namespace lucatume\WPBrowser\ManagedProcess;

use lucatume\WPBrowser\Exceptions\RuntimeException;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;
use Throwable;

trait ManagedProcessTrait
{
    private ?Process $process = null;
    private ?int $pid = null;

    private function writePidFile(): void
    {
        $pid = $this->process->getPid();

        if (!is_numeric($pid) || (int)$pid < 1) {
            $error = new RuntimeException(
                "Could not start $this->prettyName: " . $this->process->getErrorOutput(),
                ManagedProcessinterface::ERR_PID
            );
            $this->stop();
            throw $error;
        }

        $pidFile = $this->getPidFile();
        if (file_put_contents($pidFile, $pid, LOCK_EX) === false) {
            throw new RuntimeException(
                "Could not write PID file '$pidFile'.",
                ManagedProcessinterface::ERR_PID_FILE
            );
        }
    }

    public function stop(): ?int
    {
        $this->checkStarted();
        return $this->process->stop();
    }

    public function getPidFile(): string
    {
        return codecept_output_dir($this::PID_FILE_NAME);
    }

    private function checkStarted(): void
    {
        if ($this->process === null) {
            throw new RuntimeException(
                "{$this->prettyName} not started.",
                ManagedProcessinterface::ERR_NO_STARTED
            );
        }
    }

    public function getPort(): int
    {
        return $this->port;
    }

    public function start(): void
    {
        if (is_file($this->getPidFile())) {
            return;
        }

        try {
            $this->doStart();
            $this->writePidFile();
        } catch (Throwable $t) {
            if ($this->process instanceof Process) {
                $this->process->stop();
            }
            if (is_file($this->getPidFile())) {
                @unlink($this->getPidFile());
            }
            throw new RuntimeException(
                "Could not start $this->prettyName: " . $t->getMessage(),
                ManagedProcessinterface::ERR_START,
                $t
            );
        }
    }

    public function getPid(): int
    {
        $this->checkIsRunning();
        return $this->pid;
    }

    private function checkIsRunning(): void
    {
        if (!($this->process instanceof Process) && $this->process->isRunning()) {
            throw new RuntimeException(
                "{$this->prettyName} is not running.",
                ManagedProcessinterface::ERR_NOT_RUNNING
            );
        }
    }
}

<?php

namespace lucatume\WPBrowser\ManagedProcess;

use lucatume\WPBrowser\Exceptions\RuntimeException;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

/**
 * @property string $prettyName
 */
trait ManagedProcessTrait
{
    private ?Process $process = null;
    private ?int $pid = null;

    private function writePidFile(): void
    {
        $process = $this->checkIsRunning();
        $pid = $process->getPid();

        if (!is_numeric($pid) || (int)$pid < 1) {
            $error = new RuntimeException(
                "Could not start $this->prettyName: " . $process->getErrorOutput(),
                ManagedProcessInterface::ERR_PID
            );
            $this->stop();
            throw $error;
        }

        $pidFile = static::getPidFile();
        if (file_put_contents($pidFile, $pid, LOCK_EX) === false) {
            throw new RuntimeException(
                "Could not write PID file '$pidFile'.",
                ManagedProcessInterface::ERR_PID_FILE
            );
        }
    }

    public function stop(): ?int
    {
        $process = $this->checkStarted();
        $exitCode = $process->stop();

        if (is_file(static::getPidFile()) && !unlink(static::getPidFile())) {
            throw new RuntimeException(
                "Could not remove PID file '{static::getPidFile(}'.",
                ManagedProcessInterface::ERR_PID_FILE_DELETE
            );
        }

        return $exitCode;
    }

    public static function getPidFile(): string
    {
        return codecept_output_dir(self::PID_FILE_NAME);
    }

    private function checkStarted(): Process
    {
        if ($this->process === null) {
            throw new RuntimeException(
                "{$this->prettyName} not started.",
                ManagedProcessInterface::ERR_NO_STARTED
            );
        }

        return $this->process;
    }

    public function getPort(): int
    {
        return $this->port;
    }

    public function start(): void
    {
        if (is_file(static::getPidFile())) {
            return;
        }

        try {
            $this->doStart();
            $this->writePidFile();
        } catch (ProcessFailedException $t) {
            if ($this->process instanceof Process) {
                $this->process->stop();
            }
            if (is_file(static::getPidFile())) {
                @unlink(static::getPidFile());
            }
            throw new RuntimeException(
                "Could not start $this->prettyName: " . $t->getMessage(),
                ManagedProcessInterface::ERR_START,
                $t
            );
        }
    }

    public function getPid(): ?int
    {
        return $this->checkIsRunning()->getPid();
    }

    private function checkIsRunning(): Process
    {
        if (!($this->process instanceof Process && $this->process->isRunning())) {
            throw new RuntimeException(
                "$this->prettyName is not running.",
                ManagedProcessInterface::ERR_NOT_RUNNING
            );
        }

        return $this->process;
    }
}

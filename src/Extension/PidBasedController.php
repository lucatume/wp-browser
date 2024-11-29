<?php

namespace lucatume\WPBrowser\Extension;

use Codeception\Exception\ExtensionException;
use lucatume\WPBrowser\Exceptions\RuntimeException;

trait PidBasedController
{
    protected function kill(int $pid, bool $single = true): void
    {
        if (DIRECTORY_SEPARATOR === '\\') {
            exec("taskkill /F /T /PID $pid 2>nul 1>nul");
            return;
        }

        if ($single) {
            exec('kill ' . $pid . ' 2>&1 > /dev/null');
            return;
        }

        $command = exec('ps -o command= -p ' . $pid . ' 2>/dev/null');

        if (!$command) {
            exec('kill ' . $pid . ' 2>&1 > /dev/null');
            return;
        }

        exec('pgrep -f "' . $command . '" 2>/dev/null', $pids);

        if (!$pids) {
            exec('kill ' . $pid . ' 2>&1 > /dev/null');
            return;
        }

        foreach ($pids as $kpid) {
            exec('kill ' . $kpid . ' 2>&1 > /dev/null');
        }
    }

    /**
     * @throws ExtensionException
     */
    protected function removePidFile(string $pidFile): void
    {
        if (!unlink($pidFile)) {
            throw new ExtensionException(
                $this,
                "Could not delete PID file '$pidFile'."
            );
        }
    }

    /**
     * @throws RuntimeException
     */
    protected function isProcessRunning(string $pidFile):bool
    {
        if (!is_file($pidFile)) {
            return false;
        }

        try {
            $pidFileContents = file_get_contents($pidFile);
            if ($pidFileContents === false) {
                throw new \Exception();
            }
        } catch (\Exception $e) {
            if (!unlink($pidFile)) {
                throw new RuntimeException("Failed to delete PID file: $pidFile");
            }

            return false;
        }

        $pid = trim($pidFileContents);

        if (!is_numeric($pid) || (int)$pid === 0) {
            return false;
        }

        if (PHP_OS_FAMILY === 'Windows') {
            $output = [];
            exec("tasklist /FI \"PID eq $pid\" 2>NUL", $output);

            return str_contains(implode("\n", $output), $pid);
        } else {
            // Check if the process is running on POSIX (Mac or Linux)
            exec("ps -p $pid", $output, $resultCode);
            if ($resultCode === 0 && count($output) > 1) {
                // Process is running
                return true;
            }
        }

        if (!unlink($pidFile)) {
            throw new RuntimeException("Failed to delete PID file: $pidFile");
        }

        return false;
    }
}

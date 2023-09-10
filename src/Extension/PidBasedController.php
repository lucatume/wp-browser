<?php

namespace lucatume\WPBrowser\Extension;

use Codeception\Exception\ExtensionException;

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
}

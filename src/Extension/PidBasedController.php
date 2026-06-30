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

        if (!$single) {
            // Kill descendants first so long-lived workers (e.g. `php -S` PHP_CLI_SERVER_WORKERS)
            // don't outlive the parent. `pgrep -P $pid` asks the kernel for children directly
            // and doesn't need /bin/ps, which macOS sandbox-exec refuses to execute (setuid
            // root + SIP). Depth-first: recursively kill grandchildren before their parents.
            $children = [];
            exec('pgrep -P ' . $pid . ' 2>/dev/null', $children);
            foreach ($children as $childPid) {
                $childPid = (int)$childPid;
                if ($childPid > 0) {
                    $this->kill($childPid, false);
                }
            }
        }

        exec('kill ' . $pid . ' 2>&1 > /dev/null');
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

        if (strtolower(substr(PHP_OS, 0, 3)) === 'win') {
            $output = [];
            exec("tasklist /FI \"PID eq $pid\" 2>NUL", $output);

            return strpos(implode("\n", $output), $pid) !== false;
        } elseif (function_exists('posix_kill')) {
            // Liveness check on POSIX via posix_kill with signal 0: true iff the process exists
            // and is signalable by the current user. Avoids shelling out to /bin/ps, which on
            // macOS is setuid root and cannot be exec()ed under sandbox-exec regardless of profile.
            // ext-posix is suggested (not required) for portability, so we fall through to the
            // ps-based check when it's not loaded.
            if (@posix_kill((int)$pid, 0)) {
                return true;
            }
        } else {
            // Fallback for POSIX systems without ext-posix: query /bin/ps directly.
            $output = [];
            exec("ps -p $pid", $output, $resultCode);
            if ($resultCode === 0 && count($output) > 1) {
                return true;
            }
        }

        if (!unlink($pidFile)) {
            throw new RuntimeException("Failed to delete PID file: $pidFile");
        }

        return false;
    }
}

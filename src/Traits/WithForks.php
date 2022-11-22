<?php
/**
 * Provides methods to execute some code in a fork.
 *
 * Requires the `pcntl` and `sockets` extensions.
 *
 * @package lucatume\WPBrowser\Traits;
 */

namespace lucatume\WPBrowser\Traits;

use Exception;
use RuntimeException;
use Throwable;

/**
 * Trait WithForks.
 *
 * @package lucatume\WPBrowser\Traits;
 */
trait WithForks
{
    /**
     * Executes a callable in a fork.
     *
     * @param callable $do A callable function, object or Closure to execute in the fork. Note
     *                     the value will be cast to string, and it's up to the callable to
     *                     produce some value that will survive it. *
     *
     * @return string|void The return value of the callable executed in the fork.
     */
    private function inAFork(callable $do)
    {
        $isTestCase = method_exists($this, 'markTestSkipped');

        if (! function_exists('pcntl_fork')) {
            if ($isTestCase) {
                $this->markTestSkipped('This test requires the pcntl_fork function');
            } else {
                throw new RuntimeException('This trait requires the pcntl_fork function.');
            }
        }

        if (! function_exists('socket_create_pair')) {
            if ($isTestCase) {
                $this->markTestSkipped('This test requires the socket_create_pair function');
            } else {
                throw new RuntimeException('This trait requires the socket_create_pair function.');
            }
        }

        $ipcSockets = [];
        if (! socket_create_pair(AF_UNIX, SOCK_STREAM, 0, $ipcSockets)) {
            throw new RuntimeException('Failed to crate IPC socket pair.');
        }

        [$childProcIpcSocket, $parentProcIpcSocket] = $ipcSockets;

        $pid = pcntl_fork();

        if ($pid === -1) {
            throw new RuntimeException('Failed to fork: ' . pcntl_get_last_error());
        }

        if ($pid === 0) {
            socket_close($parentProcIpcSocket);

            $exitStatus = 0;
            try {
                $message = (string)$do();
                if (socket_write($childProcIpcSocket, $message, strlen($message)) === false) {
                    throw new RuntimeException('Failed to write to socket: ' . socket_last_error($parentProcIpcSocket));
                }
            } catch (Throwable $t) {
                $message = $t->getMessage();
                socket_write($childProcIpcSocket, $message, strlen($message));
                $exitStatus = $t->getCode();
            } catch (Exception $e) {
                $message = $e->getMessage();
                socket_write($childProcIpcSocket, $message, strlen($message));
                $exitStatus = $e->getCode();
            }
            // Hack to avoid Codeception "COMMAND DID NOT FINISH PROPERLY" message due to `exit`.
            ob_start(static function ($string) {
                // Log nothing from the fork process.
            });
            exit($exitStatus);
        }

        socket_close($childProcIpcSocket);
        pcntl_wait($status);
        $message = '';
        while ($read = socket_read($parentProcIpcSocket, 1024)) {
            $message .= $read;
        }
        socket_close($parentProcIpcSocket);

        return $message;
    }
}

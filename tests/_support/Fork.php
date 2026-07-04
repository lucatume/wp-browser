<?php

declare(strict_types=1);

namespace lucatume\WPBrowser\Tests\Traits;

use Codeception\Util\Debug;
use lucatume\WPBrowser\Process\SerializableThrowable;
use lucatume\WPBrowser\Utils\PackedClosure;

class Fork
{
    private const TERMINATOR = '__WPBROWSER_SEPARATOR__';
    private const IPC_SOCKET_CHUNK_SIZE = 2048;
    private const TIMEOUT = 30;

    private static ?\Closure $childShutdownHandler = null;
    private static bool $shutdownHookRegistered = false;

    /**
     * Call from the global bootstrap, before Codeception registers its ErrorHandler shutdown handler.
     *
     * Code run in fork children may `exit` by design (e.g. WordPress' not-installed redirect); the child then walks
     * the shutdown-function queue inherited from the parent. Codeception's ErrorHandler shutdown handler `exit(125)`s
     * in that scenario, aborting the queue before a handler registered by `executeFork()` could report back to the
     * parent. Registering this hook early puts the fork reporting logic ahead of it in the queue.
     */
    public static function registerChildShutdownHandler(): void
    {
        if (self::$shutdownHookRegistered) {
            return;
        }

        self::$shutdownHookRegistered = true;
        register_shutdown_function(static function (): void {
            if (self::$childShutdownHandler !== null) {
                (self::$childShutdownHandler)();
            }
        });
    }

    public static function executeClosure(\Closure $callback): mixed
    {
        if (!(function_exists('pcntl_fork') && function_exists('posix_kill'))) {
            throw new \RuntimeException('pcntl and posix extensions missing.');
        }

        $sockets = stream_socket_pair(STREAM_PF_UNIX, STREAM_SOCK_STREAM, STREAM_IPPROTO_IP);

        if ($sockets === false) {
            throw new \RuntimeException('Failed to create socket pair');
        }

        /** @var array{0: resource, 1: resource} $sockets */

        $pid = pcntl_fork();
        if ($pid === -1) {
            fclose($sockets[0]);
            fclose($sockets[1]);
            throw new \RuntimeException('Failed to fork');
        }

        if ($pid === 0) {
            self::executeFork($callback, $sockets);
            // Unreachable unless the self-SIGKILL failed: never fall through into the parent flow.
            exit(0);
        }

        return self::executeMain($pid, $sockets);
    }

    /**
     * @param array{0: resource, 1: resource} $sockets
     */
    private static function executeFork(\Closure $callback, array $sockets): void
    {
        fclose($sockets[1]);
        $ipcSocket = $sockets[0];
        $pid = getmypid();
        $didWritePayload = false;

        if ($pid === false) {
            die('Failed to get pid');
        }

        self::$childShutdownHandler = static function () use ($pid, $ipcSocket, &$didWritePayload) {
            if (!$didWritePayload) {
                // Reached on `exit`: flush pending output buffers now to capture throwing callbacks.
                try {
                    for ($level = ob_get_level(); $level > 0; $level--) {
                        ob_end_flush();
                    }
                    $lastError = error_get_last();
                    $throwable = new \RuntimeException(
                        'Fork child exited before returning a result.'
                        . ($lastError !== null ? ' Last error: ' . $lastError['message'] : '')
                    );
                } catch (\Throwable $throwable) {
                }
                self::writeResultPayload($ipcSocket, serialize(new SerializableThrowable($throwable)));
                $didWritePayload = true;
            }
            fclose($ipcSocket);
            /** @noinspection PhpComposerExtensionStubsInspection */
            posix_kill($pid, 9 /* SIGKILL */);
        };
        // Fallback for children forked outside a bootstrapped Codeception run.
        self::registerChildShutdownHandler();

        try {
            $result = $callback();
            $resultClosure = new PackedClosure(static function () use ($result) {
                return $result;
            });
            $resultPayload = serialize($resultClosure);
        } catch (\Throwable $throwable) {
            $resultPayload = serialize(new SerializableThrowable($throwable));
        }

        self::writeResultPayload($ipcSocket, $resultPayload);
        $didWritePayload = true;
        fclose($ipcSocket);

        // Kill the child process now with a signal that will not run shutdown handlers.
        /** @noinspection PhpComposerExtensionStubsInspection */
        posix_kill($pid, 9 /* SIGKILL */);
    }

    /**
     * Base64 keeps the terminator out of the payload bytes ('__' cannot appear in base64 output).
     *
     * @param resource $ipcSocket
     */
    private static function writeResultPayload($ipcSocket, string $resultPayload): void
    {
        $encoded = base64_encode($resultPayload);
        $length = strlen($encoded);
        $offset = 0;

        while ($offset < $length) {
            $written = fwrite($ipcSocket, substr($encoded, $offset, self::IPC_SOCKET_CHUNK_SIZE));

            if ($written === false || $written === 0) {
                // Parent gone or socket dead: nothing left to report to.
                return;
            }

            $offset += $written;
        }

        fwrite($ipcSocket, self::TERMINATOR);
    }

    /**
     * @param array{0: resource, 1: resource} $sockets
     * @throws \Throwable
     */
    private static function executeMain(int $pid, array $sockets): mixed
    {
        fclose($sockets[0]);
        $ipcSocket = $sockets[1];
        $resultPayload = '';
        $deadline = Debug::isEnabled() ? INF : microtime(true) + self::TIMEOUT;

        while (!str_ends_with($resultPayload, self::TERMINATOR) && !feof($ipcSocket)) {
            $read = [$ipcSocket];
            $write = null;
            $except = null;

            // Silenced: EINTR makes stream_select return false with a warning; the loop just retries.
            if (@stream_select($read, $write, $except, 1) > 0) {
                $resultPayload .= (string)fread($ipcSocket, self::IPC_SOCKET_CHUNK_SIZE);
            }

            if (microtime(true) > $deadline) {
                fclose($ipcSocket);
                /** @noinspection PhpComposerExtensionStubsInspection */
                posix_kill($pid, 9 /* SIGKILL */);
                pcntl_waitpid($pid, $status);
                throw new \RuntimeException(
                    'Fork child timed out after ' . self::TIMEOUT . 's; partial payload: '
                    . substr($resultPayload, -512)
                );
            }
        }

        fclose($ipcSocket);
        /** @noinspection PhpComposerExtensionStubsInspection */
        pcntl_waitpid($pid, $status);

        if (!str_ends_with($resultPayload, self::TERMINATOR)) {
            $exitDetail = pcntl_wifsignaled($status) ?
                'killed by signal ' . pcntl_wtermsig($status)
                : 'exited with status ' . pcntl_wexitstatus($status);
            throw new \RuntimeException(
                "Fork child died ($exitDetail) without sending a result; partial payload: "
                . substr($resultPayload, -512)
            );
        }

        $resultPayload = substr($resultPayload, 0, -strlen(self::TERMINATOR));

        $unserializedPayload = @unserialize((string)base64_decode($resultPayload, true));

        if ($unserializedPayload instanceof SerializableThrowable) {
            throw $unserializedPayload->getThrowable();
        }

        if (!$unserializedPayload instanceof PackedClosure) {
            throw new \RuntimeException(
                'Fork child sent an unreadable result (fatal error in child?); raw payload: '
                . substr($resultPayload, -512)
            );
        }

        return $unserializedPayload->getClosure()();
    }
}

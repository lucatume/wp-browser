<?php

declare(strict_types=1);

namespace lucatume\WPBrowser\Tests\Traits;

use lucatume\WPBrowser\Process\SerializableThrowable;
use lucatume\WPBrowser\Utils\PackedClosure;

/**
 * Class Fork.
 *
 * @since TBD
 *
 * @package lucatume\WPBrowser\Tests;
 */
class Fork
{
    const DEFAULT_TERMINATOR = '__WPBROWSER_SEPARATOR__';
    private static ?\Closure $childShutdownHandler = null;
    private static bool $shutdownHookRegistered = false;
    private \Closure $callback;
    private bool $quiet = false;
    /**
     * @var int<0, max>
     */
    private int $ipcSocketChunkSize = 2048;
    private string $terminator = self::DEFAULT_TERMINATOR;

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

    public static function executeClosure(
        \Closure $callback,
        bool $quiet = false,
        int $ipcSocketChunkSize = 2048,
        string $terminator = self::DEFAULT_TERMINATOR
    ): mixed {
        return (new self($callback))
            ->setQuiet($quiet)
            ->setIpcSocketChunkSize($ipcSocketChunkSize)
            ->setTerminator($terminator)
            ->execute();
    }

    public function __construct(\Closure $callback)
    {
        $this->callback = $callback;
    }

    public function setQuiet(bool $quiet): self
    {
        $this->quiet = $quiet;
        return $this;
    }

    public function execute(): mixed
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
            throw new \RuntimeException('Failed to fork');
        }


        if ($pid === 0) {
            $this->executeFork($sockets);
        }

        return $this->executeMain($pid, $sockets);
    }

    public function setIpcSocketChunkSize(int $ipcSocketChunkSize): self
    {
        if ($ipcSocketChunkSize < 0) {
            throw new \InvalidArgumentException('ipcSocketChunkSize must be a positive integer');
        }

        $this->ipcSocketChunkSize = $ipcSocketChunkSize;
        return $this;
    }

    public function setTerminator(string $terminator): self
    {
        $this->terminator = $terminator;
        return $this;
    }

    /**
     * @param array{0: resource, 1: resource} $sockets
     */
    private function executeFork(array $sockets): void
    {
        fclose($sockets[1]);
        $ipcSocket = $sockets[0];
        $pid = getmypid();
        $didWriteTerminator = false;
        $terminator = $this->terminator;

        if ($pid === false) {
            die('Failed to get pid');
        }

        if ($this->quiet) {
            fclose(STDOUT);
            fclose(STDERR);
        }

        self::$childShutdownHandler = function () use ($pid, $ipcSocket, &$didWriteTerminator, $terminator) {
            if (!$didWriteTerminator) {
                // Reached on `exit`: flush pending output buffers now to capture throwing callbacks.
                try {
                    $level = ob_get_level();
                    while (ob_get_level() > 0 && $level-- > 0) {
                        ob_end_flush();
                    }
                    fwrite($ipcSocket, $terminator);
                } catch (\Throwable $throwable) {
                    $this->writeResultPayload($ipcSocket, serialize(new SerializableThrowable($throwable)));
                }
                $didWriteTerminator = true;
            }
            fclose($ipcSocket);
            /** @noinspection PhpComposerExtensionStubsInspection */
            posix_kill($pid, 9 /* SIGKILL */);
        };
        // Fallback for children forked outside a bootstrapped Codeception run.
        self::registerChildShutdownHandler();

        try {
            $result = ($this->callback)();
            $resultClosure = new PackedClosure(static function () use ($result) {
                return $result;
            });
            $resultPayload = serialize($resultClosure);
        } catch (\Throwable $throwable) {
            $resultPayload = serialize(new SerializableThrowable($throwable));
        }

        $this->writeResultPayload($ipcSocket, $resultPayload);
        $didWriteTerminator = true;
        fclose($ipcSocket);

        // Kill the child process now with a signal that will not run shutdown handlers.
        /** @noinspection PhpComposerExtensionStubsInspection */
        posix_kill($pid, 9 /* SIGKILL */);
    }

    /**
     * @param resource $ipcSocket
     */
    private function writeResultPayload($ipcSocket, string $resultPayload): void
    {
        $offset = 0;
        while (true) {
            $chunk = substr($resultPayload, $offset, $this->ipcSocketChunkSize);

            if ($chunk === '') {
                break;
            }

            fwrite($ipcSocket, $chunk);
            $offset += $this->ipcSocketChunkSize;
        }
        fwrite($ipcSocket, $this->terminator);
    }

    /**
     * @param array{0: resource, 1: resource} $sockets
     * @throws \Throwable
     */
    private function executeMain(int $pid, array $sockets): mixed
    {
        fclose($sockets[0]);
        $resultPayload = '';

        while (!str_ends_with($resultPayload, $this->terminator) && !feof($sockets[1])) {
            $resultPayload .= (string)fread($sockets[1], $this->ipcSocketChunkSize);
        }

        fclose($sockets[1]);
        /** @noinspection PhpComposerExtensionStubsInspection */
        pcntl_waitpid($pid, $status);

        if (!str_ends_with($resultPayload, $this->terminator)) {
            $exitDetail = pcntl_wifsignaled($status) ?
                'killed by signal ' . pcntl_wtermsig($status)
                : 'exited with status ' . pcntl_wexitstatus($status);
            throw new \RuntimeException(
                "Fork child died ($exitDetail) without sending a result; partial payload: "
                . substr($resultPayload, -512)
            );
        }

        $resultPayload = substr($resultPayload, 0, -strlen($this->terminator));

        $unserializedPayload = @unserialize($resultPayload);

        if ($unserializedPayload instanceof SerializableThrowable) {
            throw $unserializedPayload->getThrowable();
        }

        if (!$unserializedPayload instanceof PackedClosure) {
            throw new \RuntimeException(
                'Fork child sent an unreadable result (fatal error in child?); raw payload: '
                . substr($resultPayload, -512)
            );
        }

        $result = $unserializedPayload->getClosure()();

        if ($result instanceof \Throwable) {
            throw $result;
        }

        return $result;
    }
}

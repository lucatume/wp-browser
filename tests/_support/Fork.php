<?php

declare(strict_types=1);

namespace lucatume\WPBrowser\Tests\Traits;

use lucatume\WPBrowser\Opis\Closure\SerializableClosure;
use lucatume\WPBrowser\Process\SerializableThrowable;

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
    /**
     * @var \Closure
     */
    private $callback;
    /**
     * @var bool
     */
    private $quiet = false;
    /**
     * @var int<0, max>
     */
    private $ipcSocketChunkSize = 2048;
    /**
     * @var string
     */
    private $terminator = self::DEFAULT_TERMINATOR;

    /**
     * @return mixed
     */
    public static function executeClosure(
        \Closure $callback,
        bool $quiet = false,
        int $ipcSocketChunkSize = 2048,
        string $terminator = self::DEFAULT_TERMINATOR
    ) {
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

    /**
     * @return mixed
     */
    public function execute()
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

        register_shutdown_function(static function () use ($pid, $ipcSocket, &$didWriteTerminator, $terminator) {
            if (!$didWriteTerminator) {
                fwrite($ipcSocket, $terminator);
                $didWriteTerminator = true;
            }
            fclose($ipcSocket);
            /** @noinspection PhpComposerExtensionStubsInspection */
            posix_kill($pid, 9 /* SIGKILL */);
        });

        try {
            $result = ($this->callback)();
            $resultClosure = new SerializableClosure(static function () use ($result) {
                return $result;
            });
            $resultPayload = serialize($resultClosure);
        } catch (\Throwable $throwable) {
            $resultPayload = serialize(new SerializableThrowable($throwable));
        } finally {
            if (!isset($resultPayload)) {
                // Something went wrong.
                fwrite($ipcSocket, serialize(null));
                fwrite($ipcSocket, $this->terminator);
                $didWriteTerminator = true;
                /** @noinspection PhpComposerExtensionStubsInspection */
                posix_kill($pid, 9 /* SIGKILL */);
            }
        }

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
        $didWriteTerminator = true;
        fclose($ipcSocket);

        // Kill the child process now with a signal that will not run shutdown handlers.
        /** @noinspection PhpComposerExtensionStubsInspection */
        posix_kill($pid, 9 /* SIGKILL */);
    }

    /**
     * @param array{0: resource, 1: resource} $sockets
     * @throws \Throwable
     * @return mixed
     */
    private function executeMain(int $pid, array $sockets)
    {
        fclose($sockets[0]);
        $resultPayload = '';

        /** @noinspection PhpComposerExtensionStubsInspection */
        while (pcntl_wait($status, 1 /* WNOHANG */) <= 0) {
            $chunk = fread($sockets[1], $this->ipcSocketChunkSize);
            $resultPayload .= $chunk;
        }

        while (substr_compare($resultPayload, $this->terminator, -strlen($this->terminator)) !== 0) {
            $chunk = fread($sockets[1], $this->ipcSocketChunkSize);
            $resultPayload .= $chunk;
        }

        fclose($sockets[1]);

        if (substr_compare($resultPayload, $this->terminator, -strlen($this->terminator)) === 0) {
            $resultPayload = substr($resultPayload, 0, -strlen($this->terminator));
        }

        try {
            /** @var SerializableClosure|SerializableThrowable $unserializedPayload */
            $unserializedPayload = @unserialize($resultPayload);
            $result = $unserializedPayload instanceof SerializableThrowable ?
                $unserializedPayload->getThrowable() : $unserializedPayload->getClosure()();
        } catch (\Throwable $t) {
            $result = $resultPayload;
        }

        if ($result instanceof \Throwable) {
            throw $result;
        }

        /** @noinspection PhpComposerExtensionStubsInspection */
        posix_kill($pid, 9 /* SIGKILL */);

        return $result;
    }
}

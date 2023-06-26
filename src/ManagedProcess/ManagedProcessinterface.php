<?php
/**
 * ${CARET}
 *
 * @since TBD
 */

namespace lucatume\WPBrowser\ManagedProcess;

use lucatume\WPBrowser\Exceptions\RuntimeException;

interface ManagedProcessinterface
{
    public const ERR_START = 1;
    public const ERR_NO_STARTED = 2;
    public const ERR_PID = 3;
    public const ERR_PID_FILE = 4;
    public const ERR_BINARY_NOT_FOUND = 5;
    public const ERR_STOP = 6;
    public const ERR_NOT_RUNNING = 7;
    public const ERR_PID_FILE_DELETE = 8;

    public function start(): void;

    public function getPort(): int;

    public function stop(): ?int;

    public function getPid(): int;

    public function getPidFile(): string;
}

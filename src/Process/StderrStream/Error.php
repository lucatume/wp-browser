<?php

namespace lucatume\WPBrowser\Process\StderrStream;

class Error
{
    public bool $isException = false;
    public string $exceptionClass = '';
    public string $date = '';
    public string $time = '';
    public string $timezone = '';
    public string $type = '';
    public string $message = '';
    public string $file = '';
    public int $line = 0;
    /**
     * @var array<TraceEntry>
     */
    public array $trace = [];
}

<?php

namespace lucatume\WPBrowser\Process\StderrStream;

class TraceEntry
{
    public string $date = '';
    public string $time = '';
    public string $timezone = '';
    public string $call = '';
    public string $args = '';
    public string $file = '';
    public int $line = 0;
}

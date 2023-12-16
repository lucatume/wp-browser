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

    /**
     * @return array{
     *     date: string,
     *     time: string,
     *     timezone: string,
     *     call: string,
     *     args: string,
     *     file: string,
     *     line: int
     * }
     */
    public function toArray(): array
    {
        return [
            'date' => $this->date,
            'time' => $this->time,
            'timezone' => $this->timezone,
            'call' => $this->call,
            'args' => $this->args,
            'file' => $this->file,
            'line' => $this->line,
        ];
    }
}

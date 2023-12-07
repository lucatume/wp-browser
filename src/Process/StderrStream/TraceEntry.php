<?php

namespace lucatume\WPBrowser\Process\StderrStream;

class TraceEntry
{
    /**
     * @var string
     */
    public $date = '';
    /**
     * @var string
     */
    public $time = '';
    /**
     * @var string
     */
    public $timezone = '';
    /**
     * @var string
     */
    public $call = '';
    /**
     * @var string
     */
    public $args = '';
    /**
     * @var string
     */
    public $file = '';
    /**
     * @var int
     */
    public $line = 0;

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

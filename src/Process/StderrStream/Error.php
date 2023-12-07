<?php

namespace lucatume\WPBrowser\Process\StderrStream;

class Error
{
    /**
     * @var bool
     */
    public $isException = false;
    /**
     * @var string
     */
    public $exceptionClass = '';
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
    public $type = '';
    /**
     * @var string
     */
    public $message = '';
    /**
     * @var string
     */
    public $file = '';
    /**
     * @var int
     */
    public $line = 0;
    /**
     * @var array<TraceEntry>
     */
    public $trace = [];
}

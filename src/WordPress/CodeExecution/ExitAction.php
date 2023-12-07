<?php

namespace lucatume\WPBrowser\WordPress\CodeExecution;

use Closure;

class ExitAction implements CodeExecutionActionInterface
{
    /**
     * @var int
     */
    private $exitCode;
    /**
     * @var string
     */
    private $stdout = '';
    /**
     * @var string
     */
    private $stderr = '';
    public function __construct(int $exitCode, string $stdout = '', string $stderr = '')
    {
        $this->exitCode = $exitCode;
        $this->stdout = $stdout;
        $this->stderr = $stderr;
    }

    public function getClosure(): Closure
    {
        $exitCode = $this->exitCode;
        $stdout = $this->stdout;
        $stderr = $this->stderr;

        return static function () use ($exitCode, $stdout, $stderr) {
            fwrite(STDOUT, $stdout, strlen($stdout));
            fwrite(STDERR, $stderr, strlen($stderr));
            exit($exitCode);
        };
    }
}

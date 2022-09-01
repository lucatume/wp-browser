<?php

namespace lucatume\WPBrowser\Traits;

use Symfony\Component\Process\Process;

trait CommandExecution
{
    protected function runCommand(
        array $command,
        string $cwd = null,
        array $env = null,
        mixed $input = null,
        ?float $timeout = 60
    ): Process {
        $process = new Process($command, $cwd, $env, $input, $timeout);
        $process->run();

        return $process;
    }
}

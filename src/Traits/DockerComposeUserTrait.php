<?php
namespace lucatume\WPBrowser\Traits;

use lucatume\WPBrowser\Exceptions\RuntimeException;
use Symfony\Component\Process\Process;

trait DockerComposeUserTrait
{
    protected function ensureDockerOrFail(): void
    {
        $process = new Process(['docker','--version']);
        $process->mustRun();
    }
}

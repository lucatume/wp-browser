<?php

namespace lucatume\WPBrowser\Tests\Traits;

use Closure;
use Codeception\Configuration;
use lucatume\WPBrowser\Process\Loop;
use lucatume\WPBrowser\Process\ProcessException;
use lucatume\WPBrowser\Process\WorkerException;
use lucatume\WPBrowser\Utils\Codeception;
use ReflectionFunction;
use ReflectionObject;
use Throwable;

trait LoopIsolation
{
    /**
     * @throws WorkerException
     * @throws Throwable
     * @throws ProcessException
     */
    protected function assertInIsolation(
        Closure $runAssertions,
        string $cwd = null,
        array $requireFiles = [],
    ): mixed {
        $options = ['rethrow' => true];

        $callerFile = (new ReflectionObject($this))->getFileName();
        $options['requireFiles'] = $requireFiles ?: [];
        if ($callerFile !== false) {
            $options['requireFiles'][] = $callerFile;
        }

        $options['cwd'] = !empty($options['cwd']) ? $options['cwd'] : getcwd();

        $result = Loop::executeClosure($runAssertions, 30, $options);
        $returnValue = $result->getReturnValue();

        if (! $returnValue instanceof \Throwable && $result->getExitCode() !== 0) {
            codecept_debug('STDOUT: ' . $result->getStdoutBuffer());
            codecept_debug('STDERR: ' . $result->getStderrBuffer());
            $this->fail('Loop execution failed with exit code ' . $result->getExitCode());
        }

        return $returnValue;
    }
}

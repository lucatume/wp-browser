<?php

namespace lucatume\WPBrowser\Tests\Traits;

use Closure;
use Codeception\Util\Debug;
use lucatume\WPBrowser\Process\Loop;
use lucatume\WPBrowser\Process\ProcessException;
use lucatume\WPBrowser\Process\WorkerException;
use lucatume\WPBrowser\Utils\Property;
use ReflectionObject;
use Throwable;

trait LoopIsolation
{
    /**
     * @throws WorkerException
     * @throws Throwable
     * @throws ProcessException
     * @return mixed
     */
    protected function assertInIsolation(Closure $runAssertions, string $cwd = null, array $requireFiles = [])
    {
        $options = ['cwd' => $cwd, 'rethrow' => false];
        $callerFile = (new ReflectionObject($this))->getFileName();
        $options['requireFiles'] = $requireFiles ?: [];
        if ($callerFile !== false) {
            $options['requireFiles'][] = $callerFile;
        }
        $options['cwd'] = !empty($options['cwd']) ? $options['cwd'] : getcwd();
        $timeout = Debug::isEnabled() ? PHP_INT_MAX : 30;
        $result = Loop::executeClosure($runAssertions, $timeout, $options);
        $returnValue = $result->getReturnValue();
        if ($returnValue instanceof Throwable) {
            // Update the stack trace to present the loop execution frame on top of the stack.
            $stackTrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
            Property::setPrivateProperties(
                $returnValue,
                ['trace' => array_merge($returnValue->getTrace(), $stackTrace),]
            );
            throw $returnValue;
        }
        if ($result->getExitCode() !== 0) {
            codecept_debug('STDOUT: ' . $result->getStdoutBuffer());
            codecept_debug('STDERR: ' . $result->getStderrBuffer());
            $this->fail('Loop execution failed with exit code ' . $result->getExitCode());
        }
        return $returnValue;
    }
}

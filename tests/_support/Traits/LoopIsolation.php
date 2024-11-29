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
     */
    protected function assertInIsolation(
        Closure $runAssertions,
        string $cwd = null,
        array $requireFiles = [],
    ): mixed {
        $options = ['cwd' => $cwd, 'rethrow' => false];

        $callerFile = (new ReflectionObject($this))->getFileName();
        $options['requireFiles'] = $requireFiles ?: [];
        if ($callerFile !== false) {
            $options['requireFiles'][] = $callerFile;
        }

        $options['cwd'] = !empty($options['cwd']) ? $options['cwd'] : getcwd();
        $options['useFilePayloads'] = true;

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
            $failureMessage = sprintf(
                "\nEXIT CODE: %s\n\nSTDOUT---\n%s\n\nSTDERR---\n%s\n",
                $result->getExitCode(),
                $result->getStdoutBuffer(),
                $result->getStderrBuffer()
            );
            $this->fail($failureMessage);
        }

        return $returnValue;
    }
}

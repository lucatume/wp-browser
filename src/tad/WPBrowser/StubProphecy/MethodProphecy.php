<?php
/**
 * The prophecy for a method, a "port" of the class by the same name from the phpspce Prophecy framework.
 *
 * @package tad\WPBrowser\StubProphecy
 */

namespace tad\WPBrowser\StubProphecy;

use PHPUnit\Framework\Assert;
use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;

/**
 * Class MethodProphecy
 *
 * @package tad\WPBrowser\StubProphecy
 */
class MethodProphecy
{
    /**
     * The fully qualified name of the class the method belongs to.
     * @var string
     */
    protected $class;

    /**
     * The name of the method this prophecy is for.
     * @var string
     */
    protected $name;

    /**
     * The expected call arguments for the method, if any.
     *
     * @var array<mixed>
     */
    protected $expectedArguments = [];
    /**
     * The value the method prophecy should return when called, or a callback that will produce the method prophecy
     * return value when called.
     * @var callable|mixed
     */
    protected $returnValue;
    /**
     * The method prophecy call count, or `null` to indicate there are no expectations on the call count of the method.
     * @var int|null
     */
    protected $expectedCallCount;

    /**
     * The actual call count of the method prophecy.
     * @var int
     */
    protected $actualCallCount = 0;

    /**
     * The post conditions that will be verified at the destruction of this object.
     * @var array<callable> An array of post conditions to assert when the object is destroyed.
     */
    protected $postConditions = [];

    /**
     * Whether the argument verification is stopped or not.
     * @var bool
     */
    protected $verificationStopped = false;

    /**
     * MethodProphecy constructor.
     *
     * @param string                $class             The fully-qualified name of the class to build a method prophecy
     *                                                 for.
     * @param string                $name              The name of the method to build a prophecy for.
     * @param array<mixed|callable> $expectedArguments The expected call arguments for the method; each call argument
     *                                                 can be a `callable` w/ the following signature:
     *                                                 function(mixed $actualArg, array $actualArgs, string $method).
     *
     * @throws StubProphecyException If there's an issue reflecting on the class method or the required argument
     *                               expectations are not satisfied.
     */
    public function __construct($class, $name, array $expectedArguments = [])
    {
        $this->class             = $class;
        $this->name              = $name;
        $this->expectedArguments = $this->setupExpectedArgs($expectedArguments);
    }

    /**
     * Builds the call parameters expectations.
     *
     * @param array<mixed> $expectedArguments The expected arguments; this list might cover all the possible method
     *                                        parameters or only a part of them. Not specified method parameters will
     *                                        be set up with an expectation of the default parameter value.
     *
     * @return array<mixed> A list of either the expected arguments.
     *
     * @throws StubProphecyException If there's an issue reflecting on the method or the expected arguments do not match
     *                               all the required arguments.
     */
    protected function setupExpectedArgs(array $expectedArguments = [])
    {
        $argumentExpectations = [];

        try {
            $reflectionMethod = new \ReflectionMethod($this->class, $this->name);
            $methodParameters = $reflectionMethod->getParameters();
            $expectedKeys = array_keys($expectedArguments);
            foreach ($methodParameters as $i => $methodParameter) {
                if (isset($expectedKeys[ $i ])) {
                    $argumentExpectations[] = $expectedArguments[ $i ];
                    continue;
                }
                if (isset($argumentExpectations[ $i - 1 ]) && $argumentExpectations[ $i - 1 ] instanceof ArgInterface) {
                    if ($argumentExpectations[ $i - 1 ]->appliesToFollowing()) {
                        $argumentExpectations[ $i ] = $argumentExpectations[ $i - 1 ];
                        continue;
                    }
                }
                if (! $methodParameter->isDefaultValueAvailable()) {
                    throw new StubProphecyException(
                        sprintf(
                            'Method %s parameter %d (%s) does not have a default value: you must specify an ' .
                            'expectation for it.',
                            $this->name,
                            $i,
                            $methodParameter->name
                        )
                    );
                }

                $argumentExpectations[] = $methodParameter->getDefaultValue();
            }
        } catch (\ReflectionException $e) {
            throw new StubProphecyException(
                sprintf(
                    'Error while trying to reflect on method %s::%s: %s',
                    $this->class,
                    $this->name,
                    $e->getMessage()
                )
            );
        }

        return $argumentExpectations;
    }

    /**
     * Returns a closure to verify that the method prophecy was called at least a number of times.
     *
     * @param int $expected The number of times the method prophecy is expected to be called.
     *
     * @return \Closure A closure to add to the post conditions of the method prophecy.
     */
    public function atLeastTimes($expected)
    {
        return function () use ($expected) {
            if ($this->actualCallCount < $expected) {
                throw new AssertionFailedError(
                    $this->failedCallCountExpectationMessage($expected, $this->actualCallCount)
                );
            }
        };
    }

    /**
     * Sets the method prophecy return value.
     *
     * @param callable $returnValue A callable that will produce, taking as input the actual call arguments, the
     *                              return value of the prophesized method.
     *
     * @return void
     */
    public function setReturnValue($returnValue)
    {
        $this->returnValue = $returnValue;
    }

    /**
     * Returns the name of the method prophesized by the object, w/o the class.
     *
     * @return string The name of the method prophesized by the object, w/o the class.
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Verifies the actual call arguments against the expected ones.
     *
     * @param array<mixed> $actualArgs The actual call arguments.
     *
     * @throws AssertionFailedError If the actual call arguments do not match the expected ones.
     *
     * @return void
     */
    public function verifyArgsExpectation(array $actualArgs = [])
    {
        $expectedArgs = $this->expectedArguments;
        $actualCount  = count($actualArgs);
        // Do this to avoid `false` from `isset` when the expected value is `null`.
        $expectedKeys = array_keys($expectedArgs);

        for ($i = 0; $i < $actualCount; $i ++) {
            if (isset($expectedKeys[ $i ])) {
                $expectation = $expectedArgs[ $i ];
                try {
                    if ($expectation instanceof ArgInterface) {
                        $this->verificationStopped = $expectation->stopVerification();
                        Assert::assertTrue($expectation->verify($actualArgs[$i]), $expectation->getFailureMessage());
                        continue;
                    }

                    Assert::assertEquals($expectedArgs[ $i ], $actualArgs[ $i ]);
                } catch (AssertionFailedError $e) {
                    $message = $this->failedArgumentExpectationMessage($expectedArgs, $actualArgs, $i);
                    throw new ExpectationFailedException($message);
                }

                if ($this->verificationStopped) {
                    break;
                }
            }
        }
    }

    /**
     * Builds and returns the failure message for an argument expectation.
     *
     * @param array<mixed> $expectedArgs The method call expected arguments.
     * @param array<mixed> $callArgs     The method call actual arguments.
     * @param int          $failIndex    The index of the call argument whose expectation failed.
     *
     * @return string The failure message for the method argument expectation.
     */
    protected function failedArgumentExpectationMessage(array $expectedArgs, array $callArgs, $failIndex)
    {
        return sprintf(
            "Method \033[96m%s\033[0m expected to be called with arguments:"
            . "\n\n%s\n\nGot instead:\n\n%s\n",
            $this->getFQAName(),
            $this->formatArgsForOutput($expectedArgs, $failIndex, true),
            $this->formatArgsForOutput($callArgs, $failIndex, false)
        );
    }

    /**
     * Returns the fully-qualified, including the class, name of the method prophesized.
     *
     * @return string The fully-qualified, including the class, name of the method prophesized.
     */
    public function getFQAName()
    {
        return $this->class . '::' . $this->name;
    }

    /**
     * Formats an array of arguments to be printed in the context of an error output.
     *
     * @param array<mixed> $args             The arguments to print in the context of the error output.
     * @param int          $failIndex        The index of the failing argument expectation in the array of arguments.
     * @param bool         $expectedOrActual Whether to format for an argument expectation of actual value.
     *
     * @return string The formatted arguments output string.
     */
    protected function formatArgsForOutput(array $args, $failIndex, $expectedOrActual)
    {
        $output = [];
        $outputLineTemplate = '    [%d]  =>  %s';
        foreach ($args as $k => $arg) {
            if ($arg instanceof ArgInterface) {
                $output[] = sprintf($outputLineTemplate, $k, $arg->getFailureMessage());
                continue;
            }

            $output[] = sprintf($outputLineTemplate, $k, $this->formatRawArg($arg));
        }

        $color = $expectedOrActual ? 96 : 95;

        foreach ($output as $i => &$outputLine) {
            if ($i === $failIndex) {
                $outputLine = sprintf("\033[%dm%s\033[0m", $color, $outputLine);
            }
        }

        return implode(PHP_EOL, $output);
    }

    /**
     * Builds and returns the return value of the method prophecy.
     *
     * @param array<mixed> $args The input arguments to build the return value from.
     *
     * @return mixed|null The return value or `null` if no return value was set.
     */
    public function buildReturnValue(array $args = [])
    {
        if (is_callable($this->returnValue)) {
            return call_user_func($this->returnValue, ...$args);
        }

        return $this->returnValue;
    }

    /**
     * Sets the method prophecy expected call count.
     *
     * @param int|callable $expectedCallCount The expected method count or a callable to verify the calls.
     *
     * @return void
     */
    public function setExpectedCallCount($expectedCallCount)
    {
        if (is_callable($expectedCallCount)) {
            $expectedCallCount = (int)$expectedCallCount();
        }

        $this->expectedCallCount = (int) $expectedCallCount;
    }

    /**
     * Verifies an expected method call count expectation.
     *
     * @return void
     *
     * @throws AssertionFailedError
     */
    public function verifyExpectedCallCount()
    {
        $this->actualCallCount++;

        if ($this->expectedCallCount === null) {
            return;
        }

        if (is_callable($this->expectedCallCount)) {
            Assert::assertTrue(call_user_func($this->expectedCallCount, $this->actualCallCount));
        }

        if ($this->actualCallCount !== $this->expectedCallCount) {
            throw new AssertionFailedError(
                $this->failedCallCountExpectationMessage($this->expectedCallCount, $this->actualCallCount)
            );
        }
    }

    /**
     * Returns the message that will details the failed expectation for a method call count.
     *
     * @param int $expectedCalls The number of expected calls.
     * @param int $actualCalls The number of calls actually received.
     *
     * @return string The formatted error message.
     */
    protected function failedCallCountExpectationMessage($expectedCalls, $actualCalls)
    {
        return sprintf(
            "Method \033[96m%s\033[0m expected to be called \033[96m%d\033[0m times," .
            " called instead \033[95m%d\033[0m times.",
            $this->getFQAName(),
            $expectedCalls,
            $actualCalls
        );
    }

    /**
     * Adds a post condition to the method prophecy, verified when the `assertPostConditions` method is called.
     *
     * @param \Closure $postCondition The post condition to verify.
     *
     * @return void
     */
    protected function addPostCondition(\Closure $postCondition)
    {
        $this->postConditions[]  = $postCondition;
    }

    /**
     * Asserts the method prophecy post conditions.
     *
     * @return void
     */
    public function assertPostConditions()
    {
        if (count($this->postConditions)) {
            foreach ($this->postConditions as $postCondition) {
                $postCondition();
            }
        }
    }

    /**
     * Sets a post condition on the method prophecy to ensure the prophecy method has been called at least times.
     *
     * @param int $times The number of times the prophecy method should be called as a minimum.
     *
     * @return void
     */
    public function shouldBeCalledAtLeastTimes($times)
    {
        $this->addPostCondition($this->atLeastTimes($times));
    }

    /**
     * Formats an argument to be printed in the error output.
     *
     * @param mixed|null $arg The argument to format for output.
     *
     * @return string The argument, formatted for the error output.
     */
    protected function formatRawArg($arg = null)
    {
        if ($arg === null) {
            return 'null';
        }

        if (is_object($arg)) {
            return method_exists($arg, '__toString')
                ? $arg->__toString()
                : sprintf("Instance of '%s'", get_class($arg));
        }

        return print_r($arg, true);
    }
}

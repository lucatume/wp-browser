<?php
/**
 * Represents an argument expectation.
 *
 * @package tad\WPBrowser\StubProphecy
 */

namespace tad\WPBrowser\StubProphecy;

use function tad\WPBrowser\isRegex;

/**
 * Class Arg
 *
 * @package tad\WPBrowser\StubProphecy
 */
class Arg implements ArgInterface
{
    /**
     * The callback to execute to check the argument expectation.
     * @var callable
     */
    protected $check;

    /**
     * The callback to execute to format the expectation error.
     * @var callable|null
     */
    protected $onFail;

    /**
     * The actual value received as input to verify the expectation.
     * @var mixed
     */
    protected $actualInput;

    /**
     * The exception thrown by the check callable, if any.
     * @var \Exception|null
     */
    protected $checkException;

    /**
     * Arg constructor.
     *
     * @param callable $check The callable that will be used to check the argument. It should return a boolean or throw
     *                        an Exception that will be formatted and thrown again.
     * @param callable|null $onFail The callable that will be used to format the error message if the argument
     *                              expectation is fails.
     */
    public function __construct(callable $check, callable $onFail = null)
    {
        $this->check  = $check;
        $this->onFail = $onFail;
    }

    /**
     * Returns an argument expectation for anything.
     *
     * @return Arg The build argument expectation.
     */
    public static function any()
    {
        return new self(
            static function () {
                return true;
            },
            static function ($input) {
                $inputType = get_class($input);

                return "Expected anything, got '{$inputType}' instead.";
            }
        );
    }

    /**
     * Returns an argument expectation that will be verified with a closure.
     *
     * The failure message will be the message of the exception thrown by the closure.
     *
     * @param callable $callback The callback that should be used to assert the argument expectation. It should return
     *                           a boolean value and throw a message on failure.
     *
     * @return Arg The build argument expectation.
     */
    public static function that(callable $callback)
    {
        $argExpectation = new self($callback);
        $argExpectation->setOnFail(static function () use ($argExpectation) {
            return $argExpectation->checkException instanceof \Exception ?
                $argExpectation->checkException->getMessage()
                : 'Argument was not as expected';
        });

        return $argExpectation;
    }

    /**
     * Builds and returns an argument expected to contain a string.
     *
     * @param string $string The string to check.
     *
     * @return Arg The built argument expectation.
     */
    public static function containingString($string)
    {
        return new self(
            function ($input) use ($string) {
                if (isRegex($string)) {
                    return (bool) preg_match($string, $input);
                }

                return str_contains($input, $string);
            },
            static function ($input) use ($string) {
                return "Failed asserting that '{$input}' contains '{$string}'.";
            }
        );
    }

    /**
     * {@inheritDoc}
     */
    public function verify($actual)
    {
        $this->actualInput = $actual;

        try {
            $result = call_user_func($this->check, $actual);

            if (null === $result) {
                // No news, good news.
                return true;
            }

            return $result;
        } catch (\Exception $checkException) {
            $this->checkException = $checkException;
            return false;
        }
    }

    /**
     * {@inheritDoc}
     *
     * @throws \RuntimeException If the onFail handler is not set.
     */
    public function getFailureMessage()
    {
        if (!is_callable($this->onFail)) {
            throw new \RuntimeException('onFail handler not set');
        }

        return call_user_func($this->onFail, $this->actualInput, $this->checkException);
    }

    /**
     * Returns a Closure that will serve to verify an input argument of a prophesized method matches an expected type.
     *
     * @param string $type The argument expected type; use `string`, `array`, `int`, `float`, `bool`, `numeric` for the
     *                     respective primitive, use a fully-qualified class name to check for a specific class type.
     *
     * @return Arg An argument expectation that will verify the expectation and format the output error.
     *
     * @throws \RuntimeException If the type results in a missing `is_<type>` function.
     */
    public static function type($type)
    {
        if (in_array($type, [ 'string', 'array', 'bool', 'int', 'float', 'resource' ])) {
            return new self(
                static function ($input) use ($type) {
                    $function = "is_{$type}";

                    if (!is_callable($function)) {
                        throw new \RuntimeException($function . ' is not callable');
                    }

                    return call_user_func($function, $input);
                },
                static function () use ($type) {
                    return "Expected argument of type '{$type}'.";
                }
            );
        }

        return new self(
            static function ($input) use ($type) {
                if (trait_exists($type)) {
                    return in_array($type, class_uses($type));
                }
                if (interface_exists($type)) {
                    return in_array($type, class_implements($type), true);
                }

                return is_a($input, $type);
            },
            static function ($input) use ($type) {
                $inputType = is_string($input) ? $input : get_class($input);
                return "Expected object of type '{$type}', got '{$inputType}' instead.";
            }
        );
    }

    /**
     * Returns a "cetera" arguments that will match any remaining parameters in the method signature.
     *
     * It's the equivalent of using `any` multiple times.
     *
     * @return CeteraArg An argument that will match the rest of the method paremeters.
     */
    public static function cetera()
    {
        return new CeteraArg();
    }

    /**
     * {@inheritDoc}
     */
    public function stopVerification()
    {
        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function appliesToFollowing()
    {
        return false;
    }

    /**
     * Sets the argument expectation failure message callback.
     *
     * @param callable $onFail The failure message callback.
     *
     * @return void
     */
    public function setOnFail(callable $onFail)
    {
        $this->onFail = $onFail;
    }
}

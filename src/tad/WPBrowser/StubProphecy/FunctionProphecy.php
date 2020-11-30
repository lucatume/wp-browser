<?php
/**
 * Provides methods to build a prophecy to replace a function call and use a phpspec-like API.
 *
 * @package tad\WPBrowser\StubProphecy
 */

namespace tad\WPBrowser\StubProphecy;

use Patchwork\CallRerouting\Handle;
use function Patchwork\redefine;
use function Patchwork\restore;

/**
 * Class FunctionProphecy
 *
 * @package tad\WPBrowser\StubProphecy
 */
class FunctionProphecy
{
    /**
     * A list of the replaced functions.
     *
     * @var array<string>
     */
    protected static $replacedFunction = [];

    /**
     * An array cached of already processed function signatures.
     *
     * @var array<string,string>
     */
    protected static $functionSignatures = [];
    /**
     * An array of handles of functions redefined using Patchwork.
     *
     * @var array<Handle>
     */
    protected static $redefinitionHandles = [];

    /**
     * Replaces a function with a prophecy using the `uopz` extension, if available.
     *
     * @param string $name The fully-qualified name of the function to replace.
     * @param array<mixed|ArgInterface> $expectedArguments An array of either expected arguments or matchers for them.
     *
     * @return MethodProphecy A built function prophecy, to be revealed using the `reveal` method.
     *
     * @throws StubProphecyException
     */
    public static function __callStatic($name, array $expectedArguments = [])
    {
        return self::theFunction($name, $expectedArguments);
    }

    /**
     * Replaces a function with a prophecy.
     *
     * @param string $name The fully-qualified name of the function to replace.
     * @param array<mixed|ArgInterface> $expectedArguments An array of either expected arguments or matchers for them.
     *
     * @return MethodProphecy A built function prophecy, to be revealed using the `reveal` method.
     *
     * @throws StubProphecyException
     */
    public static function theFunction($name, array $expectedArguments = [])
    {
        $safeName = str_replace('\\', '_', trim($name, '\\'));
        $className = $safeName . '_function_prophecy';
        if (!class_exists($className)) {
            $classCode = str_replace(
                ['{{ class_name }}', '{{ safe_name }}', '{{ function_signature }}'],
                [$className, $safeName, self::getFunctionSignatureString($name)],
                'class {{ class_name }} { public function {{ safe_name }}({{ function_signature }}){} }'
            );
            eval($classCode);
        }
        $prophecy = new StubProphecy($className);

        $closure = static function (...$args) use ($prophecy, $safeName) {
            return $prophecy->reveal()->{$safeName}(...$args);
        };

        if (function_exists('uopz_set_return')) {
            uopz_set_return($name, $closure, true);
            static::$replacedFunction[] = $name;
        } else {
            static::$redefinitionHandles[] = redefine($name, $closure);
        }

        return $prophecy->{$safeName}(...$expectedArguments);
    }

    /**
     * Returns the function signature in string format.
     *
     * @param string $name The name of the function to build the signature for.
     * @return string The function signature, not including the opening and closing braces.
     * @throws \ReflectionException If the function cannot be reflected on.
     */
    protected static function getFunctionSignatureString($name)
    {
        if (isset(static::$functionSignatures[$name])) {
            return static::$functionSignatures[$name];
        }

        $reflectionFunction = new \ReflectionFunction($name);

        return implode(', ', array_map(static function (\ReflectionParameter $parameter) {
            $default = '';
            if ($parameter->isDefaultValueAvailable()) {
                $default = print_r($parameter->getDefaultValue(), true);
            } elseif ($parameter->isOptional()) {
                $default = 'null';
            }
            /** @noinspection PhpElementIsNotAvailableInCurrentPhpVersionInspection */
            return sprintf(
                '%s$%s%s',
                ($parameter->getType() instanceof \ReflectionType ? $parameter->getType()->__toString() . ' ' : ''),
                $parameter->name,
                ($default ? '= ' . $default : '')
            );
        }, $reflectionFunction->getParameters()));
    }

    /**
     * Resets all the function replacements.
     *
     * @return void
     */
    public static function reset()
    {
        if (function_exists('uopz_unset_return')) {
            foreach (static::$replacedFunction as $replacedFunction) {
                uopz_unset_return($replacedFunction);
            }
        } else {
            foreach (static::$redefinitionHandles as $handle) {
                restore($handle);
            }
        }

        static::$replacedFunction = [];
        static::$redefinitionHandles = [];
    }
}

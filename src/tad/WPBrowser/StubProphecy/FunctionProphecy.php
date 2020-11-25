<?php
/**
 * Provides methods to build a prophecy to replace a function call and use a phpspec-like API.
 *
 * @package tad\WPBrowser\StubProphecy
 */

namespace tad\WPBrowser\StubProphecy;

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
     * Replaces a function with a prophecy using the `uopz` extension.
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
        return static::theFunction($name, $expectedArguments);
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
        $className = 'FunctionProphecy_' . md5($safeName . microtime());
        $classCode = str_replace(
            ['{{ class_name }}', '{{ safe_name }}', '{{ function_signature }}'],
            [$className, $safeName, self::getFunctionSignatureString($name)],
            'class {{ class_name }} { public function {{ safe_name }}({{ function_signature }}){} }'
        );
        eval($classCode);
        $prophecy = new StubProphecy($className);

        $closure = static function (...$args) use ($prophecy, $safeName) {
            return $prophecy->reveal()->{$safeName}(...$args);
        };

        uopz_set_return($name, $closure, true);
        static::$replacedFunction[] = $name;

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

        return implode(', ', array_map(static function (\ReflectionParameter $p) {
            return sprintf(
                '%s$%s%s',
                ($p->getType() instanceof \ReflectionType ? $p->getType()->__toString() . ' ' : ''),
                $p->name,
                ($p->isDefaultValueAvailable() ? ' = ' . print_r($p->getDefaultValue(), true) : '')
            );
        }, $reflectionFunction->getParameters()));
    }

    /**
     * Resets all the function replacements.
     *
     * @since TBD
     */
    public static function reset()
    {
        foreach (static::$replacedFunction as $replacedFunction) {
            uopz_unset_return($replacedFunction);
        }
    }
}

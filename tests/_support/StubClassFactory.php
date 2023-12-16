<?php

namespace lucatume\WPBrowser\Tests;

use Codeception\Stub;
use Exception;
use lucatume\WPBrowser\Utils\Property;
use ReflectionException;
use ReflectionMethod;

class StubClassFactory
{
    private static string $classTemplate = 'class %1$s extends %2$s
{
    public function __construct(%3$s)
    {
        $this->_stub = %4$s::connectInvocationMocker($this);
        %4$s::assertConstructorConditions("%1$s", func_get_args());
    }
}';
    private static array $stubParametersByClassName = [];
    private static array $constructorAssertions = [];

    public static function tearDown(): void
    {
        self::$stubParametersByClassName = [];
        self::$constructorAssertions = [];
    }

    /**
     * @throws Exception
     */
    public static function connectInvocationMocker(object $mock): void
    {
        $mockClassName = get_class($mock);
        [$class, $parameters] = self::$stubParametersByClassName[$mockClassName];
        $stub = Stub::makeEmpty($class, $parameters);
        Property::setPrivateProperties($mock, [
            '__phpunit_originalObject' => Property::readPrivate($stub, '__phpunit_originalObject'),
            '__phpunit_returnValueGeneration' => Property::readPrivate($stub, '__phpunit_returnValueGeneration'),
            '__phpunit_invocationMocker' => Property::readPrivate($stub, '__phpunit_invocationMocker'),
        ]);
        unset($stub);
    }

    public static function assertConstructorConditions(string $mockClassName, array $args): void
    {
        if (!isset(self::$constructorAssertions[$mockClassName])) {
            return;
        }
        self::$constructorAssertions[$mockClassName](...$args);
    }

    /**
     * @throws ReflectionException
     * @throws Exception
     */
    public static function makeEmptyClass(string $class, array $parameters): string
    {
        $classBasename = basename(str_replace('\\', '/', $class));
        $mockClassName = $classBasename . '_' . substr(md5(microtime()), 0, 8);
        $constructorStringDump = (new ReflectionMethod($class, '__construct'))->__toString();
        preg_match_all('/Parameter #\\d+ \\[ <(?:optional|required)> (?<parameter>.*) ]/u',
            $constructorStringDump,
            $matches);
        $constructorParams = '';
        if (!empty($matches)) {
            $constructorParams = implode(
                ', ',
                array_map(static function (string $p): string {
                    return str_replace('or NULL', '', $p);
                }, $matches['parameter'])
            );
        }

        if (isset($parameters['__construct'])) {
            self::$constructorAssertions[$mockClassName] = $parameters['__construct'];
            unset($parameters['__construct']);
        }

        $codeceptionStub = Stub::makeEmpty($class, $parameters);
        $classCode = sprintf(self::$classTemplate,
            $mockClassName,
            get_class($codeceptionStub),
            $constructorParams,
            self::class);
        unset($codeceptionStub);
        eval($classCode);

        self::$stubParametersByClassName[$mockClassName] = [$class, $parameters];

        return $mockClassName;
    }
}

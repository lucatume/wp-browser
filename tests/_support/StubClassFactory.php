<?php

namespace lucatume\WPBrowser\Tests;

use Codeception\Stub;
use Exception;
use lucatume\WPBrowser\Utils\Property;
use PHPUnit\Runner\Version as PHPUnitVersion;
use ReflectionException;
use ReflectionMethod;

class StubClassFactory
{
    private static string $classTemplatePhpUnitLt10 = 'class %1$s extends %2$s
{
    public function __construct(%3$s)
    {
        %4$s::connectToStub($this, true);
        %4$s::assertConstructorConditions("%1$s", func_get_args());
        %4$s::setMockForClassName("%1$s", $this);
    }
}';
    private static string $classTemplatePhpUnitEq10 = 'class %1$s extends %2$s
{
    public function __construct(%3$s)
    {
        %4$s::connectToStub($this, false);
        %4$s::assertConstructorConditions("%1$s", func_get_args());
        %4$s::setMockForClassName("%1$s", $this);
    }
}';
    private static string $classTemplatePhpUnitGt10 = 'class %1$s extends %2$s
{
    use \PHPUnit\Framework\MockObject\StubApi;
    
    public function __construct(%3$s)
    {
        $this->__phpunit_state = %4$s::getPHPUnitStateObject("%1$s");
        %4$s::assertConstructorConditions("%1$s", func_get_args());
        %4$s::setMockForClassName("%1$s", $this);
    }
}';
    /**
     * @var array<string,mixed>
     */
    private static array $constructorAssertions = [];

    /**
     * @var array<string,object>
     */
    private static mixed $stubByClassName = [];
    /**
     * @var array<string,object>
     */
    private static array $mockByClassName = [];
    /**
     * @var<string,array{0:string,1:array<string,mixed>}>
     */
    private static array $stubParametersByClassName = [];

    public static function setMockForClassName(string $mockClassName, object $mock): void
    {
        self::$mockByClassName[$mockClassName] = $mock;
    }

    public static function tearDown(): void
    {
        self::$stubByClassName = [];
        self::$constructorAssertions = [];
        self::$mockByClassName = [];
    }

    public static function connectToStub(object $mock, bool $includeOriginalObject): void{
        $mockClassName = get_class($mock);
        [$class, $parameters] = self::$stubParametersByClassName[$mockClassName];
        $stub = Stub::makeEmpty($class, $parameters);
        if($includeOriginalObject){
            Property::setPrivateProperties($mock, [
                '__phpunit_originalObject' => Property::readPrivate($stub, '__phpunit_originalObject'),
                '__phpunit_returnValueGeneration' => Property::readPrivate($stub, '__phpunit_returnValueGeneration'),
                '__phpunit_invocationMocker' => Property::readPrivate($stub, '__phpunit_invocationMocker'),
            ]);
        } else {
            Property::setPrivateProperties($mock, [
                '__phpunit_returnValueGeneration' => Property::readPrivate($stub, '__phpunit_returnValueGeneration'),
                '__phpunit_invocationMocker' => Property::readPrivate($stub, '__phpunit_invocationMocker'),
            ]);
        }
        unset($stub);
    }

    /**
     * @throws ReflectionException
     */
    public static function getPHPUnitStateObject(string $mockClassName): object
    {
        $value = Property::readPrivate(self::$stubByClassName[$mockClassName], '__phpunit_state');

        if (!is_object($value)) {
            throw new ReflectionException('No PHPUnit state object found for ' . $mockClassName);
        }

        return $value;
    }

    /**
     * @param array<mixed> $args
     */
    public static function assertConstructorConditions(string $mockClassName, array $args): void
    {
        if (!isset(self::$constructorAssertions[$mockClassName])) {
            return;
        }
        self::$constructorAssertions[$mockClassName](...$args);
    }

    /**
     * @param class-string $class
     * @param array<mixed> $parameters
     *
     * @throws Exception
     * @throws ReflectionException
     */
    public static function makeEmptyClass(string $class, array $parameters): string
    {
        $classBasename = basename(str_replace('\\', '/', $class));
        $mockClassName = $classBasename . '_' . substr(md5(microtime()), 0, 8);
        $constructorStringDump = (new ReflectionMethod($class, '__construct'))->__toString();
        preg_match_all(
            '/Parameter #\\d+ \\[ <(?:optional|required)> (?<parameter>.*) ]/u',
            $constructorStringDump,
            $matches
        );
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

        foreach ($parameters as &$value) {
            if ($value === '__itself') {
                $value = fn() => self::getMockByClassName($mockClassName);
            }
        }

        $codeceptionStub = Stub::makeEmpty($class, $parameters);
        $phpunitVersion = (int)PHPUnitVersion::series();
        if ($phpunitVersion < 10) {
            $classTemplate = self::$classTemplatePhpUnitLt10;
        } elseif ($phpunitVersion === 10) {
            $classTemplate = self::$classTemplatePhpUnitEq10;
        } else {
            $classTemplate = self::$classTemplatePhpUnitGt10;
        }

        $classCode = sprintf(
            $classTemplate,
            $mockClassName,
            get_class($codeceptionStub),
            $constructorParams,
            self::class
        );

        eval($classCode);

        self::$stubByClassName[$mockClassName] = $codeceptionStub;
        self::$stubParametersByClassName[$mockClassName] = [$class, $parameters];

        return $mockClassName;
    }

    /**
     * @param string $mockClassName
     */
    private static function getMockByClassName(string $mockClassName): object
    {
        return self::$mockByClassName[$mockClassName];
    }
}

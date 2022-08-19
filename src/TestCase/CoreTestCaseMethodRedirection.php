<?php

namespace lucatume\WPBrowser\TestCase;

use ReflectionMethod;
use WP_UnitTestCase;

trait CoreTestCaseMethodRedirection
{

    /**
     * @var array<string,ReflectionMethod>
     */
    private static array $coreTestCaseProtectedStaticApiMethods = [];

    /**
     * @var array<string,ReflectionMethod>
     */
    private array $coreTestCaseProtectedApiMethods = [];

    private ?WP_UnitTestCase $wpUnitTestCase = null;

    private static function getTestCaseStaticReflectionMethod(string $name): ReflectionMethod
    {
        if (!self::$coreTestCaseProtectedStaticApiMethods[$name] instanceof ReflectionMethod) {
            $reflectionMethod = new ReflectionMethod(WP_UnitTestCase::class, $name);
            $reflectionMethod->setAccessible(true);
            self::$coreTestCaseProtectedStaticApiMethods[$name] = $reflectionMethod;
        }

        return self::$coreTestCaseProtectedStaticApiMethods[$name];
    }

    private function getTestCaseReflectionMethod(string $name): ReflectionMethod
    {
        if (!$this->coreTestCaseProtectedApiMethods[$name] instanceof ReflectionMethod) {
            $reflectionMethod = new ReflectionMethod($this->getCoreTestCaseInstance(), $name);
            $reflectionMethod->setAccessible(true);
            $this->coreTestCaseProtectedApiMethods[$name] = $reflectionMethod;
        }

        return $this->coreTestCaseProtectedApiMethods[$name];
    }

    public function __call(string $name, array $arguments): mixed
    {
        return $this->invokeProtectedApiMethod($name, $arguments);
    }

    public static function __callStatic(string $name, array $arguments): mixed
    {
        return self::invokeProtectedStaticApiMethod($name, $arguments);
    }

    private static function invokeProtectedStaticApiMethod(string $name, array $arguments = []): mixed
    {
        return self::getTestCaseStaticReflectionMethod($name)->invoke(...$arguments);
    }

    private function invokeProtectedApiMethod(string $name, array $arguments = []): mixed
    {
        return $this->getTestCaseReflectionMethod($name)->invoke(...$arguments);
    }

    private function getCoreTestCaseInstance(): WP_UnitTestCase
    {
        if ($this->wpUnitTestCase === null) {
            $this->wpUnitTestCase = new class extends WP_UnitTestCase {
            };
        }

        return $this->wpUnitTestCase;
    }
}

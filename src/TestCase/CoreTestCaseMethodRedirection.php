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

    private static function getTestCaseStaticReflectionMethod(string $name): ReflectionMethod
    {
        if (!(isset(self::$coreTestCaseProtectedStaticApiMethods[$name]) && self::$coreTestCaseProtectedStaticApiMethods[$name] instanceof ReflectionMethod)) {
            $reflectionMethod = new ReflectionMethod(WP_UnitTestCase::class, $name);
            $reflectionMethod->setAccessible(true);
            self::$coreTestCaseProtectedStaticApiMethods[$name] = $reflectionMethod;
        }

        return self::$coreTestCaseProtectedStaticApiMethods[$name];
    }

    private function getTestCaseReflectionMethod(string $name): ReflectionMethod
    {
        if (!(isset($this->coreTestCaseProtectedApiMethods[$name]) && $this->coreTestCaseProtectedApiMethods[$name] instanceof ReflectionMethod)) {
            $reflectionMethod = new ReflectionMethod($this->getCoreTestCaseInstance(), $name);
            $reflectionMethod->setAccessible(true);
            $this->coreTestCaseProtectedApiMethods[$name] = $reflectionMethod;
        }

        return $this->coreTestCaseProtectedApiMethods[$name];
    }

    public function __call(string $name, array $arguments): mixed
    {
        codecept_debug("Calling {$name}");
        return $this->invokeProtectedApiMethod($name, $arguments);
    }

    public static function __callStatic(string $name, array $arguments): mixed
    {
        codecept_debug("Calling static {$name}");
        return self::invokeProtectedStaticApiMethod($name, $arguments);
    }

    private static function invokeProtectedStaticApiMethod(string $name, array $arguments = []): mixed
    {
        return self::getTestCaseStaticReflectionMethod($name)->invoke(null, ...$arguments);
    }

    private function invokeProtectedApiMethod(string $name, array $arguments = []): mixed
    {
        return $this->getTestCaseReflectionMethod($name)->invoke($this->getCoreTestCaseInstance(), ...$arguments);
    }

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        self::invokeProtectedStaticApiMethod('setUpBeforeClass');
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->invokeProtectedApiMethod('set_up');
    }

    protected function assertPreConditions(): void
    {
        parent::assertPreConditions();
        $this->invokeProtectedApiMethod('assert_pre_conditions');
    }

    protected function assertPostConditions(): void
    {
        parent::assertPostConditions();
        $this->invokeProtectedApiMethod('assert_post_conditions');
    }

    public function tearDown(): void
    {
        parent::tearDown();
        $this->invokeProtectedApiMethod('tear_down');
    }

    public static function tearDownAfterClass(): void
    {
        global $wpdb;
        $tables = $wpdb->get_results('SHOW TABLES');
        parent::tearDownAfterClass();
        self::invokeProtectedStaticApiMethod('tear_down_after_class');
    }
}

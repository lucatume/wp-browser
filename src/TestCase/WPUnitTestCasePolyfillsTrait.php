<?php

namespace lucatume\WPBrowser\TestCase;

use Codeception\PHPUnit\TestCase;

trait WPUnitTestCasePolyfillsTrait
{
    /**
     * @param array<mixed> $arguments
     */
    public function __call(string $name, array $arguments) //@phpstan-ignore-line cannot be type-hinted
    {
        return TestCase::$name(...$arguments);
    }

    /**
     * @param array<mixed> $arguments
     */
    public static function __callStatic(string $name, array $arguments) //@phpstan-ignore-line cannot be type-hinted
    {
        return TestCase::$name(...$arguments);
    }
}

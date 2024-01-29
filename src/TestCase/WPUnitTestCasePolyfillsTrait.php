<?php

namespace lucatume\WPBrowser\TestCase;

use Codeception\PHPUnit\TestCase;

trait WPUnitTestCasePolyfillsTrait
{
    protected function assertIsArray($actual, string $message = ''): void
    {
        TestCase::assertIsArray($actual, $message);
    }
}

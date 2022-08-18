<?php

namespace lucatume\WPBrowser\TestCase;

use Codeception\Test\Unit;
use WP_UnitTestCase;

class WPTestCase extends Unit
{
    private ?WP_UnitTestCase $wpUnitTestCase = null;

    private function testCase(): WP_UnitTestCase
    {
        if ($this->wpUnitTestCase === null) {
            $this->wpUnitTestCase = new class extends WP_UnitTestCase {
            };
        }

        return $this->wpUnitTestCase;
    }

    public function __call(string $name, array $arguments): mixed
    {
        return $this->testCase()->$name(...$arguments);
    }


    public static function __callStatic(string $name, array $arguments): mixed
    {
        return WP_UnitTestCase::$name(...$arguments);
    }
}

<?php


namespace lucatume\WPBrowser;

use lucatume\WPBrowser\Exceptions\InvalidArgumentException;
use lucatume\WPBrowser\Exceptions\RuntimeException;
use lucatume\WPBrowser\Utils\Memo;

function __test_useMemo_function(): int
{
    static $calls = 1;

    return $calls++;
}

function __test_useMemoFunctionWithDependencies(): int
{
    static $calls = 1;

    return $calls++;
}

class _UseMemoInvocableObject
{
    public function __invoke(): int
    {
        static $calls = 1;

        return $calls++;
    }
}

class functionsTest extends \Codeception\Test\Unit
{

    /**
     * @before
     */
    public function resetMemo(): void
    {
        Memo::reset();
    }

    public static function _test_useMemoStaticMethod(): int
    {
        static $calls = 1;

        return $calls++;
    }

    public function test_useMemo_with_function(): void
    {
        foreach (range(1, 3) as $k) {
            $this->assertEquals(1, useMemo(__NAMESPACE__ . '\__test_useMemo_function'));
        }
        foreach (range(2, 4) as $k) {
            $this->assertEquals($k, useMemo(__NAMESPACE__ . '\__test_useMemo_function', [$k]));
        }
    }

    public function test_useMemo_with_static_method(): void
    {
        foreach (range(1, 3) as $k) {
            $this->assertEquals(1, useMemo([__CLASS__, '_test_useMemoStaticMethod']));
        }
        foreach (range(2, 4) as $k) {
            $this->assertEquals($k, useMemo([__CLASS__, '_test_useMemoStaticMethod'], [$k]));
        }
    }

    public function useMemoInstanceMethod(): int
    {
        static $calls = 1;

        return $calls++;
    }

    public function test_useMemo_with_instance_method(): void
    {
        foreach (range(1, 3) as $k) {
            $this->assertEquals(1, useMemo([$this, 'useMemoInstanceMethod']));
        }
        foreach (range(2, 4) as $k) {
            $this->assertEquals($k, useMemo([$this, 'useMemoInstanceMethod'], [$k]));
        }
    }

    public function test_useMemo_with_closure(): void
    {
        $zorps = 0;
        $closure = function () use (&$zorps) {
            static $calls = 1;
            return $calls++;
        };

        foreach (range(1, 3) as $k) {
            $this->assertEquals(1, useMemo($closure));
        }
        foreach (range(2, 4) as $k) {
            $this->assertEquals($k, useMemo($closure, [$k]));
        }
    }

    public function test_useMemo_with_invocable_object(): void
    {
        foreach (range(1, 3) as $k) {
            $object = new _UseMemoInvocableObject();
            $this->assertEquals(1, useMemo($object));
        }
        foreach (range(2, 4) as $k) {
            $this->assertEquals($k, useMemo($object, [$k]));
        }
    }

    public function test_useMemoString_throws_if_result_not_string(): void
    {
        $this->expectException(RuntimeException::class);
        useMemoString(function () {
            return 1;
        });
    }

    public function test_useMemo_throws_if_callback_is_not_callable_string(): void
    {
        $this->expectException(InvalidArgumentException::class);
        useMemo('not_a_function');
    }

    public function test_useMemo_throws_if_callback_is_not_callable_array(): void
    {
        $this->expectException(InvalidArgumentException::class);
        useMemo([__CLASS__, 'not_a_method']);
    }
}

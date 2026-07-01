<?php

declare(strict_types=1);

namespace Unit\lucatume\WPBrowser\Utils;

use Closure;
use Codeception\Test\Unit;
use lucatume\WPBrowser\Utils\PackedClosure;
use lucatume\WPBrowser\Utils\PackerException;
use ReflectionClass;

class PackedClosureTest extends Unit
{
    public function test_get_closure_returns_the_wrapped_closure(): void
    {
        $closure = static function (): int {
            return 42;
        };
        $packed = new PackedClosure($closure);

        $this->assertSame($closure, $packed->getClosure());
    }

    public function test_invoke_forwards_arguments_and_returns_value(): void
    {
        $closure = static function (int $a, int $b): int {
            return $a + $b;
        };
        $packed = new PackedClosure($closure);

        $this->assertSame(7, $packed(3, 4));
    }

    public function test_serialize_unserialize_roundtrip_preserves_simple_closure(): void
    {
        $closure = static function (): string {
            return 'hello';
        };
        $packed = new PackedClosure($closure);

        $restored = unserialize(serialize($packed));

        $this->assertInstanceOf(PackedClosure::class, $restored);
        $this->assertInstanceOf(Closure::class, $restored->getClosure());
        $this->assertSame('hello', $restored());
    }

    public function test_serialize_unserialize_roundtrip_preserves_use_context(): void
    {
        $value = 'preserved';
        $closure = static function () use ($value): string {
            return $value;
        };
        $packed = new PackedClosure($closure);

        $restored = unserialize(serialize($packed));

        $this->assertSame('preserved', $restored());
    }

    public function test_serialize_unserialize_roundtrip_preserves_recursive_closure(): void
    {
        $factorial = static function (int $n) use (&$factorial): int {
            return $n <= 1 ? 1 : $n * $factorial($n - 1);
        };
        $packed = new PackedClosure($factorial);

        $restored = unserialize(serialize($packed));

        $this->assertSame(120, $restored(5));
    }

    public function test_unserialize_throws_when_payload_is_missing(): void
    {
        $instance = (new ReflectionClass(PackedClosure::class))->newInstanceWithoutConstructor();

        $this->expectException(PackerException::class);
        /** @phpstan-ignore-next-line - testing the runtime guard with a malformed payload */
        $instance->__unserialize([]);
    }

    public function test_unserialize_throws_when_packed_value_is_not_a_closure(): void
    {
        $instance = (new ReflectionClass(PackedClosure::class))->newInstanceWithoutConstructor();

        $this->expectException(PackerException::class);
        $instance->__unserialize(['packed' => '{"type":"integer","value":7}']);
    }
}

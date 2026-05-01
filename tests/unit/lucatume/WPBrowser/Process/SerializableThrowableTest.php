<?php

namespace lucatume\WPBrowser\Process;

use Codeception\Test\Unit;
use Codeception\Util\StackTraceFilter;
use Exception;
use Generator;
use lucatume\WPBrowser\Utils\PackedClosure;
use RuntimeException;
use tad\Codeception\SnapshotAssertions\SnapshotAssertions;
use Throwable;

/**
 * @group fast
 */
class SerializableThrowableTest extends Unit
{
    use SnapshotAssertions;

    public function throwableProvider(): Generator
    {
        yield 'Exception' => [new Exception('test')];
    }

    /**
     * It should allow serializing a throwable
     *
     * @test
     * @dataProvider throwableProvider
     */
    public function should_allow_serializing_a_throwable(Throwable $throwable): void
    {
        $serializableThrowable = new SerializableThrowable($throwable);
        $serialized = serialize($serializableThrowable);
        $unserialized = unserialize($serialized)->getThrowable();

        $this->assertEquals($throwable->getMessage(), $unserialized->getMessage());
        $this->assertEquals($throwable->getCode(), $unserialized->getCode());
        $this->assertEquals($throwable->getFile(), $unserialized->getFile());
        $this->assertEquals($throwable->getLine(), $unserialized->getLine());
        $this->assertEquals($throwable->getPrevious(), $unserialized->getPrevious());
    }

    public static function throwingMethod(): void
    {
        throw new RuntimeException('test');
    }

    private function should_pretty_print_serialized_closure_entry(): string
    {
        $throwing = static function () {
            $set = 'foo';
            $bar = 'bar';

            self::throwingMethod();
        };
        $s = serialize(new PackedClosure($throwing));
        try {
            unserialize($s)();
        } catch (RuntimeException $t) {
            $throwable = $t;
        }

        $serializableThrowable = new SerializableThrowable($throwable);
        $serializableThrowable->colorize(false);
        $serialized = serialize($serializableThrowable);
        $unserialized = unserialize($serialized)->getThrowable(SerializableThrowable::RELATIVE_PAHTNAMES);
        $trace = StackTraceFilter::getFilteredStackTrace($unserialized);
        $traceString = is_string($trace) ? $trace : '';

        // Strip upstream runner frames (Symfony console, codecept bin, PHPUnit).
        // These vary across PHP/Xdebug/opcache setups and are not what this test
        // validates — the subject is the closure pretty-print in the middle frames.
        $lines = array_filter(
            explode("\n", $traceString),
            static fn (string $line): bool => !str_starts_with($line, '/vendor/')
                && !str_starts_with($line, '/bin/'),
        );

        return trim(implode("\n", $lines));
    }

    /**
     * It should pretty print serialized Closure entry on PHP 8.0
     *
     * @test
     */
    public function should_pretty_print_serialized_closure_entry_on_php_8_0(): void
    {
        if (!(PHP_VERSION_ID >= 80000 && PHP_VERSION_ID < 80100)) {
            $this->markTestSkipped('PHP 8.0 required.');
        }

        $print = $this->should_pretty_print_serialized_closure_entry();

        codecept_debug($print);

        $this->assertMatchesCodeSnapshot($print);
    }

    /**
     * It should pretty print serialized Closure entry on PHP 8.1
     *
     * @test
     */
    public function should_pretty_print_serialized_closure_entry_on_php_8_1(): void
    {
        if (!(PHP_VERSION_ID >= 80100 && PHP_VERSION_ID < 80200)) {
            $this->markTestSkipped('PHP 8.1 required.');
        }

        $this->assertMatchesCodeSnapshot($this->should_pretty_print_serialized_closure_entry());
    }

    /**
     * It should pretty print serialized Closure entry on PHP 8.2
     *
     * @test
     */
    public function should_pretty_print_serialized_closure_entry_on_php_8_2(): void
    {
        if (!(PHP_VERSION_ID >= 80200 && PHP_VERSION_ID < 80300)) {
            $this->markTestSkipped('PHP 8.2 required.');
        }

        $this->assertMatchesCodeSnapshot($this->should_pretty_print_serialized_closure_entry());
    }
}

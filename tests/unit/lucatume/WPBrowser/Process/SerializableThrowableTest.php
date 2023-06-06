<?php

namespace lucatume\WPBrowser\Process;

use Codeception\Test\Unit;
use Codeception\Util\StackTraceFilter;
use Exception;
use Generator;
use lucatume\WPBrowser\Opis\Closure\SerializableClosure;
use RuntimeException;
use tad\Codeception\SnapshotAssertions\SnapshotAssertions;
use Throwable;

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

    /**
     * It should pretty print serialized closure entry
     *
     * @test
     */
    public function should_pretty_print_serialized_closure_entry(): void
    {
        $throwing = static function () {
            $set = 'foo';
            $bar = 'bar';

            self::throwingMethod();
        };
        $s = serialize(new SerializableClosure($throwing));
        try {
            unserialize($s)();
        } catch (RuntimeException $t) {
            $throwable = $t;
        }

        $serializableThrowable = new SerializableThrowable($throwable);
        $serialized = serialize($serializableThrowable);
        $unserialized = unserialize($serialized)->getThrowable(SerializableThrowable::RELATIVE_PAHTNAMES);
        $trace = StackTraceFilter::getFilteredStackTrace($unserialized);

        $this->assertMatchesCodeSnapshot($trace);
    }
}

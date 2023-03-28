<?php

namespace lucatume\WPBrowser\Process;

use lucatume\WPBrowser\Utils\Property;
use Opis\Closure\SerializableClosure;
use tad\Codeception\SnapshotAssertions\SnapshotAssertions;
use Throwable;

class SerializableThrowableTest extends \Codeception\Test\Unit
{
    use SnapshotAssertions;

    public function throwableProvider(): \Generator
    {
        yield 'Exception' => [new \Exception('test')];
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
        throw new \RuntimeException('test');
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
        } catch (\RuntimeException $t) {
            $throwable = $t;
        }

        $serializableThrowable = new SerializableThrowable($throwable);
        $serialized = serialize($serializableThrowable);
        $unserialized = unserialize($serialized)->getThrowable(SerializableThrowable::RELATIVE_PAHTNAMES);
        $trace = $unserialized->getTrace();
        codecept_debug($trace);

        $this->assertMatchesStringSnapshot($unserialized->getTraceAsString());
        $this->assertMatchesCodeSnapshot(var_export($unserialized->getTrace(), true));
    }
}

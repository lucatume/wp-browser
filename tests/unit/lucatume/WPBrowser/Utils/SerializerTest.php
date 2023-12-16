<?php

namespace lucatume\WPBrowser\Utils;

use Codeception\Test\Unit;
use Error;
use Exception;
use Generator;
use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\ExpectationFailedException;
use RuntimeException;
use SebastianBergmann\Comparator\ComparisonFailure;
use Serializable;
use Throwable;

class TestSerializableObject
{
    public string $foo = 'bar';
    public int $number = 23;

    public function __serialize(): array
    {
        return ['foo' => 'bar', 'number' => 23];
    }

    public function __unserialize(array $data): void
    {
        foreach ($data as $key => $value) {
            $this->{$key} = $value;
        }
    }
}

class SerializerTest extends Unit
{
    public function maybeUnserializeDataProvider(): Generator
    {
        $serializableObject = new TestSerializableObject();

        yield 'null' => [null, null];
        yield 'serialized null' => [serialize(null), null];
        yield 'empty string' => ['', ''];
        yield 'serialized empty string' => [serialize(''), ''];
        yield 'boolean true' => [true, true];
        yield 'serialized boolean true' => [serialize(true), true];
        yield 'boolean false' => [false, false];
        yield 'serialized boolean false' => [serialize(false), false];
        yield 'integer' => [23, 23];
        yield 'serialized integer' => [serialize(23), 23];
        yield 'float' => [23.89, 23.89];
        yield 'serialized float' => [serialize(23.89), 23.89];
        yield 'string' => ['foo-bar', 'foo-bar'];
        yield 'serialized string' => [serialize('foo-bar'), 'foo-bar'];
        yield 'associative array' => [
            ['string' => 'foo-bar', 'number' => 23, 'nil' => null],
            ['string' => 'foo-bar', 'number' => 23, 'nil' => null]
        ];
        yield 'serialized associative array' => [
            serialize(['string' => 'foo-bar', 'number' => 23, 'nil' => null]),
            ['string' => 'foo-bar', 'number' => 23, 'nil' => null]
        ];
        yield 'numeric array' => [
            ['foo-bar', 23, null],
            ['foo-bar', 23, null]
        ];
        yield 'serialized numeric array' => [
            serialize(['foo-bar', 23, null]),
            ['foo-bar', 23, null]
        ];
        yield 'object' => [
            (object)['string' => 'foo-bar', 'number' => 23, 'nil' => null],
            ['string' => 'foo-bar', 'number' => 23, 'nil' => null]
        ];
        yield 'serialized object' => [
            serialize((object)['string' => 'foo-bar', 'number' => 23, 'nil' => null]),
            ['string' => 'foo-bar', 'number' => 23, 'nil' => null]
        ];
        yield 'serializable object' => [
            $serializableObject,
            ['foo' => 'bar', 'number' => 23]
        ];
        yield 'serialized serializable object' => [
            serialize($serializableObject),
            ['foo' => 'bar', 'number' => 23]
        ];
    }

    /**
     * @dataProvider maybeUnserializeDataProvider
     */
    public function test_maybeUnserialize($input, $expected): void
    {
        $unserialized = Serializer::maybeUnserialize($input);

        if (is_object($unserialized)) {
            $this->assertEquals($expected, get_object_vars($unserialized));
        } else {
            $this->assertEquals($expected, $unserialized);
        }
    }

    public function isSerializedDataProvider(): Generator
    {
        $serializableObject = new TestSerializableObject();

        yield 'null' => [null, false];
        yield 'serialized null' => [serialize(null), true];
        yield 'empty string' => ['', false];
        yield 'boolean true' => [true, false];
        yield 'serialized boolean true' => [serialize(true), true];
        yield 'boolean false' => [false, false];
        yield 'serialized boolean false' => [serialize(false), true];
        yield 'integer' => [23, false];
        yield 'serialized integer' => [serialize(23), true];
        yield 'float' => [23.89, false];
        yield 'serialized float' => [serialize(23.89), true];
        yield 'string' => ['foo-bar', false];
        yield 'serialized string' => [serialize('foo-bar'), true];
        yield 'associative array' => [
            ['string' => 'foo-bar', 'number' => 23, 'nil' => null],
            false
        ];
        yield 'serialized associative array' => [
            serialize(['string' => 'foo-bar', 'number' => 23, 'nil' => null]),
            true
        ];
        yield 'numeric array' => [['foo-bar', 23, null], false];
        yield 'serialized numeric array' => [serialize(['foo-bar', 23, null]), true];
        yield 'object' => [(object)['string' => 'foo-bar', 'number' => 23, 'nil' => null], false];
        yield 'serialized object' => [serialize((object)['string' => 'foo-bar', 'number' => 23, 'nil' => null]), true];
        yield 'serializable object' => [$serializableObject, false];
        yield 'serialized serializable object' => [serialize($serializableObject), true];
    }

    /**
     * @dataProvider isSerializedDataProvider
     */
    public function test_isSerialized(mixed $input, bool $expected): void
    {
        $this->assertEquals($expected, Serializer::isSerialized($input));
    }

    public function maybeSerializeDataProvider(): Generator
    {
        $serializableObject = new TestSerializableObject();

        yield 'null' => [null, serialize(null)];
        yield 'serialized null' => [serialize(null), serialize(null)];
        yield 'empty string' => ['', ''];
        yield 'serialized empty string' => [serialize(''), serialize('')];
        yield 'boolean true' => [true, true];
        yield 'serialized boolean true' => [serialize(true), serialize(true)];
        yield 'boolean false' => [false, false];
        yield 'serialized boolean false' => [serialize(false), serialize(false)];
        yield 'integer' => [23, 23];
        yield 'serialized integer' => [serialize(23), serialize(23)];
        yield 'float' => [23.89, 23.89];
        yield 'serialized float' => [serialize(23.89), serialize(23.89)];
        yield 'string' => ['foo-bar', 'foo-bar'];
        yield 'serialized string' => [serialize('foo-bar'), serialize('foo-bar')];
        yield 'associative array' => [
            ['string' => 'foo-bar', 'number' => 23, 'nil' => null],
            serialize(['string' => 'foo-bar', 'number' => 23, 'nil' => null])
        ];
        yield 'serialized associative array' => [
            serialize(['string' => 'foo-bar', 'number' => 23, 'nil' => null]),
            serialize(['string' => 'foo-bar', 'number' => 23, 'nil' => null]),
        ];
        yield 'numeric array' => [
            ['foo-bar', 23, null],
            serialize(['foo-bar', 23, null]),
        ];
        yield 'serialized numeric array' => [
            serialize(['foo-bar', 23, null]),
            serialize(['foo-bar', 23, null]),
        ];
        yield 'object' => [
            (object)['string' => 'foo-bar', 'number' => 23, 'nil' => null],
            serialize((object)['string' => 'foo-bar', 'number' => 23, 'nil' => null]),
        ];
        yield 'serialized object' => [
            serialize((object)['string' => 'foo-bar', 'number' => 23, 'nil' => null]),
            serialize((object)['string' => 'foo-bar', 'number' => 23, 'nil' => null]),
        ];
        yield 'serializable object' => [
            $serializableObject,
            serialize($serializableObject),
        ];
        yield 'serialized serializable object' => [
            serialize($serializableObject),
            serialize($serializableObject),
        ];
    }

    /**
     * @dataProvider maybeSerializeDataProvider
     */
    public function test_maybeSerialize(mixed $input, mixed $expected): void
    {
        $this->assertEquals($expected, Serializer::maybeSerialize($input));
    }

    public function throwableDataProvider(): Generator
    {
        yield 'Error' => [new Error('Error message')];
        yield 'Exception' => [new Exception('Exception message')];
        yield 'RuntimeException' => [new RuntimeException('Runtime exception message')];
        yield 'ExpectationFailedException' => [
            new ExpectationFailedException(
                'Expectation failed exception message',
                new ComparisonFailure('foo', 'bar', 'foo', 'bar')
            )
        ];
        yield 'AssertionFailedError' => [new AssertionFailedError('Assertion failed error message')];
        yield 'ExpectationFailedException w/o Comparison Failure' => [
            new ExpectationFailedException('Expectation failed exception message')
        ];
        yield 'ExpectationFailedException w/ previous' => [
            new ExpectationFailedException(
                'Expectation failed exception message',
                new ComparisonFailure('foo', 'bar', 'foo', 'bar'),
                new Exception('Previous')
            )
        ];
    }

    /**
     * It should make throwable serializable
     *
     * @test
     * @dataProvider throwableDataProvider
     */
    public function should_make_throwable_serializable(Throwable $throwable): void
    {
        $serializableThrowable = Serializer::makeThrowableSerializable($throwable);
        $this->assertIsString(serialize($serializableThrowable));
    }
}

<?php


namespace Unit\lucatume\WPBrowser\Utils;

use Codeception\Test\Unit;
use lucatume\WPBrowser\Utils\Arr;
use stdClass;

class ArrTest extends Unit
{
    public function searchWithCallbackDataProvider(): array
    {
        return [
            'empty haystack, return true isNeedle' => [
                static function (): bool {
                    return true;
                },
                [],
                false
            ],
            'empty haystack, return false isNeedle' => [
                static function (): bool {
                    return false;
                },
                [],
                false
            ],
            'return false isNeedle' => [
                static function (): bool {
                    return false;
                },
                [1, 2, 3],
                false
            ],
            'isNeedle is true for first item' => [
                static function (int $item): bool {
                    return $item === 1;
                },
                [1, 2, 3],
                0
            ],
            'isNeedle true for 3rd and 4th argument' => [
                static function (int $item, int $key): bool {
                    return $item === 3 || $key === 3;
                },
                [1, 2, 3, 4],
                2
            ],
        ];
    }

    /**
     * @dataProvider searchWithCallbackDataProvider
     * @param int|string|false $expected
     */
    public function testSearchWithCallback(callable $isNeedle, array $hastack, $expected): void
    {
        $actual = Arr::searchWithCallback($isNeedle, $hastack);
        $this->assertEquals($expected, $actual);
    }

    public function firstFromDataProvider(): array
    {
        return [
            'empty' => [
                [],
                null,
                null
            ],
            'empty, default value is 23' => [
                [],
                23,
                23
            ],
            'object value' => [
                new stdClass(),
                null,
                null
            ],
            'array of numbers' => [
                [1, 2, 3],
                null,
                1
            ],
            'array of numbers, default value is 23' => [
                [1, 2, 3],
                23,
                1
            ]
        ];
    }

    /**
     * @dataProvider firstFromDataProvider
     * @param mixed $value
     * @param mixed $default
     * @param mixed $expected
     */
    public function test_firstFrom($value, $default, $expected): void
    {
        $actual = Arr::firstFrom($value, $default);
        $this->assertEquals($expected, $actual);
    }

    public function hasShapeDataProvider(): array
    {
        return [
            'empty array, empty shapes' => [
                [],
                true,
                []
            ],
            'empty array, 3 numbers shapes' => [
                [],
                false,
                ['int', 'int', 'int']
            ],
            'array has wrong shape' => [
                [1, 2, 3],
                false,
                ['int', 'int', 'string']
            ],
            'array has 3 objects shape' => [
                [new stdClass, new stdClass, new stdClass],
                true,
                ['stdClass', 'stdClass', 'stdClass']
            ],
            'array has 3 objects shape, misses 1' => [
                [new stdClass, new stdClass],
                false,
                ['stdClass', 'stdClass', 'stdClass']
            ],
            'array has mixed shape' => [
                [new stdClass, 2, '3'],
                true,
                ['stdClass', 'int', 'string']
            ],
            'array has mixed shape, misses one' => [
                [new stdClass, 2],
                false,
                ['stdClass', 'int', 'string']
            ],
            'array has mixed shape, 3rd type is Closure' => [
                [new stdClass, 2, '3'],
                true,
                ['stdClass', 'int', function (string $value) : bool {
                    return $value === '3';
                }]
            ],
            'array has mixed shape, 3rd type is Closure, misses one' => [
                [new stdClass, 2],
                false,
                ['stdClass', 'int', function (string $value) : bool {
                    return $value === '3';
                }]
            ],
            'array has mixed shape with associative type' => [
                ['a' => new stdClass, 'b' => 2, 'c' => '3'],
                true,
                ['a' => 'stdClass', 'b' => 'int', 'c' => function (string $value) : bool {
                    return $value === '3';
                }]
            ],
            'array shape does not match mixed types' => [
                ['a' => new stdClass, 'b' => 2, 'c' => '3'],
                false,
                ['a' => 'stdClass', 'b' => 'int', 'c' => function (string $value) : bool {
                    return $value === '4';
                }]
            ],
            'array shape matches in different order' => [
                ['a' => new stdClass, 'b' => 2, 'c' => '3'],
                true,
                ['b' => 'int', 'c' => function (string $value) : bool {
                    return $value === '3';
                }, 'a' => 'stdClass']
            ],
        ];
    }

    /**
     * @dataProvider hasShapeDataProvider
     */
    public function test_hasShape(array $array, bool $expected, array $types): void
    {
        $actual = Arr::hasShape($array, $types);
        $this->assertEquals($expected, $actual);
    }

    public function containsOnlyDataProvider(): array
    {
        return [
            'empty' => [
                'array' => [],
                'type' => 'int',
                'expected' => true
            ],
            'array of ints' => [
                'array' => [1, 2, 3],
                'type' => 'int',
                'expected' => true
            ],
            'array of ints and strings' => [
                'array' => [1, 2, '3'],
                'type' => 'int',
                'expected' => false
            ],
            'array of objects' => [
                'array' => [new stdClass(), new stdClass()],
                'type' => 'stdClass',
                'expected' => true
            ],
            'array of objects and strings' => [
                'array' => [new stdClass(), '2'],
                'type' => 'stdClass',
                'expected' => false
            ],
            'array of integers, type is Closure' => [
                'array' => [1, 2, 3],
                'type' => function (int $value) {
                    return $value > 0;
                },
                'expected' => true
            ],
            'array of integers, type is Closure, no match' => [
                'array' => [1, 2, 3],
                'type' => function (int $value) {
                    return $value > 23;
                },
                'expected' => false
            ],
        ];
    }

    /**
     * @dataProvider containsOnlyDataProvider
     * @param callable|string $type
     */
    public function test_containsOnly(array $array, $type, bool $expected): void
    {
        $actual = Arr::containsOnly($array, $type);
        $this->assertEquals($expected, $actual);
    }

    public function isAssociativeDataProvider(): array
    {
        return [
            'empty array' => [
                'input' => [],
                'expected' => true
            ],
            'array with numeric keys' => [
                'input' => [1, 2, 3],
                'expected' => false
            ],
            'array with string keys' => [
                'input' => ['a' => 1, 'b' => 2, 'c' => 3],
                'expected' => true
            ],
            'array with mixed keys' => [
                'input' => ['a' => 1, 2, 'c' => 3],
                'expected' => false
            ],
            'array with mixed keys, string key is first' => [
                'input' => ['a' => 1, 2, 'c' => 3],
                'expected' => false
            ],
            'array with mixed keys, string key is last' => [
                'input' => [1, 2, 'c' => 3],
                'expected' => false
            ],
            'array with mixed keys, string key is in the middle' => [
                'input' => [1, 'b' => 2, 3],
                'expected' => false
            ],
            'array with mixed keys, string key is in the middle, string key is first' => [
                'input' => ['a' => 1, 'b' => 2, 3],
                'expected' => false
            ],
            'array with mixed keys, string key is in the middle, string key is last' => [
                'input' => [1, 'b' => 2, 'c' => 3],
                'expected' => false
            ],
            'array with mixed keys, string key is in the middle, string key is in the middle' => [
                'input' => [1, 'b' => 2, 3, 'd' => 4],
                'expected' => false
            ],
        ];
    }

    /**
     * @dataProvider isAssociativeDataProvider
     */
    public function test_isAssociative(array $input, bool $expected): void
    {
        $actual = Arr::isAssociative($input);
        $this->assertEquals($expected, $actual);
    }
}


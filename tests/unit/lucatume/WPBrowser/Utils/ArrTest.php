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
                'isNeedle' => static function (): bool {
                    return true;
                },
                'haystack' => [],
                'expected' => false
            ],
            'empty haystack, return false isNeedle' => [
                'isNeedle' => static function (): bool {
                    return false;
                },
                'haystack' => [],
                'expected' => false
            ],
            'return false isNeedle' => [
                'isNeedle' => static function (): bool {
                    return false;
                },
                'haystack' => [1, 2, 3],
                'expected' => false
            ],
            'isNeedle is true for first item' => [
                'isNeedle' => static function (int $item): bool {
                    return $item === 1;
                },
                'haystack' => [1, 2, 3],
                'expected' => 0
            ],
            'isNeedle true for 3rd and 4th argument' => [
                'isNeedle' => static function (int $item, int $key): bool {
                    return $item === 3 || $key === 3;
                },
                'haystack' => [1, 2, 3, 4],
                'expected' => 2
            ],
        ];
    }

    /**
     * @dataProvider searchWithCallbackDataProvider
     */
    public function testSearchWithCallback(callable $isNeedle, array $hastack, int|string|false $expected): void
    {
        $actual = Arr::searchWithCallback($isNeedle, $hastack);
        $this->assertEquals($expected, $actual);
    }

    public function firstFromDataProvider(): array
    {
        return [
            'empty' => [
                'value' => [],
                'default' => null,
                'expected' => null
            ],
            'empty, default value is 23' => [
                'value' => [],
                'default' => 23,
                'expected' => 23
            ],
            'object value' => [
                'value' => new stdClass(),
                'default' => null,
                'expected' => null
            ],
            'array of numbers' => [
                'value' => [1, 2, 3],
                'default' => null,
                'expected' => 1
            ],
            'array of numbers, default value is 23' => [
                'value' => [1, 2, 3],
                'default' => 23,
                'expected' => 1
            ]
        ];
    }

    /**
     * @dataProvider firstFromDataProvider
     */
    public function test_firstFrom(mixed $value, mixed $default, mixed $expected): void
    {
        $actual = Arr::firstFrom($value, $default);
        $this->assertEquals($expected, $actual);
    }

    public function hasShapeDataProvider(): array
    {
        return [
            'empty array, empty shapes' => [
                'array' => [],
                'expected' => true,
                []
            ],
            'empty array, 3 numbers shapes' => [
                'array' => [],
                'expected' => false,
                ['int', 'int', 'int']
            ],
            'array has wrong shape' => [
                'array' => [1, 2, 3],
                'expected' => false,
                ['int', 'int', 'string']
            ],
            'array has 3 objects shape' => [
                'array' => [new stdClass, new stdClass, new stdClass],
                'expected' => true,
                ['stdClass', 'stdClass', 'stdClass']
            ],
            'array has 3 objects shape, misses 1' => [
                'array' => [new stdClass, new stdClass],
                'expected' => false,
                ['stdClass', 'stdClass', 'stdClass']
            ],
            'array has mixed shape' => [
                'array' => [new stdClass, 2, '3'],
                'expected' => true,
                ['stdClass', 'int', 'string']
            ],
            'array has mixed shape, misses one' => [
                'array' => [new stdClass, 2],
                'expected' => false,
                ['stdClass', 'int', 'string']
            ],
            'array has mixed shape, 3rd type is Closure' => [
                'array' => [new stdClass, 2, '3'],
                'expected' => true,
                ['stdClass', 'int', fn(string $value): bool => $value === '3']
            ],
            'array has mixed shape, 3rd type is Closure, misses one' => [
                'array' => [new stdClass, 2],
                'expected' => false,
                ['stdClass', 'int', fn(string $value): bool => $value === '3']
            ],
            'array has mixed shape with associative type' => [
                'array' => ['a' => new stdClass, 'b' => 2, 'c' => '3'],
                'expected' => true,
                ['a' => 'stdClass', 'b' => 'int', 'c' => fn(string $value): bool => $value === '3']
            ],
            'array shape does not match mixed types' => [
                'array' => ['a' => new stdClass, 'b' => 2, 'c' => '3'],
                'expected' => false,
                ['a' => 'stdClass', 'b' => 'int', 'c' => fn(string $value): bool => $value === '4']
            ],
            'array shape matches in different order' => [
                'array' => ['a' => new stdClass, 'b' => 2, 'c' => '3'],
                'expected' => true,
                ['b' => 'int', 'c' => fn(string $value): bool => $value === '3', 'a' => 'stdClass']
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
                'type' => fn(int $value) => $value > 0,
                'expected' => true
            ],
            'array of integers, type is Closure, no match' => [
                'array' => [1, 2, 3],
                'type' => fn(int $value) => $value > 23,
                'expected' => false
            ],
        ];
    }

    /**
     * @dataProvider containsOnlyDataProvider
     */
    public function test_containsOnly(array $array, callable|string $type, bool $expected): void
    {
        $actual = Arr::containsOnly($array, $type);
        $this->assertEquals($expected, $actual);
    }
}


<?php

use lucatume\WPBrowser\TestCase\WPTestCase;

class RunInSeparateProcessAnnotationTest extends WPTestCase
{
    /**
     * @return int[][]
     */
    public function isolation_data_provider(): array
    {
        return [
            'case one' => [23],
            'case two' => [89]
        ];
    }

    /**
     * @dataProvider isolation_data_provider
     * @runInSeparateProcess
     */
    public function test_isolation_works(int $number): void
    {
        define('TEST_CONST', $number);

        $this->assertTrue(defined('TEST_CONST'));
        $this->assertEquals($number, TEST_CONST);
    }

    public function test_state_not_leaked_from_isolated_test(): void
    {
        $this->assertFalse(defined('TEST_CONST'));
    }

    public function closures_provider(): Generator
    {
        yield 'empty return closure' => [
            function () {
                return null;
            },
            function ($value) {
                return is_null($value);
            }
        ];

        yield 'numeric return closure' => [
            function () {
                return 23;
            },
            function ($value) {
                return is_int($value);
            }
        ];

        yield 'post returning closure' => [
            function () {
                return static::factory()->post->create();
            },
            function ($value) {
                return get_post($value) instanceof WP_Post;
            }
        ];
    }

    /**
     * @test
     * @runInSeparateProcess
     * @dataProvider closures_provider
     */
    public function should_correctly_serialize_closures(Closure $createCurrent, Closure $check): void
    {
        $this->assertTrue($check($createCurrent()));
    }
}

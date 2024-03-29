<?php

use Codeception\Attribute\DataProvider;
use lucatume\WPBrowser\TestCase\WPTestCase;

/**
 * @requires PHP 8.0
 */
class RunInSeparateProcessAttributeTest extends WPTestCase
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

    #[DataProvider('isolation_data_provider')]
    #[RunInSeparateProcess]
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

    /**
     * @test
     */
    public function it_works(): void
    {
        $this->assertEquals(23, 23);
    }

    /**
     * @test
     * @runInSeparateProcess
     */
    public function it_works_2(): void
    {
        $this->assertEquals(23, 23);
    }

    /**
     * @runInSeparateProcess
     * @test
     */
    public function it_works_3(): void
    {
        $this->assertEquals(23, 23);
    }

    /**
     * @test
     */
    #[RunInSeparateProcess]
    public function it_works_4(): void
    {
        $this->assertEquals(23, 23);
    }

    #[RunInSeparateProcess]
    public function it_works_5(): void
    {
        $this->assertEquals(23, 23);
    }

    #[RunInSeparateProcess]
    /**
     * @test
     */
    #[DataProvider('isolation_data_provider')]

    public function it_works_6(int $number): void
    {
        define('TEST_CONST', $number);

        $this->assertTrue(defined('TEST_CONST'));
        $this->assertEquals($number, TEST_CONST);
    }
}

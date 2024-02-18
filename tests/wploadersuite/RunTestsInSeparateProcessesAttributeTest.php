<?php

use lucatume\WPBrowser\TestCase\WPTestCase;

#[RunTestsInSeparateProcesses]
class RunTestsInSeparateProcessesAttributeTest extends WPTestCase
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
    /**
     * @dataProvider  isolation_data_provider
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
}

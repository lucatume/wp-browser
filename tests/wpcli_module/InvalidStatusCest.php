<?php

use Wpcli_moduleTester as Tester;

class InvalidStatusCest
{
    /**
     * It should handle a non-zero exit status correctly
     *
     * @test
     */
    public function should_handle_a_non_zero_exit_status_correctly(Tester $I): void
    {
        $exit_status = $I->cli('invalid');

        // Depending on the PHP version the error status might be `1` or `255`.
        $I->assertNotEquals(0, $exit_status);
    }
}

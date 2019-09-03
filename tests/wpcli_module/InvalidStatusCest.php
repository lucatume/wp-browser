<?php

use Wpcli_moduleTester as Tester;

class InvalidStatusCest
{
    /**
     * It should handle a non-zero exit status correctly.
     *
     * @test
     */
    public function should_handle_a_non_zero_exit_status_correctly_(Tester $I)
    {
        $exit_status = $I->cli('invalid');

        $I->assertEquals(1, $exit_status);
    }
}

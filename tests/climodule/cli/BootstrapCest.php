<?php
namespace cli;

use ClimoduleTester;

class BootstrapCest
{
    public function _before(ClimoduleTester $I)
    {
    }

    public function _after(ClimoduleTester $I)
    {
    }

    /**
     * @test
     * it should allow using the cli method in a test
     */
    public function it_should_allow_using_the_cli_method_in_a_test(ClimoduleTester $I)
    {
        $I->cli();
    }
}

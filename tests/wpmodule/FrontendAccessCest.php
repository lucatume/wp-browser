<?php

/**
 * Class FrontendAccessCest
 */
class FrontendAccessCest
{
    /**
     * @test
     * it should be able to navigate to main page
     */
    public function it_should_be_able_to_navigate_to_main_page(WpmoduleTester $I)
    {
        $I->amOnPage('/');
    }
}


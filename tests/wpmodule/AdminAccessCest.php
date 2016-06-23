<?php

/**
 * Class AdminAccessCest
 */
class AdminAccessCest
{
    /**
     * @test
     * it should be able to visti the admin area when unlogged
     */
    public function it_should_be_able_to_visti_the_admin_area_when_unlogged(WpmoduleTester $I)
    {
        $I->amOnAdminPage('/');
        $I->seeElement('body.login');
    }
}

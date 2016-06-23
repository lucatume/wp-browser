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

    /**
     * @test
     * it should be able to insert a post in the database and see it on the homepage
     */
    public function it_should_be_able_to_insert_a_post_in_the_database_and_see_it_on_the_homepage(WpmoduleTester $I)
    {
        $I->havePostInDatabase(['post_type' => 'post', 'post_title' => 'A post', 'post_status' => 'publish']);
        $I->amOnPage('/');
        $I->see('A post');
    }
}

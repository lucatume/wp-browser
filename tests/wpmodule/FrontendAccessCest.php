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

    /**
     * @test
     * it should clean the database between tests
     */
    public function it_should_clean_the_database_between_tests(WpmoduleTester $I)
    {
        $I->amOnPage('/');
        $I->dontSee('A post');
    }

    /**
     * @test
     * it should be able to visit the home page multiple times
     */
    public function it_should_be_able_to_visit_the_home_page_multiple_times(WpmoduleTester $I)
    {
        $I->amOnPage('/');
        $I->amOnPage('/');
        $I->amOnPage('/');
        $I->amOnPage('/');
    }
}

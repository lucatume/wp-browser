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
     * it should be able to insert a post in the database
     */
    public function it_should_be_able_to_insert_a_post_in_the_database(WpmoduleTester $I)
    {
        wp_insert_post(['post_type' => 'post', 'post_title' => 'A post', 'post_status' => 'publish']);
        $I->amOnPage('/');
        $I->see('A post');
    }


    /**
     * @test
     * it should be able to use WPDb like methods to manipulate the database
     */
    public function it_should_be_able_to_use_wp_db_like_methods_to_manipulate_the_database(WpmoduleTester $I)
    {
        $I->havePostInDatabase(['post_type' => 'post', 'post_title' => 'A post', 'post_status' => 'publish']);
        $I->amOnPage('/');
        $I->see('A post');
    }
}

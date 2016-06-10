<?php

class PagesCest
{
    public function _before(WpmoduleTester $I)
    {
    }

    public function _after(WpmoduleTester $I)
    {
    }

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
     * it should be able to see a post on the main page
     */
    public function it_should_be_able_to_see_a_post_on_the_main_page(WpmoduleTester $I)
    {
        $I->havePostInDatabase(['post_title' => 'A post']);
        $I->amOnPage('/');
        $I->see('A post');
    }
}


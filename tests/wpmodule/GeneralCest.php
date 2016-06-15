<?php

/**
 * Class GeneralCest
 */
class GeneralCest
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
        $I->factory()->post->create(['post_title' => 'A post']);
        $I->amOnPage('/');
        $I->see('A post');
    }

    /**
     * @test
     * it should be able to navigate to a single post page
     */
    public function it_should_be_able_to_navigate_to_a_single_post_page(WpmoduleTester $I)
    {
        $id = $I->factory()->post->create(['post_title' => 'A post']);
        $I->amOnPage('/?p=' . $id);
        $I->see('A post');
        $I->seeElement('body.single');
    }

    /**
     * @test
     * it should not re-include header if already included in request
     */
    public function it_should_not_re_include_header_if_already_included_in_request(WpmoduleTester $I)
    {
        $id = $I->factory()->post->create(['post_title' => 'A post']);

        $I->amOnPage('/?p=' . $id);
        $I->seeElement('body.single');

        // header.php already included, not re-included, no body class
        $I->amOnPage('/?p=' . $id);
        $I->dontSeeElement('body.single');
    }

    /**
     * @test
     * it should allow re-setting template inclusion control in tests
     */
    public function it_should_allow_re_setting_template_inclusion_control_in_tests(WpmoduleTester $I)
    {
        $id = $I->factory()->post->create(['post_title' => 'A post']);

        $I->amOnPage('/?p=' . $id);
        $I->seeElement('body.single');

        $I->resetTemplateInclusions();

        $I->amOnPage('/?p=' . $id);
        $I->seeElement('body.single');
    }

    /**
     * @test
     * it should allow setting permalinks structure in tests
     */
    public function it_should_allow_setting_permalink_structure_in_tests(WpmoduleTester $I)
    {
        $I->factory()->post->create(['post_title' => 'A post', 'post_name' => 'a-post']);

        $I->setPermalinkStructure('/%postname%/');

        $I->amOnPage('/a-post');
        $I->seeElement('body.single');
    }
}


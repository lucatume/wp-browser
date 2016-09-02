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
        $I->cli('core version');
    }

    /**
     * @test
     * it should allow creating a post in the WordPress installation
     */
    public function it_should_allow_creating_a_post_in_the_word_press_installation(ClimoduleTester $I)
    {
        $I->cli('post create --post_title="Some Post" --post_type=post');
        $I->seePostInDatabase(['post_title' => 'Some Post', 'post_type' => 'post']);
    }
}

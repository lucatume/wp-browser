<?php

use AcceptanceTester as Tester;

class WPDbOptionsbCest
{
    /**
     * It should allow seeing an option in the database using name and value
     *
     * @test
     */
    public function should_allow_seeing_an_option_in_the_database_using_name_and_value(Tester $I)
    {
        $I->haveOptionInDatabase('test_option', 'test_value');
        $I->haveOptionInDatabase('test_array_option', ['one' => 23, 'two' => ['foo','bar','baz']]);

        $I->seeOptionInDatabase('test_option', 'test_value');
        $I->seeOptionInDatabase('test_option');
        $I->seeOptionInDatabase('test_array_option', ['one' => 23, 'two' => ['foo','bar','baz']]);
        $I->seeOptionInDatabase('test_array_option');
    }

    /**
     * It should allow seeing an option using array criteria
     *
     * @test
     */
    public function should_allow_seeing_an_option_using_array_criteria(Tester $I)
    {
        $I->haveOptionInDatabase('test_option', 'test_value');
        $I->haveOptionInDatabase('test_array_option', ['one' => 23, 'two' => ['foo','bar','baz']]);

        $I->seeOptionInDatabase(['option_name' => 'test_option', 'option_value' => 'test_value']);
        $I->seeOptionInDatabase(['option_name' => 'test_option']);
        $I->seeOptionInDatabase(['option_name' => 'test_array_option', 'option_value' => ['one' => 23, 'two' => ['foo','bar','baz']]]);
        $I->seeOptionInDatabase(['option_name' => 'test_array_option']);
    }

    /**
     * It should allow not seeing an option using name and value
     *
     * @test
     */
    public function should_allow_not_seeing_an_option_using_name_and_value(Tester $I)
    {
        $I->dontSeeOptionInDatabase('test_option', 'test_value');
        $I->dontSeeOptionInDatabase('test_option');
        $I->dontSeeOptionInDatabase('test_array_option', ['one' => 23, 'two' => ['foo','bar','baz']]);
        $I->dontSeeOptionInDatabase('test_array_option');
    }

    /**
     * It should allow not seeing and option using array criteria
     *
     * @test
     */
    public function should_allow_not_seeing_and_option_using_array_criteria(Tester $I)
    {
        $I->dontSeeOptionInDatabase(['option_name' => 'test_option', 'option_value' => 'test_value']);
        $I->dontSeeOptionInDatabase(['option_name' => 'test_option']);
        $I->dontSeeOptionInDatabase(['option_name' => 'test_array_option', 'option_value' => ['one' => 23, 'two' => ['foo','bar','baz']]]);
        $I->dontSeeOptionInDatabase(['option_name' => 'test_array_option']);
    }

    /**
     * It should allow seeing a site option in the database using name and value
     *
     * @test
     */
    public function should_allow_seeing_a_site_option_in_the_database_using_name_and_value(Tester $I)
    {
        $I->haveSiteOptionInDatabase('test_option', 'test_value');
        $I->haveSiteOptionInDatabase('test_array_option', ['one' => 23, 'two' => ['foo','bar','baz']]);

        $I->seeSiteOptionInDatabase('test_option', 'test_value');
        $I->seeSiteOptionInDatabase('test_option');
        $I->seeSiteOptionInDatabase('test_array_option', ['one' => 23, 'two' => ['foo','bar','baz']]);
        $I->seeSiteOptionInDatabase('test_array_option');
    }

    /**
     * It should allo seeing a site option in database using array criteria
     *
     * @test
     */
    public function should_allo_seeing_a_site_option_in_database_using_array_criteria(Tester $I)
    {
        $I->haveSiteOptionInDatabase('test_option', 'test_value');
        $I->haveSiteOptionInDatabase('test_array_option', ['one' => 23, 'two' => ['foo','bar','baz']]);

        $I->seeSiteOptionInDatabase(['option_name' => 'test_option', 'option_value' => 'test_value']);
        $I->seeSiteOptionInDatabase(['option_name' => 'test_option']);
        $I->seeSiteOptionInDatabase(['option_name' => 'test_array_option', 'option_value' => ['one' => 23, 'two' => ['foo','bar','baz']]]);
        $I->seeSiteOptionInDatabase(['option_name' => 'test_array_option']);
    }

    /**
     * It should allow not seeing a site option using name and value
     *
     * @test
     */
    public function should_allow_not_seeing_a_site_option_using_name_and_value(Tester $I)
    {
        $I->dontSeeSiteOptionInDatabase(['option_name' => 'test_option', 'option_value' => 'test_value']);
        $I->dontSeeSiteOptionInDatabase(['option_name' => 'test_option']);
        $I->dontSeeSiteOptionInDatabase(['option_name' => 'test_array_option', 'option_value' => ['one' => 23, 'two' => ['foo','bar','baz']]]);
        $I->dontSeeSiteOptionInDatabase(['option_name' => 'test_array_option']);
    }

    /**
     * It should allow not seeing a site option using array criteria
     *
     * @test
     */
    public function should_allow_not_seeing_a_site_option_using_array_criteria()
    {
    }
}

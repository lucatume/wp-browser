<?php


namespace Acceptance;

use AcceptanceTester as Tester;

class TransientOperationsCest
{
    /**
     * It should allow transient operations
     *
     * @test
     */
    public function should_allow_transient_operations(Tester $I): void
    {
        $I->assertEquals('', $I->grabTransientFromDatabase('a_test_transient'));
        $I->dontSeeTransientInDatabase('a_test_transient');

        $I->haveTransientInDatabase('a_test_transient', 23);

        $I->assertEquals(23, $I->grabTransientFromDatabase('a_test_transient'));
        $I->seeTransientInDatabase('a_test_transient');
        $I->seeTransientInDatabase('a_test_transient', 23);

        $I->dontHaveTransientInDatabase('a_test_transient');

        $I->assertEquals('', $I->grabTransientFromDatabase('a_test_transient'));
        $I->dontSeeTransientInDatabase('a_test_transient');
    }

    /**
     * It should allow transient operations on a second site
     *
     * @test
     */
    public function should_allow_transient_operations_on_a_second_site(Tester $I): void
    {
        $I->useBlog(2);

        $I->assertEquals('', $I->grabTransientFromDatabase('a_test_transient'));
        $I->dontSeeTransientInDatabase('a_test_transient');

        $I->haveTransientInDatabase('a_test_transient', 23);

        $I->assertEquals(23, $I->grabTransientFromDatabase('a_test_transient'));
        $I->seeTransientInDatabase('a_test_transient');
        $I->seeTransientInDatabase('a_test_transient', 23);

        $I->useMainBlog();

        $I->assertEquals('', $I->grabTransientFromDatabase('a_test_transient'));
        $I->dontSeeTransientInDatabase('a_test_transient');

        $I->dontHaveTransientInDatabase('a_test_transient');

        $I->assertEquals('', $I->grabTransientFromDatabase('a_test_transient'));
        $I->dontSeeTransientInDatabase('a_test_transient');

        $I->useBlog(2);

        $I->assertEquals(23, $I->grabTransientFromDatabase('a_test_transient'));
        $I->seeTransientInDatabase('a_test_transient');
        $I->seeTransientInDatabase('a_test_transient', 23);

        $I->dontHaveTransientInDatabase('a_test_transient');

        $I->assertEquals('', $I->grabTransientFromDatabase('a_test_transient'));
        $I->dontSeeTransientInDatabase('a_test_transient');
    }

    /**
     * It should allow site transient operations
     *
     * @test
     */
    public function should_allow_site_transient_operations(Tester $I): void
    {
        $I->assertEquals('', $I->grabSiteTransientFromDatabase('a_test_transient'));
        $I->dontSeeSiteTransientInDatabase('a_test_transient');

        $I->haveSiteTransientInDatabase('a_test_transient', 23);

        $I->assertEquals(23, $I->grabSiteTransientFromDatabase('a_test_transient'));
        $I->seeSiteTransientInDatabase('a_test_transient');
        $I->seeSiteTransientInDatabase('a_test_transient', 23);

        $I->dontHaveSiteTransientInDatabase('a_test_transient');

        $I->assertEquals('', $I->grabSiteTransientFromDatabase('a_test_transient'));
        $I->dontSeeSiteTransientInDatabase('a_test_transient');
        $I->dontSeeSiteTransientInDatabase('a_test_transient', 23);
    }
}

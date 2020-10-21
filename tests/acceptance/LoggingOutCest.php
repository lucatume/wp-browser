<?php

class LoggingOutCest
{
    /**
     * @var string
     */
    protected $postUri;

    public function _before(AcceptanceTester $I)
    {
        $I->loginAsAdmin();
        $this->postUri = '/index.php/?p=' . $I->havePostInDatabase(['post_status' => 'private']);
    }

    /**
     * It should allow logging out and not move
     *
     * @test
     */
    public function should_allow_logging_out_and_not_move(AcceptanceTester $I)
    {
        $I->logOut(false);

        $I->seeInCurrentUrl('/wp-login.php');
        $I->amOnPage($this->postUri);
        $I->seeResponseCodeIs(404);
    }

    /**
     * It should allow logging out and return to the previous page
     *
     * @test
     */
    public function should_allow_logging_out_and_return_to_the_previous_page(AcceptanceTester $I)
    {
        $I->amOnPage($this->postUri);
        // Redirection.
        $currentUri = $I->grabFromCurrentUrl();

        $I->logOut(true);

        $I->seeCurrentUrlEquals($currentUri);
        $I->amOnPage($this->postUri);
        $I->seeResponseCodeIs(404);
    }

    /**
     * It should allow logging out and go to a different page
     *
     * @test
     */
    public function should_allow_logging_out_and_go_to_a_different_page(AcceptanceTester $I)
    {
        $I->logOut($this->postUri);

        $I->dontSeeInCurrentUrl('/wp-login.php');
        $redirectedUri = $I->grabFromCurrentUrl();
        $I->amOnPage($this->postUri);
        $I->assertEquals($I->grabFromCurrentUrl(), $redirectedUri);
        $I->seeResponseCodeIs(404);
    }
}

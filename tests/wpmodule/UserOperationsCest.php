<?php

/**
 * Class UserOperationsCest
 */
class UserOperationsCest
{

    /**
     * @test
     * it should require to login when trying to access user edit page and not logged in
     */
    public function it_should_require_to_login_when_trying_to_access_user_edit_page_and_not_logged_in(WpmoduleTester $I)
    {
        $id = $I->haveUserInDatabase('editor');

        $I->amEditingUserWithId($id);

        $I->seeElement('body.login');
    }

    /**
     * @test
     * it should allow accessing a user edit page
     */
    public function it_should_allow_accessing_a_user_edit_page(WpmoduleTester $I)
    {
        $id = $I->haveUserInDatabase('editor');

        $I->loginAsAdmin();
        $I->amEditingUserWithId($id);

        $I->seeElement('body.wp-admin.user-edit-php');
    }
}

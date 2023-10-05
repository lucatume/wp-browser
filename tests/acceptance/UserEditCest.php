<?php


namespace Acceptance;

use \AcceptanceTester as Tester;

class UserEditCest
{
    public function _before(Tester $I)
    {
        $I->loginAsAdmin();
    }

    /**
     * It should allow editing a user by ID
     *
     * @test
     */
    public function should_allow_editing_a_user_by_id(Tester $I): void
    {
        $userId = $I->haveUserInDatabase('bob', 'subscriber', [
            'display_name' => 'TheBob'
        ]);

        $I->amEditingUserWithId($userId);

        $I->see('Edit User TheBob');
    }

    /**
     * It should fail to edit a user that does not exist
     *
     * @test
     */
    public function should_fail_to_edit_a_user_that_does_not_exist(Tester $I): void
    {
        $userId = 999999;

        $I->amEditingUserWithId($userId);

        $I->see('Invalid user ID.');
    }
}

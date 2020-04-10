<?php

class WPDbUserCest
{
    /**
     * It should correctly create a user nickname when specified in meta
     *
     * @test
     */
    public function should_correctly_create_a_user_nickname_when_specified_in_meta(AcceptanceTester $I)
    {
        $user = $I->haveUserInDatabase('test', 'subscriber', [ 'meta_input' => [ 'nickname' => 'test' ] ]);

        $I->seeUserMetaInDatabase([ 'user_id' => $user, 'meta_key' => 'nickname' ]);
        $I->seeUserMetaInDatabase([ 'user_id' => $user, 'meta_key' => 'nickname', 'meta_value' => 'test' ]);
    }

    /**
     * It should correctly create a user nickname when not specified in meta
     *
     * @test
     */
    public function should_correctly_create_a_user_nickname_when_not_specified_in_meta(AcceptanceTester $I)
    {
        $user = $I->haveUserInDatabase('test', 'subscriber');

        $I->seeUserMetaInDatabase([ 'user_id' => $user, 'meta_key' => 'nickname' ]);
        $I->seeUserMetaInDatabase([ 'user_id' => $user, 'meta_key' => 'nickname', 'meta_value' => 'test' ]);
    }
}

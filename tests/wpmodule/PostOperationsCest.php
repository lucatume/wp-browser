<?php

/**
 * Class AdminAccessCest
 */
class PostOperationsCest
{
    /**
     * @test
     * it should allow accessing a post edit page
     */
    public function it_should_allow_accessing_a_post_edit_page(WpmoduleTester $I)
    {
        $I->loginAsAdmin();
        $id = $I->havePostInDatabase();
        $I->amEditingPostWithId($id);

        $I->seeElement('body.wp-admin.post-php.post-type-post');
    }
}

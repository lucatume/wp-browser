<?php

/**
 * Class AdminAccessCest
 */
class PostOperationsCest
{

	/**
	 * @test
	 * it should require to login when tryng to access post edit page and not logged in
	 */
	public function it_should_require_to_login_when_tryng_to_access_post_edit_page_and_not_logged_in(WpmoduleTester $I)
	{
		$id = $I->havePostInDatabase();

		$I->amEditingPostWithId($id);

		$I->seeElement('body.login');
	}

	/**
	 * @test
	 * it should allow accessing a post edit page
	 */
	public function it_should_allow_accessing_a_post_edit_page(WpmoduleTester $I)
	{
		$id = $I->havePostInDatabase();

		$I->loginAsAdmin();
		$I->amEditingPostWithId($id);

		$I->seeElement('body.wp-admin.post-php.post-type-post');
	}
}

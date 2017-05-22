<?php


class MetaDeleteCest
{

	/**
	 * It should remove all the meta associated with a post by default
	 *
	 * @test
	 */
	public function it_should_remove_all_the_meta_associated_with_a_post_by_default(AcceptanceTester $I)
	{
		$id = $I->havePostInDatabase(['meta_input' => ['foo' => 'bar', 'baz' => 'bar']]);

		$I->seePostInDatabase(['ID' => $id]);
		$I->seePostMetaInDatabase(['post_id' => $id, 'meta_key' => 'foo']);
		$I->seePostMetaInDatabase(['post_id' => $id, 'meta_key' => 'baz']);

		$I->dontHavePostInDatabase(['ID' => $id]);

		$I->dontSeePostInDatabase(['ID' => $id]);
		$I->dontSeePostMetaInDatabase(['post_id' => $id, 'meta_key' => 'foo']);
		$I->dontSeePostMetaInDatabase(['post_id' => $id, 'meta_key' => 'baz']);
	}

	/**
	 * It should not purge post meta if not required
	 *
	 * @test
	 */
	public function it_should_not_purge_post_meta_if_not_required(AcceptanceTester $I)
	{
		$id = $I->havePostInDatabase(['meta_input' => ['foo' => 'bar', 'baz' => 'bar']]);

		$I->seePostInDatabase(['ID' => $id]);
		$I->seePostMetaInDatabase(['post_id' => $id, 'meta_key' => 'foo']);
		$I->seePostMetaInDatabase(['post_id' => $id, 'meta_key' => 'baz']);

		$I->dontHavePostInDatabase(['ID' => $id], false);

		$I->dontSeePostInDatabase(['ID' => $id]);
		$I->seePostMetaInDatabase(['post_id' => $id, 'meta_key' => 'foo']);
		$I->seePostMetaInDatabase(['post_id' => $id, 'meta_key' => 'baz']);
	}

	/**
	 * It should remove a user meta when removing the user by default
	 *
	 * @test
	 */
	public function it_should_remove_a_user_meta_when_removing_the_user_by_default(AcceptanceTester $I)
	{
		$id = $I->haveUserInDatabase('user');
		$I->haveUserMetaInDatabase($id, 'foo', 'baz');
		$I->haveUserMetaInDatabase($id, 'baz', 'bar');

		$I->seeUserInDatabase(['ID' => $id]);
		$I->seeUserMetaInDatabase(['user_id' => $id, 'meta_key' => 'foo']);
		$I->seeUserMetaInDatabase(['user_id' => $id, 'meta_key' => 'baz']);

		$I->dontHaveUserInDatabase($id);

		$I->dontSeeUserInDatabase(['ID' => $id]);
		$I->dontSeeUserMetaInDatabase(['user_id' => $id, 'meta_key' => 'foo']);
		$I->dontSeeUserMetaInDatabase(['user_id' => $id, 'meta_key' => 'baz']);
	}

	/**
	 * It should not remove a user meta when removing the user if not required
	 *
	 * @test
	 */
	public function it_should_not_remove_a_user_meta_when_removing_the_user_if_not_required(AcceptanceTester $I)
	{
		$id = $I->haveUserInDatabase('user');
		$I->haveUserMetaInDatabase($id, 'foo', 'baz');
		$I->haveUserMetaInDatabase($id, 'baz', 'bar');

		$I->seeUserInDatabase(['ID' => $id]);
		$I->seeUserMetaInDatabase(['user_id' => $id, 'meta_key' => 'foo']);
		$I->seeUserMetaInDatabase(['user_id' => $id, 'meta_key' => 'baz']);

		$I->dontHaveUserInDatabase($id, false);

		$I->dontSeeUserInDatabase(['ID' => $id]);
		$I->seeUserMetaInDatabase(['user_id' => $id, 'meta_key' => 'foo']);
		$I->seeUserMetaInDatabase(['user_id' => $id, 'meta_key' => 'baz']);
	}

	/**
	 * It should remove a term meta when removing the term by default
	 *
	 * @test
	 */
	public function it_should_remove_a_term_meta_when_removing_the_term_by_default(AcceptanceTester $I)
	{
		$termData = $I->haveTermInDatabase('term', 'taxonomy');
		$id = reset($termData);
		$I->haveTermMetaInDatabase($id, 'foo', 'baz');
		$I->haveTermMetaInDatabase($id, 'baz', 'bar');

		$I->seeTermInDatabase(['term_id' => $id]);
		$I->seeTermMetaInDatabase(['term_id' => $id, 'meta_key' => 'foo']);
		$I->seeTermMetaInDatabase(['term_id' => $id, 'meta_key' => 'baz']);

		$I->dontHaveTermInDatabase(['term_id' => $id]);

		$I->dontSeeTermInDatabase(['term_id' => $id]);
		$I->dontSeeTermMetaInDatabase(['term_id' => $id, 'meta_key' => 'foo']);
		$I->dontSeeTermMetaInDatabase(['term_id' => $id, 'meta_key' => 'baz']);
	}

	/**
	 * It should not remove a term meta when removing if not required
	 *
	 * @test
	 */
	public function it_should_not_remove_a_term_meta_when_removing_if_not_required(AcceptanceTester $I)
	{
		$termData = $I->haveTermInDatabase('term', 'taxonomy');
		$id = reset($termData);
		$I->haveTermMetaInDatabase($id, 'foo', 'baz');
		$I->haveTermMetaInDatabase($id, 'baz', 'bar');

		$I->seeTermInDatabase(['term_id' => $id]);
		$I->seeTermMetaInDatabase(['term_id' => $id, 'meta_key' => 'foo']);
		$I->seeTermMetaInDatabase(['term_id' => $id, 'meta_key' => 'baz']);

		$I->dontHaveTermInDatabase(['term_id' => $id], false);

		$I->dontSeeTermInDatabase(['term_id' => $id]);
		$I->seeTermMetaInDatabase(['term_id' => $id, 'meta_key' => 'foo']);
		$I->seeTermMetaInDatabase(['term_id' => $id, 'meta_key' => 'baz']);
	}

	/**
	 * It should remove a comment meta when removing the comment by default
	 *
	 * @test
	 */
	public function it_should_remove_a_comment_meta_when_removing_the_comment_by_default(AcceptanceTester $I)
	{
		$id = $I->haveCommentInDatabase($I->havePostInDatabase());
		$I->haveCommentMetaInDatabase($id,'foo','baz');
		$I->haveCommentMetaInDatabase($id,'baz','bar');

		$I->seeCommentInDatabase(['comment_id' => $id]);
		$I->seeCommentMetaInDatabase(['comment_id' => $id, 'meta_key' => 'foo']);
		$I->seeCommentMetaInDatabase(['comment_id' => $id, 'meta_key' => 'baz']);

		$I->dontHaveCommentInDatabase(['comment_id' => $id]);

		$I->dontSeeCommentInDatabase(['comment_id' => $id]);
		$I->dontSeeCommentMetaInDatabase(['comment_id' => $id, 'meta_key' => 'foo']);
		$I->dontSeeCommentMetaInDatabase(['comment_id' => $id, 'meta_key' => 'baz']);
	}

	/**
	 * It should not remove a comment meta removing if not required
	 *
	 * @test
	 */
	public function it_should_not_remove_a_comment_meta_removing_if_not_required(AcceptanceTester $I)
	{
		$id = $I->haveCommentInDatabase($I->havePostInDatabase());
		$I->haveCommentMetaInDatabase($id,'foo','baz');
		$I->haveCommentMetaInDatabase($id,'baz','bar');

		$I->seeCommentInDatabase(['comment_id' => $id]);
		$I->seeCommentMetaInDatabase(['comment_id' => $id, 'meta_key' => 'foo']);
		$I->seeCommentMetaInDatabase(['comment_id' => $id, 'meta_key' => 'baz']);

		$I->dontHaveCommentInDatabase(['comment_id' => $id],false);

		$I->dontSeeCommentInDatabase(['comment_id' => $id]);
		$I->seeCommentMetaInDatabase(['comment_id' => $id, 'meta_key' => 'foo']);
		$I->seeCommentMetaInDatabase(['comment_id' => $id, 'meta_key' => 'baz']);
	}
}

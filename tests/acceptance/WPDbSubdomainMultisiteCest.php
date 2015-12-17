<?php


class WPDbSubdomainMultisiteCest {

	public function _before( AcceptanceTester $I ) {
	}

	public function _after( AcceptanceTester $I ) {
	}

	/**
	 * @test
	 * it should not activate multisite by default
	 */
	public function it_should_not_activate_multisite_by_default( AcceptanceTester $I ) {
		// Set the theme to the multisite test one
		$I->haveOptionInDatabase( 'stylesheet', 'multisite', 'yes' );
		$I->haveOptionInDatabase( 'template', 'multisite', 'yes' );

		$I->amOnPage( '/' );
		$I->see( 'Multisite is not active' );
	}

	/**
	 * @test
	 * it should be able to activate multisite
	 */
	public function it_should_be_able_to_activate_multisite( AcceptanceTester $I ) {
		// Set the theme to the multisite test one
		$I->haveOptionInDatabase( 'stylesheet', 'multisite', 'yes' );
		$I->haveOptionInDatabase( 'template', 'multisite', 'yes' );

		$I->haveMultisiteInDatabase();

		$I->amOnPage( '/' );
		$I->see( 'Multisite is active' );
	}

	/**
	 * @test
	 * it should allow seing posts from different blogs
	 */
	public function it_should_allow_seing_posts_from_different_blogs( AcceptanceTester $I ) {
		$I->haveMultisiteInDatabase();
		$ids = $I->haveManyBlogsInDatabase( 3, [ 'domain' => 'test{{n}}' ] );

		for ( $i = 0; $i < 3; $i++ ) {
			$I->seeBlogInDatabase( [ 'domain' => 'test' . $i ] );
		}

		$firstBlogId = reset( $ids );
		$I->useBlog( $firstBlogId );
		$I->haveManyPostsInDatabase( 3, [
			'post_title'    => 'Blog {{blog}} - Post {{n}}',
			'template_data' => [ 'blog' => $firstBlogId ]
		] );

		$I->amOnSubdomain( 'test0' );
		$I->amOnPage( '/' );
		$I->see( "Blog $firstBlogId - Post 0" );
		$I->see( "Blog $firstBlogId - Post 1" );
		$I->see( "Blog $firstBlogId - Post 2" );

		$I->amOnSubdomain( 'test1' );
		$I->amOnPage( '/' );
		$I->dontSee( "Blog {$ids[1]} - Post 0" );

		$I->amOnSubdomain( 'test2' );
		$I->amOnPage( '/' );
		$I->dontSee( "Blog {$ids[2]} - Post 0" );
	}
}

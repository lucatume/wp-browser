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
		$I->haveOptionInDatabase( 'stylesheet', 'multisite', 'yes' );
		$I->haveOptionInDatabase( 'template', 'multisite', 'yes' );

		// subdomain, need htaccess and wp-confing.php replaced
		$I->haveMultisiteInDatabase( true, true, 1 );

		$I->amOnPage( '/' );
		$I->see( 'Multisite is active' );
	}

	/**
	 * @test
	 * it should allow seing posts from different blogs
	 */
	public function it_should_allow_seing_posts_from_different_blogs( AcceptanceTester $I ) {
		$I->haveOptionInDatabase( 'stylesheet', 'multisite', 'yes' );
		$I->haveOptionInDatabase( 'template', 'multisite', 'yes' );

		// subdomain, need htaccess and wp-confing.php replaced
		$I->haveMultisiteInDatabase( true, true, 1 );
		$blogIds = $I->haveManyBlogsInDatabase( 3, [ 'domain' => 'test{{n}}.' . $I->getSiteDomain() ] );

		for ( $i = 0; $i < 3; $i++ ) {
			$I->seeBlogInDatabase( [ 'domain' => 'test' . $i . '.' . $I->getSiteDomain() ] );
		}

		foreach ( $blogIds as $blogId ) {
			$I->useBlog( $blogId );
			$I->haveManyPostsInDatabase( 3, [
				'post_title'    => 'Blog {{blog}} - Post {{n}}',
				'template_data' => [ 'blog' => $blogId ]
			] );
		}

		for ( $i = 0; $i < 3; $i++ ) {
			$blogId = $blogIds[$i];
			$I->amOnSubdomain( 'test' . $i );
			$I->amOnPage( '/' );
			$I->see( "Blog $blogId - Post 0" );
			$I->see( "Blog $blogId - Post 1" );
			$I->see( "Blog $blogId - Post 2" );
		}
	}
}

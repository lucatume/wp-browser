<?php


class WPDbSubdomainMultisiteCest {

	public function _before( AcceptanceTester $I ) {
	}

	public function _after( AcceptanceTester $I ) {
	}

	/**
	 * @test
	 * it should be able to visit the single blog page
	 */
	public function it_should_be_able_to_visit_the_single_blog_page(AcceptanceTester $I) {
		$I->haveManyPostsInDatabase(3,['post_title' => 'Title {{n}}']);
		$I->amOnPage('/');

		$I->see('Title 0');
		$I->see('Title 1');
		$I->see('Title 2');
	}
	/**
	 * @test
	 * it should be able to visit main blog main page
	 */
	public function it_should_be_able_to_visit_main_blog_main_page( AcceptanceTester $I ) {
		$I->haveManyPostsInDatabase( 3, [ 'post_title' => 'Post {{n}}' ] );
		$I->amOnPage( '/' );
		$I->see( 'Post 0' );
		$I->see( 'Post 1' );
		$I->see( 'Post 2' );
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

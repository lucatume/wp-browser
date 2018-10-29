<?php

class WPDbMultisiteSubdomainCest {

	/**
	 * @test
	 * it should allow seing posts from different blogs
	 */
	public function it_should_allow_seing_posts_from_different_blogs(AcceptanceTester $I) {
		$blogIds = $I->haveManyBlogsInDatabase(3, ['domain' => $I->getSiteDomain().'/blog{{n}}/'],false);

		for ($i = 0; $i < 3; $i++) {
			$I->seeBlogInDatabase(['path' => "/blog{$i}/"]);
		}

		foreach ($blogIds as $blogId) {
			$I->useBlog($blogId);
			$I->haveManyPostsInDatabase(3, [
				'post_title'    => 'Blog {{blog}} Post {{n}}',
				'template_data' => ['blog' => $blogId],
			]);
		}

		foreach ($blogIds as $i => $blogId) {
			$I->useBlog($blogId);
			$I->haveOptionInDatabase('posts_per_page', 10);
			$I->amOnPage("/blog{$i}");
			$I->see("Blog $blogId Post 0");
			$I->see("Blog $blogId Post 1");
			$I->see("Blog $blogId Post 2");
		}
	}
}
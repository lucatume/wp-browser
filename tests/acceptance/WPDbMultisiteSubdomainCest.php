<?php

class WPDbMultisiteSubdomainCest
{

    /**
     * @test
     * it should allow seeing posts from different blogs
     */
    public function it_should_allow_seeing_posts_from_different_blogs(AcceptanceTester $I): void
    {
        $blogIds = $I->haveManyBlogsInDatabase(3, ['domain' => 'blog{{n}}.' . $I->getSiteDomain()]);

        for ($i = 0; $i < 3; $i++) {
            $I->seeBlogInDatabase(['domain' => 'blog' . $i . '.' . $I->getSiteDomain()]);
        }

        foreach ($blogIds as $blogId) {
            $I->useBlog($blogId);
            $I->haveManyPostsInDatabase(3, [
                'post_title' => 'Blog {{blog}} Post {{n}}',
                'template_data' => ['blog' => $blogId],
            ]);
        }

        codecept_debug($blogIds);

        for ($i = 0; $i < 3; $i++) {
            $blogId = $blogIds[$i];
            $I->amOnUrl($I->grabBlogUrl($blogId));
            $I->useBlog($blogId);
            $I->haveOptionInDatabase('posts_per_page', 10);
            $I->amOnPage('/');
            $I->see("Blog $blogId Post 0");
            $I->see("Blog $blogId Post 1");
            $I->see("Blog $blogId Post 2");
        }
    }
}

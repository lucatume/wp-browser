<?php

use lucatume\WPBrowser\Generators\Date;

class WPDbCommentCest
{
    /**
     * @test
     * it should allow having a comment in the database
     */
    public function it_should_allow_having_a_comment_in_the_database(FunctionalTester $I): void
    {
        $now = time();
        Date::_injectNow($now);
        $postId = $I->havePostInDatabase();
        $id = $I->haveCommentInDatabase($postId);

        $defaults = [
            'comment_post_ID' => $postId,
            'comment_author' => 'Mr WordPress',
            'comment_author_email' => '',
            'comment_author_url' => 'https://wordpress.org/',
            'comment_author_IP' => '',
            'comment_date' => Date::now(),
            'comment_date_gmt' => Date::gmtNow(),
            'comment_content' => "Hi, this is a comment.\nTo delete a comment, just log in and view the post&#039;s comments. There you will have the option to edit or delete them.",
            'comment_karma' => '0',
            'comment_approved' => '1',
            'comment_agent' => '',
            'comment_type' => '',
            'comment_parent' => 0,
            'user_id' => 0,
        ];

        $I->seeInDatabase($I->grabCommentsTableName(), $defaults);
    }

    /**
     * @test
     * it should allow overriding a comment defaults
     */
    public function it_should_allow_overriding_a_comment_defaults(FunctionalTester $I): void
    {
        $now = time();
        Date::_injectNow($now);
        $postId = $I->havePostInDatabase();

        $overrides = [
            'comment_author' => 'Luca',
            'comment_author_email' => 'luca@example.com',
            'comment_author_url' => 'https://theaveragedev.com',
            'comment_author_IP' => '111.222.333.444',
            'comment_date' => Date::now(),
            'comment_date_gmt' => Date::gmtNow(),
            'comment_content' => "No comment",
            'comment_karma' => '3',
            'comment_approved' => '0',
            'comment_agent' => 'some agent',
            'comment_type' => 'status',
            'comment_parent' => 23,
            'user_id' => 12,
        ];

        $id = $I->haveCommentInDatabase($postId, $overrides);

        foreach ($overrides as $key => $value) {
            $I->seeInDatabase($I->grabCommentsTableName(), [
                'comment_post_ID' => $postId,
                'comment_ID' => $id,
                $key => $value
            ]);
        }
    }

    /**
     * @test
     * it should allow having many comments
     */
    public function it_should_allow_having_many_comments(FunctionalTester $I): void
    {
        $postId = $I->havePostInDatabase();
        $ids = $I->haveManyCommentsInDatabase(5, $postId);

        $I->assertCount(5, $ids);
        foreach ($ids as $commentId) {
            $I->seeInDatabase($I->grabCommentsTableName(), [
                'comment_ID' => $commentId,
                'comment_post_ID' => $postId
            ]);
        }
    }

    /**
     * @test
     * it should allow having many comments with overrides
     */
    public function it_should_allow_having_many_comments_with_overrides(FunctionalTester $I): void
    {
        $postId = $I->havePostInDatabase();

        $overrides = [
            'comment_author' => 'Luca',
            'comment_author_email' => 'luca@example.com',
            'comment_author_url' => 'https://theaveragedev.com',
            'comment_author_IP' => '111.222.333.444',
            'comment_date' => Date::now(),
            'comment_date_gmt' => Date::gmtNow(),
            'comment_content' => "No comment",
            'comment_karma' => '3',
            'comment_approved' => '0',
            'comment_agent' => 'some agent',
            'comment_type' => 'status',
            'comment_parent' => 23,
            'user_id' => 12,
        ];

        $ids = $I->haveManyCommentsInDatabase(5, $postId, $overrides);

        $I->assertCount(5, $ids);
        foreach ($ids as $commentId) {
            foreach ($overrides as $key => $value) {
                $I->seeInDatabase($I->grabCommentsTableName(), [
                    'comment_post_ID' => $postId,
                    'comment_ID' => $commentId,
                    $key => $value
                ]);
            }
        }
    }

    /**
     * @test
     * it should allow having many comments with number placeholder
     */
    public function it_should_allow_having_many_comments_with_number_placeholder(FunctionalTester $I): void
    {
        $postId = $I->havePostInDatabase();

        $overrides = [
            'comment_author' => 'Luca',
            'comment_author_email' => 'luca@example.com',
            'comment_author_url' => 'https://theaveragedev.com',
            'comment_author_IP' => '111.222.333.444',
            'comment_date' => Date::now(),
            'comment_date_gmt' => Date::gmtNow(),
            'comment_content' => "No comment",
            'comment_karma' => '3',
            'comment_approved' => '0',
            'comment_agent' => 'some agent',
            'comment_type' => 'status',
            'comment_parent' => 23,
            'user_id' => 12,
        ];

        $ids = $I->haveManyCommentsInDatabase(5, $postId, $overrides);

        $I->assertCount(5, $ids);
        for ($i = 0; $i < count($ids); $i++) {
            foreach ($overrides as $key => $value) {
                $I->seeInDatabase($I->grabCommentsTableName(), [
                    'comment_post_ID' => $postId,
                    'comment_ID' => $ids[$i],
                    $key => str_replace('{{n}}', $i, $value)
                ]);
            }
        }
    }

    /**
     * @test
     * it should allow having comment meta in the database
     */
    public function it_should_allow_having_comment_meta_in_the_database(FunctionalTester $I): void
    {
        $postId = $I->havePostInDatabase();
        $commentId = $I->haveCommentInDatabase($postId);
        $metaId = $I->haveCommentMetaInDatabase($commentId, 'foo', 'bar');

        $I->assertTrue(!empty($metaId) && is_int($metaId));
        $I->seeInDatabase($I->grabCommentmetaTableName(), [
            'comment_id' => $commentId,
            'meta_key' => 'foo',
            'meta_value' => 'bar'
        ]);
    }

    /**
     * @test
     * it should serialize array comment meta
     */
    public function it_should_serialize_array_comment_meta(FunctionalTester $I): void
    {
        $postId = $I->havePostInDatabase();
        $commentId = $I->haveCommentInDatabase($postId);

        $meta = ['one' => 1, 'two' => 2];
        $I->haveCommentMetaInDatabase($commentId, 'foo', $meta);

        $I->seeInDatabase($I->grabCommentmetaTableName(), [
            'comment_id' => $commentId,
            'meta_key' => 'foo',
            'meta_value' => serialize($meta)
        ]);
    }

    /**
     * @test
     * it should serialize object comment meta
     */
    public function it_should_serialize_object_comment_meta(FunctionalTester $I): void
    {
        $postId = $I->havePostInDatabase();
        $commentId = $I->haveCommentInDatabase($postId);

        $meta = (object)['one' => 1, 'two' => 2];
        $I->haveCommentMetaInDatabase($commentId, 'foo', $meta);

        $I->seeInDatabase($I->grabCommentmetaTableName(), [
            'comment_id' => $commentId,
            'meta_key' => 'foo',
            'meta_value' => serialize($meta)
        ]);
    }

    /**
     * @test
     * it should allow having comment meta while having comment
     */
    public function it_should_allow_having_comment_meta_while_having_comment(FunctionalTester $I): void
    {
        $postId = $I->havePostInDatabase();
        $commentId = $I->haveCommentInDatabase($postId, ['meta' => ['foo' => 'bar']]);

        $I->seeCommentMetaInDatabase(['comment_id' => $commentId, 'meta_key' => 'foo', 'meta_value' => 'bar']);
    }

    /**
     * @test
     * it should allow not having comment meta in database
     */
    public function it_should_allow_not_having_comment_meta_in_database(FunctionalTester $I): void
    {
        $postId = $I->havePostInDatabase();
        $commentId = $I->haveCommentInDatabase($postId);
        $metaId = $I->haveCommentMetaInDatabase($commentId, 'foo', 'bar');

        $I->seeCommentMetaInDatabase(['comment_id' => $commentId, 'meta_id' => $metaId]);

        $I->dontHaveCommentMetaInDatabase(['comment_id' => $commentId, 'meta_id' => $metaId]);

        $I->dontSeeCommentMetaInDatabase(['comment_id' => $commentId, 'meta_id' => $metaId]);
    }

    /**
     * @test
     * it should allow not having comment in database
     */
    public function it_should_allow_not_having_comment_in_database(FunctionalTester $I): void
    {
        $postId = $I->havePostInDatabase();
        $commentId = $I->haveCommentInDatabase($postId);

        $I->seeCommentInDatabase(['comment_ID' => $commentId]);

        $I->dontHaveCommentInDatabase(['comment_ID' => $commentId]);

        $I->dontSeeCommentInDatabase(['comment_ID' => $commentId]);
    }

    protected function commentCountAndApproval(): array
    {
        return [
            '0-and-approved' => ['starting' => 0, 'approved' => '1', 'expected' => 1],
            '1-and-approved' => ['starting' => 1, 'approved' => '1', 'expected' => 2],
            '2-and-approved' => ['starting' => 1, 'approved' => '1', 'expected' => 2],
            '0-and-not-approved' => ['starting' => 0, 'approved' => '0', 'expected' => 0],
            '1-and-not-approved' => ['starting' => 1, 'approved' => '0', 'expected' => 1],
            '2-and-not-approved' => ['starting' => 2, 'approved' => '0', 'expected' => 2],
        ];
    }

    /**
     * It should update the comment count when approved
     *
     * @test
     *
     * @dataProvider commentCountAndApproval
     */
    public function should_update_the_comment_count_when_approved(FunctionalTester $I, \Codeception\Example $example): void
    {
        $startingCount = $example['starting'];
        $approved = $example['approved'];
        $expectedCount = $example['expected'];
        $postId = $I->havePostInDatabase(['comment_count' => $startingCount]);
        $approved = $approved ? '1' : '0';
        $I->haveManyCommentsInDatabase($startingCount, $postId, ['comment_approved' => '1']);

        $commentId = $I->haveCommentInDatabase($postId, ['comment_approved' => $approved]);

        $I->seeInDatabase($I->grabPostsTableName(), ['ID' => $postId, 'comment_count' => $expectedCount]);
        if ($approved) {
            $I->seeInDatabase($I->grabCommentsTableName(), ['comment_ID' => $commentId, 'comment_approved' => '1']);
        } else {
            $I->seeInDatabase($I->grabCommentsTableName(), ['comment_ID' => $commentId, 'comment_approved' => '0']);
        }
        $I->assertEquals(
            $expectedCount,
            $I->countRowsInDatabase($I->grabCommentsTableName(), ['comment_post_ID' => $postId, 'comment_approved' => '1'])
        );
    }
}

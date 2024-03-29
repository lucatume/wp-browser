<?php

use lucatume\WPBrowser\Generators\Date;
use lucatume\WPBrowser\Utils\Strings;

class WPDbPostCest
{

    /**
     * @var int
     */
    protected $now;

    public function _before(FunctionalTester $I): void
    {
        $this->now = time();
        Date::_injectNow($this->now);
    }

    public function _after(FunctionalTester $I): void
    {
    }

    /**
     * @test
     * it should return the post ID when inserting a post
     */
    public function it_should_return_the_post_id_when_inserting_a_post(FunctionalTester $I): void
    {
        $post_id = $I->havePostInDatabase();

        $I->assertGreaterThan(0, $post_id, "Post ID is not an int > 0; value is $post_id.");
    }

    /**
     * @test
     * it should return different ids when inserting more posts
     */
    public function it_should_return_different_ids_when_inserting_more_posts(FunctionalTester $I): void
    {
        $ids = array_map(function () use ($I) {
            return $I->havePostInDatabase();
        }, range(0, 4));

        $I->assertCount(5, array_unique($ids));
    }

    /**
     * @test
     * it should allow inserting a plain post in the database
     */
    public function it_should_allow_inserting_a_plain_post_in_the_database(FunctionalTester $I): void
    {
        $post_id = $I->havePostInDatabase();
        $table = $I->grabPostsTableName();
        $title = "Post {$post_id} title";
        $guid = $I->grabSiteUrl() . '/?p=' . $post_id;
        $now = Date::now();
        $gmtNow = Date::gmtNow();
        $defaultValues = [
            'post_author' => 1,
            'post_date' => $now,
            'post_date_gmt' => $gmtNow,
            'post_content' => "Post {$post_id} content",
            'post_title' => $title,
            'post_excerpt' => "Post {$post_id} excerpt",
            'post_status' => 'publish',
            'comment_status' => 'open',
            'ping_status' => 'open',
            'post_password' => '',
            'post_name' => Strings::slug($title),
            'to_ping' => '',
            'pinged' => '',
            'post_modified' => $now,
            'post_modified_gmt' => $gmtNow,
            'post_content_filtered' => '',
            'post_parent' => 0,
            'guid' => $guid,
            'menu_order' => 0,
            'post_type' => 'post'
        ];

        foreach ($defaultValues as $key => $value) {
            $I->seeInDatabase($table, ['ID' => $post_id, $key => $value]);
        }
    }

    /**
     * @test
     * it should allow overriding default values
     */
    public function it_should_allow_overriding_default_values(FunctionalTester $I): void
    {
        $table = $I->grabPostsTableName();
        Date::_injectNow(time() - 300);
        $now = Date::now();
        $gmtNow = Date::gmtNow();
        $overrides = [
            'post_author' => 2,
            'post_date' => $now,
            'post_date_gmt' => $gmtNow,
            'post_content' => "Post content",
            'post_title' => 'Post title',
            'post_excerpt' => "Postexcerpt",
            'post_status' => 'draft',
            'comment_status' => 'closed',
            'ping_status' => 'closed',
            'post_password' => 'foo',
            'post_name' => Strings::slug('Post title'),
            'to_ping' => 1,
            'pinged' => 1,
            'post_modified' => $now,
            'post_modified_gmt' => $gmtNow,
            'post_content_filtered' => 'Foo bar',
            'post_parent' => 23,
            'guid' => 'http://example.com/?p=122',
            'menu_order' => 3,
            'post_type' => 'antother_type'
        ];

        $post_id = $I->havePostInDatabase($overrides);

        foreach ($overrides as $key => $value) {
            $I->seeInDatabase($table, ['ID' => $post_id, $key => $value]);
        }
    }

    /**
     * @test
     * it should allow inserting many posts and return an array of ids
     */
    public function it_should_allow_inserting_many_posts_and_return_an_array_of_ids(FunctionalTester $I): void
    {
        $ids = $I->haveManyPostsInDatabase(5);

        $I->assertCount(5, array_unique($ids));
        array_map(function ($id) use ($I) {
            $I->assertIsInt($id);
        }, $ids);
    }

    /**
     * @test
     * it should allow overriding defaults when inserting many
     */
    public function it_should_allow_overriding_defaults_when_inserting_many(FunctionalTester $I): void
    {
        $table = $I->grabPostsTableName();
        Date::_injectNow(time() - 300);
        $now = Date::now();
        $gmtNow = Date::gmtNow();
        $overrides = [
            'post_author' => 2,
            'post_date' => $now,
            'post_date_gmt' => $gmtNow,
            'post_content' => "Post content",
            'post_title' => 'Post title',
            'post_excerpt' => "Postexcerpt",
            'post_status' => 'draft',
            'comment_status' => 'closed',
            'ping_status' => 'closed',
            'post_password' => 'foo',
            'post_name' => Strings::slug('Post title'),
            'to_ping' => 1,
            'pinged' => 1,
            'post_modified' => $now,
            'post_modified_gmt' => $gmtNow,
            'post_content_filtered' => 'Foo bar',
            'post_parent' => 23,
            'guid' => 'http://example.com/?p=122',
            'menu_order' => 3,
            'post_type' => 'antother_type'
        ];

        $post_ids = $I->haveManyPostsInDatabase(3, $overrides);

        foreach ($overrides as $key => $value) {
            foreach ($post_ids as $post_id) {
                $I->seeInDatabase($table, ['ID' => $post_id, $key => $value]);
            }
        }
    }

    /**
     * @test
     * it should allow overriding with n key when having many posts
     */
    public function it_should_allow_overriding_with_n_key_when_having_many_posts(FunctionalTester $I): void
    {
        $table = $I->grabPostsTableName();
        Date::_injectNow(time() - 300);
        $now = Date::now();
        $gmtNow = Date::gmtNow();
        $overrides = [
            'post_author' => '{{n}}',
            'post_date' => $now,
            'post_date_gmt' => $gmtNow,
            'post_content' => "Post content {{n}}",
            'post_title' => 'Post title {{n}}',
            'post_excerpt' => "Post excerpt {{n}}",
            'post_status' => 'draft',
            'comment_status' => 'closed',
            'ping_status' => 'closed',
            'post_password' => '{{n}}foo{{n}}',
            'post_name' => 'post-title-{{n}}',
            'to_ping' => 1,
            'pinged' => 1,
            'post_modified' => $now,
            'post_modified_gmt' => $gmtNow,
            'post_content_filtered' => 'Foo bar {{n}}{{n}}',
            'post_parent' => 23,
            'guid' => 'http://example.com/?p={{n}}&var={{n}}',
            'menu_order' => '{{n}}',
            'post_type' => 'antother_type'
        ];

        $post_ids = $I->haveManyPostsInDatabase(3, $overrides);

        foreach ($overrides as $key => $value) {
            for ($i = 0; $i < count($post_ids); $i++) {
                $post_id = $post_ids[$i];
                $processedValue = str_replace('{{n}}', $i, $value);
                $I->seeInDatabase($table, ['ID' => $post_id, $key => $processedValue]);
            }
        }
    }

    /**
     * @test
     * it should allow inserting post meta when inserting a post
     */
    public function it_should_allow_inserting_post_meta_when_inserting_a_post(FunctionalTester $I): void
    {
        $meta = ['one' => 'meta one', 'two' => 'meta two'];
        $id = $I->havePostInDatabase(['meta' => $meta]);
        $I->seeInDatabase($I->grabPostsTableName(), ['ID' => $id]);
        foreach ($meta as $meta_key => $meta_value) {
            $I->seeInDatabase($I->grabPostmetaTableName(), [
                'post_id' => $id,
                'meta_key' => $meta_key,
                'meta_value' => $meta_value
            ]);
        }
    }

    /**
     * @test
     * it should allow having meta in many posts
     */
    public function it_should_allow_having_meta_in_many_posts(FunctionalTester $I): void
    {
        $meta = ['one' => 'meta one', 'two' => 'meta two'];
        $ids = $I->haveManyPostsInDatabase(3, ['meta' => $meta]);
        for ($i = 0; $i < 3; $i++) {
            $id = $ids[$i];
            $I->seeInDatabase($I->grabPostsTableName(), ['ID' => $id]);
            foreach ($meta as $meta_key => $meta_value) {
                $I->seeInDatabase($I->grabPostmetaTableName(), [
                    'post_id' => $id,
                    'meta_key' => $meta_key,
                    'meta_value' => $meta_value
                ]);
            }
        }
    }

    /**
     * @test
     * it should allow having numbered meta for many posts
     */
    public function it_should_allow_having_numbered_meta_for_many_posts(FunctionalTester $I): void
    {
        $meta = ['one_{{n}}' => 'meta {{n}}', 'two_{{n}}' => '{{n}} meta {{n}}'];
        $ids = $I->haveManyPostsInDatabase(3, ['meta' => $meta]);
        for ($i = 0; $i < 3; $i++) {
            $id = $ids[$i];
            $I->seeInDatabase($I->grabPostsTableName(), ['ID' => $id]);
            foreach ($meta as $meta_key => $meta_value) {
                $I->seeInDatabase($I->grabPostmetaTableName(), [
                    'post_id' => $id,
                    'meta_key' => str_replace('{{n}}', $i, $meta_key),
                    'meta_value' => str_replace('{{n}}', $i, $meta_value)
                ]);
            }
        }
    }

    /**
     * @test
     * it should serialize meta value when adding array post meta
     */
    public function it_should_serialize_meta_value_when_adding_array_post_meta(FunctionalTester $I): void
    {
        $id = $I->havePostInDatabase();

        $meta = ['one', 'two', 'three'];
        $I->havePostmetaInDatabase($id, 'foo', $meta);

        $I->seeInDatabase($I->grabPostmetaTableName(), [
            'post_id' => $id,
            'meta_key' => 'foo',
            'meta_value' => serialize($meta)
        ]);
    }

    /**
     * @test
     * it should allow inserting post meta when inserting a post using meta input
     */
    public function it_should_allow_inserting_post_meta_when_inserting_a_post_using_meta_input(FunctionalTester $I): void
    {
        $meta = ['one' => 'meta one', 'two' => 'meta two'];
        $id = $I->havePostInDatabase(['meta_input' => $meta]);
        $I->seeInDatabase($I->grabPostsTableName(), ['ID' => $id]);
        foreach ($meta as $meta_key => $meta_value) {
            $I->seeInDatabase($I->grabPostmetaTableName(), [
                'post_id' => $id,
                'meta_key' => $meta_key,
                'meta_value' => $meta_value
            ]);
        }
    }

    /**
     * @test
     * it should allow having meta in many posts using meta input
     */
    public function it_should_allow_having_meta_in_many_posts_using_meta_input(FunctionalTester $I): void
    {
        $meta = ['one' => 'meta one', 'two' => 'meta two'];
        $ids = $I->haveManyPostsInDatabase(3, ['meta_input' => $meta]);
        for ($i = 0; $i < 3; $i++) {
            $id = $ids[$i];
            $I->seeInDatabase($I->grabPostsTableName(), ['ID' => $id]);
            foreach ($meta as $meta_key => $meta_value) {
                $I->seeInDatabase($I->grabPostmetaTableName(), [
                    'post_id' => $id,
                    'meta_key' => $meta_key,
                    'meta_value' => $meta_value
                ]);
            }
        }
    }

    /**
     * @test
     * it should allow having numbered meta for many posts using meta_input
     */
    public function it_should_allow_having_numbered_meta_for_many_posts_using_meta_input(FunctionalTester $I): void
    {
        $meta = ['one_{{n}}' => 'meta {{n}}', 'two_{{n}}' => '{{n}} meta {{n}}'];
        $ids = $I->haveManyPostsInDatabase(3, ['meta_input' => $meta]);
        for ($i = 0; $i < 3; $i++) {
            $id = $ids[$i];
            $I->seeInDatabase($I->grabPostsTableName(), ['ID' => $id]);
            foreach ($meta as $meta_key => $meta_value) {
                $I->seeInDatabase($I->grabPostmetaTableName(), [
                    'post_id' => $id,
                    'meta_key' => str_replace('{{n}}', $i, $meta_key),
                    'meta_value' => str_replace('{{n}}', $i, $meta_value)
                ]);
            }
        }
    }

    /**
     * It should allow getting a post meta from the database
     *
     * @test
     */
    public function should_allow_getting_a_post_meta_from_the_database(FunctionalTester $I): void
    {
        $id = $I->havePostInDatabase([
            'meta_input' => [
                'key_one' => 'single_value',
                'key_two' => ['multi_value_1', 'multi_value_2', 'multi_value_3'],
            ],
        ]);

        $I->assertEquals('single_value', $I->grabPostMetaFromDatabase($id, 'key_one', true));
        $I->assertEquals(['single_value'], $I->grabPostMetaFromDatabase($id, 'key_one', false));
        $I->assertEquals('multi_value_1', $I->grabPostMetaFromDatabase($id, 'key_two', true));
        $I->assertEquals(['multi_value_1', 'multi_value_2', 'multi_value_3'], $I->grabPostMetaFromDatabase($id, 'key_two', false));
    }
}

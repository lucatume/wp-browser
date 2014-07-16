<?php

class WPDbCest
{
    public function _before()
    {
    }

    public function _after()
    {
    }

    public function it_should_delete_comment_meta(FunctionalTester $I)
    {
        $I->wantTo('delete a comment meta');
        $table = 'wp_commentmeta';
        $I->haveInDatabase($table, [
            'meta_id' => null,
            'comment_id' => 1,
            'meta_key' => 'someKey',
            'meta_value' => 'someValue'
        ]);
        $I->seeInDatabase($table, [
            'comment_id' => 1,
            'meta_key' => 'someKey',
            'meta_value' => 'someValue'
        ]);
        $I->dontHaveCommentMetaInDatabase([
            'comment_id' => 1,
            'meta_key' => 'someKey'
        ]);
        $I->dontSeeInDatabase($table, [
            'comment_id' => 1,
            'meta_key' => 'someKey'
        ]);
    }

    public function it_should_delete_comment(FunctionalTester $I)
    {
        $I->wantTo('delete a comment');
        $table = 'wp_comments';
        $I->haveCommentInDatabase(2, 1);
        $I->seeInDatabase($table, [
            'comment_ID' => 2
        ]);
        $I->dontHaveCommentInDatabase([
            'comment_ID' => 2
        ]);
        $I->dontSeeInDatabase($table, [
            'comment_ID' => 2
        ]);
    }

    public function it_should_delete_links(FunctionalTester $I)
    {
        $I->wantTo('delete a link');
        $table = 'wp_links';
        $I->haveLinkInDatabase(13);
        $I->seeInDatabase($table, [
            'link_id' => 13
        ]);
        $I->dontHaveLinkInDatabase([
            'link_id' => 13
        ]);
        $I->dontSeeInDatabase($table, [
            'link_id' => 13
        ]);
    }

    public function it_should_delete_options(FunctionalTester $I)
    {
        $I->wantTo('delete an option');
        $table = 'wp_options';
        $I->haveOptionInDatabase('someOption', 'theValue');
        $I->seeInDatabase($table, [
            'option_name' => 'someOption',
            'option_value' => 'theValue'
        ]);
        $I->dontHaveOptionInDatabase([
            'option_name' => 'someOption',
            'option_value' => 'theValue'
        ]);
        $I->dontSeeInDatabase($table, [
            'option_name' => 'someOption',
            'option_value' => 'theValue'
        ]);
    }

    public function it_should_delete_post_meta(FunctionalTester $I)
    {
        $I->wantTo('delete a post meta');
        $table = 'wp_postmeta';
        $I->havePostMetaInDatabase(1, 'someKey', 'someValue');
        $I->seeInDatabase($table, [
            'post_id' => 1,
            'meta_key' => 'someKey',
            'meta_value' => 'someValue'
        ]);
        $I->dontHavePostMetaInDatabase([
            'post_id' => 1,
            'meta_key' => 'someKey',
            'meta_value' => 'someValue'
        ]);
        $I->dontSeeInDatabase($table, [
            'post_id' => 1,
            'meta_key' => 'someKey',
            'meta_value' => 'someValue'
        ]);
    }

    public function it_should_delete_posts(FunctionalTester $I)
    {
        $I->wantTo('delete a post');
        $table = 'wp_posts';
        $I->havePostInDatabase(13);
        $I->seeInDatabase($table, [
            'ID' => 13
        ]);
        $I->dontHavePostInDatabase([
            'ID' => 13
        ]);
        $I->dontSeeInDatabase($table, [
            'ID' => 13
        ]);
    }
}
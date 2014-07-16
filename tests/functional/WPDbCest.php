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
}
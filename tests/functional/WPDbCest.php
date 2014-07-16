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
        $I->dontHaveInDatabase($table, [
            'comment_id' => 1,
            'meta_key' => 'someKey'
        ]);
        $I->dontSeeInDatabase($table, [
            'comment_id' => 1,
            'meta_key' => 'someKey'
        ]);
    }
}
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

    public function it_should_delete_term_relationships(FunctionalTester $I)
    {
        $I->wantTo('delete a term relationship');
        $table = 'wp_term_relationships';
        $I->haveInDatabase($table, [
            'object_id' => 34,
            'term_taxonomy_id' => 12,
            'term_order' => 0
        ]);
        $I->seeInDatabase($table, [
            'term_taxonomy_id' => 12,
            'term_order' => 0
        ]);
        $I->dontHaveTermRelationshipInDatabase([
            'term_taxonomy_id' => 12,
            'term_order' => 0
        ]);
        $I->dontSeeInDatabase($table, [
            'term_taxonomy_id' => 12,
            'term_order' => 0
        ]);
    }

    public function it_should_delete_term_taxonomy(FunctionalTester $I)
    {
        $I->wantTo('delete term taxonomy relations');
        $table = 'wp_term_taxonomy';
        $I->haveInDatabase($table, [
            'term_taxonomy_id' => null,
            'term_id' => 12,
            'taxonomy' => 'category'
        ]);
        $I->seeInDatabase($table, [
            'term_id' => 12,
            'taxonomy' => 'category'
        ]);
        $I->dontHaveTermTaxonomyInDatabase([
            'term_id' => 12,
            'taxonomy' => 'category'
        ]);
        $I->dontSeeInDatabase($table, [
            'term_id' => 12,
            'taxonomy' => 'category'
        ]);
    }

    public function it_should_delete_term(FunctionalTester $I)
    {
        $I->wantTo('delete a term');
        $table = 'wp_terms';
        $I->haveTermInDatabase('someTerm', 24);
        $I->seeInDatabase($table, [
            'term_id' => 24,
            'name' => 'someTerm'
        ]);
        $I->dontHaveTermInDatabase([
            'term_id' => 24,
            'name' => 'someTerm'
        ]);
        $I->dontSeeInDatabase($table, [
            'term_id' => 24,
            'name' => 'someTerm'
        ]);
    }

    public function it_should_delete_usermeta(FunctionalTester $I)
    {
        $I->wantTo('delete a user meta');
        $table = 'wp_usermeta';
        $I->haveInDatabase($table, [
            'umeta_id' => null,
            'user_id' => 21,
            'meta_key' => 'someKey',
            'meta_value' => 'someValue'
        ]);
        $I->seeUserMetaInDatabase([
            'user_id' => 21,
            'meta_key' => 'someKey',
            'meta_value' => 'someValue'
        ]);
        $I->dontHaveUserMetaInDatabase([
            'user_id' => 21,
            'meta_key' => 'someKey',
            'meta_value' => 'someValue'
        ]);
        $I->dontSeeInDatabase($table, [
            'user_id' => 21,
            'meta_key' => 'someKey',
            'meta_value' => 'someValue'
        ]);
    }

    public function it_can_have_users(FunctionalTester $I)
    {

        $I->wantTo('have a user');
        $I->haveUserInDatabase('someUser', 23);
        $I->seeInDatabase('wp_users', [
            'ID' => 23,
            'user_login' => 'someUser'
        ]);
    }

    public function it_should_delete_users(FunctionalTester $I)
    {
        $I->wantTo('delete a user');
        $table = 'wp_users';
        $I->haveUserInDatabase('someUser', 23);
        $I->seeUserInDatabase([
            'ID' => 23,
            'user_login' => 'someUser'
        ]);
        $I->dontHaveUserInDatabase([
            'ID' => 23,
            'user_login' => 'someUser'
        ]);
        $I->dontSeeUserInDatabase([
            'ID' => 23,
            'user_login' => 'someUser'
        ]);
    }
}
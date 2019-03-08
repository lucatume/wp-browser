<?php

class WPDbBlogSubdirCest
{

    protected  $targetDb = 'multisite-subdir';

    public function _before(FunctionalTester $I)
    {
        $I->amConnectedToDatabase($this->targetDb);
    }

    /**
     * It should allow removing a blog and its tables
     *
     * @test
     */
    public function should_allow_removing_a_blog_and_its_tables(FunctionalTester $I)
    {
        $blogs = $I->grabBlogsTableName();
        $blogId = $I->grabFromDatabase($blogs, 'blog_id', ['path' => '/subdir-one/']);
        codecept_debug('Blog ID: ' . $blogId);
        $blogTablesBefore = $I->grabBlogTableNames($blogId);
        codecept_debug('Blog tables: ' . json_encode($blogTablesBefore, JSON_PRETTY_PRINT));

        $I->dontHaveBlogInDatabase(['blog_id' => $blogId], true);
        $I->dontHaveTableInDatabase($blogTablesBefore[0]);

        $I->dontSeeBlogInDatabase(['blog_id' => $blogId]);
        $blogTablesAfter = $I->grabBlogTableNames($blogId);
        $I->assertEmpty($blogTablesAfter);
    }

    /**
     * It should allow removing a blog but not its tables
     *
     * @test
     */
    public function should_allow_removing_a_blog_but_not_its_tables()
    {

    }

    /**
     * It should allow removing a blog tables and uploads
     *
     * @test
     */
    public function should_allow_removing_a_blog_tables_and_uploads()
    {

    }

    /**
     * It should allow removing a blog and its uploads but not the tables
     *
     * @test
     */
    public function should_allow_removing_a_blog_and_its_uploads_but_not_the_tables()
    {

    }

    /**
     * It should allow removing multiple blogs tables
     *
     * @test
     */
    public function should_allow_removing_multiple_blogs_tables()
    {

    }

    /**
     * It should allow removing multiple blogs tables and uploads
     *
     * @test
     */
    public function should_allow_removing_multiple_blogs_tables_and_uploads()
    {

    }
}

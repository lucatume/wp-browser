<?php

use tad\WPBrowser\Generators\Tables;

class WPDbBlogSubdirCest
{

    protected $targetDb = 'multisite-subdir';

    protected $useSubdomain = false;

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

        $I->dontSeeBlogInDatabase(['blog_id' => $blogId]);
        $blogTablesAfter = $I->grabBlogTableNames($blogId);
        $I->assertEmpty($blogTablesAfter);
    }

    /**
     * It should allow removing a blog but not its tables
     *
     * @test
     */
    public function should_allow_removing_a_blog_but_not_its_tables(FunctionalTester $I)
    {
        $blogs = $I->grabBlogsTableName();
        $blogId = $I->grabFromDatabase($blogs, 'blog_id', ['path' => '/subdir-one/']);
        codecept_debug('Blog ID: ' . $blogId);
        $blogTablesBefore = $I->grabBlogTableNames($blogId);
        codecept_debug('Blog tables: ' . json_encode($blogTablesBefore, JSON_PRETTY_PRINT));

        $I->dontHaveBlogInDatabase(['blog_id' => $blogId], false);

        $I->dontSeeBlogInDatabase(['blog_id' => $blogId]);
        $blogTablesAfter = $I->grabBlogTableNames($blogId);
        $I->assertEquals($blogTablesBefore, $blogTablesAfter);
    }

    /**
     * It should allow removing a blog tables and uploads
     *
     * @test
     */
    public function should_allow_removing_a_blog_tables_and_uploads(FunctionalTester $I)
    {
        $blogId = $I->haveBlogInDatabase('/subdir-test/', [], false);
        codecept_debug('Blog ID: ' . $blogId);
        $blogTablesBefore = $I->grabBlogTableNames($blogId);
        codecept_debug('Blog tables: ' . json_encode($blogTablesBefore, JSON_PRETTY_PRINT));
        $blogUploadsDir = $I->getBlogUploadsPath($blogId);
        codecept_debug('Blog uploads dir: ' . $blogUploadsDir);

        $I->dontHaveBlogInDatabase(['blog_id' => $blogId], true, true);

        $I->dontSeeBlogInDatabase(['blog_id' => $blogId]);
        $blogTablesAfter = $I->grabBlogTableNames($blogId);
        $I->assertEmpty($blogTablesAfter);
        $I->dontSeeFileFound($blogUploadsDir);
    }

    /**
     * It should allow removing a blog and its uploads but not the tables
     *
     * @test
     */
    public function should_allow_removing_a_blog_and_its_uploads_but_not_the_tables(FunctionalTester $I)
    {
        $blogId = $I->haveBlogInDatabase('/subdir-test/', [], false);
        codecept_debug('Blog ID: ' . $blogId);
        $blogTablesBefore = $I->grabBlogTableNames($blogId);
        codecept_debug('Blog tables: ' . json_encode($blogTablesBefore, JSON_PRETTY_PRINT));
        $blogUploadsDir = $I->getBlogUploadsPath($blogId);
        codecept_debug('Blog uploads dir: ' . $blogUploadsDir);

        $I->dontHaveBlogInDatabase(['blog_id' => $blogId], false, true);

        $I->dontSeeBlogInDatabase(['blog_id' => $blogId]);
        $blogTablesAfter = $I->grabBlogTableNames($blogId);
        $I->assertEquals($blogTablesBefore, $blogTablesAfter);
        $I->dontSeeFileFound($blogUploadsDir);
    }

    /**
     * It should allow removing multiple blogs tables
     *
     * @test
     */
    public function should_allow_removing_multiple_blogs_tables(FunctionalTester $I)
    {
        $blogIds = $I->haveManyBlogsInDatabase(3, [], false);
        codecept_debug('Blog IDs: ' . json_encode($blogIds, JSON_PRETTY_PRINT));
        codecept_debug('Blog domains and paths: ' . json_encode(array_map(function ($blogId) use ($I) {
                return [
                    'domain' => $I->grabBlogDomain($blogId),
                    'path' => $I->grabBlogPath($blogId),
                ];
        }, $blogIds), JSON_PRETTY_PRINT));
        $blogTablesBefore = array_combine($blogIds, array_map(function ($blogId) use ($I) {
            return $I->grabBlogTableNames($blogId);
        }, $blogIds));
        codecept_debug('Blog tables: ' . json_encode($blogTablesBefore, JSON_PRETTY_PRINT));
        $blogUploadsDir = array_combine($blogIds, array_map(function ($blogId) use ($I) {
            return $I->getBlogUploadsPath($blogId);
        }, $blogIds));
        codecept_debug('Blog uploads dir: ' . json_encode($blogUploadsDir, JSON_PRETTY_PRINT));

        $I->dontHaveBlogInDatabase(['domain' => $I->getSiteDomain()], true, false);

        foreach ($blogIds as $blogId) {
            $I->dontSeeBlogInDatabase(['blog_id' => $blogId]);
            $blogTablesAfter = $I->grabBlogTableNames($blogId);
            $I->assertEmpty($blogTablesAfter);
            $I->seeFileFound($blogUploadsDir[$blogId]);
        }
    }

    /**
     * It should allow removing multiple blogs tables and uploads
     *
     * @test
     */
    public function should_allow_removing_multiple_blogs_tables_and_uploads(FunctionalTester $I)
    {
        $blogIds = $I->haveManyBlogsInDatabase(3, [], false);
        codecept_debug('Blog IDs: ' . json_encode($blogIds, JSON_PRETTY_PRINT));
        codecept_debug('Blog domains and paths: ' . json_encode(array_map(function ($blogId) use ($I) {
                return [
                    'domain' => $I->grabBlogDomain($blogId),
                    'path' => $I->grabBlogPath($blogId),
                ];
        }, $blogIds), JSON_PRETTY_PRINT));
        $blogTablesBefore = array_combine($blogIds, array_map(function ($blogId) use ($I) {
            $tables = $I->grabBlogTableNames($blogId);
            sort($tables);
            return $tables;
        }, $blogIds));
        codecept_debug('Blog tables: ' . json_encode($blogTablesBefore, JSON_PRETTY_PRINT));
        $blogUploadsDir = array_combine($blogIds, array_map(function ($blogId) use ($I) {
            return $I->getBlogUploadsPath($blogId);
        }, $blogIds));
        codecept_debug('Blog uploads dir: ' . json_encode($blogUploadsDir, JSON_PRETTY_PRINT));

        $I->dontHaveBlogInDatabase(['domain' => $I->getSiteDomain()], true, true);

        foreach ($blogIds as $blogId) {
            $I->dontSeeBlogInDatabase(['blog_id' => $blogId]);
            $blogTablesAfter = $I->grabBlogTableNames($blogId);
            $I->assertEmpty($blogTablesAfter);
            $I->dontSeeFileFound($blogUploadsDir[$blogId]);
        }
    }

    /**
     * It should allow having a blog in the database with tables and filesystem
     *
     * @test
     */
    public function should_allow_having_a_blog_in_the_database_with_tables_and_filesystem(FunctionalTester $I)
    {
        $blogId = $I->haveBlogInDatabase('test', [], $this->useSubdomain);
        codecept_debug('Blog ID: ' . $blogId);

        $blogTables = $I->grabBlogTableNames($blogId);
        sort($blogTables);
        codecept_debug('Blog tables: ' . json_encode($blogTables, JSON_PRETTY_PRINT));

        $tablePrefix = $I->grabBlogTablePrefix($blogId);
        $expectedTables = array_map(function ($table) use ($tablePrefix) {
            return $tablePrefix . $table;
        }, Tables::blogTables());
        sort($expectedTables);
        $I->assertEquals($expectedTables, $blogTables);
        $I->assertFileExists($I->getBlogUploadsPath($blogId));
    }
}

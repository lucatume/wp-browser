<?php

    class WPDbTablePrefixCest
    {
        public function _before(FunctionalTester $I)
        {
        }

        public function _after(FunctionalTester $I)
        {
        }

        public function tables()
        {
            return [
                'commentmeta',
                'comments',
                'links',
                'options',
                'postmeta',
                'posts',
                'term_relationships',
                'term_taxonomy',
                'terms',
            ];
        }

        /**
         * @test
         * it should allow using a secondary blog table
         */
        public function it_should_allow_using_a_secondary_blog_table(FunctionalTester $I, $scenario)
        {

            foreach ($this->tables() as $table) {
                $I->useMainBlog();
                $tableName = $I->grabPrefixedTableNameFor($table);
                $I->useBlog(2);
                $secondaryTableName = $I->grabPrefixedTableNameFor($table);

                $I->assertNotEquals($tableName, $secondaryTableName);
                $I->assertRegExp("/^.*_2_{$table}\$/", $secondaryTableName);
            }
        }
    }

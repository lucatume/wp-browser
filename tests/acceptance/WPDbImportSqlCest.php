<?php

use Codeception\Exception\ModuleException;

class WPDbImportSqlCest
{
    /**
     * It should support importing a SQL string
     *
     * @test
     */
    public function should_support_importing_a_sql_string(AcceptanceTester $I): void
    {
        $sqlString = "INSERT INTO wp_postmeta SET post_id = 23, meta_key = '_from_dump', meta_value='test test test';";

        $I->importSql([ $sqlString ]);

        $I->seePostMetaInDatabase([ 'post_id' => 23, 'meta_key' => '_from_dump', 'meta_value' => 'test test test' ]);
    }

    /**
     * It should roll-back SQL string imports between tests
     *
     * @test
     */
    public function should_roll_back_sql_string_imports_between_tests(AcceptanceTester $I): void
    {
        $I->dontSeePostMetaInDatabase([ 'post_id' => 23, 'meta_key' => '_from_dump', 'meta_value' => 'test test test' ]);
    }

    /**
     * It should allow importing multiple SQL strings
     *
     * @test
     */
    public function should_allow_importing_multiple_sql_strings(AcceptanceTester  $I): void
    {
        $sqlStrings = [
            "INSERT INTO wp_postmeta SET post_id = 89, meta_key = '_from_dump_23', meta_value='test test test';",
            "INSERT INTO wp_postmeta SET post_id = 2389, meta_key = '_from_dump_2389', meta_value='test test test';",
            "INSERT INTO wp_postmeta SET post_id = 89, meta_key = '_from_dump_89', meta_value='test test test';",
        ];

        $I->importSql($sqlStrings);

        $I->dontSeePostMetaInDatabase([ 'post_id' => 23, 'meta_key' => '_from_dump_23', 'meta_value' => 'test test test' ]);
        $I->seePostMetaInDatabase([ 'post_id' => 2389, 'meta_key' => '_from_dump_2389', 'meta_value' => 'test test test' ]);
        $I->seePostMetaInDatabase([ 'post_id' => 89, 'meta_key' => '_from_dump_89', 'meta_value' => 'test test test' ]);
    }

    /**
     * It should throw if SQL code is not valid
     *
     * @test
     */
    public function should_throw_if_sql_code_is_not_valid(AcceptanceTester $I): void
    {
        $sqlString = "INSERT INTO florb zap;";

        $I->expectThrowable(ModuleException::class, static function () use ($I, $sqlString) {
            $I->importSql([ $sqlString ]);
        });
    }
}

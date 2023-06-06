<?php

class WPDbTableCest
{
    /**
     * It should allow removing database tables
     *
     * @test
     */
    public function should_allow_removing_database_tables(FunctionalTester $I): void
    {
        $options = $I->grabPrefixedTableNameFor('options');
        $I->seeTableInDatabase($options);

        $I->dontHaveTableInDatabase($options);

        $I->dontSeeTableInDatabase($options);
    }

    /**
     * It should not throw if trying to remove a non existing table.
     *
     * @test
     */
    public function should_not_throw_if_trying_to_remove_a_non_existing_table_(FunctionalTester $I): void
    {
        $options = $I->grabPrefixedTableNameFor('not_existing');
        $I->dontHaveTableInDatabase($options);
    }
}

<?php
use Wploader_wpdb_interactionTester as Tester;

class LoadingCest
{

    /**
     * It should be able to load WordPress correctly in concert with other MysqlDatabase modules
     *
     * @test
     */
    public function should_be_able_to_load_word_press_correctly_in_concert_with_other_db_modules(Tester $I): void
    {
        $I->assertTrue(function_exists('wp'));
    }
}

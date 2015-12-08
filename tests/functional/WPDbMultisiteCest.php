<?php
use tad\WPBrowser\Generators\Tables;

class WPDbMultisiteCest {

	/**
	 * @var Tables
	 */
	protected $tables;

	public function _before( FunctionalTester $I ) {
		$this->tables = new Tables();
	}

	public function _after( FunctionalTester $I ) {
	}

	/**
	 * @test
	 * it should scaffold base tables
	 */
	public function it_should_scaffold_base_tables_if_not( FunctionalTester $I ) {
		$I->haveMultisiteInDatabase();

		$I->seeTableInDatabase( $I->grabBlogsTableName() );
		$I->seeTableInDatabase( $I->grabBlogVersionsTableName() );
		$I->seeTableInDatabase( $I->grabSiteMetaTableName() );
		$I->seeTableInDatabase( $I->grabSiteTableName() );
		$I->seeTableInDatabase( $I->grabSignupsTableName() );
		$I->seeTableInDatabase( $I->grabRegistrationLogTableName() );
	}

	/**
	 * @test
	 * it should alter the users table
	 */
	public function it_should_alter_the_user_table( FunctionalTester $I ) {
		$first  = $I->haveMultisiteInDatabase();
		$second = $I->haveMultisiteInDatabase();

		foreach ( $second as $table => $output ) {
			$I->assertEquals( 'alter', $output['operation'] );
			$I->assertEquals( $table == 'users', $output['exit'] );
		}
	}


}

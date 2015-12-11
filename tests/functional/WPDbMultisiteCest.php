<?php
use tad\WPBrowser\Generators\Date;
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

	/**
	 * @test
	 * it should add the main site address by default
	 */
	public function it_should_add_the_main_site_address_by_default( FunctionalTester $I ) {
		$I->haveMultisiteInDatabase();
		$I->seeInDatabase( $I->grabSiteTableName(), [ 'domain' => $I->getSiteDomain(), 'path' => '/' ] );
	}

	/**
	 * @test
	 * it should allow having blogs in the database in subdomain multisite installation
	 */
	public function it_should_allow_having_blogs_in_the_database_in_subdomain_multisite_installation( FunctionalTester $I ) {
		$now = time();
		Date::_injectNow( $now );
		$I->haveMultisiteInDatabase();
		$blog1Id = $I->haveBlogInDatabase( 'test1' );
		$blog2Id = $I->haveBlogInDatabase( 'test2' );

		$I->seeBlogInDatabase( [
			'blog_id'      => $blog1Id,
			'site_id'      => 1,
			'domain'       => 'test1.' . $I->getSiteDomain(),
			'path'         => '/',
			'registered'   => date( Date::DATE_FORMAT, $now ),
			'last_updated' => date( Date::DATE_FORMAT, $now ),
			'public'       => 1,
			'archived'     => 0,
			'mature'       => 0,
			'spam'         => 0,
			'deleted'      => 0,
			'lang_id'      => 0
		] );
		$I->seeBlogInDatabase( [
			'blog_id'      => $blog2Id,
			'site_id'      => 1,
			'domain'       => 'test2.' . $I->getSiteDomain(),
			'path'         => '/',
			'registered'   => date( Date::DATE_FORMAT, $now ),
			'last_updated' => date( Date::DATE_FORMAT, $now ),
			'public'       => 1,
			'archived'     => 0,
			'mature'       => 0,
			'spam'         => 0,
			'deleted'      => 0,
			'lang_id'      => 0
		] );
	}

	/**
	 * @test
	 * it should allow having blogs in the database in subfolder multisite installation
	 */
	public function it_should_allow_having_blogs_in_the_database_in_subfolder_multisite_installation( FunctionalTester $I ) {
		$now = time();
		Date::_injectNow( $now );
		$I->haveMultisiteInDatabase( false );
		$blog1Id = $I->haveBlogInDatabase( 'test1' );
		$blog2Id = $I->haveBlogInDatabase( 'test2' );

		$I->seeBlogInDatabase( [
			'blog_id'      => $blog1Id,
			'site_id'      => 1,
			'domain'       => $I->getSiteDomain(),
			'path'         => '/test1/',
			'registered'   => date( Date::DATE_FORMAT, $now ),
			'last_updated' => date( Date::DATE_FORMAT, $now ),
			'public'       => 1,
			'archived'     => 0,
			'mature'       => 0,
			'spam'         => 0,
			'deleted'      => 0,
			'lang_id'      => 0
		] );
		$I->seeBlogInDatabase( [
			'blog_id'      => $blog2Id,
			'site_id'      => 1,
			'domain'       => $I->getSiteDomain(),
			'path'         => '/test2/',
			'registered'   => date( Date::DATE_FORMAT, $now ),
			'last_updated' => date( Date::DATE_FORMAT, $now ),
			'public'       => 1,
			'archived'     => 0,
			'mature'       => 0,
			'spam'         => 0,
			'deleted'      => 0,
			'lang_id'      => 0
		] );
	}

	/**
	 * @test
	 * it should allow overriding defaults when having blogs
	 */
	public function it_should_allow_overriding_defaults_when_having_blogs( FunctionalTester $I ) {
		$now = time() - 3600;
		Date::_injectNow( $now );
		$I->haveMultisiteInDatabase();
		$overrides = [
			'site_id'      => 2,
			'domain'       => 'test1.something.else',
			'path'         => '/other/folder',
			'registered'   => date( Date::DATE_FORMAT, $now ),
			'last_updated' => date( Date::DATE_FORMAT, $now ),
			'public'       => 0,
			'archived'     => 1,
			'mature'       => 1,
			'spam'         => 1,
			'deleted'      => 1,
			'lang_id'      => 3
		];

		$blogId = $I->haveBlogInDatabase( 'test1', $overrides );

		foreach ( $overrides as $key => $value ) {
			$I->seeBlogInDatabase( [ 'blog_id' => $blogId, $key => $value ] );
		}
	}

	/**
	 * @test
	 * it should allow having many blogs
	 */
	public function it_should_allow_having_many_blogs( FunctionalTester $I ) {
		$I->haveMultisiteInDatabase();
		$blogIds = $I->haveManyBlogsInDatabase( 5 );

		for ( $i = 0; $i < 5; $i++ ) {
			$I->seeBlogInDatabase( [ 'blog_id' => $blogIds[$i], 'domain' => "blog{$i}.{$I->getSiteDomain()}" ] );
		}
	}

	/**
	 * @test
	 * it should replace number placeholder when inserting many blogs
	 */
	public function it_should_replace_number_placeholder_when_inserting_many_blogs( FunctionalTester $I ) {
		$I->haveMultisiteInDatabase();
		$blogIds = $I->haveManyBlogsInDatabase( 5, [
			'domain' => "{{n}}_blog_{{n}}.{$I->getSiteDomain()}",
			'path'   => '/blog_{{n}}/'
		] );

		for ( $i = 0; $i < 5; $i++ ) {
			$I->seeBlogInDatabase( [
				'blog_id' => $blogIds[$i],
				'domain'  => "{$i}_blog_{$i}.{$I->getSiteDomain()}",
				'path'    => "/blog_{$i}/"
			] );
		}
	}

	/**
	 * @test
	 * it should allow not to have blog in the database
	 */
	public function it_should_allow_not_to_have_blog_in_the_database( FunctionalTester $I ) {
		$I->haveMultisiteInDatabase();

		$id = $I->haveBlogInDatabase( 'foo' );

		$I->dontHaveBlogInDatabase( [ 'blog_id' => $id ] );

		$I->dontSeeBlogInDatabase( [ 'blog_id' => $id ] );
	}

	/**
	 * @test
	 * it should scaffold new blog tables
	 */
	public function it_should_scaffold_new_blog_tables( FunctionalTester $I ) {
		$I->haveMultisiteInDatabase();
		$id = $I->haveBlogInDatabase( 'test' );

		$I->useBlog( $id );
		foreach ( Tables::newBlogTables() as $table ) {
			$I->seeTableInDatabase( $I->grabPrefixedTableNameFor( $table ) );
		}
	}
}

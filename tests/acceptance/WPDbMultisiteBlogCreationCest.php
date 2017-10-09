<?php

use tad\WPBrowser\Generators\Date;
use tad\WPBrowser\Generators\Tables;

class WPDbMultisiteBlogCreationCest {

	/**
	 * @var Tables
	 */
	protected $tables;

	public function _before(FunctionalTester $I) {
		$this->tables = new Tables();
	}

	public function _after(FunctionalTester $I) {
	}

	/**
	 * @test
	 * it should allow having blogs in the database in subdomain multisite installation
	 */
	public function it_should_allow_having_blogs_in_the_database_in_subdomain_multisite_installation(FunctionalTester $I) {
		$now = time();
		Date::_injectNow($now);
		$blog1Id = $I->haveBlogInDatabase('testsite1', [], true);
		$blog2Id = $I->haveBlogInDatabase('testsite2', [], true);

		$I->seeBlogInDatabase([
			'blog_id'      => $blog1Id,
			'site_id'      => 1,
			'domain'       => 'testsite1.' . $I->getSiteDomain(),
			'path'         => '/',
			'registered'   => date(Date::DATE_FORMAT, $now),
			'last_updated' => date(Date::DATE_FORMAT, $now),
			'public'       => 1,
			'archived'     => 0,
			'mature'       => 0,
			'spam'         => 0,
			'deleted'      => 0,
			'lang_id'      => 0,
		]);
		$I->seeBlogInDatabase([
			'blog_id'      => $blog2Id,
			'site_id'      => 1,
			'domain'       => 'testsite2.' . $I->getSiteDomain(),
			'path'         => '/',
			'registered'   => date(Date::DATE_FORMAT, $now),
			'last_updated' => date(Date::DATE_FORMAT, $now),
			'public'       => 1,
			'archived'     => 0,
			'mature'       => 0,
			'spam'         => 0,
			'deleted'      => 0,
			'lang_id'      => 0,
		]);
	}

	/**
	 * @test
	 * it should allow having blogs in the database in subfolder multisite installation
	 */
	public function it_should_allow_having_blogs_in_the_database_in_subfolder_multisite_installation(FunctionalTester $I) {
		$now = time();
		Date::_injectNow($now);
		$blog1Id = $I->haveBlogInDatabase('testsite1', [], false);
		$blog2Id = $I->haveBlogInDatabase('testsite2', [], false);

		$I->seeBlogInDatabase([
			'blog_id'      => $blog1Id,
			'site_id'      => 1,
			'domain'       => $I->getSiteDomain(),
			'path'         => '/testsite1/',
			'registered'   => date(Date::DATE_FORMAT, $now),
			'last_updated' => date(Date::DATE_FORMAT, $now),
			'public'       => 1,
			'archived'     => 0,
			'mature'       => 0,
			'spam'         => 0,
			'deleted'      => 0,
			'lang_id'      => 0,
		]);
		$I->seeBlogInDatabase([
			'blog_id'      => $blog2Id,
			'site_id'      => 1,
			'domain'       => $I->getSiteDomain(),
			'path'         => '/testsite2/',
			'registered'   => date(Date::DATE_FORMAT, $now),
			'last_updated' => date(Date::DATE_FORMAT, $now),
			'public'       => 1,
			'archived'     => 0,
			'mature'       => 0,
			'spam'         => 0,
			'deleted'      => 0,
			'lang_id'      => 0,
		]);
	}

	/**
	 * @test
	 * it should allow overriding defaults when having blogs
	 */
	public function it_should_allow_overriding_defaults_when_having_blogs(FunctionalTester $I) {
		$now = time() - 3600;
		Date::_injectNow($now);
		$overrides = [
			'site_id'      => 2,
			'domain'       => 'testsite1.something.else',
			'path'         => '/other/folder',
			'registered'   => date(Date::DATE_FORMAT, $now),
			'last_updated' => date(Date::DATE_FORMAT, $now),
			'public'       => 0,
			'archived'     => 1,
			'mature'       => 1,
			'spam'         => 1,
			'deleted'      => 1,
			'lang_id'      => 3,
		];

		$blogId = $I->haveBlogInDatabase('testsite1', $overrides, false);

		foreach ($overrides as $key => $value) {
			$I->seeBlogInDatabase(['blog_id' => $blogId, $key => $value]);
		}
	}

	/**
	 * @test
	 * it should replace number placeholder when inserting many blogs
	 */
	public function it_should_replace_number_placeholder_when_inserting_many_blogs(FunctionalTester $I) {
		$blogIds = $I->haveManyBlogsInDatabase(5, [
			'domain' => "{{n}}_blog_{{n}}.{$I->getSiteDomain()}",
			'path'   => '/blog_{{n}}/',
		], false);

		for ($i = 0; $i < 5; $i++) {
			$I->seeBlogInDatabase([
				'blog_id' => $blogIds[$i],
				'domain'  => "{$i}_blog_{$i}.{$I->getSiteDomain()}",
				'path'    => "/blog_{$i}/",
			]);
		}
	}

	/**
	 * @test
	 * it should allow not to have blog in the database
	 */
	public function it_should_allow_not_to_have_blog_in_the_database(FunctionalTester $I) {
		$id = $I->haveBlogInDatabase('foo', [], false);

		$I->dontHaveBlogInDatabase(['blog_id' => $id]);

		$I->dontSeeBlogInDatabase(['blog_id' => $id]);
	}

	/**
	 * @test
	 * it should scaffold new blog tables
	 */
	public function it_should_scaffold_new_blog_tables(FunctionalTester $I) {
		$id = $I->haveBlogInDatabase('testsite', [], false);

		$I->useBlog($id);
		foreach (Tables::newBlogTables() as $table) {
			$I->seeTableInDatabase($I->grabPrefixedTableNameFor($table));
		}
	}
}
<?php

use tad\WPBrowser\Generators\User\Roles;

class WPDbUserCest
{

	protected $rolesAndLevels = [
		'subscriber' => 0,
		'contributor' => 1,
		'author' => 2,
		'editor' => 7,
		'administrator' => 10,
		'' => 0 // no role for the site
	];

	public function _before(FunctionalTester $I)
	{
	}

	public function _after(FunctionalTester $I)
	{
	}

	/**
	 * @test
	 * it should insert a user in the database
	 */
	public function it_should_insert_a_user_in_the_database(FunctionalTester $I)
	{
		$I->wantTo('insert a user in the database with generated defaults');
		$id = $I->haveUserInDatabase('Luca');
		$criteria = [
			'user_login' => 'luca',
			'user_pass' => 'Luca',
			'user_nicename' => 'Luca',
			'user_email' => 'luca@example.com',
			'user_url' => 'http://luca.example.com',
			'user_status' => 0,
			'user_activation_key' => '',
			'display_name' => 'Luca',
		];

		foreach ($criteria as $key => $value) {
			$I->seeUserInDatabase(['ID' => $id, $key => $value]);
		}
	}

	/**
	 * @test
	 * it should insert a user in the database allowing for user defaults override
	 */
	public function it_should_insert_a_user_in_the_database_allowing_for_user_defaults_override(FunctionalTester $I)
	{
		$I->wantTo('insert a user in the database overriding defaults');
		$table = $I->grabPrefixedTableNameFor('users');
		$overrides = [
			'user_pass' => 'luca12345678',
			'user_nicename' => 'lucatume',
			'user_email' => 'luca@theaveragedev.com',
			'user_url' => 'http://theaveragedev.com',
			'user_status' => 1,
			'user_activation_key' => 'foo',
			'display_name' => 'theAverageDev',
		];
		$I->haveUserInDatabase('Luca', 'subscriber', $overrides);

		$I->seeUserInDatabase($overrides);
	}

	/**
	 * @test
	 * it should set a user role to subscriber by default
	 */
	public function it_should_set_a_user_role_to_subscriber_by_default(FunctionalTester $I)
	{
		$I->haveUserInDatabase('Luca');
		$userId = $I->grabUserIdFromDatabase('Luca');
		$criteria = [
			'user_id' => $userId,
			'meta_key' => $I->grabPrefixedTableNameFor('capabilities'),
			'meta_value' => serialize(['subscriber' => 1]),
		];
		$I->seeUserMetaInDatabase($criteria);
	}

	/**
	 * @test
	 * it should set the user level to 1 by default
	 */
	public function it_should_set_the_user_level_to_0_by_default(FunctionalTester $I)
	{
		$I->haveUserInDatabase('Luca');
		$userId = $I->grabUserIdFromDatabase('Luca');
		$criteria = [
			'user_id' => $userId,
			'meta_key' => $I->grabPrefixedTableNameFor('user_level'),
			'meta_value' => 0,
		];
		$I->seeUserMetaInDatabase($criteria);
	}

	/**
	 * @test
	 * it should allow overriding the default user level and role
	 */
	public function it_should_allow_overriding_the_default_user_level_and_role(FunctionalTester $I)
	{
		foreach ($this->rolesAndLevels as $role => $level) {
			$I->haveUserInDatabase('Luca' . $role, $role);
			$userId = $I->grabUserIdFromDatabase('Luca' . $role);
			$I->seeUserMetaInDatabase([
				'user_id' => $userId,
				'meta_key' => $I->grabPrefixedTableNameFor('capabilities'),
				'meta_value' => serialize([$role => 1]),
			]);
			$I->seeUserMetaInDatabase([
				'user_id' => $userId,
				'meta_key' => $I->grabPrefixedTableNameFor('user_level'),
				'meta_value' => $level,
			]);
		}
	}

	/**
	 * @test
	 * it should allow setting the user role on a blog base
	 */
	public function it_should_allow_setting_the_user_role_on_a_blog_base(FunctionalTester $I)
	{
		$blogIdsAndRoles = array_keys($this->rolesAndLevels);
		$I->haveUserInDatabase('Luca', $blogIdsAndRoles);
		$userId = $I->grabUserIdFromDatabase('Luca');
		foreach ($blogIdsAndRoles as $blogId => $msRole) {
			$msLevel = Roles::getLevelForRole($msRole);
			$blogIdAndPrefix = $blogId == 0 ? '' : $blogId . '_';
			$I->seeUserMetaInDatabase([
				'user_id' => $userId,
				'meta_key' => $I->grabPrefixedTableNameFor($blogIdAndPrefix . 'capabilities'),
				'meta_value' => serialize([$msRole => 1]),
			]);
			$I->seeUserMetaInDatabase([
				'user_id' => $userId,
				'meta_key' => $I->grabPrefixedTableNameFor($blogIdAndPrefix . 'user_level'),
				'meta_value' => $msLevel,
			]);
		}
	}

	/**
	 * @test
	 * it should allow setting a user meta value
	 */
	public function it_should_allow_setting_a_user_meta_value(FunctionalTester $I)
	{
		$I->haveUserInDatabase('Luca');
		$userId = $I->grabUserIdFromDatabase('Luca');
		$I->haveUserMetaInDatabase($userId, 'foo', 'bar');
		$table = $I->grabPrefixedTableNameFor('usermeta');
		$I->seeInDatabase($table, ['user_id' => $userId, 'meta_key' => 'foo', 'meta_value' => 'bar']);
	}

	/**
	 * @test
	 * it should serialize object user meta in database
	 */
	public function it_should_serialize_object_user_meta_in_database(FunctionalTester $I)
	{
		$I->haveUserInDatabase('Luca');
		$userId = $I->grabUserIdFromDatabase('Luca');
		$meta = (object)['foo' => 'bar', 'one' => 23];
		$I->haveUserMetaInDatabase($userId, 'foo', $meta);
		$table = $I->grabPrefixedTableNameFor('usermeta');
		$I->seeInDatabase($table, ['user_id' => $userId, 'meta_key' => 'foo', 'meta_value' => serialize($meta)]);
	}

	/**
	 * @test
	 * it should allow seeing user in database
	 */
	public function it_should_allow_seeing_user_in_database(FunctionalTester $I)
	{
		$I->haveUserInDatabase('Luca');
		$I->seeUserInDatabase(['user_login' => 'Luca']);
		$userId = $I->grabUserIdFromDatabase('Luca');
		$I->seeUserInDatabase(['ID' => $userId]);
		$I->dontSeeUserInDatabase(['ID' => 123]);
		$I->dontSeeUserInDatabase(['user_login' => 'Bar']);
	}

	/**
	 * @test
	 * it should allow seeing a user meta in the database
	 */
	public function it_should_allow_seeing_a_user_meta_in_the_database(FunctionalTester $I)
	{
		$I->haveUserInDatabase('Luca');
		$userId = $I->grabUserIdFromDatabase('Luca');
		$I->haveUserMetaInDatabase($userId, 'foo', 23);
		$I->seeUserMetaInDatabase(['user_id' => $userId, 'meta_key' => 'foo']);
		$I->dontSeeUserMetaInDatabase(['user_id' => $userId, 'meta_key' => 'bar']);
	}

	/**
	 * @test
	 * it should remove a user from the database
	 */
	public function it_should_remove_a_user_from_the_database(FunctionalTester $I)
	{
		$I->haveUserInDatabase('Luca');
		$userId = $I->grabUserIdFromDatabase('Luca');
		$I->haveUserMetaInDatabase($userId, 'foo', 'bar');

		$I->dontHaveUserInDatabase($userId);

		$I->dontSeeUserInDatabase(['ID' => $userId]);
		$I->dontSeeUserMetaInDatabase(['user_id' => $userId, 'meta_key' => 'foo']);
		$I->dontSeeUserMetaInDatabase([
			'user_id' => $userId,
			'meta_key' => $I->grabPrefixedTableNameFor() . 'capabilities'
		]);
		$I->dontSeeUserMetaInDatabase([
			'user_id' => $userId,
			'meta_key' => $I->grabPrefixedTableNameFor() . 'user_level'
		]);
	}

	/**
	 * @test
	 * it should allow deleting a user by login
	 */
	public function it_should_allow_deleting_a_user_by_login(FunctionalTester $I)
	{
		$I->haveUserInDatabase('Luca');
		$userId = $I->grabUserIdFromDatabase('Luca');
		$I->haveUserMetaInDatabase($userId, 'foo', 'bar');

		$I->dontHaveUserInDatabase('Luca');

		$I->dontSeeUserInDatabase(['ID' => $userId]);
		$I->dontSeeUserMetaInDatabase(['user_id' => $userId, 'meta_key' => 'foo']);
		$I->dontSeeUserMetaInDatabase([
			'user_id' => $userId,
			'meta_key' => $I->grabPrefixedTableNameFor() . 'capabilities'
		]);
		$I->dontSeeUserMetaInDatabase([
			'user_id' => $userId,
			'meta_key' => $I->grabPrefixedTableNameFor() . 'user_level'
		]);
	}

	/**
	 * @test
	 * it should allow deleting a user meta
	 */
	public function it_should_allow_deleting_a_user_meta(FunctionalTester $I)
	{
		$I->haveUserInDatabase('Luca');
		$userId = $I->grabUserIdFromDatabase('Luca');
		$I->haveUserMetaInDatabase($userId, 'foo', 'bar');
		$I->seeUserMetaInDatabase(['user_id' => $userId, 'meta_key' => 'foo']);

		$I->dontHaveUserMetaInDatabase(['user_id' => $userId]);

		$I->dontSeeUserMetaInDatabase(['user_id' => $userId, 'meta_key' => 'foo']);
	}

	/**
	 * @test
	 * it should not throw if trying to delete non existing user
	 */
	public function it_should_not_throw_if_trying_to_delete_non_existing_user(FunctionalTester $I)
	{
		$I->dontHaveUserInDatabase(23);
		$I->dontHaveUserInDatabase('Foo');
	}

	/**
	 * @test
	 * it should not throw if trying to delete non existing meta in database
	 */
	public function it_should_not_throw_if_trying_to_delete_non_existing_meta_in_database(FunctionalTester $I)
	{
		$I->haveUserInDatabase('Luca');
		$userId = $I->grabUserIdFromDatabase('Luca');

		$I->dontHaveUserMetaInDatabase(['user_id' => $userId, 'meta_key' => 'baz']);
	}

	/**
	 * @test
	 * it should allow adding multiple user meta
	 */
	public function it_should_allow_adding_multiple_user_meta(FunctionalTester $I)
	{
		$I->haveUserInDatabase('Luca');
		$userId = $I->grabUserIdFromDatabase('Luca');

		$I->haveUserMetaInDatabase($userId, 'some_key', 'one');
		$I->haveUserMetaInDatabase($userId, 'some_key', 'two');
		$I->haveUserMetaInDatabase($userId, 'some_key', 'three');

		$meta = $I->grabUserMetaFromDatabase($userId, 'some_key');

		$I->assertEquals(3, count($meta));
		$I->assertEquals('one', $meta[0]);
		$I->assertEquals('two', $meta[1]);
		$I->assertEquals('three', $meta[2]);
	}

	/**
	 * @test
	 * it should allow grabbing a user unique meta
	 */
	public function it_should_allow_grabbing_a_user_unique_meta(FunctionalTester $I)
	{
		$I->haveUserInDatabase('Luca');
		$userId = $I->grabUserIdFromDatabase('Luca');
		$I->haveUserMetaInDatabase($userId, 'some_key', 'some_value');

		$meta = $I->grabUserMetaFromDatabase($userId, 'some_key');

		$I->assertEquals(1, count($meta));
		$I->assertEquals('some_value', $meta[0]);
	}

	/**
	 * @test
	 * it should remove all user meta
	 */
	public function it_should_remove_all_user_meta(FunctionalTester $I)
	{
		$I->haveUserInDatabase('Luca');
		$userId = $I->grabUserIdFromDatabase('Luca');

		$I->haveUserMetaInDatabase($userId, 'some_key', 'one');
		$I->haveUserMetaInDatabase($userId, 'some_key', 'two');
		$I->haveUserMetaInDatabase($userId, 'some_key', 'three');

		$meta = $I->dontHaveUserMetaInDatabase(['user_id' => $userId, 'meta_key' => 'some_key']);

		$table = $I->grabPrefixedTableNameFor('usermeta');
		$I->dontSeeInDatabase($table, [
			'user_id' => $userId,
			'meta_key' => 'some_key'
		]);
	}

	/**
	 * @test
	 * it should allow having many users in the database
	 */
	public function it_should_allow_having_many_users_in_the_database(FunctionalTester $I)
	{
		$ids = $I->haveManyUsersInDatabase(5, 'user');

		for ($i = 0; $i < 5; $i++) {
			$I->assertTrue(is_int($ids[$i]));
			$I->seeUserInDatabase(['ID' => $ids[$i], 'user_login' => 'user_' . $i]);
		}
	}

	/**
	 * @test
	 * it should replace number placeholder when having many users
	 */
	public function it_should_replace_number_placeholder_when_having_many_users(FunctionalTester $I)
	{
		$ids = $I->haveManyUsersInDatabase(5, 'user_{{n}}_login');

		for ($i = 0; $i < 5; $i++) {
			$I->assertTrue(is_int($ids[$i]));
			$I->seeUserInDatabase(['ID' => $ids[$i], 'user_login' => 'user_' . $i . '_login']);
		}
	}

	/**
	 * @test
	 * it should allow having user meta while having user
	 */
	public function it_should_allow_having_user_meta_while_having_user(FunctionalTester $I)
	{
		$userId = $I->haveUserInDatabase('Luca', 'editor', ['meta' => ['foo' => 'bar', 'one' => 2]]);

		$I->seeUserMetaInDatabase(['user_id' => $userId, 'meta_key' => 'foo', 'meta_value' => 'bar']);
		$I->seeUserMetaInDatabase(['user_id' => $userId, 'meta_key' => 'one', 'meta_value' => 2]);
	}

	/**
	 * @test
	 * it should allow having user meta while having many users
	 */
	public function it_should_allow_having_user_meta_while_having_many_users(FunctionalTester $I)
	{
		$userIds = $I->haveManyUsersInDatabase(5, 'Luca', 'editor', ['meta' => ['foo' => 'bar', 'one' => 2]]);

		for ($i = 0; $i < 5; $i++) {
			$I->seeUserMetaInDatabase(['user_id' => $userIds[$i], 'meta_key' => 'foo', 'meta_value' => 'bar']);
			$I->seeUserMetaInDatabase(['user_id' => $userIds[$i], 'meta_key' => 'one', 'meta_value' => 2]);
		}
	}

	/**
	 * @test
	 * it should allow having meta while having many users with number replaced
	 */
	public function it_should_allow_having_meta_while_having_many_users_with_number_replaced(FunctionalTester $I)
	{
		$userIds = $I->haveManyUsersInDatabase(5, 'Luca', 'editor', [
			'meta' => [
				'foo_{{n}}' => 'bar_{{n}}',
				'{{n}}_one' => 2
			]
		]);

		for ($i = 0; $i < 5; $i++) {
			$I->seeUserMetaInDatabase([
				'user_id' => $userIds[$i],
				'meta_key' => 'foo_' . $i,
				'meta_value' => 'bar_' . $i
			]);
			$I->seeUserMetaInDatabase(['user_id' => $userIds[$i], 'meta_key' => $i . '_one', 'meta_value' => 2]);
		}
	}

}

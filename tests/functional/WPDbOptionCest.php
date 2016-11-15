<?php

class WPDbOptionCest
{
	public function _before(FunctionalTester $I)
	{
		$I->useBlog(0);
	}

	public function _after(FunctionalTester $I)
	{
	}

	/**
	 * @test
	 * it should allow having an option in the database
	 */
	public function it_should_allow_having_an_option_in_the_database(FunctionalTester $I)
	{
		$I->haveOptionInDatabase('my_option', 'foo');
		$table = $I->grabPrefixedTableNameFor('options');
		$I->seeInDatabase($table, ['option_name' => 'my_option', 'option_value' => 'foo']);
	}

	/**
	 * @test
	 * it should allow having a serialized option in the database if data is array
	 */
	public function it_should_allow_having_a_serialized_option_in_the_database_if_data_is_array(FunctionalTester $I)
	{
		$data = ['foo' => 'bar', 'here' => 23];
		$I->haveOptionInDatabase('my_option', $data);
		$table = $I->grabPrefixedTableNameFor('options');
		$I->seeInDatabase($table, ['option_name' => 'my_option', 'option_value' => serialize($data)]);
	}

	/**
	 * @test
	 * it should allow having a serialized option in the database if data is object
	 */
	public function it_should_allow_having_a_serialized_option_in_the_database_if_data_is_object(FunctionalTester $I)
	{
		$data = (object)['foo' => 'bar', 'here' => 23];
		$I->haveOptionInDatabase('my_option', $data);
		$table = $I->grabPrefixedTableNameFor('options');
		$I->seeInDatabase($table, ['option_name' => 'my_option', 'option_value' => serialize($data)]);
	}

	/**
	 * @test
	 * it should allow not to have an option in the database
	 */
	public function it_should_allow_not_to_have_an_option_in_the_database(FunctionalTester $I)
	{
		$data = '23';
		$I->haveOptionInDatabase('my_option', $data);
		$table = $I->grabPrefixedTableNameFor('options');
		$I->dontHaveOptionInDatabase('my_option');
		$I->dontSeeInDatabase($table, ['option_name' => 'my_option', 'option_value' => $data]);
	}

	/**
	 * @test
	 * it should allow not to have serialized option in database
	 */
	public function it_should_allow_not_to_have_serialized_option_in_database(FunctionalTester $I)
	{
		$data = ['foo' => 'bar', 'some' => 23];
		$I->haveOptionInDatabase('my_option', $data);
		$table = $I->grabPrefixedTableNameFor('options');
		$I->dontHaveOptionInDatabase('my_option');
		$I->dontSeeInDatabase($table, ['option_name' => 'my_option', 'option_value' => serialize($data)]);
	}

	/**
	 * @test
	 * it should overwrite option if already present
	 */
	public function it_should_overwrite_option_if_already_present(FunctionalTester $I)
	{
		$I->haveOptionInDatabase('my_option', 23);
		$table = $I->grabPrefixedTableNameFor('options');
		$I->seeInDatabase($table, ['option_name' => 'my_option', 'option_value' => 23]);
		$I->haveOptionInDatabase('my_option', 'foo');
		$I->seeInDatabase($table, ['option_name' => 'my_option', 'option_value' => 'foo']);
		$I->dontHaveOptionInDatabase('my_option');
	}

	/**
	 * @test
	 * it should allow grabbing an option value
	 */
	public function it_should_allow_grabbing_an_option_value(FunctionalTester $I)
	{
		$I->haveOptionInDatabase('my_option', 'luca');
		$name = $I->grabOptionFromDatabase('my_option');

		$I->assertEquals('luca', $name);
	}

	/**
	 * @test
	 * it should allow grabbing a serialized option value
	 */
	public function it_should_allow_grabbing_a_serialized_option_value(FunctionalTester $I)
	{
		$data = ['foo' => 'bar', 'baz' => 23];
		$I->haveOptionInDatabase('my_option', $data);

		$onDb = $I->grabOptionFromDatabase('my_option');

		$I->assertEquals($data, $onDb);
	}

	/**
	 * @test
	 * it should allow having transient in the database
	 */
	public function it_should_allow_having_transient_in_the_database(FunctionalTester $I)
	{
		$table = $I->grabPrefixedTableNameFor('options');

		$I->haveTransientInDatabase('key', 'value');

		$I->seeInDatabase($table, ['option_name' => '_transient_key', 'option_value' => 'value']);
	}

	/**
	 * @test
	 * it should allow having serialized transient in the database
	 */
	public function it_should_allow_having_serialized_transient_in_the_database(FunctionalTester $I)
	{
		$table = $I->grabPrefixedTableNameFor('options');

		$data = ['foo' => 'bar'];
		$I->haveTransientInDatabase('key', $data);

		$I->seeInDatabase($table, ['option_name' => '_transient_key', 'option_value' => serialize($data)]);
	}

	/**
	 * @test
	 * it should overwrite a transient when having again
	 */
	public function it_should_overwrite_a_transient_when_having_again(FunctionalTester $I)
	{
		$table = $I->grabPrefixedTableNameFor('options');

		$I->haveTransientInDatabase('key', '23');
		$I->seeInDatabase($table, ['option_name' => '_transient_key', 'option_value' => '23']);

		$I->haveTransientInDatabase('key', 'foo');
		$I->seeInDatabase($table, ['option_name' => '_transient_key', 'option_value' => 'foo']);
	}

	/**
	 * @test
	 * it should allow deleting a transient
	 */
	public function it_should_allow_deleting_a_transient(FunctionalTester $I)
	{
		$table = $I->grabPrefixedTableNameFor('options');

		$I->haveTransientInDatabase('key', '23');
		$I->seeInDatabase($table, ['option_name' => '_transient_key', 'option_value' => '23']);

		$I->dontHaveTransientInDatabase('key');
		$I->dontSeeInDatabase($table, ['option_name' => '_transient_key']);
	}

	/**
	 * @test
	 * it should allow setting a site option
	 */
	public function it_should_allow_setting_a_site_option(FunctionalTester $I)
	{
		$table = $I->grabPrefixedTableNameFor('options');

		$I->haveSiteOptionInDatabase('key', 'value');

		$I->seeInDatabase($table, ['option_name' => '_site_option_key']);
	}

	/**
	 * @test
	 * it should allow deleting a site option
	 */
	public function it_should_allow_deleting_a_site_option(FunctionalTester $I)
	{
		$table = $I->grabPrefixedTableNameFor('options');
		$I->haveInDatabase($table, ['option_name' => '_site_option_key', 'option_value' => 'some value']);

		$I->dontHaveSiteOptionInDatabase('key');

		$I->dontSeeInDatabase($table, ['option_name' => '_site_option_key']);
	}

	/**
	 * @test
	 * it should allow adding a site transient
	 */
	public function it_should_allow_adding_a_site_transient(FunctionalTester $I)
	{
		$I->haveSiteTransientInDatabase('key', 'value');

		$table = $I->grabPrefixedTableNameFor('options');

		$I->seeInDatabase($table, ['option_name' => '_site_transient_key']);
	}

	/**
	 * @test
	 * it should allow deleting a site transient
	 */
	public function it_should_allow_deleting_a_site_transient(FunctionalTester $I)
	{
		$table = $I->grabPrefixedTableNameFor('options');
		$I->haveInDatabase($table, ['option_name' => '_site_transient_key', 'option_value' => 'some value']);

		$I->dontHaveSiteTransientInDatabase('key');

		$I->dontSeeInDatabase($table, ['option_name' => '_site_transient_key']);
	}

	/**
	 * @test
	 * it should allow setting an option in a secondary site
	 */
	public function it_should_allow_setting_an_option_in_a_secondary_site(FunctionalTester $I)
	{
		$I->useBlog(2);
		$I->haveOptionInDatabase('key', 'value');

		$table = $I->grabPrefixedTableNameFor('options');
		$I->seeInDatabase($table, ['option_name' => 'key', 'option_value' => 'value']);
	}

	/**
	 * @test
	 * it should allow grabbing a site option
	 */
	public function it_should_allow_grabbing_a_site_option(FunctionalTester $I)
	{
		$table = $I->grabPrefixedTableNameFor('options');
		$I->haveInDatabase($table, ['option_name' => '_site_option_key', 'option_value' => 'foo']);

		$value = $I->grabSiteOptionFromDatabase('key');

		$I->assertEquals('foo', $value);
	}

	/**
	 * @test
	 * it should allow grabbing a site transient
	 */
	public function it_should_allow_grabbing_a_site_transient(FunctionalTester $I)
	{
		$table = $I->grabPrefixedTableNameFor('options');
		$I->haveInDatabase($table, ['option_name' => '_site_transient_key', 'option_value' => 'foo']);

		$value = $I->grabSiteTransientFromDatabase('key');

		$I->assertEquals('foo', $value);
	}

	/**
	 * @test
	 * it should allow getting a site option while using secondary blog
	 */
	public function it_should_allow_getting_a_site_option_while_using_secondary_blog(FunctionalTester $I)
	{
		$table = $I->grabPrefixedTableNameFor('options');
		$I->haveInDatabase($table, ['option_name' => '_site_option_key', 'option_value' => 'foo']);

		$I->useBlog(2);
		$value = $I->grabSiteOptionFromDatabase('key');

		$I->assertEquals('foo', $value);
	}

	/**
	 * @test
	 * it should allow grabbing a site transient while using secondary blog
	 */
	public function it_should_allow_grabbing_a_site_transient_while_using_secondary_blog(FunctionalTester $I)
	{
		$table = $I->grabPrefixedTableNameFor('options');
		$I->haveInDatabase($table, ['option_name' => '_site_transient_key', 'option_value' => 'foo']);

		$I->useBlog(2);
		$value = $I->grabSiteTransientFromDatabase('key');

		$I->assertEquals('foo', $value);
	}

	/**
	 * @test
	 * it should allow setting a site option while using a secondary blog
	 */
	public function it_should_allow_setting_a_site_option_while_using_a_secondary_blog(FunctionalTester $I)
	{
		$I->useBlog(2);
		$I->haveSiteOptionInDatabase('key', 'value');

		$I->useMainBlog();
		$I->seeSiteOptionInDatabase('key');
	}

	/**
	 * @test
	 * it should allow setting a site transient while using a secondary blog
	 */
	public function it_should_allow_setting_a_site_transient_while_using_a_secondary_blog(FunctionalTester $I)
	{
		$I->useBlog(2);
		$I->haveSiteTransientInDatabase('key', 'value');

		$I->useMainBlog();
		$I->seeSiteSiteTransientInDatabase('key');
	}
}

<?php
namespace cli;

use ClimoduleTester;

class BasicOperationsCest
{
	public function _before(ClimoduleTester $I)
	{
	}

	public function _after(ClimoduleTester $I)
	{
	}

	/**
	 * @test
	 * it should allow using the cli method in a test
	 */
	public function it_should_allow_using_the_cli_method_in_a_test(ClimoduleTester $I)
	{
		$I->cli('core version');
	}

	/**
	 * @test
	 * it should allow creating a post in the WordPress installation
	 */
	public function it_should_allow_creating_a_post_in_the_word_press_installation(ClimoduleTester $I)
	{
		$I->cli('post create --post_title="Some Post" --post_type=post');

		$I->seePostInDatabase(['post_title' => 'Some Post', 'post_type' => 'post']);
	}

	/**
	 * @test
	 * it should allow trashing a post
	 */
	public function it_should_allow_trashing_a_post(ClimoduleTester $I)
	{
		$id = $I->havePostInDatabase(['post_title' => 'some post', 'post_type' => 'post']);

		$I->cli('post delete ' . $id);

		$I->seePostInDatabase(['ID' => $id, 'post_status' => 'trash']);
	}

	/**
	 * @test
	 * it should allow deleting a post from the database
	 */
	public function it_should_allow_deleting_a_post_from_the_database(ClimoduleTester $I)
	{
		$id = $I->havePostInDatabase(['post_title' => 'some post', 'post_type' => 'post']);

		$I->cli('post delete ' . $id . ' --force');

		$I->dontSeePostInDatabase(['ID' => $id]);
	}
}

<?php


class WPDbThemeCest
{

	public function _before(FunctionalTester $I)
	{
	}

	public function _after(FunctionalTester $I)
	{
	}

	/**
	 * @test
	 * it should set the current theme
	 */
	public function it_should_set_the_current_theme(FunctionalTester $I)
	{
		$I->useTheme('foo', 'bar', 'Baz');

		$I->seeOptionInDatabase(['option_name' => 'stylesheet', 'option_value' => 'foo']);
		$I->seeOptionInDatabase(['option_name' => 'template', 'option_value' => 'bar']);
		$I->seeOptionInDatabase(['option_name' => 'current_theme', 'option_value' => 'Baz']);
	}

	/**
	 * @test
	 * it should default the template to stylesheet
	 */
	public function it_should_default_the_template_to_stylesheet(FunctionalTester $I)
	{
		$I->useTheme('foo');

		$I->seeOptionInDatabase(['option_name' => 'stylesheet', 'option_value' => 'foo']);
		$I->seeOptionInDatabase(['option_name' => 'template', 'option_value' => 'foo']);
	}

	/**
	 * @test
	 * it should default the theme name to title version of stylesheet
	 */
	public function it_should_default_the_theme_name_to_title_version_of_stylesheet(FunctionalTester $I)
	{
		$I->useTheme('foo');

		$I->seeOptionInDatabase(['option_name' => 'stylesheet', 'option_value' => 'foo']);
		$I->seeOptionInDatabase(['option_name' => 'current_theme', 'option_value' => 'Foo']);
	}

	/**
	 * @test
	 * it should properly set title versions of stylesheet as theme name
	 */
	public function it_should_properly_set_title_versions_of_stylesheet_as_theme_name(FunctionalTester $I)
	{
		$I->useTheme('_s');

		$I->seeOptionInDatabase(['option_name' => 'stylesheet', 'option_value' => '_S']);
		$I->seeOptionInDatabase(['option_name' => 'current_theme', 'option_value' => '_S']);
	}
}

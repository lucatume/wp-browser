<?php


class WPFilesystemCest {

	/**
	 * It should allow creating a plugin
	 *
	 * @test
	 */
	public function should_allow_creating_a_plugin(AcceptanceTester $I) {
		$code = <<< PHP
add_action('admin_notices',function(){
	echo '<div class="notice">A test notice!</div>';
});
PHP;

		$I->havePlugin('foo/foo.php', $code);

		$I->loginAsAdmin();
		$I->amOnPluginsPage();
		$I->activatePlugin('foo');
		$I->see('A test notice!');
	}

	/**
	 * It should allow creating a mu-plugin
	 *
	 * @test
	 */
	public function should_allow_creating_a_mu_plugin(AcceptanceTester $I) {
		$code = <<< PHP
add_action('admin_notices',function(){
	echo '<div class="notice">A test notice!</div>';
});
PHP;

		$I->haveMuPlugin('bar.php', $code);

		$I->loginAsAdmin();
		$I->amOnPluginsPage();
		$I->see('A test notice!');
	}

	/**
	 * It should allow creating a theme
	 *
	 * @test
	 */
	public function should_allow_creating_a_theme(AcceptanceTester $I) {
		$indexCode = <<< PHP
echo "Hello from baz theme!";
PHP;

		$I->haveTheme('baz', $indexCode);

		$I->useTheme('baz');
		$I->amOnPage('/');
		$I->see('Hello from baz theme!');
	}

	/**
	 * It should allow creating a theme with functions files
	 *
	 * @test
	 */
	public function should_allow_creating_a_theme_with_functions_files(
		AcceptanceTester $I
	) {
		$indexCode = <<< PHP
echo baz_say_hi();
PHP;

		$functionsCode = <<< PHP
function baz_say_hi(){
	return "Hello from functions.php";
}
PHP;


		$I->haveTheme('baz-2', $indexCode, $functionsCode);

		$I->useTheme('baz-2');
		$I->amOnPage('/');
		$I->see('Hello from functions.php');
	}

	/**
	 * It should remove temp files after each test
	 *
	 * @test
	 */
	public function should_remove_temp_files_after_each_test(AcceptanceTester $I
	) {
		$I->dontSeePluginFileFound('foo');
		$I->dontSeeMuPluginFileFound('bar');
		$I->dontSeeThemeFileFound('baz');
	}
}

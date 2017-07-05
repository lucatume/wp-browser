<?php


class WPFilesystemPluginCest {
	/**
	 * It should allow creating a plugin
	 *
	 * @test
	 */
	public function should_allow_creating_a_plugin( AcceptanceTester $I ) {
		$code = <<< PHP
add_action('admin_notices',function(){
	echo '<div class="notice">A test notice!</div>';
});
PHP;

		$I->havePlugin( 'foo/foo.php', $code );

		$I->loginAsAdmin();
		$I->amOnPluginsPage();
		$I->activatePlugin( 'foo' );
		$I->see( 'A test notice!' );
	}
}

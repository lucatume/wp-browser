<?php
$I = new WpfunctionalTester($scenario);
$I->wantTo('use the wp_create_nonce function to generate a valid nonce');

$I->haveOptionInDatabase('active_plugins', ['test/test.php']);

$activePlugins = $I->cliToArray(['plugin', 'list', '--status=active', '--field=name']);
$I->assertEquals(['test'], $activePlugins);

$id = $I->haveUserInDatabase('user', 'subscriber', ['user_pass' => 'pass']);
$I->loginAs('user', 'pass');

$I->startWpFiltersDebug();
wp_set_current_user($id);
$I->extractCookie(LOGGED_IN_COOKIE);
$nonce = wp_create_nonce('wp_rest');
$I->stopWpFiltersDebug();

$I->haveHttpHeader('X-WP-Nonce', $nonce);
$I->amOnPage('/wp-json/test/whoami');

$I->assertEquals('user', $I->getResponseContent());

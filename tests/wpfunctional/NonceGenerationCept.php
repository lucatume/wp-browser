<?php
$I = new WpfunctionalTester($scenario);
$I->wantTo('use the wp_create_nonce function to generate a valid function');

$I->haveOptionInDatabase('active_plugins', ['test/test.php']);

$id = $I->haveUserInDatabase('user', 'subscriber', ['user_pass' => 'pass']);
$I->loginAs('user', 'pass');

$I->extractCookie(LOGGED_IN_COOKIE);
wp_set_current_user($id);
$nonce = wp_create_nonce('wp_rest');

$I->haveHttpHeader('X-WP-Nonce', $nonce);
$I->amOnPage('/wp-json/test/whoami');

$I->assertEquals('user', $I->getResponseContent());

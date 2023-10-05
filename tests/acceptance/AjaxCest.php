<?php

class AjaxCest {
	public function _before( AcceptanceTester $I ) {
		$code = <<< PHP
<?php
/**
 * Plugin Name: Test Ajax Plugin
 */

function onTestAjaxAction(){
	check_ajax_referer('test_ajax_action');
	
	wp_send_json_success(['type' => 'priv']);
}

function onTestNoPrivAjaxAction(){
	check_ajax_referer('test_ajax_action');
	
	wp_send_json_success(['type' => 'no-priv']);
}

add_action( 'wp_ajax_test_ajax_action', 'onTestAjaxAction');
add_action( 'wp_ajax_nopriv_test_ajax_action', 'onTestNoPrivAjaxAction');

// Print a nonce in the footer.
add_action( 'wp_footer', function(){
	\$nonce = wp_create_nonce('test_ajax_action');
	echo '<input type="hidden" id="test-ajax-nonce" value="'.\$nonce.'">';
});
PHP;

		$I->haveMuPlugin( 'test-ajax-plugin', $code );
	}

	public function test_invalid_action_returns_400( AcceptanceTester $I ) {
		$I->amOnAdminAjaxPage( [
			'action' => 'invalid_action'
		] );
		$I->seeResponseCodeIs( 400 );
	}

	public function test_missing_nonce_returns_403( AcceptanceTester $I ) {
		$I->amOnAdminAjaxPage( [
			'action' => 'test_ajax_action'
		] );
		$I->seeResponseCodeIs( 403 );
	}

	public function test_wrong_nonce_returns_403( AcceptanceTester $I ) {
		$I->amOnAdminAjaxPage( [
			'action'      => 'test_ajax_action',
			'_ajax_nonce' => 'wrong_nonce'
		] );
		$I->seeResponseCodeIs( 403 );
	}

	public function test_valid_nonce_returns_200_for_visitor( AcceptanceTester $I ) {
		$I->amOnPage( '/' );
		$nonce = $I->grabValueFrom( '#test-ajax-nonce' );

		$I->amOnAdminAjaxPage( [
			'action'      => 'test_ajax_action',
			'_ajax_nonce' => $nonce
		] );
		$I->seeResponseCodeIs( 200 );
		$I->canSee( json_encode( [ 'type' => 'no-priv' ] ) );
	}

	public function test_valid_nonce_returns_200_for_admin( AcceptanceTester $I ) {
		$I->loginAsAdmin();
		$I->amOnPage( '/' );
		$nonce = $I->grabValueFrom( '#test-ajax-nonce' );

		$I->amOnAdminAjaxPage( [
			'action'      => 'test_ajax_action',
			'_ajax_nonce' => $nonce
		] );
		$I->seeResponseCodeIs( 200 );
		$I->canSee( json_encode( [ 'type' => 'priv' ] ) );
	}
}

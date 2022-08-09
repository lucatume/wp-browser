<?php
/**
 * Bootstraps a WordPress installation, logs in the user and creates a nonce for the required action.
 */

// Include wp-load.php to bootstrap WordPress
include $argv[1];

$request = unserialize($argv[2]);

// signon the user; will set the cookies but on next page reload...
$user = wp_signon($request['credentials']);

if (! $user instanceof WP_User) {
    $error = $user->get_error_message();

    throw new \RuntimeException($error);
}

//... so let's set and use the ones we've been passed.
$_COOKIE = array_merge($_COOKIE, $request['cookies']);

wp_set_current_user($user->ID, $user->user_login);

$nonce = wp_create_nonce($request['action']);

die($nonce);

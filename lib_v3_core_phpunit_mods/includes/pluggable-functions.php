<?php

if (! function_exists('wp_set_auth_cookie') && ! function_exists('wp_clear_auth_cookie')) {
    // No `setcookie` calls to avoid: "Cannot modify header information - headers already sent"
    function wp_set_auth_cookie($user_id, $remember = false, $secure = '', $token = '')
    {
        /** This action is documented in wp-inclues/pluggable.php */
        do_action('set_auth_cookie', null, null, null, $user_id, null);
        /** This action is documented in wp-inclues/pluggable.php */
        do_action('set_logged_in_cookie', null, null, null, $user_id, 'logged_in');
    }

    function wp_clear_auth_cookie()
    {
        /** This action is documented in wp-inclues/pluggable.php */
        do_action('clear_auth_cookie');
    }
}

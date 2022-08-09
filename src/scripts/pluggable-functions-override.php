<?php
/**
 * Copies of original WordPress "pluggable" functions to log WordPress set headers that
 * would not be available in CLI execution.
 */

/**
 * Copy and paste of WordPress original function where headers are but stored
 * before sending to avoid CLI limitations.
 *
 * @param     $location
 * @param int $status
 *
 * @return bool
 */
function wp_redirect($location, $status = 302)
{
    global $is_IIS;

    /**
     * Filter the redirect location.
     *
     * @since 2.1.0
     *
     * @param string $location The path to redirect to.
     * @param int    $status   Status code to use.
     */
    $location = apply_filters('wp_redirect', $location, $status);

    /**
     * Filter the redirect status code.
     *
     * @since 2.3.0
     *
     * @param int    $status   Status code to use.
     * @param string $location The path to redirect to.
     */
    $status = apply_filters('wp_redirect_status', $status, $location);

    if (!$location) {
        return false;
    }

    $location = wp_sanitize_redirect($location);

    if (!$is_IIS && PHP_SAPI != 'cgi-fcgi') {
        status_header($status);
    } // This causes problems on IIS and some FastCGI setups

    header("Location: $location", true, $status);

    global $cli_headers;
    $cli_headers["Location: $location"] = $status;

    return true;
}

/**
 * Copy and paste of WordPress original function.
 * Works like the original but cookies will be immediately set on the `$_COOKIE` superglobal too.
 *
 * @param int    $user_id  User ID
 * @param bool   $remember Whether to remember the user
 * @param mixed  $secure   Whether the admin cookies should only be sent over HTTPS.
 *                         Default is_ssl().
 * @param string $token    Optional. User's session token to use for this cookie.
 */
function wp_set_auth_cookie($user_id, $remember = false, $secure = '', $token = '')
{
    if ($remember) {
        /**
         * Filter the duration of the authentication cookie expiration period.
         *
         * @since 2.8.0
         *
         * @param int  $length   Duration of the expiration period in seconds.
         * @param int  $user_id  User ID.
         * @param bool $remember Whether to remember the user login. Default false.
         */
        $expiration = time() + apply_filters('auth_cookie_expiration', 14 * DAY_IN_SECONDS, $user_id, $remember);

        /*
         * Ensure the browser will continue to send the cookie after the expiration time is reached.
         * Needed for the login grace period in wp_validate_auth_cookie().
         */
        $expire = $expiration + (12 * HOUR_IN_SECONDS);
    } else {
        /** This filter is documented in wp-includes/pluggable.php */
        $expiration = time() + apply_filters('auth_cookie_expiration', 2 * DAY_IN_SECONDS, $user_id, $remember);
        $expire = 0;
    }

    if ('' === $secure) {
        $secure = is_ssl();
    }

    // Front-end cookie is secure when the auth cookie is secure and the site's home URL is forced HTTPS.
    $secure_logged_in_cookie = $secure && 'https' === parse_url(get_option('home'), PHP_URL_SCHEME);

    /**
     * Filter whether the connection is secure.
     *
     * @since 3.1.0
     *
     * @param bool $secure  Whether the connection is secure.
     * @param int  $user_id User ID.
     */
    $secure = apply_filters('secure_auth_cookie', $secure, $user_id);

    /**
     * Filter whether to use a secure cookie when logged-in.
     *
     * @since 3.1.0
     *
     * @param bool $secure_logged_in_cookie Whether to use a secure cookie when logged-in.
     * @param int  $user_id                 User ID.
     * @param bool $secure                  Whether the connection is secure.
     */
    $secure_logged_in_cookie = apply_filters(
        'secure_logged_in_cookie',
        $secure_logged_in_cookie,
        $user_id,
        $secure
    );

    if ($secure) {
        $auth_cookie_name = SECURE_AUTH_COOKIE;
        $scheme = 'secure_auth';
    } else {
        $auth_cookie_name = AUTH_COOKIE;
        $scheme = 'auth';
    }

    if ('' === $token) {
        $manager = WP_Session_Tokens::get_instance($user_id);
        $token = $manager->create($expiration);
    }

    $auth_cookie = wp_generate_auth_cookie($user_id, $expiration, $scheme, $token);
    $logged_in_cookie = wp_generate_auth_cookie($user_id, $expiration, 'logged_in', $token);

    /**
     * Fires immediately before the authentication cookie is set.
     *
     * @since 2.5.0
     *
     * @param string $auth_cookie Authentication cookie.
     * @param int    $expire      Login grace period in seconds. Default 43,200 seconds, or 12 hours.
     * @param int    $expiration  Duration in seconds the authentication cookie should be valid.
     *                            Default 1,209,600 seconds, or 14 days.
     * @param int    $user_id     User ID.
     * @param string $scheme      Authentication scheme. Values include 'auth', 'secure_auth', or 'logged_in'.
     */
    do_action('set_auth_cookie', $auth_cookie, $expire, $expiration, $user_id, $scheme);

    /**
     * Fires immediately before the secure authentication cookie is set.
     *
     * @since 2.6.0
     *
     * @param string $logged_in_cookie The logged-in cookie.
     * @param int    $expire           Login grace period in seconds. Default 43,200 seconds, or 12 hours.
     * @param int    $expiration       Duration in seconds the authentication cookie should be valid.
     *                                 Default 1,209,600 seconds, or 14 days.
     * @param int    $user_id          User ID.
     * @param string $scheme           Authentication scheme. Default 'logged_in'.
     */
    do_action('set_logged_in_cookie', $logged_in_cookie, $expire, $expiration, $user_id, 'logged_in');

    setcookie($auth_cookie_name, $auth_cookie, $expire, PLUGINS_COOKIE_PATH, COOKIE_DOMAIN, $secure, true);
    setcookie($auth_cookie_name, $auth_cookie, $expire, ADMIN_COOKIE_PATH, COOKIE_DOMAIN, $secure, true);
    setcookie(
        LOGGED_IN_COOKIE,
        $logged_in_cookie,
        $expire,
        COOKIEPATH,
        COOKIE_DOMAIN,
        $secure_logged_in_cookie,
        true
    );
    if (COOKIEPATH != SITECOOKIEPATH) {
        setcookie(
            LOGGED_IN_COOKIE,
            $logged_in_cookie,
            $expire,
            SITECOOKIEPATH,
            COOKIE_DOMAIN,
            $secure_logged_in_cookie,
            true
        );
    }

    // set the cookies in the $_COOKIE superglobal too
    $_COOKIE[$auth_cookie_name] = $auth_cookie;
    $_COOKIE[LOGGED_IN_COOKIE] = $logged_in_cookie;
}

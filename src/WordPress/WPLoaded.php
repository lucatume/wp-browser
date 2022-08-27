<?php

namespace lucatume\WPBrowser\WordPress;

class Auth
{
    /**
     * @return array{authCookieName: string, authCookieValue: string}
     */
    public function getAuthCookieForUserId(int $adminUserId): array
    {
        wp_set_current_user($adminUserId);
        return [AUTH_COOKIE, wp_generate_auth_cookie($adminUserId, time() + 3600),];
    }
}

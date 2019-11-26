<?php

namespace tad\WPBrowser\Generators;

/**
 * Generates user entries to be inserted in a WordPress database.
 */
class User
{
    /**
     * Generated the entry for the users table.
     *
     * @param string $user_login The user login slug.
     *
     * @return array             An associative array of column/values for the "users" table.
     */
    public static function generateUserTableDataFrom($user_login, array $userData = array())
    {
        $login = \tad\WPBrowser\sanitize_user($user_login, true);
        $usersTableDefaults = array(
            'user_login' => $login,
            'user_pass' => WpPassword::instance()->make($user_login),
            'user_nicename' => $user_login,
            'user_email' => $login . "@example.com",
            'user_url' => "http://{$login}.example.com",
            'user_registered' => Date::now(),
            'user_activation_key' => '',
            'user_status' => '0',
            'display_name' => $user_login
        );
        if (!empty($userData['user_pass'])) {
            $userData['user_pass'] = WpPassword::instance()->make($userData['user_pass']);
        }

        return array_merge($usersTableDefaults, array_intersect_key($userData, $usersTableDefaults));
    }

    /**
     * Returns the user level for a user role.
     *
     * @param string $role The input role.
     * @return string The corresponding user level.
     */
    public static function getLevelForRole($role = 'subscriber')
    {
        $map = [
            'subscriber' => 0,
            'contributor' => 1,
            'author' => 2,
            'editor' => 7,
            'administrator' => 10,
            '' => 0 // no role for the site
        ];
        return array_key_exists($role, $map) ? $map[$role] : $map['subscriber'];
    }
}

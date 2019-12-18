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
     * @param string|int $role The input role as a slug, e.g. `subscriber`, or as an integer for identity.
     *
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

    /**
     * Builds and returns an array of capabilities, built supporting different formats.
     *
     * @param mixed  $role        Either a user role (e.g. `editor`), a list of user roles and capabilities (e.g.
     *                            `['editor' => true, 'edit_themes' => true]`) or a list of blog IDs and, for each, a
     *                            list of user roles in the previously specified formats.
     * @param string $tablePrefix The table and blog prefix for the user capabilities.
     *
     * @return array<string,array> An array of meta keys to insert to correctly represent the desired user capabilities.
     */
    public static function buildCapabilities($role, $tablePrefix = 'wp_')
    {
        $roles = (array)$role;
        $boolValues = count(array_filter($roles, 'is_bool')) === count($roles);
        $stringValues = count(array_filter($roles, 'is_string')) === count($roles);
        $blogIdEntries = !isset($roles[0]) && count(array_filter(array_keys($roles), 'is_int')) === count($roles);

        if ($blogIdEntries) {
            // Format is `[<blogId> => <role>, <blogId> => <role>]`.
            return array_merge(...array_map(static function ($role, $blogId) use ($tablePrefix) {
                $blogPrefix = (int)$blogId < 2 ? '' : (int)$blogId . '_';
                return static::buildCapabilities($role, $tablePrefix . $blogPrefix);
            }, $roles, array_keys($roles)));
        }

        if ($stringValues) {
            // Format is `[<cap1>, <cap2>]`.
            $roles = array_combine($roles, array_fill(0, count($roles), true)) ;
            $boolValues = true;
        }

        if ($boolValues) {
            // Format is `[<cap1> => <true|false>, <cap2> => <true|false>]`.
            $meta_key = $tablePrefix . 'capabilities';
            return [$meta_key => $roles];
        }

        throw new \InvalidArgumentException(
            "The roles array must either have the 'array<string,bool>' format (e.g. `['author' => true, " .
            "'edit_themes' => true ]`) or the 'array<int,array>' format to setup roles for diff. blogs (e.g. " .
            "`[1 => ['author'=> true], 2 => ['editor' => true]]`."
        );
    }
}

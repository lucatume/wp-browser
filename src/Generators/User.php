<?php

namespace lucatume\WPBrowser\Generators;

use lucatume\WPBrowser\Exceptions\InvalidArgumentException;
use lucatume\WPBrowser\Utils\Strings;
use lucatume\WPBrowser\Utils\WP;

/**
 * Generates user entries to be inserted in a WordPress database.
 */
class User
{
    /**
     * Generates the entry for the users table.
     *
     * @param string $user_login The user login slug.
     * @param array<int|string,mixed> $userData An array of user data to override the default values. It should NOT
     *                                        include `meta` and `meta_input` keys.
     *
     * @return array<string,string>             An associative array of column/values for the "users" table.
     */
    public static function generateUserTableDataFrom(string $user_login, array $userData = []): array
    {
        $login = Strings::sanitizeUsername($user_login, true);
        $usersTableDefaults = [
            'user_login' => $login,
            'user_pass' => WP::passwordHash($user_login),
            'user_nicename' => $user_login,
            'user_email' => $login . "@example.com",
            'user_url' => "https://$login.example.com",
            'user_registered' => Date::now() ?: '0000-00-00 00:00:00',
            'user_activation_key' => '',
            'user_status' => '0',
            'display_name' => $user_login
        ];
        if (!empty($userData['user_pass'])) {
            if (!is_string($userData['user_pass'])) {
                throw new InvalidArgumentException('The user_pass key in the user data array must be a string.');
            }
            $userData['user_pass'] = WP::passwordHash($userData['user_pass']);
        }

        $userData = array_filter($userData, 'is_string');

        return array_merge($usersTableDefaults, array_intersect_key($userData, $usersTableDefaults));
    }

    /**
     * Returns the user level for a user role.
     *
     * @param string|int $role The input role as a slug, e.g. `subscriber`, or as an integer for identity.
     *
     * @return int The corresponding user level, an integer.
     */
    public static function getLevelForRole($role = 'subscriber'): int
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
     * @param string|array<string>|array<string,bool>|array<int,array<string,bool>> $role Either a user role (e.g.
     * `editor`), a list of user roles and capabilities (e.g. `['editor' => true, 'edit_themes' => true]`) or a list of
     * blog IDs and, for each, a list of user roles in the previously specified formats.
     * @param string $tablePrefix The table and blog prefix for the user capabilities.
     *
     * @return array<string,array<string,bool>> An array of meta keys to insert to correctly represent the desired user
     *     capabilities.
     */
    public static function buildCapabilities($role, string $tablePrefix = 'wp_'): array
    {
        $roles = (array)$role;
        $stringKeys = count(array_filter(array_keys($roles), 'is_string')) === count($roles);
        $boolValues = $stringKeys && count(array_filter($roles, 'is_bool')) === count($roles);
        $stringValues = !$stringKeys && count(array_filter($roles, 'is_string')) === count($roles);
        $blogIdEntries = !$stringKeys && !isset($roles[0]) &&
            count(array_filter(array_keys($roles), 'is_int')) === count($roles);

        if ($blogIdEntries) {
            // Format is `[<blogId> => <role>, <blogId> => <role>, ...]`.
            return array_merge(...array_map(static function ($role, $blogId) use ($tablePrefix): array {
                $blogPrefix = (int)$blogId < 2 ? '' : (int)$blogId . '_';
                // @phpstan-ignore-next-line if bool it will throw.
                return static::buildCapabilities($role, $tablePrefix . $blogPrefix);
            }, $roles, array_keys($roles)));
        }

        if ($stringValues) {
            // Format is `[<cap1>, <cap2>, ...]`.
            // @phpstan-ignore-next-line $roles is array<string> at this point.
            $roles = array_combine($roles, array_fill(0, count($roles), true));
            $boolValues = true;
        }

        if ($boolValues) {
            // Format is `[<cap1> => bool, <cap2> => bool, ...]`.
            $meta_key = $tablePrefix . 'capabilities';
            // @phpstan-ignore-next-line $roles is array<string,bool> at this point.
            return [$meta_key => $roles];
        }

        throw new \InvalidArgumentException(
            "The roles array must either have the 'array<string,bool>' format (e.g. `['author' => true, " .
            "'edit_themes' => true ]`) or the 'array<int,array>' format to setup roles for diff. blogs (e.g. " .
            "`[1 => ['author'=> true], 2 => ['editor' => true]]`."
        );
    }

    /**
     * Generates the default meta values for a user.
     *
     * The values DO NOT include the meta keys related to the user capabilities (`wp_capabilities` and `wp_user_level`).
     * To set those see the `buildCapabilities` method.
     *
     * @param string $user_login The user login name, slug form.
     * @param array<string,mixed> $overrides A map of the user meta values to override and add to the default values.
     *
     * @return array<string,mixed> The user meta keys and values, with overrides applied.
     */
    public static function generateUserMetaTableDataFrom(string $user_login, array $overrides = []): array
    {
        $login = Strings::sanitizeUsername($user_login, true);

        $usersMetaTableDefaults = [
            'nickname' => $login,
            'first_name' => '',
            'last_name' => '',
            'description' => '',
            'rich_editing' => 'true',
            'syntax_highlighting' => 'true',
            'comment_shortcuts' => 'false',
            'admin_color' => 'fresh',
            'use_ssl' => 0,
            'show_admin_bar_front' => 'true',
            'locale' => '',
            'dismissed_wp_pointers' => '',
            'show_welcome_panel' => 0,
        ];

        return array_merge($usersMetaTableDefaults, $overrides);
    }
}

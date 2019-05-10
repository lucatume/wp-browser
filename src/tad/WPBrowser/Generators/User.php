<?php

namespace tad\WPBrowser\Generators;

use BaconStringUtils\Filter\Slugify;
use BaconStringUtils\Slugifier;

/**
 * Generates user entries to be inserted in a WordPress database.
 */
class User
{

    /**
     * The user roles (actual WordPress) to user levels (WordPress prev. versions) relations
     *
     * @var array
     */
    protected static $userRolesToLevels = array(
        'subscriber'    => '0',
        'contributor'   => '1',
        'author'        => '2',
        'editor'        => '7',
        'administrator' => '10'
    );

    /**
     * Generates a user entry to be inserted in a WordPress database
     *
     * @param string $user_login The user slug
     * @param string $role       The user role
     * @param array  $userData   The user data to use overriding defaults.
     * @return array An array containing key\value pairs for the "wp_user_level" "usermeta" table entry, the "users"
     * table entry, the "wp_capabilities" "usermeta" table entry.
     */
    public static function makeUser($user_login, $role, array $userData = array())
    {
        if (!is_string($user_login)) {
            throw new \BadMethodCallException('User login must be a string', 1);
        }
        if (!is_string($role)) {
            throw new \BadMethodCallException('User role must be a string', 3);
        }
        $userTableDefaults        = self::generateUserTableDataFrom($user_login, $role);
        $userCapabilitiesDefaults = self::generateCapabilitiesDefaultsFrom($role);
        $userLevelDefaults        = self::generateUserLevelDefaultsFrom($role);
        // merge user data with defaults
        $userTableData        = array_merge($userTableDefaults, $userData);
        $userCapabilitiesData = array_merge($userCapabilitiesDefaults, $userData);
        return array( $userLevelDefaults, $userTableData, $userCapabilitiesData );
    }

    /**
     * Generated the entry for the users table.
     *
     * @param  string $user_login The user login slug
     *
     * @return array             An associtive array of column/values for the "users" table.
     */
    public static function generateUserTableDataFrom($user_login, array $userData = array())
    {
        $utils              = new Slugifier();
        $login              = $utils->slugify($user_login);
        $usersTableDefaults = array(
            'user_login'          => $login,
            'user_pass'           => WpPassword::instance()->make($user_login),
            'user_nicename'       => $user_login,
            'user_email'          => $login . "@example.com",
            'user_url'            => "http://{$login}.example.com",
            'user_registered'     => Date::now(),
            'user_activation_key' => '',
            'user_status'         => '0',
            'display_name'        => $user_login
        );
        if (!empty($userData['user_pass'])) {
            $userData['user_pass'] = WpPassword::instance()->make($userData['user_pass']);
        }

        return array_merge($usersTableDefaults, array_intersect_key($userData, $usersTableDefaults));
    }

    /**
     * Generates the default values entry for the "wp_capabilities" "usermeta" table entry.
     *
     * @param  int    $user_id The user id.
     * @param  string $role    The user role.
     *
     * @return array          An associtive array of column/values for the "usermeta" table.
     */
    protected static function generateCapabilitiesDefaultsFrom($user_id, $role)
    {
        $capabilitiesDefaults = array(
            'umeta_id'   => null,
            'user_id'    => $user_id,
            'meta_key'   => 'wp_capabilities',
            'meta_value' => str_replace('"', '\"', serialize(array( $role => true )))
        );
        return $capabilitiesDefaults;
    }

    /**
     * Generates the default values entry for the "wp_user_level" "usermeta" table entry.
     *
     * @param  int    $user_id The user id.
     * @param  string $role    The user role.
     *
     * @return array          An associtive array of column/values for the "usermeta" table.
     */
    protected static function generateUserLevelDefaultsFrom($user_id, $role)
    {
        $intRole = 0;
        if (isset(self::$userRolesToLevels[$role])) {
            $intRole = self::$userRolesToLevels[$role];
        }
        $userLevelDefaults = array(
            'umeta_id'   => null,
            'user_id'    => $user_id,
            'meta_key'   => 'wp_user_level',
            'meta_value' => $intRole
        );
        return $userLevelDefaults;
    }
}

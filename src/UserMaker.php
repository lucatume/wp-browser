<?php

namespace tad\wordpress\maker;

class UserMaker
{
    protected static $userRolesToLevels = ['subscriber' => '0', 'contributor' => '1', 'author' => '2', 'editor' => '7', 'administrator' => '10'];

    public function makeUser($user_login, $user_id, $role, $userData)
    {
        $userTableDefaults = self::generateUserDefaultsFrom($user_login, $user_id, $role);
        $userCapabilitiesDefaults = self::generateCapabilitiesDefaultsFrom($user_id, $role);
        $userLevelDefaults = self::generateUserLevelDefaultsFrom($user_id, $role);
        // merge user data with defaults
        $userTableData = array_merge($userTableDefaults, $userData);
        $userCapabilitiesData = array_merge($userCapabilitiesDefaults, $userData);
        $userLevelData = array_merge($userLevelDefaults, $userData);
        return array($userLevelDefaults, $userTableData, $userCapabilitiesData);
    }

    protected static function generateUserDefaultsFrom($user_login, $user_id, $role = 'subscriber')
    {
        $usersTableDefaults = array(
            'ID' => $user_id,
            'user_login' => $user_login,
            'user_pass' => md5($user_login),
            'user_nicename' => $user_login,
            'user_email' => $user_login . "@example.com",
            'user_url' => 'http://www.example.com',
            'user_registered' => DateMaker::now(),
            'user_status' => '0',
            'display_name' => $user_login);
        return $usersTableDefaults;
    }

    protected static function generateCapabilitiesDefaultsFrom($user_id, $role)
    {
        $capabilitiesDefaults = array(
            'umeta_id' => null,
            'user_id' => $user_id,
            'meta_key' => 'wp_capabilities',
            'meta_value' => serialize(array($role, '1'))
        );
        return $capabilitiesDefaults;
    }

    protected static function generateUserLevelDefaultsFrom($user_id, $role)
    {
        $intRole = 0;
        if (isset(self::$userRolesToLevels[$role])) {
            $intRole = self::$userRolesToLevels[$role];
        }
        $userLevelDefaults = array(
            'umeta_id' => null,
            'user_id' => $user_id,
            'meta_key' => 'wp_user_level',
            'meta_value' => $intRole
        );
        return $userLevelDefaults;
    }
}
<?php

    namespace tad\WPBrowser\Generators\User;

class Roles
{

    public static function getLevelForRole($role = 'subscriber')
    {
        $map = [
            'subscriber'    => 0,
            'contributor'   => 1,
            'author'        => 2,
            'editor'        => 7,
            'administrator' => 10,
            ''              => 0 // no role for the site
        ];
        return array_key_exists($role, $map) ? $map[$role] : $map['subscriber'];
    }
}

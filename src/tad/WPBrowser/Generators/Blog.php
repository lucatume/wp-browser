<?php

namespace tad\WPBrowser\Generators;

class Blog
{

    public static function makeDefaults($isSubdomainMultisiteInstall = true)
    {
        return [
            'site_id'      => 1,
            'domain'       => 'subdomain',
            'path'         => '/',
            'registered'   => Date::now(),
            'last_updated' => Date::now(),
            'public'       => 1,
            'archived'     => 0,
            'mature'       => 0,
            'spam'         => 0,
            'deleted'      => 0,
            'lang_id'      => 0
        ];
    }
}

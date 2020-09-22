<?php
/**
 * Blog data generator.
 *
 * @package tad\WPBrowser\Generators
 */

namespace tad\WPBrowser\Generators;

/**
 * Class Blog
 *
 * @package tad\WPBrowser\Generators
 */
class Blog
{

    /**
     * Generates the data for a subdomain installation.
     *
     * @return array<string,mixed> The blog data.
     */
    public static function makeDefaults()
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

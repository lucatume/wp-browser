<?php
/**
 * Blog data generator.
 *
 * @package lucatume\WPBrowser\Generators
 */

namespace lucatume\WPBrowser\Generators;

/**
 * Class Blog
 *
 * @package lucatume\WPBrowser\Generators
 */
class Blog
{

    /**
     * Generates the data for a subdomain installation.
     *
     * @return array{
     *     site_id: int,
     *     domain: string,
     *     path: string,
     *     registered: string|false,
     *     last_updated: string|false,
     *     public: int,
     *     archived: int,
     *     mature: int,
     *     spam: int,
     *     deleted: int,
     *     lang_id: int
     * } The blog data.
     */
    public static function makeDefaults(): array
    {
        return [
            'site_id' => 1,
            'domain' => 'subdomain',
            'path' => '/',
            'registered' => Date::now(),
            'last_updated' => Date::now(),
            'public' => 1,
            'archived' => 0,
            'mature' => 0,
            'spam' => 0,
            'deleted' => 0,
            'lang_id' => 0
        ];
    }
}

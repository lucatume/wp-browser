<?php
/**
 * Links data generator.
 *
 * @package tad\WPBrowser\Generators
 */

namespace tad\WPBrowser\Generators;

/**
 * Class Links
 *
 * @package tad\WPBrowser\Generators
 */
class Links
{
    /**
     * Generates an array of default links table entries.
     *
     * @return array<string,mixed> The generated data.
     */
    public static function getDefaults()
    {
        return [
            'link_url'         => 'http://wordpress.org',
            'link_name'        => 'WordPress',
            'link_image'       => '',
            'link_target'      => '',
            'link_description' => '',
            'link_visible'     => 'Y',
            'link_owner'       => 1,
            'link_rating'      => 0,
            'link_updated'     => Date::now(),
            'link_rel'         => '',
            'link_notes'       => '',
            'link_rss'         => '',
        ];
    }
}

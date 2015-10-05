<?php

namespace tad\WPBrowser\Utils;

use BaconStringUtils\Slugifier;

/**
 * Generates WordPress posts to be inserted in the database.
 */
class Post
{
    protected static $count = 1;

    /**
     * Generates a WordPress page entry.
     *
     * @param  int $ID The post ID to use.
     * @param  string $url The site url.
     *
     * @param array $data
     * @return array Key/value pairs to be used to insert the page in the database.
     */
    public static function makePage($ID, $url = 'http://www.example.com', array $data = array())
    {
        $defaults = self::generateDefaultsForType($ID, 'page', $url);
        self::$count++;
        return array_merge($defaults, $data);
    }

    /**
     * Generates a default post entry.
     *
     * @param  int $ID The post ID to use.
     * @param  string $type The type (e.g. 'event') of the post that will be inserted.
     * @param  string $url The site url
     *
     * @return array       Key\value pairs of post entry with default values.
     */
    protected static function generateDefaultsForType($ID, $type, $url)
    {
        list($title, $content) = self::generateTitleAndContent();
        $guid = '';
        $type == 'page' ? $guid = self::generatePageGuid($ID, $url) : $guid = self::generatePostGuid($ID, $url);
        $defaults = array(
            'ID' => $ID,
            'post_author' => 1,
            'post_date' => Date::now(),
            'post_date_gmt' => Date::gmtNow(),
            'post_content' => $content,
            'post_title' => $title,
            'post_excerpt' => '',
            'post_status' => 'publish',
            'comment_status' => 'open',
            'ping_status' => 'open',
            'post_password' => '',
            'post_name' => (new Slugifier())->slugify($title),
            'to_ping' => '',
            'pinged' => '',
            'post_modified' => Date::now(),
            'post_modified_gmt' => Date::gmtNow(),
            'post_content_filtered' => '',
            'post_parent' => 0,
            'guid' => $guid,
            'menu_order' => 0,
            'post_type' => $type
        );
        return $defaults;
    }

    /**
     * Generates random title and content for a post.
     *
     * @return array      Random generated title and content of the post.
     */
    protected static function generateTitleAndContent()
    {
        // create a default value array
        $title = sprintf('Post title %d', self::$count);
        $content = sprintf('Post content %d', self::$count);
        return array($title, $content);
    }

    /**
     * Generates a page guid.
     *
     * @param  int $ID The page id.
     * @param  string $url The site url.
     *
     * @return string      The database guid entry.
     */
    protected static function generatePageGuid($ID, $url)
    {
        $guid = rtrim($url, '/') . '/?page_id=' . $ID;
        return $guid;
    }

    /**
     * Generates a post guid.
     *
     * @param  int $ID The post id.
     * @param  string $url The site url.
     *
     * @return string      The database guid entry.
     */
    protected static function generatePostGuid($ID, $url)
    {
        $guid = rtrim($url, '/') . '/?p=' . $ID;
        return $guid;
    }

    public static function makePost($ID, $url = 'http://www.example.com', array $data = array())
    {
        $defaults = self::generateDefaultsForType($ID, 'post', $url);
        return array_merge($defaults, $data);
    }
}
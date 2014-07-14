<?php

namespace tad\wordpress\maker;

use Badcow\LoremIpsum\Generator;
use tad\utils\Str;
    
/**
 * Generates WordPress posts to be inserted in the database.
 */
class PostMaker
{
    /**
     * Generates a WordPress page entry.
     *
     * @param  int $ID  The post ID to use.
     * @param  string $url The site url.
     *
     * @return array      Key/value pairs to be used to insert the page in the database.
     */
    public static function makePage($ID, $url = 'http://www.example.com', array $data = array())
    {
        $defaults = self::generateDefaultsForType($ID, 'page', $url);
        return array_merge($defaults, $data);
    }

    /**
     * Generates a default post entry.
     *
     * @param  int $ID   The post ID to use.
     * @param  string $type The type (e.g. 'event') of the post that will be inserted.
     * @param  string $url  The site url
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
            'post_date' => DateMaker::now(),
            'post_date_gmt' => DateMaker::now(),
            'post_content' => $content,
            'post_title' => $title,
            'post_excerpt' => '',
            'post_status' => 'publish',
            'comment_status' => 'open',
            'ping_status' => 'open',
            'post_password' => '',
            'post_name' => lcfirst(Str::hyphen($title)),
            'to_ping' => '',
            'pinged' => '',
            'post_modified' => DateMaker::now(),
            'post_modified_gmt' => DateMaker::now(),
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
        $loremMaker = new Generator();
        // create a default value array
        $title = implode('', $loremMaker->getSentences(1));
        $content = implode("\n", $loremMaker->getParagraphs(3));
        return array($title, $content);
    }

    /**
     * Generates a page guid.
     *
     * @param  int $ID  The page id.
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
     * @param  int $ID  The post id.
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
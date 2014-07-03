<?php

namespace tad\wordpress\maker;

use Badcow\LoremIpsum\Generator;
use tad\utils\Str;

class PostMaker
{
    public static function makePage($ID, $url = 'http://www.example.com', array $data = array())
    {
        $defaults = self::generateDefaultsForType($ID, 'page', $url);
        return array_merge($defaults, $data);
    }

    protected static function generateDefaultsForType($ID, $type, $url)
    {
        list($title, $content) = self::generateTitleAndContent($ID, $url);
        $guid = '';
        $type == 'page' ? $guid = self::generatePageGuid($ID, $url) : $guid = self::generatePostGuid($ID, $url);
        $defaults = array(
            'ID' => $ID,
            'post_author' => 1,
            'post_date' => DateMaker::now(),
            'post_date_gmt' => DateMaker::gmnow(),
            'post_content' => $content,
            'post_title' => $title,
            'post_excerpt' => '',
            'post_status' => 'publish',
            'comment_status' => 'open',
            'ping_status' => 'open',
            'post_password' => '',
            'post_name' => Str::hyphen($title),
            'to_ping' => '',
            'pinged' => '',
            'post_modified' => DateMaker::now(),
            'post_modified_gmt' => DateMaker::gmnow(),
            'post_content_filtered' => '',
            'post_parent' => 0,
            'guid' => $guid,
            'menu_order' => 0,
            'post_type' => $type
        );
        return $defaults;
    }

    protected static function generateTitleAndContent($ID, $url)
    {
        if (!is_int($ID)) {
            throw new \BadMethodCallException('Id must be an int', 1);
        }
        if (!is_string($url)) {
            throw new \BadMethodCallException('Url must be a string', 2);
        }
        $loremMaker = new Generator();
        // create a default value array
        $title = implode('', $loremMaker->getSentences(1));
        $content = implode("\n", $loremMaker->getParagraphs(3));
        return array($title, $content);
    }

    protected static function generatePageGuid($ID, $url)
    {
        $guid = rtrim($url, '/') . '/?page_id=' . $ID;
        return $guid;
    }

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
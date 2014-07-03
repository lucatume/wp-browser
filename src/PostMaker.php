<?php

namespace tad\wordpress\maker;

use Badcow\LoremIpsum\Generator;
use tad\utils\Str;

class PostMaker
{
    public static function makePost($ID, $url = 'http://www.example.com', array $data = array())
    {
        $loremMaker = new Generator();
        // create a default value array
        $title = implode('', $loremMaker->getSentences(1));
        $content = implode("\n", $loremMaker->getParagraphs(3));
        $guid = rtrim($url);
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
            'post_type' => 'post'
        );
        return array_merge($defaults, $data);
    }
}
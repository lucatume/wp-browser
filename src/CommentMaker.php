<?php

namespace tad\wordpress\maker;


use Badcow\LoremIpsum\Generator;

class CommentMaker
{

    public static function makeComment($comment_ID, $comment_post_ID, array $data = array())
    {
        $defaults = self::generateDefaultsFor($comment_ID, $comment_post_ID);
        return array_merge($defaults, $data);
    }

    protected static function generateCommentContent()
    {
        $lorem = new Generator();
        return implode('', $lorem->getSentences(1));
    }

    protected static function generateDefaultsForType($comment_ID, $comment_post_ID)
    {
        $randIP = "" . mt_rand(0, 255) . "." . mt_rand(0, 255) . "." . mt_rand(0, 255) . "." . mt_rand(0, 255);
        $content = self::generateCommmentContent();
        $defaults = array(
            'comment_ID' => $comment_ID,
            'comment_post_ID' => $comment_post_ID,
            'comment_author' => 'John Doe',
            'comment_author_email' => 'john.doe@example.com',
            'comment_author_url' => 'http://www.johndoe.com',
            'comment_author_IP' => $randIP,
            'comment_date' => DateMaker::now(),
            'comment_date_gmt' => DateMaker::now(),
            'comment_content' => $content,
            'comment_karma' => 0,
            'comment_approved' => 1,
            'comment_agent' => '',
            'comment_type' => '',
            'comment_parent' => 0,
            'user_id' => 0
        );
        return $defaults;
    }
}
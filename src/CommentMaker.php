<?php

namespace tad\wordpress\maker;

use Badcow\LoremIpsum\Generator;
    

class CommentMaker
{
    /**
     * Generates a comment to be used in a WordPress database.
     *
     * @param  int $comment_ID          The comment ID to use.
     * @param  int $comment_post_ID     The ID of the post the comment relates to.
     * @param  array  $data             The optional data to be used to generate the comment.
     *
     * @return array                    A column as key array of comment content.
     */
    public static function makeComment($comment_ID, $comment_post_ID, array $data = array())
    {
        $defaults = self::generateDefaultsFor($comment_ID, $comment_post_ID);
        return array_merge($defaults, $data);
    }

    /**
     * Generates a random comment content in a lorem ipsum fashion.
     *
     * @return string  The comment content. 
     */
    protected static function generateCommentContent()
    {
        $lorem = new Generator();
        return implode('', $lorem->getSentences(1));
    }

    /**
     * Generate the complete comment entry default structure.
     *
     * @param  int $comment_ID      The comment id.
     * @param  int $comment_post_ID The if of the post the comment refers to.
     *
     * @return array                An associative array of column/default values.
     */
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
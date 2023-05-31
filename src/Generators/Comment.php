<?php
/**
 * Comment data generator.
 *
 * @package lucatume\WPBrowser\Generators
 */

namespace lucatume\WPBrowser\Generators;

/**
 * Class Comment
 *
 * @package lucatume\WPBrowser\Generators
 */
class Comment
{

    /**
     * Generates a comment to be used in a WordPress database.
     * generateDefaultsFor*
     *
     * @param int $comment_post_ID               The ID of the post the comment relates to.
     * @param array<int|string,mixed> $overrides The optional data to be used to generate the comment.
     *
     * @return array<int|string,mixed>                    A column as key array of comment content.
     */
    public static function makeComment(int $comment_post_ID, array $overrides = array()): array
    {
        $defaults = self::generateDefaultsFor($comment_post_ID);

        return array_merge($defaults, array_intersect_key($overrides, $defaults));
    }

    /**
     * Generate the complete comment entry default structure.
     *
     * @param int $comment_post_ID The if of the post the comment refers to.
     *
     * @return array{
     *     comment_post_ID: int,
     *     comment_author: string,
     *     comment_author_email: string,
     *     comment_author_url: string,
     *     comment_author_IP: string,
     *     comment_date: string|false,
     *     comment_date_gmt: string|false,
     *     comment_content: string,
     *     comment_karma: string,
     *     comment_approved: string,
     *     comment_agent: string,
     *     comment_type: string,
     *     comment_parent: int,
     *     user_id: int
     * } An associative array of column/default values.
     */
    protected static function generateDefaultsFor(int $comment_post_ID): array
    {
        $content = "Hi, this is a comment.\nTo delete a comment, just log in and view the post&#039;s comments. "
            . 'There you will have the option to edit or delete them.';
        $defaults = [
            'comment_post_ID' => $comment_post_ID,
            'comment_author' => 'Mr WordPress',
            'comment_author_email' => '',
            'comment_author_url' => 'https://wordpress.org/',
            'comment_author_IP' => '',
            'comment_date' => Date::now(),
            'comment_date_gmt' => Date::gmtNow(),
            'comment_content' => $content,
            'comment_karma' => '0',
            'comment_approved' => '1',
            'comment_agent' => '',
            'comment_type' => '',
            'comment_parent' => 0,
            'user_id' => 0,
        ];

        return $defaults;
    }
}

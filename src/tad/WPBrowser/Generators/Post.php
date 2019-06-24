<?php

    namespace tad\WPBrowser\Generators;

    use BaconStringUtils\Slugifier;

    /**
     * Generates WordPress posts to be inserted in the database.
     */
class Post
{

    /**
     * Generates a WordPress page entry.
     *
     * @param  int $ID The post ID to use.
     * @param  string $url The site url.
     *
     * @param array $data
     *
     * @return array Key/value pairs to be used to insert the page in the database.
     */
//      public static function makePage(
//          $ID,
//          $url = 'http://www.example.com',
//          array $data = array()
//      ) {
//          $defaults = self::getDefaults( $ID, 'page', $url );
//          self::$count ++;
//
//          return array_merge( $defaults, $data );
//      }

    protected static function getDefaults($id, $url)
    {
        $title    = self::generateTitle($id);
        $defaults = array(
            'post_author'           => 1,
            'post_date'             => Date::now(),
            'post_date_gmt'         => Date::gmtNow(),
            'post_content'          => self::generateContent($id),
            'post_title'            => $title,
            'post_excerpt'          => self::generateExcerpt($id),
            'post_status'           => 'publish',
            'comment_status'        => 'open',
            'ping_status'           => 'open',
            'post_password'         => '',
            'post_name'             => ( new Slugifier() )->slugify($title),
            'to_ping'               => '',
            'pinged'                => '',
            'post_modified'         => Date::now(),
            'post_modified_gmt'     => Date::gmtNow(),
            'post_content_filtered' => '',
            'post_parent'           => 0,
            'guid'                  => "{$url}/?p={$id}",
            'menu_order'            => 0,
            'post_type'             => 'post'
        );

        return $defaults;
    }

    /**
     * Generates a page guid.
     *
     * @param  int $ID The page id.
     * @param  string $url The site url.
     *
     * @return string      The database guid entry.
     */
    protected static function generatePageGuid(
        $ID,
        $url
    ) {
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
    protected static function generatePostGuid(
        $ID,
        $url
    ) {
        $guid = rtrim($url, '/') . '/?p=' . $ID;

        return $guid;
    }

    public static function makePost($id, $url = 'http://www.example.com', array $data = [ ])
    {
        return array_merge(self::getDefaults($id, $url), $data);
    }

    /**
     * @return string
     */
    protected static function generateTitle($id)
    {
        return sprintf('Post %d title', $id);
    }

    /**
     * @return string
     */
    protected static function generateContent($id)
    {
        return sprintf('Post %d content', $id);
    }

    private static function generateExcerpt($id)
    {
        return sprintf('Post %d excerpt', $id);
    }
}

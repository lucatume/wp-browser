<?php


namespace tad\WPBrowser\Module\WPLoader;

class FactoryStore
{

    /**
     * @var \WP_UnitTest_Factory_For_Post
     */
    public $post;

    /**
     * @var \WP_UnitTest_Factory_For_Bookmark
     */
    public $bookmark;

    /**
     * @var \WP_UnitTest_Factory_For_Attachment
     */
    protected $attachment;

    /**
     * @var \WP_UnitTest_Factory_For_User
     */
    public $user;

    /**
     * @var \WP_UnitTest_Factory_For_Comment
     */
    public $comment;

    /**
     * @var \WP_UnitTest_Factory_For_Blog
     */
    public $blog;

    /**
     * @var \WP_UnitTest_Factory_For_Network
     */
    public $network;

    /**
     * @var \WP_UnitTest_Factory_For_Term
     */
    public $term;

    /**
     * Sets up and instantiates the factories.
     */
    public function setupFactories()
    {
        require_once dirname(dirname(dirname(dirname(__DIR__)))) . '/includes/factory.php';

        $this->post         = new \WP_UnitTest_Factory_For_Post();
        $this->bookmark     = new \WP_UnitTest_Factory_For_Bookmark();
        $this->attachment   = new \WP_UnitTest_Factory_For_Attachment();
        $this->user         = new \WP_UnitTest_Factory_For_User();
        $this->comment      = new \WP_UnitTest_Factory_For_Comment();
        $this->blog         = new \WP_UnitTest_Factory_For_Blog();
        $this->network      = new \WP_UnitTest_Factory_For_Network();
        $this->term         = new \WP_UnitTest_Factory_For_Term();
    }

    public function __get($name)
    {
        if ($name==='attachment') {
            require_once(ABSPATH . 'wp-admin/includes/image.php');
        }

        return $this->{$name};
    }
}

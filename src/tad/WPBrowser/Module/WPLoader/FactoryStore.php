<?php
/**
 * Handles the instantiation of the WordPress test factories.
 *
 * @package tad\WPBrowser\Module\WPLoader
 */


namespace tad\WPBrowser\Module\WPLoader;

use Codeception\Exception\ModuleException;
use Codeception\Module\WPLoader;

/**
* Class FactoryStore
 *
 * @package tad\WPBrowser\Module\WPLoader
 *
 * @property \WP_UnitTest_Factory_For_Post $post
 * @property \WP_UnitTest_Factory_For_Bookmark $bookmark
 * @property \WP_UnitTest_Factory_For_Attachment $attachment
 * @property \WP_UnitTest_Factory_For_User $user
 * @property \WP_UnitTest_Factory_For_Comment $comment
 * @property \WP_UnitTest_Factory_For_Blog $blog
 * @property \WP_UnitTest_Factory_For_Network $network
 * @property \WP_UnitTest_Factory_For_Term $term
 */
class FactoryStore
{

    /**
     * @var \WP_UnitTest_Factory_For_Post
     */
    protected $post;

    /**
     * @var \WP_UnitTest_Factory_For_Bookmark
     */
    protected $bookmark;

    /**
     * @var \WP_UnitTest_Factory_For_Attachment
     */
    protected $attachment;

    /**
     * @var \WP_UnitTest_Factory_For_User
     */
    protected $user;

    /**
     * @var \WP_UnitTest_Factory_For_Comment
     */
    protected $comment;

    /**
     * @var \WP_UnitTest_Factory_For_Blog
     */
    protected $blog;

    /**
     * @var \WP_UnitTest_Factory_For_Network
     */
    protected $network;

    /**
     * @var \WP_UnitTest_Factory_For_Term
     */
    protected $term;

    /**
     * Lazily instantiate the factories if required.
     *
     * @param string $name The name of the property being accessed.
     *
     * @return \WP_UnitTest_Factory_For_Thing The required factory instance.
     */
    public function __get($name)
    {
        if ($this->{$name} !== null) {
            return $this->{$name};
        }

        require_once dirname(dirname(dirname(dirname(__DIR__)))) . '/includes/factory.php';

        switch ($name) {
            case 'post':
                $this->post         = new \WP_UnitTest_Factory_For_Post();
                break;
            case 'bookmark':
                $this->bookmark     = new \WP_UnitTest_Factory_For_Bookmark();
                break;
            case 'attachment':
                require_once ABSPATH . 'wp-admin/includes/image.php';
                $this->attachment   = new \WP_UnitTest_Factory_For_Attachment();
                break;
            case 'user':
                $this->user         = new \WP_UnitTest_Factory_For_User();
                break;
            case 'comment':
                $this->comment      = new \WP_UnitTest_Factory_For_Comment();
                break;
            case 'blog':
                if (! function_exists('is_multisite') || ! is_multisite()) {
                    throw new ModuleException(
                        WPLoader::class,
                        'The `blog` factory can only be used in multisite context:' .
                                         'in `WPLoader` module configuration set `multisite: true`; read more at ' .
                                         'https://wpbrowser.wptestkit.dev/summary/modules/wploader#configuration'
                    );
                }
                $this->blog         = new \WP_UnitTest_Factory_For_Blog();
                break;
            case 'network':
                $this->network      = new \WP_UnitTest_Factory_For_Network();
                break;
            case 'term':
                $this->term         = new \WP_UnitTest_Factory_For_Term();
                break;
        }

        return $this->{$name};
    }
}

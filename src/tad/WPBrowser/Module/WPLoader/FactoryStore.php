<?php
/**
 * Handles the instantiation of the WordPress test factories.
 *
 * @package tad\WPBrowser\Module\WPLoader
 */

namespace tad\WPBrowser\Module\WPLoader;

use Codeception\Exception\ModuleException;
use Codeception\Module\WPLoader;
use WP_UnitTest_Factory_For_Thing as ThingFactory;

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
     * @return ThingFactory The required factory instance.
     *
     * @throws \InvalidArgumentException If the required factory slug is not a supported one.
     */
    public function __get($name)
    {
        return $this->getThingFactory($name);
    }

    /**
     * Returns a factory of the required type.
     *
     * @since TBD
     *
     * @param string $name The slug of the factory to return an instance of.
     *
     * @return ThingFactory The factory instance for the required slug.
     *
     * @throws \InvalidArgumentException If the required factory slug is not a supported one.
     */
    public function getThingFactory($name)
    {
        if ($this->{$name} instanceof ThingFactory) {
            return $this->{$name};
        }

        require_once dirname(dirname(dirname(dirname(__DIR__)))) . '/includes/factory.php';

        $factoryForThing = null;

        switch ($name) {
            default:
            case 'post':
                $this->post      = new \WP_UnitTest_Factory_For_Post();
                $factoryForThing = $this->post;
                break;
            case 'bookmark':
                $this->bookmark  = new \WP_UnitTest_Factory_For_Bookmark();
                $factoryForThing = $this->bookmark;
                break;
            case 'attachment':
                require_once ABSPATH . 'wp-admin/includes/image.php';
                $this->attachment = new \WP_UnitTest_Factory_For_Attachment();
                $factoryForThing  = $this->attachment;
                break;
            case 'user':
                $this->user      = new \WP_UnitTest_Factory_For_User();
                $factoryForThing = $this->user;
                break;
            case 'comment':
                $this->comment   = new \WP_UnitTest_Factory_For_Comment();
                $factoryForThing = $this->comment;
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
                $this->blog      = new \WP_UnitTest_Factory_For_Blog();
                $factoryForThing = $this->blog;
                break;
            case 'network':
                $this->network   = new \WP_UnitTest_Factory_For_Network();
                $factoryForThing = $this->network;
                break;
            case 'term':
                $this->term      = new \WP_UnitTest_Factory_For_Term();
                $factoryForThing = $this->term;
                break;
        }

        if (!$factoryForThing instanceof ThingFactory) {
            throw new \InvalidArgumentException("No factory available for key '{$name}'");
        }

        return $factoryForThing;
    }
}

<?php

namespace Codeception\Module;

use Codeception\Exception\ModuleConfigException;
use Codeception\Lib\ModuleContainer;
use Codeception\Module;
use tad\WPBrowser\Services\WP\Bootstrapper;

class WPRequests extends Module
{

    protected $requiredFields = ['wpRootFolder'];

    /**
     * @var Bootstrapper
     */
    protected $wp;

    /**
     * @var string The absolute path to the WordPress installation `wp-load.php` file.
     */
    protected $wpLoadPath;

    /**
     * WPRequests constructor.
     *
     * @param ModuleContainer $moduleContainer
     * @param null $config
     * @param Bootstrapper|null $wpBootstrapper
     */
    public function __construct(ModuleContainer $moduleContainer, $config, Bootstrapper $wpBootstrapper = null)
    {
        parent::__construct($moduleContainer, $config);
        $this->wp = $wpBootstrapper ? $wpBootstrapper : new Bootstrapper();
    }

    /**
     * Initializes the module.
     *
     * @throws ModuleConfigException
     */
    public function _initialize()
    {
        $wpRootFolder = $this->config['wpRootFolder'];
        if (!is_dir($wpRootFolder)) {
            throw new ModuleConfigException(static::class, 'WordPress root folder is not a folder');
        }
        if (!is_readable($wpRootFolder)) {
            throw new ModuleConfigException(static::class, 'WordPress root folder is not readable');
        }
        $wpLoad = $wpRootFolder . DIRECTORY_SEPARATOR . 'wp-load.php';
        if (!file_exists($wpLoad)) {
            throw new ModuleConfigException(static::class, 'WordPress root folder does not contain a wp-load.php file');
        }
        if (!is_readable($wpLoad)) {
            throw new ModuleConfigException(static::class, 'wp-load.php file is not readable');
        }
        $this->wpLoadPath = $wpLoad;

        $this->wp->setWpLoadPath($this->wpLoadPath);
    }

    /**
     * Generates a nonce for the given user and action.
     *
     * @param string $action
     * @param int $user Defaults to visitor user with ID `0`
     */
    public function createNonce($action, array $credentials)
    {
        $nonce = $this->wp->createNonce($action, $credentials);
        if (empty($nonce)) {
            throw new \RuntimeException(static::class . ': could not generate nonce for action [' . $action . '] with credentials ' . json_encode($credentials));
        }

        return $nonce;
    }
}

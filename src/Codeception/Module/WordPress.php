<?php

namespace Codeception\Module;


use Codeception\Lib\Framework;
use Codeception\Lib\ModuleContainer;
use Codeception\Module;

class WordPress extends Framework
{
    /**
     * @var WPLoader
     */
    private $loader;

    /**
     * @var array
     */
    protected $requiredFields = array('wpRootFolder', 'dbName', 'dbHost', 'dbUser', 'dbPassword',);

    protected $config = array(
        'wpDebug' => false,
        'multisite' => false,
        'dbCharset' => 'utf8',
        'dbCollate' => '',
        'tablePrefix' => 'wptests_',
        'domain' => 'example.org',
        'adminEmail' => 'admin@example.org',
        'title' => 'Test Blog',
        'phpBinary' => 'php',
        'language' => '',
        'configFile' => '',
        'pluginsFolder' => '',
        'plugins' => '',
        'activatePlugins' => '',
        'bootstrapActions' => ''
    );

    /**
     * WordPress constructor.
     *
     * @param ModuleContainer $moduleContainer
     * @param null $config
     * @param WPLoader|null $loader
     */
    public function __construct(ModuleContainer $moduleContainer, $config, WPLoader $loader = null)
    {
        $config = array_merge($this->config, (array)$config);

        parent::__construct($moduleContainer, $config);
        $this->loader = $loader ? $loader : new WPLoader($moduleContainer, $config);
    }

    public function _initialize()
    {
        $this->loader->_initialize();
    }
}
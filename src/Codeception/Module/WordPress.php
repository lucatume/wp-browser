<?php

namespace Codeception\Module;


use Codeception\Lib\Connector\Universal as UniversalConnector;
use Codeception\Lib\Framework;
use Codeception\Lib\ModuleContainer;
use Codeception\Module;
use Codeception\TestInterface;

class WordPress extends Framework
{
    /**
     * @var string
     */
    protected $index;
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
        'bootstrapActions' => '',
    );
    /**
     * @var WPLoader
     */
    private $loader;

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
        $config['isolatedInstall'] = false;

        parent::__construct($moduleContainer, $config);
        $this->loader = $loader ? $loader : new WPLoader($moduleContainer, $config);
        $this->index = __DIR__ . '/wp-index.php';
    }

    public function _initialize()
    {
        $this->loader->_initialize();
    }

    public function _before(TestInterface $test)
    {
        $this->client = new UniversalConnector();
        $this->client->followRedirects(true);
        $this->client->setIndex($this->index);
    }
}
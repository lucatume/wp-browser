<?php

namespace Codeception\Module;


use Codeception\Lib\Connector\Universal as UniversalConnector;
use Codeception\Lib\Framework;
use Codeception\Lib\ModuleContainer;
use Codeception\Module;
use Codeception\TestInterface;
use tad\Adapters\WP;

class WordPress extends Framework
{
    /**
     * @var string The absolute path to the index file that should be loaded to handle requests.
     */
    protected $index;
    
    /**
     * @var array
     */
    protected $requiredFields = array('wpRootFolder', 'dbName', 'dbHost', 'dbUser', 'dbPassword',);

    /**
     * @var array
     */
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
     * @var WP
     */
    protected $wp;

    /**
     * @var WPLoader
     */
    protected $loader;

    /**
     * WordPress constructor.
     *
     * @param ModuleContainer $moduleContainer
     * @param null $config
     * @param WPLoader|null $loader
     */
    public function __construct(ModuleContainer $moduleContainer, $config, WPLoader $loader = null, WP $wp = null)
    {
        $config = array_merge($this->config, (array)$config);
        $config['isolatedInstall'] = false;

        parent::__construct($moduleContainer, $config);
        $this->loader = $loader ? $loader : new WPLoader($moduleContainer, $config);
        $this->wp = $wp ? $wp : new WP;
        
        $this->index = __DIR__ . '/wp-index.php';
    }

    public function _initialize()
    {
        $this->bootstrapWordPress();
        
        /** @var \wpdb $wpdb */
        global $wpdb;
        
        
    }

    public function _before(TestInterface $test)
    {
        $this->client = new UniversalConnector();
        $this->client->followRedirects(true);
        $this->client->setIndex($this->index);
    }

    /**
     * Sets a new permalink structure in the database and soft flushes the rewrite rules.
     *
     * @param $permalinksStructure
     */
    public function setPermalinksStructureTo($permalinksStructure)
    {
        $this->wp->update_option('permalink_structure', $permalinksStructure);
        $this->flushRewriteRules();
    }

    /**
     * Soft flushes and regenerates the site rewrite rules.
     */
    public function flushRewriteRules()
    {
        $this->wp->soft_flush_rewrite_rules();
    }

    private function bootstrapWordPress()
    {
        $this->loader->_initialize();
    }
}
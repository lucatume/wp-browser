<?php

namespace Codeception\Module;


use Codeception\Exception\ModuleConfigException;
use Codeception\Module;

/**
 * Class WPLoader
 *
 * Loads a WordPress installation for testing purposes.
 *
 * The class is a Codeception adaptation of WordPress automated testing suite,
 * see [here](http://make.wordpress.org/core/handbook/automated-testing/),
 * and takes care of configuring and installing a WordPress installation.
 * To work properly the \WP_UnitTestCase should be used to run the tests
 * in a PHPUnit-like behaviour.
 *
 * @package Codeception\Module
 */
class WPLoader extends Module
{
    
    /**
     * The fields the user will have to set to legit values for the module to run.
     *
     * wpRootFolder - the absolute path to the root folder of the WordPress
     * installation to use for testing, the ABSPATH global value.
     * dbNAme - the name of the database to use for the tests, will be trashed
     * during tests so take care, will be the DB_NAME global.
     * dbHost - the host the database can be found at, will be the DB_HOST
     * global.
     * dbUser - the database privileged user, should GRANT ALL on the database,
     * will be the DB_USER global.
     * dbPassword - the password for the user, will be the DB_PASSWORD global.
     *
     * @var array
     */
    protected $requiredFields = array(
        'wpRootFolder',
        'dbName',
        'dbHost',
        'dbUser',
        'dbPassword'
    );
    
    /**
     * The fields the user will be able to override while running tests.
     *
     * All of the fields have a correspondant in the standard `wp-tests-config.php`
     * file found in [WordPress automated testing suite.](http://make.wordpress.org/core/handbook/automated-testing/)
     *
     * wpDebug - bool, def. `true`, the WP_DEBUG global value.
     * multisite - bool, def. `false`, if set to `true` will create a
     * multisite instllation, the WP_TESTS_MULTISITE global value.
     * dbCharset - string, def. `utf8`, the DB_CHARSET global value.
     * dbCollate - string, def. ``, the DB_COLLATE global value.
     * tablePrefix - string, def. `wptests_`, the WP_TESTS_TABLE_PREFIX value.
     * domain - string, def. `example.org`, the root URL of the site, the
     * WP_TESTS_DOMAIN global value.
     * adminEmail - string, def. `admin@example.org`, the admin email, the
     * WP_TEST_EMAIL global value.
     * title - string, def. `Test Blog`, the blog title, the WP_TESTS_TITLE
     * global value.
     * phpBinary - string, def. `php`, the php bin command, the WP_PHP_BINARY
     * global value.
     * language - string, def. ``, the installation language, the WPLANG global
     * value.
     *
     * @var array
     */
    protected $config = array(
        'wpDebug' => true,
        'multisite' => false,
        'dbCharset' => 'utf8',
        'dbCollate' => '',
        'tablePrefix' => 'wptests_',
        'domain' => 'example.org',
        'adminEmail' => 'admin@example.org',
        'title' => 'Test Blog',
        'phpBinary' => 'php',
        'language' => ''
    );
    
    /**
     * The path to the modified tests bootstrap file.
     *
     * @var string
     */
    protected $wpBootstrapFile;
    
    public static $includeInheritedActions = true;
    public static $onlyActions = array();
    public static $excludeActions = array();
    
    /**
     * Defines the globals needed by WordPress to run to user set values.
     *
     * The method replaces the "wp-tests-config.php" file the original
     * testing workflow included to allow run-time customization of the
     * globals in a Codeception friendly way.
     *
     * @return void
     */
    protected function defineGlobals()
    {
        // allow me not to bother with traling slashes
        $wpRootFolder = rtrim($this->config['wpRootFolder'], '/') . '/';
        $constants = array(
            'ABSPATH' => $wpRootFolder,
            'DB_NAME' => $this->config['dbName'],
            'DB_USER' => $this->config['dbUser'],
            'DB_PASSWORD' => $this->config['dbPassword'],
            'DB_HOST' => $this->config['dbHost'],
            'DB_CHARSET' => $this->config['dbCharset'],
            'DB_COLLATE' => $this->config['dbCollate'],
            'WP_TESTS_TABLE_PREFIX' => $this->config['tablePrefix'],
            'WP_TESTS_DOMAIN' => $this->config['domain'],
            'WP_TESTS_EMAIL' => $this->config['adminEmail'],
            'WP_TESTS_TITLE' => $this->config['title'],
            'WP_PHP_BINARY' => $this->config['phpBinary'],
            'WPLANG' => $this->config['language'],
            'WP_DEBUG' => $this->config['wpDebug'],
            'WP_TESTS_MULTISITE' => $this->config['multisite'],
        );
        foreach ( $constants as $key => $value ) {
            if ( !defined( $key ) ) {
                define( $key, $value );
            }
        }
    }
    
    /**
     * The function that will initialize the module.
     *
     * The function will set up the WordPress testing configuration and will
     * take care of installing and loading WordPress. The simple inclusion of
     * the module in an test helper class will hence trigger WordPress loading,
     * no explicit method calling on the user side is needed.
     *
     * @return void
     */
    public function _initialize()
    {
        
        // check that the wordpress path exists
        if (!file_exists($this->config['wpRootFolder'])) {
            throw new ModuleConfigException(__CLASS__, "\nWordpress root folder doesn't exists. Please, check that " . $this->config['wpRootFolder'] . " contains a valid WordPress installation.");
        }
        
        // WordPress  will deal with database connection errors
        $this->wpBootstrapFile = dirname(__FILE__) . '/includes/bootstrap.php';
        $this->loadWordPress();
    }
    
    /**
     * Loads WordPress calling the bootstrap file
     *
     * This method does little but wrapping preparing the global space for the
     * original automated testing bootstrap file and taking charge of replacing
     * the original "wp-tests-config.php" file in setting up the globals.
     *
     * @return void
     */
    protected function loadWordPress()
    {
        $this->defineGlobals();
        
        if ($this->config['multisite']) {
            $this->debug('Running as multisite');
        } else {
            $this->debug('Running as single site');
        }
        require_once $this->wpBootstrapFile;
    }
}

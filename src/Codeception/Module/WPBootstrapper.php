<?php

namespace Codeception\Module;

use Codeception\Exception\ModuleConfigException;
use Codeception\Lib\ModuleContainer;
use Codeception\Module;
use SebastianBergmann\GlobalState\Restorer;
use SebastianBergmann\GlobalState\Snapshot;

/**
 * Class WPBootstrapper
 *
 * Bootstraps a WordPress installation to access its functions.
 *
 * @package Codeception\Moduleb
 */
class WPBootstrapper extends Module
{
    use WPSugarMethods;

    protected $requiredFields = ['wpRootFolder'];

    protected $config = [
        'backupGlobals' => true
    ];

    /**
     * @var Snapshot
     */
    protected $globalStateSnapshot = false;

    /**
     * @var string The absolute path to the target WordPress installation wp-load.php file.
     */
    protected $wpLoadPath;

    /**
     * @var Restorer
     */
    private $restorer;

    public function __construct(ModuleContainer $moduleContainer, $config, Restorer $restorer = null)
    {
        parent::__construct($moduleContainer, $config);
        $this->restorer = $restorer ?: new Restorer();
    }

    public function _initialize()
    {
        $wpRootFolder = $this->config['wpRootFolder'];
        if (!is_dir($wpRootFolder)) {
            throw new ModuleConfigException(__CLASS__, 'WordPress root folder is not a folder');
        }
        if (!is_readable($wpRootFolder)) {
            throw new ModuleConfigException(__CLASS__, 'WordPress root folder is not readable');
        }
        $wpLoad = $wpRootFolder . DIRECTORY_SEPARATOR . 'wp-load.php';
        if (!file_exists($wpLoad)) {
            throw new ModuleConfigException(__CLASS__, 'WordPress root folder does not contain a wp-load.php file');
        }
        if (!is_readable($wpLoad)) {
            throw new ModuleConfigException(__CLASS__, 'wp-load.php file is not readable');
        }
        $this->wpLoadPath = $wpLoad;
    }

    public function bootstrapWp()
    {
        include_once($this->wpLoadPath);
        if ($this->config['backupGlobals']) {
            if ($this->globalStateSnapshot === false) {
                $this->globalStateSnapshot = new Snapshot();
                codecept_debug('WPBootstrapper: backed up global state.');
            } else {
                $this->restorer->restoreGlobalVariables($this->globalStateSnapshot);
                $this->restorer->restoreStaticAttributes($this->globalStateSnapshot);
                $this->restoreAllGlobals();
                $this->restoreWpdbConnection();
                codecept_debug('WPBootstrapper: restored global state.');
            }
        }
        codecept_debug('WPBootstrappper: WordPress bootstrapped from wp-load.php file');

        sleep(2);
    }

    private function restoreAllGlobals()
    {
        $superGlobalArrays = $this->globalStateSnapshot->superGlobalArrays();

        $globalVariables = $this->globalStateSnapshot->globalVariables();

        foreach (array_keys($globalVariables) as $key) {
            if ($key != 'GLOBALS' &&
                !in_array($key, $superGlobalArrays) &&
                !$this->globalStateSnapshot->blacklist()->isGlobalVariableBlacklisted($key)
            ) {
                if (!isset($GLOBALS[$key])) {
                    $GLOBALS[$key] = $globalVariables[$key];
                }
            }
        }
    }

    private function restoreWpdbConnection()
    {
        /** @var \wpdb $wpdb */
        global $wpdb;

        if (empty($wpdb)) {
            $wpdb = new \wpdb(DB_USER, DB_PASSWORD, DB_NAME, DB_HOST);
        } else {
            $wpdb->check_connection();
        }

        /**
         * To avoid `mysqli_free_result` errors during the `wpdb::flush()` method set the result to `null`.
         * See `wp-db.php` file line 1425.
         */
        $reflection = new \ReflectionClass($wpdb);
        $resultProperty = $reflection->getProperty('result');
        $resultProperty->setAccessible(true);
        $resultProperty->setValue($wpdb, null);
        $resultProperty->setAccessible(false);
    }

    public function getSnapshot()
    {
        return $this->globalStateSnapshot;
    }
}
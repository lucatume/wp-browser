<?php

namespace Codeception\Module;

// here you can define custom actions
// all public methods declared in helper class will be available in $I

use Codeception\Exception\ModuleConfigException;
use Codeception\Exception\ModuleException;
use Codeception\Module;
use lucatume\WPBrowser\Module\WPDb;

class AcceptanceHelper extends Module
{

    /**
     * Reconfigures the WPDb module during a test and re-imports the default database dump(s).
     *
     * @param array<string,mixed> $configurationOverrides A map of the WPDb configuration parameters to override.
     *
     * @throws ModuleConfigException|ModuleException If the WPDb module cannot be fetched.
     */
    public function reconfigureWPDb(array $configurationOverrides): void
    {
        $wpdb = $this->getWPDbModule();
        $configuration = array_merge($wpdb->_getConfig(), $configurationOverrides);
        $wpdb->_reconfigure($configuration);
        $wpdb->_loadDump();
    }

    /**
     * Returns the current instance of the WPDb module.
     *
     * @return WPDb The current instance of the WPDb module.
     *
     * @throws ModuleException If the WPDb module is not loaded in the suite.
     */
    protected function getWPDbModule(): WPDb
    {
        return $this->getModule(WPDb::class);
    }
}

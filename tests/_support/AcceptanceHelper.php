<?php
namespace Codeception\Module;

// here you can define custom actions
// all public methods declared in helper class will be available in $I

class AcceptanceHelper extends \Codeception\Module
{

    /**
     * Reconfigures the WPDb module during a test and re-imports the default database dump(s).
     *
     * @param array<string,mixed> $configurationOverrides A map of the WPDb configuration parameters to override.
     *
     * @throws \Codeception\Exception\ModuleConfigException If the WPDb module cannot be fetched.
     */
    public function reconfigureWPDb(array $configurationOverrides)
    {
        $wpdb          = $this->getWPDbModule();
        $configuration = array_merge($wpdb->_getConfig(), $configurationOverrides);
        $wpdb->_reconfigure($configuration);
        $wpdb->_loadDump();
    }

    /**
     * Returns the current instance of the WPDb module.
     *
     * @return \Codeception\Module\WPDb The current instance of the WPDb module.
     *
     * @throws \Codeception\Exception\ModuleException If the WPDb module is not loaded in the suite.
     */
    protected function getWPDbModule()
    {
        return $this->getModule('WPDb');
    }
}

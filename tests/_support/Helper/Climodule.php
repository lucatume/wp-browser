<?php
namespace Helper;

// here you can define custom actions
// all public methods declared in helper class will be available in $I

use Codeception\Module;

class Climodule extends Module
{
    public function setCliEnv($key, $value)
    {
        /** @var \lucatume\WPBrowser\Module\WPCLI $cli */
        $cli = $this->getModule(\lucatume\WPBrowser\Module\WPCLI::class);
        $cliConfig = $cli->_getConfig();
        $cliConfig['env'] = isset($cliConfig['env']) ?
            array_merge($cliConfig['env'], [$key => $value])
            : [$key => $value];

        $cli->_reconfigure($cliConfig);
    }
}

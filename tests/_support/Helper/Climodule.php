<?php
namespace Helper;

// here you can define custom actions
// all public methods declared in helper class will be available in $I

use Codeception\Module;
use tad\WPBrowser\Exceptions\WpCliException;

class Climodule extends Module
{
    public function setCliEnv($key, $value)
    {
        try {
            /** @var \Codeception\Module\WPCLI $cli */
            $cli = $this->getModule('WPCLI');
            $cliConfig = $cli->_getConfig();
            $cliConfig['env'] = isset($cliConfig['env']) ?
                array_merge($cliConfig['env'], [$key => $value])
                : [$key => $value];

            $cli->_reconfigure($cliConfig);
        } catch (\Exception $e) {
            throw new WpCliException($e->getMessage(), $e->getCode(), $e);
        }
    }
}

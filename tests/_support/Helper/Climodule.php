<?php
namespace Helper;

// here you can define custom actions
// all public methods declared in helper class will be available in $I

class Climodule extends \Codeception\Module
{
    public function setCliEnv($key, $value)
    {
        /** @var \Codeception\Module\WPCLI $cli */
        $cli = $this->getModule('WPCLI');

        $cli->_reconfigure(['env' => [$key => $value]]);
    }
}

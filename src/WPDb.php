<?php

namespace Codeception\Module;

use Codeception\Module\Db;

class WPDb extends Db
{
    protected $config = array('tablePrefix' => 'wp');

    public function _initialize()
    {
        parent::_initialize();
        $this->tablePrefix = $this->config['tablePrefix'];
    }

    public function haveOptionInDatabase($option_name, $option_value)
    {
        $tableName = $this->config['tablePrefix'] . '_options';
        $this->db->haveInDatabase($tableName, array($ption_name => $option_value));
    }

    public function haveSerializedOptionInDatabase($option_name, $option_value)
    {
        $this->haveOptionInDatabase($option_name, @serialize($option_value));
    }
}
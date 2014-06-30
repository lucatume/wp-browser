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
        $tableName = $this->getPrefixedTableNameFor('options');
        return $this->haveInDatabase($tableName, array('option_name' => $option_name, 'option_value' => $option_value, 'autoload' => 'yes'));
    }

    public function haveSerializedOptionInDatabase($option_name, $option_value)
    {
        $serializedOptionValue = @serialize($option_value);
        return $this->haveOptionInDatabase($option_name, $serializedOptionValue);
    }

    public function seeOptionInDatabase($option_name, $option_value)
    {
        $tableName = $this->getPrefixedTableNameFor('options');
        $this->seeInDatabase($tableName, array('option_name' => $option_name, 'option_value' => $option_value));
    }

    public function dontSeeOptionInDatabase($option_name, $option_value)
    {
        $tableName = $this->getPrefixedTableNameFor('options');
        $this->dontSeeInDatabase($tableName, array('option_name' => $option_name, 'option_value' => $option_value));
    }

    public function seeSerializedOptionInDatabase($option_name, $option_value)
    {
        $this->seeOptionInDatabase($option_name, @serialize($option_value));
    }

    public function dontSeeSerializedOptionInDatabase($option_name, $option_value)
    {
        $this->dontSeeOptionInDatabase($option_name, @serialize($option_value));
    }

    protected function getPrefixedTableNameFor($tableName)
    {
        $tableName = $this->config['tablePrefix'] . '_' . ltrim($tableName, '_');
        return $tableName;
    }
}
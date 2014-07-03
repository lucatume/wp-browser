<?php

namespace Codeception\Module;

use Badcow\LoremIpsum\Generator;
use tad\utils\Str;
use tad\wordpress\maker\DateMaker;
use tad\wordpress\maker\UserMaker;

class WPDb extends Db
{
    protected $requiredFields = array('url');
    protected $config = array('tablePrefix' => 'wp');
    protected $tablePrefix = 'wp';

    public function _initialize()
    {
        parent::_initialize();
        $this->tablePrefix = $this->config['tablePrefix'];
    }

    public function haveUserInDatabase($user_login, $user_id, $role = 'subscriber', Array $userData = array())
    {
        if (!is_string($user_login)) {
            throw new \BadMethodCallException('User login name must be a string', 1);
        }
        $userTableDefaults = UserMaker::generateUserDefaultsFrom($user_login, $user_id, $role);
        $userCapabilitiesDefaults = UserMaker::generateCapabilitiesDefaultsFrom($user_id, $role);
        $userLevelDefaults = UserMaker::generateUserLevelDefaultsFrom($user_id, $role);
        // merge user data with defaults
        $userTableData = array_merge($userTableDefaults, $userData);
        $userCapabilitiesData = array_merge($userCapabilitiesDefaults, $userData);
        $userLevelData = array_merge($userLevelDefaults, $userData);
        // add the data to the database
        $tableName = $this->getPrefixedTableNameFor('users');
        $this->haveInDatabase($tableName, $userTableData);
        $tableName = $this->getPrefixedTableNameFor('usermeta');
        $this->haveInDatabase($tableName, $userCapabilitiesData);
        $this->haveInDatabase($tableName, $userLevelDefaults);
    }

    public function seeUserInDatabase(Array $criteria)
    {
        $tableName = $this->getPrefixedTableNameFor('users');
        $this->seeInDatabase($tableName, $criteria);
    }

    public function havePostInDatabase($ID, Array $data = array())
    {
        $loremMaker = new Generator();
        // create a default value array
        $title = $loremMaker->getSentences(1);
        $content = $loremMaker->getParagraphs(3);
        $guid = rtrim($this->config['url'], '/') . '/?p=' . $ID;
        $defaults = array(
            'post_author' => '1',
            'post_date' => DateMaker::now(),
            'post_date_gmt' => DateMaker::gmnow(),
            'post_content' => $content,
            'post_title' => $title,
            'post_excerpt' => '',
            'post_status' => 'publish',
            'comment_status' => 'open',
            'ping_status' => 'open',
            'post_password' => '',
            'post_name' => Str::hyphen($title),
            'to_ping' => '',
            'pinged' => '',
            'post_modified' => DateMaker::now(),
            'post_modified_gmt' => DateMaker::gmnow(),
            'post_content_filtered' => '',
            'post_parent' => 0,
            'guid' => $guid,
            'menu_order' => 0,
            'post_type' => 'post'
        );
        $mergedData = array_merge($defaults, $data);
        $tableName = $this->getPrefixedTableNameFor('posts');
        $this->haveInDatabase($tableName, $mergedData);
    }

    public function seePostInDatabase(Array $criteria)
    {
        $tableName = $this->getPrefixedTableNameFor('posts');
        $this->seeInDatabase($tableName, $criteria);
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
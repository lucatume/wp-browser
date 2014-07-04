<?php

namespace Codeception\Module;

use tad\wordpress\maker\PostMaker;
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

    public function haveUserInDatabase($user_login, $user_id, $role = 'subscriber', array $userData = array())
    {
        // get the user
        list($userLevelDefaults, $userTableData, $userCapabilitiesData) = UserMaker::makeUser($user_login, $user_id, $role, $userData);
        // add the data to the database
        $tableName = $this->getPrefixedTableNameFor('users');
        $this->haveInDatabase($tableName, $userTableData);
        $tableName = $this->getPrefixedTableNameFor('usermeta');
        $this->haveInDatabase($tableName, $userCapabilitiesData);
        $this->haveInDatabase($tableName, $userLevelDefaults);
    }

    protected function getPrefixedTableNameFor($tableName)
    {
        $tableName = $this->config['tablePrefix'] . '_' . ltrim($tableName, '_');
        return $tableName;
    }

    public function seeUserInDatabase(array $criteria)
    {
        $tableName = $this->getPrefixedTableNameFor('users');
        $this->seeInDatabase($tableName, $criteria);
    }

    public function havePostInDatabase($ID, array $data = array())
    {
        $post = PostMaker::makePost($ID, $this->config['url'], $data);
        $tableName = $this->getPrefixedTableNameFor('posts');
        $this->haveInDatabase($tableName, $post);
    }

    public function havePageInDatabase($ID, array $data = array())
    {
        $post = PostMaker::makePage($ID, $this->config['url'], $data);
        $tableName = $this->getPrefixedTableNameFor('posts');
        $this->haveInDatabase($tableName, $post);
    }

    public function seePostInDatabase(array $criteria)
    {
        $tableName = $this->getPrefixedTableNameFor('posts');
        $this->seeInDatabase($tableName, $criteria);
    }

    public function seePageInDatabase(array $criteria)
    {
        $criteria = array_merge($criteria, array('post_type' => 'page'));
        $tableName = $this->getPrefixedTableNameFor('posts');
        $this->seeInDatabase($tableName, $criteria);
    }

    public function haveUserMetaInDatabase($user_id, $meta_key, $meta_value, $umeta_id = null)
    {
        if (!is_int($user_id)) {
            throw new \BadMethodCallException('User id must be an int', 1);
        }
        if (!is_string($meta_key) or !is_string($meta_value)) {
            throw new \BadMethodCallException('Meta key and value must be strings', 2);
        }
        if (!is_null($umeta_id) and !is_int($umeta_id)) {
            throw new \BadMethodCallException('User meta id must either be an int or null', 3);
        }
        $tableName = $this->getPrefixedTableNameFor('usermeta');
        $this->haveInDatabase($tableName, array(
            'umeta_id' => $umeta_id,
            'user_id' => $user_id,
            'meta_key' => $meta_key,
            'meta_value' => $meta_value
        ));
    }

    public function seeUserMetaInDatabase(array $criteria)
    {
        $tableName = $this->getPrefixedTableNameFor('usermeta');
        $this->seeInDatabase($tableName, $criteria);
    }

    public
    function haveSerializedOptionInDatabase($option_name, $option_value)
    {
        $serializedOptionValue = @serialize($option_value);
        return $this->haveOptionInDatabase($option_name, $serializedOptionValue);
    }

    public
    function haveOptionInDatabase($option_name, $option_value)
    {
        $tableName = $this->getPrefixedTableNameFor('options');
        return $this->haveInDatabase($tableName, array('option_name' => $option_name, 'option_value' => $option_value, 'autoload' => 'yes'));
    }

    public
    function seeSerializedOptionInDatabase($option_name, $option_value)
    {
        $this->seeOptionInDatabase($option_name, @serialize($option_value));
    }

    public
    function seeOptionInDatabase($option_name, $option_value)
    {
        $tableName = $this->getPrefixedTableNameFor('options');
        $this->seeInDatabase($tableName, array('option_name' => $option_name, 'option_value' => $option_value));
    }

    public
    function dontSeeSerializedOptionInDatabase($option_name, $option_value)
    {
        $this->dontSeeOptionInDatabase($option_name, @serialize($option_value));
    }

    public
    function dontSeeOptionInDatabase($option_name, $option_value)
    {
        $tableName = $this->getPrefixedTableNameFor('options');
        $this->dontSeeInDatabase($tableName, array('option_name' => $option_name, 'option_value' => $option_value));
    }

}
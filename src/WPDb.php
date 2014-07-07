<?php

namespace Codeception\Module;

use tad\utils\Str;
use tad\wordpress\maker\PostMaker;
use tad\wordpress\maker\UserMaker;

class WPDb extends Db
{
    protected $requiredFields = array('url');
    protected $config = array('tablePrefix' => 'wp', 'checkExistence' => false);
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

    public function dontSeeUserInDatabase(array $criteria)
    {
        $tableName = $this->getPrefixedTableNameFor('users');
        $this->dontSeeInDatabase($tableName, $criteria);
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

    public function dontSeePostInDatabase(array $criteria)
    {
        $tableName = $this->getPrefixedTableNameFor('posts');
        $this->dontSeeInDatabase($tableName, $criteria);
    }

    public function seePageInDatabase(array $criteria)
    {
        $criteria = array_merge($criteria, array('post_type' => 'page'));
        $tableName = $this->getPrefixedTableNameFor('posts');
        $this->seeInDatabase($tableName, $criteria);
    }

    public function dontSeePageInDatabase(array $criteria)
    {
        $criteria = array_merge($criteria, array('post_type' => 'page'));
        $tableName = $this->getPrefixedTableNameFor('posts');
        $this->dontSeeInDatabase($tableName, $criteria);
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
        $this->maybeCheckUserExistsInDatabase($user_id);
        $tableName = $this->getPrefixedTableNameFor('usermeta');
        $this->haveInDatabase($tableName, array(
            'umeta_id' => $umeta_id,
            'user_id' => $user_id,
            'meta_key' => $meta_key,
            'meta_value' => $meta_value
        ));
    }

    protected function maybeCheckUserExistsInDatabase($user_id)
    {
        if (!isset($this->config['checkExistence']) or false == $this->config['checkExistence']) {
            return;
        }
        $tableName = $this->getPrefixedTableNameFor('users');
        if (!$this->grabFromDatabase($tableName, 'ID', array('ID' => $user_id))) {
            throw new \RuntimeException("A user with an id of $user_id does not exist", 1);
        }
    }

    public function havePostWithTermInDatabase($post_id, $term_id, $term_order = 0)
    {
        if (!is_int($post_id) or !is_int($term_id) or !is_int($term_order)) {
            throw new \BadMethodCallException("Post ID, term ID and term order must be strings", 1);
        }
        $this->maybeCheckPostExistsInDatabase($post_id);
        $this->maybeCheckTermExistsInDatabase($term_id);
        // add the relationship in the database
        $tableName = $this->getPrefixedTableNameFor('term_relationships');
        $this->haveInDatabase($tableName, array('object_id' => $post_id, 'term_taxonomy_id' => $term_id, 'term_order' => $term_order));
    }

    /**
     * @param $post_id
     * @return string
     */
    protected function maybeCheckPostExistsInDatabase($post_id)
    {
        if (!isset($this->config['checkExistence']) or false == $this->config['checkExistence']) {
            return;
        }
        $tableName = $this->getPrefixedTableNameFor('posts');
        if (!$this->grabFromDatabase($tableName, 'ID', array('ID' => $post_id))) {
            throw new \RuntimeException("A post with an id of $post_id does not exist", 1);
        }
    }

    /**
     * @param $post_id
     * @param $term_id
     * @return string
     */
    protected function maybeCheckTermExistsInDatabase($post_id, $term_id)
    {
        if (!isset($this->config['checkExistence']) or false == $this->config['checkExistence']) {
            return;
        }
        $tableName = $this->getPrefixedTableNameFor('terms');
        if (!$this->grabFromDatabase($tableName, 'term_id', array('term_id' => $post_id))) {
            throw new \RuntimeException("A term with an id of $term_id does not exist", 1);
        }
    }

    public function havePostMetaInDatabase($post_id, $meta_key, $meta_value, $meta_id = null)
    {
        if (!is_int($post_id)) {
            throw new \BadMethodCallException('Post id must be an int', 1);
        }
        if (!is_null($meta_id) and !is_int($meta_key)) {
            throw new \BadMethodCallException('Meta id must be either null or an int', 2);
        }
        if (!is_string($meta_key)) {
            throw new \BadMethodCallException('Meta key must be an string', 3);
        }
        if (!is_string($meta_value)) {
            throw new \BadMethodCallException('Meta value must be an string', 4);
        }
        $this->maybeCheckPostExistsInDatabase($post_id);
        $tableName = $this->getPrefixedTableNameFor('postmeta');
        $this->haveInDatabase($tableName, array('meta_id' => $meta_id, 'post_id' => $post_id, 'meta_key' => $meta_key, 'meta_value' => $meta_value));
    }

    public function haveTermInDatabase($term, $term_id, array $args = array())
    {
        // term table entry
        $taxonomy = isset($args['taxonomy']) ? $args['taxonomy'] : 'category';
        $termsTableEntry = array(
            'term_id' => $term_id,
            'name' => $term,
            'slug' => isset($args['slug']) ? $args['slug'] : lcfirst(Str::hyphen($term)),
            'term_group' => isset($args['term_group']) ? $args['term_group'] : 0,
        );
        $tableName = $this->getPrefixedTableNameFor('terms');
        $this->haveInDatabase($tableName, $termsTableEntry);
        // term_taxonomy table entry
        $termTaxonomyTableEntry = array(
            'term_taxonomy_id' => isset($args['term_taxonomy_id']) ? $args['term_taxonomy_id'] : null,
            'term_id' => $term_id,
            'taxonomy' => $taxonomy,
            'description' => isset($args['description']) ? $args['description'] : '',
            'parent' => isset($args['parent']) ? $args['parent'] : 0,
            'count' => isset($args['count']) ? $args['count'] : 0
        );
        $tableName = $this->getPrefixedTableNameFor('term_taxonomy');
        $this->haveInDatabase($tableName, $termTaxonomyTableEntry);
    }

    public function seeTermInDatabase($criteria)
    {
        if (isset($criteria['description']) or isset($criteria['taxonomy'])) {
            // the matching will be attempted against the term_taxonomy table
            $termTaxonomyCriteria = array(
                'taxonomy' => isset($criteria['taxonomy']) ? $criteria['taxonomy'] : false,
                'description' => isset($criteria['description']) ? $criteria['description'] : false,
                'term_id' => isset($criteria['term_id']) ? $criteria['term_id'] : false
            );
            $termTaxonomyCriteria = array_filter($termTaxonomyCriteria);
            $tableName = $this->getPrefixedTableNameFor('term_taxonomy');
            $this->seeInDatabase($tableName, $termTaxonomyCriteria);
        } else {
            // the matching will be attempted against the terms table
            $tableName = $this->getPrefixedTableNameFor('terms');
            $this->seeInDatabase($tableName, $criteria);
        }
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

    public function seePostMetaInDatabase(array $criteria)
    {
        $tableName = $this->getPrefixedTableNameFor('postmeta');
        $this->seeInDatabase($tableName, $criteria);
    }

    public function dontSeePostMetaInDatabase(array $criteria)
    {
        $tableName = $this->getPrefixedTableNameFor('postmeta');
        $this->dontSeeInDatabase($tableName, $criteria);
    }
}
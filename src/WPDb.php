<?php
namespace Codeception\Module;

// Load the modified driver
include_once dirname(__FILE__) . '/ExtendedDb.php';

use Codeception\Configuration as Configuration;
use Codeception\Exception\Module as ModuleException;
use Codeception\Exception\ModuleConfig as ModuleConfigException;
use Codeception\Lib\Driver\ExtendedDb as Driver;
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
        if ($this->config['dump'] && ($this->config['cleanup'] or ($this->config['populate']))) {

            if (!file_exists(Configuration::projectDir() . $this->config['dump'])) {
                throw new ModuleConfigException(
                    __CLASS__,
                    "\nFile with dump doesn't exist.
                    Please, check path for sql file: " . $this->config['dump']
                );
            }
            $sql = file_get_contents(Configuration::projectDir() . $this->config['dump']);
            $sql = preg_replace('%/\*(?!!\d+)(?:(?!\*/).)*\*/%s', "", $sql);
            $this->sql = explode("\n", $sql);
        }

        try {
            $this->driver = Driver::create($this->config['dsn'], $this->config['user'], $this->config['password']);
        } catch (\PDOException $e) {
            throw new ModuleException(__CLASS__, $e->getMessage() . ' while creating PDO connection');
        }

        $this->dbh = $this->driver->getDbh();

        // starting with loading dump
        if ($this->config['populate']) {
            $this->cleanup();
            $this->loadDump();
            $this->populated = true;
        }
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

    public function seeSerializedOptionInDatabase($criteria)
    {
        if (isset($criteria['option_value'])) {
            $criteria['option_value'] = @serialize($criteria['option_value']);
        }
        $this->seeOptionInDatabase($criteria);
    }

    public function seeOptionInDatabase(array $criteria)
    {
        $tableName = $this->getPrefixedTableNameFor('options');
        $this->seeInDatabase($tableName, $criteria);
    }

    public function dontSeeSerializedOptionInDatabase($criteria)
    {
        if (isset($criteria['option_value'])) {
            $criteria['option_value'] = @serialize($criteria['option_value']);
        }
        $this->dontSeeOptionInDatabase($criteria);
    }

    public function dontSeeOptionInDatabase(array $criteria)
    {
        $tableName = $this->getPrefixedTableNameFor('options');
        $this->dontSeeInDatabase($tableName, $criteria);
    }

    public function seePostMetaInDatabase(array $criteria)
    {
        $tableName = $this->getPrefixedTableNameFor('postmeta');
        $this->seeInDatabase($tableName, $criteria);
    }

    public function haveLinkInDatabase($link_id, array $data = array())
    {
        if (!is_int($link_id)) {
            throw new \BadMethodCallException('Link id must be an int');
        }
        $tableName = $this->getPrefixedTableNameFor('postmeta');
        $data = array_merge($data, array('link_id' => $link_id));
        $this->haveInDatabase($tableName, $data);
    }

    public function seeLinkInDatabase(array $criteria)
    {
        $tableName = $this->getPrefixedTableNameFor('links');
        $this->seeInDatabase($tableName, $criteria);
    }

    public function dontSeeLinkInDatabase(array $criteria)
    {
        $tableName = $this->getPrefixedTableNameFor('links');
        $this->dontSeeInDatabase($tableName, $criteria);
    }

    public function dontSeePostMetaInDatabase(array $criteria)
    {
        $tableName = $this->getPrefixedTableNameFor('postmeta');
        $this->dontSeeInDatabase($tableName, $criteria);
    }

    public function seePostWithTermInDatabase($post_id, $term_id, $term_order = 0)
    {
        $tableName = $this->getPrefixedTableNameFor('term_relationships');
        $this->dontSeeInDatabase($tableName, array('object_id' => $post_id, 'term_id' => $term_id, 'term_order' => $term_order));
    }

    public
    function seeUserInDatabase(array $criteria)
    {
        $tableName = $this->getPrefixedTableNameFor('users');
        $this->seeInDatabase($tableName, $criteria);
    }

    public
    function dontSeeUserInDatabase(array $criteria)
    {
        $tableName = $this->getPrefixedTableNameFor('users');
        $this->dontSeeInDatabase($tableName, $criteria);
    }

    public
    function havePostInDatabase($ID, array $data = array())
    {
        $post = PostMaker::makePost($ID, $this->config['url'], $data);
        $tableName = $this->getPrefixedTableNameFor('posts');
        $this->haveInDatabase($tableName, $post);
    }

    public
    function havePageInDatabase($ID, array $data = array())
    {
        $post = PostMaker::makePage($ID, $this->config['url'], $data);
        $tableName = $this->getPrefixedTableNameFor('posts');
        $this->haveInDatabase($tableName, $post);
    }

    public
    function seePostInDatabase(array $criteria)
    {
        $tableName = $this->getPrefixedTableNameFor('posts');
        $this->seeInDatabase($tableName, $criteria);
    }

    public
    function dontSeePostInDatabase(array $criteria)
    {
        $tableName = $this->getPrefixedTableNameFor('posts');
        $this->dontSeeInDatabase($tableName, $criteria);
    }

    public
    function seePageInDatabase(array $criteria)
    {
        $criteria = array_merge($criteria, array('post_type' => 'page'));
        $tableName = $this->getPrefixedTableNameFor('posts');
        $this->seeInDatabase($tableName, $criteria);
    }

    public
    function dontSeePageInDatabase(array $criteria)
    {
        $criteria = array_merge($criteria, array('post_type' => 'page'));
        $tableName = $this->getPrefixedTableNameFor('posts');
        $this->dontSeeInDatabase($tableName, $criteria);
    }

    public
    function haveUserMetaInDatabase($user_id, $meta_key, $meta_value, $umeta_id = null)
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

    protected
    function maybeCheckUserExistsInDatabase($user_id)
    {
        if (!isset($this->config['checkExistence']) or false == $this->config['checkExistence']) {
            return;
        }
        $tableName = $this->getPrefixedTableNameFor('users');
        if (!$this->grabFromDatabase($tableName, 'ID', array('ID' => $user_id))) {
            throw new \RuntimeException("A user with an id of $user_id does not exist", 1);
        }
    }

    public
    function haveLinkWithTermInDatabase($link_id, $term_id, $term_order = 0)
    {
        if (!is_int($link_id) or !is_int($term_id) or !is_int($term_order)) {
            throw new \BadMethodCallException("Link ID, term ID and term order must be strings", 1);
        }
        $this->maybeCheckLinkExistsInDatabase($post_id);
        $this->maybeCheckTermExistsInDatabase($term_id);
        // add the relationship in the database
        $tableName = $this->getPrefixedTableNameFor('term_relationships');
        $this->haveInDatabase($tableName, array('object_id' => $link_id, 'term_taxonomy_id' => $term_id, 'term_order' => $term_order));
    }

    protected
    function maybeCheckLinkExistsInDatabase($link_id)
    {
        if (!isset($this->config['checkExistence']) or false == $this->config['checkExistence']) {
            return;
        }
        $tableName = $this->getPrefixedTableNameFor('links');
        if (!$this->grabFromDatabase($tableName, 'link_id', array('link_id' => $link_id))) {
            throw new \RuntimeException("A link with an id of $link_id does not exist", 1);
        }
    }

    protected
    function maybeCheckTermExistsInDatabase($term_id)
    {
        if (!isset($this->config['checkExistence']) or false == $this->config['checkExistence']) {
            return;
        }
        $tableName = $this->getPrefixedTableNameFor('terms');
        if (!$this->grabFromDatabase($tableName, 'term_id', array('term_id' => $term_id))) {
            throw new \RuntimeException("A term with an id of $term_id does not exist", 1);
        }
    }

    public
    function haveCommentInDatabase($comment_ID, $comment_post_ID, array $data = array())
    {
        if (!is_int($comment_ID) or !is_int($comment_post_ID)) {
            throw new \BadMethodCallException('Comment id and post id must be int', 1);
        }
        $comment = CommentMaker::makeComment($comment_ID, $comment_post_ID, $data);
        $tableName = $this->getPrefixedTableNameFor('comments');
        $this->haveInDatabase($tableName, $comment);
    }

    public
    function seeCommentInDatabase(array $criteria)
    {
        $tableName = $this->getPrefixedTableNameFor('comments');
        $this->seeInDatabase($tableName, $criteria);
    }

    public
    function dontSeeCommentInDatabase(array $criteria)
    {
        $tableName = $this->getPrefixedTableNameFor('comments');
        $this->dontSeeInDatabase($tableName, $criteria);
    }

    public
    function seeCommentMetaInDatabase(array $criteria)
    {
        $tableName = $this->getPrefixedTableNameFor('commentmeta');
        $this->dontSeeInDatabase($tableName, $criteria);
    }

    public
    function dontSeeCommentMetaInDatabase(array $criteria)
    {
        $tableName = $this->getPrefixedTableNameFor('commentmeta');
        $this->dontSeeInDatabase($tableName, $criteria);
    }

    public
    function havePostWithTermInDatabase($post_id, $term_id, $term_order = 0)
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

    protected
    function maybeCheckPostExistsInDatabase($post_id)
    {
        if (!isset($this->config['checkExistence']) or false == $this->config['checkExistence']) {
            return;
        }
        $tableName = $this->getPrefixedTableNameFor('posts');
        if (!$this->grabFromDatabase($tableName, 'ID', array('ID' => $post_id))) {
            throw new \RuntimeException("A post with an id of $post_id does not exist", 1);
        }
    }

    public
    function haveCommentMetaInDatabase($comment_id, $meta_key, $meta_value, $meta_id = null)
    {
        if (!is_int($comment_id)) {
            throw new \BadMethodCallException('Comment id must be an int', 1);
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
        $this->maybeCheckCommentExistsInDatabase($comment_id);
        $tableName = $this->getPrefixedTableNameFor('commmentmeta');
        $this->haveInDatabase($tableName, array('meta_id' => $meta_id, 'comment_id' => $comment_id, 'meta_key' => $meta_key, 'meta_value' => $meta_value));
    }

    protected
    function maybeCheckCommentExistsInDatabase($comment_id)
    {
        if (!isset($this->config['checkExistence']) or false == $this->config['checkExistence']) {
            return;
        }
        $tableName = $this->getPrefixedTableNameFor('comments');
        if (!$this->grabFromDatabase($tableName, 'comment_ID', array('commment_ID' => $comment_id))) {
            throw new \RuntimeException("A comment with an id of $comment_id does not exist", 1);
        }
    }

    public
    function havePostMetaInDatabase($post_id, $meta_key, $meta_value, $meta_id = null)
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

    public
    function haveTermInDatabase($term, $term_id, array $args = array())
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

    public function dontSeeTermInDatabase($criteria)
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
            $this->dontSeeInDatabase($tableName, $termTaxonomyCriteria);
        }
        // the matching will be attempted against the terms table
        $tableName = $this->getPrefixedTableNameFor('terms');
        $this->dontSeeInDatabase($tableName, $criteria);
    }

    public
    function seeUserMetaInDatabase(array $criteria)
    {
        $tableName = $this->getPrefixedTableNameFor('usermeta');
        $this->seeInDatabase($tableName, $criteria);
    }

    public function dontSeeUserMetaInDatabase(array $criteria)
    {
        $tableName = $this->getPrefixedTableNameFor('usermeta');
        $this->dontSeeInDatabase($tableName, $criteria);
    }

    public function haveSerializedOptionInDatabase($option_name, $option_value)
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

//    public function haveOrUpdateInDatabase($table, array $data)
//    {
//        // generate the update query
//        $columns = array_map(
//            array($this, 'getQuotedName'),
//            array_keys($data)
//        );
//        $updateAssignments = array();
//        foreach($data as $key => $value) {
//           $updateAssignments[] = sprintf('%s=%s', $key, $value);
//        }
//        $updateAssignments = implode(', ', $updateAssignments);
//        $query = sprintf(
//            "INSERT INTO %s (%s) VALUES (%s) ON DUPLICATE KEY UPDATE %s",
//            $this->getQuotedName($tableName),
//            implode(', ', $columns),
//            implode(', ', array_fill(0, count($data), '?')),
//            $updateAssignments
//        );
//        //  $query = $this->driver->insert($table, $data);
//        $this->debugSection('Query', $query);
//
//        $sth = $this->driver->getDbh()->prepare($query);
//        if (!$sth) {
//            $this->fail("Query '$query' can't be executed.");
//        }
//        $i = 1;
//        foreach ($data as $val) {
//            $sth->bindValue($i, $val);
//            $i++;
//        }
//        $res = $sth->execute();
//        if (!$res) {
//            $this->fail(sprintf("Record with %s couldn't be inserted into %s", json_encode($data), $table));
//        }
//    }
}
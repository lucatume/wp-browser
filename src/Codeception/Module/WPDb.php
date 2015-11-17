<?php
    namespace Codeception\Module;

    use BaconStringUtils\Slugifier;
    use Codeception\Configuration as Configuration;
    use Codeception\Exception\ModuleConfigException;
    use Codeception\Lib\Driver\ExtendedDbDriver as Driver;
    use PDO;
    use tad\WPBrowser\Generators\Comment;
    use tad\WPBrowser\Generators\Post;
    use tad\WPBrowser\Generators\User;

    /**
     * An extension of Codeception Db class to add WordPress database specific
     * methods.
     */
    class WPDb extends ExtendedDb
    {
        protected $uniqueTables = [
            'blogs',
            'blog_versions',
            'registration_log',
            'signups',
            'site',
            'sitemeta',
            'users',
            'usermeta',
        ];

        /**
         * The module required configuration parameters.
         *
         * url - the site url
         *
         * @var array
         */
        protected $requiredFields = array('url');

        /**
         * The module optional configuration parameters.
         *
         * tablePrefix - the WordPress installation table prefix, defaults to "wp".
         * checkExistence - if true will attempt to insert coherent data in the database; e.g. an post with term insertion
         * will trigger post and term insertions before the relation between them is inserted; defaults to false. update -
         * if true have... methods will attempt an update on duplicate keys; defaults to true.
         *
         * @var array
         */
        protected $config = array('tablePrefix'    => 'wp_',
                                  'checkExistence' => false,
                                  'update'         => true,
                                  'reconnect'      => false);
        /**
         * The table prefix to use.
         *
         * @var string
         */
        protected $tablePrefix = 'wp_';

        /**
         * @var int The id of the blog currently used.
         */
        protected $blogId = 0;

        /**
         * Initializes the module.
         *
         * @return void
         */
        public function _initialize()
        {
            if ($this->config['dump'] && ($this->config['cleanup'] or ($this->config['populate']))) {

                if (!file_exists(Configuration::projectDir() . $this->config['dump'])) {
                    throw new ModuleConfigException(__CLASS__, "\nFile with dump doesn't exist.
                    Please, check path for sql file: " . $this->config['dump']);
                }
                $sql = file_get_contents(Configuration::projectDir() . $this->config['dump']);
                $sql = preg_replace('%/\*(?!!\d+)(?:(?!\*/).)*\*/%s', "", $sql);
                $this->sql = explode("\n", $sql);
            }

            try {
                $this->driver = Driver::create($this->config['dsn'], $this->config['user'], $this->config['password']);
            } catch (\PDOException $e) {
                throw new ModuleConfigException(__CLASS__, $e->getMessage() . ' while creating PDO connection');
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

        /**
         * Inserts a user and appropriate meta in the database.
         *
         * @param  string $user_login The user login slug
         * @param  string $role The user role slug, e.g. "administrator"; defaults to "subscriber".
         * @param  array $userData An associative array of column names and values overridind defaults in the "users"
         *                            and "usermeta" table.
         *
         * @return void
         */
        public function haveUserInDatabase($user_login,
                                           $role = 'subscriber',
                                           array $userData = array())
        {
            // get the user
            $userTableData = User::generateUserTableDataFrom($user_login, $userData);
            $this->debugSection('Generated users table data', json_encode($userTableData));
            $this->haveInDatabase($this->getUsersTableName(), $userTableData);

            $userId = $this->grabUserIdFromDatabase($user_login);

            $this->haveUserCapabilitiesInDatabase($userId, $role);
            $this->haveUserLevelsInDatabase($userId, $role);
//            list($userLevelDefaults, $userTableData, $userCapabilitiesData) = User::makeUser($user_login, $role, $userData);
//            $this->debugSection('Makers output', print_r($userTableData));
//            // add the data to the database
//            $tableName = $this->grabPrefixedTableNameFor('usermeta');
//            $this->haveInDatabase($tableName, $userCapabilitiesData);
//            $this->haveInDatabase($tableName, $userLevelDefaults);
        }

        /**
         * @return string
         */
        protected function getUsersTableName()
        {
            $usersTableName = $this->grabPrefixedTableNameFor('users');
            return $usersTableName;
        }

        /**
         * Returns a prefixed table name.
         *
         * @param  string $tableName The table name, e.g. "users".
         *
         * @return string            The prefixed table name, e.g. "wp_users".
         */
        public function grabPrefixedTableNameFor($tableName = '')
        {
            $idFrag = '';
            if (!in_array($tableName, $this->uniqueTables)) {
                $idFrag = empty($this->blogId) ? '' : "{$this->blogId}_";
            }

            $tableName = $this->config['tablePrefix'] . $idFrag . $tableName;

            return $tableName;
        }

        public function grabUserIdFromDatabase($userLogin)
        {
            return $this->grabFromDatabase('wp_users', 'ID', ['user_login' => $userLogin]);
        }

        /**
         * @param $userId
         * @param $role
         * @return array
         */
        public function haveUserCapabilitiesInDatabase($userId, $role)
        {
            if (!is_array($role)) {
                $meta_key = $this->grabPrefixedTableNameFor() . 'capabilities';
                $meta_value = serialize([$role => 1]);
                $this->haveUserMetaInDatabase($userId, $meta_key, $meta_value);
                return;
            }
            foreach ($role as $blogId => $_role) {
                $blogIdAndPrefix = $blogId == 0 ? '' : $blogId . '_';
                $meta_key = $this->grabPrefixedTableNameFor() . $blogIdAndPrefix . 'capabilities';
                $meta_value = serialize([$_role => 1]);
                $this->haveUserMetaInDatabase($userId, $meta_key, $meta_value);
            }
        }

        /**
         * @param $userId
         * @param $meta_key
         * @param $meta_value
         */
        public function haveUserMetaInDatabase($userId, $meta_key, $meta_value)
        {
            $data = ['user_id'    => $userId,
                     'meta_key'   => $meta_key,
                     'meta_value' => $this->maybeSerialize($meta_value)];
            $this->haveInDatabase($this->getUsermetaTableName(), $data);
        }

        /**
         * @param $value
         * @return string
         */
        protected function maybeSerialize($value)
        {
            $metaValue = (is_array($value) || is_object($value)) ? serialize($value) : $value;
            return $metaValue;
        }

        /**
         * @return string
         */
        protected function getUsermetaTableName()
        {
            $usermetaTable = $this->grabPrefixedTableNameFor('usermeta');
            return $usermetaTable;
        }

        /**
         * @param $userId
         * @param $role
         */
        public function haveUserLevelsInDatabase($userId, $role)
        {
            if (!is_array($role)) {
                $meta_key = $this->grabPrefixedTableNameFor() . 'user_level';
                $meta_value = User\Roles::getLevelForRole($role);
                $this->haveUserMetaInDatabase($userId, $meta_key, $meta_value);
                return;
            }
            foreach ($role as $blogId => $_role) {
                $blogIdAndPrefix = $blogId == 0 ? '' : $blogId . '_';
                $meta_key = $this->grabPrefixedTableNameFor() . $blogIdAndPrefix . 'user_level';
                $meta_value = User\Roles::getLevelForRole($_role);
                $this->haveUserMetaInDatabase($userId, $meta_key, $meta_value);
            }
        }

        /**
         * Checks if an option is in the database and is serialized.
         *
         * Will look in the "options" table.
         *
         * @param  array $criteria
         *
         * @return void
         */
        public function seeSerializedOptionInDatabase($key, $value = null)
        {
            $criteria['option_name'] = $key;
            if (!empty($value)) {
                $criteria['option_value'] = @serialize($value);
            }
            $this->seeOptionInDatabase($criteria);
        }

        /**
         * Checks if an option is in the database.
         *
         * Will look in the "options" table.
         *
         * @param $key
         * @param $value
         */
        public function seeOptionInDatabase($key, $value = null)
        {
            $tableName = $this->grabPrefixedTableNameFor('options');
            $criteria['option_name'] = $key;
            if (!empty($value)) {
                $criteria['option_value'] = $value;
            }
            $this->seeInDatabase($tableName, $criteria);
        }

        /**
         * Checks that a serialized option is not in the database.
         *
         * Will look in the "options" table.
         *
         * @param $key
         * @param null $value
         *
         */
        public function dontSeeSerializedOptionInDatabase($key, $value = null)
        {
            $criteria['option_name'] = $key;
            if (!empty($value)) {
                $criteria['option_value'] = @serialize($criteria['option_value']);
            }
            $this->dontSeeOptionInDatabase($criteria);
        }

        /**
         * Checks that an option is not in the database.
         *
         * Will look in the "options" table.
         *
         * @param $key
         * @param null $value
         */
        public function dontSeeOptionInDatabase($key, $value = null)
        {
            $tableName = $this->grabPrefixedTableNameFor('options');
            $criteria['option_name'] = $key;
            if (!empty($value)) {
                $criteria['option_value'] = $value;
            }
            $this->dontSeeInDatabase($tableName, $criteria);
        }

        /**
         * Checks for a post meta value in the database.
         *
         * Will look up the "postmeta"  table.
         *
         * @param  array $criteria
         *
         * @return void
         */
        public function seePostMetaInDatabase(array $criteria)
        {
            $tableName = $this->grabPrefixedTableNameFor('postmeta');
            $this->seeInDatabase($tableName, $criteria);
        }

        /**
         * Inserts a link in the database.
         *
         * Will insert in the "links" table.
         *
         * @param  int $link_id The link id to insert.
         * @param  array $data The data to insert.
         *
         * @return void
         */
        public function haveLinkInDatabase($link_id,
                                           array $data = array())
        {
            if (!is_int($link_id)) {
                throw new \BadMethodCallException('Link id must be an int');
            }
            $tableName = $this->grabPrefixedTableNameFor('links');
            $data = array_merge($data, array('link_id' => $link_id));
            $this->haveInDatabase($tableName, $data);
        }

        /**
         * Checks for a link in the database.
         *
         * Will look up the "links" table.
         *
         * @param  array $criteria
         *
         * @return void
         */
        public function seeLinkInDatabase(array $criteria)
        {
            $tableName = $this->grabPrefixedTableNameFor('links');
            $this->seeInDatabase($tableName, $criteria);
        }

        /**
         * Checks for a link is not in the database.
         *
         * Will look up the "links" table.
         *
         * @param  array $criteria
         *
         * @return void
         */
        public function dontSeeLinkInDatabase(array $criteria)
        {
            $tableName = $this->grabPrefixedTableNameFor('links');
            $this->dontSeeInDatabase($tableName, $criteria);
        }

        /**
         * Checks that a post meta value is not there.
         *
         * Will look up the "postmeta" table.
         *
         * @param  array $criteria
         *
         * @return void
         */
        public function dontSeePostMetaInDatabase(array $criteria)
        {
            $tableName = $this->grabPrefixedTableNameFor('postmeta');
            $this->dontSeeInDatabase($tableName, $criteria);
        }

        /**
         * Checks that a post to term relation exists in the database.
         *
         * Will look up the "term_relationships" table.
         *
         * @param  int $post_id The post ID.
         * @param  int $term_id The term ID.
         * @param  integer $term_order The order the term applies to the post, defaults to 0.
         *
         * @return void
         */
        public function seePostWithTermInDatabase($post_id,
                                                  $term_id,
                                                  $term_order = 0)
        {
            $tableName = $this->grabPrefixedTableNameFor('term_relationships');
            $this->dontSeeInDatabase($tableName, array(
                'object_id'  => $post_id,
                'term_id'    => $term_id,
                'term_order' => $term_order
            ));
        }

        /**
         * Checks that a user is in the database.
         *
         * Will look up the "users" table.
         *
         * @param  array $criteria
         *
         * @return void
         */
        public function seeUserInDatabase(array $criteria)
        {
            $tableName = $this->grabPrefixedTableNameFor('users');
            $this->seeInDatabase($tableName, $criteria);
        }

        /**
         * Checks that some user meta value is in the database.
         *
         * Will look up the "usermeta" table.
         *
         * @param  int $user_id The user ID.
         * @param  string $meta_key
         * @param         string /int $meta_value
         * @param  int $umeta_id The optional user meta id.
         *
         * @return void
         */

        /**
         * Checks that a user is not in the database.
         *
         * @param  array $criteria
         *
         * @return void
         */
        public function dontSeeUserInDatabase(array $criteria)
        {
            $tableName = $this->grabPrefixedTableNameFor('users');
            $this->dontSeeInDatabase($tableName, $criteria);
        }

        /**
         * Inserts a post in the database.
         *
         * @param  int $ID The post ID.
         * @param  array $data An associative array of post data to override default and random generated values.
         *
         * @return void
         */
        public function havePostInDatabase($ID,
                                           array $data = array())
        {
            $post = Post::makePost($ID, $this->config['url'], $data);
            $tableName = $this->grabPrefixedTableNameFor('posts');
            $this->haveInDatabase($tableName, $post);
        }

        /**
         * Inserts a post in the database.
         *
         * @param  int $ID The post ID.
         * @param  array $data An associative array of post data to override default and random generated values.
         *
         * @return void
         */
        public function havePageInDatabase($ID,
                                           array $data = array())
        {
            $post = Post::makePage($ID, $this->config['url'], $data);
            $tableName = $this->grabPrefixedTableNameFor('posts');
            $this->haveInDatabase($tableName, $post);
        }

        /**
         * Checks for a post in the database.
         *
         * Will look up the "posts" table.
         *
         * @param  array $criteria
         *
         * @return void
         */
        public function seePostInDatabase(array $criteria)
        {
            $tableName = $this->grabPrefixedTableNameFor('posts');
            $this->seeInDatabase($tableName, $criteria);
        }

        /**
         * Checks that a post is not in the database.
         *
         * Will look up the "posts" table.
         *
         * @param  array $criteria
         *
         * @return void
         */
        public function dontSeePostInDatabase(array $criteria)
        {
            $tableName = $this->grabPrefixedTableNameFor('posts');
            $this->dontSeeInDatabase($tableName, $criteria);
        }

        /**
         * Checks for a page in the database.
         *
         * Will look up the "posts" table.
         *
         * @param  array $criteria
         *
         * @return void
         */
        public function seePageInDatabase(array $criteria)
        {
            $criteria = array_merge($criteria, array('post_type' => 'page'));
            $tableName = $this->grabPrefixedTableNameFor('posts');
            $this->seeInDatabase($tableName, $criteria);
        }

        /**
         * Checks that a page is not in the database.
         *
         * Will look up the "posts" table.
         *
         * @param  array $criteria
         *
         * @return void
         */
        public function dontSeePageInDatabase(array $criteria)
        {
            $criteria = array_merge($criteria, array('post_type' => 'page'));
            $tableName = $this->grabPrefixedTableNameFor('posts');
            $this->dontSeeInDatabase($tableName, $criteria);
        }

        /**
         * Inserts a link to term relationship in the database.
         *
         * If "checkExistence" then will make some checks for missing term and/or link.
         *
         * @param  int $link_id The link ID.
         * @param  int $term_id The term ID.
         * @param  integer $term_order An optional term order value, will default to 0.
         *
         * @return void
         */
        public function haveLinkWithTermInDatabase($link_id,
                                                   $term_id,
                                                   $term_order = 0)
        {
            if (!is_int($link_id) or !is_int($term_id) or !is_int($term_order)) {
                throw new \BadMethodCallException("Link ID, term ID and term order must be strings", 1);
            }
            $this->maybeCheckTermExistsInDatabase($term_id);
            // add the relationship in the database
            $tableName = $this->grabPrefixedTableNameFor('term_relationships');
            $this->haveInDatabase($tableName, array(
                'object_id'        => $link_id,
                'term_taxonomy_id' => $term_id,
                'term_order'       => $term_order
            ));
        }

        /**
         * Conditionally checks that a term exists in the database.
         *
         * Will look up the "terms" table, will throw if not found.
         *
         * @param  int $term_id The term ID.
         *
         * @return void
         */
        protected function maybeCheckTermExistsInDatabase($term_id)
        {
            if (!isset($this->config['checkExistence']) or false == $this->config['checkExistence']) {
                return;
            }
            $tableName = $this->grabPrefixedTableNameFor('terms');
            if (!$this->grabFromDatabase($tableName, 'term_id', array('term_id' => $term_id))) {
                throw new \RuntimeException("A term with an id of $term_id does not exist", 1);
            }
        }

        /**
         * Inserts a comment in the database.
         *
         * @param  int $comment_post_ID The id of the post the comment refers to.
         * @param  array $data The comment data overriding default and random generated values.
         *
         * @return void
         */
        public function haveCommentInDatabase(
            $comment_post_ID,
            array $data = array())
        {
            if (!is_int($comment_post_ID)) {
                throw new \BadMethodCallException('Comment id and post id must be int', 1);
            }
            $comment = Comment::makeComment($comment_post_ID, $data);
            $tableName = $this->grabPrefixedTableNameFor('comments');
            $this->haveInDatabase($tableName, $comment);
        }

        /**
         * Checks for a comment in the database.
         *
         * Will look up the "comments" table.
         *
         * @param  array $criteria
         *
         * @return void
         */
        public function seeCommentInDatabase(array $criteria)
        {
            $tableName = $this->grabPrefixedTableNameFor('comments');
            $this->seeInDatabase($tableName, $criteria);
        }

        /**
         * Checks that a comment is not in the database.
         *
         * Will look up the "comments" table.
         *
         * @param  array $criteria
         *
         * @return void
         */
        public function dontSeeCommentInDatabase(array $criteria)
        {
            $tableName = $this->grabPrefixedTableNameFor('comments');
            $this->dontSeeInDatabase($tableName, $criteria);
        }

        /**
         * Checks that a comment meta value is in the database.
         *
         * Will look up the "commentmeta" table.
         *
         * @param  array $criteria
         *
         * @return void
         */
        public function seeCommentMetaInDatabase(array $criteria)
        {
            $tableName = $this->grabPrefixedTableNameFor('commentmeta');
            $this->dontSeeInDatabase($tableName, $criteria);
        }

        /**
         * Checks that a comment meta value is not in the database.
         *
         * Will look up the "commentmeta" table.
         *
         * @param  array $criteria
         *
         * @return void
         */
        public function dontSeeCommentMetaInDatabase(array $criteria)
        {
            $tableName = $this->grabPrefixedTableNameFor('commentmeta');
            $this->dontSeeInDatabase($tableName, $criteria);
        }

        /**
         * Inserts a post to term relationship in the database.
         *
         * Will conditionally check for post and term existence if "checkExistence" is set to true.
         *
         * @param  int $post_id The post ID.
         * @param  int $term_id The term ID.
         * @param  integer $term_order The optional term order.
         *
         * @return void
         */
        public function havePostWithTermInDatabase($post_id,
                                                   $term_id,
                                                   $term_order = 0)
        {
            if (!is_int($post_id) or !is_int($term_id) or !is_int($term_order)) {
                throw new \BadMethodCallException("Post ID, term ID and term order must be strings", 1);
            }
            $this->maybeCheckPostExistsInDatabase($post_id);
            $this->maybeCheckTermExistsInDatabase($term_id);
            // add the relationship in the database
            $tableName = $this->grabPrefixedTableNameFor('term_relationships');
            $this->haveInDatabase($tableName, array(
                'object_id'        => $post_id,
                'term_taxonomy_id' => $term_id,
                'term_order'       => $term_order
            ));
        }

        /**
         * Conditionally checks that a post exists in database, will throw if not existent.
         *
         * @param  int $post_id The post ID.
         *
         * @return void
         */
        protected function maybeCheckPostExistsInDatabase($post_id)
        {
            if (!isset($this->config['checkExistence']) or false == $this->config['checkExistence']) {
                return;
            }
            $tableName = $this->grabPrefixedTableNameFor('posts');
            if (!$this->grabFromDatabase($tableName, 'ID', array('ID' => $post_id))) {
                throw new \RuntimeException("A post with an id of $post_id does not exist", 1);
            }
        }

        /**
         * Inserts a commment meta value in the database.
         *
         * @param  int $comment_id The comment ID.
         * @param  string $meta_key
         * @param         string /int $meta_value
         * @param  int $meta_id The optinal meta ID.
         *
         * @return void
         */
        public function haveCommentMetaInDatabase($comment_id,
                                                  $meta_key,
                                                  $meta_value,
                                                  $meta_id = null)
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
            $tableName = $this->grabPrefixedTableNameFor('commmentmeta');
            $this->haveInDatabase($tableName, array(
                'meta_id'    => $meta_id,
                'comment_id' => $comment_id,
                'meta_key'   => $meta_key,
                'meta_value' => $meta_value
            ));
        }

        /**
         * Conditionally checks that a comment exists in database, will throw if not existent.
         *
         * @param $comment_id
         *
         */
        protected function maybeCheckCommentExistsInDatabase($comment_id)
        {
            if (!isset($this->config['checkExistence']) or false == $this->config['checkExistence']) {
                return;
            }
            $tableName = $this->grabPrefixedTableNameFor('comments');
            if (!$this->grabFromDatabase($tableName, 'comment_ID', array('commment_ID' => $comment_id))) {
                throw new \RuntimeException("A comment with an id of $comment_id does not exist", 1);
            }
        }

        /**
         * Inserts a post meta value in the database.
         *
         * Will check for post existence if "checkExistence" set to true.
         *
         * @param  int $post_id
         * @param  string $meta_key
         * @param         string /int $meta_value
         * @param  int $meta_id The optional meta ID.
         *
         * @return void
         */
        public function havePostMetaInDatabase($post_id,
                                               $meta_key,
                                               $meta_value,
                                               $meta_id = null)
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
            $tableName = $this->grabPrefixedTableNameFor('postmeta');
            $this->haveInDatabase($tableName, array(
                'meta_id'    => $meta_id,
                'post_id'    => $post_id,
                'meta_key'   => $meta_key,
                'meta_value' => $meta_value
            ));
        }

        /**
         * Inserts a term in the database.
         *
         * @param  string $term The term scree name, e.g. "Fuzzy".
         * @param  int $term_id
         * @param  array $args Term arguments overriding default and generated ones.
         *
         * @return void
         */
        public function haveTermInDatabase($term,
                                           $term_id,
                                           array $args = array())
        {
            // term table entry
            $taxonomy = isset($args['taxonomy']) ? $args['taxonomy'] : 'category';
            $termsTableEntry = array(
                'term_id'    => $term_id,
                'name'       => $term,
                'slug'       => isset($args['slug']) ? $args['slug'] : (new Slugifier())->slugify($term),
                'term_group' => isset($args['term_group']) ? $args['term_group'] : 0,
            );
            $tableName = $this->grabPrefixedTableNameFor('terms');
            $this->haveInDatabase($tableName, $termsTableEntry);
            // term_taxonomy table entry
            $termTaxonomyTableEntry = array(
                'term_taxonomy_id' => isset($args['term_taxonomy_id']) ? $args['term_taxonomy_id'] : null,
                'term_id'          => $term_id,
                'taxonomy'         => $taxonomy,
                'description'      => isset($args['description']) ? $args['description'] : '',
                'parent'           => isset($args['parent']) ? $args['parent'] : 0,
                'count'            => isset($args['count']) ? $args['count'] : 0
            );
            $tableName = $this->grabPrefixedTableNameFor('term_taxonomy');
            $this->haveInDatabase($tableName, $termTaxonomyTableEntry);
        }

        /**
         * Checks for a term in the database.
         *
         * Will look up the "terms" table.
         *
         * @param  array $criteria
         *
         * @return void
         */
        public function seeTermInDatabase($criteria)
        {
            if (isset($criteria['description']) or isset($criteria['taxonomy'])) {
                // the matching will be attempted against the term_taxonomy table
                $termTaxonomyCriteria = array(
                    'taxonomy'    => isset($criteria['taxonomy']) ? $criteria['taxonomy'] : false,
                    'description' => isset($criteria['description']) ? $criteria['description'] : false,
                    'term_id'     => isset($criteria['term_id']) ? $criteria['term_id'] : false
                );
                $termTaxonomyCriteria = array_filter($termTaxonomyCriteria);
                $tableName = $this->grabPrefixedTableNameFor('term_taxonomy');
                $this->seeInDatabase($tableName, $termTaxonomyCriteria);
            } else {
                // the matching will be attempted against the terms table
                $tableName = $this->grabPrefixedTableNameFor('terms');
                $this->seeInDatabase($tableName, $criteria);
            }
        }

        /**
         * Checks that a term is not in the database.
         *
         *  Will look up the "terms" table.
         *
         * @param  array $criteria
         *
         * @return void
         */
        public function dontSeeTermInDatabase($criteria)
        {
            if (isset($criteria['description']) or isset($criteria['taxonomy'])) {
                // the matching will be attempted against the term_taxonomy table
                $termTaxonomyCriteria = array(
                    'taxonomy'    => isset($criteria['taxonomy']) ? $criteria['taxonomy'] : false,
                    'description' => isset($criteria['description']) ? $criteria['description'] : false,
                    'term_id'     => isset($criteria['term_id']) ? $criteria['term_id'] : false
                );
                $termTaxonomyCriteria = array_filter($termTaxonomyCriteria);
                $tableName = $this->grabPrefixedTableNameFor('term_taxonomy');
                $this->dontSeeInDatabase($tableName, $termTaxonomyCriteria);
            }
            // the matching will be attempted against the terms table
            $tableName = $this->grabPrefixedTableNameFor('terms');
            $this->dontSeeInDatabase($tableName, $criteria);
        }

        /**
         * Checks for a user meta value in the database.
         *
         * Will look up the "usermeta" table.
         *
         * @param  array $criteria
         *
         * @return void
         */
        public function seeUserMetaInDatabase(array $criteria)
        {
            $tableName = $this->grabPrefixedTableNameFor('usermeta');
            $this->seeInDatabase($tableName, $criteria);
        }

        /**
         * Check that a user meta value is not in the database.
         *
         * Will look up the "usermeta" table.
         *
         * @param  array $criteria
         *
         * @return void
         */
        public function dontSeeUserMetaInDatabase(array $criteria)
        {
            $tableName = $this->grabPrefixedTableNameFor('usermeta');
            $this->dontSeeInDatabase($tableName, $criteria);
        }

        /**
         * Removes an entry from the commentmeta table.
         *
         * @param  array $criteria
         *
         * @return void
         */
        public function dontHaveCommentMetaInDatabase(array $criteria)
        {
            $tableName = $this->grabPrefixedTableNameFor('commentmeta');
            $this->dontHaveInDatabase($tableName, $criteria);
        }

        /**
         * Removes an entry from the comment table.
         *
         * @param  array $criteria
         *
         * @return void
         */
        public function dontHaveCommentInDatabase(array $criteria)
        {
            $tableName = $this->grabPrefixedTableNameFor('comments');
            $this->dontHaveInDatabase($tableName, $criteria);
        }

        /**
         * Removes an entry from the links table.
         *
         * @param  array $criteria
         *
         * @return void
         */
        public function dontHaveLinkInDatabase(array $criteria)
        {
            $tableName = $this->grabPrefixedTableNameFor('links');
            $this->dontHaveInDatabase($tableName, $criteria);
        }

        /**
         * Removes an entry from the postmeta table.
         *
         * @param  array $criteria
         *
         * @return void
         */
        public function dontHavePostMetaInDatabase(array $criteria)
        {
            $tableName = $this->grabPrefixedTableNameFor('postmeta');
            $this->dontHaveInDatabase($tableName, $criteria);
        }

        /**
         * Removes an entry from the posts table.
         *
         * @param  array $criteria
         *
         * @return void
         */
        public function dontHavePostInDatabase(array $criteria)
        {
            $tableName = $this->grabPrefixedTableNameFor('posts');
            $this->dontHaveInDatabase($tableName, $criteria);
        }

        /**
         * Removes an entry from the term_relationships table.
         *
         * @param  array $criteria
         *
         * @return void
         */
        public function dontHaveTermRelationshipInDatabase(array $criteria)
        {
            $tableName = $this->grabPrefixedTableNameFor('term_relationships');
            $this->dontHaveInDatabase($tableName, $criteria);
        }

        /**
         * Removes an entry from the term_taxonomy table.
         *
         * @param  array $criteria
         *
         * @return void
         */
        public function dontHaveTermTaxonomyInDatabase(array $criteria)
        {
            $tableName = $this->grabPrefixedTableNameFor('term_taxonomy');
            $this->dontHaveInDatabase($tableName, $criteria);
        }

        /**
         * Removes an entry from the terms table.
         *
         * @param  array $criteria
         *
         * @return void
         */
        public function dontHaveTermInDatabase(array $criteria)
        {
            $tableName = $this->grabPrefixedTableNameFor('terms');
            $this->dontHaveInDatabase($tableName, $criteria);
        }

        /**
         * Removes an entry from the usermeta table.
         *
         * @param  array $criteria
         *
         * @return void
         */
        public function dontHaveUserMetaInDatabase(array $criteria)
        {
            $tableName = $this->grabPrefixedTableNameFor('usermeta');
            $this->dontHaveInDatabase($tableName, $criteria);
        }

        /**
         * @param int $userIdOrLogin
         */
        public function dontHaveUserInDatabase($userIdOrLogin)
        {
            $userId = is_numeric($userIdOrLogin) ? intval($userIdOrLogin) : $this->grabUserIdFromDatabase($userIdOrLogin);
            $this->dontHaveInDatabase($this->grabPrefixedTableNameFor('users'), ['ID' => $userId]);
            $this->dontHaveInDatabase($this->grabPrefixedTableNameFor('usermeta'), ['user_id' => $userId]);
        }

        public function grabUserMetaFromDatabase($userId, $meta_key)
        {
            $table = $this->grabPrefixedTableNameFor('usermeta');
            $meta = $this->grabAllFromDatabase($table, 'meta_value', ['user_id' => $userId, 'meta_key' => $meta_key]);
            if (empty($meta)) {
                return [];
            }

            return array_map(function ($val) {
                return $val['meta_value'];
            }, $meta);
        }

        public function grabAllFromDatabase($table, $column, $criteria)
        {
            $query = $this->driver->select($column, $table, $criteria);

            $sth = $this->driver->executeQuery($query, array_values($criteria));

            return $sth->fetchAll(PDO::FETCH_ASSOC);
        }

        public function haveTransientInDatabase($transient, $value)
        {
            $this->haveOptionInDatabase('_transient_' . $transient, $value);
        }

        /**
         * Inserts an option in the database.
         *
         * @param  string $option_name
         * @param         string /int $option_value
         *
         * @return void
         */
        public function haveOptionInDatabase($option_name,
                                             $option_value)
        {
            $table = $this->grabPrefixedTableNameFor('options');

            $this->dontHaveInDatabase($table, ['option_name' => $option_name]);

            $option_value = $this->maybeSerialize($option_value);
            $this->haveInDatabase($table, array(
                'option_name'  => $option_name,
                'option_value' => $option_value,
                'autoload'     => 'yes'
            ));
        }

        public function dontHaveTransientInDatabase($transient)
        {
            $this->dontHaveOptionInDatabase('_transient_' . $transient);
        }

        /**
         * Removes an entry from the options table.
         *
         * @param $key
         * @param null $value
         */
        public function dontHaveOptionInDatabase($key, $value = null)
        {
            $tableName = $this->grabPrefixedTableNameFor('options');
            $criteria['option_name'] = $key;
            if (!empty($value)) {
                $criteria['option_value'] = $value;
            }
            $this->dontHaveInDatabase($tableName, $criteria);
        }

        public function haveSiteOptionInDatabase($key, $value)
        {
            $currentBlogId = $this->blogId;
            $this->useMainBlog();
            $this->haveOptionInDatabase('_site_option_' . $key, $value);
            $this->useBlog($currentBlogId);
        }

        public function useMainBlog()
        {
            $this->useBlog(0);
        }

        public function useBlog($id = 0)
        {
            if (!(is_numeric($id) && intval($id) === $id && intval($id) >= 0)) {
                throw new \InvalidArgumentException('Id must be an integer greater than or equal to 0');
            }
            $this->blogId = intval($id);
        }

        public function dontHaveSiteOptionInDatabase($key, $value = null)
        {
            $currentBlogId = $this->blogId;
            $this->useMainBlog();
            $this->dontHaveOptionInDatabase('_site_option_' . $key, $value);
            $this->useBlog($currentBlogId);
        }

        public function haveSiteTransientInDatabase($key, $value)
        {
            $currentBlogId = $this->blogId;
            $this->useMainBlog();
            $this->haveOptionInDatabase('_site_transient_' . $key, $value);
            $this->useBlog($currentBlogId);
        }

        public function dontHaveSiteTransientInDatabase($key)
        {
            $currentBlogId = $this->blogId;
            $this->useMainBlog();
            $this->dontHaveOptionInDatabase('_site_transient_' . $key);
            $this->useBlog($currentBlogId);
        }

        public function grabSiteOptionFromDatabase($key)
        {
            $currentBlogId = $this->blogId;
            $this->useMainBlog();
            $value = $this->grabOptionFromDatabase('_site_option_' . $key);
            $this->useBlog($currentBlogId);
            return $value;
        }

        public function grabOptionFromDatabase($option_name)
        {
            $table = $this->grabPrefixedTableNameFor('options');
            $option_value = $this->grabFromDatabase($table, 'option_value', ['option_name' => $option_name]);
            return empty($option_value) ? '' : $this->maybeUnserialize($option_value);
        }

        private function maybeUnserialize($value)
        {
            $unserialized = @unserialize($value);
            return false === $unserialized ? $value : $unserialized;
        }

        public function grabSiteTransientFromDatabase($key)
        {
            $currentBlogId = $this->blogId;
            $this->useMainBlog();
            $value = $this->grabOptionFromDatabase('_site_transient_' . $key);
            $this->useBlog($currentBlogId);
            return $value;
        }

        public function seeSiteSiteTransientInDatabase($key, $value = null)
        {
            $currentBlogId = $this->blogId;
            $this->useMainBlog();
            $this->seeOptionInDatabase('_site_transient_' . $key, $value);
            $this->useBlog($currentBlogId);
        }

        public function seeSiteOptionInDatabase($key, $value = null)
        {
            $currentBlogId = $this->blogId;
            $this->useMainBlog();
            $this->seeOptionInDatabase('_site_option_' . $key, $value);
            $this->useBlog($currentBlogId);
        }

        /**
         * Conditionally checks for a user in the database.
         *
         * Will look up the "users" table, will throw if not found.
         *
         * @param  int $user_id The user ID.
         *
         * @return void
         */
        protected function maybeCheckUserExistsInDatabase($user_id)
        {
            if (!isset($this->config['checkExistence']) or false == $this->config['checkExistence']) {
                return;
            }
            $tableName = $this->grabPrefixedTableNameFor('users');
            if (!$this->grabFromDatabase($tableName, 'ID', array('ID' => $user_id))) {
                throw new \RuntimeException("A user with an id of $user_id does not exist", 1);
            }
        }

        /**
         * Conditionally check for a link in the database.
         *
         * Will look up the "links" table, will throw if not found.
         *
         * @param  int $link_id The link ID.
         *
         * @return bool True if the link exists, false otherwise.
         */
        protected function maybeCheckLinkExistsInDatabase($link_id)
        {
            if (!isset($this->config['checkExistence']) or false == $this->config['checkExistence']) {
                return;
            }
            $tableName = $this->grabPrefixedTableNameFor('links');
            if (!$this->grabFromDatabase($tableName, 'link_id', array('link_id' => $link_id))) {
                throw new \RuntimeException("A link with an id of $link_id does not exist", 1);
            }
        }
    }
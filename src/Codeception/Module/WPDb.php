<?php

namespace Codeception\Module;

use Codeception\Exception\ModuleConfigException;
use Codeception\Exception\ModuleException;
use Codeception\Exception\ModuleRequireException;
use Codeception\Lib\ModuleContainer;
use Gumlet\ImageResize;
use Gumlet\ImageResizeException;
use PDO;
use tad\WPBrowser\Exceptions\DumpException;
use tad\WPBrowser\Generators\Blog;
use tad\WPBrowser\Generators\Comment;
use tad\WPBrowser\Generators\Links;
use tad\WPBrowser\Generators\Post;
use tad\WPBrowser\Generators\Tables;
use tad\WPBrowser\Generators\User;
use tad\WPBrowser\Generators\WpPassword;
use tad\WPBrowser\Module\Support\DbDump;
use tad\WPBrowser\Traits\WithEvents;
use function tad\WPBrowser\db;
use function tad\WPBrowser\dbDsnString;
use function tad\WPBrowser\dbDsnToMap;
use function tad\WPBrowser\ensure;
use function tad\WPBrowser\renderString;
use function tad\WPBrowser\requireCodeceptionModules;
use function tad\WPBrowser\slug;
use function tad\WPBrowser\unleadslashit;
use function tad\WPBrowser\untrailslashit;

//phpcs:disable
requireCodeceptionModules('WPDb', [ 'Db' ]);
//phpcs:enable

/**
 * An extension of Codeception Db class to add WordPress database specific
 * methods.
 */
class WPDb extends Db
{
    use WithEvents;

    const EVENT_BEFORE_SUITE = 'WPDb.before_suite';
    const EVENT_BEFORE_INITIALIZE = 'WPDb.before_initialize';
    const EVENT_AFTER_INITIALIZE = 'WPDb.after_initialize';
    const EVENT_AFTER_DB_PREPARE =  'WPDb.after_db_prepare';
    const ADMIN_EMAIL_LIFESPAN = 2533080438;

    /**
     * @var \tad\WPBrowser\Module\Support\DbDump
     */
    protected $dbDump;

    /**
     * The theme stylesheet in use.
     *
     * @var string
     */
    protected $stylesheet = '';

    /**
     * The current theme menus.
     *
     * @var array<array<string,mixed>>
     */
    protected $menus = [];

    /**
     * The current menu items, per menu.
     *
     * @var array<array<string,mixed>>
     */
    protected $menuItems = [];

    /**
     * The placeholder that will be replaced with the iteration number when found in strings.
     *
     * @var string
     */
    protected $numberPlaceholder = '{{n}}';

    /**
     * The legit keys to term criteria.
     *
     * @var array<string>
     */
    protected $termKeys = ['term_id', 'name', 'slug', 'term_group'];

    /**
     * The legit keys for the term taxonomy criteria.
     *
     * @var array<string>
     */
    protected $termTaxonomyKeys = ['term_taxonomy_id', 'term_id', 'taxonomy', 'description', 'parent', 'count'];

    /**
     * A list of tables that WordPress will nor replicate in multisite installations.
     *
     * @var array<string>
     */
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
     * @var array<string>
     */
    protected $requiredFields = ['url'];

    /**
     * The module optional configuration parameters.
     *
     * @var array<string,mixed>
     */
    protected $config = [
        'tablePrefix' => 'wp_',
        'populate' => true,
        'cleanup' => true,
        'reconnect' => false,
        'dump' => null,
        'populator' => null,
        'urlReplacement' => true,
        'originalUrl' => null,
        'waitlock' => 10,
    ];

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
     * @var Tables
     */
    protected $tables;

    /**
     * The current template data.
     *
     * @var array<string,mixed>
     */
    protected $templateData;

    /**
     * An array containing the blog IDs of the sites scaffolded by the module.
     *
     * @var array<int>
     */
    protected $scaffoldedBlogIds;

    /**
     * Whether the module did init already or not.
     *
     * @var bool
     */
    protected $didInit = false;

    /**
     * The database driver object.
     *
     * @var \Codeception\Lib\Driver\Db
     */
    protected $driver;

    /**
     * Whether the database has been previously populated or not.
     *
     * @var bool
     */
    protected $populated;

    /**
     * WPDb constructor.
     *
     * @param ModuleContainer $moduleContainer The module container handling the suite modules.
     * @param array<string,mixed>|null            $config The module configuration
     * @param DbDump|null     $dbDump The database dump handler.
     *
     * @return void
     */
    public function __construct(ModuleContainer $moduleContainer, $config = null, DbDump $dbDump = null)
    {
        parent::__construct($moduleContainer, $config);
        $this->dbDump = $dbDump !== null ? $dbDump : new DbDump();
    }

    /**
     * Initializes the module.
     *
     * @param Tables $table An instance of the tables management object.
     *
     * @return void
     */
    public function _initialize(Tables $table = null)
    {
        /**
         * Dispatches an event before the WPDb module initializes.
         *
         * @param WPDb $this The current module instance.
         */
        $this->doAction(static::EVENT_BEFORE_INITIALIZE, $this);

        $this->createDatabasesIfNotExist($this->config);

        parent::_initialize();

        $this->tablePrefix = $this->config['tablePrefix'];
        $this->tables = $table ?: new Tables();
        $this->didInit = true;

        /**
         * Dispatches an event after the WPDb module has initialized.
         *
         * @param WPDb $this The current module instance.
         */
        $this->doAction(static::EVENT_AFTER_INITIALIZE, $this);
    }

    /**
     * Import the SQL dump file if populate is enabled.
     *
     * @example
     * ```php
     * // Import a dump file passing the absolute path.
     * $I->importSqlDumpFile(codecept_data_dir('dumps/start.sql'));
     * ```
     *
     * Specifying a dump file that file will be imported.
     *
     * @param string|null $dumpFile The dump file that should be imported in place of the default one.
     *
     * @return void
     *
     * @throws \InvalidArgumentException If the specified file does not exist.
     */
    public function importSqlDumpFile($dumpFile = null)
    {
        if ($dumpFile !== null) {
            if (!file_exists($dumpFile) || !is_readable($dumpFile)) {
                throw new \InvalidArgumentException("Dump file [{$dumpFile}] does not exist or is not readable.");
            }

            $this->driver->load($dumpFile);

            return;
        }

        if ($this->config['populate']) {
            $this->_cleanup();
            $this->_loadDump();
            $this->populated = true;
        }
    }

    /**
     * Cleans up the database.
     *
     * @param string|null $databaseKey The key of the database to clean up.
     * @param array<string,mixed>|null $databaseConfig The configuration of the database to clean up.
     *
     * @return void
     *
     * @throws ModuleException|ModuleConfigException If there's a configuration or operation issue.
     */
    public function _cleanup($databaseKey = null, $databaseConfig = null)
    {
        parent::_cleanup($databaseKey, $databaseConfig);
        $this->blogId = 0;
    }

    /**
     * Checks that an option is not in the database for the current blog.
     *
     * If the value is an object or an array then the serialized option will be checked.
     *
     * @example
     * ```php
     * $I->dontHaveOptionInDatabase('posts_per_page');
     * $I->dontSeeOptionInDatabase('posts_per_page');
     * ```
     *
     * @param array<string,mixed> $criteria An array of search criteria.
     *
     * @return void
     */
    public function dontSeeOptionInDatabase(array $criteria)
    {
        $tableName = $this->grabPrefixedTableNameFor('options');
        if (!empty($criteria['option_value'])) {
            $criteria['option_value'] = $this->maybeSerialize($criteria['option_value']);
        }
        $this->dontSeeInDatabase($tableName, $criteria);
    }

    /**
     * Returns a prefixed table name for the current blog.
     *
     * If the table is not one to be prefixed (e.g. `users`) then the proper table name will be returned.
     *
     * @example
     * ```php
     * // Will return wp_users.
     * $usersTable = $I->grabPrefixedTableNameFor('users');
     * // Will return wp_options.
     * $optionsTable = $I->grabPrefixedTableNameFor('options');
     * // Use a different blog and get its options table.
     * $I->useBlog(2);
     * $blogOptionsTable = $I->grabPrefixedTableNameFor('options');
     * ```
     *
     * @param  string $tableName The table name, e.g. `options`.
     *
     * @return string            The prefixed table name, e.g. `wp_options` or `wp_2_options`.
     */
    public function grabPrefixedTableNameFor($tableName = '')
    {
        $idFrag = '';
        if (!(in_array($tableName, $this->uniqueTables) || $this->blogId == 1)) {
            $idFrag = empty($this->blogId) ? '' : "{$this->blogId}_";
        }

        $tableName = $this->config['tablePrefix'] . $idFrag . $tableName;

        return $tableName;
    }

    /**
     * Maybe serialize the value if serializable and not already serialized.
     *
     * @param mixed $value The value to serialize.
     *
     * @return string The serialized value if not serialized or the original value.
     */
    protected function maybeSerialize($value)
    {
        return (is_array($value) || is_object($value)) ? serialize($value) : $value;
    }

    /**
     * Checks for a post meta value in the database for the current blog.
     *
     * If the `meta_value` is an object or an array then the check will be made for serialized values.
     *
     * @example
     * ```php
     * $postId = $I->havePostInDatabase(['meta_input' => ['foo' => 'bar']];
     * $I->seePostMetaInDatabase(['post_id' => '$postId', 'meta_key' => 'foo']);
     * ```
     *
     * @param  array<string,mixed> $criteria An array of search criteria.
     *
     * @return void
     */
    public function seePostMetaInDatabase(array $criteria)
    {
        $tableName = $this->grabPrefixedTableNameFor('postmeta');
        if (!empty($criteria['meta_value'])) {
            $criteria['meta_value'] = $this->maybeSerialize($criteria['meta_value']);
        }
        $this->seeInDatabase($tableName, $criteria);
    }

    /**
     * Checks for a link in the `links` table of the database.
     *
     * @example
     * ```php
     * // Asserts a link exists by name.
     * $I->seeLinkInDatabase(['link_name' => 'my-link']);
     * // Asserts at least one link exists for the user.
     * $I->seeLinkInDatabase(['link_owner' => $userId]);
     * ```
     *
     * @param  array<string,mixed> $criteria An array of search criteria.
     *
     * @return void
     */
    public function seeLinkInDatabase(array $criteria)
    {
        $tableName = $this->grabPrefixedTableNameFor('links');
        $this->seeInDatabase($tableName, $criteria);
    }

    /**
     * Checks that a link is not in the `links` database table.
     *
     * @example
     * ```php
     * $I->dontSeeLinkInDatabase(['link_url' => 'http://example.com']);
     * $I->dontSeeLinkInDatabase(['link_url' => 'http://example.com', 'link_name' => 'example']);
     * ```
     *
     * @param  array<string,mixed> $criteria An array of search criteria.
     *
     * @return void
     */
    public function dontSeeLinkInDatabase(array $criteria)
    {
        $tableName = $this->grabPrefixedTableNameFor('links');
        $this->dontSeeInDatabase($tableName, $criteria);
    }

    /**
     * Checks that a post meta value does not exist.
     *
     * If the meta value is an object or an array then the check will be made on its serialized version.
     *
     * @example
     * ```php
     * $postId = $I->havePostInDatabase(['meta_input' => ['foo' => 'bar']]);
     * $I->dontSeePostMetaInDatabase(['post_id' => $postId, 'meta_key' => 'woot']);
     * ```
     *
     * @param  array<string,mixed> $criteria An array of search criteria.
     *
     * @return void
     */
    public function dontSeePostMetaInDatabase(array $criteria)
    {
        $tableName = $this->grabPrefixedTableNameFor('postmeta');
        if (!empty($criteria['meta_value'])) {
            $criteria['meta_value'] = $this->maybeSerialize($criteria['meta_value']);
        }
        $this->dontSeeInDatabase($tableName, $criteria);
    }

    /**
     * Checks that a post to term relation exists in the database.
     *
     * The method will check the "term_relationships" table.
     *
     * @example
     * ```php
     * $fiction = $I->haveTermInDatabase('fiction', 'genre');
     * $postId = $I->havePostInDatabase(['tax_input' => ['genre' => ['fiction']]]);
     * $I->seePostWithTermInDatabase($postId, $fiction['term_taxonomy_id']);
     * ```
     *
     * @param  int          $post_id           The post ID.
     * @param  int          $term_taxonomy_id  The term `term_id` or `term_taxonomy_id`; if the `$taxonomy` argument is
     *                                         passed this parameter will be interpreted as a `term_id`, else as a
     *                                         `term_taxonomy_id`.
     * @param  int|null     $term_order        The order the term applies to the post, defaults to `null` to not use
     *                                         the
     *                                         term order.
     * @param  string|null  $taxonomy          The taxonomy the `term_id` is for; if passed this parameter will be used
     *                                         to build a `taxonomy_term_id` from the `term_id`.
     *
     * @return void
     *
     * @throws ModuleException If a `term_id` is specified but it cannot be matched to the `taxonomy`.
     */
    public function seePostWithTermInDatabase($post_id, $term_taxonomy_id, $term_order = null, $taxonomy = null)
    {
        if ($taxonomy !== null) {
            $match = $this->grabTermTaxonomyIdFromDatabase([
                'term_id' => $term_taxonomy_id,
                'taxonomy' => $taxonomy
            ]);
            if (empty($match)) {
                throw new ModuleException(
                    $this,
                    "No term exists for the `term_id` ({$term_taxonomy_id}) and `taxonomy`({$taxonomy}) couple."
                );
            }
            $term_taxonomy_id = $match;
        }

        $tableName = $this->grabPrefixedTableNameFor('term_relationships');
        $criteria = [
            'object_id' => $post_id,
            'term_taxonomy_id' => $term_taxonomy_id,
        ];

        if (null !== $term_order) {
            $criteria['term_order'] = $term_order;
        }

        $this->seeInDatabase($tableName, $criteria);
    }

    /**
     * Checks that a user is in the database.
     *
     * The method will check the "users" table.
     *
     * @example
     * ```php
     * $I->seeUserInDatabase([
     *     "user_email" => "test@example.org",
     *     "user_login" => "login name"
     * ])
     * ```
     *
     * @param  array<string,mixed> $criteria An array of search criteria.
     *
     * @return void
     */
    public function seeUserInDatabase(array $criteria)
    {
        $tableName = $this->grabPrefixedTableNameFor('users');
        $allCriteria = $criteria;
        if (!empty($criteria['user_pass'])) {
            $userPass = $criteria['user_pass'];
            unset($criteria['user_pass']);
            $hashedPass = $this->grabFromDatabase($tableName, 'user_pass', $criteria);
            $passwordOk = WpPassword::instance()->check($userPass, $hashedPass);
            $this->assertTrue(
                $passwordOk,
                'No matching records found for criteria ' . json_encode($allCriteria) . ' in table ' . $tableName
            );
        }
        $this->seeInDatabase($tableName, $criteria);
    }

    /**
     * Checks that a user is not in the database.
     *
     * @example
     * ```php
     * // Asserts a user does not exist in the database.
     * $I->dontSeeUserInDatabase(['user_login' => 'luca']);
     * // Asserts a user with email and login is not in the database.
     * $I->dontSeeUserInDatabase(['user_login' => 'luca', 'user_email' => 'luca@theaveragedev.com']);
     * ```
     *
     * @param  array<string,mixed> $criteria An array of search criteria.
     *
     * @return void
     */
    public function dontSeeUserInDatabase(array $criteria)
    {
        $tableName = $this->grabPrefixedTableNameFor('users');
        $allCriteria = $criteria;
        $passwordOk = false;
        if (!empty($criteria['user_pass'])) {
            $userPass = $criteria['user_pass'];
            unset($criteria['user_pass']);
            $hashedPass = $this->grabFromDatabase($tableName, 'user_pass', [$criteria]);
            $passwordOk = WpPassword::instance()->check($userPass, $hashedPass);
        }

        $count = $this->countInDatabase($tableName, $criteria);
        $this->assertTrue(
            !$passwordOk && $count < 1,
            'Unexpectedly found matching records for criteria ' . json_encode($allCriteria) . ' in table ' . $tableName
        );
    }

    /**
     * Inserts a page in the database.
     *
     * @example
     * ```php
     * // Creates a test page in the database with random values.
     * $randomPageId = $I->havePageInDatabase();
     * // Creates a test page in the database defining its title.
     * $testPageId = $I->havePageInDatabase(['post_title' => 'Test page']);
     * ```
     *
     * @param array<string,mixed> $overrides An array of values to override the default ones.
     *
     * @return int The inserted page post ID.
     *
     * @see \Codeception\Module\WPDb::havePostInDatabase()
     */
    public function havePageInDatabase(array $overrides = [])
    {
        $overrides['post_type'] = 'page';

        return $this->havePostInDatabase($overrides);
    }

    /**
     * Inserts a post in the database.
     *
     * @param array<int|string,mixed> $data An associative array of post data to override default and random generated
     *                                  values.
     *
     * @return int post_id The inserted post ID.
     *
     * @throws \Exception If there's an exception during the insertion.
     *
     * @example
     * ```php
     * // Insert a post with random values in the database.
     * $randomPostId = $I->havePostInDatabase();
     * // Insert a post with specific values in the database.
     * $I->havePostInDatabase([
     * 'post_type' => 'book',
     * 'post_title' => 'Alice in Wonderland',
     * 'meta_input' => [
     * 'readers_count' => 23
     * ],
     * 'tax_input' => [
     * ['genre' => 'fiction']
     * ]
     * ]);
     * ```
     *
     */
    public function havePostInDatabase(array $data = [])
    {
        $postTableName = $this->grabPostsTableName();
        $idColumn = 'ID';
        $id = $this->grabLatestEntryByFromDatabase($postTableName, $idColumn) + 1;
        $post = Post::buildPostData($id, $this->config['url'], $data);
        $hasMeta = !empty($data['meta']) || !empty($data['meta_input']);
        $hasTerms = !empty($data['terms']) || !empty($data['tax_input']);

        $meta = [];
        if ($hasMeta) {
            $meta = !empty($data['meta']) ? $data['meta'] : $data['meta_input'];
            unset($post['meta']);
            unset($post['meta_input']);
        }

        $terms = [];
        if ($hasTerms) {
            $terms = !empty($data['terms']) ? $data['terms'] : $data['tax_input'];
            unset($post['terms']);
            unset($post['tax_input']);
        }

        $postId = $this->haveInDatabase($postTableName, $post);

        if ($hasMeta) {
            foreach ($meta as $meta_key => $meta_value) {
                $this->havePostmetaInDatabase($postId, $meta_key, $meta_value);
            }
        }

        if ($hasTerms) {
            foreach ($terms as $taxonomy => $termNames) {
                foreach ($termNames as $termName) {
                    // Let's try to match the term by name first.
                    $termId = $this->grabTermIdFromDatabase(['name' => $termName]);

                    // Then by slug.
                    if (empty($termId)) {
                        $termId = $this->grabTermIdFromDatabase(['slug' => $termName]);
                    }

                    // Then by `term_id`.
                    if (empty($termId)) {
                        $termId = $this->grabTermIdFromDatabase(['term_id' => $termName]);
                    }

                    if (empty($termId)) {
                        $termIds = $this->haveTermInDatabase($termName, $taxonomy);
                        $termId = reset($termIds);
                    }

                    $termTaxonomyId = $this->grabTermTaxonomyIdFromDatabase([
                        'term_id' => $termId,
                        'taxonomy' => $taxonomy,
                    ]);

                    $this->haveTermRelationshipInDatabase($postId, $termTaxonomyId);
                    $this->increaseTermCountBy($termTaxonomyId, 1);
                }
            }
        }

        return $postId;
    }

    /**
     * Gets the posts prefixed table name.
     *
     * @example
     * ```php
     * // Given a `wp_` table prefix returns `wp_posts`.
     * $postsTable = $I->grabPostsTableName();
     * // Given a `wp_` table prefix returns `wp_23_posts`.
     * $I->useBlog(23);
     * $postsTable = $I->grabPostsTableName();
     * ```
     *
     * @return string The prefixed table name, e.g. `wp_posts`
     */
    public function grabPostsTableName()
    {
        return $this->grabPrefixedTableNameFor('posts');
    }

    /**
     * Returns the id value of the last table entry.
     *
     * @example
     * ```php
     * $I->haveManyPostsInDatabase();
     * $postsTable = $I->grabPostsTableName();
     * $last = $I->grabLatestEntryByFromDatabase($postsTable, 'ID');
     * ```
     *
     * @param string $tableName The table to fetch the last insertion for.
     * @param string $idColumn The column that is used, in the table, to uniquely identify
     *                         items.
     *
     * @return int The last insertion id.
     */
    public function grabLatestEntryByFromDatabase($tableName, $idColumn = 'ID')
    {
        $dbh = $this->_getDbh();
        $sth = $dbh->prepare("SELECT {$idColumn} FROM {$tableName} ORDER BY {$idColumn} DESC LIMIT 1");
        $this->debugSection('Query', $sth->queryString);
        $sth->execute();

        return $sth->fetchColumn();
    }

    /**
     * Adds one or more meta key and value couples in the database for a post.
     *
     * @example
     * ```php
     * // Set the post-meta for a post.
     * $I->havePostmetaInDatabase($postId, 'karma', 23);
     * // Set an array post-meta for a post, it will be serialized in the db.
     * $I->havePostmetaInDatabase($postId, 'data', ['one', 'two']);
     * // Use a loop to insert one meta per row.
     * foreach( ['one', 'two'] as $value){
     *      $I->havePostmetaInDatabase($postId, 'data', $value);
     * }
     * ```
     * @param int    $postId     The post ID.
     * @param string $meta_key   The meta key.
     * @param mixed  $meta_value The value to insert in the database, objects and arrays will be serialized.
     *
     * @return int The inserted meta `meta_id`.
     *
     */
    public function havePostmetaInDatabase($postId, $meta_key, $meta_value)
    {
        if (!is_int($postId)) {
            throw new \BadMethodCallException('Post id must be an int', 1);
        }
        if (!is_string($meta_key)) {
            throw new \BadMethodCallException('Meta key must be an string', 3);
        }
        $tableName = $this->grabPostMetaTableName();

        return $this->haveInDatabase($tableName, [
            'post_id' => $postId,
            'meta_key' => $meta_key,
            'meta_value' => $this->maybeSerialize($meta_value),
        ]);
    }

    /**
     * Returns the prefixed post meta table name.
     *
     * @example
     * ```php
     * // Returns 'wp_postmeta'.
     * $I->grabPostmetaTableName();
     * // Returns 'wp_23_postmeta'.
     * $I->useBlog(23);
     * $I->grabPostmetaTableName();
     * ```
     *
     * @return string The prefixed `postmeta` table name, e.g. `wp_postmeta`.
     */
    public function grabPostmetaTableName()
    {
        return $this->grabPrefixedTableNameFor('postmeta');
    }

    /**
     * Gets a term ID from the database.
     * Looks up the prefixed `terms` table, e.g. `wp_terms`.
     *
     * @example
     * ```php
     * // Return the 'fiction' term 'term_id'.
     * $termId = $I->grabTermIdFromDatabase(['name' => 'fiction']);
     * // Get a term ID by more stringent criteria.
     * $termId = $I->grabTermIdFromDatabase(['name' => 'fiction', 'slug' => 'genre--fiction']);
     * // Return the 'term_id' of the first term for a group.
     * $termId = $I->grabTermIdFromDatabase(['term_group' => 23]);
     * ```
     *
     * @param array<string,mixed> $criteria An array of search criteria.
     *
     * @return int The matching term `term_id`
     */
    public function grabTermIdFromDatabase(array $criteria)
    {
        return $this->grabFromDatabase($this->grabTermsTableName(), 'term_id', $criteria);
    }

    /**
     * Gets the prefixed terms table name, e.g. `wp_terms`.
     *
     * @example
     * ```php
     * // Returns 'wp_terms'.
     * $I->grabTermsTableName();
     * // Returns 'wp_23_terms'.
     * $I->useBlog(23);
     * $I->grabTermsTableName();
     * ```
     *
     * @return string The prefixed terms table name.
     */
    public function grabTermsTableName()
    {
        return $this->grabPrefixedTableNameFor('terms');
    }

    /**
     * Inserts a term in the database.
     *
     * @example
     * ```php
     * // Insert a random 'genre' term in the database.
     * $I->haveTermInDatabase('non-fiction', 'genre');
     * // Insert a term in the database with term meta.
     * $I->haveTermInDatabase('fiction', 'genre', [
     *      'slug' => 'genre--fiction',
     *      'meta' => [
     *         'readers_count' => 23
     *      ]
     * ]);
     * ```
     *
     * @param  string $name      The term name, e.g. "Fuzzy".
     * @param string  $taxonomy  The term taxonomy
     * @param array<int|string,mixed>   $overrides An array of values to override the default ones.
     *
     * @return array<int> An array containing `term_id` and `term_taxonomy_id` of the inserted term.
     */
    public function haveTermInDatabase($name, $taxonomy, array $overrides = [])
    {
        $termDefaults = ['slug' => slug($name), 'term_group' => 0];

        $hasMeta = !empty($overrides['meta']);
        $meta = [];
        if ($hasMeta) {
            $meta = $overrides['meta'];
            unset($overrides['meta']);
        }

        $termData = array_merge($termDefaults, array_intersect_key($overrides, $termDefaults));
        $termData['name'] = $name;
        $term_id = $this->haveInDatabase($this->grabTermsTableName(), $termData);

        $termTaxonomyDefaults = ['description' => '', 'parent' => 0, 'count' => 0];
        $termTaxonomyData = array_merge($termTaxonomyDefaults, array_intersect_key($overrides, $termTaxonomyDefaults));
        $termTaxonomyData['taxonomy'] = $taxonomy;
        $termTaxonomyData['term_id'] = $term_id;
        $term_taxonomy_id = $this->haveInDatabase($this->grabTermTaxonomyTableName(), $termTaxonomyData);

        if ($hasMeta) {
            foreach ($meta as $key => $value) {
                $this->haveTermMetaInDatabase($term_id, $key, $value);
            }
        }

        return [$term_id, $term_taxonomy_id];
    }

    /**
     * Gets the prefixed term and taxonomy table name, e.g. `wp_term_taxonomy`.
     *
     * @example
     * ```php
     * // Returns 'wp_term_taxonomy'.
     * $I->grabTermTaxonomyTableName();
     * // Returns 'wp_23_term_taxonomy'.
     * $I->useBlog(23);
     * $I->grabTermTaxonomyTableName();
     * ```
     *
     * @return string The prefixed term taxonomy table name.
     */
    public function grabTermTaxonomyTableName()
    {
        return $this->grabPrefixedTableNameFor('term_taxonomy');
    }

    /**
     * Inserts a term meta row in the database.
     * Objects and array meta values will be serialized.
     *
     * @example
     * ```php
     * $I->haveTermMetaInDatabase($fictionId, 'readers_count', 23);
     * // Insert some meta that will be serialized.
     * $I->haveTermMetaInDatabase($fictionId, 'flags', [3, 4, 89]);
     * // Use a loop to insert one meta per row.
     * foreach([3, 4, 89] as $value) {
     *      $I->haveTermMetaInDatabase($fictionId, 'flag', $value);
     * }
     * ```
     *
     * @param int    $term_id The ID of the term to insert the meta for.
     * @param string $meta_key The key of the meta to insert.
     * @param mixed  $meta_value The value of the meta to insert, if serializable it will be serialized.
     *
     * @return int The inserted term meta `meta_id`.
     */
    public function haveTermMetaInDatabase($term_id, $meta_key, $meta_value)
    {
        if (!is_int($term_id)) {
            throw new \BadMethodCallException('Term id must be an int');
        }
        if (!is_string($meta_key)) {
            throw new \BadMethodCallException('Meta key must be an string');
        }
        $tableName = $this->grabTermMetaTableName();

        return $this->haveInDatabase($tableName, [
            'term_id' => $term_id,
            'meta_key' => $meta_key,
            'meta_value' => $this->maybeSerialize($meta_value),
        ]);
    }

    /**
     * Gets the terms meta table prefixed name.
     *
     * @example
     * ```php
     * // Returns 'wp_termmeta'.
     * $I->grabTermMetaTableName();
     * // Returns 'wp_23_termmeta'.
     * $I->useBlog(23);
     * $I->grabTermMetaTableName();
     * ```
     *
     * @return string The prefixed term meta table name.
     */
    public function grabTermMetaTableName()
    {
        return $this->grabPrefixedTableNameFor('termmeta');
    }

    /**
     * Gets a `term_taxonomy_id` from the database.
     *
     * Looks up the prefixed `terms_relationships` table, e.g. `wp_term_relationships`.
     *
     * @example
     * ```php
     * // Get the `term_taxonomy_id` for a term and a taxonomy.
     * $I->grabTermTaxonomyIdFromDatabase(['term_id' => $fictionId, 'taxonomy' => 'genre']);
     * // Get the `term_taxonomy_id` for the first term with a count of 23.
     * $I->grabTermTaxonomyIdFromDatabase(['count' => 23]);
     * ```
     *
     * @param array<string,mixed> $criteria An array of search criteria.
     *
     * @return int The matching term `term_taxonomy_id`
     */
    public function grabTermTaxonomyIdFromDatabase(array $criteria)
    {
        return $this->grabFromDatabase($this->grabTermTaxonomyTableName(), 'term_taxonomy_id', $criteria);
    }

    /**
     * Creates a term relationship in the database.
     *
     * No check about the consistency of the insertion is made. E.g. a post could be assigned a term from
     * a taxonomy that's not registered for that post type.
     *
     * @example
     * ```php
     * // Assign the `fiction` term to a book.
     * $I->haveTermRelationshipInDatabase($bookId, $fictionId);
     * ```
     *
     * @param int $object_id  A post ID, a user ID or anything that can be assigned a taxonomy term.
     * @param int $term_taxonomy_id The `term_taxonomy_id` of the term and taxonomy to create a relation with.
     * @param int $term_order Defaults to `0`.
     *
     * @return void
     */
    public function haveTermRelationshipInDatabase($object_id, $term_taxonomy_id, $term_order = 0)
    {
        $this->haveInDatabase($this->grabTermRelationshipsTableName(), [
            'object_id' => $object_id,
            'term_taxonomy_id' => $term_taxonomy_id,
            'term_order' => $term_order,
        ]);
    }

    /**
     * Gets the prefixed term relationships table name, e.g. `wp_term_relationships`.
     *
     * @example
     * ```php
     * $I->grabTermRelationshipsTableName();
     * ```
     *
     * @return string The `term_relationships` table complete name, including the table prefix.
     */
    public function grabTermRelationshipsTableName()
    {
        return $this->grabPrefixedTableNameFor('term_relationships');
    }

    /**
     * Increases the term counter.
     *
     * @param    int $termTaxonomyId The ID of the term to increase the count for.
     * @param int $by The value to increase the count by.
     *
     * @return bool Whether the update happened correctly or not.
     *
     * @throws \Exception If there's any error during the update.
     */
    protected function increaseTermCountBy($termTaxonomyId, $by = 1)
    {
        $updateQuery = "UPDATE {$this->grabTermTaxonomyTableName()} SET count = count + {$by}
          WHERE term_taxonomy_id = {$termTaxonomyId}";

        return (bool)$this->_getDriver()->executeQuery($updateQuery, []);
    }

    /**
     * Checks for a page in the database.
     *
     * @example
     * ```php
     * // Asserts a page with an exists in the database.
     * $I->seePageInDatabase(['ID' => 23]);
     * // Asserts a page with a slug and ID exists in the database.
     * $I->seePageInDatabase(['post_title' => 'Test Page', 'ID' => 23]);
     * ```
     *
     * @param  array<string,mixed> $criteria An array of search criteria.
     *
     * @return void
     */
    public function seePageInDatabase(array $criteria)
    {
        $criteria['post_type'] = 'page';
        $this->seePostInDatabase($criteria);
    }

    /**
     * Checks for a post in the database.
     *
     * @example
     * ```php
     * // Assert a post exists in the database.
     * $I->seePostInDatabase(['ID' => 23]);
     * // Assert a post with a slug and ID exists in the database.
     * $I->seePostInDatabase(['post_content' => 'test content', 'ID' => 23]);
     * ```
     *
     * @param  array<string,mixed> $criteria An array of search criteria.
     *
     * @return void
     */
    public function seePostInDatabase(array $criteria)
    {
        $tableName = $this->grabPrefixedTableNameFor('posts');
        $this->seeInDatabase($tableName, $criteria);
    }

    /**
     * Checks that a page is not in the database.
     *
     * @example
     * ```php
     * // Assert a page with an ID does not exist.
     * $I->dontSeePageInDatabase(['ID' => 23]);
     * // Assert a page with a slug and ID.
     * $I->dontSeePageInDatabase(['post_name' => 'test', 'ID' => 23]);
     * ```
     *
     * @param  array<string,mixed> $criteria An array of search criteria.
     *
     * @return void
     */
    public function dontSeePageInDatabase(array $criteria)
    {
        $criteria['post_type'] = 'page';
        $this->dontSeePostInDatabase($criteria);
    }

    /**
     * Checks that a post is not in the database.
     *
     * @example
     * ```php
     * // Asserts a post with title 'Test' is not in the database.
     * $I->dontSeePostInDatabase(['post_title' => 'Test']);
     * // Asserts a post with title 'Test' and content 'Test content' is not in the database.
     * $I->dontSeePostInDatabase(['post_title' => 'Test', 'post_content' => 'Test content']);
     * ```
     *
     * @param  array<string,mixed> $criteria An array of search criteria.
     *
     * @return void
     */
    public function dontSeePostInDatabase(array $criteria)
    {
        $tableName = $this->grabPrefixedTableNameFor('posts');
        $this->dontSeeInDatabase($tableName, $criteria);
    }

    /**
     * Checks for a comment in the database.
     *
     * Will look up the "comments" table.
     *
     * @example
     * ```php
     * $I->seeCommentInDatabase(['comment_ID' => 23]);
     * ```
     *
     * @param  array<string,mixed> $criteria An array of search criteria.
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
     * @example
     * ```php
     * // Checks for one comment.
     * $I->dontSeeCommentInDatabase(['comment_ID' => 23]);
     * // Checks for comments from a user.
     * $I->dontSeeCommentInDatabase(['user_id' => 89]);
     * ```
     *
     * @param  array<string,mixed> $criteria The search criteria.
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
     * Will look up the "commentmeta" table.
     *
     * @example
     * ```php
     * // Assert a specifid meta for a comment exists.
     * $I->seeCommentMetaInDatabase(['comment_ID' => $commentId, 'meta_key' => 'karma', 'meta_value' => 23]);
     * // Assert the comment has at least one meta set.
     * $I->seeCommentMetaInDatabase(['comment_ID' => $commentId]);
     * ```
     *
     * @param  array<string,mixed> $criteria An array of search criteria.
     *
     * @return void
     */
    public function seeCommentMetaInDatabase(array $criteria)
    {
        $tableName = $this->grabPrefixedTableNameFor('commentmeta');
        if (!empty($criteria['meta_value'])) {
            $criteria['meta_value'] = $this->maybeSerialize($criteria['meta_value']);
        }
        $this->seeInDatabase($tableName, $criteria);
    }

    /**
     * Checks that a comment meta value is not in the database.
     *
     * Will look up the "commentmeta" table.
     *
     * @example
     * ```php
     * // Delete a comment `karma` meta.
     * $I->dontSeeCommentMetaInDatabase(['comment_id' => 23, 'meta_key' => 'karma']);
     * // Delete all meta for a comment.
     * $I->dontSeeCommentMetaInDatabase(['comment_id' => 23]);
     * ```
     *
     * @param  array<string,mixed> $criteria An array of search criteria.
     *
     * @return void
     */
    public function dontSeeCommentMetaInDatabase(array $criteria)
    {
        $tableName = $this->grabPrefixedTableNameFor('commentmeta');
        if (!empty($criteria['meta_value'])) {
            $criteria['meta_value'] = $this->maybeSerialize($criteria['meta_value']);
        }
        $this->dontSeeInDatabase($tableName, $criteria);
    }

    /**
     * Checks for a user meta value in the database.
     *
     * @example
     * ```php
     * $I->seeUserMetaInDatabase(['user_id' => 23, 'meta_key' => 'karma']);
     * ```
     *
     * @param  array<string,mixed> $criteria An array of search criteria.
     *
     * @return void
     */
    public function seeUserMetaInDatabase(array $criteria)
    {
        $tableName = $this->grabPrefixedTableNameFor('usermeta');
        if (!empty($criteria['meta_value'])) {
            $criteria['meta_value'] = $this->maybeSerialize($criteria['meta_value']);
        }
        $this->seeInDatabase($tableName, $criteria);
    }

    /**
     * Check that a user meta value is not in the database.
     *
     * @example
     * ```php
     * // Asserts a user does not have a 'karma' meta assigned.
     * $I->dontSeeUserMetaInDatabase(['user_id' => 23, 'meta_key' => 'karma']);
     * // Asserts no user has any 'karma' meta assigned.
     * $I->dontSeeUserMetaInDatabase(['meta_key' => 'karma']);
     * ```
     *
     * @param  array<string,mixed> $criteria An array of search criteria.
     *
     * @return void
     */
    public function dontSeeUserMetaInDatabase(array $criteria)
    {
        $tableName = $this->grabPrefixedTableNameFor('usermeta');
        if (!empty($criteria['meta_value'])) {
            $criteria['meta_value'] = $this->maybeSerialize($criteria['meta_value']);
        }
        $this->dontSeeInDatabase($tableName, $criteria);
    }

    /**
     * Removes a link from the database.
     *
     * @example
     * ```php
     * $I->dontHaveLinkInDatabase(['link_url' => 'http://example.com']);
     * ```
     *
     * @param  array<string,mixed> $criteria An array of search criteria.
     *
     * @return void
     */
    public function dontHaveLinkInDatabase(array $criteria)
    {
        $tableName = $this->grabPrefixedTableNameFor('links');
        $this->dontHaveInDatabase($tableName, $criteria);
    }

    /**
     * Deletes a database entry.
     *
     * @param string              $table    The table name.
     * @param array<string,mixed> $criteria An associative array of the column names and values to use as deletion
     *                                      criteria.
     *
     * @return void
     * @example
     * ```php
     * $I->dontHaveInDatabase('custom_table', ['book_ID' => 23, 'book_genre' => 'fiction']);
     * ```
     *
     */
    public function dontHaveInDatabase($table, array $criteria)
    {
        try {
            $this->_getDriver()->deleteQueryByCriteria($table, $criteria);
        } catch (\Exception $e) {
            $this->debug("Couldn't delete record(s) from {$table} with criteria " . json_encode($criteria));
        }
    }

    /**
     * Removes an entry from the term_relationships table.
     *
     * @example
     * ```php
     * // Remove the relation between a post and a category.
     * $I->dontHaveTermRelationshipInDatabase(['object_id' => $postId, 'term_taxonomy_id' => $ttaxId]);
     * // Remove all terms for a post.
     * $I->dontHaveTermMetaInDatabase(['object_id' => $postId]);
     * ```
     *
     * @param  array<string,mixed> $criteria An array of search criteria.
     *
     * @return void
     */
    public function dontHaveTermRelationshipInDatabase(array $criteria)
    {
        $tableName = $this->grabPrefixedTableNameFor('term_relationships');
        $this->dontHaveInDatabase($tableName, $criteria);
    }

    /**
     * Removes an entry from the `term_taxonomy` table.
     *
     * @example
     * ```php
     * // Remove a specific term from the genre taxonomy.
     * $I->dontHaveTermTaxonomyInDatabase(['term_id' => $postId, 'taxonomy' => 'genre']);
     * // Remove all terms for a taxonomy.
     * $I->dontHaveTermTaxonomyInDatabase(['taxonomy' => 'genre']);
     * ```
     *
     * @param  array<string,mixed> $criteria An array of search criteria.
     *
     * @return void
     */
    public function dontHaveTermTaxonomyInDatabase(array $criteria)
    {
        $tableName = $this->grabPrefixedTableNameFor('term_taxonomy');
        $this->dontHaveInDatabase($tableName, $criteria);
    }

    /**
     * Removes an entry from the usermeta table.
     *
     * @example
     * ```php
     * // Remove the `karma` user meta for a user.
     * $I->dontHaveUserMetaInDatabase(['user_id' => 23, 'meta_key' => 'karma']);
     * // Remove all the user meta for a user.
     * $I->dontHaveUserMetaInDatabase(['user_id' => 23]);
     * ```
     *
     * @param  array<string,mixed> $criteria An array of search criteria.
     *
     * @return void
     */
    public function dontHaveUserMetaInDatabase(array $criteria)
    {
        $tableName = $this->grabPrefixedTableNameFor('usermeta');
        $this->dontHaveInDatabase($tableName, $criteria);
    }

    /**
     * Gets a user meta from the database.
     *
     * @example
     * ```php
     * // Returns a user 'karma' value.
     * $I->grabUserMetaFromDatabase($userId, 'karma');
     * // Returns an array, the unserialized version of the value stored in the database.
     * $I->grabUserMetaFromDatabase($userId, 'api_data');
     * ```
     *
     * @param int    $userId The ID of th user to get the meta for.
     * @param string $meta_key The meta key to fetch the value for.
     *
     * @return array<string,mixed> An associative array of meta key/values.
     *
     * @throws \Exception If the search criteria is incoherent.
     */
    public function grabUserMetaFromDatabase($userId, $meta_key)
    {
        $table = $this->grabPrefixedTableNameFor('usermeta');
        $meta = $this->grabAllFromDatabase($table, 'meta_value', ['user_id' => $userId, 'meta_key' => $meta_key]);
        if (empty($meta)) {
            return [];
        }

        return array_column($meta, 'meta_value');
    }

    /**
     * Returns all entries matching a criteria from the database.
     *
     * @example
     * ```php
     * $books = $I->grabPrefixedTableNameFor('books');
     * $I->grabAllFromDatabase($books, 'title', ['genre' => 'fiction']);
     * ```
     *
     * @param string $table The table to grab the values from.
     * @param string $column The column to fetch.
     * @param array<string,mixed>  $criteria The search criteria.
     *
     * @return array<string,mixed> An array of results.
     *
     * @throws \Exception If the criteria is inconsistent.
     */
    public function grabAllFromDatabase($table, $column, $criteria)
    {
        $query = $this->_getDriver()->select($column, $table, $criteria);

        $sth = $this->_getDriver()->executeQuery($query, array_values($criteria));

        return $sth->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Inserts a transient in the database.
     *
     * If the value is an array or an object then the value will be serialized.
     * Since the transients are set in the context of tests it's not possible to
     * set an expiration directly.
     *
     * @example
     * ```php
     * // Store an array in the `tweets` transient.
     * $I->haveTransientInDatabase('tweets', $tweets);
     * ```
     *
     * @param string $transient The transient name.
     * @param mixed  $value The transient value.
     *
     * @return int The inserted option `option_id`.
     */
    public function haveTransientInDatabase($transient, $value)
    {
        return $this->haveOptionInDatabase('_transient_' . $transient, $value);
    }

    /**
     * Inserts an option in the database.
     *
     * @example
     * ```php
     * $I->haveOptionInDatabase('posts_per_page', 23);
     * $I->haveOptionInDatabase('my_plugin_options', ['key_one' => 'value_one', 'key_two' => 89]);
     * ```
     *
     * If the option value is an object or an array then the value will be serialized.
     *
     * @param  string $option_name The option name.
     * @param  mixed  $option_value The option value; if an array or object it will be serialized.
     * @param string  $autoload Wether the option should be autoloaded by WordPress or not.
     *
     * @return int The inserted option `option_id`
     */
    public function haveOptionInDatabase($option_name, $option_value, $autoload = 'yes')
    {
        $table = $this->grabPrefixedTableNameFor('options');
        $this->dontHaveInDatabase($table, ['option_name' => $option_name]);
        $option_value = $this->maybeSerialize($option_value);

        return $this->haveInDatabase($table, [
            'option_name' => $option_name,
            'option_value' => $option_value,
            'autoload' => $autoload,
        ]);
    }

    /**
     * Removes a transient from the database.
     *
     * @example
     * ```php
     * // Removes the `tweets` transient from the database, if set.
     * $I->dontHaveTransientInDatabase('tweets');
     * ```
     *
     * @param string $transient The name of the transient to delete.
     *
     * @return void
     */
    public function dontHaveTransientInDatabase($transient)
    {
        $this->dontHaveOptionInDatabase('_transient_' . $transient);
    }

    /**
     * Removes an entry from the options table.
     *
     * @example
     * ```php
     * // Remove the `foo` option.
     * $I->dontHaveOptionInDatabase('foo');
     * // Remove the 'bar' option only if it has the `baz` value.
     * $I->dontHaveOptionInDatabase('bar', 'baz');
     * ```
     *
     * @param string     $key   The option name.
     * @param mixed|null $value If set the option will only be removed if its value matches the passed one.
     *
     * @return void
     */
    public function dontHaveOptionInDatabase($key, $value = null)
    {
        $tableName = $this->grabPrefixedTableNameFor('options');
        $criteria['option_name'] = $key;
        if (!empty($value)) {
            $criteria['option_value'] = $this->maybeUnserialize($value);
        }

        $this->dontHaveInDatabase($tableName, $criteria);
    }

    /**
     * Inserts a site option in the database.
     *
     * If the value is an array or an object then the value will be serialized.
     *
     * @example
     * ```php
     * $fooCountOptionId = $I->haveSiteOptionInDatabase('foo_count','23');
     * ```
     *
     * @param string $key The name of the option to insert.
     * @param mixed  $value The value ot insert for the option.
     *
     * @return int The inserted option `option_id`.
     */
    public function haveSiteOptionInDatabase($key, $value)
    {
        $currentBlogId = $this->blogId;
        $this->useMainBlog();
        $option_id = $this->haveOptionInDatabase('_site_option_' . $key, $value);
        $this->useBlog($currentBlogId);

        return $option_id;
    }

    /**
     * Sets the current blog to the main one (`blog_id` 1).
     *
     * @example
     * ```php
     * // Switch to the blog with ID 23.
     * $I->useBlog(23);
     * // Switch back to the main blog.
     * $I->useMainBlog();
     * ```
     *
     * @return void
     */
    public function useMainBlog()
    {
        $this->useBlog(0);
    }

    /**
     * Sets the blog to be used.
     *
     * This has nothing to do with WordPress `switch_to_blog` function, this code will affect the table prefixes used.
     *
     * @example
     * ```php
     * // Switch to the blog with ID 23.
     * $I->useBlog(23);
     * // Switch back to the main blog.
     * $I->useMainBlog();
     * ```
     *
     * @param int $blogId The ID of the blog to use.
     *
     * @return void
     */
    public function useBlog($blogId = 0)
    {
        if (!(is_numeric($blogId) && intval($blogId) === $blogId && intval($blogId) >= 0)) {
            throw new \InvalidArgumentException('Id must be an integer greater than or equal to 0');
        }
        $this->blogId = intval($blogId);
    }

    /**
     * Removes a site option from the database.
     *
     * @example
     * ```php
     * // Remove the `foo_count` option.
     * $I->dontHaveSiteOptionInDatabase('foo_count');
     * // Remove the `foo_count` option only if its value is `23`.
     * $I->dontHaveSiteOptionInDatabase('foo_count', 23);
     * ```
     *
     * @param string $key The option name.
     * @param mixed|null $value If set the option will only be removed it its value matches the specified one.
     *
     * @return void
     */
    public function dontHaveSiteOptionInDatabase($key, $value = null)
    {
        $currentBlogId = $this->blogId;
        $this->useMainBlog();
        $this->dontHaveOptionInDatabase('_site_option_' . $key, $value);
        $this->useBlog($currentBlogId);
    }

    /**
     * Inserts a site transient in the database.
     * If the value is an array or an object then the value will be serialized.
     *
     * @example
     * ```php
     * $I->haveSiteTransientInDatabase('total_comments_count', 23);
     * // This value will be serialized.
     * $I->haveSiteTransientInDatabase('api_data', ['user' => 'luca', 'token' => '11ae3ijns-j83']);
     * ```
     *
     * @param string $key The key of the site transient to insert, w/o the `_site_transient_` prefix.
     * @param mixed $value The value to insert; if serializable the value will be serialized.
     *
     * @return int The inserted transient `option_id`
     */
    public function haveSiteTransientInDatabase($key, $value)
    {
        $currentBlogId = $this->blogId;
        $this->useMainBlog();
        $option_id = $this->haveOptionInDatabase('_site_transient_' . $key, $value);
        $this->useBlog($currentBlogId);

        return $option_id;
    }

    /**
     * Removes a site transient from the database.
     *
     * @example
     * ```php
     * $I->dontHaveSiteTransientInDatabase(['my_plugin_site_buffer']);
     * ```
     *
     * @param string $key The name of the transient to delete.
     *
     * @return void
     */
    public function dontHaveSiteTransientInDatabase($key)
    {
        $currentBlogId = $this->blogId;
        $this->useMainBlog();
        $this->dontHaveOptionInDatabase('_site_transient_' . $key);
        $this->useBlog($currentBlogId);
    }

    /**
     * Gets a site option from the database.
     *
     * @example
     * ```php
     * $fooCountOptionId = $I->haveSiteOptionInDatabase('foo_count','23');
     * ```
     *
     * @param string $key The name of the option to read from the database.
     *
     * @return string|mixed The value of the option stored in the database, unserialized if serialized.
     */
    public function grabSiteOptionFromDatabase($key)
    {
        $currentBlogId = $this->blogId;
        $this->useMainBlog();
        $value = $this->grabOptionFromDatabase('_site_option_' . $key);
        $this->useBlog($currentBlogId);

        return $value;
    }

    /**
     * Gets an option value from the database.
     *
     * @example
     * ```php
     * $count = $I->grabOptionFromDatabase('foo_count');
     * ```
     *
     * @param string $option_name The name of the option to grab from the database.
     *
     * @return mixed The option value. If the value is serialized it will be unserialized.
     */
    public function grabOptionFromDatabase($option_name)
    {
        $table = $this->grabPrefixedTableNameFor('options');
        $option_value = $this->grabFromDatabase($table, 'option_value', ['option_name' => $option_name]);

        return empty($option_value) ? '' : $this->maybeUnserialize($option_value);
    }

    /**
     * Unserializes serialized values.
      @since TBD
     *
     * @param mixed $value The value to a
     *
     * @return mixed The unserialized value.
     */
    protected function maybeUnserialize($value)
    {
        $unserialized = @unserialize($value);

        return false === $unserialized ? $value : $unserialized;
    }

    /**
     * Gets a site transient from the database.
     *
     * @example
     * ```php
     * $I->grabSiteTransientFromDatabase('total_comments');
     * $I->grabSiteTransientFromDatabase('api_data');
     * ```
     *
     * @param string $key The site transient to fetch the value for, w/o the `_site_transient_` prefix.
     *
     * @return mixed|string The value of the site transient. If the value is serialized it will be unserialized.
     */
    public function grabSiteTransientFromDatabase($key)
    {
        $currentBlogId = $this->blogId;
        $this->useMainBlog();
        $value = $this->grabOptionFromDatabase('_site_transient_' . $key);
        $this->useBlog($currentBlogId);

        return $value;
    }

    /**
     * Checks that a site option is in the database.
     *
     * @example
     * ```php
     * // Check a transient exists.
     * $I->seeSiteSiteTransientInDatabase('total_counts');
     * // Check a transient exists and has a specific value.
     * $I->seeSiteSiteTransientInDatabase('total_counts', 23);
     * ```
     *
     * @param string     $key The name of the transient to check for, w/o the `_site_transient_` prefix.
     * @param mixed|null $value If provided then the assertion will include the value.
     *
     * @return void
     */
    public function seeSiteSiteTransientInDatabase($key, $value = null)
    {
        $currentBlogId = $this->blogId;
        $criteria = ['option_name' => '_site_transient_' . $key];
        if ($value) {
            $criteria['option_value'] = $value;
        }
        $this->seeOptionInDatabase($criteria);
        $this->useBlog($currentBlogId);
    }

    /**
     * Checks if an option is in the database for the current blog.
     * If checking for an array or an object then the serialized version will be checked for.
     *
     * @example
     * ```php
     * // Checks an option is in the database.
     * $I->seeOptionInDatabase('tables_version');
     * // Checks an option is in the database and has a specific value.
     * $I->seeOptionInDatabase('tables_version', '1.0');
     * ```
     *
     * @param array<string,mixed> $criteria An array of search criteria.
     *
     * @return void
     */
    public function seeOptionInDatabase(array $criteria)
    {
        $tableName = $this->grabPrefixedTableNameFor('options');
        if (!empty($criteria['option_value'])) {
            $criteria['option_value'] = $this->maybeSerialize($criteria['option_value']);
        }
        $this->seeInDatabase($tableName, $criteria);
    }

    /**
     * Checks that a site option is in the database.
     *
     * @example
     * ```php
     * // Check that the option is set in the database.
     * $I->seeSiteOptionInDatabase('foo_count');
     * // Check that the option is set and has a specific value.
     * $I->seeSiteOptionInDatabase('foo_count', 23);
     * ```
     *
     * @param string     $key The name of the otpion to check.
     * @param mixed|null $value If set the assertion will also check the option value.
     *
     * @return void
     */
    public function seeSiteOptionInDatabase($key, $value = null)
    {
        $currentBlogId = $this->blogId;
        $this->useMainBlog();
        $criteria = ['option_name' => '_site_option_' . $key];
        if ($value) {
            $criteria['option_value'] = $value;
        }
        $this->seeOptionInDatabase($criteria);
        $this->useBlog($currentBlogId);
    }

    /**
     * Inserts many posts in the database returning their IDs.
     *
     * @param int   $count     The number of posts to insert.
     * @param array<string,mixed> $overrides {
     *                         An array of values to override the defaults.
     *                         The `{{n}}` placeholder can be used to have the post count inserted in its place;
     *                         e.g. `Post Title - {{n}}` will be set to `Post Title - 0` for the first post,
     *                         `Post Title - 1` for the second one and so on.
     *                         The same applies to meta values as well.
     *
     * @return array<int> An array of the inserted post IDs.
     *
     * @example
     * ```php
     * // Insert 3 random posts.
     * $I->haveManyPostsInDatabase(3);
     * // Insert 3 posts with generated titles.
     * $I->haveManyPostsInDatabase(3, ['post_title' => 'Test post {{n}}']);
     * ```
     *
     */
    public function haveManyPostsInDatabase($count, array $overrides = [])
    {
        if (!is_int($count)) {
            throw new \InvalidArgumentException('Count must be an integer value');
        }
        $overrides = $this->setTemplateData($overrides);
        $ids = [];
        for ($i = 0; $i < $count; $i++) {
            $thisOverrides = $this->replaceNumbersInArray($overrides, $i);
            $ids[] = $this->havePostInDatabase($thisOverrides);
        }

        return $ids;
    }

    /**
     * Sets the template data in the overrides array.
     *
     * @param array<string,mixed> $overrides An array of overrides to apply the template data to.
     *
     * @return array<string,mixed> The array of overrides with the template data replaced.
     */
    protected function setTemplateData(array $overrides = [])
    {
        if (empty($overrides['template_data'])) {
            $this->templateData = [];
        } else {
            $this->templateData = $overrides['template_data'];
            $overrides = array_diff_key($overrides, ['template_data' => []]);
        }

        return $overrides;
    }

    /**
     * Replaces each occurrence of the `{{n}}` placeholder with the specified number.
     *
     * @param string|array<string|array> $input The entry, or entries, to replace the placeholder in.
     * @param int   $i     The value to replace the placeholder with.
     *
     * @return array<int|string,mixed> The input array with any `{{n}}` placeholder replaced with a number.
     */
    protected function replaceNumbersInArray($input, $i)
    {
        $out = [];
        foreach ((array)$input as $key => $value) {
            if (is_array($value)) {
                $out[$this->replaceNumbersInString($key, $i)] = $this->replaceNumbersInArray($value, $i);
            } else {
                $out[$this->replaceNumbersInString($key, $i)] = $this->replaceNumbersInString($value, $i);
            }
        }

        return $out;
    }

    /**
     * Replaces the `{{n}}` placeholder with the specified number.
     *
     * @param string $template The string to replace the placeholder in.
     * @param int    $i        The value to replace the placeholder with.
     *
     * @return string The string with replaces placeholders.
     */
    protected function replaceNumbersInString($template, $i)
    {
        if (! is_string($template)) {
            return $template;
        }

        $fnArgs = [ 'n' => $i ];
        $data   = array_merge($this->templateData, $fnArgs);

        return renderString($template, $data, $fnArgs);
    }

    /**
     * Checks for a term in the database.
     * Looks up the `terms` and `term_taxonomy` prefixed tables.
     *
     * @param array<string,mixed> $criteria An array of criteria to search for the term, can be columns from the `terms`
     *                                      and the `term_taxonomy` tables.
     *
     * @return void
     * @example
     * ```php
     * $I->seeTermInDatabase(['slug' => 'genre--fiction']);
     * $I->seeTermInDatabase(['name' => 'Fiction', 'slug' => 'genre--fiction']);
     * ```
     *
     */
    public function seeTermInDatabase(array $criteria)
    {
        $termsCriteria = array_intersect_key($criteria, array_flip($this->termKeys));
        $termTaxonomyCriteria = array_intersect_key($criteria, array_flip($this->termTaxonomyKeys));

        if (!empty($termsCriteria)) {
            // this one fails... go to...
            $this->seeInDatabase($this->grabTermsTableName(), $termsCriteria);
        }
        if (!empty($termTaxonomyCriteria)) {
            $this->seeInDatabase($this->grabTermTaxonomyTableName(), $termTaxonomyCriteria);
        }
    }

    /**
     * Removes a term from the database.
     *
     * @example
     * ```php
     * $I->dontHaveTermInDatabase(['name' => 'romance']);
     * $I->dontHaveTermInDatabase(['slug' => 'genre--romance']);
     * ```
     *
     * @param array<string,mixed> $criteria  An array of search criteria.
     * @param bool  $purgeMeta Whether the terms meta should be purged along side with the meta or not.
     *
     * @return void
     */
    public function dontHaveTermInDatabase(array $criteria, $purgeMeta = true)
    {
        $termRelationshipsKeys = ['term_taxonomy_id'];

        $termTableCriteria = array_intersect_key($criteria, array_flip($this->termKeys));
        $termTaxonomyTableCriteria = array_intersect_key($criteria, array_flip($this->termTaxonomyKeys));

        if ($purgeMeta) {
            $ids = false;

            if (!empty($termTableCriteria)) {
                $ids = $this->grabAllFromDatabase($this->grabTermsTableName(), 'term_id', $criteria);
            } elseif (!empty($termTaxonomyTableCriteria)) {
                $ids = $this->grabAllFromDatabase($this->grabTermTaxonomyTableName(), 'term_id', $criteria);
            }

            if (!empty($ids)) {
                foreach ($ids as $id) {
                    $this->dontHaveTermMetaInDatabase($id);
                }
            }
        }

        $this->dontHaveInDatabase($this->grabTermsTableName(), $termTableCriteria);
        $this->dontHaveInDatabase($this->grabTermTaxonomyTableName(), $termTaxonomyTableCriteria);
        $this->dontHaveInDatabase(
            $this->grabTermRelationshipsTableName(),
            array_intersect_key($criteria, array_flip($termRelationshipsKeys))
        );
    }

    /**
     * Removes a term meta from the database.
     *
     * @example
     * ```php
     * // Remove the "karma" key.
     * $I->dontHaveTermMetaInDatabase(['term_id' => $termId, 'meta_key' => 'karma']);
     * // Remove all meta for the term.
     * $I->dontHaveTermMetaInDatabase(['term_id' => $termId]);
     * ```
     *
     * @param array<string,mixed> $criteria An array of search criteria.
     *
     * @return void
     */
    public function dontHaveTermMetaInDatabase(array $criteria)
    {
        $this->dontHaveInDatabase($this->grabTermMetaTableName(), $criteria);
    }

    /**
     * Makes sure a term is not in the database.
     *
     * Looks up both the `terms` table and the `term_taxonomy` tables.
     *
     * @param array<string,mixed> $criteria An array of criteria to search for the term, can be columns from the `terms`
     *                                      and the `term_taxonomy` tables.
     *
     * @return void
     *
     * @example
     * ```php
     * // Asserts a 'fiction' term is not in the database.
     * $I->dontSeeTermInDatabase(['name' => 'fiction']);
     * // Asserts a 'fiction' term with slug 'genre--fiction' is not in the database.
     * $I->dontSeeTermInDatabase(['name' => 'fiction', 'slug' => 'genre--fiction']);
     * ```
     */
    public function dontSeeTermInDatabase(array $criteria)
    {
        $termsCriteria = array_intersect_key($criteria, array_flip($this->termKeys));
        $termTaxonomyCriteria = array_intersect_key($criteria, array_flip($this->termTaxonomyKeys));

        if (!empty($termsCriteria)) {
            // this one fails... go to...
            $this->dontSeeInDatabase($this->grabTermsTableName(), $termsCriteria);
        }
        if (!empty($termTaxonomyCriteria)) {
            $this->dontSeeInDatabase($this->grabTermTaxonomyTableName(), $termTaxonomyCriteria);
        }
    }

    /**
     * Inserts many comments in the database.
     *
     *
     * @example
     * ```php
     * // Insert 3 random comments for a post.
     * $I->haveManyCommentsInDatabase(3, $postId);
     * // Insert 3 random comments for a post.
     * $I->haveManyCommentsInDatabase(3, $postId, ['comment_content' => 'Comment {{n}}']);
     * ```
     *
     * @param int   $count           The number of comments to insert.
     * @param   int $comment_post_ID The comment parent post ID.
     * @param array<string,mixed> $overrides       An associative array to override the defaults.
     *
     * @return array<int> An array containing the inserted comments IDs.
     */
    public function haveManyCommentsInDatabase($count, $comment_post_ID, array $overrides = [])
    {
        if (!is_int($count)) {
            throw new \InvalidArgumentException('Count must be an integer value');
        }
        $overrides = $this->setTemplateData($overrides);
        $ids = [];
        for ($i = 0; $i < $count; $i++) {
            $thisOverrides = $this->replaceNumbersInArray($overrides, $i);
            $ids[] = $this->haveCommentInDatabase($comment_post_ID, $thisOverrides);
        }

        return $ids;
    }

    /**
     * Inserts a comment in the database.
     *
     * @example
     * ```php
     * $I->haveCommentInDatabase($postId, ['comment_content' => 'Test Comment', 'comment_karma' => 23]);
     * ```
     *
     * @param  int   $comment_post_ID The id of the post the comment refers to.
     * @param  array<int|string,mixed> $data            The comment data overriding default and random generated values.
     *
     * @return int The inserted comment `comment_id`.
     */
    public function haveCommentInDatabase($comment_post_ID, array $data = [])
    {
        if (!is_int($comment_post_ID)) {
            throw new \BadMethodCallException('Comment post ID must be int');
        }

        $has_meta = !empty($data['meta']);
        $meta = [];
        if ($has_meta) {
            $meta = $data['meta'];
            unset($data['meta']);
        }

        $comment = Comment::makeComment($comment_post_ID, $data);

        $commentsTableName = $this->grabPrefixedTableNameFor('comments');
        $commentId = $this->haveInDatabase($commentsTableName, $comment);

        if ($has_meta) {
            foreach ($meta as $key => $value) {
                $this->haveCommentMetaInDatabase($commentId, $key, $value);
            }
        }

        if ($comment['comment_approved']) {
            $commentCount = $this->countInDatabase(
                $commentsTableName,
                [
                    'comment_approved' => '1',
                    'comment_post_ID' => $comment_post_ID,
                ]
            );

            $postsTableName = $this->grabPostsTableName();
            $this->updateInDatabase(
                $postsTableName,
                ['comment_count' => $commentCount],
                ['ID' => $comment_post_ID]
            );
        }

        return $commentId;
    }

    /**
     * Inserts a comment meta field in the database.
     * Array and object meta values will be serialized.
     *
     * @example
     * ```php
     * $I->haveCommentMetaInDatabase($commentId, 'api_ID', 23);
     * // The value will be serialized.
     * $apiData = ['ID' => 23, 'user' => 89, 'origin' => 'twitter'];
     * $I->haveCommentMetaInDatabase($commentId, 'api_data', $apiData);
     * ```
     *
     * @param int    $comment_id The ID of the comment to insert the meta for.
     * @param string $meta_key The key of the comment meta to insert.
     * @param mixed  $meta_value The value of the meta to insert, if serializable it will be serialized.
     *
     * @return int The inserted comment meta ID.
     */
    public function haveCommentMetaInDatabase($comment_id, $meta_key, $meta_value)
    {
        if (!is_int($comment_id)) {
            throw new \BadMethodCallException('Comment id must be an int');
        }
        if (!is_string($meta_key)) {
            throw new \BadMethodCallException('Meta key must be an string');
        }

        return $this->haveInDatabase($this->grabCommentmetaTableName(), [
            'comment_id' => $comment_id,
            'meta_key' => $meta_key,
            'meta_value' => $this->maybeSerialize($meta_value),
        ]);
    }

    /**
     * Returns the prefixed comment meta table name.
     *
     * @example
     * ```php
     * // Get all the values of 'karma' for all comments.
     * $commentMeta = $I->grabCommentmetaTableName();
     * $I->grabAllFromDatabase($commentMeta, 'meta_value', ['meta_key' => 'karma']);
     * ```
     *
     * @return string The complete name of the comment meta table name, including the table prefix.
     */
    public function grabCommentmetaTableName()
    {
        return $this->grabPrefixedTableNameFor('commentmeta');
    }

    /**
     * Returns the number of table rows matching a criteria.
     *
     * @example
     * ```php
     * $I->haveManyPostsInDatabase(3, ['post_status' => 'draft' ]);
     * $I->haveManyPostsInDatabase(3, ['post_status' => 'private' ]);
     * // Make sure there are now the expected number of draft posts.
     * $postsTable = $I->grabPostsTableName();
     * $draftsCount = $I->countRowsInDatabase($postsTable, ['post_status' => 'draft']);
     * ```
     *
     * @param string $table    The table to count the rows in.
     * @param array<string,mixed>  $criteria Search criteria, if empty all table rows will be counted.
     *
     * @return int The number of table rows matching the search criteria.
     */
    public function countRowsInDatabase($table, array $criteria = [])
    {
        return parent::countInDatabase($table, $criteria);
    }

    /**
     * Removes an entry from the comments table.
     *
     * @example
     * ```php
     * $I->dontHaveCommentInDatabase(['comment_post_ID' => 23, 'comment_url' => 'http://example.copm']);
     * ```
     *
     * @param  array<string,mixed> $criteria  An array of search criteria.
     * @param bool $purgeMeta If set to `true` then the meta for the comment will be purged too.
     *
     * @return void
     *
     * @throws \Exception In case of incoherent query criteria.
     */
    public function dontHaveCommentInDatabase(array $criteria, $purgeMeta = true)
    {
        $table = $this->grabCommentsTableName();
        if ($purgeMeta) {
            $ids = $this->grabAllFromDatabase($table, 'comment_id', $criteria);
            if (!empty($ids)) {
                foreach ($ids as $id) {
                    $this->dontHaveCommentMetaInDatabase($id);
                }
            }
        }

        $this->dontHaveInDatabase($table, $criteria);
    }

    /**
     * Gets the comments table name.
     *
     * @example
     * ```php
     * // Will be `wp_comments`.
     * $comments = $I->grabCommentsTableName();
     * // Will be `wp_23_comments`.
     * $I->useBlog(23);
     * $comments = $I->grabCommentsTableName();
     * ```
     *
     * @return string The prefixed table name, e.g. `wp_comments`.
     */
    public function grabCommentsTableName()
    {
        return $this->grabPrefixedTableNameFor('comments');
    }

    /**
     * Removes a post comment meta from the database
     *
     * @example
     * ```php
     * // Remove all meta for the comment with an ID of 23.
     * $I->dontHaveCommentMetaInDatabase(['comment_id' => 23]);
     * // Remove the `count` comment meta for the comment with an ID of 23.
     * $I->dontHaveCommentMetaInDatabase(['comment_id' => 23, 'meta_key' => 'count']);
     * ```
     *
     * @param  array<string,mixed> $criteria An array of search criteria.
     *
     * @return void
     */
    public function dontHaveCommentMetaInDatabase(array $criteria)
    {
        $tableName = $this->grabPrefixedTableNameFor('commentmeta');
        $this->dontHaveInDatabase($tableName, $criteria);
    }

    /**
     * Inserts many links in the database `links` table.
     *
     * @example
     * ```php
     * // Insert 3 randomly generated links in the database.
     * $linkIds = $I->haveManyLinksInDatabase(3);
     * // Inserts links in the database replacing the `n` placeholder.
     * $linkIds = $I->haveManyLinksInDatabase(3, ['link_url' => 'http://example.org/test-{{n}}']);
     * ```
     *
     * @param int $count The number of links to insert.
     * @param array<string,mixed> $overrides Overrides for the default arguments.
     *
     * @return array<int> An array of inserted `link_id`s.
     */
    public function haveManyLinksInDatabase($count, array $overrides = [])
    {
        if (!is_int($count)) {
            throw new \InvalidArgumentException('Count must be an integer value');
        }
        $overrides = $this->setTemplateData($overrides);
        $ids = [];
        for ($i = 0; $i < $count; $i++) {
            $thisOverrides = $this->replaceNumbersInArray($overrides, $i);
            $ids[] = $this->haveLinkInDatabase($thisOverrides);
        }

        return $ids;
    }

    /**
     * Inserts a link in the database.
     *
     * @example
     * ```php
     * $linkId = $I->haveLinkInDatabase(['link_url' => 'http://example.org']);
     * ```
     *
     * @param  array<int|string,mixed> $overrides The data to insert.
     *
     * @return int The inserted link `link_id`.
     */
    public function haveLinkInDatabase(array $overrides = [])
    {
        $tableName = $this->grabLinksTableName();
        $defaults = Links::getDefaults();
        $overrides = array_merge($defaults, array_intersect_key($overrides, $defaults));

        return $this->haveInDatabase($tableName, $overrides);
    }

    /**
     * Returns the prefixed links table name.
     *
     * @example
     * ```php
     * // Given a `wp_` table prefix returns `wp_links`.
     * $linksTable = $I->grabLinksTableName();
     * // Given a `wp_` table prefix returns `wp_23_links`.
     * $I->useBlog(23);
     * $linksTable = $I->grabLinksTableName();
     * ```
     *
     * @return string The links table including the blog-aware table prefix.
     */
    public function grabLinksTableName()
    {
        return $this->grabPrefixedTableNameFor('links');
    }

    /**
     * Inserts many users in the database.
     *
     * @example
     * ```php
     * $subscribers = $I->haveManyUsersInDatabase(5, 'user-{{n}}');
     * $editors = $I->haveManyUsersInDatabase(
     *      5,
     *      'user-{{n}}',
     *      'editor',
     *      ['user_email' => 'user-{{n}}@example.org']
     * );
     * ```
     *
     * @param int    $count      The number of users to insert.
     * @param string $user_login The user login name.
     * @param string $role       The user role.
     * @param array<string,mixed>  $overrides  An array of values to override the default ones.
     *
     * @return array<int> An array of user IDs.
     */
    public function haveManyUsersInDatabase($count, $user_login, $role = 'subscriber', array $overrides = [])
    {
        if (!is_int($count)) {
            throw new \InvalidArgumentException('Count must be an integer value');
        }
        $ids = [];
        $overrides = $this->setTemplateData($overrides);
        for ($i = 0; $i < $count; $i++) {
            $thisOverrides = $this->replaceNumbersInArray($overrides, $i);
            $thisUserLogin = false === strpos(
                $user_login,
                $this->numberPlaceholder
            ) ? $user_login . '_' . $i : $this->replaceNumbersInString($user_login, $i);
            $ids[] = $this->haveUserInDatabase($thisUserLogin, $role, $thisOverrides);
        }

        return $ids;
    }

    /**
     * Inserts a user and its meta in the database.
     *
     * @param string               $user_login The user login name.
     * @param string|array<string> $role       The user role slug(s), e.g. `administrator` or `['author', 'editor']`;
     *                                         defaults to `subscriber`. If more than one role is specified, then the
     *                                         first role in the list will be the user primary role and the
     *                                         `wp_user_level` will be set to that role.
     * @param array<int|string,mixed> $overrides An associative array of column names and values overriding defaults
     *                                           in the `users` and `usermeta` table.
     *
     * @return int The inserted user ID.
     *
     * @example
     * ```php
     * // Create an editor user in blog 1 w/ specific email.
     * $userId = $I->haveUserInDatabase('luca', 'editor', ['user_email' => 'luca@example.org']);
     *
     * // Create a subscriber user in blog 1.
     * $subscriberId = $I->haveUserInDatabase('subscriber');
     *
     * // Create a user editor in blog 1, author in blog 2, administrator in blog 3.
     * $userWithMeta = $I->haveUserInDatabase('luca',
     *      [
     *          1 => 'editor',
     *          2 => 'author',
     *          3 => 'administrator'
     *      ], [
     *          'user_email' => 'luca@example.org'
     *          'meta' => ['a meta_key' => 'a_meta_value']
     *      ]
     * );
     *
     * // Create editor in blog 1 w/ `edit_themes` cap, author in blog 2, admin in blog 3 w/o `manage_options` cap.
     * $userWithMeta = $I->haveUserInDatabase('luca',
     *      [
     *          1 => ['editor', 'edit_themes'],
     *          2 => 'author',
     *          3 => ['administrator' => true, 'manage_options' => false]
     *      ]
     * );
     *
     * // Create a user w/o role.
     * $userId = $I->haveUserInDatabase('luca', '');
     * ```
     *
     * @see WPDb::haveUserCapabilitiesInDatabase() for the roles and caps options.
     */
    public function haveUserInDatabase($user_login, $role = 'subscriber', array $overrides = [])
    {
        // Support `meta` and `meta_input` for compatibility w/ format used by Core user factory.
        $hasMeta = !empty($overrides['meta']) || !empty($overrides['meta_input']);
        $meta = [];
        if ($hasMeta) {
            $meta = isset($overrides['meta']) ? $overrides['meta'] : $overrides['meta_input'];
            unset($overrides['meta'], $overrides['meta_input']);
        }

        $userTableData = User::generateUserTableDataFrom($user_login, $overrides);
        $this->debugSection('Generated users table data', json_encode($userTableData));
        $userId = $this->haveInDatabase($this->grabUsersTableName(), $userTableData);

        // Handle the user capabilities and associated meta values.
        $this->haveUserCapabilitiesInDatabase($userId, $role);

        // Set up the user meta, apply the user-set overrides.
        $userMetaTableDataFrom = User::generateUserMetaTableDataFrom($user_login, $meta);
        foreach ($userMetaTableDataFrom as $key => $value) {
            $this->haveUserMetaInDatabase($userId, $key, $value);
        }

        return $userId;
    }

    /**
     * Returns the prefixed users table name.
     *
     * @example
     * ```php
     * // Given a `wp_` table prefix returns `wp_users`.
     * $usersTable = $I->getUsersTableName();
     * // Given a `wp_` table prefix returns `wp_users`.
     * $I->useBlog(23);
     * $usersTable = $I->getUsersTableName();
     * ```
     *
     * @return string The users table including the table prefix.
     * @deprecated Use `grabUsersTableName`.
     */
    public function getUsersTableName()
    {
        return $this->grabUsersTableName();
    }

    /**
     * Returns the prefixed users table name.
     *
     * @example
     * ```php
     * // Given a `wp_` table prefix returns `wp_users`.
     * $usersTable = $I->grabUsersTableName();
     * // Given a `wp_` table prefix returns `wp_users`.
     * $I->useBlog(23);
     * $usersTable = $I->grabUsersTableName();
     * ```
     *
     * @return string The users table including the table prefix.
     */
    public function grabUsersTableName()
    {
        return $this->grabPrefixedTableNameFor('users');
    }

    /**
     * Sets a user capabilities in the database.
     *
     * @example
     * ```php
     * // Assign one user a role in a blog.
     * $blogId = $I->haveBlogInDatabase('test');
     * $editor = $I->haveUserInDatabase('luca', 'editor');
     * $capsIds = $I->haveUserCapabilitiesInDatabase($editor, [$blogId => 'editor']);
     *
     * // Assign a user two roles in blog 1.
     * $capsIds = $I->haveUserCapabilitiesInDatabase($userId, ['editor', 'subscriber']);
     *
     * // Assign one user different roles in different blogs.
     * $capsIds = $I->haveUserCapabilitiesInDatabase($userId, [$blogId1 => 'editor', $blogId2 => 'author']);
     *
     * // Assign a user a role and an additional capability in blog 1.
     * $I->haveUserCapabilitiesInDatabase($userId, ['editor' => true, 'edit_themes' => true]);
     *
     * // Assign a user a mix of roles and capabilities in different blogs.
     * $capsIds = $I->haveUserCapabilitiesInDatabase(
     *      $userId,
     *      [
     *          $blogId1 => ['editor' => true, 'edit_themes' => true],
     *          $blogId2 => ['administrator' => true, 'edit_themes' => false]
     *      ]
     * );
     * ```
     *
     * @param int                                        $userId The ID of the user to set the capabilities of.
     * @param string|array<string|bool>|array<int,array> $role   Either a role string (e.g. `administrator`),an
     *                                                           associative array of blog IDs/roles for a multisite
     *                                                           installation (e.g. `[1 => 'administrator`, 2 =>
     *                                                           'subscriber']`).
     *
     * @return array<int|string,array<int>|int> An array of inserted `meta_id`.
     */
    public function haveUserCapabilitiesInDatabase($userId, $role)
    {
        $insert = User::buildCapabilities($role);

        $roleIds = [];
        foreach ($insert as $meta_key => $meta_value) {
            // Delete pre-existing values, if any.
            $this->dontHaveUserMetaInDatabase(['user_id' => $userId, 'meta_key' => $meta_key]);
            $roleIds[] = $this->haveUserMetaInDatabase($userId, $meta_key, serialize($meta_value));
        }

        $levelIds = $this->haveUserLevelsInDatabase($userId, $role);

        return array_merge($roleIds, $levelIds);
    }

    /**
     * Sets a user meta in the database.
     *
     * @example
     * ```php
     * $userId = $I->haveUserInDatabase('luca', 'editor');
     * $I->haveUserMetaInDatabase($userId, 'karma', 23);
     * ```
     *
     * @param int    $userId The user ID.
     * @param string $meta_key The meta key to set the value for.
     * @param mixed  $meta_value Either a single value or an array of values; objects will be serialized while array of
     *                           values will trigger the insertion of multiple rows.
     *
     * @return array<int> An array of inserted `umeta_id`s.
     */
    public function haveUserMetaInDatabase($userId, $meta_key, $meta_value)
    {
        $ids = [];
        $meta_values = is_array($meta_value) ? $meta_value : [$meta_value];

        foreach ($meta_values as $value) {
            $data = [
                'user_id' => $userId,
                'meta_key' => $meta_key,
                'meta_value' => $this->maybeSerialize($value),
            ];
            $ids[] = $this->haveInDatabase($this->grabUsermetaTableName(), $data);
        }

        return $ids;
    }

    /**
     * Returns the prefixed users meta table name.
     *
     * @example
     * ```php
     * // Given a `wp_` table prefix returns `wp_usermeta`.
     * $usermetaTable = $I->grabUsermetaTableName();
     * // Given a `wp_` table prefix returns `wp_usermeta`.
     * $I->useBlog(23);
     * $usermetaTable = $I->grabUsermetaTableName();
     * ```
     *
     * @return string The user meta table name.
     */
    public function grabUsermetaTableName()
    {
        $usermetaTable = $this->grabPrefixedTableNameFor('usermeta');

        return $usermetaTable;
    }

    /**
     * Sets the user access level meta in the database for a user.
     *
     * @param int                             $userId The ID of the user to set the level for.
     * @param array<array|bool|string>|string $role   Either a role string (e.g. `administrator`) or an array of blog
     *                                                IDs/roles for a multisite installation
     *                                                (e.g. `[1 => 'administrator`, 2 => 'subscriber']`).
     *
     * @return array<int> An array of inserted `meta_id`.
     * @example
     * ```php
     * $userId = $I->haveUserInDatabase('luca', 'editor');
     * $moreThanAnEditorLessThanAnAdmin = 8;
     * $I->haveUserLevelsInDatabase($userId, $moreThanAnEditorLessThanAnAdmin);
     * ```
     *
     */
    public function haveUserLevelsInDatabase($userId, $role)
    {
        $roles = User::buildCapabilities($role);

        $ids = [];
        foreach ($roles as $roleMetaKey => $roleMetaValue) {
            $levelMetaKey = preg_replace('/capabilities$/', 'user_level', $roleMetaKey);
            if ($levelMetaKey === null) {
                $levelMetaKey = $this->grabTablePrefix() . 'user_level';
            }
            $this->dontHaveUserMetaInDatabase(['user_id' => $userId, 'meta_key' => $levelMetaKey]);
            $blogRoles = array_keys((array)$roleMetaValue);
            $blogPrimaryRole = reset($blogRoles);
            $level = $blogPrimaryRole ? User::getLevelForRole($blogPrimaryRole) : 0;
            $ids[] = $this->haveUserMetaInDatabase($userId, $levelMetaKey, $level);
        }

        return array_merge(...$ids);
    }

    /**
     * Inserts many terms in the database.
     *
     * @example
     * ```php
     * $terms = $I->haveManyTermsInDatabase(3, 'genre-{{n}}', 'genre');
     * $termIds = array_column($terms, 0);
     * $termTaxonomyIds = array_column($terms, 1);
     * ```
     *
     * @param       int    $count     The number of terms to insert.
     * @param       string $name      The term name template, can include the `{{n}}` placeholder.
     * @param       string $taxonomy  The taxonomy to insert the terms for.
     * @param array<string,mixed>        $overrides An associative array of default overrides.
     *
     * @return array<array<int>> An array of arrays containing `term_id` and `term_taxonomy_id` of the inserted terms.
     */
    public function haveManyTermsInDatabase($count, $name, $taxonomy, array $overrides = [])
    {
        if (!is_int($count)) {
            throw new \InvalidArgumentException('Count must be an integer value');
        }
        $ids = [];
        $overrides = $this->setTemplateData($overrides);
        for ($i = 0; $i < $count; $i++) {
            $thisName = false === strpos(
                $name,
                $this->numberPlaceholder
            ) ? $name . ' ' . $i : $this->replaceNumbersInString($name, $i);
            $thisTaxonomy = $this->replaceNumbersInString($taxonomy, $i);
            $thisOverrides = $this->replaceNumbersInArray($overrides, $i);
            $ids[] = $this->haveTermInDatabase($thisName, $thisTaxonomy, $thisOverrides);
        }

        return $ids;
    }

    /**
     * Checks for a taxonomy taxonomy in the database.
     *
     * @example
     * ```php
     * list($termId, $termTaxonomyId) = $I->haveTermInDatabase('fiction', 'genre');
     * $I->seeTermTaxonomyInDatabase(['term_id' => $termId, 'taxonomy' => 'genre']);
     * ```
     *
     * @param array<string,mixed> $criteria An array of search criteria.
     *
     * @return void
     */
    public function seeTermTaxonomyInDatabase(array $criteria)
    {
        $this->seeInDatabase($this->grabTermTaxonomyTableName(), $criteria);
    }

    /**
     * Checks that a term taxonomy is not in the database.
     *
     * @example
     * ```php
     * list($termId, $termTaxonomyId) = $I->haveTermInDatabase('fiction', 'genre');
     * $I->dontSeeTermTaxonomyInDatabase(['term_id' => $termId, 'taxonomy' => 'country']);
     * ```
     *
     * @param array<string,mixed> $criteria An array of search criteria.
     *
     * @return void
     */
    public function dontSeeTermTaxonomyInDatabase(array $criteria)
    {
        $this->dontSeeInDatabase($this->grabTermTaxonomyTableName(), $criteria);
    }

    /**
     * Checks for a term meta in the database.
     *
     * @example
     * ```php
     * list($termId, $termTaxonomyId) = $I->haveTermInDatabase('fiction', 'genre');
     * $I->haveTermMetaInDatabase($termId, 'rating', 4);
     * $I->seeTermMetaInDatabase(['term_id' => $termId,'meta_key' => 'rating', 'meta_value' => 4]);
     * ```
     *
     * @param array<string,mixed> $criteria An array of search criteria.
     *
     * @return void
     */
    public function seeTermMetaInDatabase(array $criteria)
    {
        if (!empty($criteria['meta_value'])) {
            $criteria['meta_value'] = $this->maybeSerialize($criteria['meta_value']);
        }
        $this->seeInDatabase($this->grabTermMetaTableName(), $criteria);
    }

    /**
     * Checks that a term meta is not in the database.
     *
     * @example
     * ```php
     * list($termId, $termTaxonomyId) = $I->haveTermInDatabase('fiction', 'genre');
     * $I->haveTermMetaInDatabase($termId, 'rating', 4);
     * $I->dontSeeTermMetaInDatabase(['term_id' => $termId,'meta_key' => 'average_review']);
     * ```
     *
     * @param array<string,mixed> $criteria An array of search criteria.
     *
     * @return void
     */
    public function dontSeeTermMetaInDatabase(array $criteria)
    {
        if (!empty($criteria['meta_value'])) {
            $criteria['meta_value'] = $this->maybeSerialize($criteria['meta_value']);
        }
        $this->dontSeeInDatabase($this->grabTermMetaTableName(), $criteria);
    }

    /**
     * Checks that a table is in the database.
     *
     * @example
     * ```php
     * $options = $I->grabPrefixedTableNameFor('options');
     * $I->seeTableInDatabase($options);
     * ```
     *
     * @param string $table The full table name, including the table prefix.
     *
     * @return void
     */
    public function seeTableInDatabase($table)
    {
        $count = $this->_seeTableInDatabase($table);

        $this->assertTrue($count > 0, "No matching tables found for table '" . $table . "' in database.");
    }

    /**
     * Asserts a table exists in the database.
     *
     * @param string $table The table to look for.
     *
     * @return bool Whether the table exists in the database or not.
     */
    protected function _seeTableInDatabase($table)
    {
        $dbh = $this->_getDbh();
        $sth = $dbh->prepare('SHOW TABLES LIKE :table');
        $this->debugSection('Query', $sth->queryString);
        $sth->execute(['table' => $table]);
        $count = $sth->rowCount();

        return $count == 1;
    }

    /**
     * Gets the prefixed `blog_versions` table name.
     *
     * @example
     * ```php
     * // Assuming a `wp_` table prefix it will return `wp_blog_versions`.
     * $blogVersionsTable = $I->grabBlogVersionsTableName();
     * $I->useBlog(23);
     * // Assuming a `wp_` table prefix it will return `wp_blog_versions`.
     * $blogVersionsTable = $I->grabBlogVersionsTableName();
     * ```
     *
     * @return string The blogs versions table name including the table prefix.
     */
    public function grabBlogVersionsTableName()
    {
        return $this->grabPrefixedTableNameFor('blog_versions');
    }

    /**
     * Gets the prefixed `sitemeta` table name.
     *
     * @example
     * ```php
     * // Assuming a `wp_` table prefix it will return `wp_sitemeta`.
     * $blogVersionsTable = $I->grabSiteMetaTableName();
     * $I->useBlog(23);
     * // Assuming a `wp_` table prefix it will return `wp_sitemeta`.
     * $blogVersionsTable = $I->grabSiteMetaTableName();
     * ```
     *
     * @return string The site meta table name including the table prefix.
     */
    public function grabSiteMetaTableName()
    {
        return $this->grabPrefixedTableNameFor('sitemeta');
    }

    /**
     * Gets the prefixed `signups` table name.
     *
     * @example
     * ```php
     * // Assuming a `wp_` table prefix it will return `wp_signups`.
     * $blogVersionsTable = $I->grabSignupsTableName();
     * $I->useBlog(23);
     * // Assuming a `wp_` table prefix it will return `wp_signups`.
     * $blogVersionsTable = $I->grabSignupsTableName();
     * ```
     *
     * @return string The signups table name including the table prefix.
     */
    public function grabSignupsTableName()
    {
        return $this->grabPrefixedTableNameFor('signups');
    }

    /**
     * Gets the prefixed `registration_log` table name.
     *
     * @example
     * ```php
     * // Assuming a `wp_` table prefix it will return `wp_registration_log`.
     * $blogVersionsTable = $I->grabRegistrationLogTableName();
     * $I->useBlog(23);
     * // Assuming a `wp_` table prefix it will return `wp_registration_log`.
     * $blogVersionsTable = $I->grabRegistrationLogTableName();
     * ```
     *
     * @return string The registration log table name including the table prefix.
     */
    public function grabRegistrationLogTableName()
    {
        return $this->grabPrefixedTableNameFor('registration_log');
    }

    /**
     * Gets the prefixed `site` table name.
     *
     * @example
     * ```php
     * // Assuming a `wp_` table prefix it will return `wp_site`.
     * $blogVersionsTable = $I->grabSiteTableName();
     * $I->useBlog(23);
     * // Assuming a `wp_` table prefix it will return `wp_site`.
     * $blogVersionsTable = $I->grabSiteTableName();
     * ```
     *
     * @return string The site table name including the table prefix.
     */
    public function grabSiteTableName()
    {
        return $this->grabPrefixedTableNameFor('site');
    }

    /**
     * Checks for a blog in the `blogs` table.
     *
     * @example
     * ```php
     * // Search for a blog by `blog_id`.
     * $I->seeBlogInDatabase(['blog_id' => 23]);
     * // Search for all blogs on a path.
     * $I->seeBlogInDatabase(['path' => '/sub-path/']);
     * ```
     *
     * @param array<string,mixed> $criteria An array of search criteria.
     *
     * @return void
     */
    public function seeBlogInDatabase(array $criteria)
    {
        $this->seeInDatabase($this->grabBlogsTableName(), $this->prepareBlogCriteria($criteria));
    }

    /**
     * Gets the prefixed `blogs` table name.
     *
     * @example
     * ```php
     * // Assuming a `wp_` table prefix it will return `wp_blogs`.
     * $blogVersionsTable = $I->grabBlogsTableName();
     * $I->useBlog(23);
     * // Assuming a `wp_` table prefix it will return `wp_blogs`.
     * $blogVersionsTable = $I->grabBlogsTableName();
     * ```
     *
     * @return string The blogs table name including the table prefix.
     */
    public function grabBlogsTableName()
    {
        return $this->grabPrefixedTableNameFor('blogs');
    }

    /**
     * Prepares the array of criteria that will be used to search a blog in the database.
     *
     * @param array<string,mixed> $criteria An input array of blog search criteria.
     *
     * @return array<string,mixed> The prepared array of blog search criteria.
     */
    protected function prepareBlogCriteria(array $criteria)
    {
        // Allow using the non leading/trailing slash format to search for sub-domains.
        if (isset($criteria['path']) && $criteria['path'] !== '/') {
            $criteria['path'] = '/' . trim($criteria['path'], '/') . '/';
        }
        return $criteria;
    }

    /**
     * Inserts many blogs in the database.
     *
     * @param int                 $count     The number of blogs to create.
     * @param array<string,mixed> $overrides An array of values to override the default ones; `{{n}}` will be replaced
     *                                       by the count.
     * @param bool                $subdomain Whether the new blogs should be created as a subdomain or subfolder.
     *
     * @return array<int> An array of inserted blogs `blog_id`s.
     * @example
     *      ```php
     *      $blogIds = $I->haveManyBlogsInDatabase(3, ['domain' =>'test-{{n}}']);
     *      foreach($blogIds as $blogId){
     *      $I->useBlog($blogId);
     *      $I->haveManuPostsInDatabase(3);
     * }
     * ```
     *
     */
    public function haveManyBlogsInDatabase($count, array $overrides = [], $subdomain = true)
    {
        $blogIds = [];
        $overrides = $this->setTemplateData($overrides);
        for ($i = 0; $i < $count; $i++) {
            $blogOverrides = $this->replaceNumbersInArray($overrides, $i);
            $domainOrPath = 'blog-' . $i;

            if (isset($blogOverrides['slug'])) {
                $domainOrPath = (string)$blogOverrides['slug'];
                unset($blogOverrides['slug']);
            }

            $blogIds[] = $this->haveBlogInDatabase($domainOrPath, $blogOverrides, $subdomain);
        }

        return $blogIds;
    }

    /**
     * Inserts a blog in the `blogs` table.
     *
     * @example
     * ```php
     * // Create the `test` subdomain blog.
     * $blogId = $I->haveBlogInDatabase('test', ['administrator' => $userId]);
     * // Create the `/test` subfolder blog.
     * $blogId = $I->haveBlogInDatabase('test', ['administrator' => $userId], false);
     * ```
     *
     * @param  string $domainOrPath     The subdomain or the path to the be used for the blog.
     * @param array<int|string,mixed>   $overrides        An array of values to override the defaults.
     * @param bool    $subdomain        Whether the new blog should be created as a subdomain (`true`)
     *                                  or subfolder (`true`)
     *
     * @return int The inserted blog `blog_id`.
     */
    public function haveBlogInDatabase($domainOrPath, array $overrides = [], $subdomain = true)
    {
        $base = Blog::makeDefaults();
        if ($subdomain) {
            $base['domain'] = false !== strpos($domainOrPath, $this->getSiteDomain())
                ? $domainOrPath
                : trim($domainOrPath, '/') . '.' . $this->getSiteDomain();
            $base['path'] = '/';
        } else {
            $base['domain'] = $this->getSiteDomain();
            $base['path'] = '/' . trim($domainOrPath, '/') . '/';
        }

        $data = array_merge($base, array_intersect_key($overrides, $base));

        // Make sure the path is in the `/path/` format.
        if (isset($data['path']) && $data['path'] !== '/') {
            $data['path'] = '/' . unleadslashit(untrailslashit($data['path'])) . '/';
        }

        $blogId = $this->haveInDatabase($this->grabBlogsTableName(), $data);
        $this->scaffoldBlogTables($blogId, $domainOrPath, (bool)$subdomain);

        try {
            $fs = $this->getWpFilesystemModule();
            $this->debug('Scaffolding blog uploads directories.');
            $fs->makeUploadsDir("sites/{$blogId}");
        } catch (ModuleException $e) {
            $this->debugSection(
                'Filesystem',
                'Could not scaffold blog directories: WPFilesystem module not loaded in suite.'
            );
        }

        return $blogId;
    }

    /**
     * Returns the site domain inferred from the `url` set in the config.
     *
     * @example
     * ```php
     * $domain = $I->getSiteDomain();
     * // We should be redirected to the HTTPS version when visiting the HTTP version.
     * $I->amOnPage('http://' . $domain);
     * $I->seeCurrentUrlEquals('https://' . $domain);
     * ```
     *
     * @return string The site domain, e.g. `worpdress.localhost` or `localhost:8080`.
     */
    public function getSiteDomain()
    {
        return last(explode('//', $this->config['url']));
    }

    /**
     * Scaffolds the blog tables to support and create a blog.
     *
     * @param int $blogId The blog ID.
     * @param string $domainOrPath Either the path or the sub-domain of the blog to create.
     * @param bool $isSubdomain Whether to create a sub-folder or a sub-domain blog.
     *
     * @return void
     */
    protected function scaffoldBlogTables($blogId, $domainOrPath, $isSubdomain = true)
    {
        $stylesheet = $this->grabOptionFromDatabase('stylesheet');
        $subdomain = $isSubdomain ?
            trim($domainOrPath, '.')
            : '';
        $subFolder = !$isSubdomain ?
            trim($domainOrPath, '/')
            : '';

        $data = [
            'subdomain' => $subdomain,
            'domain' => $this->getSiteDomain(),
            'subfolder' => $subFolder,
            'stylesheet' => $stylesheet,
        ];
        $dbh = $this->_getDbh();

        $dropQuery = $this->tables->getBlogDropQuery($this->config['tablePrefix'], $blogId);
        $sth = $dbh->prepare($dropQuery);
        $this->debugSection('Query', $sth->queryString);
        $dropped = $sth->execute();

        $scaffoldQuery = $this->tables->getBlogScaffoldQuery($this->config['tablePrefix'], $blogId, $data);
        $sth = $dbh->prepare($scaffoldQuery);
        $this->debugSection('Query', $sth->queryString);
        $created = $sth->execute();

        $this->scaffoldedBlogIds[] = $blogId;
    }

    /**
     * Gets the WPFilesystem module.
     *
     * @return WPFilesystem The filesystem module instance if loaded in the suite.
     *
     * @throws ModuleException If the WPFilesystem module is not loaded in the suite.
     */
    protected function getWpFilesystemModule()
    {
        try {
            /** @var WPFilesystem $fs */
            $fs = $this->getModule('WPFilesystem');

            return $fs;
        } catch (ModuleException $e) {
            $message = 'This method requires the WPFilesystem module.';
            throw new ModuleException(__CLASS__, $message);
        }
    }

    /**
     * Removes one ore more blogs frome the database.
     *
     * @example
     * ```php
     * // Remove the blog, all its tables and files.
     * $I->dontHaveBlogInDatabase(['path' => 'test/one']);
     * // Remove the blog entry, not the tables though.
     * $I->dontHaveBlogInDatabase(['blog_id' => $blogId]);
     * // Remove multiple blogs.
     * $I->dontHaveBlogInDatabase(['domain' => 'test']);
     * ```
     *
     * @param array<string,mixed> $criteria An array of search criteria to find the blog rows in the blogs table.
     * @param bool $removeTables Remove the blog tables.
     * @param bool $removeUploads Remove the blog uploads; requires the `WPFilesystem` module.
     *
     * @return void
     *
     * @throws \Exception
     */
    public function dontHaveBlogInDatabase(array $criteria, $removeTables = true, $removeUploads = true)
    {
        $criteria = $this->prepareBlogCriteria($criteria);

        $blogIds = $this->grabAllFromDatabase($this->grabBlogsTableName(), 'blog_id', $criteria);

        foreach (array_column($blogIds, 'blog_id') as $blogId) {
            if (empty($blogId)) {
                $this->debug('No blog found matching criteria ' . json_encode($criteria, JSON_PRETTY_PRINT));
                return;
            }

            if ($removeTables) {
                foreach ($this->grabBlogTableNames($blogId) as $tableName) {
                    $this->dontHaveTableInDatabase($tableName);
                }
            }

            if ($removeUploads) {
                try {
                    $fs = $this->getWpFilesystemModule();
                    $fs->deleteUploadedDir($fs->getBlogUploadsPath($blogId));
                } catch (ModuleException $e) {
                    $this->debugSection(
                        'Filesystem',
                        'Could not delete blog directories: WPFilesystem module not loaded in suite.'
                    );
                }
            }

            $this->dontHaveInDatabase($this->grabBlogsTableName(), $criteria);
        }
    }

    /**
     * Returns a list of tables for a blog ID.
     *
     * @param int $blogId The ID of the blog to fetch the tables for.
     *
     * @return array<string> An array of tables for the blog, it does not include the tables common to all blogs; an
     *                       empty array if the tables for the blog do not exist.
     *
     * @throws \Exception If there is any error while preparing the query.
     * @example
     *      ```php
     *      $blogId = $I->haveBlogInDatabase('test');
     *      $tables = $I->grabBlogTableNames($blogId);
     *      $options = array_filter($tables, function($tableName){
     *      return str_pos($tableName, 'options') !== false;
     * });
     * ```
     *
     */
    public function grabBlogTableNames($blogId)
    {
        $table_prefix = "{$this->tablePrefix}{$blogId}_";
        $query = 'SELECT table_name '
                 . 'FROM information_schema.tables '
                 . "WHERE table_schema = ? and table_name like '{$table_prefix}%'";
        $databaseName = $this->_getDriver()->executeQuery('select database()', [])->fetchColumn();
        return $this->_getDriver()->executeQuery($query, [$databaseName])->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * Removes a table from the database.
     * The case where a table does not exist is handled without raising an error.
     *
     * @example
     * ```php
     * $ordersTable = $I->grabPrefixedTableNameFor('orders');
     * $I->dontHaveTableInDatabase($ordersTable);
     * ```
     *
     * @param string $fullTableName The full table name, including the table prefix.
     *
     * @return void
     *
     * @throws \Exception If there is an error while dropping the table.
     */
    public function dontHaveTableInDatabase($fullTableName)
    {
        $drop = "DROP TABLE {$fullTableName}";

        try {
            $this->_getDriver()->executeQuery($drop, []);
        } catch (\PDOException $e) {
            if (false === strpos($e->getMessage(), 'table or view not found')) {
                throw $e;
            }
            $this->debug("Table {$fullTableName} not removed from database: it did not exist.");
            return;
        }

        $this->debug("Table {$fullTableName} removed from database.");
    }

    /**
     * Checks that a row is not present in the `blogs` table.
     *
     * @example
     * ```php
     * $I->haveManyBlogsInDatabase(2, ['path' => 'test-{{n}}'], false)
     * $I->dontSeeBlogInDatabase(['path' => '/test-3/'])
     * ```
     *
     * @param array<string,mixed> $criteria An array of search criteria.
     *
     * @return void
     */
    public function dontSeeBlogInDatabase(array $criteria)
    {
        $this->dontSeeInDatabase($this->grabBlogsTableName(), $this->prepareBlogCriteria($criteria));
    }

    /**
     * Sets the current theme options.
     *
     * @example
     * ```php
     * $I->useTheme('twentyseventeen');
     * $I->useTheme('child-of-twentyseventeen', 'twentyseventeen');
     * $I->useTheme('acme', 'acme', 'Acme Theme');
     * ```
     *
     * @param string      $stylesheet The theme stylesheet slug, e.g. `twentysixteen`.
     * @param string $template   The theme template slug, e.g. `twentysixteen`, defaults to `$stylesheet`.
     *
     * @param string $themeName The theme name, e.g. `Acme`, defaults to the "title" version of
     *                                     `$stylesheet`.
     *
     * @return void
     */
    public function useTheme($stylesheet, $template = null, $themeName = null)
    {
        ensure(is_string($stylesheet), 'Stylesheet must be a string');
        ensure(is_string((string)$template), 'Template must either be a string or be null.');
        ensure(is_string((string)$themeName), 'Current Theme must either be a string or be null.');

        $template = $template ?: $stylesheet;
        $themeName = $themeName ?: ucwords($stylesheet, ' _');

        $this->haveOptionInDatabase('stylesheet', $stylesheet);
        $this->haveOptionInDatabase('template', $template);
        $this->haveOptionInDatabase('current_theme', $themeName);

        $this->stylesheet = $stylesheet;
        $this->menus[$stylesheet] = empty($this->menus[$stylesheet]) ? [] : $this->menus[$stylesheet];
    }

    /**
     * Creates and adds a menu to a theme location in the database.
     *
     * @example
     * ```php
     * list($termId, $termTaxId) = $I->haveMenuInDatabase('test', 'sidebar');
     * ```
     *
     * @param string $slug      The menu slug.
     * @param string $location  The theme menu location the menu will be assigned to.
     * @param array<string,mixed>  $overrides An array of values to override the defaults.
     *
     * @return array<int> An array containing the created menu `term_id` and `term_taxonomy_id`.
     */
    public function haveMenuInDatabase($slug, $location, array $overrides = [])
    {
        if (!is_string($slug)) {
            throw new \InvalidArgumentException('Menu slug must be a string.');
        }
        if (!is_string($location)) {
            throw new \InvalidArgumentException('Menu location must be a string.');
        }

        if (empty($this->stylesheet)) {
            throw new \RuntimeException('Stylesheet must be set to add menus, use `useTheme` first.');
        }

        $title = empty($overrides['title']) ? ucwords($slug, ' -_') : $overrides['title'];
        $menuIds = $this->haveTermInDatabase($title, 'nav_menu', ['slug' => $slug]);

        $menuTermTaxonomyIds = reset($menuIds);

        // set theme options to use the `primary` location
        $this->haveOptionInDatabase(
            'theme_mods_' . $this->stylesheet,
            ['nav_menu_locations' => [$location => $menuTermTaxonomyIds]]
        );

        $this->menus[$this->stylesheet][$slug] = $menuIds;
        $this->menuItems[$this->stylesheet][$slug] = [];

        return $menuIds;
    }

    /**
     * Adds a menu element to a menu for the current theme.
     *
     * @param string              $menuSlug  The menu slug the item should be added to.
     * @param string              $title     The menu item title.
     * @param int|null            $menuOrder An optional menu order, `1` based.
     * @param array<string,mixed> $meta      An associative array that will be prefixed with `_menu_item_` for the item
     *                                       post meta.
     *
     * @return int The menu item post `ID`
     * @example
     * ```php
     * $I->haveMenuInDatabase('test', 'sidebar');
     * $I->haveMenuItemInDatabase('test', 'Test one', 0);
     * $I->haveMenuItemInDatabase('test', 'Test two', 1);
     * ```
     *
     */
    public function haveMenuItemInDatabase($menuSlug, $title, $menuOrder = null, array $meta = [])
    {
        if (!is_string($menuSlug)) {
            throw new \InvalidArgumentException('Menu slug must be a string.');
        }

        if (empty($this->stylesheet)) {
            throw new \RuntimeException('Stylesheet must be set to add menus, use `useTheme` first.');
        }
        if (!array_key_exists($menuSlug, $this->menus[$this->stylesheet])) {
            throw new \RuntimeException("Menu $menuSlug is not a registered menu for the current theme.");
        }
        $menuOrder = $menuOrder ?: count($this->menuItems[$this->stylesheet][$menuSlug]) + 1;
        $menuItemId = $this->havePostInDatabase([
            'post_title' => $title,
            'menu_order' => $menuOrder,
            'post_type' => 'nav_menu_item',
        ]);
        $defaults = [
            'type' => 'custom',
            'object' => 'custom',
            'url' => 'http://example.com',
        ];
        $meta = array_merge($defaults, $meta);
        array_walk($meta, function ($value, $key) use ($menuItemId) {
            $this->havePostmetaInDatabase($menuItemId, '_menu_item_' . $key, $value);
        });
        $this->haveTermRelationshipInDatabase($menuItemId, $this->menus[$this->stylesheet][$menuSlug][1]);
        $this->menuItems[$this->stylesheet][$menuSlug][] = $menuItemId;

        return $menuItemId;
    }

    /**
     * Checks for a term relationship in the database.
     *
     * @example
     * ```php
     * $postId = $I->havePostInDatabase(['tax_input' => ['category' => 'one']]);
     * $I->seeTermRelationshipInDatabase(['object_id' => $postId, 'term_taxonomy_id' => $oneTermTaxId]);
     * ```
     *
     * @param array<string,mixed> $criteria An array of search criteria.
     *
     * @return void
     */
    public function seeTermRelationshipInDatabase(array $criteria)
    {
        $this->seeInDatabase($this->grabPrefixedTableNameFor('term_relationships'), $criteria);
    }

    /**
     * Sets the database driver of this object.
     *
     * @param mixed $driver
     *
     * @return void
     */
    public function _setDriver($driver)
    {
        $this->driver = $driver;
    }

    /**
     * Creates the database entries representing an attachment and moves the attachment file to the right location.
     *
     * @param string                   $file       The absolute path to the attachment file.
     * @param string|int               $date       Either a string supported by the `strtotime` function or a UNIX
     *                                             timestamp that should be used to build the "year/time" uploads
     *                                             sub-folder structure.
     * @param array<string,mixed>      $overrides  An associative array of values overriding the default ones.
     * @param array<string,array<int>> $imageSizes An associative array in the format [ <size> => [<width>,<height>]] to
     *                                             override the image sizes created by default.
     *
     * @return int The post ID of the inserted attachment.
     *
     * @throws ModuleException If the WPFilesystem module is not loaded in the suite or the file to attach is not
     *                         readable
     *
     * @throws \Gumlet\ImageResizeException If the image resize operation fails while trying to create the image sizes.
     *
     * @throws ModuleRequireException If the `WPFileSystem` module is not loaded in the suite or if the
     *                                'gumlet/php-image-resize:^1.6' package is not installed.
     * @example
     * ```php
     * $file = codecept_data_dir('images/test.png');
     * $attachmentId = $I->haveAttachmentInDatabase($file);
     * $image = codecept_data_dir('images/test-2.png');
     * $lastWeekAttachment = $I->haveAttachmentInDatabase($image, '-1 week');
     * ```
     *
     * Requires the WPFilesystem module.
     *
     */
    public function haveAttachmentInDatabase($file, $date = 'now', array $overrides = [], $imageSizes = null)
    {
        if (!class_exists('\\Gumlet\\ImageResize')) {
            $message = 'The "haveAttachmentInDatabase" method requires the "gumlet/php-image-resize:^1.6" package.' .
                PHP_EOL .
                'Please install it using the command "composer require --dev gumlet/php-image-resize:^1.6"';
            throw new ModuleRequireException($this, $message);
        }

        try {
            $fs = $this->getWpFilesystemModule();
        } catch (ModuleException $e) {
            throw new ModuleRequireException(
                $this,
                'The haveAttachmentInDatabase method requires the WPFilesystem module: update the suite ' .
                'configuration to use it'
            );
        }

        $pathInfo = pathinfo($file);
        $slug = slug($pathInfo['filename']);

        if (!is_readable($file)) {
            throw new ModuleException($this, "File [{$file}] is not readable.");
        }

        $data = file_get_contents($file);

        if (false === $data) {
            throw new ModuleException($this, "File [{$file}] contents could not be read.");
        }

        $uploadedFilePath = $fs->writeToUploadedFile($pathInfo['basename'], $data, $date);
        $uploadUrl = $this->grabSiteUrl(str_replace($fs->getWpRootFolder(), '', $uploadedFilePath));
        $uploadLocation = unleadslashit(str_replace($fs->getUploadsPath(), '', $uploadedFilePath));

        $mimeType = mime_content_type($file);

        $overrides = array_merge([
            'post_type' => 'attachment',
            'post_title' => $slug,
            'post_status' => 'inherit',
            'post_name' => $slug,
            'post_parent' => '0',
            'guid' => $uploadUrl,
            'post_mime_type' => $mimeType,
        ], $overrides);

        $mimeType = $overrides['post_mime_type'];

        $id = $this->havePostInDatabase($overrides);

        $imageInfo = getimagesize($file);

        $this->havePostmetaInDatabase($id, '_wp_attached_file', $uploadLocation);

        if ($imageInfo === false) {
            return $id;
        }

        list($imageWidth, $imageHeight) = $imageInfo;

        if ($imageSizes === null) {
            $imageSizes = [
                'thumbnail' => [150, 150],
                'medium' => 300,
                'large' => 768,
            ];
        }

        $extension = isset($pathInfo['extension']) ? $pathInfo['extension'] : '';

        $createdImages = [];
        foreach ($imageSizes as $size => $thisSizes) {
            $thisSizes = (array)$thisSizes;
            $width = (int)$thisSizes[0];
            $height = isset($thisSizes[1]) ? (int)$thisSizes[1] : false;

            try {
                $image = new ImageResize($file);
            } catch (ImageResizeException $e) {
                throw new ModuleException(__CLASS__, "Could not initialize image processing class for file [{$file}]");
            }

            if (empty($height)) {
                // resize to width
                $height = (int)($imageHeight * ($width / $imageWidth));
                $image->resizeToWidth($width);
            } elseif (empty($width)) {
                // resize to height
                $width = (int)($imageWidth * ($height / $imageHeight));
                $image->resizeToHeight($height);
            } else {
                // resize width and height
                $image->resizeToBestFit($width, $height);
            }

            $image->save(str_replace($slug, "{$slug}-{$width}x{$height}", $uploadedFilePath));

            $createdImages[$size] = (object)['width' => $width, 'height' => $height];
        }

        $createSizeEntry = function ($sizes) use ($slug, $mimeType, $extension) {
            return [
                'file' => "{$slug}-{$sizes->width}x{$sizes->height}.{$extension}",
                'width' => $sizes->width,
                'height' => $sizes->height,
                'mime-type' => $mimeType,
            ];
        };
        $metadata = [
            'width' => $imageWidth,
            'height' => $imageHeight,
            'file' => $uploadLocation,
            'sizes' => array_combine(
                array_keys($createdImages),
                array_map($createSizeEntry, $createdImages)
            ),
            'image_meta' =>
                [
                    'aperture' => '0',
                    'credit' => '',
                    'camera' => '',
                    'caption' => '',
                    'created_timestamp' => '0',
                    'copyright' => '',
                    'focal_length' => '0',
                    'iso' => '0',
                    'shutter_speed' => '0',
                    'title' => '',
                    'orientation' => '0',
                    'keywords' => [],
                ],
        ];
        $this->havePostmetaInDatabase($id, '_wp_attachment_metadata', $metadata);

        return $id;
    }

    /**
     * Returns the current site URL as specified in the module configuration.
     *
     * @example
     * ```php
     * $shopPath = $I->grabSiteUrl('/shop');
     * ```
     *
     * @param string $path A path that should be appended to the site URL.
     *
     * @return string The current site URL
     */
    public function grabSiteUrl($path = null)
    {
        $url = $this->config['url'];

        if ($path !== null) {
            return untrailslashit($this->config['url']) . DIRECTORY_SEPARATOR . unleadslashit($path);
        }

        return $url;
    }

    /**
     * Checks for an attachment in the database.
     *
     * @example
     * ```php
     * $url = 'https://example.org/images/foo.png';
     * $I->seeAttachmentInDatabase(['guid' => $url]);
     * ```
     * @param  array<string,mixed> $criteria An array of search criteria.
     *
     * @return void
     */
    public function seeAttachmentInDatabase(array $criteria)
    {
        $this->seePostInDatabase(array_merge($criteria, ['post_type' => 'attachment']));
    }

    /**
     * Checks that an attachment is not in the database.
     *
     * @example
     * ```php
     * $url = 'https://example.org/images/foo.png';
     * $I->dontSeeAttachmentInDatabase(['guid' => $url]);
     * ```
     *
     * @param  array<string,mixed> $criteria An array of search criteria.
     *
     * @return void
     */
    public function dontSeeAttachmentInDatabase(array $criteria)
    {
        $this->dontSeePostInDatabase(array_merge($criteria, ['post_type' => 'attachment']));
    }

    /**
     * Removes an attachment from the posts table.
     *
     * @param array<string,mixed> $criteria    An array of search criteria to find the attachment post in the posts
     *                                         table.
     * @param bool                $purgeMeta   If set to `true` then the meta for the attachment will be purged too.
     * @param bool $removeFiles                Remove all files too, requires the `WPFilesystem` module to be loaded in
     *                                         the suite.
     *
     * @return void
     *
     * @throws ModuleException If the WPFilesystem module is not loaded in the suite
     *                                                and the `$removeFiles` argument is `true`.
     * @example
     *      ```
     *      $postmeta = $I->grabpostmetatablename();
     * $thumbnailId = $I->grabFromDatabase($postmeta, 'meta_value', [
     *      'post_id' => $id,
     *      'meta_key'=>'thumbnail_id'
     * ]);
     * // Remove only the database entry (including postmeta) but not the files.
     * $I->dontHaveAttachmentInDatabase($thumbnailId);
     * // Remove the database entry (including postmeta) and the files.
     * $I->dontHaveAttachmentInDatabase($thumbnailId, true, true);
     * ```
     *
     */
    public function dontHaveAttachmentInDatabase(array $criteria, $purgeMeta = true, $removeFiles = false)
    {
        $mergedCriteria = array_merge($criteria, ['post_type' => 'attachment']);

        if ((bool)$removeFiles) {
            $posts = $this->grabPostsTableName();
            $attachmentIds = $this->grabColumnFromDatabase($posts, 'ID', $mergedCriteria);
            $this->dontHaveAttachmentFilesInDatabase($attachmentIds);
        }

        $this->dontHavePostInDatabase($mergedCriteria, $purgeMeta);
    }

    /**
     * Removes all the files attached with an attachment post, it will not remove the database entries.
     * Requires the `WPFilesystem` module to be loaded in the suite.
     *
     * @example
     * ```php
     * $posts = $I->grabPostsTableName();
     * $attachmentIds = $I->grabColumnFromDatabase($posts, 'ID', ['post_type' => 'attachment']);
     * // This will only remove the files, not the database entries.
     * $I->dontHaveAttachmentFilesInDatabase($attachmentIds);
     * ```
     *
     * @param array<int>|int $attachmentIds An attachment post ID or an array of attachment post IDs.
     *
     * @return void
     *
     * @throws ModuleRequireException If the `WPFilesystem` module is not loaded in the suite.
     */
    public function dontHaveAttachmentFilesInDatabase($attachmentIds)
    {
        try {
            $fs = $this->getWpFilesystemModule();
        } catch (ModuleException $e) {
            throw new ModuleRequireException(
                $this,
                'The haveAttachmentInDatabase method requires the WPFilesystem module: update the suite ' .
                'configuration to use it'
            );
        }

        $postmeta = $this->grabPostmetaTableName();

        foreach ((array)$attachmentIds as $attachmentId) {
            $attachedFile = $this->grabAttachmentAttachedFile($attachmentId);
            $attachmentMetadata = $this->grabAttachmentMetadata($attachmentId);

            $filesPath = untrailslashit($fs->getUploadsPath(dirname($attachedFile)));


            if (!isset($attachmentMetadata['sizes']) && is_array($attachmentMetadata['sizes'])) {
                continue;
            }

            foreach ($attachmentMetadata['sizes'] as $size => $sizeData) {
                $filePath = $filesPath . '/' . $sizeData['file'];
                $fs->deleteUploadedFile($filePath);
            }
            $fs->deleteUploadedFile($attachedFile);
        }
    }

    /**
     * Returns the path, as stored in the database, of an attachment `_wp_attached_file` meta.
     * The attached file is, usually, an attachment origal file.
     *
     * @example
     * ```php
     * $file = $I->grabAttachmentAttachedFile($attachmentId);
     * $fileInfo = new SplFileInfo($file);
     * $I->assertEquals('jpg', $fileInfo->getExtension());
     * ```
     *
     * @param int $attachmentPostId The attachment post ID.
     *
     * @return string The attachment attached file path or an empt string if not set.
     */
    public function grabAttachmentAttachedFile($attachmentPostId)
    {
        $attachedFile = $this->grabFromDatabase(
            $this->grabPostmetaTableName(),
            'meta_value',
            ['meta_key' => '_wp_attached_file', 'post_id' => $attachmentPostId]
        );

        return (string)$attachedFile;
    }

    /**
     * Returns the metadata array for an attachment post.
     * This is the value of the `_wp_attachment_metadata` meta.
     *
     * @param int $attachmentPostId The attachment post ID.
     *
     * @return array<string,mixed> The unserialized contents of the attachment `_wp_attachment_metadata` meta or an
     *                             empty array.
     * @example
     * ```php
     * $metadata = $I->grabAttachmentMetadata($attachmentId);
     * $I->assertEquals(['thumbnail', 'medium', 'medium_large'], array_keys($metadata['sizes']);
     * ```
     *
     */
    public function grabAttachmentMetadata($attachmentPostId)
    {
        $serializedData = $this->grabFromDatabase(
            $this->grabPostmetaTableName(),
            'meta_value',
            ['meta_key' => '_wp_attachment_metadata', 'post_id' => $attachmentPostId]
        );

        return !empty($serializedData) ?
            unserialize($serializedData)
            : [];
    }

    /**
     * Removes an entry from the posts table.
     *
     * @example
     * ```php
     * $posts = $I->haveManyPostsInDatabase(3, ['post_title' => 'Test {{n}}']);
     * $I->dontHavePostInDatabase(['post_title' => 'Test 2']);
     * ```
     *
     * @param  array<string,mixed> $criteria  An array of search criteria.
     * @param bool   $purgeMeta If set to `true` then the meta for the post will be purged too.
     *
     * @return void
     */
    public function dontHavePostInDatabase(array $criteria, $purgeMeta = true)
    {
        $postsTable = $this->grabPrefixedTableNameFor('posts');
        if ($purgeMeta) {
            $id = $this->grabFromDatabase($postsTable, 'ID', $criteria);
            if (!empty($id)) {
                $this->dontHavePostMetaInDatabase(['post_id' => $id]);
            }
        }

        $this->dontHaveInDatabase($postsTable, $criteria);
    }

    /**
     * Removes an entry from the postmeta table.
     *
     * @example
     * ```php
     * $postId = $I->havePostInDatabase(['meta_input' => ['rating' => 23]]);
     * $I->dontHavePostMetaInDatabase(['post_id' => $postId, 'meta_key' => 'rating']);
     * ```
     *
     * @param  array<string,mixed> $criteria An array of search criteria.
     *
     * @return void
     */
    public function dontHavePostMetaInDatabase(array $criteria)
    {
        $tableName = $this->grabPrefixedTableNameFor('postmeta');
        $this->dontHaveInDatabase($tableName, $criteria);
    }

    /**
     * Removes a user(s) from the database using the user email address.
     *
     * @example
     * ```php
     * $luca = $I->haveUserInDatabase('luca', 'editor', ['user_email' => 'luca@example.org']);
     * $I->dontHaveUserInDatabaseWithEmail('luca@exampl.org');
     * ```
     *
     * @param string $userEmail The email of the user to remove.
     * @param bool   $purgeMeta Whether the user meta should be purged alongside the user or not.
     *
     * @return array<int> An array of the deleted user(s) ID(s)
     */
    public function dontHaveUserInDatabaseWithEmail($userEmail, $purgeMeta = true)
    {
        $data = $this->grabAllFromDatabase($this->grabUsersTableName(), 'ID', ['user_email' => $userEmail]);
        if (!(is_array($data) && !empty($data))) {
            return [];
        }

        $ids = array_column($data, 'ID');

        foreach ($ids as $id) {
            $this->dontHaveUserInDatabase($id, $purgeMeta);
        }

        return $ids;
    }

    /**
     * Returns the table prefix, namespaced for secondary blogs if selected.
     *
     * @example
     * ```php
     * // Assuming a table prefix of `wp_` it will return `wp_`;
     * $tablePrefix = $I->grabTablePrefix();
     * $I->useBlog(23);
     * // Assuming a table prefix of `wp_` it will return `wp_23_`;
     * $tablePrefix = $I->grabTablePrefix();
     * ```
     *
     * @return string The blog aware table prefix.
     */
    public function grabTablePrefix()
    {
        return $this->tablePrefix;
    }

    /**
     * Removes a user from the database.
     *
     * @example
     * ```php
     * $bob = $I->haveUserInDatabase('bob');
     * $alice = $I->haveUserInDatabase('alice');
     * // Remove Bob's user and meta.
     * $I->dontHaveUserInDatabase('bob');
     * // Remove Alice's user but not meta.
     * $I->dontHaveUserInDatabase($alice);
     * ```
     *
     * @param int|string $userIdOrLogin The user ID or login name.
     * @param bool       $purgeMeta Whether the user meta should be purged alongside the user or not.
     *
     * @return void
     */
    public function dontHaveUserInDatabase($userIdOrLogin, $purgeMeta = true)
    {
        $userId = is_numeric($userIdOrLogin) ? intval($userIdOrLogin) : $this->grabUserIdFromDatabase($userIdOrLogin);
        $this->dontHaveInDatabase($this->grabPrefixedTableNameFor('users'), ['ID' => $userId]);
        if ($purgeMeta) {
            $this->dontHaveInDatabase($this->grabPrefixedTableNameFor('usermeta'), ['user_id' => $userId]);
        }
    }

    /**
     * Gets the a user ID from the database using the user login.
     *
     * @example
     * ```php
     * $userId = $I->grabUserIdFromDatabase('luca');
     * ```
     *
     * @param string $userLogin The user login name.
     *
     * @return int The user ID
     */
    public function grabUserIdFromDatabase($userLogin)
    {
        return $this->grabFromDatabase($this->grabUsersTableName(), 'ID', ['user_login' => $userLogin]);
    }

    /**
     * Gets the value of one or more post meta values from the database.
     *
     * @example
     * ```php
     * $thumbnail_id = $I->grabPostMetaFromDatabase($postId, '_thumbnail_id', true);
     * ```
     *
     * @param int    $postId  The post ID.
     * @param string $metaKey The key of the meta to retrieve.
     * @param bool   $single  Whether to return a single meta value or an arrya of all available meta values.
     *
     * @return mixed|array<string,mixed> Either a single meta value or an array of all the available meta values.
     */
    public function grabPostMetaFromDatabase($postId, $metaKey, $single = false)
    {
        $postmeta = $this->grabPostmetaTableName();
        $grabbed = (array)$this->grabColumnFromDatabase(
            $postmeta,
            'meta_value',
            ['post_id' => $postId, 'meta_key' => $metaKey]
        );
        $values = array_reduce($grabbed, function (array $metaValues, $value) {
            $values = (array)$this->maybeUnserialize($value);
            array_push($metaValues, ...$values);
            return $metaValues;
        }, []);

        return (bool)$single ? $values[0] : $values;
    }

    /**
     * Returns the full name of a table for a blog from a multisite installation database.
     *
     * @example
     * ```php
     * $blogOptionTable = $I->grabBlogTableName($blogId, 'option');
     * ```
     *
     * @param int    $blogId The blog ID.
     * @param string $table  The table name, without table prefix.
     *
     * @return string The full blog table name, including the table prefix or an empty string
     *                if the table does not exist.
     *
     * @throws ModuleException If no tables are found for the blog.
     */
    public function grabBlogTableName($blogId, $table)
    {
        $blogTableNames = $this->grabBlogTableNames($blogId);

        if (!count($blogTableNames)) {
            throw new ModuleException($this, 'No tables found for blog with ID ' . $blogId);
        }

        foreach ($blogTableNames as $candidate) {
            if (strpos($candidate, $table) === false) {
                continue;
            }
            return $candidate;
        }

        return '';
    }

    /**
     * Checks that a table is not in the database.
     *
     * @example
     * ```php
     * $options = $I->grabPrefixedTableNameFor('options');
     * $I->dontHaveTableInDatabase($options)
     * $I->dontSeeTableInDatabase($options);
     * ```
     *
     * @param string $table The full table name, including the table prefix.
     *
     * @return void
     */
    public function dontSeeTableInDatabase($table)
    {
        $count = $this->_seeTableInDatabase($table);
        $this->assertEmpty($count, "Found {$count} matches for the {$table} table in database; expected none.");
    }

    /**
     * Returns the table prefix for a blog.
     *
     * @example
     * ```php
     * $blogId = $I->haveBlogInDatabase('test');
     * $blogTablePrefix = $I->getBlogTablePrefix($blogId);
     * $blogOrders = $I->blogTablePrefix . 'orders';
     * ```
     *
     * @param int $blogId The blog ID.
     *
     * @return string The table prefix for the blog.
     */
    public function grabBlogTablePrefix($blogId)
    {
        return $this->grabTablePrefix() . "{$blogId}_";
    }

    /**
     * Returns a blog domain given its ID.
     *
     * @example
     * ```php
     * $blogIds = $I->haveManyBlogsInDatabase(3);
     * $domains = array_map(function($blogId){
     *      return $I->grabBlogDomain($blogId);
     * }, $blogIds);
     * ```
     *
     * @param int $blogId The blog ID.
     *
     * @return string The blog domain.
     */
    public function grabBlogDomain($blogId)
    {
        return $this->grabFromDatabase($this->grabBlogsTableName(), 'domain', ['blog_id' => $blogId]);
    }

    /**
     * Grabs a blog domain from the blogs table.
     *
     * @example
     * ```php
     * $blogId = $I->haveBlogInDatabase('test');
     * $path = $I->grabBlogDomain($blogId);
     * $I->amOnSubdomain($path);
     * $I->amOnPage('/');
     * ```
     *
     * @param int $blogId The blog ID.
     *
     * @return string The blog domain, if set in the database.
     */
    public function grabBlogPath($blogId)
    {
        return $this->grabFromDatabase($this->grabBlogsTableName(), 'path', ['blog_id' => $blogId]);
    }

    /**
     * Return whether the module did init already or not.
     *
     * @return bool Whether the module did init already or not.
     */
    public function _didInit()
    {
        return $this->didInit;
    }

    /**
     * Prepares a database dump to be loaded by cleaning it and replacing
     * URLs in it if required.
     *
     * @param string|array<string> $dump The dump string or array of lines.
     *
     * @return string|array<string> The ready SQL dump.
     *
     * @throws ModuleException If there's any issue with the URL replacement.
     */
    protected function prepareSqlDump($dump)
    {
        // Remove C-style comments (except MySQL directives).
        $prepared = preg_replace('%/\*(?!!\d+).*?\*/%s', '', $dump) ?: '';

        if (empty($prepared)) {
            return '';
        }

        return $this->_replaceUrlInDump((array)$prepared);
    }

    /**
     * Replaces the URL hard-coded in the database with the one set in the the config if required.
     *
     * @param array<string>|string $sql The SQL dump string or strings.
     *
     * @return string|array<string> The SQL dump string, or strings, with the hard-coded URL replaced.
     *
     * @throws ModuleException If there's an issue while processing the SQL dump file.
     */
    public function _replaceUrlInDump($sql)
    {
        if ($this->config['urlReplacement'] === false) {
            return $sql;
        }

        $this->dbDump->setTablePrefix($this->config['tablePrefix']);
        $this->dbDump->setUrl($this->config['url']);
        $this->dbDump->setOriginalUrl(null);

        if (!empty($this->config['originalUrl'])) {
            $this->dbDump->setOriginalUrl($this->config['originalUrl']);
        }

        try {
            if (\is_array($sql)) {
                $sql = $this->dbDump->replaceSiteDomainInSqlArray($sql);
                $sql = $this->dbDump->replaceSiteDomainInMultisiteSqlArray($sql);
            } else {
                $sql = $this->dbDump->replaceSiteDomainInSqlString($sql, true);
                $sql = $this->dbDump->replaceSiteDomainInMultisiteSqlString($sql, true);
            }
        } catch (DumpException $e) {
            throw new ModuleException($this, $e->getMessage());
        }

        return $sql;
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
        if (!$this->grabFromDatabase($tableName, 'term_id', ['term_id' => $term_id])) {
            throw new \RuntimeException("A term with an id of $term_id does not exist", 1);
        }
    }

    /**
     * Loads a database dump using the current driver.
     *
     * @param string $databaseKey The key of the database to load the dump for.
     *
     * @return void
     *
     * @throws ModuleException If there's a configuration or operation issue.
     */
    protected function loadDumpUsingDriver($databaseKey)
    {
        if ($this->config['urlReplacement'] === true) {
            $this->databasesSql[$databaseKey] = $this->_replaceUrlInDump($this->databasesSql[$databaseKey]);
        }

        parent::loadDumpUsingDriver($databaseKey);
    }

    /**
     * Loads the SQL dumps specified for a database.
     *
     * @param string|null $databaseKey The key of the database to load.
     * @param array<string,mixed>|null $databaseConfig The configuration for the database to load.
     *
     * @return void
     */
    public function _loadDump($databaseKey = null, $databaseConfig = null)
    {
        parent::_loadDump($databaseKey, $databaseConfig);
        $this->prepareDb();
    }

    /**
     * Checks that a post to term relation does not exist in the database.
     *
     * The method will check the "term_relationships" table.
     *
     * @example
     * ```php
     * $fiction = $I->haveTermInDatabase('fiction', 'genre');
     * $nonFiction = $I->haveTermInDatabase('non-fiction', 'genre');
     * $postId = $I->havePostInDatabase(['tax_input' => ['genre' => ['fiction']]]);
     * $I->dontSeePostWithTermInDatabase($postId, $nonFiction['term_taxonomy_id], );
     * ```
     *
     * @param  int          $post_id           The post ID.
     * @param  int          $term_taxonomy_id  The term `term_id` or `term_taxonomy_id`; if the `$taxonomy` argument is
     *                                         passed this parameter will be interpreted as a `term_id`, else as a
     *                                         `term_taxonomy_id`.
     * @param  int|null     $term_order        The order the term applies to the post, defaults to `null` to not use
     *                                         the
     *                                         term order.
     * @param  string|null  $taxonomy          The taxonomy the `term_id` is for; if passed this parameter will be used
     *                                         to build a `taxonomy_term_id` from the `term_id`.
     *
     * @return void
     *
     * @throws ModuleException If a `term_id` is specified but it cannot be matched to the `taxonomy`.
     */
    public function dontSeePostWithTermInDatabase($post_id, $term_taxonomy_id, $term_order = null, $taxonomy = null)
    {
        if ($taxonomy !== null) {
            $match = $this->grabTermTaxonomyIdFromDatabase([
                'term_id' => $term_taxonomy_id,
                'taxonomy' => $taxonomy
            ]);
            if (empty($match)) {
                throw new ModuleException(
                    $this,
                    "No term exists for the `term_id` ({$term_taxonomy_id}) and `taxonomy`({$taxonomy}) couple."
                );
            }
            $term_taxonomy_id = $match;
        }

        $tableName = $this->grabPrefixedTableNameFor('term_relationships');
        $criteria = [
            'object_id' => $post_id,
            'term_taxonomy_id' => $term_taxonomy_id,
        ];

        if (null !== $term_order) {
            $criteria['term_order'] = $term_order;
        }

        $this->dontSeeInDatabase($tableName, $criteria);
    }

    /**
     * Overrides the base module implementation to fire a ModuleEvent.
     *
     * @param array<string,mixed> $settings The suite settings.
     *
     * @return void
     *
     * @throws ModuleException If there's any issue getting hold of the current Codeception global dispatcher.
     */
    public function _beforeSuite($settings = [])
    {
        parent::_beforeSuite($settings);

        /**
         * Dispatches an event after the WPDb module handled the BEFORE_SUITE event.
         *
         * @param WPDb $this The current module instance.
         */
        $this->doAction(static::EVENT_BEFORE_SUITE, $this);
    }

    /**
     * Creates any database that is flagged, in the config, with the `createIfNotExists` flag.
     *
     * @param array<string,mixed> $config The current module configuration.
     *
     * @return void
     *
     * @throws ModuleException If there's any issue processing or reading the database DSN information.
     */
    protected function createDatabasesIfNotExist(array $config)
    {
        $createIfNotExist = [];
        if (!empty($config['createIfNotExists'])) {
            $createIfNotExist[$config['dsn']] = [$config['user'], $config['password']];
        }

        if (!empty($config['databases']) && is_array($config['databases'])) {
            foreach ($config['databases'] as $config) {
                if (!empty($config['createIfNotExists'])) {
                    $createIfNotExist[$config['dsn']] = [$config['user'], $config['password']];
                }
            }
        }

        if (!empty($createIfNotExist)) {
            foreach ($createIfNotExist as $dsn => list($user, $pass)) {
                $dsnMap = dbDsnToMap((string)$dsn);
                $dbname = $dsnMap('dbname', '');

                if (empty($dbname)) {
                    throw new ModuleException(
                        $this,
                        sprintf('Failed to create database; DSN "%s" does not contain the database name.', $dsn)
                    );
                }

                try {
                    // Since the database might not exist at this point, remove the `dbname` from the DSN string.
                    unset($dsnMap['dbname']);
                    $db = db(dbDsnString($dsnMap), $user, $pass);
                    $db('CREATE DATABASE IF NOT EXISTS ' . $dbname);
                } catch (\Exception $e) {
                    throw new ModuleException(
                        $this,
                        sprintf('Failed to create database; error: .' . $e->getMessage())
                    );
                }
            }
        }
    }

    /**
     * Prepares the WordPress database with some test-quality-of-life-improvements.
     *
     * @return void
     */
    protected function prepareDb()
    {
        if (empty($this->config['letAdminEmailVerification'])) {
            // Skip the admin email verification screen.
            $this->haveOptionInDatabase('admin_email_lifespan', self::ADMIN_EMAIL_LIFESPAN);
        }

        if (empty($this->config['letCron'])) {
            // Avoid spawning cron.
            $this->haveTransientInDatabase('doing_cron', strtotime('+9 minutes'));
        }

        /**
         * Dispatches an event after the database has been prepared.
         *
         * @param WPDb $origin This objects.
         * @param array<string,mixed> $config The current WPDb module configuration.
         */
        $this->doAction(static::EVENT_AFTER_DB_PREPARE, $this, $this->config);
    }

    /**
     * Assigns the specified attachment ID as thumbnail (featured image) to a post.
     *
     * @example
     * ```php
     * $attachmentId = $I->haveAttachmentInDatabase(codecept_data_dir('some-image.png'));
     * $postId = $I->havePostInDatabase();
     * $I->havePostThumbnailInDatabase($postId, $attachmentId);
     * ```
     *
     * @param int $postId      The post ID to assign the thumbnail (featured image) to.
     * @param int $thumbnailId The post ID of the attachment.
     *
     * @return int The inserted meta id.
     */
    public function havePostThumbnailInDatabase($postId, $thumbnailId)
    {
        $this->dontHavePostThumbnailInDatabase($postId);
        return $this->havePostmetaInDatabase($postId, '_thumbnail_id', (int) $thumbnailId);
    }

    /**
     * Remove the thumbnail (featured image) from a post, if any.
     *
     * Please note: the method will NOT remove the attachment post, post meta and file.
     *
     * @example
     * ```php
     * $attachmentId = $I->haveAttachmentInDatabase(codecept_data_dir('some-image.png'));
     * $postId = $I->havePostInDatabase();
     * // Attach the thumbnail to the post.
     * $I->havePostThumbnailInDatabase($postId, $attachmentId);
     * // Remove the thumbnail from the post.
     * $I->dontHavePostThumbnailInDatabase($postId);
     * ```
     *
     * @param int $postId The post ID to remove the thumbnail (featured image) from.
     *
     * @return void
     */
    public function dontHavePostThumbnailInDatabase($postId)
    {
        $this->dontHavePostMetaInDatabase([ 'post_id' => $postId, 'meta_key' => '_thumbnail_id' ]);
    }

    /**
     * Loads a set SQL code lines in the current database.
     *
     * @example
     * ```php
     * // Import a SQL string.
     * $I->importSql([$sqlString]);
     * // Import a set of SQL strings.
     * $I->importSql($sqlStrings);
     * // Import a prepared set of SQL strings.
     * $preparedSqlStrings = array_map(function($line){
     *     return str_replace('{{date}}', date('Y-m-d H:i:s'), $line);
     * }, $sqlTemplate);
     * $I->importSql($preparedSqlStrings);
     * ```
     *
     * @param array<string> $sql The SQL strings to load.
     *
     * @return void
     */
    public function importSql(array $sql)
    {
        $this->_getDriver()->load($sql);
    }
}

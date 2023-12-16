<?php

namespace lucatume\WPBrowser\Module;

use Codeception\Exception\ModuleConfigException;
use Codeception\Exception\ModuleException;
use Codeception\Exception\ModuleRequireException;
use Codeception\Lib\Driver\Db as Driver;
use Codeception\Lib\ModuleContainer;
use Codeception\Module\Db;
use Exception;
use Gumlet\ImageResize;
use Gumlet\ImageResizeException;
use InvalidArgumentException;
use JsonException;
use lucatume\WPBrowser\Events\Dispatcher;
use lucatume\WPBrowser\Generators\Blog;
use lucatume\WPBrowser\Generators\Comment;
use lucatume\WPBrowser\Generators\Links;
use lucatume\WPBrowser\Generators\Post;
use lucatume\WPBrowser\Generators\Tables;
use lucatume\WPBrowser\Generators\User;
use lucatume\WPBrowser\Module\Support\DbDump;
use lucatume\WPBrowser\Utils\Db as DbUtils;
use lucatume\WPBrowser\Utils\Filesystem as FS;
use lucatume\WPBrowser\Utils\Serializer;
use lucatume\WPBrowser\Utils\Strings;
use lucatume\WPBrowser\Utils\WP;
use PDO;
use PDOException;
use ReflectionException;
use ReflectionMethod;
use RuntimeException;

/**
 * An extension of Codeception Db class to add WordPress database specific
 * methods.
 */
class WPDb extends Db
{
    public const EVENT_BEFORE_SUITE = 'WPDb.before_suite';
    public const EVENT_BEFORE_INITIALIZE = 'WPDb.before_initialize';
    public const EVENT_AFTER_INITIALIZE = 'WPDb.after_initialize';
    public const EVENT_AFTER_DB_PREPARE = 'WPDb.after_db_prepare';
    public const ADMIN_EMAIL_LIFESPAN = 2533080438;

    protected DbDump $dbDump;

    /**
     * The theme stylesheet in use.
     *
     * @var string
     */
    protected string $stylesheet = '';

    /**
     * The current theme menus.
     *
     * @var array<string,array<string,int[]>>
     */
    protected array $menus = [];

    /**
     * The current menu items, by stylesheet, per menu slug.
     *
     * @var array<string,array<string,int[]>>
     */
    protected array $menuItems = [];

    /**
     * The placeholder that will be replaced with the iteration number when found in strings.
     *
     * @var string
     */
    protected string $numberPlaceholder = '{{n}}';

    /**
     * The legit keys to term criteria.
     *
     * @var array<string>
     */
    protected array $termKeys = ['term_id', 'name', 'slug', 'term_group'];

    /**
     * The legit keys for the term taxonomy criteria.
     *
     * @var array<string>
     */
    protected array $termTaxonomyKeys = ['term_taxonomy_id', 'term_id', 'taxonomy', 'description', 'parent', 'count'];

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
    protected array $requiredFields = ['dsn', 'user', 'password', 'url'];

    /**
     * The module optional configuration parameters.
     *
     * @var array{
     *     tablePrefix: string,
     *     populate: bool,
     *     cleanup: bool,
     *     reconnect: bool,
     *     dump: string|string[]|null,
     *     populator: string|null,
     *     urlReplacement: bool,
     *     originalUrl: string|null,
     *     waitlock: int,
     *     dbUrl?: string,
     *     createIfNotExists?: bool
     * }
     */
    protected array $config = [
        'tablePrefix' => 'wp_',
        'populate' => true,
        'cleanup' => true,
        'reconnect' => false,
        'dump' => null,
        'populator' => null,
        'urlReplacement' => true,
        'originalUrl' => null,
        'waitlock' => 10,
        'createIfNotExists' => false,
    ];

    /**
     * The table prefix to use.
     *
     * @var string
     */
    protected string $tablePrefix = 'wp_';

    /**
     * @var int The id of the blog currently used.
     */
    protected int $blogId = 0;

    /**
     * @var Tables
     */
    protected Tables $tables;

    /**
     * The current template data.
     *
     * @var array<string,mixed>
     */
    protected array $templateData;

    /**
     * An array containing the blog IDs of the sites scaffolded by the module.
     *
     * @var array<int>
     */
    protected array $scaffoldedBlogIds;

    /**
     * Whether the module did init already or not.
     *
     * @var bool
     */
    protected bool $didInit = false;

    /**
     * The database driver object.
     *
     * @var Driver
     */
    protected Driver $driver;

    /**
     * Whether the database has been previously populated or not.
     *
     * @var bool
     */
    protected bool $populated;

    protected ?string $blogUrl = null;

    /**
     * WPDb constructor.
     *
     * @param ModuleContainer $moduleContainer The module container handling the suite modules.
     * @param array<string,mixed>|null $config The module configuration
     * @param DbDump|null $dbDump              The database dump handler.
     *
     * @return void
     */
    public function __construct(ModuleContainer $moduleContainer, ?array $config = null, DbDump $dbDump = null)
    {
        parent::__construct($moduleContainer, $config);
        $this->dbDump = $dbDump ?? new DbDump();
    }

    /**
     * Adds a meta key and value for a site in the database.
     *
     * @example
     * ```php
     * $I->haveSiteMetaInDatabase(1, 'foo', 'bar');
     * $insertedId = $I->haveSiteMetaInDatabase(2, 'foo', ['bar' => 'baz']);
     * ```
     *
     * @param int $blogId    The blog ID.
     * @param string $string The meta key.
     * @param mixed $value   The meta value.
     *
     * @return int The inserted row ID.
     */
    public function haveSiteMetaInDatabase(int $blogId, string $string, mixed $value): int
    {
        $tableName = $this->grabPrefixedTableNameFor('sitemeta');
        return $this->haveInDatabase($tableName, [
            'meta_key' => $string,
            'meta_value' => Serializer::maybeSerialize($value),
            'site_id' => $blogId,
        ]);
    }

    /**
     * Returns a single or all meta values for a site meta key.
     *
     * @example
     * ```php
     * $I->haveSiteMetaInDatabase(1, 'foo', 'bar');
     * $value = $I->grabSiteMetaFromDatabase(1, 'foo', true);
     * $values = $I->grabSiteMetaFromDatabase(1, 'foo', false);
     * ```
     *
     * @param int $blogId The blog ID.
     * @param string $key The meta key.
     * @param bool $single Whether to return a single value or all of them.
     *
     * @return mixed Either a single value or an array of values.
     *
     * @throws Exception On unserialize failure.
     */
    public function grabSiteMetaFromDatabase(int $blogId, string $key, bool $single): mixed
    {
        $tableName = $this->grabPrefixedTableNameFor('sitemeta');
        $criteria = [
            'meta_key' => $key,
            'site_id' => $blogId,
        ];

        if ($single) {
            $meta = $this->grabFromDatabase($tableName, 'meta_value', $criteria);
            return Serializer::maybeUnserialize($meta);
        }

        $meta = $this->grabColumnFromDatabase($tableName, 'meta_value', $criteria);
        return array_map([Serializer::class, 'maybeUnserialize'], $meta);
    }

    /**
     * Returns the value of a post field for a post, from the `posts`  table.
     *
     * @example
     * ```php
     * $title = $I->grabPostFieldFromDatabase(1, 'post_title');
     * $type = $I->grabPostFieldFromDatabase(1, 'post_type');
     * ```
     *
     * @param int $postId   The post ID.
     * @param string $field The post field to get the value for.
     *
     * @return mixed The value of the post field.
     */
    public function grabPostFieldFromDatabase(int $postId, string $field): mixed
    {
        $tableName = $this->grabPrefixedTableNameFor('posts');
        return $this->grabFromDatabase($tableName, $field, ['ID' => $postId]);
    }

    protected function validateConfig(): void
    {
        if (isset($this->config['dbUrl'])) {
            if (!is_string($this->config['dbUrl'])) {
                throw new ModuleConfigException(
                    __CLASS__,
                    message: "The 'dbUrl' configuration parameter must be a string."
                );
            }
            $parsedDbUrl = DbUtils::parseDbUrl($this->config['dbUrl'] ?? '');
            $this->config['dsn'] = $parsedDbUrl['dsn'];

            if ($parsedDbUrl['type'] === 'sqlite') {
                // Ensure the path is relative to Codeception root directory; the SQLite adapter will assume it.
                $rootRelativePath = FS::relativePath(codecept_root_dir(), $parsedDbUrl['name']);
                $this->config['dsn'] = 'sqlite:' . $rootRelativePath;
            }

            $this->config['user'] = $parsedDbUrl['user'];
            $this->config['password'] = $parsedDbUrl['password'];
        }

        $dsn = $this->config['dsn'] ?? '';
        $useSqlite = str_starts_with($dsn, 'sqlite:');

        parent::validateConfig();

        $this->config['tablePrefix'] = (string)$this->config['tablePrefix'];
        $this->config['populate'] = (bool)$this->config['populate'];
        $this->config['cleanup'] = (bool)$this->config['cleanup'];
        $this->config['reconnect'] = (bool)$this->config['reconnect'];
        $this->config['dump'] = array_filter((array)$this->config['dump']);
        $this->config['populator'] = (string)$this->config['populator'];
        $this->config['urlReplacement'] = (bool)$this->config['urlReplacement'];

        if ($useSqlite && !empty($this->config['urlReplacement'])) {
            throw new ModuleConfigException(
                __CLASS__,
                message: "The 'urlReplacement' configuration parameter cannot be used with SQLite."
            );
        }

        $this->config['originalUrl'] = (string)$this->config['originalUrl'];
        $this->config['waitlock'] = (int)$this->config['waitlock'];
        $this->config['createIfNotExists'] = $this->config['createIfNotExists'] ?? false;
    }

    /**
     * Initializes the module.
     *
     * @param Tables|null $table An instance of the tables management object.
     *
     * @throws ModuleException It the database cannot be correctly initialized.
     */
    public function _initialize(Tables $table = null): void
    {
        /**
         * Dispatches an event before the WPDb module initializes.
         *
         * @param WPDb $this The current module instance.
         */
        Dispatcher::dispatch(static::EVENT_BEFORE_INITIALIZE, $this);

        $this->createDatabasesIfNotExist($this->config);

        parent::_initialize();

        $this->tablePrefix = $this->config['tablePrefix'];
        $this->didInit = true;

        /**
         * Dispatches an event after the WPDb module has initialized.
         *
         * @param WPDb $this The current module instance.
         */
        Dispatcher::dispatch(static::EVENT_AFTER_INITIALIZE, $this);
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
     *
     * @throws ModuleConfigException|ModuleException If there's an issue during the cleanup phase.
     */
    public function importSqlDumpFile(string $dumpFile = null): void
    {
        if ($dumpFile !== null) {
            if (!is_file($dumpFile) || !is_readable($dumpFile)) {
                throw new ModuleConfigException($this, "Dump file [{$dumpFile}] does not exist or is not readable.");
            }

            $sql = file($dumpFile);

            if ($sql === false) {
                throw new ModuleConfigException($this, "Failed to read dump file [{$dumpFile}].");
            }

            $this->_getDriver()->load($sql);

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
     * @param string|null $databaseKey                 The key of the database to clean up.
     * @param array<string,mixed>|null $databaseConfig The configuration of the database to clean up.
     *
     *
     * @throws ModuleException|ModuleConfigException If there's a configuration or operation issue.
     */
    public function _cleanup(string $databaseKey = null, array $databaseConfig = null): void
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
     * $I->dontSeeOptionInDatabase('posts_per_page', 23);
     * $I->dontSeeOptionInDatabase(['option_name' => 'posts_per_page']);
     * $I->dontSeeOptionInDatabase(['option_name' => 'posts_per_page', 'option_value' => 23]);
     * ```
     *
     * @param array<string,mixed>|string $criteriaOrName An array of search criteria or the option name.
     * @param mixed|null $value                          The optional value to try and match, only used if the option
     *                                                   name is provided.
     *
     *
     * @throws JsonException If there's an issue debugging the failure.
     */
    public function dontSeeOptionInDatabase(array|string $criteriaOrName, mixed $value = null): void
    {
        $criteria = $this->normalizeOptionCriteria($criteriaOrName, $value);
        $tableName = $this->grabPrefixedTableNameFor('options');

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
     * @param string $tableName The table name, e.g. `options`.
     *
     * @return string            The prefixed table name, e.g. `wp_options` or `wp_2_options`.
     */
    public function grabPrefixedTableNameFor(string $tableName = ''): string
    {
        $idFrag = '';
        if (!(in_array($tableName, $this->uniqueTables) || $this->blogId == 1)) {
            $idFrag = empty($this->blogId) ? '' : "{$this->blogId}_";
        }

        return $this->config['tablePrefix'] . $idFrag . $tableName;
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
     * @param array<string,mixed> $criteria An array of search criteria.
     *
     * @throws JsonException
     */
    public function seePostMetaInDatabase(array $criteria): void
    {
        $tableName = $this->grabPrefixedTableNameFor('postmeta');
        if (!empty($criteria['meta_value'])) {
            $criteria['meta_value'] = Serializer::maybeSerialize($criteria['meta_value']);
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
     * @param array<string,mixed> $criteria An array of search criteria.
     *
     * @throws JsonException If there's an issue debugging the failure.
     */
    public function seeLinkInDatabase(array $criteria): void
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
     * @param array<string,mixed> $criteria An array of search criteria.
     *
     * @throws JsonException If there's an issue debugging the failure.
     */
    public function dontSeeLinkInDatabase(array $criteria): void
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
     * @param array<string,mixed> $criteria An array of search criteria.
     *
     * @throws JsonException If there's an issue debugging the failure.
     */
    public function dontSeePostMetaInDatabase(array $criteria): void
    {
        $tableName = $this->grabPrefixedTableNameFor('postmeta');
        if (!empty($criteria['meta_value'])) {
            $criteria['meta_value'] = Serializer::maybeSerialize($criteria['meta_value']);
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
     * @param int $post_id                     The post ID.
     * @param int $term_taxonomy_id            The term `term_id` or `term_taxonomy_id`; if the `$taxonomy` argument is
     *                                         passed this parameter will be interpreted as a `term_id`, else as a
     *                                         `term_taxonomy_id`.
     * @param int|null $term_order             The order the term applies to the post, defaults to `null` to not use
     *                                         the
     *                                         term order.
     * @param string|null $taxonomy            The taxonomy the `term_id` is for; if passed this parameter will be used
     *                                         to build a `taxonomy_term_id` from the `term_id`.
     *
     *
     * @throws ModuleException|JsonException If a `term_id` is specified, but it cannot be matched to the `taxonomy`.
     */
    public function seePostWithTermInDatabase(
        int $post_id,
        int $term_taxonomy_id,
        int $term_order = null,
        string $taxonomy = null
    ): void {
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
     * @param array<string,mixed> $criteria An array of search criteria.
     *
     * @throws JsonException If there's an issue debugging the failure.
     * @throws ModuleException
     */
    public function seeUserInDatabase(array $criteria): void
    {
        $tableName = $this->grabPrefixedTableNameFor('users');
        $allCriteria = $criteria;
        if (!empty($criteria['user_pass'])) {
            $userPass = $criteria['user_pass'];

            if (!is_string($userPass)) {
                throw new ModuleException(
                    $this,
                    'The user_pass criteria must be a string'
                );
            }

            unset($criteria['user_pass']);

            $hashedPass = $this->grabFromDatabase($tableName, 'user_pass', $criteria);
            $passwordOk = is_string($hashedPass) && WP::checkHashedPassword($userPass, $hashedPass);
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
     * @param array<string,mixed> $criteria An array of search criteria.
     *
     * @throws ModuleException
     */
    public function dontSeeUserInDatabase(array $criteria): void
    {
        $tableName = $this->grabPrefixedTableNameFor('users');
        $allCriteria = $criteria;
        $passwordOk = false;
        if (!empty($criteria['user_pass'])) {
            $userPass = $criteria['user_pass'];

            if (!is_string($userPass)) {
                throw new ModuleException(
                    $this,
                    'The user_pass criteria must be a string'
                );
            }

            unset($criteria['user_pass']);
            $hashedPass = $this->grabFromDatabase($tableName, 'user_pass', [$criteria]);
            $passwordOk = is_string($hashedPass) && WP::checkHashedPassword($userPass, $hashedPass);
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
     * @throws ModuleException If the page cannot be inserted.
     *
     * @see \Codeception\Module\WPDb::havePostInDatabase()
     */
    public function havePageInDatabase(array $overrides = []): int
    {
        $overrides['post_type'] = 'page';

        return $this->havePostInDatabase($overrides);
    }

    /**
     * Inserts a post in the database.
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
     * @param array<int|string,mixed> $data An associative array of post data to override default and random generated
     *                                      values.
     *
     * @return int post_id The inserted post ID.
     *
     * @throws ModuleException If there's an exception during the insertion.
     */
    public function havePostInDatabase(array $data = []): int
    {
        try {
            $postTableName = $this->grabPostsTableName();
            $idColumn = 'ID';
            $id = $this->grabLatestEntryByFromDatabase($postTableName, $idColumn) + 1;
            /** @var array{url: string} $config Validated module config. */
            $config = $this->config;
            $post = Post::buildPostData($id, $this->grabSiteUrl(), $data);
            $hasMeta = !empty($data['meta']) || !empty($data['meta_input']);
            $hasTerms = !empty($data['terms']) || !empty($data['tax_input']);
            $meta = [];
            if ($hasMeta) {
                $meta = !empty($data['meta']) ? $data['meta'] : $data['meta_input'];
                if (!is_array($meta)) {
                    throw new ModuleException(
                        $this,
                        'The meta payload must be an array'
                    );
                }
                unset($post['meta'], $post['meta_input']);
            }
            $terms = [];
            if ($hasTerms) {
                $terms = !empty($data['terms']) ? $data['terms'] : $data['tax_input'];
                if (!is_array($terms)) {
                    throw new ModuleException(
                        $this,
                        'The terms payload must be an array'
                    );
                }
                unset($post['terms'], $post['tax_input']);
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

                        $this->assertIsNumeric($termTaxonomyId, sprintf(
                            'Term taxonomy ID for term "%s" in taxonomy "%s" is not numeric',
                            $termName,
                            $taxonomy
                        ));

                        $this->assertNotEmpty($termTaxonomyId, sprintf(
                            'Term taxonomy ID for term "%s" in taxonomy "%s" is empty',
                            $termName,
                            $taxonomy
                        ));

                        $this->haveTermRelationshipInDatabase($postId, (int)$termTaxonomyId);
                        $this->increaseTermCountBy((int)$termTaxonomyId, 1);
                    }
                }
            }
            return $postId;
        } catch (Exception $e) {
            throw new ModuleException($this, $e->getMessage());
        }
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
    public function grabPostsTableName(): string
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
     * @param string $idColumn  The column that is used, in the table, to uniquely identify
     *                          items.
     *
     * @return int The last insertion id.
     *
     * @throws JsonException If there's an issue logging the failure.
     */
    public function grabLatestEntryByFromDatabase(string $tableName, string $idColumn = 'ID'): int
    {
        $sth = $this->_getDbh()->prepare("SELECT {$idColumn} FROM {$tableName} ORDER BY {$idColumn} DESC LIMIT 1");
        $this->debugSection('Query', $sth->queryString);
        $sth->execute();

        return (int)$sth->fetchColumn();
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
     *
     * @param int $postId       The post ID.
     * @param string $meta_key  The meta key.
     * @param mixed $meta_value The value to insert in the database, objects and arrays will be serialized.
     *
     * @return int The inserted meta `meta_id`.
     */
    public function havePostmetaInDatabase(int $postId, string $meta_key, mixed $meta_value): int
    {
        $tableName = $this->grabPostMetaTableName();

        return $this->haveInDatabase($tableName, [
            'post_id' => $postId,
            'meta_key' => $meta_key,
            'meta_value' => Serializer::maybeSerialize($meta_value),
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
    public function grabPostmetaTableName(): string
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
     * @return int|false The matching term `term_id` or `false` if not found.
     */
    public function grabTermIdFromDatabase(array $criteria): int|false
    {
        $termId = $this->grabFromDatabase($this->grabTermsTableName(), 'term_id', $criteria);

        if (false === $termId) {
            return false;
        }

        $this->assertIsNumeric($termId, "Term ID not found for criteria: " . json_encode($criteria));

        /** @var string|int $termId */
        return (int)$termId;
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
    public function grabTermsTableName(): string
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
     * @param string $name                       The term name, e.g. "Fuzzy".
     * @param string $taxonomy                   The term taxonomy
     * @param array<int|string,mixed> $overrides An array of values to override the default ones.
     *
     * @return array<int> An array containing `term_id` and `term_taxonomy_id` of the inserted term.
     * @throws ModuleException
     */
    public function haveTermInDatabase(string $name, string $taxonomy, array $overrides = []): array
    {
        $termDefaults = ['slug' => Strings::slug($name), 'term_group' => 0];

        $hasMeta = !empty($overrides['meta']);
        $meta = [];
        if ($hasMeta) {
            $meta = $overrides['meta'];
            if (!is_array($meta)) {
                throw new ModuleException(
                    $this,
                    'The meta payload must be an array'
                );
            }
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
    public function grabTermTaxonomyTableName(): string
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
     * @param int $term_id      The ID of the term to insert the meta for.
     * @param string $meta_key  The key of the meta to insert.
     * @param mixed $meta_value The value of the meta to insert, if serializable it will be serialized.
     *
     * @return int The inserted term meta `meta_id`.
     */
    public function haveTermMetaInDatabase(int $term_id, string $meta_key, mixed $meta_value): int
    {
        $tableName = $this->grabTermMetaTableName();

        return $this->haveInDatabase($tableName, [
            'term_id' => $term_id,
            'meta_key' => $meta_key,
            'meta_value' => Serializer::maybeSerialize($meta_value),
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
    public function grabTermMetaTableName(): string
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
     * @return int|false The matching term `term_taxonomy_id` or `false` if not found.
     */
    public function grabTermTaxonomyIdFromDatabase(array $criteria): int|false
    {
        $termTaxId = $this->grabFromDatabase($this->grabTermTaxonomyTableName(), 'term_taxonomy_id', $criteria);

        if ($termTaxId === false) {
            return false;
        }

        $this->assertIsNumeric($termTaxId, "Term taxonomy ID not found for criteria: " . json_encode($criteria));

        /** @var string|int $termTaxId */
        return (int)$termTaxId;
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
     * @param int $object_id        A post ID, a user ID or anything that can be assigned a taxonomy term.
     * @param int $term_taxonomy_id The `term_taxonomy_id` of the term and taxonomy to create a relation with.
     * @param int $term_order       Defaults to `0`.
     */
    public function haveTermRelationshipInDatabase(int $object_id, int $term_taxonomy_id, int $term_order = 0): void
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
    public function grabTermRelationshipsTableName(): string
    {
        return $this->grabPrefixedTableNameFor('term_relationships');
    }

    /**
     * Increases the term counter.
     *
     * @param int $termTaxonomyId The ID of the term to increase the count for.
     * @param int $by             The value to increase the count by.
     *
     * @return bool Whether the update happened correctly or not.
     *
     * @throws ModuleException If there's any error during the update.
     */
    protected function increaseTermCountBy(int $termTaxonomyId, int $by = 1): bool
    {

        try {
            $updateQuery = "UPDATE {$this->grabTermTaxonomyTableName()} SET count = count + {$by}
              WHERE term_taxonomy_id = {$termTaxonomyId}";
            return (bool)$this->_getDriver()->executeQuery($updateQuery, []);
        } catch (Exception $e) {
            throw new ModuleException($this, $e->getMessage());
        }
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
     * @param array<string,mixed> $criteria An array of search criteria.
     *
     * @throws JsonException
     */
    public function seePageInDatabase(array $criteria): void
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
     * @param array<string,mixed> $criteria An array of search criteria.
     *
     * @throws JsonException If there's any issue debugging the failure.
     */
    public function seePostInDatabase(array $criteria): void
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
     * @param array<string,mixed> $criteria An array of search criteria.
     */
    public function dontSeePageInDatabase(array $criteria): void
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
     * @param array<string,mixed> $criteria An array of search criteria.
     *
     * @throws JsonException If there's any issue debugging the failure.
     */
    public function dontSeePostInDatabase(array $criteria): void
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
     * @param array<string,mixed> $criteria An array of search criteria.
     *
     * @throws JsonException If there's any issue debugging the failure.
     */
    public function seeCommentInDatabase(array $criteria): void
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
     * @param array<string,mixed> $criteria The search criteria.
     *
     * @throws JsonException If there's any issue debugging the failure.
     */
    public function dontSeeCommentInDatabase(array $criteria): void
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
     * // Assert a specified meta for a comment exists.
     * $I->seeCommentMetaInDatabase(['comment_ID' => $commentId, 'meta_key' => 'karma', 'meta_value' => 23]);
     * // Assert the comment has at least one meta set.
     * $I->seeCommentMetaInDatabase(['comment_ID' => $commentId]);
     * ```
     *
     * @param array<string,mixed> $criteria An array of search criteria.
     *
     * @throws JsonException If there's any issue debugging the failure.
     */
    public function seeCommentMetaInDatabase(array $criteria): void
    {
        $tableName = $this->grabPrefixedTableNameFor('commentmeta');
        if (!empty($criteria['meta_value'])) {
            $criteria['meta_value'] = Serializer::maybeSerialize($criteria['meta_value']);
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
     * @param array<string,mixed> $criteria An array of search criteria.
     *
     * @throws JsonException If there's any issue debugging the failure.
     */
    public function dontSeeCommentMetaInDatabase(array $criteria): void
    {
        $tableName = $this->grabPrefixedTableNameFor('commentmeta');
        if (!empty($criteria['meta_value'])) {
            $criteria['meta_value'] = Serializer::maybeSerialize($criteria['meta_value']);
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
     * @param array<string,mixed> $criteria An array of search criteria.
     *
     * @throws JsonException If there's any issue debugging the failure.
     */
    public function seeUserMetaInDatabase(array $criteria): void
    {
        $tableName = $this->grabPrefixedTableNameFor('usermeta');
        if (!empty($criteria['meta_value'])) {
            $criteria['meta_value'] = Serializer::maybeSerialize($criteria['meta_value']);
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
     * @param array<string,mixed> $criteria An array of search criteria.
     *
     * @throws JsonException If there's any issue debugging the failure.
     */
    public function dontSeeUserMetaInDatabase(array $criteria): void
    {
        $tableName = $this->grabPrefixedTableNameFor('usermeta');
        if (!empty($criteria['meta_value'])) {
            $criteria['meta_value'] = Serializer::maybeSerialize($criteria['meta_value']);
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
     * @param array<string,mixed> $criteria An array of search criteria.
     */
    public function dontHaveLinkInDatabase(array $criteria): void
    {
        $tableName = $this->grabPrefixedTableNameFor('links');
        $this->dontHaveInDatabase($tableName, $criteria);
    }

    /**
     * Deletes a database entry.
     *
     * @example
     * ```php
     * $I->dontHaveInDatabase('custom_table', ['book_ID' => 23, 'book_genre' => 'fiction']);
     * ```
     *
     * @param array<string,mixed> $criteria An associative array of the column names and values to use as deletion
     *                                      criteria.
     * @param string $table                 The table name.
     */
    public function dontHaveInDatabase(string $table, array $criteria): void
    {
        try {
            $this->_getDriver()->deleteQueryByCriteria($table, $criteria);
        } catch (Exception) {
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
     * @param array<string,mixed> $criteria An array of search criteria.
     */
    public function dontHaveTermRelationshipInDatabase(array $criteria): void
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
     * @param array<string,mixed> $criteria An array of search criteria.
     */
    public function dontHaveTermTaxonomyInDatabase(array $criteria): void
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
     * @param array<string,mixed> $criteria An array of search criteria.
     */
    public function dontHaveUserMetaInDatabase(array $criteria): void
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
     * @param int $userId      The ID of th user to get the meta for.
     * @param string $meta_key The meta key to fetch the value for.
     * @param bool $single     Whether to return a single value or an array of values.
     *
     * @return array<int,mixed>|mixed An array of the different meta key values or a single value if `$single` is set
     *                                to `true`.
     *
     * @throws Exception If the search criteria is incoherent.
     */
    public function grabUserMetaFromDatabase(int $userId, string $meta_key, bool $single = false): mixed
    {
        $table = $this->grabPrefixedTableNameFor('usermeta');
        $meta = $this->grabAllFromDatabase(
            $table,
            'meta_key, meta_value',
            ['user_id' => $userId, 'meta_key' => $meta_key]
        );
        if (empty($meta)) {
            return $single ? '' : [];
        }

        $normalized = [];
        foreach ($meta as $row) {
            $value = Serializer::maybeUnserialize($row['meta_value']);

            if ($single) {
                return $value;
            }

            $normalized[] =$value;
        }

        return $normalized;
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
     * @param string $table                 The table to grab the values from.
     * @param string $column                The column to fetch.
     * @param array<string,mixed> $criteria The search criteria.
     *
     * @return array<array<string,mixed>> An array of results.
     *
     * @throws Exception If the criteria is inconsistent.
     */
    public function grabAllFromDatabase(string $table, string $column, array $criteria): array
    {
        $query = $this->_getDriver()->select($column, $table, $criteria);

        return $this->_getDriver()->executeQuery($query, array_values($criteria))->fetchAll(PDO::FETCH_ASSOC);
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
     * @param mixed $value      The transient value.
     *
     * @return int The inserted option `option_id`.
     */
    public function haveTransientInDatabase(string $transient, mixed $value): int
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
     * @param string $option_name The option name.
     * @param mixed $option_value The option value; if an array or object it will be serialized.
     * @param string $autoload    Whether the option should be autoloaded by WordPress or not.
     *
     * @return int The inserted option `option_id`
     */
    public function haveOptionInDatabase(string $option_name, mixed $option_value, string $autoload = 'yes'): int
    {
        $table = $this->grabPrefixedTableNameFor('options');
        $this->dontHaveInDatabase($table, ['option_name' => $option_name]);
        $option_value = Serializer::maybeSerialize($option_value);

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
     */
    public function dontHaveTransientInDatabase(string $transient): void
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
     * @param string $key       The option name.
     * @param mixed|null $value If set the option will only be removed if its value matches the passed one.
     */
    public function dontHaveOptionInDatabase(string $key, mixed $value = null): void
    {
        $tableName = $this->grabPrefixedTableNameFor('options');
        $criteria['option_name'] = $key;
        if (!empty($value)) {
            $criteria['option_value'] = Serializer::maybeUnserialize($value);
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
     * @param string $key  The name of the option to insert.
     * @param mixed $value The value to insert for the option.
     *
     * @return int The inserted option `option_id`.
     */
    public function haveSiteOptionInDatabase(string $key, mixed $value): int
    {
        $currentBlogId = $this->blogId;
        $this->useMainBlog();
        $option_id = $this->haveOptionInDatabase('_site_option_' . $key, $value);

        if (empty($currentBlogId)) {
            $this->useMainBlog();
        } else {
            $this->useBlog($currentBlogId);
        }

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
     */
    public function useMainBlog(): void
    {
        $this->useBlog(1);
    }

    /**
     * Sets the blog to be used.
     *
     * This has nothing to do with WordPress `switch_to_blog` function, this code will affect the table prefixes used.
     *
     * @param int $blogId The ID of the blog to use.
     * @throws ModuleException If the blog ID is not an integer greater than or equal to 0.
     * @example
     * ```php
     * // Switch to the blog with ID 23.
     * $I->useBlog(23);
     * // Switch back to the main blog.
     * $I->useMainBlog();
     * // Switch to the main blog using this method.
     * $I->useBlog(1);
     * ```
     */
    public function useBlog(int $blogId = 1): void
    {
        if ($blogId < 0) {
            throw new InvalidArgumentException('Id must be an integer greater than 0');
        }

        if ($blogId === 1) {
            $this->blogId = 1;
            $this->blogUrl = $this->grabSiteUrl();
            return;
        }

        $this->blogId = $blogId;
        $this->blogUrl = $this->grabBlogUrl($blogId);
    }

    /**
     * Gets the blog URL from the Blog ID.
     *
     * @param int $blogId The ID of the blog to get the URL for.
     *
     * @return string The blog URL.
     * @throws ModuleException If the blog ID is not found in the database.
     *
     * @example
     * ```php
     * // Get the URL for the main blog.
     * $mainBlogUrl = $I->grabBlogUrl();
     * // Get the URL for the blog with ID 23.
     * $blog23Url = $I->grabBlogUrl(23);
     * ```
     */
    public function grabBlogUrl(int $blogId = 1): string
    {
        if ($blogId === 0) {
            return $this->grabSiteUrl();
        }

        $domain = $this->grabFromDatabase(
            $this->grabPrefixedTableNameFor('blogs'),
            'domain',
            ['blog_id' => $blogId]
        );

        $path = $this->grabFromDatabase(
            $this->grabPrefixedTableNameFor('blogs'),
            'path',
            ['blog_id' => $blogId]
        );

        if (!($domain && $path && is_string($domain) && is_string($path))) {
            throw new ModuleException(
                $this,
                "Couldn't find the blog with ID {$blogId} in the database."
            );
        }

        /** @var array{url: string} $config Validated module config. */
        $config = $this->config;
        $siteUrl = $config['url'];
        $siteScheme = parse_url($siteUrl, PHP_URL_SCHEME);

        // In the site URL replace HOST and PATH with the blog's domain and path.
        $blogUrl = rtrim($siteScheme . '://' . $domain . $path, '/');

        return $blogUrl;
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
     * @param string $key       The option name.
     * @param mixed|null $value If set the option will only be removed it its value matches the specified one.
     */
    public function dontHaveSiteOptionInDatabase(string $key, mixed $value = null): void
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
     * @param string $key  The key of the site transient to insert, w/o the `_site_transient_` prefix.
     * @param mixed $value The value to insert; if serializable the value will be serialized.
     *
     * @return int The inserted transient `option_id`
     */
    public function haveSiteTransientInDatabase(string $key, mixed $value): int
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
     */
    public function dontHaveSiteTransientInDatabase(string $key): void
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
     * @return mixed The value of the option stored in the database, unserialized if serialized.
     */
    public function grabSiteOptionFromDatabase(string $key): mixed
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
    public function grabOptionFromDatabase(string $option_name): mixed
    {
        $table = $this->grabPrefixedTableNameFor('options');
        $option_value = $this->grabFromDatabase($table, 'option_value', ['option_name' => $option_name]);

        return empty($option_value) ? '' : Serializer::maybeUnserialize($option_value);
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
     * @return mixed The value of the site transient. If the value is serialized it will be unserialized.
     */
    public function grabSiteTransientFromDatabase(string $key): mixed
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
     * @param string $key       The name of the transient to check for, w/o the `_site_transient_` prefix.
     * @param mixed|null $value If provided then the assertion will include the value.
     *
     * @throws JsonException
     */
    public function seeSiteSiteTransientInDatabase(string $key, mixed $value = null): void
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
     * Checks if an option is in the database for the current blog, either by criteria or by name and value.
     *
     * If checking for an array or an object then the serialized version will be checked for.
     *
     * @example
     * ```php
     * // Checks an option is in the database.
     * $I->seeOptionInDatabase('tables_version');
     * // Checks an option is in the database and has a specific value.
     * $I->seeOptionInDatabase('tables_version', '1.0');
     * $I->seeOptionInDatabase(['option_name' => 'tables_version', 'option_value' => 1.0']);
     * ```
     *
     * @param array<string,mixed>|string $criteriaOrName An array of search criteria or the option name.
     * @param mixed|null $value                          The optional value to try and match, only used if the option
     *                                                   name is provided.
     *
     *
     * @throws JsonException If there's any issue debugging the failure.
     */
    public function seeOptionInDatabase(array|string $criteriaOrName, mixed $value = null): void
    {
        $criteria = $this->normalizeOptionCriteria($criteriaOrName, $value);
        $tableName = $this->grabPrefixedTableNameFor('options');

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
     * @param array<string,mixed>|string $criteriaOrName An array of search criteria or the option name.
     * @param mixed|null $value                          The optional value to try and match, only used if the option
     *                                                   name is provided.
     *
     *
     * @throws JsonException If there's any issue debugging the failure.
     */
    public function seeSiteOptionInDatabase(array|string $criteriaOrName, mixed $value = null): void
    {
        $currentBlogId = $this->blogId;
        $this->useMainBlog();

        $criteria = $this->buildSiteOptionCriteria($criteriaOrName, $value);

        $this->seeOptionInDatabase($criteria);
        $this->useBlog($currentBlogId);
    }

    /**
     * Inserts many posts in the database returning their IDs.
     *
     * @example
     * ```php
     * // Insert 3 random posts.
     * $I->haveManyPostsInDatabase(3);
     * // Insert 3 posts with generated titles.
     * $I->haveManyPostsInDatabase(3, ['post_title' => 'Test post {{n}}']);
     * ```
     *
     * @param array<string,mixed> $overrides {
     *                                       An array of values to override the defaults.
     *                                       The `{{n}}` placeholder can be used to have the post count inserted in its
     *                                       place; e.g. `Post Title - {{n}}` will be set to `Post Title - 0` for the
     *                                       first post,
     *                                       `Post Title - 1` for the second one and so on.
     *                                       The same applies to meta values as well.
     *
     * @param int $count                     The number of posts to insert.
     *
     * @return array<int> An array of the inserted post IDs.
     *
     * @throws ModuleException If there's any issue wit the post insertion.
     */
    public function haveManyPostsInDatabase(int $count, array $overrides = []): array
    {
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
     * @throws ModuleException
     */
    protected function setTemplateData(array $overrides = []): array
    {
        if (empty($overrides['template_data'])) {
            $this->templateData = [];
        } else {
            if (!is_array($overrides['template_data'])) {
                throw new ModuleException(
                    $this,
                    'The template data must be an array, ' . gettype($overrides['template_data']) . ' given.'
                );
            }
            $this->templateData = $overrides['template_data'];
            $overrides = array_diff_key($overrides, ['template_data' => []]);
        }

        return $overrides;
    }

    /**
     * Replaces each occurrence of the `{{n}}` placeholder with the specified number.
     *
     * @param string|array<string|int,mixed> $input The entry, or entries, to replace the placeholder in.
     * @param int $i                                The value to replace the placeholder with.
     *
     * @return array<string|int,mixed> The input array with any `{{n}}` placeholder replaced with a number.
     */
    protected function replaceNumbersInArray(string|array $input, int $i): array
    {
        $out = [];
        foreach ((array)$input as $key => $value) {
            $updatedKey = is_string($key) ? $this->replaceNumbersInString($key, $i) : $key;
            if (is_array($value)) {
                $out[$updatedKey] = $this->replaceNumbersInArray($value, $i);
            } else {
                $out[$updatedKey] = is_string($value) ? $this->replaceNumbersInString($value, $i) : $value;
            }
        }

        return $out;
    }

    /**
     * Replaces the `{{n}}` placeholder with the specified number.
     *
     * @param string $template The string to replace the placeholder in.
     * @param int $i           The value to replace the placeholder with.
     *
     * @return string The string with replaces placeholders.
     */
    protected function replaceNumbersInString(string $template, int $i): string
    {
        $fnArgs = ['n' => $i];
        $data = array_merge($this->templateData, $fnArgs);

        return Strings::renderString($template, $data, $fnArgs);
    }

    /**
     * Checks for a term in the database.
     * Looks up the `terms` and `term_taxonomy` prefixed tables.
     *
     * @example
     * ```php
     * $I->seeTermInDatabase(['slug' => 'genre--fiction']);
     * $I->seeTermInDatabase(['name' => 'Fiction', 'slug' => 'genre--fiction']);
     * ```
     *
     * @param array<string,mixed> $criteria An array of criteria to search for the term, can be columns from the `terms`
     *                                      and the `term_taxonomy` tables.
     *
     * @throws JsonException If there's any issue debugging the failure.
     */
    public function seeTermInDatabase(array $criteria): void
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
     * @param array<string,mixed> $criteria An array of search criteria.
     * @param bool $purgeMeta               Whether the terms meta should be purged along side with the meta or not.
     *
     * @throws Exception If there's an issue removing the rows.
     */
    public function dontHaveTermInDatabase(array $criteria, bool $purgeMeta = true): void
    {
        try {
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
                    $ids = array_column($ids, 'term_id');
                    foreach ($ids as $id) {
                        if (!is_numeric($id)) {
                            continue;
                        }
                        $this->dontHaveTermMetaInDatabase(['term_id' => (int)$id]);
                    }
                }
            }
            $this->dontHaveInDatabase($this->grabTermsTableName(), $termTableCriteria);
            $this->dontHaveInDatabase($this->grabTermTaxonomyTableName(), $termTaxonomyTableCriteria);
            $this->dontHaveInDatabase(
                $this->grabTermRelationshipsTableName(),
                array_intersect_key($criteria, array_flip($termRelationshipsKeys))
            );
        } catch (Exception $e) {
            throw new ModuleException($this, $e->getMessage());
        }
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
     */
    public function dontHaveTermMetaInDatabase(array $criteria): void
    {
        $this->dontHaveInDatabase($this->grabTermMetaTableName(), $criteria);
    }

    /**
     * Makes sure a term is not in the database.
     *
     * Looks up both the `terms` table and the `term_taxonomy` tables.
     *
     * @example
     * ```php
     * // Asserts a 'fiction' term is not in the database.
     * $I->dontSeeTermInDatabase(['name' => 'fiction']);
     * // Asserts a 'fiction' term with slug 'genre--fiction' is not in the database.
     * $I->dontSeeTermInDatabase(['name' => 'fiction', 'slug' => 'genre--fiction']);
     * ```
     *
     * @param array<string,mixed> $criteria An array of criteria to search for the term, can be columns from the `terms`
     *                                      and the `term_taxonomy` tables.
     *
     * @throws JsonException If there's any issue debugging the failure.
     */
    public function dontSeeTermInDatabase(array $criteria): void
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
     * @param int $count                     The number of comments to insert.
     * @param int $comment_post_ID           The comment parent post ID.
     * @param array<string,mixed> $overrides An associative array to override the defaults.
     *
     * @return array<int> An array containing the inserted comments IDs.
     */
    public function haveManyCommentsInDatabase(int $count, int $comment_post_ID, array $overrides = []): array
    {
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
     * @param int $comment_post_ID          The id of the post the comment refers to.
     * @param array<int|string,mixed> $data The comment data overriding default and random generated values.
     *
     * @return int The inserted comment `comment_id`.
     * @throws ModuleException
     */
    public function haveCommentInDatabase(int $comment_post_ID, array $data = []): int
    {
        $has_meta = !empty($data['meta']);
        $meta = [];
        if ($has_meta) {
            $meta = $data['meta'];
            if (!is_array($meta)) {
                throw new ModuleException(
                    $this,
                    'The meta payload must be an array'
                );
            }
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
     * @param int $comment_id   The ID of the comment to insert the meta for.
     * @param string $meta_key  The key of the comment meta to insert.
     * @param mixed $meta_value The value of the meta to insert, if serializable it will be serialized.
     *
     * @return int The inserted comment meta ID.
     */
    public function haveCommentMetaInDatabase(int $comment_id, string $meta_key, mixed $meta_value): int
    {
        return $this->haveInDatabase($this->grabCommentmetaTableName(), [
            'comment_id' => $comment_id,
            'meta_key' => $meta_key,
            'meta_value' => Serializer::maybeSerialize($meta_value),
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
    public function grabCommentmetaTableName(): string
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
     * @param string $table                 The table to count the rows in.
     * @param array<string,mixed> $criteria Search criteria, if empty all table rows will be counted.
     *
     * @return int The number of table rows matching the search criteria.
     */
    public function countRowsInDatabase(string $table, array $criteria = []): int
    {
        return $this->countInDatabase($table, $criteria);
    }

    /**
     * Removes an entry from the comments table.
     *
     * @example
     * ```php
     * $I->dontHaveCommentInDatabase(['comment_post_ID' => 23, 'comment_url' => 'http://example.copm']);
     * ```
     *
     * @param array<string,mixed> $criteria An array of search criteria.
     * @param bool $purgeMeta               If set to `true` then the meta for the comment will be purged too.
     *
     *
     * @throws Exception In case of incoherent query criteria.
     */
    public function dontHaveCommentInDatabase(array $criteria, bool $purgeMeta = true): void
    {
        $table = $this->grabCommentsTableName();
        if ($purgeMeta) {
            $ids = $this->grabAllFromDatabase($table, 'comment_id', $criteria);
            if (!empty($ids)) {
                $ids = array_column($ids, 'comment_id');
                foreach ($ids as $id) {
                    if (!is_numeric($id)) {
                        continue;
                    }
                    $this->dontHaveCommentMetaInDatabase(['comment_id' => (int)$id]);
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
    public function grabCommentsTableName(): string
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
     * @param array<string,mixed> $criteria An array of search criteria.
     */
    public function dontHaveCommentMetaInDatabase(array $criteria): void
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
     * @param int $count                     The number of links to insert.
     * @param array<string,mixed> $overrides Overrides for the default arguments.
     *
     * @return array<int> An array of inserted `link_id`s.
     */
    public function haveManyLinksInDatabase(int $count, array $overrides = []): array
    {
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
     * @param array<int|string,mixed> $overrides The data to insert.
     *
     * @return int The inserted link `link_id`.
     */
    public function haveLinkInDatabase(array $overrides = []): int
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
    public function grabLinksTableName(): string
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
     * @param int $count                     The number of users to insert.
     * @param string $user_login             The user login name.
     * @param string $role                   The user role.
     * @param array<string,mixed> $overrides An array of values to override the default ones.
     *
     * @return array<int> An array of user IDs.
     */
    public function haveManyUsersInDatabase(
        int $count,
        string $user_login,
        string $role = 'subscriber',
        array $overrides = []
    ): array {
        $ids = [];
        $overrides = $this->setTemplateData($overrides);
        for ($i = 0; $i < $count; $i++) {
            $thisOverrides = $this->replaceNumbersInArray($overrides, $i);
            $thisUserLogin = !str_contains(
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
     * @param string|array<string> $role         The user role slug(s), e.g. `administrator` or `['author', 'editor']`;
     *                                           defaults to `subscriber`. If more than one role is specified, then the
     *                                           first role in the list will be the user primary role and the
     *                                           `wp_user_level` will be set to that role.
     * @param array<int|string,mixed> $overrides An associative array of column names and values overriding defaults
     *                                           in the `users` and `usermeta` table.
     *
     * @param string $user_login                 The user login name.
     *
     * @return int The inserted user ID.
     *
     * @throws JsonException If there's any issue debugging the failure.
     * @throws ModuleException
     *
     * @see WPDb::haveUserCapabilitiesInDatabase() for the roles and caps options.
     */
    public function haveUserInDatabase(
        string $user_login,
        string|array $role = 'subscriber',
        array $overrides = []
    ): int {
        // Support `meta` and `meta_input` for compatibility w/ format used by Core user factory.
        $hasMeta = !empty($overrides['meta']) || !empty($overrides['meta_input']);
        $meta = [];
        if ($hasMeta) {
            $meta = $overrides['meta'] ?? $overrides['meta_input'];

            if (!is_array($meta)) {
                throw new ModuleException(
                    $this,
                    'The meta payload must be an array'
                );
            }

            unset($overrides['meta'], $overrides['meta_input']);
        }

        $userTableData = User::generateUserTableDataFrom($user_login, $overrides);
        $this->debugSection('Generated users table data', json_encode($userTableData));
        $userId = $this->haveInDatabase($this->grabUsersTableName(), $userTableData);

        // Handle the user capabilities and associated meta values.
        $this->haveUserCapabilitiesInDatabase($userId, $role);

        // Set up the user meta, apply the user-set overrides.
        foreach (User::generateUserMetaTableDataFrom($user_login, $meta) as $key => $value) {
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
    public function getUsersTableName(): string
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
    public function grabUsersTableName(): string
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
     * @param int $userId                                              The ID of the user to set the capabilities of.
     * @param string|array<string>|array<int,array<string,bool>> $role Either a role string (e.g.
     *                                                                 `administrator`),an associative array of blog
     *                                                                 IDs/roles for a multisite installation (e.g. `[1
     *                                                                 =>
     *                                                                 'administrator`, 2 => 'subscriber']`).
     *
     * @return array<int|string,array<int>|int> An array of inserted `meta_id`.
     */
    public function haveUserCapabilitiesInDatabase(int $userId, string|array $role): array
    {
        $insert = User::buildCapabilities($role, $this->grabTablePrefix());

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
     * @param int $userId        The user ID.
     * @param string $meta_key   The meta key to set the value for.
     * @param mixed $meta_value  Either a single value or an array of values; objects will be serialized while array of
     *                           values will trigger the insertion of multiple rows.
     *
     * @return array<int> An array of inserted `umeta_id`s.
     */
    public function haveUserMetaInDatabase(int $userId, string $meta_key, mixed $meta_value): array
    {
        $ids = [];
        $meta_values = is_array($meta_value) ? $meta_value : [$meta_value];

        foreach ($meta_values as $value) {
            $data = [
                'user_id' => $userId,
                'meta_key' => $meta_key,
                'meta_value' => Serializer::maybeSerialize($value),
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
    public function grabUsermetaTableName(): string
    {
        return $this->grabPrefixedTableNameFor('usermeta');
    }

    /**
     * Sets the user access level meta in the database for a user.
     *
     * @example
     * ```php
     * $userId = $I->haveUserInDatabase('luca', 'editor');
     * $moreThanAnEditorLessThanAnAdmin = 8;
     * $I->haveUserLevelsInDatabase($userId, $moreThanAnEditorLessThanAnAdmin);
     * ```
     *
     * @param int $userId                                                                 The ID of the user to set the
     *                                                                                    level for.
     * @param string|array<string>|array<string,bool>|array<int,array<string,bool>> $role Either a user role (e.g.
     *                                                                                    `editor`), a list of user
     *                                                                                    roles and capabilities (e.g.
     *                                                                                    `['editor' => true,
     *                                                                                    'edit_themes' => true]`) or a
     *                                                                                    list of blog IDs and, for
     *                                                                                    each, a list of user roles in
     *                                                                                    the previously specified
     *                                                                                    formats.
     *
     *
     * @return array<int> An array of inserted `meta_id`.
     */
    public function haveUserLevelsInDatabase(int $userId, array|string $role): array
    {
        $roles = User::buildCapabilities($role, $this->grabTablePrefix());

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
     * @param int $count                     The number of terms to insert.
     * @param string $name                   The term name template, can include the `{{n}}` placeholder.
     * @param string $taxonomy               The taxonomy to insert the terms for.
     * @param array<string,mixed> $overrides An associative array of default overrides.
     *
     * @return array<array<int>> An array of arrays containing `term_id` and `term_taxonomy_id` of the inserted terms.
     */
    public function haveManyTermsInDatabase(int $count, string $name, string $taxonomy, array $overrides = []): array
    {
        if (!is_int($count)) {
            throw new InvalidArgumentException('Count must be an integer value');
        }
        $ids = [];
        $overrides = $this->setTemplateData($overrides);
        for ($i = 0; $i < $count; $i++) {
            $thisName = !str_contains(
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
     * @throws JsonException If there's any issue debugging the failure.
     */
    public function seeTermTaxonomyInDatabase(array $criteria): void
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
     * @throws JsonException If there's any issue debugging the failure.
     */
    public function dontSeeTermTaxonomyInDatabase(array $criteria): void
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
     * @throws JsonException If there's any issue debugging the failure.
     */
    public function seeTermMetaInDatabase(array $criteria): void
    {
        if (!empty($criteria['meta_value'])) {
            $criteria['meta_value'] = Serializer::maybeSerialize($criteria['meta_value']);
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
     * @throws JsonException If there's any issue debugging the failure.
     */
    public function dontSeeTermMetaInDatabase(array $criteria): void
    {
        if (!empty($criteria['meta_value'])) {
            $criteria['meta_value'] = Serializer::maybeSerialize($criteria['meta_value']);
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
     */
    public function seeTableInDatabase(string $table): void
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
     *
     * @throws JsonException If there's any issue debugging the failure.
     */
    protected function _seeTableInDatabase(string $table): bool
    {
        $dbh = $this->_getDbh();
        $sth = $dbh->prepare('SHOW TABLES LIKE :table');
        $this->debugSection('Query', $sth->queryString);
        $sth->execute(['table' => $table]);
        $count = $sth->rowCount();

        return $count === 1;
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
    public function grabBlogVersionsTableName(): string
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
    public function grabSiteMetaTableName(): string
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
    public function grabSignupsTableName(): string
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
    public function grabRegistrationLogTableName(): string
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
    public function grabSiteTableName(): string
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
     * @throws JsonException If there's any issue debugging the failure.
     */
    public function seeBlogInDatabase(array $criteria): void
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
    public function grabBlogsTableName(): string
    {
        return $this->grabPrefixedTableNameFor('blogs');
    }

    /**
     * Prepares the array of criteria that will be used to search a blog in the database.
     *
     * @param array<string,mixed> $criteria An input array of blog search criteria.
     *
     * @return array<string,mixed> The prepared array of blog search criteria.
     * @throws ModuleException
     */
    protected function prepareBlogCriteria(array $criteria): array
    {
        // Allow using the non leading/trailing slash format to search for sub-domains.
        if (isset($criteria['path']) && $criteria['path'] !== '/') {
            if (!is_string($criteria['path'])) {
                throw new ModuleException($this, 'The `path` criteria must be a string.');
            }
            $criteria['path'] = '/' . trim($criteria['path'], '/') . '/';
        }
        return $criteria;
    }

    /**
     * Inserts many blogs in the database.
     *
     * @example
     *      ```php
     *      $blogIds = $I->haveManyBlogsInDatabase(3, ['domain' =>'test-{{n}}']);
     *      foreach($blogIds as $blogId){
     *      $I->useBlog($blogId);
     *      $I->haveManuPostsInDatabase(3);
     * }
     * ```
     *
     * @param int $count                     The number of blogs to create.
     *
     * @param array<string,mixed> $overrides An array of values to override the default ones; `{{n}}` will be replaced
     *                                       by the count.
     * @param bool $subdomain                Whether the new blogs should be created as a subdomain or subfolder.
     *
     * @return array<int> An array of inserted blogs `blog_id`s.
     * @throws JsonException
     * @throws ModuleException
     */
    public function haveManyBlogsInDatabase(int $count, array $overrides = [], bool $subdomain = true): array
    {
        $blogIds = [];
        $overrides = $this->setTemplateData($overrides);
        for ($i = 0; $i < $count; $i++) {
            $blogOverrides = $this->replaceNumbersInArray($overrides, $i);
            $domainOrPath = 'blog-' . $i;

            if (isset($blogOverrides['slug']) && is_string($blogOverrides['slug'])) {
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
     * @param string $domainOrPath               The subdomain or the path to the be used for the blog.
     * @param array<int|string,mixed> $overrides An array of values to override the defaults.
     * @param bool $subdomain                    Whether the new blog should be created as a subdomain (`true`)
     *                                           or subfolder (`true`)
     *
     * @return int The inserted blog `blog_id`.
     *
     * @throws JsonException
     * @throws ModuleException
     */
    public function haveBlogInDatabase(string $domainOrPath, array $overrides = [], bool $subdomain = true): int
    {
        $base = Blog::makeDefaults();
        if ($subdomain) {
            $base['domain'] = str_contains($domainOrPath, $this->getSiteDomain())
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
            if (!is_string($data['path'])) {
                throw new ModuleException($this, 'The `path` override must be a string.');
            }
            $data['path'] = '/' . FS::unleadslashit(FS::untrailslashit($data['path'])) . '/';
        }

        $blogId = $this->haveInDatabase($this->grabBlogsTableName(), $data);
        $this->scaffoldBlogTables($blogId, $domainOrPath, (bool)$subdomain);

        try {
            $fs = $this->getWpFilesystemModule();
            $this->debug('Scaffolding blog uploads directories.');
            $fs->makeUploadsDir("sites/{$blogId}");
        } catch (ModuleException) {
            $this->debugSection(
                'Filesystem',
                'Could not scaffold blog directories: WPFilesystem module not loaded in suite.'
            );
        }

        // A table DROP and INSERT will trigger a schema change and will trigger a transaction commit.
        // Reconnecting now is a good insurance against schema change related errors.
        $this->reconnectCurrentDatabase();

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
    public function getSiteDomain(): string
    {
        /** @var array{url: string} $config Validated module config. */
        $config = $this->config;
        $domainFrags = explode('//', $config['url']);
        return end($domainFrags);
    }

    /**
     * Scaffolds the blog tables to support and create a blog.
     *
     * @param int $blogId          The blog ID.
     * @param string $domainOrPath Either the path or the sub-domain of the blog to create.
     * @param bool $isSubdomain    Whether to create a sub-folder or a sub-domain blog.
     *
     * @throws PDOException If there's any issue executing the query.
     * @throws JsonException If there's any issue debugging the query.
     */
    protected function scaffoldBlogTables(int $blogId, string $domainOrPath, bool $isSubdomain = true): void
    {
        $stylesheet = $this->grabOptionFromDatabase('stylesheet');

        if (!is_string($stylesheet)) {
            $stylesheet = '';
        }

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
        $pdo = $this->_getDbh();

        $tables = new Tables(get_class($this->drivers[$this->currentDatabase]));

        $dropQueries = $tables->getBlogDropQueries($this->config['tablePrefix'], $blogId);
        foreach ($dropQueries as $dropQuery) {
            $this->debugSection('Query', $dropQuery);
            if ($pdo->exec($dropQuery) === false) {
                throw new ModuleException($this, 'Failed to drop blog tables: ' . ($pdo->errorInfo()[2] ?? 'n/a'));
            }
        }

        $scaffoldQueries = $tables->getBlogScaffoldQueries($this->config['tablePrefix'], $blogId, $data);
        foreach ($scaffoldQueries as $scaffoldQuery) {
            $this->debugSection('Query', $scaffoldQuery);
            if ($pdo->exec($scaffoldQuery) === false) {
                throw new ModuleException($this, 'Failed to scaffold blog tables: ' . ($pdo->errorInfo()[2] ?? 'n/a'));
            }
        }

        $this->scaffoldedBlogIds[] = $blogId;
    }

    /**
     * Gets the WPFilesystem module.
     *
     * @return WPFilesystem The filesystem module instance if loaded in the suite.
     *
     * @throws ModuleException If the WPFilesystem module is not loaded in the suite.
     */
    protected function getWpFilesystemModule(): WPFilesystem
    {
        try {
            /** @var WPFilesystem $fs */
            $fs = $this->getModule('\\' . WPFilesystem::class);

            return $fs;
        } catch (ModuleException) {
            $message = 'This method requires the WPFilesystem module.';
            throw new ModuleException(__CLASS__, $message);
        }
    }

    /**
     * Removes one ore more blogs from the database.
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
     * @param bool $removeTables            Remove the blog tables.
     * @param bool $removeUploads           Remove the blog uploads; requires the `WPFilesystem` module.
     *
     * @throws JsonException If there's any issue debugging the query.
     */
    public function dontHaveBlogInDatabase(array $criteria, bool $removeTables = true, bool $removeUploads = true): void
    {
        $criteria = $this->prepareBlogCriteria($criteria);

        $blogIds = $this->grabAllFromDatabase($this->grabBlogsTableName(), 'blog_id', $criteria);

        foreach (array_column($blogIds, 'blog_id') as $blogId) {
            if (empty($blogId) || !is_numeric($blogId)) {
                $this->debug(message: 'No blog found matching criteria ' .
                    json_encode($criteria, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT));
                return;
            }

            $blogId = (int)$blogId;

            if ($removeTables) {
                foreach ($this->grabBlogTableNames($blogId) as $tableName) {
                    $this->dontHaveTableInDatabase($tableName);
                }
            }

            if ($removeUploads) {
                try {
                    $fs = $this->getWpFilesystemModule();
                    $fs->deleteUploadedDir($fs->getBlogUploadsPath($blogId));
                } catch (ModuleException) {
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
     * @example
     *      ```php
     *      $blogId = $I->haveBlogInDatabase('test');
     *      $tables = $I->grabBlogTableNames($blogId);
     *      $options = array_filter($tables, function($tableName){
     *      return str_pos($tableName, 'options') !== false;
     * });
     * ```
     *
     * @param int $blogId The ID of the blog to fetch the tables for.
     *
     * @return array<string> An array of tables for the blog, it does not include the tables common to all blogs; an
     *                       empty array if the tables for the blog do not exist.
     *
     * @throws Exception If there is any error while preparing the query.
     */
    public function grabBlogTableNames(int $blogId): array
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
     *
     * @throws Exception If there is an error while dropping the table.
     */
    public function dontHaveTableInDatabase(string $fullTableName): void
    {
        $drop = "DROP TABLE {$fullTableName}";

        try {
            $this->_getDriver()->executeQuery($drop, []);
        } catch (PDOException $e) {
            if (!str_contains($e->getMessage(), 'table or view not found')) {
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
     * @throws JsonException If there's any issue debugging the query.
     */
    public function dontSeeBlogInDatabase(array $criteria): void
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
     * @param string $stylesheet           The theme stylesheet slug, e.g. `twentysixteen`.
     * @param string|null $template        The theme template slug, e.g. `twentysixteen`, defaults to `$stylesheet`.
     *
     * @param string|null $themeName       The theme name, e.g. `Acme`, defaults to the "title" version of
     *                                     `$stylesheet`.
     */
    public function useTheme(string $stylesheet, string $template = null, string $themeName = null): void
    {
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
     * @param string $slug                   The menu slug.
     * @param string $location               The theme menu location the menu will be assigned to.
     * @param array<string,mixed> $overrides An array of values to override the defaults.
     *
     * @return array<int> An array containing the created menu `term_id` and `term_taxonomy_id`.
     * @throws ModuleException
     */
    public function haveMenuInDatabase(string $slug, string $location, array $overrides = []): array
    {
        if (empty($this->stylesheet)) {
            throw new RuntimeException('Stylesheet must be set to add menus, use `useTheme` first.');
        }

        if (empty($overrides['title'])) {
            $title = ucwords($slug, ' -_');
        } else {
            if (!is_string($overrides['title'])) {
                throw new ModuleException(__CLASS__, 'Menu title must be a string.');
            }
            $title = $overrides['title'];
        }
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
     * @example
     * ```php
     * $I->haveMenuInDatabase('test', 'sidebar');
     * $I->haveMenuItemInDatabase('test', 'Test one', 0);
     * $I->haveMenuItemInDatabase('test', 'Test two', 1);
     * ```
     *
     * @param string $title                  The menu item title.
     * @param int|null $menuOrder            An optional menu order, `1` based.
     * @param array<string,mixed> $meta      An associative array that will be prefixed with `_menu_item_` for the item
     *                                       post meta.
     * @param string $menuSlug               The menu slug the item should be added to.
     *
     * @return int The menu item post `ID`
     * @throws ModuleException If there's an issue inserting the database row.
     */
    public function haveMenuItemInDatabase(
        string $menuSlug,
        string $title,
        int $menuOrder = null,
        array $meta = []
    ): int {
        if (empty($this->stylesheet)) {
            throw new RuntimeException('Stylesheet must be set to add menus, use `useTheme` first.');
        }
        if (!array_key_exists($menuSlug, $this->menus[$this->stylesheet])) {
            throw new RuntimeException("Menu $menuSlug is not a registered menu for the current theme.");
        }
        $menuOrder = $menuOrder ?? (count($this->menuItems[$this->stylesheet][$menuSlug]) + 1);
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
        array_walk($meta, function ($value, $key) use ($menuItemId): void {
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
     * @throws JsonException If there's any issue debugging the query.
     */
    public function seeTermRelationshipInDatabase(array $criteria): void
    {
        $this->seeInDatabase($this->grabPrefixedTableNameFor('term_relationships'), $criteria);
    }

    /**
     * Sets the database driver of this object.
     *
     * @param Driver $driver      A reference to the database driver being set.
     * @param string $forDatabase The database key to set the
     *                            database driver for.
     */
    public function _setDriver(Driver $driver, string $forDatabase = 'default'): void
    {
        $this->driver = $driver;
        $this->drivers[$forDatabase] = $driver;
    }

    /**
     * Creates the database entries representing an attachment and moves the attachment file to the right location.
     *
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
     * @param string|int $date                          Either a string supported by the `strtotime` function or a UNIX
     *                                                  timestamp that should be used to build the "year/time" uploads
     *                                                  sub-folder structure.
     * @param array<string,mixed> $overrides            An associative array of values overriding the default ones.
     * @param array<string,array<int>>|null $imageSizes An associative array in the format [ <size> =>
     *                                                  [<width>,<height>]] to override the image sizes created by
     *                                                  default.
     *
     * @param string $file                              The absolute path to the attachment file.
     *
     * @return int The post ID of the inserted attachment.
     *
     * @throws ImageResizeException If the image resize operation fails while trying to create the image sizes.
     *
     * @throws ModuleRequireException If the `WPFileSystem` module is not loaded in the suite or if the
     *                                'gumlet/php-image-resize:^1.6' package is not installed.
     * @throws ModuleException If the WPFilesystem module is not loaded in the suite or the file to attach is not
     *                         readable.
     */
    public function haveAttachmentInDatabase(
        string $file,
        string|int $date = 'now',
        array $overrides = [],
        array $imageSizes = null
    ): int {
        if (!class_exists(ImageResize::class)) {
            $message = 'The "haveAttachmentInDatabase" method requires the "gumlet/php-image-resize:^1.6" package.' .
                PHP_EOL .
                'Please install it using the command "composer require --dev gumlet/php-image-resize:^1.6"';
            throw new ModuleRequireException($this, $message);
        }

        try {
            $fs = $this->getWpFilesystemModule();
        } catch (ModuleException) {
            throw new ModuleRequireException(
                $this,
                'The haveAttachmentInDatabase method requires the WPFilesystem module: update the suite ' .
                'configuration to use it'
            );
        }

        $pathInfo = pathinfo($file);
        $slug = Strings::slug($pathInfo['filename']);

        if (!is_readable($file)) {
            throw new ModuleException($this, "File [{$file}] is not readable.");
        }

        $data = file_get_contents($file);

        if (false === $data) {
            throw new ModuleException($this, "File [{$file}] contents could not be read.");
        }

        $uploadedFilePath = $fs->writeToUploadedFile($pathInfo['basename'], $data, $date);
        $uploadUrl = $this->grabSiteUrl(str_replace($fs->getWpRootFolder(), '', $uploadedFilePath));
        $uploadLocation = FS::unleadslashit(str_replace($fs->getUploadsPath(), '', $uploadedFilePath));

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

        [$imageWidth, $imageHeight] = $imageInfo;

        if ($imageSizes === null) {
            $imageSizes = [
                'thumbnail' => [150, 150],
                'medium' => 300,
                'large' => 768,
            ];
        }

        $extension = $pathInfo['extension'] ?? '';

        $createdImages = [];
        foreach ($imageSizes as $size => $thisSizes) {
            $thisSizes = (array)$thisSizes;
            $width = (int)$thisSizes[0];
            $height = isset($thisSizes[1]) ? (int)$thisSizes[1] : false;

            try {
                $image = new ImageResize($file);
            } catch (ImageResizeException) {
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

        $createSizeEntry = static function ($sizes) use ($slug, $mimeType, $extension): array {
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
     * @param string|null $path A path that should be appended to the site URL.
     *
     * @return string The current site URL
     */
    public function grabSiteUrl(string $path = null): string
    {
        /** @var array{url: string} $config Validated module config. */
        $config = $this->config;
        $url = $config['url'];

        if ($path !== null) {
            return FS::untrailslashit($config['url']) . DIRECTORY_SEPARATOR . FS::unleadslashit($path);
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
     *
     * @param array<string,mixed> $criteria An array of search criteria.
     *
     * @throws JsonException If there's any issue debugging the query.
     */
    public function seeAttachmentInDatabase(array $criteria): void
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
     * @param array<string,mixed> $criteria An array of search criteria.
     *
     * @throws JsonException If there's any issue debugging the query.
     */
    public function dontSeeAttachmentInDatabase(array $criteria): void
    {
        $this->dontSeePostInDatabase(array_merge($criteria, ['post_type' => 'attachment']));
    }

    /**
     * Removes an attachment from the posts table.
     *
     * @example
     * ``` php
     * $postmeta = $I->grabpostmetatablename();
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
     * @param bool $purgeMeta                  If set to `true` then the meta for the attachment will be purged too.
     * @param bool $removeFiles                Remove all files too, requires the `WPFilesystem` module to be loaded in
     *                                         the suite.
     *
     *
     * @param array<string,mixed> $criteria    An array of search criteria to find the attachment post in the posts
     *                                         table.
     *
     * @throws ModuleRequireException If the WPFilesystem module is not loaded in the suite and the `$removeFiles`
     *                                         argument is `true`.
     */
    public function dontHaveAttachmentInDatabase(
        array $criteria,
        bool $purgeMeta = true,
        bool $removeFiles = false
    ): void {
        $mergedCriteria = array_merge($criteria, ['post_type' => 'attachment']);

        if ($removeFiles) {
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
     *
     * @throws ModuleRequireException If the `WPFilesystem` module is not loaded in the suite.
     */
    public function dontHaveAttachmentFilesInDatabase(array|int $attachmentIds): void
    {
        try {
            $fs = $this->getWpFilesystemModule();
        } catch (ModuleException) {
            throw new ModuleRequireException(
                $this,
                'The haveAttachmentInDatabase method requires the WPFilesystem module: update the suite ' .
                'configuration to use it'
            );
        }

        foreach ((array)$attachmentIds as $attachmentId) {
            $attachedFile = $this->grabAttachmentAttachedFile($attachmentId);
            $attachmentMetadata = $this->grabAttachmentMetadata($attachmentId);

            $filesPath = FS::untrailslashit($fs->getUploadsPath(dirname($attachedFile)));

            if (!(isset($attachmentMetadata['sizes']) && is_array($attachmentMetadata['sizes']))) {
                continue;
            }

            foreach ($attachmentMetadata['sizes'] as $sizeData) {
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
     * @return string The attachment attached file path or an empty string if not set.
     */
    public function grabAttachmentAttachedFile(int $attachmentPostId): string
    {
        $attachedFile = $this->grabFromDatabase(
            $this->grabPostmetaTableName(),
            'meta_value',
            ['meta_key' => '_wp_attached_file', 'post_id' => $attachmentPostId]
        );

        return is_string($attachedFile) ? $attachedFile : '';
    }

    /**
     * Returns the metadata array for an attachment post.
     * This is the value of the `_wp_attachment_metadata` meta.
     *
     * @example
     * ```php
     * $metadata = $I->grabAttachmentMetadata($attachmentId);
     * $I->assertEquals(['thumbnail', 'medium', 'medium_large'], array_keys($metadata['sizes']);
     * ```
     *
     * @param int $attachmentPostId The attachment post ID.
     *
     * @return array<string,mixed> The attachment `_wp_attachment_metadata` meta or an empty array if not found or
     *                             not valid.
     */
    public function grabAttachmentMetadata(int $attachmentPostId): array
    {
        $serializedData = $this->grabFromDatabase(
            $this->grabPostmetaTableName(),
            'meta_value',
            ['meta_key' => '_wp_attachment_metadata', 'post_id' => $attachmentPostId]
        );

        if (!empty($serializedData)) {
            $unserialized = Serializer::maybeUnserialize($serializedData);
            if (!is_array($unserialized)) {
                return [];
            }

            return $unserialized;
        }

        return [];
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
     * @param array<string,mixed> $criteria An array of search criteria.
     * @param bool $purgeMeta               If set to `true` then the meta for the post will be purged too.
     */
    public function dontHavePostInDatabase(array $criteria, bool $purgeMeta = true): void
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
     * @param array<string,mixed> $criteria An array of search criteria.
     */
    public function dontHavePostMetaInDatabase(array $criteria): void
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
     * @param bool $purgeMeta   Whether the user meta should be purged alongside the user or not.
     *
     * @return array<int> An array of the deleted user(s) ID(s)
     *
     * @throws Exception If there's any issue running the queries.
     */
    public function dontHaveUserInDatabaseWithEmail(string $userEmail, bool $purgeMeta = true): array
    {
        $data = $this->grabAllFromDatabase($this->grabUsersTableName(), 'ID', ['user_email' => $userEmail]);
        if (empty($data)) {
            return [];
        }

        $ids = array_column($data, 'ID');

        $deleted = [];
        foreach ($ids as $id) {
            if (!is_numeric($id)) {
                continue;
            }
            $id = (int)$id;
            $this->dontHaveUserInDatabase($id, $purgeMeta);
            $deleted[] = $id;
        }

        return $deleted;
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
    public function grabTablePrefix(): string
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
     * @param bool $purgeMeta           Whether the user meta should be purged alongside the user or not.
     */
    public function dontHaveUserInDatabase(int|string $userIdOrLogin, bool $purgeMeta = true): void
    {
        if (is_numeric($userIdOrLogin)) {
            $userId = (int)$userIdOrLogin;
        } else {
            try {
                $userId = $this->grabUserIdFromDatabase($userIdOrLogin);
            } catch (Exception) {
                // User not found nothing to do.
                return;
            }
        }
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
     * @return int|false The user ID or `false` if the user was not found.
     */
    public function grabUserIdFromDatabase(string $userLogin): int|false
    {
        $userId = $this->grabFromDatabase($this->grabUsersTableName(), 'ID', ['user_login' => $userLogin]);

        if ($userId === false) {
            return false;
        }

        $this->assertIsNumeric($userId, sprintf('Failed to grab user ID for user "%s"', $userLogin));

        /** @var string|int $userId */
        return (int)$userId;
    }

    /**
     * Gets the value of one or more post meta values from the database.
     *
     * @example
     * ```php
     * $thumbnail_id = $I->grabPostMetaFromDatabase($postId, '_thumbnail_id', true);
     * ```
     *
     * @param int $postId     The post ID.
     * @param string $metaKey The key of the meta to retrieve.
     * @param bool $single    Whether to return a single meta value or an array of all available meta values.
     *
     * @return mixed|array<string,mixed> Either a single meta value or an array of all the available meta values.
     */
    public function grabPostMetaFromDatabase(int $postId, string $metaKey, bool $single = false): mixed
    {
        $postmeta = $this->grabPostmetaTableName();
        $grabbed = (array)$this->grabColumnFromDatabase(
            $postmeta,
            'meta_value',
            ['post_id' => $postId, 'meta_key' => $metaKey]
        );
        $values = array_reduce($grabbed, static function (array $metaValues, $value): array {
            $values = (array)Serializer::maybeUnserialize($value);
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
     * @param int $blogId   The blog ID.
     * @param string $table The table name, without table prefix.
     *
     * @return string The full blog table name, including the table prefix or an empty string
     *                if the table does not exist.
     *
     * @throws ModuleException|Exception If there's any issue running the queries.
     */
    public function grabBlogTableName(int $blogId, string $table): string
    {
        $blogTableNames = $this->grabBlogTableNames($blogId);
        if (!count($blogTableNames)) {
            throw new ModuleException($this, 'No tables found for blog with ID ' . $blogId);
        }
        foreach ($blogTableNames as $candidate) {
            if (!str_contains($candidate, $table)) {
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
     * @throws JsonException If there's any issue debugging the query.
     */
    public function dontSeeTableInDatabase(string $table): void
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
    public function grabBlogTablePrefix(int $blogId): string
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
    public function grabBlogDomain(int $blogId): string
    {
        $blogDomain = $this->grabFromDatabase($this->grabBlogsTableName(), 'domain', ['blog_id' => $blogId]);
        $this->assertIsString($blogDomain, sprintf('Failed to grab domain for blog with ID "%s"', $blogId));

        /** @var string $blogDomain */
        return $blogDomain;
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
     * @return string The blog path.
     */
    public function grabBlogPath(int $blogId): string
    {
        $blogPath = $this->grabFromDatabase($this->grabBlogsTableName(), 'path', ['blog_id' => $blogId]);
        $this->assertIsString($blogPath, sprintf('Failed to grab path for blog with ID "%s"', $blogId));

        /** @var string $blogPath */
        return $blogPath;
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
    public function _replaceUrlInDump(array|string $sql): string|array
    {
        /** @var array{
         *     urlReplacement: bool,
         *     tablePrefix: string,
         *     url: string,
         *     originalUrl?: string,
         * } $config Validated module config.
         */
        $config = $this->config;
        if ($config['urlReplacement'] === false) {
            return $sql;
        }

        $this->dbDump->setTablePrefix($config['tablePrefix']);
        $this->dbDump->setUrl($config['url']);
        $this->dbDump->setOriginalUrl(null);

        if (!empty($config['originalUrl'])) {
            $this->dbDump->setOriginalUrl($config['originalUrl']);
        }

        if (is_array($sql)) {
            $sql = $this->dbDump->replaceSiteDomainInSqlArray($sql);
            $sql = $this->dbDump->replaceSiteDomainInMultisiteSqlArray($sql);
        } else {
            $sql = $this->dbDump->replaceSiteDomainInSqlString($sql);
            $sql = $this->dbDump->replaceSiteDomainInMultisiteSqlString($sql, true);
        }

        return $sql;
    }

    /**
     * Conditionally checks that a term exists in the database.
     *
     * Will look up the "terms" table, will throw if not found.
     *
     * @param int $term_id The term ID.
     */
    protected function maybeCheckTermExistsInDatabase(int $term_id): void
    {
        if (!isset($this->config['checkExistence']) or false == $this->config['checkExistence']) {
            return;
        }
        $tableName = $this->grabPrefixedTableNameFor('terms');
        if (!$this->grabFromDatabase($tableName, 'term_id', ['term_id' => $term_id])) {
            throw new RuntimeException("A term with an id of $term_id does not exist", 1);
        }
    }

    /**
     * Loads a database dump using the current driver.
     *
     * @param string $databaseKey The key of the database to load the dump for.
     *
     *
     * @throws ModuleException If there's a configuration or operation issue.
     */
    protected function loadDumpUsingDriver(string $databaseKey): void
    {
        if ($this->config['urlReplacement'] === true) {
            $this->databasesSql[$databaseKey] = $this->_replaceUrlInDump($this->databasesSql[$databaseKey]);
        }

        parent::loadDumpUsingDriver($databaseKey);
    }

    /**
     * Loads the SQL dumps specified for a database.
     *
     * @param string|null $databaseKey                 The key of the database to load.
     * @param array<string,mixed>|null $databaseConfig The configuration for the database to load.
     */
    public function _loadDump(string $databaseKey = null, array $databaseConfig = null): void
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
     * @param int $post_id                     The post ID.
     * @param int $term_taxonomy_id            The term `term_id` or `term_taxonomy_id`; if the `$taxonomy` argument is
     *                                         passed this parameter will be interpreted as a `term_id`, else as a
     *                                         `term_taxonomy_id`.
     * @param int|null $term_order             The order the term applies to the post, defaults to `null` to not use
     *                                         the
     *                                         term order.
     * @param string|null $taxonomy            The taxonomy the `term_id` is for; if passed this parameter will be used
     *                                         to build a `taxonomy_term_id` from the `term_id`.
     *
     *
     * @throws ModuleException|JsonException If a `term_id` is specified but it cannot be matched to the `taxonomy`.
     */
    public function dontSeePostWithTermInDatabase(
        int $post_id,
        int $term_taxonomy_id,
        int $term_order = null,
        string $taxonomy = null
    ): void {
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
     */
    public function _beforeSuite($settings = []): void
    {
        parent::_beforeSuite($settings);

        /**
         * Dispatches an event after the WPDb module handled the BEFORE_SUITE event.
         *
         * @param WPDb $this The current module instance.
         */
        Dispatcher::dispatch(static::EVENT_BEFORE_SUITE, $this);
    }

    /**
     * Creates any database that is flagged, in the config, with the `createIfNotExists` flag.
     *
     * @param array<string,mixed> $config The current module configuration.
     *
     *
     * @throws ModuleException If there's any issue processing or reading the database DSN information.
     */
    protected function createDatabasesIfNotExist(array $config): void
    {
        $configDsn = $config['dsn'];
        if (is_string($configDsn) && str_starts_with($configDsn, 'sqlite:')) {
            return;
        }

        $createIfNotExist = [];
        if (!empty($config['createIfNotExists'])) {
            $createIfNotExist[$configDsn] = [$config['user'], $config['password']];
        }

        if (!empty($config['databases']) && is_array($config['databases'])) {
            foreach ($config['databases'] as $dbConfig) {
                if (!empty($dbConfig['createIfNotExists'])) {
                    $createIfNotExist[$dbConfig['dsn']] = [$dbConfig['user'], $dbConfig['password']];
                }
            }
        }

        if (!empty($createIfNotExist)) {
            /** @var array<string,array{0: string, 1: string}> $createIfNotExist Validated module config. */
            foreach ($createIfNotExist as $dsn => [$user, $pass]) {
                $dsnMap = DbUtils::dbDsnToMap((string)$dsn);
                $dbname = $dsnMap['dbname'] ?? '';

                if (empty($dbname)) {
                    throw new ModuleException(
                        $this,
                        sprintf('Failed to create database; DSN "%s" does not contain the database name.', $dsn)
                    );
                }

                try {
                    // Since the database might not exist at this point, remove the `dbname` from the DSN string.
                    unset($dsnMap['dbname']);
                    $db = DbUtils::db(DbUtils::dbDsnString($dsnMap), $user, $pass);
                    $db("CREATE DATABASE IF NOT EXISTS `{$dbname}`");
                } catch (Exception $e) {
                    throw new ModuleException(
                        $this,
                        'Failed to create database; error: .' . $e->getMessage()
                    );
                }
            }
        }
    }

    /**
     * Prepares the WordPress database with some test-quality-of-life-improvements.
     */
    protected function prepareDb(): void
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
         * @param WPDb $origin                This objects.
         * @param array<string,mixed> $config The current WPDb module configuration.
         */
        Dispatcher::dispatch(static::EVENT_AFTER_DB_PREPARE, $this, $this->config);
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
    public function havePostThumbnailInDatabase(int $postId, int $thumbnailId): int
    {
        $this->dontHavePostThumbnailInDatabase($postId);
        return $this->havePostmetaInDatabase($postId, '_thumbnail_id', (int)$thumbnailId);
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
     */
    public function dontHavePostThumbnailInDatabase(int $postId): void
    {
        $this->dontHavePostMetaInDatabase(['post_id' => $postId, 'meta_key' => '_thumbnail_id']);
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
     */
    public function importSql(array $sql): void
    {
        $this->_getDriver()->load($sql);
    }

    /**
     * Normalizes a site option name.
     *
     * @param string $name   The site option name to normalize, either containing a `_site_option_` prefix or not.
     * @param string $prefix The option name prefix to normalize for.
     *
     * @return string The normalized site option name, with a `_site_option_` prefix.
     */
    protected function normalizePrefixedOptionName(string $name, string $prefix): string
    {
        return str_starts_with($name, $prefix) ? $name : $prefix . $name;
    }

    /**
     * Checks that a site option is not in the database.
     *
     * @example
     * ```php
     * // Check that the option is not set in the database.
     * $I->dontSeeSiteOptionInDatabase('foo_count');
     * // Check that the option is not set with a specific value.
     * $I->dontSeeSiteOptionInDatabase('foo_count', 23);
     * $I->dontSeeSiteOptionInDatabase(['option_name => 'foo_count', 'option_value' => 23]);
     * ```
     *
     * @param array<string,mixed>|string $criteriaOrName An array of search criteria or the option name.
     * @param mixed|null $value                          The optional value to try and match, only used if the option
     *                                                   name is provided.
     *
     *
     * @throws JsonException If there's any issue debugging the query.
     */
    public function dontSeeSiteOptionInDatabase(array|string $criteriaOrName, mixed $value = null): void
    {
        $currentBlogId = $this->blogId;
        $this->useMainBlog();

        $criteria = $this->buildSiteOptionCriteria($criteriaOrName, $value);

        $this->dontSeeOptionInDatabase($criteria);
        $this->useBlog($currentBlogId);
    }

    /**
     * Builds the criteria required to search for a site option.
     *
     * @param string|array<string,mixed> $criteriaOrName Either the ready to use array criteria or the site option
     *                                                   name.
     * @param mixed|null $value                          The site option value, only used if the first parameter is not
     *                                                   an array.
     *
     * @return array<string,mixed> An array of criteria to search for the site option.
     */
    protected function buildSiteOptionCriteria(string|array $criteriaOrName, mixed $value = null): array
    {
        $criteria = $this->normalizeOptionCriteria($criteriaOrName, $value);
        if (isset($criteria['option_name'])) {
            /** @var array{option_name: string} $criteria Just normalized. */
            $criteria['option_name'] = $this->normalizePrefixedOptionName($criteria['option_name'], '_site_option_');
        }

        return $criteria;
    }

    /**
     * Normalizes an option criteria to consistently build array format criteria from name and value tuples.
     *
     * @param array<string,mixed>|string $criteriaOrName The option name or the criteria to check the option by.
     * @param mixed|null $value                          The option value to check; ignored if the first parameter is
     *                                                   an array.
     *
     * @return array<string,mixed> An array of option criteria, normalized.
     */
    protected function normalizeOptionCriteria(array|string $criteriaOrName, mixed $value = null): array
    {
        $criteria = [];

        if (is_string($criteriaOrName)) {
            $criteria['option_name'] = $criteriaOrName;
            if ($value !== null) {
                $criteria['option_value'] = $value;
            }
        } else {
            $criteria = $criteriaOrName;
        }

        if (isset($criteria['option_value'])) {
            $criteria['option_value'] = Serializer::maybeSerialize($criteria['option_value']);
        }

        return $criteria;
    }

    /**
     * Fetches the value of a transient from the database.
     *
     * @example
     * ```php
     * $I->haveTransientInDatabase('foo', 23);
     * $transientValue = $I->grabTransientFromDatabase('foo');
     * $I->assertEquals(23, $transientValue);
     * ```
     * @param string $transient The transient name.
     *
     * @return mixed The transient value; it will be unserialized if it was serialized.
     *
     */
    public function grabTransientFromDatabase(string $transient): mixed
    {
        $transient = $this->normalizePrefixedOptionName($transient, '_transient_');
        return $this->grabOptionFromDatabase($transient);
    }

    /**
     * Checks that a transient is not in the database.
     *
     * @example
     * ```php
     * $I->dontSeeTransientInDatabase('foo');
     * $I->dontSeeTransientInDatabase('foo', 23);
     * ```
     * @param mixed $value The optional value to try and match.
     *
     * @param string $transient The transient name.
     * @return void
     * @throws JsonException
     *
     */
    public function dontSeeTransientInDatabase(string $transient, mixed $value = null): void
    {
        $transient = $this->normalizePrefixedOptionName($transient, '_transient_');
        $this->dontSeeOptionInDatabase($transient, $value);
    }

    /**
     * Checks that a transient is in the database.
     *
     * @example
     * ```php
     * $I->haveTransientInDatabase('foo', 23);
     * $I->seeTransientInDatabase('foo');
     * $I->seeTransientInDatabase('foo', 23);
     * ```
     * @param mixed $value The optional value to try and match.
     *
     * @param string $name The transient name.
     * @return void
     * @throws JsonException
     *
     */
    public function seeTransientInDatabase(string $name, mixed $value = null): void
    {
        $transient = $this->normalizePrefixedOptionName($name, '_transient_');
        $this->seeOptionInDatabase($transient, $value);
    }

    /**
     * Checks that a site transient is not in the database.
     *
     * @example
     * ```php
     * $I->dontSeeSiteTransientInDatabase('foo');
     * $I->dontSeeSiteTransientInDatabase('foo', 23);
     * ```
     * @param mixed|null $value The optional value to try and match.
     *
     * @param string $transient The transient name.
     * @return void
     *
     * @throws JsonException|ModuleException
     *
     */
    public function dontSeeSiteTransientInDatabase(string $transient, mixed $value = null): void
    {
        $currentBlogId = $this->blogId;
        $this->useMainBlog();
        $transient = $this->normalizePrefixedOptionName($transient, '_site_transient_');
        $this->dontSeeOptionInDatabase($transient, $value);
        $this->useBlog($currentBlogId);
    }

    /**
     * Checks that a site transient is in the database.
     *
     * @example
     * ```php
     * $I->haveSiteTransientInDatabase('foo', 23);
     * $I->seeSiteTransientInDatabase('foo');
     * $I->seeSiteTransientInDatabase('foo', 23);
     * ```
     * @param mixed|null $value The optional value to try and match.
     *
     * @param string $transient The transient name.
     * @return void
     * @throws JsonException|ModuleException
     *
     */
    public function seeSiteTransientInDatabase(string $transient, mixed $value = null): void
    {
        $currentBlogId = $this->blogId;
        $this->useMainBlog();
        $transient = $this->normalizePrefixedOptionName($transient, '_site_transient_');
        $this->seeOptionInDatabase($transient, $value);
        $this->useBlog($currentBlogId);
    }

    private function reconnectCurrentDatabase(): void
    {
        $allDbConfigs = $this->getDatabases();
        if (!isset($allDbConfigs[$this->currentDatabase])) {
            return;
        }
        $currentDatabaseConfig = $allDbConfigs[$this->currentDatabase];

        try {
            $disconnectMethodReflection = new ReflectionMethod($this, 'disconnect');
            $connectMethodReflection = new ReflectionMethod($this, 'connect');
            $disconnectMethodReflection->setAccessible(true);
            $connectMethodReflection->setAccessible(true);
            $this->debugSection('WPDb', 'Reconnecting to database ' . $this->currentDatabase);
            $disconnectMethodReflection->invoke($this, $this->currentDatabase);
            $connectMethodReflection->invoke($this, $this->currentDatabase, $currentDatabaseConfig);
        } catch (ReflectionException $e) {
            // Do nothing, the attempt was not successful.
        }
    }
}

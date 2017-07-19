<?php

namespace Codeception\Module;

use BaconStringUtils\Slugifier;
use Codeception\Configuration;
use Codeception\Exception\ModuleConfigException;
use Codeception\Exception\ModuleException;
use Codeception\Lib\Driver\ExtendedDbDriver as Driver;
use Eventviva\ImageResize;
use Eventviva\ImageResizeException;
use Handlebars\Handlebars;
use PDO;
use tad\WPBrowser\Filesystem\Utils;
use tad\WPBrowser\Generators\Blog;
use tad\WPBrowser\Generators\Comment;
use tad\WPBrowser\Generators\Links;
use tad\WPBrowser\Generators\Post;
use tad\WPBrowser\Generators\Tables;
use tad\WPBrowser\Generators\User;
use tad\WPBrowser\Generators\WpPassword;

/**
 * An extension of Codeception Db class to add WordPress database specific
 * methods.
 */
class WPDb extends ExtendedDb {

	/**
	 * @var string The theme stylesheet in use.
	 */
	protected $stylesheet = '';

	/**
	 * @var array
	 */
	protected $menus = [];

	/**
	 * @var array
	 */
	protected $menuItems = [];

	/**
	 * @var string
	 */
	protected $numberPlaceholder = '{{n}}';

	/**
	 * @var array
	 */
	protected $termKeys = ['term_id', 'name', 'slug', 'term_group'];

	/**
	 * @var array
	 */
	protected $termTaxonomyKeys = ['term_taxonomy_id', 'term_id', 'taxonomy', 'description', 'parent', 'count'];

	/**
	 * @var array A list of tables that WordPress will nor replicate in multisite installations.
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
	 * url - the site url
	 *
	 * @var array
	 */
	protected $requiredFields = ['url'];

	/**
	 * The module optional configuration parameters.
	 *
	 * @var array
	 */
	protected $config = [
		'tablePrefix' => 'wp_',
		'populate'    => true,
		'cleanup'     => true,
		'reconnect'   => false,
		'dump'        => null,
		'populator'   => null,
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
	 * @var Handlebars
	 */
	protected $handlebars;

	/**
	 * @var Tables
	 */
	protected $tables;

	/**
	 * @var bool
	 */
	protected $isSubdomainMultisiteInstall = false;

	/**
	 * @var array
	 */
	protected $templateData;

	public function _cleanup() {
		parent::_cleanup();

		$this->blogId = 0;
	}

	/**
	 * Initializes the module.
	 *
	 * @param Handlebars $handlebars
	 *
	 * @param Tables     $table
	 *
	 * @throws ModuleConfigException
	 * @throws \Codeception\Exception\ModuleException
	 */
	public function _initialize(Handlebars $handlebars = null, Tables $table = null) {
		$this->prepareSqlDumpFile();

		$this->initialize_driver();

		$this->dbh = $this->driver->getDbh();

		// starting with loading dump
		$this->importSqlDumpFile();

		$this->tablePrefix = $this->config['tablePrefix'];
		$this->handlebars  = $handlebars ?: new Handlebars();
		$this->tables      = $table ?: new Tables();
	}

	private function prepareSqlDumpFile() {
		if ($this->config['dump'] && ($this->config['cleanup'] or ($this->config['populate']))) {

			if (!file_exists(Configuration::projectDir() . $this->config['dump'])) {
				throw new ModuleConfigException(__CLASS__, "\nFile with dump doesn't exist.
                    Please, check path for sql file: " . $this->config['dump']);
			}
			$sql = file_get_contents(Configuration::projectDir() . $this->config['dump']);
			$sql = preg_replace('%/\*(?!!\d+)(?:(?!\*/).)*\*/%s', "", $sql);

			if (empty($this->config['urlReplacement'])) {
				$sql = $this->replaceSiteDomainInSqlString($sql, true);
				$sql = $this->replaceSiteDomainInMultisiteSqlString($sql, true);
			}

			$this->sql = explode("\n", $sql);
		}
	}

	/**
	 * Replaces the WordPress domains in a SQL dump string.
	 *
	 * @param string $sql   The input SQL dump string.
	 * @param bool   $debug Whether a debug message should be printed or not.
	 *
	 * @return string The modified SQL string.
	 */
	public function replaceSiteDomainInSqlString($sql, $debug = false) {
		$optionsTable = $this->config['tablePrefix'] . 'options';

		$matches = [];
		preg_match("/INSERT\\s+INTO\\s+`{$optionsTable}`.*'home'\\s*,\\s*'(.*)',/uiU", $sql, $matches);

		if (empty($matches) || empty($matches[1])) {
			if ($debug) {
				codecept_debug('Tried to replace WordPress site domain but dump file does not contain an `options` table INSERT instruction.');
			}

			return $sql;
		}

		$dumpSiteUrl = $matches[1];

		if (empty($dumpSiteUrl)) {
			if ($debug) {
				codecept_debug('Tried to replace WordPress site domain but dump file does not contain dump of `home` option.');
			}

			return $sql;
		}

		$thisSiteUrl = $this->config['url'];

		if ($dumpSiteUrl === $thisSiteUrl) {
			if ($debug) {
				codecept_debug('Dump file domain not replaced as identical to the one specified in the configuration.');
			}

			return $sql;
		}

		$sql = str_replace($dumpSiteUrl, $thisSiteUrl, $sql);

		codecept_debug('Dump file domain [' . $dumpSiteUrl . '] replaced with [' . $thisSiteUrl . ']');

		return $sql;
	}

	/**
	 * Replaces the site domain in the multisite tables of a SQL dump.
	 *
	 * @param string $sql
	 *
	 * @return string
	 */
	public function replaceSiteDomainInMultisiteSqlString($sql, $debug = false) {
		$tables = [
			'blogs' => "VALUES\\s+\\(\\d+,\\s*\\d+,\\s*'(.*)',/uiU",
			'site'  => "VALUES\\s+\\(\\d+,\\s*'(.*)',/uiU",
		];

		$thisSiteUrl = preg_replace('~https?:\\/\\/~', '', $this->config['url']);

		foreach ($tables as $table => $pattern) {
			$currentTable = $this->config['tablePrefix'] . $table;
			$matches      = [];
			preg_match("/INSERT\\s+INTO\\s+`{$currentTable}`\\s+{$pattern}", $sql, $matches);

			if (empty($matches) || empty($matches[1])) {
				if ($debug) {
					codecept_debug('Tried to replace WordPress site domain but dump file does not contain a table INSERT instruction for table ['
								   . $table . '].');
				}
				continue;
			}

			$dumpSiteUrl = $matches[1];
			if (empty($dumpSiteUrl)) {
				if ($debug) {
					codecept_debug('Tried to replace WordPress site domain but dump file does not contain dump of [domain] option.');
				}
				continue;
			}

			if ($dumpSiteUrl === $thisSiteUrl) {
				if ($debug) {
					codecept_debug('Dump file domain not replaced as identical to the one specified in the configuration ['
								   . $dumpSiteUrl . '].');
				}
				continue;
			}

			if ($debug) {
				codecept_debug('Dump file domain [' . $dumpSiteUrl . '] replaced with [' . $thisSiteUrl . '].');
			}

			$sql = str_replace($dumpSiteUrl, $thisSiteUrl, $sql);
		}

		return $sql;
	}

	protected function initialize_driver() {
		try {
			$this->driver = Driver::create($this->config['dsn'], $this->config['user'], $this->config['password']);
		} catch (\PDOException $e) {
			throw new ModuleConfigException(__CLASS__, $e->getMessage() . ' while creating PDO connection');
		}
	}

	/**
	 * Import the SQL dump file if populate is enabled.
	 *
	 * Specifying a dump file that file will be imported.
	 *
	 * @param null|string $dumpFile The dump file that should be imported in place of the default one.
	 */
	public function importSqlDumpFile($dumpFile = null) {
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
	 * Checks that an option is not in the database for the current blog.
	 *
	 * If the value is an object or an array then the serialized option will be checked for.
	 *
	 * @param array $criteria An array of search criteria.
	 */
	public function dontSeeOptionInDatabase(array $criteria) {
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
	 * @param  string $tableName The table name, e.g. `options`.
	 *
	 * @return string            The prefixed table name, e.g. `wp_options` or `wp_2_options`.
	 */
	public function grabPrefixedTableNameFor($tableName = '') {
		$idFrag = '';
		if (!(in_array($tableName, $this->uniqueTables) || $this->blogId == 1)) {
			$idFrag = empty($this->blogId) ? '' : "{$this->blogId}_";
		}

		$tableName = $this->config['tablePrefix'] . $idFrag . $tableName;

		return $tableName;
	}

	/**
	 * @param $value
	 *
	 * @return string
	 */
	protected function maybeSerialize($value) {
		$metaValue = (is_array($value) || is_object($value)) ? serialize($value) : $value;

		return $metaValue;
	}

	/**
	 * Checks for a post meta value in the database for the current blog.
	 *
	 * If the `meta_value` is an object or an array then the serialized value will be checked for.
	 *
	 * @param  array $criteria An array of search criteria.
	 */
	public function seePostMetaInDatabase(array $criteria) {
		$tableName = $this->grabPrefixedTableNameFor('postmeta');
		if (!empty($criteria['meta_value'])) {
			$criteria['meta_value'] = $this->maybeSerialize($criteria['meta_value']);
		}
		$this->seeInDatabase($tableName, $criteria);
	}

	/**
	 * Checks for a link in the database.
	 *
	 * Will look up the "links" table.
	 *
	 * @param  array $criteria An array of search criteria.
	 */
	public function seeLinkInDatabase(array $criteria) {
		$tableName = $this->grabPrefixedTableNameFor('links');
		$this->seeInDatabase($tableName, $criteria);
	}

	/**
	 * Checks that a link is not in the database.
	 *
	 * Will look up the "links" table.
	 *
	 * @param  array $criteria An array of search criteria.
	 */
	public function dontSeeLinkInDatabase(array $criteria) {
		$tableName = $this->grabPrefixedTableNameFor('links');
		$this->dontSeeInDatabase($tableName, $criteria);
	}

	/**
	 * Checks that a post meta value is not there.
	 *
	 * If the meta value is an object or an array then the serialized version will be checked for.
	 *
	 * @param  array $criteria An array of search criteria.
	 */
	public function dontSeePostMetaInDatabase(array $criteria) {
		$tableName = $this->grabPrefixedTableNameFor('postmeta');
		if (!empty($criteria['meta_value'])) {
			$criteria['meta_value'] = $this->maybeSerialize($criteria['meta_value']);
		}
		$this->dontSeeInDatabase($tableName, $criteria);
	}

	/**
	 * Checks that a post to term relation exists in the database.
	 *
	 * Will look up the "term_relationships" table.
	 *
	 * @param  int     $post_id    The post ID.
	 * @param  int     $term_id    The term ID.
	 * @param  integer $term_order The order the term applies to the post, defaults to 0.
	 *
	 * @return void
	 */
	public function seePostWithTermInDatabase($post_id, $term_id, $term_order = 0) {
		$tableName = $this->grabPrefixedTableNameFor('term_relationships');
		$this->dontSeeInDatabase($tableName, [
			'object_id'  => $post_id,
			'term_id'    => $term_id,
			'term_order' => $term_order,
		]);
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
	public function seeUserInDatabase(array $criteria) {
		$tableName   = $this->grabPrefixedTableNameFor('users');
		$allCriteria = $criteria;
		if (!empty($criteria['user_pass'])) {
			$userPass = $criteria['user_pass'];
			unset($criteria['user_pass']);
			$hashedPass = $this->grabFromDatabase($tableName, 'user_pass', $criteria);
			$passwordOk = WpPassword::instance()->check($userPass, $hashedPass);
			$this->assertTrue($passwordOk,
				'No matching records found for criteria ' . json_encode($allCriteria) . ' in table ' . $tableName);
		}
		$this->seeInDatabase($tableName, $criteria);
	}

	/**
	 * Checks that a user is not in the database.
	 *
	 * @param  array $criteria An array of search criteria.
	 */
	public function dontSeeUserInDatabase(array $criteria) {
		$tableName   = $this->grabPrefixedTableNameFor('users');
		$allCriteria = $criteria;
		$passwordOk  = false;
		if (!empty($criteria['user_pass'])) {
			$userPass = $criteria['user_pass'];
			unset($criteria['user_pass']);
			$hashedPass = $this->grabFromDatabase($tableName, 'user_pass', [$criteria]);
			$passwordOk = WpPassword::instance()->check($userPass, $hashedPass);
		}

		$count = $this->countInDatabase($tableName, $criteria);
		$this->assertTrue(!$passwordOk && $count < 1,
			'Unexpectedly found matching records for criteria ' . json_encode($allCriteria) . ' in table ' . $tableName);
	}

	/**
	 * Inserts a page in the database.
	 *
	 * @param array $overrides An array of values to override the default ones.
	 *
	 * @return int The inserted page post ID.
	 */
	public function havePageInDatabase(array $overrides = []) {
		$overrides['post_type'] = 'page';

		return $this->havePostInDatabase($overrides);
	}

	/**
	 * Inserts a post in the database.
	 *
	 * @param  array $data An associative array of post data to override default and random generated values.
	 *
	 * @return int post_id The inserted post ID.
	 */
	public function havePostInDatabase(array $data = []) {
		$postTableName = $this->grabPostsTableName();
		$idColumn      = 'ID';
		$id            = $this->grabLatestEntryByFromDatabase($postTableName, $idColumn) + 1;
		$post          = Post::makePost($id, $this->config['url'], $data);
		$hasMeta       = !empty($data['meta']) || !empty($data['meta_input']);
		$hasTerms      = !empty($data['terms']) || !empty($data['tax_input']);

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
					$termId = $this->grabTermIdFromDatabase(['name' => $termName]);

					if (empty($termId)) {
						$termId = $this->grabTermIdFromDatabase(['slug' => $termName]);
					}

					if (empty($termId)) {
						$termIds = $this->haveTermInDatabase($termName, $taxonomy);
						$termId  = reset($termIds);
					}

					$termTaxonomyId = $this->grabTermTaxonomyIdFromDatabase([
						'term_id'  => $termId,
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
	 * Gets the posts table name.
	 *
	 * @return string The prefixed table name, e.g. `wp_posts`
	 */
	public function grabPostsTableName() {
		return $this->grabPrefixedTableNameFor('posts');
	}

	/**
	 * Returns the id value of the last table entry.
	 *
	 * @param string $tableName
	 * @param string $idColumn
	 *
	 * @return mixed
	 */
	public function grabLatestEntryByFromDatabase($tableName, $idColumn = 'ID') {
		$dbh = $this->driver->getDbh();
		$sth = $dbh->prepare("SELECT {$idColumn} FROM {$tableName} ORDER BY {$idColumn} DESC LIMIT 1");
		$this->debugSection('Query', $sth->queryString);
		$sth->execute();

		return $sth->fetchColumn();
	}

	/**
	 * Adds one or more meta key and value couples in the database for a post.
	 *
	 * @param int    $post_id
	 * @param string $meta_key
	 * @param mixed  $meta_value The value to insert in the database, objects and arrays will be serialized.
	 *
	 * @return int The inserted meta `meta_id`.
	 */
	public function havePostmetaInDatabase($post_id, $meta_key, $meta_value) {
		if (!is_int($post_id)) {
			throw new \BadMethodCallException('Post id must be an int', 1);
		}
		if (!is_string($meta_key)) {
			throw new \BadMethodCallException('Meta key must be an string', 3);
		}
		$tableName = $this->grabPostMetaTableName();

		return $this->haveInDatabase($tableName, [
			'post_id'    => $post_id,
			'meta_key'   => $meta_key,
			'meta_value' => $this->maybeSerialize($meta_value),
		]);
	}

	/**
	 * Returns the prefixed post meta table name.
	 *
	 * @return string The prefixed `postmeta` table name, e.g. `wp_postmeta`.
	 */
	public function grabPostMetaTableName() {
		return $this->grabPrefixedTableNameFor('postmeta');
	}

	/**
	 * Gets a term from the database.
	 *
	 * Looks up the prefixed `terms` table, e.g. `wp_terms`.
	 *
	 * @param array $criteria An array of search criteria.
	 *
	 * @return int The matching term `term_id`
	 */
	public function grabTermIdFromDatabase(array $criteria) {
		return $this->grabFromDatabase($this->grabTermsTableName(), 'term_id', $criteria);
	}

	/**
	 * Gets the prefixed terms table name, e.g. `wp_terms`.
	 *
	 * @return string
	 */
	public function grabTermsTableName() {
		return $this->grabPrefixedTableNameFor('terms');

	}

	/**
	 * Inserts a term in the database.
	 *
	 * @param  string $name      The term name, e.g. "Fuzzy".
	 * @param string  $taxonomy  The term taxonomy
	 * @param array   $overrides An array of values to override the default ones.
	 *
	 * @return array An array containing `term_id` and `term_taxonomy_id` of the inserted term.
	 */
	public function haveTermInDatabase($name, $taxonomy, array $overrides = []) {
		$termDefaults = ['slug' => (new Slugifier())->slugify($name), 'term_group' => 0];

		$hasMeta = !empty($overrides['meta']);
		$meta    = [];
		if ($hasMeta) {
			$meta = $overrides['meta'];
			unset($overrides['meta']);
		}

		$termData         = array_merge($termDefaults, array_intersect_key($overrides, $termDefaults));
		$termData['name'] = $name;
		$term_id          = $this->haveInDatabase($this->grabTermsTableName(), $termData);

		$termTaxonomyDefaults         = ['description' => '', 'parent' => 0, 'count' => 0];
		$termTaxonomyData             = array_merge($termTaxonomyDefaults, array_intersect_key($overrides, $termTaxonomyDefaults));
		$termTaxonomyData['taxonomy'] = $taxonomy;
		$termTaxonomyData['term_id']  = $term_id;
		$term_taxonomy_id             = $this->haveInDatabase($this->grabTermTaxonomyTableName(), $termTaxonomyData);

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
	 * @return string
	 */
	public function grabTermTaxonomyTableName() {
		return $this->grabPrefixedTableNameFor('term_taxonomy');
	}

	/**
	 * Inserts a term meta row in the database.
	 *
	 * Objects and array meta values will be serialized.
	 *
	 * @param int    $term_id
	 * @param string $meta_key
	 * @param mixed  $meta_value
	 *
	 * @return int The inserted term meta `meta_id`
	 */
	public function haveTermMetaInDatabase($term_id, $meta_key, $meta_value) {
		if (!is_int($term_id)) {
			throw new \BadMethodCallException('Term id must be an int');
		}
		if (!is_string($meta_key)) {
			throw new \BadMethodCallException('Meta key must be an string');
		}
		$tableName = $this->grabTermMetaTableName();

		return $this->haveInDatabase($tableName, [
			'term_id'    => $term_id,
			'meta_key'   => $meta_key,
			'meta_value' => $this->maybeSerialize($meta_value),
		]);
	}

	/**
	 * Gets the terms meta table prefixed name.
	 *
	 * E.g.: `wp_termmeta`.
	 *
	 * @return string
	 */
	public function grabTermMetaTableName() {
		return $this->grabPrefixedTableNameFor('termmeta');
	}

	/**
	 * Gets a `term_taxonomy_id` from the database.
	 *
	 * Looks up the prefixed `terms_relationships` table, e.g. `wp_term_relationships`.
	 *
	 * @param array $criteria An array of search criteria.
	 *
	 * @return int The matching term `term_taxonomy_id`
	 */
	public function grabTermTaxonomyIdFromDatabase(array $criteria) {
		return $this->grabFromDatabase($this->grabTermTaxonomyTableName(), 'term_taxonomy_id', $criteria);
	}

	/**
	 * Creates a term relationship in the database.
	 *
	 * Please mind that no check about the consistency of the insertion is made. E.g. a post could be assigned a term from
	 * a taxonomy that's not registered for that post type.
	 *
	 * @param     int $object_id  A post ID, a user ID or anything that can be assigned a taxonomy term.
	 * @param     int $term_taxonomy_id
	 * @param int     $term_order Defaults to `0`.
	 */
	public function haveTermRelationshipInDatabase($object_id, $term_taxonomy_id, $term_order = 0) {
		$this->haveInDatabase($this->grabTermRelationshipsTableName(), [
			'object_id'        => $object_id,
			'term_taxonomy_id' => $term_taxonomy_id,
			'term_order'       => $term_order,
		]);
	}

	/**
	 * Gets the prefixed term relationships table name, e.g. `wp_term_relationships`.
	 *
	 * @return string
	 */
	public function grabTermRelationshipsTableName() {
		return $this->grabPrefixedTableNameFor('term_relationships');
	}

	private function increaseTermCountBy($termTaxonomyId, $by = 1) {
		$updateQuery =
			"UPDATE {$this->grabTermTaxonomyTableName()} SET count = count + {$by} WHERE term_taxonomy_id = {$termTaxonomyId}";

		return $this->driver->executeQuery($updateQuery, []);
	}

	/**
	 * Checks for a page in the database.
	 *
	 * @param  array $criteria An array of search criteria.
	 */
	public function seePageInDatabase(array $criteria) {
		$criteria['post_type'] = 'page';
		$this->seePostInDatabase($criteria);
	}

	/**
	 * Checks for a post in the database.
	 *
	 * @param  array $criteria An array of search criteria.
	 */
	public function seePostInDatabase(array $criteria) {
		$tableName = $this->grabPrefixedTableNameFor('posts');
		$this->seeInDatabase($tableName, $criteria);
	}

	/**
	 * Checks that a page is not in the database.
	 *
	 * @param  array $criteria An array of search criteria.
	 */
	public function dontSeePageInDatabase(array $criteria) {
		$criteria['post_type'] = 'page';
		$this->dontSeePostInDatabase($criteria);
	}

	/**
	 * Checks that a post is not in the database.
	 *
	 * @param  array $criteria An array of search criteria.
	 */
	public function dontSeePostInDatabase(array $criteria) {
		$tableName = $this->grabPrefixedTableNameFor('posts');
		$this->dontSeeInDatabase($tableName, $criteria);
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
	public function seeCommentInDatabase(array $criteria) {
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
	public function dontSeeCommentInDatabase(array $criteria) {
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
	public function seeCommentMetaInDatabase(array $criteria) {
		$tableName = $this->grabPrefixedTableNameFor('commentmeta');
		$this->seeInDatabase($tableName, $criteria);
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
	public function dontSeeCommentMetaInDatabase(array $criteria) {
		$tableName = $this->grabPrefixedTableNameFor('commentmeta');
		$this->dontSeeInDatabase($tableName, $criteria);
	}

	/**
	 * Checks for a user meta value in the database.
	 *
	 * @param  array $criteria An array of search criteria.
	 */
	public function seeUserMetaInDatabase(array $criteria) {
		$tableName = $this->grabPrefixedTableNameFor('usermeta');
		$this->seeInDatabase($tableName, $criteria);
	}

	/**
	 * Check that a user meta value is not in the database.
	 *
	 * @param  array $criteria An array of search criteria.
	 */
	public function dontSeeUserMetaInDatabase(array $criteria) {
		$tableName = $this->grabPrefixedTableNameFor('usermeta');
		$this->dontSeeInDatabase($tableName, $criteria);
	}

	/**
	 * Removes a link from the database.
	 *
	 * @param  array $criteria An array of search criteria.
	 */
	public function dontHaveLinkInDatabase(array $criteria) {
		$tableName = $this->grabPrefixedTableNameFor('links');
		$this->dontHaveInDatabase($tableName, $criteria);
	}

	/**
	 * Removes an entry from the posts table.
	 *
	 * @param  array $criteria  An array of search criteria.
	 * @param bool   $purgeMeta If set to `true` then the meta for the post will be purged too.
	 */
	public function dontHavePostInDatabase(array $criteria, $purgeMeta = true) {
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
	 * @param  array $criteria An array of search criteria.
	 */
	public function dontHavePostMetaInDatabase(array $criteria) {
		$tableName = $this->grabPrefixedTableNameFor('postmeta');
		$this->dontHaveInDatabase($tableName, $criteria);
	}

	/**
	 * Removes an entry from the term_relationships table.
	 *
	 * @param  array $criteria An array of search criteria.
	 */
	public function dontHaveTermRelationshipInDatabase(array $criteria) {
		$tableName = $this->grabPrefixedTableNameFor('term_relationships');
		$this->dontHaveInDatabase($tableName, $criteria);
	}

	/**
	 * Removes an entry from the term_taxonomy table.
	 *
	 * @param  array $criteria An array of search criteria.
	 */
	public function dontHaveTermTaxonomyInDatabase(array $criteria) {
		$tableName = $this->grabPrefixedTableNameFor('term_taxonomy');
		$this->dontHaveInDatabase($tableName, $criteria);
	}

	/**
	 * Removes an entry from the usermeta table.
	 *
	 * @param  array $criteria An array of search criteria.
	 */
	public function dontHaveUserMetaInDatabase(array $criteria) {
		$tableName = $this->grabPrefixedTableNameFor('usermeta');
		$this->dontHaveInDatabase($tableName, $criteria);
	}

	/**
	 * Removes a user from the database.
	 *
	 * @param int|string $userIdOrLogin
	 * @param bool       $purgeMeta Whether the user meta should be purged alongside the user or not.
	 */
	public function dontHaveUserInDatabase($userIdOrLogin, $purgeMeta = true) {
		$userId = is_numeric($userIdOrLogin) ? intval($userIdOrLogin) : $this->grabUserIdFromDatabase($userIdOrLogin);
		$this->dontHaveInDatabase($this->grabPrefixedTableNameFor('users'), ['ID' => $userId]);
		if ($purgeMeta) {
			$this->dontHaveInDatabase($this->grabPrefixedTableNameFor('usermeta'), ['user_id' => $userId]);
		}
	}

	/**
	 * Gets the a user ID from the database using the user login.
	 *
	 * @param string $userLogin
	 *
	 * @return int The user ID
	 */
	public function grabUserIdFromDatabase($userLogin) {
		return $this->grabFromDatabase($this->getUsersTableName(), 'ID', ['user_login' => $userLogin]);
	}

	/**
	 * Gets a user meta from the database.
	 *
	 * @param int    $userId
	 * @param string $meta_key
	 *
	 * @return array An associative array of meta key/values.
	 */
	public function grabUserMetaFromDatabase($userId, $meta_key) {
		$table = $this->grabPrefixedTableNameFor('usermeta');
		$meta  = $this->grabAllFromDatabase($table, 'meta_value', ['user_id' => $userId, 'meta_key' => $meta_key]);
		if (empty($meta)) {
			return [];
		}

		return array_map(function ($val) {
			return $val['meta_value'];
		}, $meta);
	}

	/**
	 * Returns all entries matching a criteria from the database.
	 *
	 * @param string $table
	 * @param string $column
	 * @param array  $criteria
	 *
	 * @return array An array of results.
	 * @throws \Exception
	 */
	public function grabAllFromDatabase($table, $column, $criteria) {
		$query = $this->driver->select($column, $table, $criteria);

		$sth = $this->driver->executeQuery($query, array_values($criteria));

		return $sth->fetchAll(PDO::FETCH_ASSOC);
	}

	/**
	 * Inserts a transient in the database.
	 *
	 * If the value is an array or an object then the value will be serialized.
	 *
	 * @param string $transient
	 * @param mixed  $value
	 *
	 * @return int The inserted option `option_id`.
	 */
	public function haveTransientInDatabase($transient, $value) {
		return $this->haveOptionInDatabase('_transient_' . $transient, $value);
	}

	/**
	 * Inserts an option in the database.
	 *
	 * If the option value is an object or an array then the value will be serialized.
	 *
	 * @param  string $option_name
	 * @param  mixed  $option_value
	 * @param string  $autoload
	 *
	 * @return int The inserted option `option_id`
	 */
	public function haveOptionInDatabase($option_name, $option_value, $autoload = 'yes') {
		$table = $this->grabPrefixedTableNameFor('options');
		$this->dontHaveInDatabase($table, ['option_name' => $option_name]);
		$option_value = $this->maybeSerialize($option_value);

		return $this->haveInDatabase($table, [
			'option_name'  => $option_name,
			'option_value' => $option_value,
			'autoload'     => $autoload,
		]);
	}

	/**
	 * Removes a transient from the database.
	 *
	 * @param $transient
	 *
	 * @return int The removed option `option_id`.
	 */
	public function dontHaveTransientInDatabase($transient) {
		return $this->dontHaveOptionInDatabase('_transient_' . $transient);
	}

	/**
	 * Removes an entry from the options table.
	 *
	 * @param      $key
	 * @param null $value
	 *
	 * @return void
	 */
	public function dontHaveOptionInDatabase($key, $value = null) {
		$tableName               = $this->grabPrefixedTableNameFor('options');
		$criteria['option_name'] = $key;
		if (!empty($value)) {
			$criteria['option_value'] = $value;
		}

		$this->dontHaveInDatabase($tableName, $criteria);
	}

	/**
	 * Inserts a site option in the database.
	 *
	 * If the value is an array or an object then the value will be serialized.
	 *
	 * @param string $key
	 * @param mixed  $value
	 *
	 * @return int The inserted option `option_id`.
	 */
	public function haveSiteOptionInDatabase($key, $value) {
		$currentBlogId = $this->blogId;
		$this->useMainBlog();
		$option_id = $this->haveOptionInDatabase('_site_option_' . $key, $value);
		$this->useBlog($currentBlogId);

		return $option_id;
	}

	/**
	 * Sets the current blog to the main one (`blog_id` 1).
	 */
	public function useMainBlog() {
		$this->useBlog(0);
	}

	/**
	 * Sets the blog to be used.
	 *
	 * @param int $id
	 */
	public function useBlog($id = 0) {
		if (!(is_numeric($id) && intval($id) === $id && intval($id) >= 0)) {
			throw new \InvalidArgumentException('Id must be an integer greater than or equal to 0');
		}
		$this->blogId = intval($id);
	}

	/**
	 * Removes a site option from the database.
	 *
	 * @param      $key
	 * @param null $value
	 */
	public function dontHaveSiteOptionInDatabase($key, $value = null) {
		$currentBlogId = $this->blogId;
		$this->useMainBlog();
		$this->dontHaveOptionInDatabase('_site_option_' . $key, $value);
		$this->useBlog($currentBlogId);
	}

	/**
	 * Inserts a site transient in the database.
	 *
	 * If the value is an array or an object then the value will be serialized.
	 *
	 * @param $key
	 * @param $value
	 *
	 * @return int The inserted transient `option_id`
	 */
	public function haveSiteTransientInDatabase($key, $value) {
		$currentBlogId = $this->blogId;
		$this->useMainBlog();
		$option_id = $this->haveOptionInDatabase('_site_transient_' . $key, $value);
		$this->useBlog($currentBlogId);

		return $option_id;
	}

	/**
	 * Removes a site transient from the database.
	 *
	 * @param string $key
	 */
	public function dontHaveSiteTransientInDatabase($key) {
		$currentBlogId = $this->blogId;
		$this->useMainBlog();
		$this->dontHaveOptionInDatabase('_site_transient_' . $key);
		$this->useBlog($currentBlogId);
	}

	/**
	 * Gets a site option from the database.
	 *
	 * @param string $key
	 *
	 * @return mixed|string
	 */
	public function grabSiteOptionFromDatabase($key) {
		$currentBlogId = $this->blogId;
		$this->useMainBlog();
		$value = $this->grabOptionFromDatabase('_site_option_' . $key);
		$this->useBlog($currentBlogId);

		return $value;
	}

	/**
	 * Gets an option from the database.
	 *
	 * @param string $option_name
	 *
	 * @return mixed|string
	 */
	public function grabOptionFromDatabase($option_name) {
		$table        = $this->grabPrefixedTableNameFor('options');
		$option_value = $this->grabFromDatabase($table, 'option_value', ['option_name' => $option_name]);

		return empty($option_value) ? '' : $this->maybeUnserialize($option_value);
	}

	private function maybeUnserialize($value) {
		$unserialized = @unserialize($value);

		return false === $unserialized ? $value : $unserialized;
	}

	/**
	 * Gets a site transient from the database.
	 *
	 * @param string $key
	 *
	 * @return mixed|string
	 */
	public function grabSiteTransientFromDatabase($key) {
		$currentBlogId = $this->blogId;
		$this->useMainBlog();
		$value = $this->grabOptionFromDatabase('_site_transient_' . $key);
		$this->useBlog($currentBlogId);

		return $value;
	}

	/**
	 * Checks that a site option is in the database.
	 *
	 * @param string     $key
	 * @param mixed|null $value
	 */
	public function seeSiteSiteTransientInDatabase($key, $value = null) {
		$currentBlogId = $this->blogId;
		$criteria      = ['option_name' => '_site_transient_' . $key];
		if ($value) {
			$criteria['option_value'] = $value;
		}
		$this->seeOptionInDatabase($criteria);
		$this->useBlog($currentBlogId);
	}

	/**
	 * Checks if an option is in the database for the current blog.
	 *
	 * If checking for an array or an object then the serialized version will be checked for.
	 *
	 * @param array $criteria An array of search criteria.
	 */
	public function seeOptionInDatabase(array $criteria) {
		$tableName = $this->grabPrefixedTableNameFor('options');
		if (!empty($criteria['option_value'])) {
			$criteria['option_value'] = $this->maybeSerialize($criteria['option_value']);
		}
		$this->seeInDatabase($tableName, $criteria);
	}

	/**
	 * Checks that a site option is in the database.
	 *
	 * @param string     $key
	 * @param mixed|null $value
	 */
	public function seeSiteOptionInDatabase($key, $value = null) {
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
	 * Returns the current site url as specified in the module configuration.
	 *
	 * @param string $path A path that should be appended to the site URL.
	 *
	 * @return string The current site URL
	 */
	public function grabSiteUrl($path = null) {
		$url = $this->config['url'];

		if ($path !== null) {
			return Utils::untrailslashit($this->config['url']) . DIRECTORY_SEPARATOR . Utils::unleadslashit($path);
		}

		return $url;
	}

	/**
	 * Inserts many posts in the database returning their IDs.
	 *
	 * @param int   $count     The number of posts to insert.
	 * @param array $overrides {
	 *                         An array of values to override the defaults.
	 *                         The `{{n}}` placeholder can be used to have the post count inserted in its place;
	 *                         e.g. `Post Title - {{n}}` will be set to `Post Title - 0` for the first post,
	 *                         `Post Title - 1` for the second one and so on.
	 *                         The same applies to meta values as well.
	 *
	 * @type array  $meta      An associative array of meta key/values to be set for the post, shorthand for the
	 *       `havePostmetaInDatabase` method. e.g. `['one' => 'foo', 'two' => 'bar']`; to have an array value inserted in a
	 *       single row serialize it e.g.
	 *                    `['serialized_field` => serialize(['one','two','three'])]` otherwise a distinct row will be added for
	 *                    each entry. See `havePostmetaInDatabase` method.
	 * }
	 *
	 * @return array
	 */
	public function haveManyPostsInDatabase($count, array $overrides = []) {
		if (!is_int($count)) {
			throw new \InvalidArgumentException('Count must be an integer value');
		}
		$overrides = $this->setTemplateData($overrides);
		$ids       = [];
		for ($i = 0; $i < $count; $i++) {
			$thisOverrides = $this->replaceNumbersInArray($overrides, $i);
			$ids[]         = $this->havePostInDatabase($thisOverrides);
		}

		return $ids;
	}

	protected function setTemplateData(array $overrides = []) {
		if (empty($overrides['template_data'])) {
			$this->templateData = [];
		}
		else {
			$this->templateData = $overrides['template_data'];
			$overrides          = array_diff_key($overrides, ['template_data' => []]);
		}

		return $overrides;
	}

	protected function replaceNumbersInArray($entry, $i) {
		$out = [];
		foreach ($entry as $key => $value) {
			if (is_array($value)) {
				$out[$this->replaceNumbersInString($key, $i)] = $this->replaceNumbersInArray($value, $i);
			}
			else {
				$out[$this->replaceNumbersInString($key, $i)] = $this->replaceNumbersInString($value, $i);
			}
		}

		return $out;
	}

	/**
	 * @param $value
	 * @param $i
	 *
	 * @return mixed
	 */
	protected function replaceNumbersInString($value, $i) {
		if (!is_string($value)) {
			return $value;
		}
		$thisTemplateData = array_merge($this->templateData, ['n' => $i]);
		array_walk($thisTemplateData, function (&$value) use ($i) {
			$value = is_callable($value) ? $value($i) : $value;
		});

		return $this->handlebars->render($value, $thisTemplateData);
	}

	/**
	 * Checks for a term in the database.
	 *
	 * Looks up the `terms` and `term_taxonomy` prefixed tables.
	 *
	 * @param array $criteria An array of criteria to search for the term, can be columns from the `terms` and the
	 *                        `term_taxonomy` tables.
	 */
	public function seeTermInDatabase(array $criteria) {
		$termsCriteria        = array_intersect_key($criteria, array_flip($this->termKeys));
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
	 * @param array $criteria  An array of search criteria.
	 * @param bool  $purgeMeta Whether the terms meta should be purged along side with the meta or not.
	 */
	public function dontHaveTermInDatabase(array $criteria, $purgeMeta = true) {
		$termRelationshipsKeys = ['term_taxonomy_id'];

		$termTableCriteria         = array_intersect_key($criteria, array_flip($this->termKeys));
		$termTaxonomyTableCriteria = array_intersect_key($criteria, array_flip($this->termTaxonomyKeys));

		if ($purgeMeta) {
			$ids = false;

			if (!empty($termTableCriteria)) {
				$ids = $this->grabAllFromDatabase($this->grabTermsTableName(), 'term_id', $criteria);
			}
			elseif (!empty($termTaxonomyTableCriteria)) {
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
		$this->dontHaveInDatabase($this->grabTermRelationshipsTableName(),
			array_intersect_key($criteria, array_flip($termRelationshipsKeys)));
	}

	/**
	 * Removes a term meta from the database.
	 *
	 * @param array $criteria An array of search criteria.
	 */
	public function dontHaveTermMetaInDatabase(array $criteria) {
		$this->dontHaveInDatabase($this->grabTermMetaTableName(), $criteria);
	}

	/**
	 * Makes sure a term is not in the database.
	 *
	 * Looks up both the `terms` table and the `term_taxonomy` tables.
	 *
	 * @param array $criteria An array of criteria to search for the term, can be columns from the `terms` and the
	 *                        `term_taxonomy` tables.
	 */
	public function dontSeeTermInDatabase(array $criteria) {
		$termsCriteria        = array_intersect_key($criteria, array_flip($this->termKeys));
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
	 * @param int   $count           The number of comments to insert.
	 * @param   int $comment_post_ID The comment parent post ID.
	 * @param array $overrides       An associative array to override the defaults.
	 *
	 * @return int[] An array containing the inserted comments IDs.
	 */
	public function haveManyCommentsInDatabase($count, $comment_post_ID, array $overrides = []) {
		if (!is_int($count)) {
			throw new \InvalidArgumentException('Count must be an integer value');
		}
		$overrides = $this->setTemplateData($overrides);
		$ids       = [];
		for ($i = 0; $i < $count; $i++) {
			$thisOverrides = $this->replaceNumbersInArray($overrides, $i);
			$ids[]         = $this->haveCommentInDatabase($comment_post_ID, $thisOverrides);
		}

		return $ids;
	}

	/**
	 * Inserts a comment in the database.
	 *
	 * @param  int   $comment_post_ID The id of the post the comment refers to.
	 * @param  array $data            The comment data overriding default and random generated values.
	 *
	 * @return int The inserted comment `comment_id`
	 */
	public function haveCommentInDatabase($comment_post_ID, array $data = []) {
		if (!is_int($comment_post_ID)) {
			throw new \BadMethodCallException('Comment post ID must be int');
		}

		$has_meta = !empty($data['meta']);
		$meta     = [];
		if ($has_meta) {
			$meta = $data['meta'];
			unset($data['meta']);
		}

		$comment = Comment::makeComment($comment_post_ID, $data);

		$tableName = $this->grabPrefixedTableNameFor('comments');
		$commentId = $this->haveInDatabase($tableName, $comment);

		if ($has_meta) {
			foreach ($meta as $key => $value) {
				$this->haveCommentMetaInDatabase($commentId, $key, $value);
			}
		}

		return $commentId;
	}

	/**
	 * Inserts a comment meta field in the database.
	 *
	 * Array and object meta values will be serialized.
	 *
	 * @param int    $comment_id
	 * @param string $meta_key
	 * @param mixed  $meta_value
	 *
	 * @return int The inserted comment meta ID
	 */
	public function haveCommentMetaInDatabase($comment_id, $meta_key, $meta_value) {
		if (!is_int($comment_id)) {
			throw new \BadMethodCallException('Comment id must be an int');
		}
		if (!is_string($meta_key)) {
			throw new \BadMethodCallException('Meta key must be an string');
		}

		return $this->haveInDatabase($this->grabCommentmetaTableName(), [
			'comment_id' => $comment_id,
			'meta_key'   => $meta_key,
			'meta_value' => $this->maybeSerialize($meta_value),
		]);
	}

	/**
	 * Returns the prefixed comment meta table name.
	 *
	 * E.g. `wp_commentmeta`.
	 *
	 * @return string
	 */
	public function grabCommentmetaTableName() {
		return $this->grabPrefixedTableNameFor('commentmeta');
	}

	/**
	 * Removes an entry from the comments table.
	 *
	 * @param  array $criteria  An array of search criteria.
	 * @param bool   $purgeMeta If set to `true` then the meta for the comment will be purged too.
	 */
	public function dontHaveCommentInDatabase(array $criteria, $purgeMeta = true) {
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
	 * @return string The prefixed table name, e.g. `wp_comments`.
	 */
	public function grabCommentsTableName() {
		return $this->grabPrefixedTableNameFor('comments');
	}

	/**
	 * Removes an entry from the commentmeta table.
	 *
	 * @param  array $criteria An array of search criteria.
	 */
	public function dontHaveCommentMetaInDatabase(array $criteria) {
		$tableName = $this->grabPrefixedTableNameFor('commentmeta');
		$this->dontHaveInDatabase($tableName, $criteria);
	}

	/**
	 * Inserts many links in the database.
	 *
	 * @param           int $count
	 * @param array|null    $overrides
	 *
	 * @return array An array of inserted `link_id`s.
	 */
	public function haveManyLinksInDatabase($count, array $overrides = []) {
		if (!is_int($count)) {
			throw new \InvalidArgumentException('Count must be an integer value');
		}
		$overrides = $this->setTemplateData($overrides);
		$ids       = [];
		for ($i = 0; $i < $count; $i++) {
			$thisOverrides = $this->replaceNumbersInArray($overrides, $i);
			$ids[]         = $this->haveLinkInDatabase($thisOverrides);
		}

		return $ids;
	}

	/**
	 * Inserts a link in the database.
	 *
	 * @param  array $overrides The data to insert.
	 *
	 * @return int The inserted link `link_id`.
	 */
	public function haveLinkInDatabase(array $overrides = []) {
		$tableName = $this->grabLinksTableName();
		$defaults  = Links::getDefaults();
		$overrides = array_merge($defaults, array_intersect_key($overrides, $defaults));

		return $this->haveInDatabase($tableName, $overrides);
	}

	/**
	 * Returns the prefixed links table name.
	 *
	 * E.g. `wp_links`.
	 *
	 * @return string
	 */
	public function grabLinksTableName() {
		return $this->grabPrefixedTableNameFor('links');
	}

	public function haveManyUsersInDatabase($count, $user_login, $role = 'subscriber', array $overrides = []) {
		if (!is_int($count)) {
			throw new \InvalidArgumentException('Count must be an integer value');
		}
		$ids       = [];
		$overrides = $this->setTemplateData($overrides);
		for ($i = 0; $i < $count; $i++) {
			$thisOverrides = $this->replaceNumbersInArray($overrides, $i);
			$thisUserLogin = false === strpos($user_login,
				$this->numberPlaceholder) ? $user_login . '_' . $i : $this->replaceNumbersInString($user_login, $i);
			$ids[]         = $this->haveUserInDatabase($thisUserLogin, $role, $thisOverrides);
		}

		return $ids;
	}

	/**
	 * Inserts a user and appropriate meta in the database.
	 *
	 * @param  string $user_login The user login slug
	 * @param  string $role       The user role slug, e.g. "administrator"; defaults to "subscriber".
	 * @param  array  $overrides  An associative array of column names and values overridind defaults in the "users"
	 *                            and "usermeta" table.
	 *
	 * @return int The inserted user `ID`
	 */
	public function haveUserInDatabase($user_login, $role = 'subscriber', array $overrides = []) {
		$hasMeta = !empty($overrides['meta']);
		$meta    = [];
		if ($hasMeta) {
			$meta = $overrides['meta'];
			unset($overrides['meta']);
		}

		$userTableData = User::generateUserTableDataFrom($user_login, $overrides);
		$this->debugSection('Generated users table data', json_encode($userTableData));
		$userId = $this->haveInDatabase($this->getUsersTableName(), $userTableData);

		$this->haveUserCapabilitiesInDatabase($userId, $role);
		$this->haveUserLevelsInDatabase($userId, $role);

		if ($hasMeta) {
			foreach ($meta as $key => $value) {
				$this->haveUserMetaInDatabase($userId, $key, $value);
			}
		}

		return $userId;
	}

	/**
	 * Returns the users table name, e.g. `wp_users`.
	 *
	 * @return string
	 */
	protected function getUsersTableName() {
		$usersTableName = $this->grabPrefixedTableNameFor('users');

		return $usersTableName;
	}

	/**
	 * Sets a user capabilities.
	 *
	 * @param int          $userId
	 * @param string|array $role Either a role string (e.g. `administrator`) or an associative array of blog IDs/roles
	 *                           for a multisite installation; e.g. `[1 => 'administrator`, 2 => 'subscriber']`.
	 *
	 * @return array An array of inserted `meta_id`.
	 */
	public function haveUserCapabilitiesInDatabase($userId, $role) {
		if (!is_array($role)) {
			$meta_key   = $this->grabPrefixedTableNameFor() . 'capabilities';
			$meta_value = serialize([$role => 1]);

			return $this->haveUserMetaInDatabase($userId, $meta_key, $meta_value);
		}
		$ids = [];
		foreach ($role as $blogId => $_role) {
			$blogIdAndPrefix = $blogId == 0 ? '' : $blogId . '_';
			$meta_key        = $this->grabPrefixedTableNameFor() . $blogIdAndPrefix . 'capabilities';
			$meta_value      = serialize([$_role => 1]);
			$ids[]           = array_merge($ids, $this->haveUserMetaInDatabase($userId, $meta_key, $meta_value));
		}

		return $ids;
	}

	/**
	 * Sets a user meta.
	 *
	 * @param int    $userId
	 * @param string $meta_key
	 * @param mixed  $meta_value Either a single value or an array of values; objects will be serialized while array of
	 *                           values will trigger the insertion of multiple rows.
	 *
	 * @return array An array of inserted `user_id`.
	 */
	public function haveUserMetaInDatabase($userId, $meta_key, $meta_value) {
		$ids         = [];
		$meta_values = is_array($meta_value) ? $meta_value : [$meta_value];
		foreach ($meta_values as $meta_value) {
			$data  = [
				'user_id'    => $userId,
				'meta_key'   => $meta_key,
				'meta_value' => $this->maybeSerialize($meta_value),
			];
			$ids[] = $this->haveInDatabase($this->grabUsermetaTableName(), $data);
		}

		return $ids;
	}

	/**
	 * Returns the prefixed `usermeta` table name, e.g. `wp_usermeta`.
	 *
	 * @return string
	 */
	public function grabUsermetaTableName() {
		$usermetaTable = $this->grabPrefixedTableNameFor('usermeta');

		return $usermetaTable;
	}

	/**
	 * Sets the user level in the database for a user.
	 *
	 * @param int          $userId
	 * @param string|array $role Either a role string (e.g. `administrator`) or an array of blog IDs/roles for a
	 *                           multisite installation.
	 *
	 * @return array An array of inserted `meta_id`.
	 */
	public function haveUserLevelsInDatabase($userId, $role) {
		if (!is_array($role)) {
			$meta_key   = $this->grabPrefixedTableNameFor() . 'user_level';
			$meta_value = User\Roles::getLevelForRole($role);

			return $this->haveUserMetaInDatabase($userId, $meta_key, $meta_value);
		}
		$ids = [];
		foreach ($role as $blogId => $_role) {
			$blogIdAndPrefix = $blogId == 0 ? '' : $blogId . '_';
			$meta_key        = $this->grabPrefixedTableNameFor() . $blogIdAndPrefix . 'user_level';
			$meta_value      = User\Roles::getLevelForRole($_role);
			$ids[]           = $this->haveUserMetaInDatabase($userId, $meta_key, $meta_value);
		}

		return $ids;
	}

	/**
	 * Inserts many terms in the database.
	 *
	 * @param       int    $count
	 * @param       string $name      The term name.
	 * @param       string $taxonomy  The taxonomy name.
	 * @param array        $overrides An associative array of default overrides.
	 *
	 * @return array An array of inserted terms `term_id`s.
	 */
	public function haveManyTermsInDatabase($count, $name, $taxonomy, array $overrides = []) {
		if (!is_int($count)) {
			throw new \InvalidArgumentException('Count must be an integer value');
		}
		$ids       = [];
		$overrides = $this->setTemplateData($overrides);
		for ($i = 0; $i < $count; $i++) {
			$thisName      = false === strpos($name,
				$this->numberPlaceholder) ? $name . ' ' . $i : $this->replaceNumbersInString($name, $i);
			$thisTaxonomy  = $this->replaceNumbersInString($taxonomy, $i);
			$thisOverrides = $this->replaceNumbersInArray($overrides, $i);
			$ids[]         = $this->haveTermInDatabase($thisName, $thisTaxonomy, $thisOverrides);
		}

		return $ids;
	}

	/**
	 * Checks for a term taxonomy in the database.
	 *
	 * Will look up the prefixed `term_taxonomy` table, e.g. `wp_term_taxonomy`.
	 *
	 * @param array $criteria An array of search criteria.
	 */
	public function seeTermTaxonomyInDatabase(array $criteria) {
		$this->seeInDatabase($this->grabTermTaxonomyTableName(), $criteria);
	}

	/**
	 * Checks that a term taxonomy is not in the database.
	 *
	 * Will look up the prefixed `term_taxonomy` table, e.g. `wp_term_taxonomy`.
	 *
	 * @param array $criteria An array of search criteria.
	 */
	public function dontSeeTermTaxonomyInDatabase(array $criteria) {
		$this->dontSeeInDatabase($this->grabTermTaxonomyTableName(), $criteria);
	}

	/**
	 * Checks for a term meta in the database.
	 *
	 * @param array $criteria An array of search criteria.
	 */
	public function seeTermMetaInDatabase(array $criteria) {
		$this->seeInDatabase($this->grabTermMetaTableName(), $criteria);
	}

	/**
	 * Checks that a term meta is not in the database.
	 *
	 * @param array $criteria An array of search criteria.
	 */
	public function dontSeeTermMetaInDatabase(array $criteria) {
		$this->dontSeeInDatabase($this->grabTermMetaTableName(), $criteria);
	}

	/**
	 * Checks for a table in the database.
	 *
	 * @param string $table
	 */
	public function seeTableInDatabase($table) {
		$count = $this->_seeTableInDatabase($table);

		$this->assertTrue($count > 0, "No matching tables found for table '" . $table . "' in database.");
	}

	/**
	 * @param $table
	 *
	 * @return int
	 */
	protected function _seeTableInDatabase($table) {
		$dbh = $this->driver->getDbh();
		$sth = $dbh->prepare('SHOW TABLES LIKE :table');
		$this->debugSection('Query', $sth->queryString);
		$sth->execute(['table' => $table]);
		$count = $sth->rowCount();

		return $count == 1;
	}

	/**
	 * Gets the prefixed `blog_versions` table name.
	 *
	 * @return string
	 */
	public function grabBlogVersionsTableName() {
		return $this->grabPrefixedTableNameFor('blog_versions');
	}

	/**
	 * Gets the prefixed `sitemeta` table name.
	 *
	 * @return string
	 */
	public function grabSiteMetaTableName() {
		return $this->grabPrefixedTableNameFor('sitemeta');
	}

	/**
	 * Gets the prefixed `signups` table name.
	 *
	 * @return string
	 */
	public function grabSignupsTableName() {
		return $this->grabPrefixedTableNameFor('signups');
	}

	/**
	 * Gets the prefixed `registration_log` table name.
	 *
	 * @return string
	 */
	public function grabRegistrationLogTableName() {
		return $this->grabPrefixedTableNameFor('registration_log');
	}

	/**
	 * Gets the prefixed `site` table name.
	 *
	 * @return string
	 */
	public function grabSiteTableName() {
		return $this->grabPrefixedTableNameFor('site');
	}

	/**
	 * Checks for a blog in the database, looks up the `blogs` table.
	 *
	 * @param array $criteria An array of search criteria.
	 */
	public function seeBlogInDatabase(array $criteria) {
		$this->seeInDatabase($this->grabBlogsTableName(), $criteria);
	}

	/**
	 * Gets the prefixed `blogs` table name.
	 *
	 * @return string
	 */
	public function grabBlogsTableName() {
		return $this->grabPrefixedTableNameFor('blogs');
	}

	/**
	 * Inserts many blogs in the database.
	 *
	 * @param int   $count
	 * @param array $overrides
	 *
	 * @return array An array of inserted blogs `blog_id`s.
	 */
	public function haveManyBlogsInDatabase($count, array $overrides = []) {
		$blogIds   = [];
		$overrides = $this->setTemplateData($overrides);
		for ($i = 0; $i < $count; $i++) {
			$blogIds[] = $this->haveBlogInDatabase('blog' . $i, $this->replaceNumbersInArray($overrides, $i));
		}

		return $blogIds;
	}

	/**
	 * Inserts a blog in the `blogs` table.
	 *
	 * @param  string $domainOrPath The subdomain or the path to the be used for the blog.
	 * @param array   $overrides    An array of values to override the defaults.
	 *
	 * @return int The inserted blog `blog_id`.
	 */
	public function haveBlogInDatabase($domainOrPath, array $overrides = []) {
		$defaults = Blog::makeDefaults($this->isSubdomainMultisiteInstall);
		if ($this->isSubdomainMultisiteInstall) {
			if (empty($overrides['domain'])) {
				$defaults['domain'] = sprintf('%s.%s', $domainOrPath, $this->getSiteDomain());
			}
			$defaults['path'] = '/';
		}
		else {
			$defaults['domain'] = $this->getSiteDomain();
			$defaults['path']   = sprintf('/%s/', $domainOrPath);
		}
		$data = array_merge($defaults, array_intersect_key($overrides, $defaults));

		$blogId = $this->haveInDatabase($this->grabBlogsTableName(), $data);

		return $blogId;
	}

	/**
	 * Returns the site domain inferred from the `url` set in the config.
	 *
	 * @return string
	 */
	public function getSiteDomain() {
		$domain = last(preg_split('~//~', $this->config['url']));

		return $domain;
	}

	/**
	 * Removes an entry from the `blogs` table.
	 *
	 * @param array $criteria An array of search criteria.
	 */
	public function dontHaveBlogInDatabase(array $criteria) {
		$this->dontHaveInDatabase($this->grabBlogsTableName(), $criteria);
	}

	/**
	 * Checks that a row is not present in the `blogs` table.
	 *
	 * @param array $criteria An array of search criteria.
	 */
	public function dontSeeBlogInDatabase(array $criteria) {
		$this->dontSeeInDatabase($this->grabBlogsTableName(), $criteria);
	}

	/**
	 * Returns the table prefix, namespaced for secondary blogs if selected.
	 *
	 * @return string The blog aware table prefix.
	 */
	public function grabTablePrefix() {
		return $this->tablePrefix;
	}

	/**
	 * Sets the current theme options.
	 *
	 * @param string      $stylesheet The theme stylesheet slug, e.g. `twentysixteen`.
	 * @param string|null $template   The theme template slug, e.g. `twentysixteen`, defaults to `$stylesheet`.
	 * @param string|null $themeName  The theme name, e.g. `Twentysixteen`, defaults to title version of `$stylesheet`.
	 */
	public function useTheme($stylesheet, $template = null, $themeName = null) {
		if (!(is_string($stylesheet))) {
			throw new \InvalidArgumentException('Stylesheet must be a string');
		}
		if (!(is_string($template) || is_null($template))) {
			throw new \InvalidArgumentException('Template must either be a string or be null.');
		}
		if (!(is_string($themeName) || is_null($themeName))) {
			throw new \InvalidArgumentException('Current Theme must either be a string or be null.');
		}

		$template  = $template ?: $stylesheet;
		$themeName = $themeName ?: ucwords($stylesheet, " _");

		$this->haveOptionInDatabase('stylesheet', $stylesheet);
		$this->haveOptionInDatabase('template', $template);
		$this->haveOptionInDatabase('current_theme', $themeName);

		$this->stylesheet         = $stylesheet;
		$this->menus[$stylesheet] = empty($this->menus[$stylesheet]) ? [] : $this->menus[$stylesheet];
	}

	/**
	 * Creates and adds a menu to a theme location in the database.
	 *
	 * @param string $slug      The menu slug.
	 * @param string $location  The theme menu location the menu will be assigned to.
	 * @param array  $overrides An array of values to override the defaults.
	 *
	 * @return array An array containing the created menu `term_id` and `term_taxonomy_id`.
	 */
	public function haveMenuInDatabase($slug, $location, array $overrides = []) {
		if (!is_string($slug)) {
			throw new \InvalidArgumentException('Menu slug must be a string.');
		}
		if (!is_string($location)) {
			throw new \InvalidArgumentException('Menu location must be a string.');
		}

		if (empty($this->stylesheet)) {
			throw new \RuntimeException('Stylesheet must be set to add menus, use `useTheme` first.');
		}

		$title   = empty($overrides['title']) ? ucwords($slug, ' -_') : $overrides['title'];
		$menuIds = $this->haveTermInDatabase($title, 'nav_menu', ['slug' => $slug]);

		$menuTermTaxonomyIds = reset($menuIds);

		// set theme options to use the `primary` location
		$this->haveOptionInDatabase('theme_mods_' . $this->stylesheet,
			['nav_menu_locations' => [$location => $menuTermTaxonomyIds]]);

		$this->menus[$this->stylesheet][$slug]     = $menuIds;
		$this->menuItems[$this->stylesheet][$slug] = [];

		return $menuIds;
	}

	/**
	 * Adds a menu element to a menu for the current theme.
	 *
	 * @param string     $menuSlug  The menu slug the item should be added to.
	 * @param string     $title     The menu item title.
	 * @param int|null   $menuOrder An optional menu order, `1` based.
	 * @param array|null $meta      An associative array that will be prefixed with `_menu_item_` for the item post meta.
	 *
	 * @return int The menu item post `ID`
	 */
	public function haveMenuItemInDatabase($menuSlug, $title, $menuOrder = null, array $meta = []) {
		if (!is_string($menuSlug)) {
			throw new \InvalidArgumentException('Menu slug must be a string.');
		}

		if (empty($this->stylesheet)) {
			throw new \RuntimeException('Stylesheet must be set to add menus, use `useTheme` first.');
		}
		if (!array_key_exists($menuSlug, $this->menus[$this->stylesheet])) {
			throw new \RuntimeException("Menu $menuSlug is not a registered menu for the current theme.");
		}
		$menuOrder  = $menuOrder ?: count($this->menuItems[$this->stylesheet][$menuSlug]) + 1;
		$menuItemId = $this->havePostInDatabase([
			'post_title' => $title,
			'menu_order' => $menuOrder,
			'post_type'  => 'nav_menu_item',
		]);
		$defaults   = [
			'type'   => 'custom',
			'object' => 'custom',
			'url'    => 'http://example.com',
		];
		$meta       = array_merge($defaults, $meta);
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
	 * @param array $criteria An array of search criteria.
	 */
	public function seeTermRelationshipInDatabase(array $criteria) {
		$this->seeInDatabase($this->grabPrefixedTableNameFor('term_relationships'), $criteria);
	}

	/**
	 * @param Db|MsSql|ExtendedMySql|Oracle|PostgreSql|Sqlite $driver
	 */
	public function _setDriver($driver) {
		$this->driver = $driver;
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
	protected function maybeCheckTermExistsInDatabase($term_id) {
		if (!isset($this->config['checkExistence']) or false == $this->config['checkExistence']) {
			return;
		}
		$tableName = $this->grabPrefixedTableNameFor('terms');
		if (!$this->grabFromDatabase($tableName, 'term_id', ['term_id' => $term_id])) {
			throw new \RuntimeException("A term with an id of $term_id does not exist", 1);
		}
	}

	/**
	 * @return string
	 */
	protected function getSiteSubfolder() {
		$frags     = explode($this->getSiteDomain(), $this->config['url']);
		$subfolder = ltrim(end($frags), '/');

		return $subfolder;
	}

	protected function loadDumpUsingDriver() {
		if (!$this->sql) {
			$this->debugSection('WPDb', 'No SQL loaded, loading dump skipped');

			return;
		}

		$sql = $this->replaceSiteDomainInSqlArray($this->sql);
		$sql = $this->replaceSiteDomainInMultisiteSqlArray($sql);

		$this->driver->load($sql);
		$this->populated = true;
	}

	/**
	 * Replaces the WordPress domains in an array of SQL dump string.
	 *
	 * @param array $sql The input SQL dump array.
	 *
	 * @return array The modified SQL array.
	 */
	public function replaceSiteDomainInSqlArray(array $sql) {
		if (empty($sql)) {
			return [];
		}

		return array_map([$this, 'replaceSiteDomainInSqlString'], $sql);
	}

	/**
	 * Replaces the site domain in the multisite tables of an array of SQL dump strings.
	 *
	 * @param array $sql The input SQL dump array.
	 *
	 * @return array The modified SQL array.
	 */
	public function replaceSiteDomainInMultisiteSqlArray(array $sql) {
		if (empty($sql)) {
			return [];
		}

		return array_map([$this, 'replaceSiteDomainInMultisiteSqlString'], $sql);
	}

	/**
	 * Creates the database entries representing an attachment and moves the attachment file to the right location.
	 *
	 * Requires the WPFilesystem module.
	 *
	 * @param string     $file       The absolute path to the attachment file.
	 * @param string|int $date       Either a string supported by the `strtotime` function or a UNIX timestamp that should be used to
	 *                               build the "year/time" uploads sub-folder structure.
	 * @param array      $overrides  An associative array of values overriding the default ones.
	 * @param array      $imageSizes An associative array in the format [ <size> => [<width>,<height>]] to override the image sizes created by
	 *                               default.
	 *
	 * @return int If the WPFilesystem module is not loaded in the suite.
	 * @throws \Codeception\Exception\ModuleException If the WPFilesystem module is not loaded in the suite.
	 */
	public function haveAttachmentInDatabase($file, $date = 'now', array $overrides = [], $imageSizes = null) {
		/** @var WPFilesystem $fs */
		try {
			$fs = $this->getModule('WPFilesystem');
		} catch (ModuleException $e) {
			$method = __METHOD__;
			throw new ModuleException(__CLASS__, "the {$method} method requires the WPFilesystem module.");
		}

		$pathInfo = pathinfo($file);
		$slug     = str_slug($pathInfo['filename']);

		$uploadedFilePath = $fs->writeToUploadedFile($pathInfo['basename'], file_get_contents($file), $date);
		$uploadUrl        = $this->grabSiteUrl(str_replace($fs->getWpRootFolder(), '', $uploadedFilePath));
		$uploadLocation   = Utils::unleadslashit(str_replace($fs->getUploadsPath(), '', $uploadedFilePath));

		$mimeType = mime_content_type($file);

		$overrides = array_merge([
			'post_type'      => 'attachment',
			'post_title'     => $slug,
			'post_status'    => 'inherit',
			'post_name'      => $slug,
			'post_parent'    => '0',
			'guid'           => $uploadUrl,
			'post_mime_type' => $mimeType,
		], $overrides);

		$mimeType = $overrides['post_mime_type'];

		$id = $this->havePostInDatabase($overrides);

		$imageInfo = getimagesize($file);

		$this->havePostmetaInDatabase($id, '_wp_attached_file', $uploadLocation);

		if ($imageInfo === false) {
			return $id;
		}

		$imageWidth  = $imageInfo[0];
		$imageHeight = $imageInfo[1];

		if ($imageSizes === null) {
			$imageSizes = [
				'thumbnail' => [150, 150],
				'medium'    => 300,
				'large'     => 768,
			];
		}

		$createdImages = [];
		foreach ($imageSizes as $size => $thisSizes) {
			$thisSizes = (array) $thisSizes;
			$width     = (int) $thisSizes[0];
			$height    = isset($thisSizes[1]) ? (int) $thisSizes[1] : false;

			try {
				$image = new ImageResize($file);
			} catch (ImageResizeException $e) {
				throw new ModuleException(__CLASS__, "Could not initialize image processing class for file [{$file}]");
			}

			if (empty($height)) {
				// resize to width
				$height = (int) ($imageHeight * ($width / $imageWidth));
				$image->resizeToWidth($width);
			}
			elseif (empty($width)) {
				// resize to height
				$width = (int) ($imageWidth * ($height / $imageHeight));
				$image->resizeToHeight($height);
			}
			else {
				// resize width and height
				$image->resizeToBestFit($width, $height);
			}

			$image->save(str_replace($slug, "{$slug}-{$width}x{$height}", $uploadedFilePath));

			$createdImages[$size] = (object) ['width' => $width, 'height' => $height];
		}

		$metadata = [
			'width'      => $imageWidth,
			'height'     => $imageHeight,
			'file'       => $uploadLocation,
			'sizes'      => array_combine(array_keys($createdImages), array_map(function ($sizes) use ($slug, $mimeType) {
				return [
					'file'      => "{$slug}-{$sizes->width}x{$sizes->height}.jpeg",
					'width'     => $sizes->width,
					'height'    => $sizes->height,
					'mime-type' => $mimeType,
				];
			}, $createdImages)),
			'image_meta' =>
				[
					'aperture'          => '0',
					'credit'            => '',
					'camera'            => '',
					'caption'           => '',
					'created_timestamp' => '0',
					'copyright'         => '',
					'focal_length'      => '0',
					'iso'               => '0',
					'shutter_speed'     => '0',
					'title'             => '',
					'orientation'       => '0',
					'keywords'          => [],
				],
		];
		$this->havePostmetaInDatabase($id, '_wp_attachment_metadata', $metadata);

		return $id;
	}

	/**
	 * Checks for an attachment in the database.
	 *
	 * @param  array $criteria An array of search criteria.
	 */
	public function seeAttachmentInDatabase(array $criteria) {
		$this->seePostInDatabase(array_merge($criteria, ['post_type' => 'attachment']));
	}

	/**
	 * Checks that an attachment is not in the database.
	 *
	 * @param  array $criteria An array of search criteria.
	 */
	public function dontSeeAttachmentInDatabase(array $criteria) {
		$this->dontSeePostInDatabase(array_merge($criteria, ['post_type' => 'attachment']));
	}

	/**
	 * Removes an attachment from the posts table.
	 *
	 * @param  array $criteria  An array of search criteria.
	 * @param bool   $purgeMeta If set to `true` then the meta for the attachment will be purged too.
	 */
	public function dontHaveAttachmentInDatabase(array $criteria, $purgeMeta = true) {
		$this->dontHavePostInDatabase(array_merge($criteria, ['post_type' => 'attachment']), $purgeMeta);
	}
}

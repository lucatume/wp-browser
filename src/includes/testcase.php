<?php

require_once __DIR__ . '/factory.php';
require_once __DIR__ . '/trac.php';

/**
 * Defines a basic fixture to run multiple tests.
 *
 * Resets the state of the WordPress installation before and after every test.
 *
 * Includes utility functions and assertions useful for testing WordPress.
 *
 * All WordPress unit tests should inherit from this class.
 */
class WP_UnitTestCase extends PHPUnit_Framework_TestCase {

	protected static $forced_tickets = [];
	protected $expected_deprecated = [];
	protected $caught_deprecated = [];
	protected $expected_doing_it_wrong = [];
	protected $caught_doing_it_wrong = [];

	protected static $hooks_saved = [];
	protected static $ignore_files;

	function __isset( $name ) {
		return 'factory' === $name;
 	}

	function __get( $name ) {
		if ( 'factory' === $name ) {
			return self::factory();
 	    }
 	}

	protected static function factory() {
		static $factory = null;
		if ( ! $factory ) {
			$factory = new WP_UnitTest_Factory();
		}
		return $factory;
	}

	public static function get_called_class() {
		if ( function_exists( 'get_called_class' ) ) {
			return static::class;
		}

		// PHP 5.2 only
		$backtrace = debug_backtrace();
		// [0] WP_UnitTestCase::get_called_class()
		// [1] WP_UnitTestCase::setUpBeforeClass()
		if ( 'call_user_func' ===  $backtrace[2]['function'] ) {
			return $backtrace[2]['args'][0][0];
		}
		return $backtrace[2]['class'];
	}

	public static function setUpBeforeClass() {
		global $wpdb;

		$wpdb->suppress_errors = false;
		$wpdb->show_errors = true;
		$wpdb->db_connect();
		ini_set('display_errors', 1 );

		parent::setUpBeforeClass();

		$c = self::get_called_class();
		if ( ! method_exists( $c, 'wpSetUpBeforeClass' ) ) {
			self::commit_transaction();
			return;
		}

		call_user_func( [$c, 'wpSetUpBeforeClass'], self::factory() );

		self::commit_transaction();
	}

	public static function tearDownAfterClass() {
		parent::tearDownAfterClass();

		_delete_all_data();
		self::flush_cache();

		$c = self::get_called_class();
		if ( ! method_exists( $c, 'wpTearDownAfterClass' ) ) {
			self::commit_transaction();
			return;
		}

		call_user_func( [$c, 'wpTearDownAfterClass'] );

		self::commit_transaction();
	}

	function setUp() {
		set_time_limit(0);

		if ( ! self::$ignore_files ) {
			self::$ignore_files = $this->scan_user_uploads();
		}

		if ( ! self::$hooks_saved ) {
			$this->_backup_hooks();
		}

		global $wp_rewrite;

		$this->clean_up_global_scope();

		/*
		 * When running core tests, ensure that post types and taxonomies
		 * are reset for each test. We skip this step for non-core tests,
		 * given the large number of plugins that register post types and
		 * taxonomies at 'init'.
		 */
		if ( defined( 'WP_RUN_CORE_TESTS' ) && WP_RUN_CORE_TESTS ) {
			$this->reset_post_types();
			$this->reset_taxonomies();
			$this->reset_post_statuses();
			$this->reset__SERVER();

			if ( $wp_rewrite->permalink_structure ) {
				$this->set_permalink_structure( '' );
			}
		}

		$this->start_transaction();
		$this->expectDeprecated();
		add_filter( 'wp_die_handler', [$this, 'get_wp_die_handler'] );
	}

	/**
	 * Detect post-test failure conditions.
	 *
	 * We use this method to detect expectedDeprecated and expectedIncorrectUsage annotations.
	 *
	 * @since 4.2.0
	 */
	protected function assertPostConditions() {
		$this->expectedDeprecated();
	}

	/**
	 * After a test method runs, reset any state in WordPress the test method might have changed.
	 */
	function tearDown() {
		global $wpdb, $wp_query, $wp;
		$wpdb->query( 'ROLLBACK' );
		if ( is_multisite() ) {
			while ( ms_is_switched() ) {
				restore_current_blog();
			}
		}
		$wp_query = new WP_Query();
		$wp = new WP();

		// Reset globals related to the post loop and `setup_postdata()`.
		$post_globals = ['post', 'id', 'authordata', 'currentday', 'currentmonth', 'page', 'pages', 'multipage', 'more', 'numpages'];
		foreach ( $post_globals as $global ) {
			$GLOBALS[ $global ] = null;
		}

		remove_theme_support( 'html5' );
		remove_filter( 'query', [$this, '_create_temporary_tables'] );
		remove_filter( 'query', [$this, '_drop_temporary_tables'] );
		remove_filter( 'wp_die_handler', [$this, 'get_wp_die_handler'] );
		$this->_restore_hooks();
		wp_set_current_user( 0 );
	}

	function clean_up_global_scope() {
		$_GET = [];
		$_POST = [];
		self::flush_cache();
	}

	/**
	 * Unregister existing post types and register defaults.
	 *
	 * Run before each test in order to clean up the global scope, in case
	 * a test forgets to unregister a post type on its own, or fails before
	 * it has a chance to do so.
	 */
	protected function reset_post_types() {
		foreach ( get_post_types( [], 'objects' ) as $pt ) {
			if ( empty( $pt->tests_no_auto_unregister ) ) {
				_unregister_post_type( $pt->name );
			}
		}
		create_initial_post_types();
	}

	/**
	 * Unregister existing taxonomies and register defaults.
	 *
	 * Run before each test in order to clean up the global scope, in case
	 * a test forgets to unregister a taxonomy on its own, or fails before
	 * it has a chance to do so.
	 */
	protected function reset_taxonomies() {
		foreach ( get_taxonomies() as $tax ) {
			_unregister_taxonomy( $tax );
		}
		create_initial_taxonomies();
	}

	/**
	 * Unregister non-built-in post statuses.
	 */
	protected function reset_post_statuses() {
		foreach ( get_post_stati( ['_builtin' => false] ) as $post_status ) {
			_unregister_post_status( $post_status );
		}
	}

	/**
	 * Reset `$_SERVER` variables
	 */
	protected function reset__SERVER() {
		tests_reset__SERVER();
	}

	/**
	 * Saves the action and filter-related globals so they can be restored later.
	 *
	 * Stores $merged_filters, $wp_actions, $wp_current_filter, and $wp_filter
	 * on a class variable so they can be restored on tearDown() using _restore_hooks().
	 *
	 * @global array $merged_filters
	 * @global array $wp_actions
	 * @global array $wp_current_filter
	 * @global array $wp_filter
	 * @return void
	 */
	protected function _backup_hooks() {
		global $wp_version;

		if(version_compare($wp_version, '4.7.0','<')){
			$globals = ['merged_filters', 'wp_actions', 'wp_current_filter', 'wp_filter'];
			foreach ( $globals as $key ) {
				self::$hooks_saved[ $key ] = $GLOBALS[ $key ];
			}
		} else {
			$globals = ['wp_actions', 'wp_current_filter'];
			foreach ( $globals as $key ) {
				self::$hooks_saved[ $key ] = $GLOBALS[ $key ];
			}
			self::$hooks_saved['wp_filter'] = [];
			foreach ( $GLOBALS['wp_filter'] as $hook_name => $hook_object ) {
				self::$hooks_saved['wp_filter'][ $hook_name ] = clone $hook_object;
			}
		}
	}

	/**
	 * Restores the hook-related globals to their state at setUp()
	 * so that future tests aren't affected by hooks set during this last test.
	 *
	 * @global array $merged_filters
	 * @global array $wp_actions
	 * @global array $wp_current_filter
	 * @global array $wp_filter
	 * @return void
	 */
	protected function _restore_hooks() {
		global $wp_version;

		if(version_compare($wp_version, '4.7.0','<')){
			$globals = ['merged_filters', 'wp_actions', 'wp_current_filter', 'wp_filter'];
			foreach ( $globals as $key ) {
				if ( isset( self::$hooks_saved[ $key ] ) ) {
					$GLOBALS[ $key ] = self::$hooks_saved[ $key ];
				}
			}
		} else {
			$globals = ['wp_actions', 'wp_current_filter'];
			foreach ( $globals as $key ) {
				if ( isset( self::$hooks_saved[ $key ] ) ) {
					$GLOBALS[ $key ] = self::$hooks_saved[ $key ];
				}
			}
			if ( isset( self::$hooks_saved['wp_filter'] ) ) {
				$GLOBALS['wp_filter'] = [];
				foreach ( self::$hooks_saved['wp_filter'] as $hook_name => $hook_object ) {
					$GLOBALS['wp_filter'][ $hook_name ] = clone $hook_object;
				}
			}
		}
	}

	static function flush_cache() {
		global $wp_object_cache;
		$wp_object_cache->group_ops = [];
		$wp_object_cache->stats = [];
		$wp_object_cache->memcache_debug = [];
		$wp_object_cache->cache = [];
		if ( method_exists( $wp_object_cache, '__remoteset' ) ) {
			$wp_object_cache->__remoteset();
		}
		wp_cache_flush();
		wp_cache_add_global_groups( ['users', 'userlogins', 'usermeta', 'user_meta', 'site-transient', 'site-options', 'site-lookup', 'blog-lookup', 'blog-details', 'rss', 'global-posts', 'blog-id-cache'] );
		wp_cache_add_non_persistent_groups( ['comment', 'counts', 'plugins'] );
	}

	function start_transaction() {
		global $wpdb;
		$wpdb->query( 'SET autocommit = 0;' );
		$wpdb->query( 'START TRANSACTION;' );
		add_filter( 'query', [$this, '_create_temporary_tables'] );
		add_filter( 'query', [$this, '_drop_temporary_tables'] );
	}

	/**
	 * Commit the queries in a transaction.
	 *
	 * @since 4.1.0
	 */
	public static function commit_transaction() {
		global $wpdb;
		$wpdb->query( 'COMMIT;' );
	}

	function _create_temporary_tables( $query ) {
		if ( str_starts_with(trim( $query ), 'CREATE TABLE') )
			return substr_replace( trim( $query ), 'CREATE TEMPORARY TABLE', 0, 12 );
		return $query;
	}

	function _drop_temporary_tables( $query ) {
		if ( str_starts_with(trim( $query ), 'DROP TABLE') )
			return substr_replace( trim( $query ), 'DROP TEMPORARY TABLE', 0, 10 );
		return $query;
	}

	function get_wp_die_handler( $handler ) {
		return [$this, 'wp_die_handler'];
	}

	function wp_die_handler( $message ) {
		if ( ! is_scalar( $message ) ) {
			$message = '0';
		}

		throw new WPDieException( $message );
	}

	function expectDeprecated() {
		$annotations = \PHPUnit\Util\Test::parseTestMethodAnnotations(
			$this::class,
			$this->name
		);
		foreach ( ['class', 'method'] as $depth ) {
			if ( ! empty( $annotations[ $depth ]['expectedDeprecated'] ) )
				$this->expected_deprecated = array_merge( $this->expected_deprecated, $annotations[ $depth ]['expectedDeprecated'] );
			if ( ! empty( $annotations[ $depth ]['expectedIncorrectUsage'] ) )
				$this->expected_doing_it_wrong = array_merge( $this->expected_doing_it_wrong, $annotations[ $depth ]['expectedIncorrectUsage'] );
		}
		add_action( 'deprecated_function_run', [$this, 'deprecated_function_run'] );
		add_action( 'deprecated_argument_run', [$this, 'deprecated_function_run'] );
		add_action( 'deprecated_hook_run', [$this, 'deprecated_function_run'] );
		add_action( 'doing_it_wrong_run', [$this, 'doing_it_wrong_run'] );
		add_action( 'deprecated_function_trigger_error', '__return_false' );
		add_action( 'deprecated_argument_trigger_error', '__return_false' );
		add_action( 'deprecated_hook_trigger_error',     '__return_false' );
		add_action( 'doing_it_wrong_trigger_error',      '__return_false' );
	}

	function expectedDeprecated() {
		$errors = [];

		$not_caught_deprecated = array_diff( $this->expected_deprecated, $this->caught_deprecated );
		foreach ( $not_caught_deprecated as $not_caught ) {
			$errors[] = "Failed to assert that $not_caught triggered a deprecated notice";
		}

		$unexpected_deprecated = array_diff( $this->caught_deprecated, $this->expected_deprecated );
		foreach ( $unexpected_deprecated as $unexpected ) {
			$errors[] = "Unexpected deprecated notice for $unexpected";
		}

		$not_caught_doing_it_wrong = array_diff( $this->expected_doing_it_wrong, $this->caught_doing_it_wrong );
		foreach ( $not_caught_doing_it_wrong as $not_caught ) {
			$errors[] = "Failed to assert that $not_caught triggered an incorrect usage notice";
		}

		$unexpected_doing_it_wrong = array_diff( $this->caught_doing_it_wrong, $this->expected_doing_it_wrong );
		foreach ( $unexpected_doing_it_wrong as $unexpected ) {
			$errors[] = "Unexpected incorrect usage notice for $unexpected";
		}

		if ( ! empty( $errors ) ) {
			$this->fail( implode( "\n", $errors ) );
		}
	}

	/**
	 * Declare an expected `_deprecated_function()` or `_deprecated_argument()` call from within a test.
	 *
	 * @since 4.2.0
	 *
	 * @param string $deprecated Name of the function, method, class, or argument that is deprecated. Must match
	 *                           first parameter of the `_deprecated_function()` or `_deprecated_argument()` call.
	 */
	public function setExpectedDeprecated( $deprecated ) {
		array_push( $this->expected_deprecated, $deprecated );
	}

	/**
	 * Declare an expected `_doing_it_wrong()` call from within a test.
	 *
	 * @since 4.2.0
	 *
	 * @param string $deprecated Name of the function, method, or class that appears in the first argument of the
	 *                           source `_doing_it_wrong()` call.
	 */
	public function setExpectedIncorrectUsage( $doing_it_wrong ) {
		array_push( $this->expected_doing_it_wrong, $doing_it_wrong );
	}

	function deprecated_function_run( $function ) {
		if ( ! in_array( $function, $this->caught_deprecated ) )
			$this->caught_deprecated[] = $function;
	}

	function doing_it_wrong_run( $function ) {
		if ( ! in_array( $function, $this->caught_doing_it_wrong ) )
			$this->caught_doing_it_wrong[] = $function;
	}

	function assertWPError( $actual, $message = '' ) {
		$this->assertInstanceOf( 'WP_Error', $actual, $message );
	}

	function assertNotWPError( $actual, $message = '' ) {
		if ( is_wp_error( $actual ) && '' === $message ) {
			$message = $actual->get_error_message();
		}
		$this->assertNotInstanceOf( 'WP_Error', $actual, $message );
	}

	function assertEqualFields( $object, $fields ) {
		foreach( $fields as $field_name => $field_value ) {
			if ( $object->$field_name != $field_value ) {
				$this->fail();
			}
		}
	}

	function assertDiscardWhitespace( $expected, $actual ) {
		$this->assertEquals( preg_replace( '/\s*/', '', $expected ), preg_replace( '/\s*/', '', $actual ) );
	}

	function assertEqualSets( $expected, $actual ) {
		sort( $expected );
		sort( $actual );
		$this->assertEquals( $expected, $actual );
	}

	function assertEqualSetsWithIndex( $expected, $actual ) {
		ksort( $expected );
		ksort( $actual );
		$this->assertEquals( $expected, $actual );
	}

	/**
	 * Asserts that the given variable is a multidimensional array, and that all arrays are non-empty.
	 *
	 * @param array $array
	 */
	function assertNonEmptyMultidimensionalArray( $array ) {
		$this->assertTrue( is_array( $array ) );
		$this->assertNotEmpty( $array );

		foreach( $array as $sub_array ) {
			$this->assertTrue( is_array( $sub_array ) );
			$this->assertNotEmpty( $sub_array );
		}
	}

	/**
	 * Asserts that a condition is not false.
	 *
	 * @param bool   $condition
	 * @param string $message
	 *
	 * @throws PHPUnit_Framework_AssertionFailedError
	 */
	public static function assertNotFalse( $condition, $message = '' ) {
		self::assertThat( $condition, self::logicalNot( self::isFalse() ), $message );
	}

	/**
	 * Modify WordPress's query internals as if a given URL has been requested.
	 *
	 * @param string $url The URL for the request.
	 */
	function go_to( $url ) {
		// note: the WP and WP_Query classes like to silently fetch parameters
		// from all over the place (globals, GET, etc), which makes it tricky
		// to run them more than once without very carefully clearing everything
		$_GET = $_POST = [];
		foreach (['query_string', 'id', 'postdata', 'authordata', 'day', 'currentmonth', 'page', 'pages', 'multipage', 'more', 'numpages', 'pagenow'] as $v) {
			if ( isset( $GLOBALS[$v] ) ) unset( $GLOBALS[$v] );
		}
		$parts = parse_url($url);
		if (isset($parts['scheme'])) {
			$req = $parts['path'] ?? '';
			if (isset($parts['query'])) {
				$req .= '?' . $parts['query'];
				// parse the url query vars into $_GET
				parse_str($parts['query'], $_GET);
			}
		} else {
			$req = $url;
		}
		if ( ! isset( $parts['query'] ) ) {
			$parts['query'] = '';
		}

		$_SERVER['REQUEST_URI'] = $req;
		unset($_SERVER['PATH_INFO']);

		self::flush_cache();
		unset($GLOBALS['wp_query'], $GLOBALS['wp_the_query']);
		$GLOBALS['wp_the_query'] = new WP_Query();
		$GLOBALS['wp_query'] = $GLOBALS['wp_the_query'];

		$public_query_vars  = $GLOBALS['wp']->public_query_vars;
		$private_query_vars = $GLOBALS['wp']->private_query_vars;

		$GLOBALS['wp'] = new WP();
		$GLOBALS['wp']->public_query_vars  = $public_query_vars;
		$GLOBALS['wp']->private_query_vars = $private_query_vars;

		_cleanup_query_vars();

		$GLOBALS['wp']->main($parts['query']);
	}

	/**
	 * Skips the current test if there is an open WordPress ticket with id $ticket_id
	 */
	function knownWPBug( $ticket_id ) {
		if ( WP_TESTS_FORCE_KNOWN_BUGS || in_array( $ticket_id, self::$forced_tickets ) )
			return;
		if ( ! TracTickets::isTracTicketClosed( 'https://core.trac.wordpress.org', $ticket_id ) )
			$this->markTestSkipped( sprintf( 'WordPress Ticket #%d is not fixed', $ticket_id ) );
	}

	/**
	 * Skips the current test if there is an open unit tests ticket with id $ticket_id
	 */
	function knownUTBug( $ticket_id ) {
		if ( WP_TESTS_FORCE_KNOWN_BUGS || in_array( 'UT' . $ticket_id, self::$forced_tickets ) )
			return;
		if ( ! TracTickets::isTracTicketClosed( 'https://unit-tests.trac.wordpress.org', $ticket_id ) )
			$this->markTestSkipped( sprintf( 'Unit Tests Ticket #%d is not fixed', $ticket_id ) );
	}

	/**
	 * Skips the current test if there is an open plugin ticket with id $ticket_id
	 */
	function knownPluginBug( $ticket_id ) {
		if ( WP_TESTS_FORCE_KNOWN_BUGS || in_array( 'Plugin' . $ticket_id, self::$forced_tickets ) )
			return;
		if ( ! TracTickets::isTracTicketClosed( 'https://plugins.trac.wordpress.org', $ticket_id ) )
			$this->markTestSkipped( sprintf( 'WordPress Plugin Ticket #%d is not fixed', $ticket_id ) );
	}

	public static function forceTicket( $ticket ) {
		self::$forced_tickets[] = $ticket;
	}

	/**
	 * Define constants after including files.
	 */
	function prepareTemplate( Text_Template $template ) {
		$template->setVar( ['constants' => ''] );
		$template->setVar( ['wp_constants' => PHPUnit_Util_GlobalState::getConstantsAsString()] );
		parent::prepareTemplate( $template );
	}

	/**
	 * Returns the name of a temporary file
	 */
	function temp_filename() {
		$tmp_dir = '';
		$dirs = ['TMP', 'TMPDIR', 'TEMP'];
		foreach( $dirs as $dir )
			if ( isset( $_ENV[$dir] ) && !empty( $_ENV[$dir] ) ) {
				$tmp_dir = $dir;
				break;
			}
		if ( empty( $tmp_dir ) ) {
			$tmp_dir = '/tmp';
		}
		$tmp_dir = realpath( $tmp_dir );
		return tempnam( $tmp_dir, 'wpunit' );
	}

	/**
	 * Check each of the WP_Query is_* functions/properties against expected boolean value.
	 *
	 * Any properties that are listed by name as parameters will be expected to be true; any others are
	 * expected to be false. For example, assertQueryTrue('is_single', 'is_feed') means is_single()
	 * and is_feed() must be true and everything else must be false to pass.
	 *
	 * @param string $prop,... Any number of WP_Query properties that are expected to be true for the current request.
	 */
	function assertQueryTrue(/* ... */) {
		global $wp_query;
		$all = ['is_404', 'is_admin', 'is_archive', 'is_attachment', 'is_author', 'is_category', 'is_comment_feed', 'is_date', 'is_day', 'is_embed', 'is_feed', 'is_front_page', 'is_home', 'is_month', 'is_page', 'is_paged', 'is_post_type_archive', 'is_posts_page', 'is_preview', 'is_robots', 'is_search', 'is_single', 'is_singular', 'is_tag', 'is_tax', 'is_time', 'is_trackback', 'is_year'];
		$true = func_get_args();

		foreach ( $true as $true_thing ) {
			$this->assertContains( $true_thing, $all, "{$true_thing}() is not handled by assertQueryTrue()." );
		}

		$passed = true;
		$not_false = $not_true = []; // properties that were not set to expected values

		foreach ( $all as $query_thing ) {
			$result = is_callable( $query_thing ) ? call_user_func( $query_thing ) : $wp_query->$query_thing;

			if ( in_array( $query_thing, $true ) ) {
				if ( ! $result ) {
					array_push( $not_true, $query_thing );
					$passed = false;
				}
			} else if ( $result ) {
				array_push( $not_false, $query_thing );
				$passed = false;
			}
		}

		$message = '';
		if ( count($not_true) )
			$message .= implode( $not_true, ', ' ) . ' is expected to be true. ';
		if ( count($not_false) )
			$message .= implode( $not_false, ', ' ) . ' is expected to be false.';
		$this->assertTrue( $passed, $message );
	}

	function unlink( $file ) {
		$exists = is_file( $file );
		if ( $exists && ! in_array( $file, self::$ignore_files ) ) {
			//error_log( $file );
			unlink( $file );
		} elseif ( ! $exists ) {
			$this->fail( "Trying to delete a file that doesn't exist: $file" );
		}
	}

	function rmdir( $path ) {
		$files = $this->files_in_dir( $path );
		foreach ( $files as $file ) {
			if ( ! in_array( $file, self::$ignore_files ) ) {
				$this->unlink( $file );
			}
		}
	}

	function remove_added_uploads() {
		// Remove all uploads.
		$uploads = wp_upload_dir();
		$this->rmdir( $uploads['basedir'] );
	}

	function files_in_dir( $dir ) {
		$files = [];

		$iterator = new RecursiveDirectoryIterator( $dir );
		$objects = new RecursiveIteratorIterator( $iterator );
		foreach ( $objects as $name => $object ) {
			if ( is_file( $name ) ) {
				$files[] = $name;
			}
		}

		return $files;
	}

	function scan_user_uploads() {
		static $files = [];
		if ( ! empty( $files ) ) {
			return $files;
		}

		$uploads = wp_upload_dir();
		$files = $this->files_in_dir( $uploads['basedir'] );
		return $files;
	}

	function delete_folders( $path ) {
		$this->matched_dirs = [];
		if ( ! is_dir( $path ) ) {
			return;
		}

		$this->scandir( $path );
		foreach ( array_reverse( $this->matched_dirs ) as $dir ) {
			rmdir( $dir );
		}
		rmdir( $path );
	}

	function scandir( $dir ) {
		foreach ( scandir( $dir ) as $path ) {
			if ( !str_starts_with($path, '.') && is_dir( $dir . '/' . $path ) ) {
				$this->matched_dirs[] = $dir . '/' . $path;
				$this->scandir( $dir . '/' . $path );
			}
		}
	}

	/**
	 * Helper to Convert a microtime string into a float
	 */
	protected function _microtime_to_float($microtime ){
		$time_array = explode( ' ', $microtime );
		return array_sum( $time_array );
	}

	/**
	 * Multisite-agnostic way to delete a user from the database.
	 *
	 * @since 4.3.0
	 */
	public static function delete_user( $user_id ) {
		if ( is_multisite() ) {
			return wpmu_delete_user( $user_id );
		} else {
			return wp_delete_user( $user_id );
		}
	}

	/**
	 * Utility method that resets permalinks and flushes rewrites.
	 *
	 * @since 4.4.0
	 *
	 * @global WP_Rewrite $wp_rewrite
	 *
	 * @param string $structure Optional. Permalink structure to set. Default empty.
	 */
	public function set_permalink_structure( $structure = '' ) {
		global $wp_rewrite;

		$wp_rewrite->init();
		$wp_rewrite->set_permalink_structure( $structure );
		$wp_rewrite->flush_rules();
	}

	function _make_attachment($upload, $parent_post_id = 0) {
		$type = '';
		if ( !empty($upload['type']) ) {
			$type = $upload['type'];
		} else {
			$mime = wp_check_filetype( $upload['file'] );
			if ($mime)
				$type = $mime['type'];
		}

		$attachment = ['post_title' => basename( $upload['file'] ), 'post_content' => '', 'post_type' => 'attachment', 'post_parent' => $parent_post_id, 'post_mime_type' => $type, 'guid' => $upload[ 'url' ]];

		// Save the data
		$id = wp_insert_attachment( $attachment, $upload[ 'file' ], $parent_post_id );
		wp_update_attachment_metadata( $id, wp_generate_attachment_metadata( $id, $upload['file'] ) );
		return $id;
	}
}

> **This is the documentation for version 3 of the project.**
> **The current version is version 4 and the documentation can be found [here](./../README.md).**

# WPQueries module
This module should be used in integration tests, see [levels of testing for more information](./../levels-of-testing.md), to make assertions on the database queries made by the global `$wpdb` object.  
This module **requires** the [WPLoader module](WPLoader.md) to work.  
The module will set, if not set already, the `SAVEQUERIES` constant to `true` and will throw an exception if the constant is already set to a falsy value.  

## Configuration
This module does not require any configuration, but requires the [WPLoader module](WPLoader.md) to work correctly. 

## Usage
This module must be used in a test case extending the `\Codeception\TestCase\WPTestCase` class.  

The module public API is accessible calling via the `\Codeception\TestCase\WPTestCase::queries()` method:

```php
<?php

use Codeception\Module\WPQueries;

class WPQueriesUsageTest extends \Codeception\TestCase\WPTestCase
{
    public function test_queries_made_by_factory_are_not_tracked()
    {
        $currentQueriesCount = $this->queries()->countQueries();

        $this->assertNotEmpty($currentQueriesCount);

        static::factory()->post->create_many(3);

        $this->assertNotEmpty($currentQueriesCount);
        $this->assertEquals($currentQueriesCount, $this->queries()->countQueries());
    }

    public function test_count_queries()
    {
        $currentQueriesCount = $this->queries()->countQueries();

        $this->assertNotEmpty($currentQueriesCount);

        foreach (range(1, 3) as $i) {
            wp_insert_post(['post_title' => 'Post ' . $i, 'post_content' => str_repeat('test', $i)]);
        }

        $this->assertNotEmpty($currentQueriesCount);
        $this->assertGreaterThan($currentQueriesCount, $this->queries()->countQueries());
    }
}
```
<!--doc-->


## Public API
<nav>
	<ul>
		<li>
			<a href="#assertcountqueries">assertCountQueries</a>
		</li>
		<li>
			<a href="#assertnotqueries">assertNotQueries</a>
		</li>
		<li>
			<a href="#assertnotqueriesbyaction">assertNotQueriesByAction</a>
		</li>
		<li>
			<a href="#assertnotqueriesbyfilter">assertNotQueriesByFilter</a>
		</li>
		<li>
			<a href="#assertnotqueriesbyfunction">assertNotQueriesByFunction</a>
		</li>
		<li>
			<a href="#assertnotqueriesbymethod">assertNotQueriesByMethod</a>
		</li>
		<li>
			<a href="#assertnotqueriesbystatement">assertNotQueriesByStatement</a>
		</li>
		<li>
			<a href="#assertnotqueriesbystatementandaction">assertNotQueriesByStatementAndAction</a>
		</li>
		<li>
			<a href="#assertnotqueriesbystatementandfilter">assertNotQueriesByStatementAndFilter</a>
		</li>
		<li>
			<a href="#assertnotqueriesbystatementandfunction">assertNotQueriesByStatementAndFunction</a>
		</li>
		<li>
			<a href="#assertnotqueriesbystatementandmethod">assertNotQueriesByStatementAndMethod</a>
		</li>
		<li>
			<a href="#assertqueries">assertQueries</a>
		</li>
		<li>
			<a href="#assertqueriesbyaction">assertQueriesByAction</a>
		</li>
		<li>
			<a href="#assertqueriesbyfilter">assertQueriesByFilter</a>
		</li>
		<li>
			<a href="#assertqueriesbyfunction">assertQueriesByFunction</a>
		</li>
		<li>
			<a href="#assertqueriesbymethod">assertQueriesByMethod</a>
		</li>
		<li>
			<a href="#assertqueriesbystatement">assertQueriesByStatement</a>
		</li>
		<li>
			<a href="#assertqueriesbystatementandaction">assertQueriesByStatementAndAction</a>
		</li>
		<li>
			<a href="#assertqueriesbystatementandfilter">assertQueriesByStatementAndFilter</a>
		</li>
		<li>
			<a href="#assertqueriesbystatementandfunction">assertQueriesByStatementAndFunction</a>
		</li>
		<li>
			<a href="#assertqueriesbystatementandmethod">assertQueriesByStatementAndMethod</a>
		</li>
		<li>
			<a href="#assertqueriescountbyaction">assertQueriesCountByAction</a>
		</li>
		<li>
			<a href="#assertqueriescountbyfilter">assertQueriesCountByFilter</a>
		</li>
		<li>
			<a href="#assertqueriescountbyfunction">assertQueriesCountByFunction</a>
		</li>
		<li>
			<a href="#assertqueriescountbymethod">assertQueriesCountByMethod</a>
		</li>
		<li>
			<a href="#assertqueriescountbystatement">assertQueriesCountByStatement</a>
		</li>
		<li>
			<a href="#assertqueriescountbystatementandaction">assertQueriesCountByStatementAndAction</a>
		</li>
		<li>
			<a href="#assertqueriescountbystatementandfilter">assertQueriesCountByStatementAndFilter</a>
		</li>
		<li>
			<a href="#assertqueriescountbystatementandfunction">assertQueriesCountByStatementAndFunction</a>
		</li>
		<li>
			<a href="#assertqueriescountbystatementandmethod">assertQueriesCountByStatementAndMethod</a>
		</li>
		<li>
			<a href="#countqueries">countQueries</a>
		</li>
		<li>
			<a href="#getqueries">getQueries</a>
		</li>
	</ul>
</nav>

<h3>assertCountQueries</h3>

<hr>

<p>Asserts that n queries have been made.</p>
```php
$posts = $this->factory()->post->create_many(3);
  $cachedUsers = $this->factory()->user->create_many(2);
  $nonCachedUsers = $this->factory()->user->create_many(2);
  foreach($cachedUsers as $userId){
  wp_cache_set('page-posts-for-user-' . $userId, $posts, 'acme');
  }
  // Run the same query as different users
  foreach(array_merge($cachedUsers, $nonCachedUsers) as $userId){
  $pagePosts = $plugin->getPagePostsForUser($userId);
  }
  $I->assertCountQueries(2, 'A query should be made for each user missing cached posts.')
```

<h4>Parameters</h4>
<ul>
<li><code>int</code> <strong>$n</strong> - The expected number of queries.</li>
<li><code>string</code> <strong>$message</strong> - An optional message to override the default one.</li></ul>
  

<h3>assertNotQueries</h3>

<hr>

<p>Asserts that no queries were made. Queries generated by <code>setUp</code>, <code>tearDown</code> and <code>factory</code> methods are excluded by default.</p>
```php
$posts = $this->factory()->post->create_many(3);
  wp_cache_set('page-posts', $posts, 'acme');
  $pagePosts = $plugin->getPagePosts();
  $I->assertNotQueries('Queries should not be made if the cache is set.')
```

<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$message</strong> - An optional message to override the default one.</li></ul>
  

<h3>assertNotQueriesByAction</h3>

<hr>

<p>Asserts that no queries were made as a consequence of the specified action. Queries generated by <code>setUp</code>, <code>tearDown</code> and <code>factory</code> methods are excluded by default.</p>
```php
add_action( 'edit_post', function($postId){
  $count = get_option('acme_title_updates_count');
  update_option('acme_title_updates_count', ++$count);
  } );
  wp_delete_post($bookId);
  $this->assertNotQueriesByAction('edit_post');
```

<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$action</strong> - The action name, e.g. 'init'.</li>
<li><code>string</code> <strong>$message</strong> - An optional message to override the default one.</li></ul>
  

<h3>assertNotQueriesByFilter</h3>

<hr>

<p>Asserts that no queries were made as a consequence of the specified filter. Queries generated by <code>setUp</code>, <code>tearDown</code> and <code>factory</code> methods are excluded by default.</p>
```php
add_filter('the_title', function($title, $postId){
  $post = get_post($postId);
  if($post->post_type !== 'book'){
  return $title;
  }
  $new = get_option('acme_new_prefix');
  return "{$new} - " . $title;
  });
  $title = apply_filters('the_title', get_post($notABookId)->post_title, $notABookId);
  $this->assertNotQueriesByFilter('the_title');
```

<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$filter</strong> - The filter name, e.g. 'posts_where'.</li>
<li><code>string</code> <strong>$message</strong> - An optional message to override the default one.</li></ul>
  

<h3>assertNotQueriesByFunction</h3>

<hr>

<p>Asserts that no queries were made by the specified function. Queries generated by <code>setUp</code>, <code>tearDown</code> and <code>factory</code> methods are excluded by default.</p>
```php
$this->assertEmpty(Acme\get_orphaned_posts());
  Acme\delete_orphaned_posts();
  $this->assertNotQueriesByFunction('Acme\delete_orphaned_posts');
```

<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$function</strong> - The fully qualified name of the function to check.</li>
<li><code>string</code> <strong>$message</strong> - An optional message to override the default one.</li></ul>
  

<h3>assertNotQueriesByMethod</h3>

<hr>

<p>Asserts that no queries have been made by the specified class method. Queries generated by <code>setUp</code>, <code>tearDown</code> and <code>factory</code> methods are excluded by default.</p>
```php
$options = new Acme\Options();
  $options->update('adsSource', 'not-a-real-url.org');
  $I->assertNotQueriesByMethod('Acme\Options', 'update');
```

<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$class</strong> - The fully qualified name of the class to check.</li>
<li><code>string</code> <strong>$method</strong> - The name of the method to check.</li>
<li><code>string</code> <strong>$message</strong> - An optional message to override the default one.</li></ul>
  

<h3>assertNotQueriesByStatement</h3>

<hr>

<p>Asserts that no queries have been made by the specified class method. Queries generated by <code>setUp</code>, <code>tearDown</code> and <code>factory</code> methods are excluded by default.</p>
```php
$bookRepository = new Acme\BookRepository();
  $repository->where('ID', 23)->set('title', 'Peter Pan', $deferred = true);
  $this->assertNotQueriesByStatement('INSERT', 'Deferred write should happen on __destruct');
  Regular expressions must contain delimiters.
```

<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$statement</strong> - A simple string the statement should start with or a valid regular expression.</li>
<li><code>string</code> <strong>$message</strong> - An optional message to override the default one.</li></ul>
  

<h3>assertNotQueriesByStatementAndAction</h3>

<hr>

<p>Asserts that no queries were made as a consequence of the specified action containing the SQL query. Queries generated by <code>setUp</code>, <code>tearDown</code> and <code>factory</code> methods are excluded by default.</p>
```php
add_action( 'edit_post', function($postId){
  $count = get_option('acme_title_updates_count');
  update_option('acme_title_updates_count', ++$count);
  } );
  wp_delete_post($bookId);
  $this->assertNotQueriesByStatementAndAction('DELETE', 'delete_post');
  Regular expressions must contain delimiters.
```

<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$statement</strong> - A simple string the statement should start with or a valid regular expression.</li>
<li><code>string</code> <strong>$action</strong> - The action name, e.g. 'init'.</li>
<li><code>string</code> <strong>$message</strong> - An optional message to override the default one.</li></ul>
  

<h3>assertNotQueriesByStatementAndFilter</h3>

<hr>

<p>Asserts that no queries were made as a consequence of the specified filter containing the specified SQL query. Queries generated by <code>setUp</code>, <code>tearDown</code> and <code>factory</code> methods are excluded by default.</p>
```php
add_filter('the_title', function($title, $postId){
  $post = get_post($postId);
  if($post->post_type !== 'book'){
  return $title;
  }
  $new = get_option('acme_new_prefix');
  return "{$new} - " . $title;
  });
  $title = apply_filters('the_title', get_post($notABookId)->post_title, $notABookId);
  $this->assertNotQueriesByStatementAndFilter('SELECT', 'the_title');
  Regular expressions must contain delimiters.
```

<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$statement</strong> - A simple string the statement should start with or a valid regular expression.</li>
<li><code>string</code> <strong>$filter</strong> - The filter name, e.g. 'posts_where'.</li>
<li><code>string</code> <strong>$message</strong> - An optional message to override the default one.</li></ul>
  

<h3>assertNotQueriesByStatementAndFunction</h3>

<hr>

<p>Asserts that no queries were made by the specified function starting with the specified SQL statement. Queries generated by <code>setUp</code>, <code>tearDown</code> and <code>factory</code> methods are excluded by default.</p>
```php
wp_insert_post(['ID' => $bookId, 'post_title' => 'The Call of the Wild']);
  $this->assertNotQueriesByStatementAndFunction('INSERT', 'wp_insert_post');
  $this->assertQueriesByStatementAndFunction('UPDATE', 'wp_insert_post');
  Regular expressions must contain delimiters.
```

<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$statement</strong> - A simple string the statement should start with or a valid regular expression.</li>
<li><code>string</code> <strong>$function</strong> - The name of the function to check the assertions for.</li>
<li><code>string</code> <strong>$message</strong> - An optional message to override the default one.</li></ul>
  

<h3>assertNotQueriesByStatementAndMethod</h3>

<hr>

<p>Asserts that no queries were made by the specified class method starting with the specified SQL statement. Queries generated by <code>setUp</code>, <code>tearDown</code> and <code>factory</code> methods are excluded by default.</p>
```php
Acme\BookRepository::new(['title' => 'Alice in Wonderland'])->commit();
  $this->assertQueriesByStatementAndMethod('INSERT', Acme\BookRepository::class, 'commit');
  Regular expressions must contain delimiters.
```

<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$statement</strong> - A simple string the statement should start with or a valid regular expression.</li>
<li><code>string</code> <strong>$class</strong> - The fully qualified name of the class to check.</li>
<li><code>string</code> <strong>$method</strong> - The name of the method to check.</li>
<li><code>string</code> <strong>$message</strong> - An optional message to override the default one.</li></ul>
  

<h3>assertQueries</h3>

<hr>

<p>Asserts that at least one query was made during the test. Queries generated by <code>setUp</code>, <code>tearDown</code> and <code>factory</code> methods are excluded by default.</p>
```php
wp_cache_delete('page-posts', 'acme');
  $pagePosts = $plugin->getPagePosts();
  $I->assertQueries('Queries should be made to set the cache.')
```

<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$message</strong> - An optional message to override the default one.</li></ul>
  

<h3>assertQueriesByAction</h3>

<hr>

<p>Asserts that at least one query was made as a consequence of the specified action. Queries generated by <code>setUp</code>, <code>tearDown</code> and <code>factory</code> methods are excluded by default.</p>
```php
add_action( 'edit_post', function($postId){
  $count = get_option('acme_title_updates_count');
  update_option('acme_title_updates_count', ++$count);
  } );
  wp_update_post(['ID' => $bookId, 'post_title' => 'New Title']);
  $this->assertQueriesByAction('edit_post');
```

<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$action</strong> - The action name, e.g. 'init'.</li>
<li><code>string</code> <strong>$message</strong> - An optional message to override the default one.</li></ul>
  

<h3>assertQueriesByFilter</h3>

<hr>

<p>Asserts that at least one query was made as a consequence of the specified filter. Queries generated by <code>setUp</code>, <code>tearDown</code> and <code>factory</code> methods are excluded by default.</p>
```php
add_filter('the_title', function($title, $postId){
  $post = get_post($postId);
  if($post->post_type !== 'book'){
  return $title;
  }
  $new = get_option('acme_new_prefix');
  return "{$new} - " . $title;
  });
  $title = apply_filters('the_title', get_post($bookId)->post_title, $bookId);
  $this->assertQueriesByFilter('the_title');
```

<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$filter</strong> - The filter name, e.g. 'posts_where'.</li>
<li><code>string</code> <strong>$message</strong> - An optional message to override the default one.</li></ul>
  

<h3>assertQueriesByFunction</h3>

<hr>

<p>Asserts that queries were made by the specified function. Queries generated by <code>setUp</code>, <code>tearDown</code> and <code>factory</code> methods are excluded by default.</p>
```php
acme_clean_queue();
  $this->assertQueriesByFunction('acme_clean_queue');
```

<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$function</strong> - The fully qualified name of the function to check.</li>
<li><code>string</code> <strong>$message</strong> - An optional message to override the default one.</li></ul>
  

<h3>assertQueriesByMethod</h3>

<hr>

<p>Asserts that at least one query has been made by the specified class method. Queries generated by <code>setUp</code>, <code>tearDown</code> and <code>factory</code> methods are excluded by default.</p>
```php
$options = new Acme\Options();
  $options->update('showAds', false);
  $I->assertQueriesByMethod('Acme\Options', 'update');
```

<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$class</strong> - The fully qualified name of the class to check.</li>
<li><code>string</code> <strong>$method</strong> - The name of the method to check.</li>
<li><code>string</code> <strong>$message</strong> - An optional message to override the default one.</li></ul>
  

<h3>assertQueriesByStatement</h3>

<hr>

<p>Asserts that at least a query starting with the specified statement was made. Queries generated by <code>setUp</code>, <code>tearDown</code> and <code>factory</code> methods are excluded by default.</p>
```php
wp_cache_flush();
  cached_get_posts($args);
  $I->assertQueriesByStatement('SELECT');
  Regular expressions must contain delimiters.
```

<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$statement</strong> - A simple string the statement should start with or a valid regular expression.</li>
<li><code>string</code> <strong>$message</strong> - An optional message to override the default one.</li></ul>
  

<h3>assertQueriesByStatementAndAction</h3>

<hr>

<p>Asserts that at least one query was made as a consequence of the specified action containing the SQL query. Queries generated by <code>setUp</code>, <code>tearDown</code> and <code>factory</code> methods are excluded by default.</p>
```php
add_action( 'edit_post', function($postId){
  $count = get_option('acme_title_updates_count');
  update_option('acme_title_updates_count', ++$count);
  } );
  wp_update_post(['ID' => $bookId, 'post_title' => 'New']);
  $this->assertQueriesByStatementAndAction('UPDATE', 'edit_post');
  Regular expressions must contain delimiters.
```

<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$statement</strong> - A simple string the statement should start with or a valid regular expression.</li>
<li><code>string</code> <strong>$action</strong> - The action name, e.g. 'init'.</li>
<li><code>string</code> <strong>$message</strong> - An optional message to override the default one.</li></ul>
  

<h3>assertQueriesByStatementAndFilter</h3>

<hr>

<p>Asserts that at least one query was made as a consequence of the specified filter containing the SQL query. Queries generated by <code>setUp</code>, <code>tearDown</code> and <code>factory</code> methods are excluded by default.</p>
```php
add_filter('the_title', function($title, $postId){
  $post = get_post($postId);
  if($post->post_type !== 'book'){
  return $title;
  }
  $new = get_option('acme_new_prefix');
  return "{$new} - " . $title;
  });
  $title = apply_filters('the_title', get_post($bookId)->post_title, $bookId);
  $this->assertQueriesByStatementAndFilter('SELECT', 'the_title');
  Regular expressions must contain delimiters.
```

<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$statement</strong> - A simple string the statement should start with or a valid regular expression.</li>
<li><code>string</code> <strong>$filter</strong> - The filter name, e.g. 'posts_where'.</li>
<li><code>string</code> <strong>$message</strong> - An optional message to override the default one.</li></ul>
  

<h3>assertQueriesByStatementAndFunction</h3>

<hr>

<p>Asserts that queries were made by the specified function starting with the specified SQL statement. Queries generated by <code>setUp</code>, <code>tearDown</code> and <code>factory</code> methods are excluded by default.</p>
```php
wp_insert_post(['post_type' => 'book', 'post_title' => 'Alice in Wonderland']);
  $this->assertQueriesByStatementAndFunction('INSERT', 'wp_insert_post');
  Regular expressions must contain delimiters.
```

<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$statement</strong> - A simple string the statement should start with or a valid regular expression.</li>
<li><code>string</code> <strong>$function</strong> - The fully qualified function name.</li>
<li><code>string</code> <strong>$message</strong> - An optional message to override the default one.</li></ul>
  

<h3>assertQueriesByStatementAndMethod</h3>

<hr>

<p>Asserts that queries were made by the specified class method starting with the specified SQL statement. Queries generated by <code>setUp</code>, <code>tearDown</code> and <code>factory</code> methods are excluded by default.</p>
```php
Acme\BookRepository::new(['title' => 'Alice in Wonderland'])->commit();
  $this->assertQueriesByStatementAndMethod('UPDATE', Acme\BookRepository::class, 'commit');
  Regular expressions must contain delimiters.
```

<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$statement</strong> - A simple string the statement should start with or a valid regular expression.</li>
<li><code>string</code> <strong>$class</strong> - The fully qualified name of the class to check.</li>
<li><code>string</code> <strong>$method</strong> - The name of the method to check.</li>
<li><code>string</code> <strong>$message</strong> - An optional message to override the default one.</li></ul>
  

<h3>assertQueriesCountByAction</h3>

<hr>

<p>Asserts that n queries were made as a consequence of the specified action. Queries generated by <code>setUp</code>, <code>tearDown</code> and <code>factory</code> methods are excluded by default.</p>
```php
add_action( 'edit_post', function($postId){
  $count = get_option('acme_title_updates_count');
  update_option('acme_title_updates_count', ++$count);
  } );
  wp_update_post(['ID' => $bookOneId, 'post_title' => 'One']);
  wp_update_post(['ID' => $bookTwoId, 'post_title' => 'Two']);
  wp_update_post(['ID' => $bookThreeId, 'post_title' => 'Three']);
  $this->assertQueriesCountByAction(3, 'edit_post');
```

<h4>Parameters</h4>
<ul>
<li><code>int</code> <strong>$n</strong> - The expected number of queries.</li>
<li><code>string</code> <strong>$action</strong> - The action name, e.g. 'init'.</li>
<li><code>string</code> <strong>$message</strong> - An optional message to override the default one.</li></ul>
  

<h3>assertQueriesCountByFilter</h3>

<hr>

<p>Asserts that n queries were made as a consequence of the specified filter. Queries generated by <code>setUp</code>, <code>tearDown</code> and <code>factory</code> methods are excluded by default.</p>
```php
add_filter('the_title', function($title, $postId){
  $post = get_post($postId);
  if($post->post_type !== 'book'){
  return $title;
  }
  $new = get_option('acme_new_prefix');
  return "{$new} - " . $title;
  });
  $title = apply_filters('the_title', get_post($bookOneId)->post_title, $bookOneId);
  $title = apply_filters('the_title', get_post($notABookId)->post_title, $notABookId);
  $title = apply_filters('the_title', get_post($bookTwoId)->post_title, $bookTwoId);
  $this->assertQueriesCountByFilter(2, 'the_title');
```

<h4>Parameters</h4>
<ul>
<li><code>int</code> <strong>$n</strong> - The expected number of queries.</li>
<li><code>string</code> <strong>$filter</strong> - The filter name, e.g. 'posts_where'.</li>
<li><code>string</code> <strong>$message</strong> - An optional message to override the default one.</li></ul>
  

<h3>assertQueriesCountByFunction</h3>

<hr>

<p>Asserts that n queries were made by the specified function. Queries generated by <code>setUp</code>, <code>tearDown</code> and <code>factory</code> methods are excluded by default.</p>
```php
$this->assertCount(3, Acme\get_orphaned_posts());
  Acme\delete_orphaned_posts();
  $this->assertQueriesCountByFunction(3, 'Acme\delete_orphaned_posts');
```

<h4>Parameters</h4>
<ul>
<li><code>int</code> <strong>$n</strong> - The expected number of queries.</li>
<li><code>string</code> <strong>$function</strong> - The function to check the queries for.</li>
<li><code>string</code> <strong>$message</strong> - An optional message to override the default one.</li></ul>
  

<h3>assertQueriesCountByMethod</h3>

<hr>

<p>Asserts that n queries have been made by the specified class method. Queries generated by <code>setUp</code>, <code>tearDown</code> and <code>factory</code> methods are excluded by default.</p>
```php
$bookRepository = new Acme\BookRepository();
  $repository->where('ID', 23)->commit('title', 'Peter Pan');
  $repository->where('ID', 89)->commit('title', 'Moby-dick');
  $repository->where('ID', 2389)->commit('title', 'The call of the wild');
  $this->assertQueriesCountByMethod(3, 'Acme\BookRepository', 'commit');
```

<h4>Parameters</h4>
<ul>
<li><code>int</code> <strong>$n</strong> - The expected number of queries.</li>
<li><code>string</code> <strong>$class</strong> - The fully qualified name of the class to check.</li>
<li><code>string</code> <strong>$method</strong> - The name of the method to check.</li>
<li><code>string</code> <strong>$message</strong> - An optional message to override the default one.</li></ul>
  

<h3>assertQueriesCountByStatement</h3>

<hr>

<p>Asserts that n queries starting with the specified statement were made. Queries generated by <code>setUp</code>, <code>tearDown</code> and <code>factory</code> methods are excluded by default.</p>
```php
$bookRepository = new Acme\BookRepository();
  $repository->where('ID', 23)->set('title', 'Peter Pan', $deferred = true);
  $repository->where('ID', 89)->set('title', 'Moby-dick', $deferred = true);
  $repository->where('ID', 2389)->set('title', 'The call of the wild', $deferred = false);
  $this->assertQueriesCountByStatement(1, 'INSERT', 'Deferred write should happen on __destruct');
  Regular expressions must contain delimiters.
```

<h4>Parameters</h4>
<ul>
<li><code>int</code> <strong>$n</strong> - The expected number of queries.</li>
<li><code>string</code> <strong>$statement</strong> - A simple string the statement should start with or a valid regular expression.</li>
<li><code>string</code> <strong>$message</strong> - An optional message to override the default one.</li></ul>
  

<h3>assertQueriesCountByStatementAndAction</h3>

<hr>

<p>Asserts that n queries were made as a consequence of the specified action containing the specified SQL statement. Queries generated by <code>setUp</code>, <code>tearDown</code> and <code>factory</code> methods are excluded by default.</p>
```php
add_action( 'edit_post', function($postId){
  $count = get_option('acme_title_updates_count');
  update_option('acme_title_updates_count', ++$count);
  } );
  wp_delete_post($bookOneId);
  wp_delete_post($bookTwoId);
  wp_update_post(['ID' => $bookThreeId, 'post_title' => 'New']);
  $this->assertQueriesCountByStatementAndAction(2, 'DELETE', 'delete_post');
  $this->assertQueriesCountByStatementAndAction(1, 'INSERT', 'edit_post');
  Regular expressions must contain delimiters.
```

<h4>Parameters</h4>
<ul>
<li><code>int</code> <strong>$n</strong> - The expected number of queries.</li>
<li><code>string</code> <strong>$statement</strong> - A simple string the statement should start with or a valid regular expression.</li>
<li><code>string</code> <strong>$action</strong> - The action name, e.g. 'init'.</li>
<li><code>string</code> <strong>$message</strong> - An optional message to override the default one.</li></ul>
  

<h3>assertQueriesCountByStatementAndFilter</h3>

<hr>

<p>Asserts that n queries were made as a consequence of the specified filter containing the specified SQL statement. Queries generated by <code>setUp</code>, <code>tearDown</code> and <code>factory</code> methods are excluded by default.</p>
```php
add_filter('the_title', function($title, $postId){
  $post = get_post($postId);
  if($post->post_type !== 'book'){
  return $title;
  }
  $new = get_option('acme_new_prefix');
  return "{$new} - " . $title;
  });
  // Warm up the cache.
  $title = apply_filters('the_title', get_post($bookOneId)->post_title, $bookOneId);
  // Cache is warmed up now.
  $title = apply_filters('the_title', get_post($bookTwoId)->post_title, $bookTwoId);
  $title = apply_filters('the_title', get_post($bookThreeId)->post_title, $bookThreeId);
  $this->assertQueriesCountByStatementAndFilter(1, 'SELECT', 'the_title');
  Regular expressions must contain delimiters.
```

<h4>Parameters</h4>
<ul>
<li><code>int</code> <strong>$n</strong> - The expected number of queries.</li>
<li><code>string</code> <strong>$statement</strong> - A simple string the statement should start with or a valid regular expression.</li>
<li><code>string</code> <strong>$filter</strong> - The filter name, e.g. 'posts_where'.</li>
<li><code>string</code> <strong>$message</strong> - An optional message to override the default one.</li></ul>
  

<h3>assertQueriesCountByStatementAndFunction</h3>

<hr>

<p>Asserts that n queries were made by the specified function starting with the specified SQL statement. Queries generated by <code>setUp</code>, <code>tearDown</code> and <code>factory</code> methods are excluded by default.</p>
```php
wp_insert_post(['post_type' => 'book', 'post_title' => 'The Call of the Wild']);
  wp_insert_post(['post_type' => 'book', 'post_title' => 'Alice in Wonderland']);
  wp_insert_post(['post_type' => 'book', 'post_title' => 'The Chocolate Factory']);
  $this->assertQueriesCountByStatementAndFunction(3, 'INSERT', 'wp_insert_post');
  Regular expressions must contain delimiters.
```

<h4>Parameters</h4>
<ul>
<li><code>int</code> <strong>$n</strong> - The expected number of queries.</li>
<li><code>string</code> <strong>$statement</strong> - A simple string the statement should start with or a valid regular expression.</li>
<li><code>string</code> <strong>$function</strong> - The fully-qualified function name.</li>
<li><code>string</code> <strong>$message</strong> - An optional message to override the default one.</li></ul>
  

<h3>assertQueriesCountByStatementAndMethod</h3>

<hr>

<p>Asserts that n queries were made by the specified class method starting with the specified SQL statement. Queries generated by <code>setUp</code>, <code>tearDown</code> and <code>factory</code> methods are excluded by default.</p>
```php
Acme\BookRepository::new(['title' => 'Alice in Wonderland'])->commit();
  Acme\BookRepository::new(['title' => 'Moby-Dick'])->commit();
  Acme\BookRepository::new(['title' => 'The Call of the Wild'])->commit();
  $this->assertQueriesCountByStatementAndMethod(3, 'INSERT', Acme\BookRepository::class, 'commit');
  Regular expressions must contain delimiters.
```

<h4>Parameters</h4>
<ul>
<li><code>int</code> <strong>$n</strong> - The expected number of queries.</li>
<li><code>string</code> <strong>$statement</strong> - A simple string the statement should start with or a valid regular expression.</li>
<li><code>string</code> <strong>$class</strong> - The fully qualified name of the class to check.</li>
<li><code>string</code> <strong>$method</strong> - The name of the method to check.</li>
<li><code>string</code> <strong>$message</strong> - An optional message to override the default one.</li></ul>
  

<h3>countQueries</h3>

<hr>

<p>Returns the current number of queries. Set-up and tear-down queries performed by the test case are filtered out.</p>
```php
// In a WPTestCase, using the global $wpdb object.
  $queriesCount = $this->queries()->countQueries();
  // In a WPTestCase, using a custom $wpdb object.
  $queriesCount = $this->queries()->countQueries($customWdbb);
```

<h4>Parameters</h4>
<ul>
<li><code>\wpdb/null</code> <strong>$wpdb</strong> - A specific instance of the <code>wpdb</code> class or <code>null</code> to use the global one.</li></ul>
  

<h3>getQueries</h3>

<hr>

<p>Returns the queries currently performed by the global database object or the specified one. Set-up and tear-down queries performed by the test case are filtered out.</p>
```php
// In a WPTestCase, using the global $wpdb object.
  $queries = $this->queries()->getQueries();
  // In a WPTestCase, using a custom $wpdb object.
  $queries = $this->queries()->getQueries($customWdbb);
```

<h4>Parameters</h4>
<ul>
<li><code>null/\wpdb</code> <strong>$wpdb</strong> - A specific instance of the <code>wpdb</code> class or <code>null</code> to use the global one.</li></ul>


*This class extends \Codeception\Module*

<!--/doc-->

## WPDb module

This module allows to manipulate the database of the WordPress installation under test directly, without using the
WordPress API.

The module is used together with the [WPBrowser module](WPBrowser.md), [WPWebDriver](WPWebDriver.md)
and [WPFilesystem](WPFilesystem.md) modules to control the site state, the database, and the site file structure.

**Note about interaction with the WPLoader module**: both this module and [the WPLoader one](WPLoader.md) can be used to
control the state of the database before tests and set up fixtures: use either this or WPLoader, do not use both.
This module should be used in end-to-end testing, [the WPLoader module](WPLoader.md) should be used in integration
testing.
If you're using this module to load a database dump before integration tests,
use [the WPLoader module](WPLoader.md#configuration-with-loadonly-false) `dump` configuration parameter instead.

This module should be with [Cest][3] and [Cept][4] test cases.

## Configuration

This module extends [the Codeception Db module][1] adding some configuration options and functions that are specific to
WordPress.

* `dbUrl` - **required**; the URL to use to connect to the database. The URL must be in the form
  `mysql://user:password@host:port/database` if you're using a MySQL database for your tests, or in the form
  `sqlite://path/to/database/file` if you're using a SQLite database for your tests (
  like [the default configuration](./../default-configuration.md) does)
* `dsn` - **required**; the DSN to use to connect to the database; required if not using the `dbUrl` parameter.
* `user` - **required**; the user to use to connect to the database; required if not using the `dbUrl` parameter.
* `password` - **required**; the password to use to connect to the database; required if not using the `dbUrl`
  parameter.
* `url` - **required**;the URL of the WordPress installation under test. E.g. `http://localhost:8080`
  or `https://wordpress.test`.
* `tablePrefix` - the table prefix to use when interacting with the database; defaults to `wp_`.
* `dump` - the path to a database dump file, or a set of database files, to load before running tests. The path can be
  relative to the project root directory, e.g. `tests/_data/dump.sql`, or absolute.
* `populate` - a boolean value to indicate if the database should be populated importing the dump file(s) at the start
  of the suite.
* `cleanup` - a boolean value to indicate if the database should be populated importing the dump file(s) before each
  test.
* `reconnect` - a boolean value to indicate if the database connection should be re-established before each test.
* `populator` - a command to use to populate the database instead of using
  PHP; [read more on the Codeception documentation.][2]
* `urlReplacement` - a boolean value to indicate if the database dump file(s) should be searched for the `siteurl`
  and `home` options and replaced with the `url` parameter value. This is required since WordPress hard-codes URLs in
  the database, the original URL is inferred, if the `originalUrl` parameter is not provided.
* `originalUrl` - if provided together with the `urlReplacement` parameter, the module will not try to infer the
  original URL from the database dump file(s) but use the provided value instead.
* `waitlock` - the number of seconds to wait for a database lock to be released before failing the test. Defaults to
  `10` meaning that the test will fail if the database lock is not released after 10 seconds.
* `createIfNotExists` - a boolean value to indicate if the database should be created if it does not exist. Defaults to
  `false`.

The following is an example of the module configuration to run tests on the`http://localhost:8080` site:

```yaml
modules:
  enabled:
    - lucatume\WPBrowser\Module\WPDb:
        dbUrl: 'mysql://root:password@localhost:3306/wordpress'
        url: 'http://localhost:8080'
        tablePrefix: 'wp_'
        dump: 'tests/_data/dump.sql'
        populate: true
        cleanup: true
        reconnect: false
        urlReplacement: true
        originalUrl: http://wordpress.test
        waitlock: 10
        createIfNotExists: true
```

The following configuration uses [dynamic configuration parameters][3] to set the module configuration:

```yaml
modules:
  enabled:
    - lucatume\WPBrowser\Module\WPDb:
        dbUrl: '%DB_URL%'
        url: '%WORDPRESS_URL%'
        tablePrefix: '%WORDPRESS_TABLE_PREFIX%'
        dump: '%DB_DUMP%'
        populate: true
        cleanup: true
        reconnect: false
        urlReplacement: true
        originalUrl: '%WORDPRESS_ORIGINAL_URL%'
        waitlock: 10
        createIfNotExists: true
```

The following configuration uses a SQLite database:

```yaml
modules:
  enabled:
    - lucatume\WPBrowser\Module\WPDb:
        dbUrl: 'sqlite://tests/database.sqlite'
        url: 'http://localhost:8080'
        tablePrefix: 'wp_'
        dump: 'tests/_data/dump.sql'
        populate: true
        cleanup: true
        reconnect: false
        urlReplacement: true
        originalUrl: http://wordpress.test
        waitlock: 10
        createIfNotExists: true
```

## Methods

<!-- methods -->

#### amConnectedToDatabase
Signature: `amConnectedToDatabase(string $databaseKey)` : `void`  

Make sure you are connected to the right database.

```php
<?php
$I->seeNumRecords(2, 'users');   //executed on default database
$I->amConnectedToDatabase('db_books');
$I->seeNumRecords(30, 'books');  //executed on db_books database
//All the next queries will be on db_books
```

#### countRowsInDatabase
Signature: `countRowsInDatabase(string $table, [array $criteria])` : `int`  

Returns the number of table rows matching a criteria.

```php
<?php
$I->haveManyPostsInDatabase(3, ['post_status' => 'draft' ]);
$I->haveManyPostsInDatabase(3, ['post_status' => 'private' ]);
// Make sure there are now the expected number of draft posts.
$postsTable = $I->grabPostsTableName();
$draftsCount = $I->countRowsInDatabase($postsTable, ['post_status' => 'draft']);
```

#### dontHaveAttachmentFilesInDatabase
Signature: `dontHaveAttachmentFilesInDatabase(array|int $attachmentIds)` : `void`  

Removes all the files attached with an attachment post, it will not remove the database entries.
Requires the `WPFilesystem` module to be loaded in the suite.

```php
<?php
$posts = $I->grabPostsTableName();
$attachmentIds = $I->grabColumnFromDatabase($posts, 'ID', ['post_type' => 'attachment']);
// This will only remove the files, not the database entries.
$I->dontHaveAttachmentFilesInDatabase($attachmentIds);
```

#### dontHaveAttachmentInDatabase
Signature: `dontHaveAttachmentInDatabase(array $criteria, [bool $purgeMeta], [bool $removeFiles])` : `void`  

Removes an attachment from the posts table.

``` php
$postmeta = $I->grabpostmetatablename();
$thumbnailId = $I->grabFromDatabase($postmeta, 'meta_value', [
     'post_id' => $id,
     'meta_key'=>'thumbnail_id'
]);
// Remove only the database entry (including postmeta) but not the files.
$I->dontHaveAttachmentInDatabase($thumbnailId);
// Remove the database entry (including postmeta) and the files.
$I->dontHaveAttachmentInDatabase($thumbnailId, true, true);
```

#### dontHaveBlogInDatabase
Signature: `dontHaveBlogInDatabase(array $criteria, [bool $removeTables], [bool $removeUploads])` : `void`  

Removes one ore more blogs from the database.

```php
<?php
// Remove the blog, all its tables and files.
$I->dontHaveBlogInDatabase(['path' => 'test/one']);
// Remove the blog entry, not the tables though.
$I->dontHaveBlogInDatabase(['blog_id' => $blogId]);
// Remove multiple blogs.
$I->dontHaveBlogInDatabase(['domain' => 'test']);
```

#### dontHaveCommentInDatabase
Signature: `dontHaveCommentInDatabase(array $criteria, [bool $purgeMeta])` : `void`  

Removes an entry from the comments table.

```php
<?php
$I->dontHaveCommentInDatabase(['comment_post_ID' => 23, 'comment_url' => 'http://example.copm']);
```

#### dontHaveCommentMetaInDatabase
Signature: `dontHaveCommentMetaInDatabase(array $criteria)` : `void`  

Removes a post comment meta from the database

```php
<?php
// Remove all meta for the comment with an ID of 23.
$I->dontHaveCommentMetaInDatabase(['comment_id' => 23]);
// Remove the `count` comment meta for the comment with an ID of 23.
$I->dontHaveCommentMetaInDatabase(['comment_id' => 23, 'meta_key' => 'count']);
```

#### dontHaveInDatabase
Signature: `dontHaveInDatabase(string $table, array $criteria)` : `void`  

Deletes a database entry.

```php
<?php
$I->dontHaveInDatabase('custom_table', ['book_ID' => 23, 'book_genre' => 'fiction']);
```

#### dontHaveLinkInDatabase
Signature: `dontHaveLinkInDatabase(array $criteria)` : `void`  

Removes a link from the database.

```php
<?php
$I->dontHaveLinkInDatabase(['link_url' => 'http://example.com']);
```

#### dontHaveOptionInDatabase
Signature: `dontHaveOptionInDatabase(string $key, [mixed $value])` : `void`  

Removes an entry from the options table.

```php
<?php
// Remove the `foo` option.
$I->dontHaveOptionInDatabase('foo');
// Remove the 'bar' option only if it has the `baz` value.
$I->dontHaveOptionInDatabase('bar', 'baz');
```

#### dontHavePostInDatabase
Signature: `dontHavePostInDatabase(array $criteria, [bool $purgeMeta])` : `void`  

Removes an entry from the posts table.

```php
<?php
$posts = $I->haveManyPostsInDatabase(3, ['post_title' => 'Test {{n}}']);
$I->dontHavePostInDatabase(['post_title' => 'Test 2']);
```

#### dontHavePostMetaInDatabase
Signature: `dontHavePostMetaInDatabase(array $criteria)` : `void`  

Removes an entry from the postmeta table.

```php
<?php
$postId = $I->havePostInDatabase(['meta_input' => ['rating' => 23]]);
$I->dontHavePostMetaInDatabase(['post_id' => $postId, 'meta_key' => 'rating']);
```

#### dontHavePostThumbnailInDatabase
Signature: `dontHavePostThumbnailInDatabase(int $postId)` : `void`  

Remove the thumbnail (featured image) from a post, if any.

Please note: the method will NOT remove the attachment post, post meta and file.

```php
<?php
$attachmentId = $I->haveAttachmentInDatabase(codecept_data_dir('some-image.png'));
$postId = $I->havePostInDatabase();
// Attach the thumbnail to the post.
$I->havePostThumbnailInDatabase($postId, $attachmentId);
// Remove the thumbnail from the post.
$I->dontHavePostThumbnailInDatabase($postId);
```

#### dontHaveSiteOptionInDatabase
Signature: `dontHaveSiteOptionInDatabase(string $key, [mixed $value])` : `void`  

Removes a site option from the database.

```php
<?php
// Remove the `foo_count` option.
$I->dontHaveSiteOptionInDatabase('foo_count');
// Remove the `foo_count` option only if its value is `23`.
$I->dontHaveSiteOptionInDatabase('foo_count', 23);
```

#### dontHaveSiteTransientInDatabase
Signature: `dontHaveSiteTransientInDatabase(string $key)` : `void`  

Removes a site transient from the database.

```php
<?php
$I->dontHaveSiteTransientInDatabase(['my_plugin_site_buffer']);
```

#### dontHaveTableInDatabase
Signature: `dontHaveTableInDatabase(string $fullTableName)` : `void`  

Removes a table from the database.
The case where a table does not exist is handled without raising an error.

```php
<?php
$ordersTable = $I->grabPrefixedTableNameFor('orders');
$I->dontHaveTableInDatabase($ordersTable);
```

#### dontHaveTermInDatabase
Signature: `dontHaveTermInDatabase(array $criteria, [bool $purgeMeta])` : `void`  

Removes a term from the database.

```php
<?php
$I->dontHaveTermInDatabase(['name' => 'romance']);
$I->dontHaveTermInDatabase(['slug' => 'genre--romance']);
```

#### dontHaveTermMetaInDatabase
Signature: `dontHaveTermMetaInDatabase(array $criteria)` : `void`  

Removes a term meta from the database.

```php
<?php
// Remove the "karma" key.
$I->dontHaveTermMetaInDatabase(['term_id' => $termId, 'meta_key' => 'karma']);
// Remove all meta for the term.
$I->dontHaveTermMetaInDatabase(['term_id' => $termId]);
```

#### dontHaveTermRelationshipInDatabase
Signature: `dontHaveTermRelationshipInDatabase(array $criteria)` : `void`  

Removes an entry from the term_relationships table.

```php
<?php
// Remove the relation between a post and a category.
$I->dontHaveTermRelationshipInDatabase(['object_id' => $postId, 'term_taxonomy_id' => $ttaxId]);
// Remove all terms for a post.
$I->dontHaveTermMetaInDatabase(['object_id' => $postId]);
```

#### dontHaveTermTaxonomyInDatabase
Signature: `dontHaveTermTaxonomyInDatabase(array $criteria)` : `void`  

Removes an entry from the `term_taxonomy` table.

```php
<?php
// Remove a specific term from the genre taxonomy.
$I->dontHaveTermTaxonomyInDatabase(['term_id' => $postId, 'taxonomy' => 'genre']);
// Remove all terms for a taxonomy.
$I->dontHaveTermTaxonomyInDatabase(['taxonomy' => 'genre']);
```

#### dontHaveTransientInDatabase
Signature: `dontHaveTransientInDatabase(string $transient)` : `void`  

Removes a transient from the database.

```php
<?php
// Removes the `tweets` transient from the database, if set.
$I->dontHaveTransientInDatabase('tweets');
```

#### dontHaveUserInDatabase
Signature: `dontHaveUserInDatabase(string|int $userIdOrLogin, [bool $purgeMeta])` : `void`  

Removes a user from the database.

```php
<?php
$bob = $I->haveUserInDatabase('bob');
$alice = $I->haveUserInDatabase('alice');
// Remove Bob's user and meta.
$I->dontHaveUserInDatabase('bob');
// Remove Alice's user but not meta.
$I->dontHaveUserInDatabase($alice);
```

#### dontHaveUserInDatabaseWithEmail
Signature: `dontHaveUserInDatabaseWithEmail(string $userEmail, [bool $purgeMeta])` : `array`  

Removes a user(s) from the database using the user email address.

```php
<?php
$luca = $I->haveUserInDatabase('luca', 'editor', ['user_email' => 'luca@example.org']);
$I->dontHaveUserInDatabaseWithEmail('luca@exampl.org');
```

#### dontHaveUserMetaInDatabase
Signature: `dontHaveUserMetaInDatabase(array $criteria)` : `void`  

Removes an entry from the usermeta table.

```php
<?php
// Remove the `karma` user meta for a user.
$I->dontHaveUserMetaInDatabase(['user_id' => 23, 'meta_key' => 'karma']);
// Remove all the user meta for a user.
$I->dontHaveUserMetaInDatabase(['user_id' => 23]);
```

#### dontSeeAttachmentInDatabase
Signature: `dontSeeAttachmentInDatabase(array $criteria)` : `void`  

Checks that an attachment is not in the database.

```php
<?php
$url = 'https://example.org/images/foo.png';
$I->dontSeeAttachmentInDatabase(['guid' => $url]);
```

#### dontSeeBlogInDatabase
Signature: `dontSeeBlogInDatabase(array $criteria)` : `void`  

Checks that a row is not present in the `blogs` table.

```php
<?php
$I->haveManyBlogsInDatabase(2, ['path' => 'test-{{n}}'], false)
$I->dontSeeBlogInDatabase(['path' => '/test-3/'])
```

#### dontSeeCommentInDatabase
Signature: `dontSeeCommentInDatabase(array $criteria)` : `void`  

Checks that a comment is not in the database.

Will look up the "comments" table.

```php
<?php
// Checks for one comment.
$I->dontSeeCommentInDatabase(['comment_ID' => 23]);
// Checks for comments from a user.
$I->dontSeeCommentInDatabase(['user_id' => 89]);
```

#### dontSeeCommentMetaInDatabase
Signature: `dontSeeCommentMetaInDatabase(array $criteria)` : `void`  

Checks that a comment meta value is not in the database.

Will look up the "commentmeta" table.

```php
<?php
// Delete a comment `karma` meta.
$I->dontSeeCommentMetaInDatabase(['comment_id' => 23, 'meta_key' => 'karma']);
// Delete all meta for a comment.
$I->dontSeeCommentMetaInDatabase(['comment_id' => 23]);
```

#### dontSeeInDatabase
Signature: `dontSeeInDatabase(string $table, [array $criteria])` : `void`  


#### dontSeeLinkInDatabase
Signature: `dontSeeLinkInDatabase(array $criteria)` : `void`  

Checks that a link is not in the `links` database table.

```php
<?php
$I->dontSeeLinkInDatabase(['link_url' => 'http://example.com']);
$I->dontSeeLinkInDatabase(['link_url' => 'http://example.com', 'link_name' => 'example']);
```

#### dontSeeOptionInDatabase
Signature: `dontSeeOptionInDatabase(array|string $criteriaOrName, [mixed $value])` : `void`  

Checks that an option is not in the database for the current blog.

If the value is an object or an array then the serialized option will be checked.

```php
<?php
$I->dontHaveOptionInDatabase('posts_per_page');
$I->dontSeeOptionInDatabase('posts_per_page');
$I->dontSeeOptionInDatabase('posts_per_page', 23);
$I->dontSeeOptionInDatabase(['option_name' => 'posts_per_page']);
$I->dontSeeOptionInDatabase(['option_name' => 'posts_per_page', 'option_value' => 23]);
```

#### dontSeePageInDatabase
Signature: `dontSeePageInDatabase(array $criteria)` : `void`  

Checks that a page is not in the database.

```php
<?php
// Assert a page with an ID does not exist.
$I->dontSeePageInDatabase(['ID' => 23]);
// Assert a page with a slug and ID.
$I->dontSeePageInDatabase(['post_name' => 'test', 'ID' => 23]);
```

#### dontSeePostInDatabase
Signature: `dontSeePostInDatabase(array $criteria)` : `void`  

Checks that a post is not in the database.

```php
<?php
// Asserts a post with title 'Test' is not in the database.
$I->dontSeePostInDatabase(['post_title' => 'Test']);
// Asserts a post with title 'Test' and content 'Test content' is not in the database.
$I->dontSeePostInDatabase(['post_title' => 'Test', 'post_content' => 'Test content']);
```

#### dontSeePostMetaInDatabase
Signature: `dontSeePostMetaInDatabase(array $criteria)` : `void`  

Checks that a post meta value does not exist.

If the meta value is an object or an array then the check will be made on its serialized version.

```php
<?php
$postId = $I->havePostInDatabase(['meta_input' => ['foo' => 'bar']]);
$I->dontSeePostMetaInDatabase(['post_id' => $postId, 'meta_key' => 'woot']);
```

#### dontSeePostWithTermInDatabase
Signature: `dontSeePostWithTermInDatabase(int $post_id, int $term_taxonomy_id, [?int $term_order], [?string $taxonomy])` : `void`  

Checks that a post to term relation does not exist in the database.

The method will check the "term_relationships" table.

```php
<?php
$fiction = $I->haveTermInDatabase('fiction', 'genre');
$nonFiction = $I->haveTermInDatabase('non-fiction', 'genre');
$postId = $I->havePostInDatabase(['tax_input' => ['genre' => ['fiction']]]);
$I->dontSeePostWithTermInDatabase($postId, $nonFiction['term_taxonomy_id], );
```

#### dontSeeSiteOptionInDatabase
Signature: `dontSeeSiteOptionInDatabase(array|string $criteriaOrName, [mixed $value])` : `void`  

Checks that a site option is not in the database.

```php
<?php
// Check that the option is not set in the database.
$I->dontSeeSiteOptionInDatabase('foo_count');
// Check that the option is not set with a specific value.
$I->dontSeeSiteOptionInDatabase('foo_count', 23);
$I->dontSeeSiteOptionInDatabase(['option_name => 'foo_count', 'option_value' => 23]);
```

#### dontSeeTableInDatabase
Signature: `dontSeeTableInDatabase(string $table)` : `void`  

Checks that a table is not in the database.

```php
<?php
$options = $I->grabPrefixedTableNameFor('options');
$I->dontHaveTableInDatabase($options)
$I->dontSeeTableInDatabase($options);
```

#### dontSeeTermInDatabase
Signature: `dontSeeTermInDatabase(array $criteria)` : `void`  

Makes sure a term is not in the database.

Looks up both the `terms` table and the `term_taxonomy` tables.

```php
<?php
// Asserts a 'fiction' term is not in the database.
$I->dontSeeTermInDatabase(['name' => 'fiction']);
// Asserts a 'fiction' term with slug 'genre--fiction' is not in the database.
$I->dontSeeTermInDatabase(['name' => 'fiction', 'slug' => 'genre--fiction']);
```

#### dontSeeTermMetaInDatabase
Signature: `dontSeeTermMetaInDatabase(array $criteria)` : `void`  

Checks that a term meta is not in the database.

```php
<?php
list($termId, $termTaxonomyId) = $I->haveTermInDatabase('fiction', 'genre');
$I->haveTermMetaInDatabase($termId, 'rating', 4);
$I->dontSeeTermMetaInDatabase(['term_id' => $termId,'meta_key' => 'average_review']);
```

#### dontSeeTermTaxonomyInDatabase
Signature: `dontSeeTermTaxonomyInDatabase(array $criteria)` : `void`  

Checks that a term taxonomy is not in the database.

```php
<?php
list($termId, $termTaxonomyId) = $I->haveTermInDatabase('fiction', 'genre');
$I->dontSeeTermTaxonomyInDatabase(['term_id' => $termId, 'taxonomy' => 'country']);
```

#### dontSeeUserInDatabase
Signature: `dontSeeUserInDatabase(array $criteria)` : `void`  

Checks that a user is not in the database.

```php
<?php
// Asserts a user does not exist in the database.
$I->dontSeeUserInDatabase(['user_login' => 'luca']);
// Asserts a user with email and login is not in the database.
$I->dontSeeUserInDatabase(['user_login' => 'luca', 'user_email' => 'luca@theaveragedev.com']);
```

#### dontSeeUserMetaInDatabase
Signature: `dontSeeUserMetaInDatabase(array $criteria)` : `void`  

Check that a user meta value is not in the database.

```php
<?php
// Asserts a user does not have a 'karma' meta assigned.
$I->dontSeeUserMetaInDatabase(['user_id' => 23, 'meta_key' => 'karma']);
// Asserts no user has any 'karma' meta assigned.
$I->dontSeeUserMetaInDatabase(['meta_key' => 'karma']);
```

#### getSiteDomain
Signature: `getSiteDomain()` : `string`  

Returns the site domain inferred from the `url` set in the config.

```php
<?php
$domain = $I->getSiteDomain();
// We should be redirected to the HTTPS version when visiting the HTTP version.
$I->amOnPage('http://' . $domain);
$I->seeCurrentUrlEquals('https://' . $domain);
```

#### getUsersTableName
Signature: `getUsersTableName()` : `string`  

Returns the prefixed users table name.

```php
<?php
// Given a `wp_` table prefix returns `wp_users`.
$usersTable = $I->getUsersTableName();
// Given a `wp_` table prefix returns `wp_users`.
$I->useBlog(23);
$usersTable = $I->getUsersTableName();
```

#### grabAllFromDatabase
Signature: `grabAllFromDatabase(string $table, string $column, array $criteria)` : `array`  

Returns all entries matching a criteria from the database.

```php
<?php
$books = $I->grabPrefixedTableNameFor('books');
$I->grabAllFromDatabase($books, 'title', ['genre' => 'fiction']);
```

#### grabAttachmentAttachedFile
Signature: `grabAttachmentAttachedFile(int $attachmentPostId)` : `string`  

Returns the path, as stored in the database, of an attachment `_wp_attached_file` meta.
The attached file is, usually, an attachment origal file.

```php
<?php
$file = $I->grabAttachmentAttachedFile($attachmentId);
$fileInfo = new SplFileInfo($file);
$I->assertEquals('jpg', $fileInfo->getExtension());
```

#### grabAttachmentMetadata
Signature: `grabAttachmentMetadata(int $attachmentPostId)` : `array`  

Returns the metadata array for an attachment post.
This is the value of the `_wp_attachment_metadata` meta.

```php
<?php
$metadata = $I->grabAttachmentMetadata($attachmentId);
$I->assertEquals(['thumbnail', 'medium', 'medium_large'], array_keys($metadata['sizes']);
```

#### grabBlogDomain
Signature: `grabBlogDomain(int $blogId)` : `string`  

Returns a blog domain given its ID.

```php
<?php
$blogIds = $I->haveManyBlogsInDatabase(3);
$domains = array_map(function($blogId){
     return $I->grabBlogDomain($blogId);
}, $blogIds);
```

#### grabBlogPath
Signature: `grabBlogPath(int $blogId)` : `string`  

Grabs a blog domain from the blogs table.

```php
<?php
$blogId = $I->haveBlogInDatabase('test');
$path = $I->grabBlogDomain($blogId);
$I->amOnSubdomain($path);
$I->amOnPage('/');
```

#### grabBlogTableName
Signature: `grabBlogTableName(int $blogId, string $table)` : `string`  

Returns the full name of a table for a blog from a multisite installation database.

```php
<?php
$blogOptionTable = $I->grabBlogTableName($blogId, 'option');
```

#### grabBlogTableNames
Signature: `grabBlogTableNames(int $blogId)` : `array`  

Returns a list of tables for a blog ID.

```php
<?php
     $blogId = $I->haveBlogInDatabase('test');
     $tables = $I->grabBlogTableNames($blogId);
     $options = array_filter($tables, function($tableName){
     return str_pos($tableName, 'options') !== false;
});
```

#### grabBlogTablePrefix
Signature: `grabBlogTablePrefix(int $blogId)` : `string`  

Returns the table prefix for a blog.

```php
<?php
$blogId = $I->haveBlogInDatabase('test');
$blogTablePrefix = $I->getBlogTablePrefix($blogId);
$blogOrders = $I->blogTablePrefix . 'orders';
```

#### grabBlogUrl
Signature: `grabBlogUrl([int $blogId])` : `string`  

Gets the blog URL from the Blog ID.

#### grabBlogVersionsTableName
Signature: `grabBlogVersionsTableName()` : `string`  

Gets the prefixed `blog_versions` table name.

```php
<?php
// Assuming a `wp_` table prefix it will return `wp_blog_versions`.
$blogVersionsTable = $I->grabBlogVersionsTableName();
$I->useBlog(23);
// Assuming a `wp_` table prefix it will return `wp_blog_versions`.
$blogVersionsTable = $I->grabBlogVersionsTableName();
```

#### grabBlogsTableName
Signature: `grabBlogsTableName()` : `string`  

Gets the prefixed `blogs` table name.

```php
<?php
// Assuming a `wp_` table prefix it will return `wp_blogs`.
$blogVersionsTable = $I->grabBlogsTableName();
$I->useBlog(23);
// Assuming a `wp_` table prefix it will return `wp_blogs`.
$blogVersionsTable = $I->grabBlogsTableName();
```

#### grabColumnFromDatabase
Signature: `grabColumnFromDatabase(string $table, string $column, [array $criteria])` : `array`  

Fetches all values from the column in database.
Provide table name, desired column and criteria.

``` php
<?php
$mails = $I->grabColumnFromDatabase('users', 'email', array('name' => 'RebOOter'));
```
#### grabCommentmetaTableName
Signature: `grabCommentmetaTableName()` : `string`  

Returns the prefixed comment meta table name.

```php
<?php
// Get all the values of 'karma' for all comments.
$commentMeta = $I->grabCommentmetaTableName();
$I->grabAllFromDatabase($commentMeta, 'meta_value', ['meta_key' => 'karma']);
```

#### grabCommentsTableName
Signature: `grabCommentsTableName()` : `string`  

Gets the comments table name.

```php
<?php
// Will be `wp_comments`.
$comments = $I->grabCommentsTableName();
// Will be `wp_23_comments`.
$I->useBlog(23);
$comments = $I->grabCommentsTableName();
```

#### grabEntriesFromDatabase
Signature: `grabEntriesFromDatabase(string $table, [array $criteria])` : `array`  

Fetches a set of entries from a database.
Provide table name and criteria.

``` php
<?php
$mail = $I->grabEntriesFromDatabase('users', array('name' => 'Davert'));
```
Comparison expressions can be used as well:

```php
<?php
$post = $I->grabEntriesFromDatabase('posts', ['num_comments >=' => 100]);
$user = $I->grabEntriesFromDatabase('users', ['email like' => 'miles%']);
```

Supported operators: `<`, `>`, `>=`, `<=`, `!=`, `like`.

#### grabEntryFromDatabase
Signature: `grabEntryFromDatabase(string $table, [array $criteria])` : `array`  

Fetches a whole entry from a database.
Make the test fail if the entry is not found.
Provide table name, desired column and criteria.

``` php
<?php
$mail = $I->grabEntryFromDatabase('users', array('name' => 'Davert'));
```
Comparison expressions can be used as well:

```php
<?php
$post = $I->grabEntryFromDatabase('posts', ['num_comments >=' => 100]);
$user = $I->grabEntryFromDatabase('users', ['email like' => 'miles%']);
```

Supported operators: `<`, `>`, `>=`, `<=`, `!=`, `like`.

#### grabFromDatabase
Signature: `grabFromDatabase(string $table, string $column, [array $criteria])` : `void`  

Fetches a single column value from a database.
Provide table name, desired column and criteria.

``` php
<?php
$mail = $I->grabFromDatabase('users', 'email', array('name' => 'Davert'));
```
Comparison expressions can be used as well:

```php
<?php
$postNum = $I->grabFromDatabase('posts', 'num_comments', ['num_comments >=' => 100]);
$mail = $I->grabFromDatabase('users', 'email', ['email like' => 'miles%']);
```

Supported operators: `<`, `>`, `>=`, `<=`, `!=`, `like`.

#### grabLatestEntryByFromDatabase
Signature: `grabLatestEntryByFromDatabase(string $tableName, [string $idColumn])` : `int`  

Returns the id value of the last table entry.

```php
<?php
$I->haveManyPostsInDatabase();
$postsTable = $I->grabPostsTableName();
$last = $I->grabLatestEntryByFromDatabase($postsTable, 'ID');
```

#### grabLinksTableName
Signature: `grabLinksTableName()` : `string`  

Returns the prefixed links table name.

```php
<?php
// Given a `wp_` table prefix returns `wp_links`.
$linksTable = $I->grabLinksTableName();
// Given a `wp_` table prefix returns `wp_23_links`.
$I->useBlog(23);
$linksTable = $I->grabLinksTableName();
```

#### grabNumRecords
Signature: `grabNumRecords(string $table, [array $criteria])` : `int`  

Returns the number of rows in a database

#### grabOptionFromDatabase
Signature: `grabOptionFromDatabase(string $option_name)` : `mixed`  

Gets an option value from the database.

```php
<?php
$count = $I->grabOptionFromDatabase('foo_count');
```

#### grabPostFieldFromDatabase
Signature: `grabPostFieldFromDatabase(int $postId, string $field)` : `mixed`  

Returns the value of a post field for a post, from the `posts`  table.

```php
<?php
$title = $I->grabPostFieldFromDatabase(1, 'post_title');
$type = $I->grabPostFieldFromDatabase(1, 'post_type');
```

#### grabPostMetaFromDatabase
Signature: `grabPostMetaFromDatabase(int $postId, string $metaKey, [bool $single])` : `mixed`  

Gets the value of one or more post meta values from the database.

```php
<?php
$thumbnail_id = $I->grabPostMetaFromDatabase($postId, '_thumbnail_id', true);
```

#### grabPostmetaTableName
Signature: `grabPostmetaTableName()` : `string`  

Returns the prefixed post meta table name.

```php
<?php
// Returns 'wp_postmeta'.
$I->grabPostmetaTableName();
// Returns 'wp_23_postmeta'.
$I->useBlog(23);
$I->grabPostmetaTableName();
```

#### grabPostsTableName
Signature: `grabPostsTableName()` : `string`  

Gets the posts prefixed table name.

```php
<?php
// Given a `wp_` table prefix returns `wp_posts`.
$postsTable = $I->grabPostsTableName();
// Given a `wp_` table prefix returns `wp_23_posts`.
$I->useBlog(23);
$postsTable = $I->grabPostsTableName();
```

#### grabPrefixedTableNameFor
Signature: `grabPrefixedTableNameFor([string $tableName])` : `string`  

Returns a prefixed table name for the current blog.

If the table is not one to be prefixed (e.g. `users`) then the proper table name will be returned.

```php
<?php
// Will return wp_users.
$usersTable = $I->grabPrefixedTableNameFor('users');
// Will return wp_options.
$optionsTable = $I->grabPrefixedTableNameFor('options');
// Use a different blog and get its options table.
$I->useBlog(2);
$blogOptionsTable = $I->grabPrefixedTableNameFor('options');
```

#### grabRegistrationLogTableName
Signature: `grabRegistrationLogTableName()` : `string`  

Gets the prefixed `registration_log` table name.

```php
<?php
// Assuming a `wp_` table prefix it will return `wp_registration_log`.
$blogVersionsTable = $I->grabRegistrationLogTableName();
$I->useBlog(23);
// Assuming a `wp_` table prefix it will return `wp_registration_log`.
$blogVersionsTable = $I->grabRegistrationLogTableName();
```

#### grabSignupsTableName
Signature: `grabSignupsTableName()` : `string`  

Gets the prefixed `signups` table name.

```php
<?php
// Assuming a `wp_` table prefix it will return `wp_signups`.
$blogVersionsTable = $I->grabSignupsTableName();
$I->useBlog(23);
// Assuming a `wp_` table prefix it will return `wp_signups`.
$blogVersionsTable = $I->grabSignupsTableName();
```

#### grabSiteMetaFromDatabase
Signature: `grabSiteMetaFromDatabase(int $blogId, string $key, bool $single)` : `mixed`  

Returns a single or all meta values for a site meta key.

```php
<?php
$I->haveSiteMetaInDatabase(1, 'foo', 'bar');
$value = $I->grabSiteMetaFromDatabase(1, 'foo', true);
$values = $I->grabSiteMetaFromDatabase(1, 'foo', false);
```

#### grabSiteMetaTableName
Signature: `grabSiteMetaTableName()` : `string`  

Gets the prefixed `sitemeta` table name.

```php
<?php
// Assuming a `wp_` table prefix it will return `wp_sitemeta`.
$blogVersionsTable = $I->grabSiteMetaTableName();
$I->useBlog(23);
// Assuming a `wp_` table prefix it will return `wp_sitemeta`.
$blogVersionsTable = $I->grabSiteMetaTableName();
```

#### grabSiteOptionFromDatabase
Signature: `grabSiteOptionFromDatabase(string $key)` : `mixed`  

Gets a site option from the database.

```php
<?php
$fooCountOptionId = $I->haveSiteOptionInDatabase('foo_count','23');
```

#### grabSiteTableName
Signature: `grabSiteTableName()` : `string`  

Gets the prefixed `site` table name.

```php
<?php
// Assuming a `wp_` table prefix it will return `wp_site`.
$blogVersionsTable = $I->grabSiteTableName();
$I->useBlog(23);
// Assuming a `wp_` table prefix it will return `wp_site`.
$blogVersionsTable = $I->grabSiteTableName();
```

#### grabSiteTransientFromDatabase
Signature: `grabSiteTransientFromDatabase(string $key)` : `mixed`  

Gets a site transient from the database.

```php
<?php
$I->grabSiteTransientFromDatabase('total_comments');
$I->grabSiteTransientFromDatabase('api_data');
```

#### grabSiteUrl
Signature: `grabSiteUrl([?string $path])` : `string`  

Returns the current site URL as specified in the module configuration.

```php
<?php
$shopPath = $I->grabSiteUrl('/shop');
```

#### grabTablePrefix
Signature: `grabTablePrefix()` : `string`  

Returns the table prefix, namespaced for secondary blogs if selected.

```php
<?php
// Assuming a table prefix of `wp_` it will return `wp_`;
$tablePrefix = $I->grabTablePrefix();
$I->useBlog(23);
// Assuming a table prefix of `wp_` it will return `wp_23_`;
$tablePrefix = $I->grabTablePrefix();
```

#### grabTermIdFromDatabase
Signature: `grabTermIdFromDatabase(array $criteria)` : `int|false`  

Gets a term ID from the database.
Looks up the prefixed `terms` table, e.g. `wp_terms`.

```php
<?php
// Return the 'fiction' term 'term_id'.
$termId = $I->grabTermIdFromDatabase(['name' => 'fiction']);
// Get a term ID by more stringent criteria.
$termId = $I->grabTermIdFromDatabase(['name' => 'fiction', 'slug' => 'genre--fiction']);
// Return the 'term_id' of the first term for a group.
$termId = $I->grabTermIdFromDatabase(['term_group' => 23]);
```

#### grabTermMetaTableName
Signature: `grabTermMetaTableName()` : `string`  

Gets the terms meta table prefixed name.

```php
<?php
// Returns 'wp_termmeta'.
$I->grabTermMetaTableName();
// Returns 'wp_23_termmeta'.
$I->useBlog(23);
$I->grabTermMetaTableName();
```

#### grabTermRelationshipsTableName
Signature: `grabTermRelationshipsTableName()` : `string`  

Gets the prefixed term relationships table name, e.g. `wp_term_relationships`.

```php
<?php
$I->grabTermRelationshipsTableName();
```

#### grabTermTaxonomyIdFromDatabase
Signature: `grabTermTaxonomyIdFromDatabase(array $criteria)` : `int|false`  

Gets a `term_taxonomy_id` from the database.

Looks up the prefixed `terms_relationships` table, e.g. `wp_term_relationships`.

```php
<?php
// Get the `term_taxonomy_id` for a term and a taxonomy.
$I->grabTermTaxonomyIdFromDatabase(['term_id' => $fictionId, 'taxonomy' => 'genre']);
// Get the `term_taxonomy_id` for the first term with a count of 23.
$I->grabTermTaxonomyIdFromDatabase(['count' => 23]);
```

#### grabTermTaxonomyTableName
Signature: `grabTermTaxonomyTableName()` : `string`  

Gets the prefixed term and taxonomy table name, e.g. `wp_term_taxonomy`.

```php
<?php
// Returns 'wp_term_taxonomy'.
$I->grabTermTaxonomyTableName();
// Returns 'wp_23_term_taxonomy'.
$I->useBlog(23);
$I->grabTermTaxonomyTableName();
```

#### grabTermsTableName
Signature: `grabTermsTableName()` : `string`  

Gets the prefixed terms table name, e.g. `wp_terms`.

```php
<?php
// Returns 'wp_terms'.
$I->grabTermsTableName();
// Returns 'wp_23_terms'.
$I->useBlog(23);
$I->grabTermsTableName();
```

#### grabUserIdFromDatabase
Signature: `grabUserIdFromDatabase(string $userLogin)` : `int|false`  

Gets the a user ID from the database using the user login.

```php
<?php
$userId = $I->grabUserIdFromDatabase('luca');
```

#### grabUserMetaFromDatabase
Signature: `grabUserMetaFromDatabase(int $userId, string $meta_key, [bool $single])` : `mixed`  

Gets a user meta from the database.

```php
<?php
// Returns a user 'karma' value.
$I->grabUserMetaFromDatabase($userId, 'karma');
// Returns an array, the unserialized version of the value stored in the database.
$I->grabUserMetaFromDatabase($userId, 'api_data');
```

#### grabUsermetaTableName
Signature: `grabUsermetaTableName()` : `string`  

Returns the prefixed users meta table name.

```php
<?php
// Given a `wp_` table prefix returns `wp_usermeta`.
$usermetaTable = $I->grabUsermetaTableName();
// Given a `wp_` table prefix returns `wp_usermeta`.
$I->useBlog(23);
$usermetaTable = $I->grabUsermetaTableName();
```

#### grabUsersTableName
Signature: `grabUsersTableName()` : `string`  

Returns the prefixed users table name.

```php
<?php
// Given a `wp_` table prefix returns `wp_users`.
$usersTable = $I->grabUsersTableName();
// Given a `wp_` table prefix returns `wp_users`.
$I->useBlog(23);
$usersTable = $I->grabUsersTableName();
```

#### haveAttachmentInDatabase
Signature: `haveAttachmentInDatabase(string $file, [string|int $date], [array $overrides], [?array $imageSizes])` : `int`  

Creates the database entries representing an attachment and moves the attachment file to the right location.

```php
<?php
$file = codecept_data_dir('images/test.png');
$attachmentId = $I->haveAttachmentInDatabase($file);
$image = codecept_data_dir('images/test-2.png');
$lastWeekAttachment = $I->haveAttachmentInDatabase($image, '-1 week');
```

Requires the WPFilesystem module.

#### haveBlogInDatabase
Signature: `haveBlogInDatabase(string $domainOrPath, [array $overrides], [bool $subdomain])` : `int`  

Inserts a blog in the `blogs` table.

```php
<?php
// Create the `test` subdomain blog.
$blogId = $I->haveBlogInDatabase('test', ['administrator' => $userId]);
// Create the `/test` subfolder blog.
$blogId = $I->haveBlogInDatabase('test', ['administrator' => $userId], false);
```

#### haveCommentInDatabase
Signature: `haveCommentInDatabase(int $comment_post_ID, [array $data])` : `int`  

Inserts a comment in the database.

```php
<?php
$I->haveCommentInDatabase($postId, ['comment_content' => 'Test Comment', 'comment_karma' => 23]);
```

#### haveCommentMetaInDatabase
Signature: `haveCommentMetaInDatabase(int $comment_id, string $meta_key, mixed $meta_value)` : `int`  

Inserts a comment meta field in the database.
Array and object meta values will be serialized.

```php
<?php
$I->haveCommentMetaInDatabase($commentId, 'api_ID', 23);
// The value will be serialized.
$apiData = ['ID' => 23, 'user' => 89, 'origin' => 'twitter'];
$I->haveCommentMetaInDatabase($commentId, 'api_data', $apiData);
```

#### haveInDatabase
Signature: `haveInDatabase(string $table, array $data)` : `int`  

Inserts an SQL record into a database. This record will be erased after the test,
unless you've configured "skip_cleanup_if_failed", and the test fails.

```php
<?php
$I->haveInDatabase('users', array('name' => 'miles', 'email' => 'miles@davis.com'));
```
#### haveLinkInDatabase
Signature: `haveLinkInDatabase([array $overrides])` : `int`  

Inserts a link in the database.

```php
<?php
$linkId = $I->haveLinkInDatabase(['link_url' => 'http://example.org']);
```

#### haveManyBlogsInDatabase
Signature: `haveManyBlogsInDatabase(int $count, [array $overrides], [bool $subdomain])` : `array`  

Inserts many blogs in the database.

```php
<?php
     $blogIds = $I->haveManyBlogsInDatabase(3, ['domain' =>'test-{{n}}']);
     foreach($blogIds as $blogId){
     $I->useBlog($blogId);
     $I->haveManuPostsInDatabase(3);
}
```

#### haveManyCommentsInDatabase
Signature: `haveManyCommentsInDatabase(int $count, int $comment_post_ID, [array $overrides])` : `array`  

Inserts many comments in the database.


```php
<?php
// Insert 3 random comments for a post.
$I->haveManyCommentsInDatabase(3, $postId);
// Insert 3 random comments for a post.
$I->haveManyCommentsInDatabase(3, $postId, ['comment_content' => 'Comment {{n}}']);
```

#### haveManyLinksInDatabase
Signature: `haveManyLinksInDatabase(int $count, [array $overrides])` : `array`  

Inserts many links in the database `links` table.

```php
<?php
// Insert 3 randomly generated links in the database.
$linkIds = $I->haveManyLinksInDatabase(3);
// Inserts links in the database replacing the `n` placeholder.
$linkIds = $I->haveManyLinksInDatabase(3, ['link_url' => 'http://example.org/test-{{n}}']);
```

#### haveManyPostsInDatabase
Signature: `haveManyPostsInDatabase(int $count, [array $overrides])` : `array`  

Inserts many posts in the database returning their IDs.

```php
<?php
// Insert 3 random posts.
$I->haveManyPostsInDatabase(3);
// Insert 3 posts with generated titles.
$I->haveManyPostsInDatabase(3, ['post_title' => 'Test post {{n}}']);
```

#### haveManyTermsInDatabase
Signature: `haveManyTermsInDatabase(int $count, string $name, string $taxonomy, [array $overrides])` : `array`  

Inserts many terms in the database.

```php
<?php
$terms = $I->haveManyTermsInDatabase(3, 'genre-{{n}}', 'genre');
$termIds = array_column($terms, 0);
$termTaxonomyIds = array_column($terms, 1);
```

#### haveManyUsersInDatabase
Signature: `haveManyUsersInDatabase(int $count, string $user_login, [string $role], [array $overrides])` : `array`  

Inserts many users in the database.

```php
<?php
$subscribers = $I->haveManyUsersInDatabase(5, 'user-{{n}}');
$editors = $I->haveManyUsersInDatabase(
     5,
     'user-{{n}}',
     'editor',
     ['user_email' => 'user-{{n}}@example.org']
);
```

#### haveMenuInDatabase
Signature: `haveMenuInDatabase(string $slug, string $location, [array $overrides])` : `array`  

Creates and adds a menu to a theme location in the database.

```php
<?php
list($termId, $termTaxId) = $I->haveMenuInDatabase('test', 'sidebar');
```

#### haveMenuItemInDatabase
Signature: `haveMenuItemInDatabase(string $menuSlug, string $title, [?int $menuOrder], [array $meta])` : `int`  

Adds a menu element to a menu for the current theme.

```php
<?php
$I->haveMenuInDatabase('test', 'sidebar');
$I->haveMenuItemInDatabase('test', 'Test one', 0);
$I->haveMenuItemInDatabase('test', 'Test two', 1);
```

#### haveOptionInDatabase
Signature: `haveOptionInDatabase(string $option_name, mixed $option_value, [string $autoload])` : `int`  

Inserts an option in the database.

```php
<?php
$I->haveOptionInDatabase('posts_per_page', 23);
$I->haveOptionInDatabase('my_plugin_options', ['key_one' => 'value_one', 'key_two' => 89]);
```

If the option value is an object or an array then the value will be serialized.

#### havePageInDatabase
Signature: `havePageInDatabase([array $overrides])` : `int`  

Inserts a page in the database.

```php
<?php
// Creates a test page in the database with random values.
$randomPageId = $I->havePageInDatabase();
// Creates a test page in the database defining its title.
$testPageId = $I->havePageInDatabase(['post_title' => 'Test page']);
```

#### havePostInDatabase
Signature: `havePostInDatabase([array $data])` : `int`  

Inserts a post in the database.

```php
<?php
// Insert a post with random values in the database.
$randomPostId = $I->havePostInDatabase();
// Insert a post with specific values in the database.
$I->havePostInDatabase([
'post_type' => 'book',
'post_title' => 'Alice in Wonderland',
'meta_input' => [
'readers_count' => 23
],
'tax_input' => [
['genre' => 'fiction']
]
]);
```

#### havePostThumbnailInDatabase
Signature: `havePostThumbnailInDatabase(int $postId, int $thumbnailId)` : `int`  

Assigns the specified attachment ID as thumbnail (featured image) to a post.

```php
<?php
$attachmentId = $I->haveAttachmentInDatabase(codecept_data_dir('some-image.png'));
$postId = $I->havePostInDatabase();
$I->havePostThumbnailInDatabase($postId, $attachmentId);
```

#### havePostmetaInDatabase
Signature: `havePostmetaInDatabase(int $postId, string $meta_key, mixed $meta_value)` : `int`  

Adds one or more meta key and value couples in the database for a post.

```php
<?php
// Set the post-meta for a post.
$I->havePostmetaInDatabase($postId, 'karma', 23);
// Set an array post-meta for a post, it will be serialized in the db.
$I->havePostmetaInDatabase($postId, 'data', ['one', 'two']);
// Use a loop to insert one meta per row.
foreach( ['one', 'two'] as $value){
     $I->havePostmetaInDatabase($postId, 'data', $value);
}
```

#### haveSiteMetaInDatabase
Signature: `haveSiteMetaInDatabase(int $blogId, string $string, mixed $value)` : `int`  

Adds a meta key and value for a site in the database.

```php
<?php
$I->haveSiteMetaInDatabase(1, 'foo', 'bar');
$insertedId = $I->haveSiteMetaInDatabase(2, 'foo', ['bar' => 'baz']);
```

#### haveSiteOptionInDatabase
Signature: `haveSiteOptionInDatabase(string $key, mixed $value)` : `int`  

Inserts a site option in the database.

If the value is an array or an object then the value will be serialized.

```php
<?php
$fooCountOptionId = $I->haveSiteOptionInDatabase('foo_count','23');
```

#### haveSiteTransientInDatabase
Signature: `haveSiteTransientInDatabase(string $key, mixed $value)` : `int`  

Inserts a site transient in the database.
If the value is an array or an object then the value will be serialized.

```php
<?php
$I->haveSiteTransientInDatabase('total_comments_count', 23);
// This value will be serialized.
$I->haveSiteTransientInDatabase('api_data', ['user' => 'luca', 'token' => '11ae3ijns-j83']);
```

#### haveTermInDatabase
Signature: `haveTermInDatabase(string $name, string $taxonomy, [array $overrides])` : `array`  

Inserts a term in the database.

```php
<?php
// Insert a random 'genre' term in the database.
$I->haveTermInDatabase('non-fiction', 'genre');
// Insert a term in the database with term meta.
$I->haveTermInDatabase('fiction', 'genre', [
     'slug' => 'genre--fiction',
     'meta' => [
        'readers_count' => 23
     ]
]);
```

#### haveTermMetaInDatabase
Signature: `haveTermMetaInDatabase(int $term_id, string $meta_key, mixed $meta_value)` : `int`  

Inserts a term meta row in the database.
Objects and array meta values will be serialized.

```php
<?php
$I->haveTermMetaInDatabase($fictionId, 'readers_count', 23);
// Insert some meta that will be serialized.
$I->haveTermMetaInDatabase($fictionId, 'flags', [3, 4, 89]);
// Use a loop to insert one meta per row.
foreach([3, 4, 89] as $value) {
     $I->haveTermMetaInDatabase($fictionId, 'flag', $value);
}
```

#### haveTermRelationshipInDatabase
Signature: `haveTermRelationshipInDatabase(int $object_id, int $term_taxonomy_id, [int $term_order])` : `void`  

Creates a term relationship in the database.

No check about the consistency of the insertion is made. E.g. a post could be assigned a term from
a taxonomy that's not registered for that post type.

```php
<?php
// Assign the `fiction` term to a book.
$I->haveTermRelationshipInDatabase($bookId, $fictionId);
```

#### haveTransientInDatabase
Signature: `haveTransientInDatabase(string $transient, mixed $value)` : `int`  

Inserts a transient in the database.

If the value is an array or an object then the value will be serialized.
Since the transients are set in the context of tests it's not possible to
set an expiration directly.

```php
<?php
// Store an array in the `tweets` transient.
$I->haveTransientInDatabase('tweets', $tweets);
```

#### haveUserCapabilitiesInDatabase
Signature: `haveUserCapabilitiesInDatabase(int $userId, array|string $role)` : `array`  

Sets a user capabilities in the database.

```php
<?php
// Assign one user a role in a blog.
$blogId = $I->haveBlogInDatabase('test');
$editor = $I->haveUserInDatabase('luca', 'editor');
$capsIds = $I->haveUserCapabilitiesInDatabase($editor, [$blogId => 'editor']);

// Assign a user two roles in blog 1.
$capsIds = $I->haveUserCapabilitiesInDatabase($userId, ['editor', 'subscriber']);

// Assign one user different roles in different blogs.
$capsIds = $I->haveUserCapabilitiesInDatabase($userId, [$blogId1 => 'editor', $blogId2 => 'author']);

// Assign a user a role and an additional capability in blog 1.
$I->haveUserCapabilitiesInDatabase($userId, ['editor' => true, 'edit_themes' => true]);

// Assign a user a mix of roles and capabilities in different blogs.
$capsIds = $I->haveUserCapabilitiesInDatabase(
     $userId,
     [
         $blogId1 => ['editor' => true, 'edit_themes' => true],
         $blogId2 => ['administrator' => true, 'edit_themes' => false]
     ]
);
```

#### haveUserInDatabase
Signature: `haveUserInDatabase(string $user_login, [array|string $role], [array $overrides])` : `int`  

Inserts a user and its meta in the database.

```php
<?php
// Create an editor user in blog 1 w/ specific email.
$userId = $I->haveUserInDatabase('luca', 'editor', ['user_email' => 'luca@example.org']);

// Create a subscriber user in blog 1.
$subscriberId = $I->haveUserInDatabase('subscriber');

// Create a user editor in blog 1, author in blog 2, administrator in blog 3.
$userWithMeta = $I->haveUserInDatabase('luca',
     [
         1 => 'editor',
         2 => 'author',
         3 => 'administrator'
     ], [
         'user_email' => 'luca@example.org'
         'meta' => ['a meta_key' => 'a_meta_value']
     ]
);

// Create editor in blog 1 w/ `edit_themes` cap, author in blog 2, admin in blog 3 w/o `manage_options` cap.
$userWithMeta = $I->haveUserInDatabase('luca',
     [
         1 => ['editor', 'edit_themes'],
         2 => 'author',
         3 => ['administrator' => true, 'manage_options' => false]
     ]
);

// Create a user w/o role.
$userId = $I->haveUserInDatabase('luca', '');
```

#### haveUserLevelsInDatabase
Signature: `haveUserLevelsInDatabase(int $userId, array|string $role)` : `array`  

Sets the user access level meta in the database for a user.

```php
<?php
$userId = $I->haveUserInDatabase('luca', 'editor');
$moreThanAnEditorLessThanAnAdmin = 8;
$I->haveUserLevelsInDatabase($userId, $moreThanAnEditorLessThanAnAdmin);
```

#### haveUserMetaInDatabase
Signature: `haveUserMetaInDatabase(int $userId, string $meta_key, mixed $meta_value)` : `array`  

Sets a user meta in the database.

```php
<?php
$userId = $I->haveUserInDatabase('luca', 'editor');
$I->haveUserMetaInDatabase($userId, 'karma', 23);
```

#### importSql
Signature: `importSql(array $sql)` : `void`  

Loads a set SQL code lines in the current database.

```php
<?php
// Import a SQL string.
$I->importSql([$sqlString]);
// Import a set of SQL strings.
$I->importSql($sqlStrings);
// Import a prepared set of SQL strings.
$preparedSqlStrings = array_map(function($line){
    return str_replace('{{date}}', date('Y-m-d H:i:s'), $line);
}, $sqlTemplate);
$I->importSql($preparedSqlStrings);
```

#### importSqlDumpFile
Signature: `importSqlDumpFile([?string $dumpFile])` : `void`  

Import the SQL dump file if populate is enabled.

```php
<?php
// Import a dump file passing the absolute path.
$I->importSqlDumpFile(codecept_data_dir('dumps/start.sql'));
```

Specifying a dump file that file will be imported.

#### performInDatabase
Signature: `performInDatabase($databaseKey, $actions)` : `void`  

Can be used with a callback if you don't want to change the current database in your test.

```php
<?php
$I->seeNumRecords(2, 'users');   //executed on default database
$I->performInDatabase('db_books', function($I) {
    $I->seeNumRecords(30, 'books');  //executed on db_books database
});
$I->seeNumRecords(2, 'users');  //executed on default database
```
List of actions can be pragmatically built using `Codeception\Util\ActionSequence`:

```php
<?php
$I->performInDatabase('db_books', ActionSequence::build()
    ->seeNumRecords(30, 'books')
);
```
Alternatively an array can be used:

```php
<?php
$I->performInDatabase('db_books', ['seeNumRecords' => [30, 'books']]);
```

Choose the syntax you like the most and use it,

Actions executed from array or ActionSequence will print debug output for actions, and adds an action name to
exception on failure.

#### seeAttachmentInDatabase
Signature: `seeAttachmentInDatabase(array $criteria)` : `void`  

Checks for an attachment in the database.

```php
<?php
$url = 'https://example.org/images/foo.png';
$I->seeAttachmentInDatabase(['guid' => $url]);
```

#### seeBlogInDatabase
Signature: `seeBlogInDatabase(array $criteria)` : `void`  

Checks for a blog in the `blogs` table.

```php
<?php
// Search for a blog by `blog_id`.
$I->seeBlogInDatabase(['blog_id' => 23]);
// Search for all blogs on a path.
$I->seeBlogInDatabase(['path' => '/sub-path/']);
```

#### seeCommentInDatabase
Signature: `seeCommentInDatabase(array $criteria)` : `void`  

Checks for a comment in the database.

Will look up the "comments" table.

```php
<?php
$I->seeCommentInDatabase(['comment_ID' => 23]);
```

#### seeCommentMetaInDatabase
Signature: `seeCommentMetaInDatabase(array $criteria)` : `void`  

Checks that a comment meta value is in the database.
Will look up the "commentmeta" table.

```php
<?php
// Assert a specified meta for a comment exists.
$I->seeCommentMetaInDatabase(['comment_ID' => $commentId, 'meta_key' => 'karma', 'meta_value' => 23]);
// Assert the comment has at least one meta set.
$I->seeCommentMetaInDatabase(['comment_ID' => $commentId]);
```

#### seeInDatabase
Signature: `seeInDatabase(string $table, [array $criteria])` : `void`  


#### seeLinkInDatabase
Signature: `seeLinkInDatabase(array $criteria)` : `void`  

Checks for a link in the `links` table of the database.

```php
<?php
// Asserts a link exists by name.
$I->seeLinkInDatabase(['link_name' => 'my-link']);
// Asserts at least one link exists for the user.
$I->seeLinkInDatabase(['link_owner' => $userId]);
```

#### seeNumRecords
Signature: `seeNumRecords(int $expectedNumber, string $table, [array $criteria])` : `void`  

Asserts that the given number of records were found in the database.

```php
<?php
$I->seeNumRecords(1, 'users', ['name' => 'davert'])
```

#### seeOptionInDatabase
Signature: `seeOptionInDatabase(array|string $criteriaOrName, [mixed $value])` : `void`  

Checks if an option is in the database for the current blog, either by criteria or by name and value.

If checking for an array or an object then the serialized version will be checked for.

```php
<?php
// Checks an option is in the database.
$I->seeOptionInDatabase('tables_version');
// Checks an option is in the database and has a specific value.
$I->seeOptionInDatabase('tables_version', '1.0');
$I->seeOptionInDatabase(['option_name' => 'tables_version', 'option_value' => 1.0']);
```

#### seePageInDatabase
Signature: `seePageInDatabase(array $criteria)` : `void`  

Checks for a page in the database.

```php
<?php
// Asserts a page with an exists in the database.
$I->seePageInDatabase(['ID' => 23]);
// Asserts a page with a slug and ID exists in the database.
$I->seePageInDatabase(['post_title' => 'Test Page', 'ID' => 23]);
```

#### seePostInDatabase
Signature: `seePostInDatabase(array $criteria)` : `void`  

Checks for a post in the database.

```php
<?php
// Assert a post exists in the database.
$I->seePostInDatabase(['ID' => 23]);
// Assert a post with a slug and ID exists in the database.
$I->seePostInDatabase(['post_content' => 'test content', 'ID' => 23]);
```

#### seePostMetaInDatabase
Signature: `seePostMetaInDatabase(array $criteria)` : `void`  

Checks for a post meta value in the database for the current blog.

If the `meta_value` is an object or an array then the check will be made for serialized values.

```php
<?php
$postId = $I->havePostInDatabase(['meta_input' => ['foo' => 'bar']];
$I->seePostMetaInDatabase(['post_id' => '$postId', 'meta_key' => 'foo']);
```

#### seePostWithTermInDatabase
Signature: `seePostWithTermInDatabase(int $post_id, int $term_taxonomy_id, [?int $term_order], [?string $taxonomy])` : `void`  

Checks that a post to term relation exists in the database.

The method will check the "term_relationships" table.

```php
<?php
$fiction = $I->haveTermInDatabase('fiction', 'genre');
$postId = $I->havePostInDatabase(['tax_input' => ['genre' => ['fiction']]]);
$I->seePostWithTermInDatabase($postId, $fiction['term_taxonomy_id']);
```

#### seeSiteOptionInDatabase
Signature: `seeSiteOptionInDatabase(array|string $criteriaOrName, [mixed $value])` : `void`  

Checks that a site option is in the database.

```php
<?php
// Check that the option is set in the database.
$I->seeSiteOptionInDatabase('foo_count');
// Check that the option is set and has a specific value.
$I->seeSiteOptionInDatabase('foo_count', 23);
```

#### seeSiteSiteTransientInDatabase
Signature: `seeSiteSiteTransientInDatabase(string $key, [mixed $value])` : `void`  

Checks that a site option is in the database.

```php
<?php
// Check a transient exists.
$I->seeSiteSiteTransientInDatabase('total_counts');
// Check a transient exists and has a specific value.
$I->seeSiteSiteTransientInDatabase('total_counts', 23);
```

#### seeTableInDatabase
Signature: `seeTableInDatabase(string $table)` : `void`  

Checks that a table is in the database.

```php
<?php
$options = $I->grabPrefixedTableNameFor('options');
$I->seeTableInDatabase($options);
```

#### seeTermInDatabase
Signature: `seeTermInDatabase(array $criteria)` : `void`  

Checks for a term in the database.
Looks up the `terms` and `term_taxonomy` prefixed tables.

```php
<?php
$I->seeTermInDatabase(['slug' => 'genre--fiction']);
$I->seeTermInDatabase(['name' => 'Fiction', 'slug' => 'genre--fiction']);
```

#### seeTermMetaInDatabase
Signature: `seeTermMetaInDatabase(array $criteria)` : `void`  

Checks for a term meta in the database.

```php
<?php
list($termId, $termTaxonomyId) = $I->haveTermInDatabase('fiction', 'genre');
$I->haveTermMetaInDatabase($termId, 'rating', 4);
$I->seeTermMetaInDatabase(['term_id' => $termId,'meta_key' => 'rating', 'meta_value' => 4]);
```

#### seeTermRelationshipInDatabase
Signature: `seeTermRelationshipInDatabase(array $criteria)` : `void`  

Checks for a term relationship in the database.

```php
<?php
$postId = $I->havePostInDatabase(['tax_input' => ['category' => 'one']]);
$I->seeTermRelationshipInDatabase(['object_id' => $postId, 'term_taxonomy_id' => $oneTermTaxId]);
```

#### seeTermTaxonomyInDatabase
Signature: `seeTermTaxonomyInDatabase(array $criteria)` : `void`  

Checks for a taxonomy taxonomy in the database.

```php
<?php
list($termId, $termTaxonomyId) = $I->haveTermInDatabase('fiction', 'genre');
$I->seeTermTaxonomyInDatabase(['term_id' => $termId, 'taxonomy' => 'genre']);
```

#### seeUserInDatabase
Signature: `seeUserInDatabase(array $criteria)` : `void`  

Checks that a user is in the database.

The method will check the "users" table.

```php
<?php
$I->seeUserInDatabase([
    "user_email" => "test@example.org",
    "user_login" => "login name"
])
```

#### seeUserMetaInDatabase
Signature: `seeUserMetaInDatabase(array $criteria)` : `void`  

Checks for a user meta value in the database.

```php
<?php
$I->seeUserMetaInDatabase(['user_id' => 23, 'meta_key' => 'karma']);
```

#### updateInDatabase
Signature: `updateInDatabase(string $table, array $data, [array $criteria])` : `void`  

Update an SQL record into a database.

```php
<?php
$I->updateInDatabase('users', array('isAdmin' => true), array('email' => 'miles@davis.com'));
```
#### useBlog
Signature: `useBlog([int $blogId])` : `void`  

Sets the blog to be used.

This has nothing to do with WordPress `switch_to_blog` function, this code will affect the table prefixes used.

#### useMainBlog
Signature: `useMainBlog()` : `void`  

Sets the current blog to the main one (`blog_id` 1).

```php
<?php
// Switch to the blog with ID 23.
$I->useBlog(23);
// Switch back to the main blog.
$I->useMainBlog();
```
#### useTheme
Signature: `useTheme(string $stylesheet, [?string $template], [?string $themeName])` : `void`  

Sets the current theme options.

```php
<?php
$I->useTheme('twentyseventeen');
$I->useTheme('child-of-twentyseventeen', 'twentyseventeen');
$I->useTheme('acme', 'acme', 'Acme Theme');
```
<!-- /methods -->

Read more [in Codeception documentation for the Db module.][1]

[1]: https://codeception.com/docs/modules/Db

[2]: https://codeception.com/docs/modules/Db#Populator

[3]: https://codeception.com/docs/AcceptanceTests

[4]: https://codeception.com/docs/AdvancedUsage#Cest-Classes 

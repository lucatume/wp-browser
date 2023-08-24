# WPDb module

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
  like [the default configuration](./../default-configuration) does)
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

The module provides the following methods:

* `amConnectedToDatabase(string $databaseKey)` : `void`
* `countRowsInDatabase(string $table, array [$criteria])` : `int`
* `dontHaveAttachmentFilesInDatabase(array|int $attachmentIds)` : `void`
* `dontHaveAttachmentInDatabase(array $criteria, bool [$purgeMeta], bool [$removeFiles])` : `void`
* `dontHaveBlogInDatabase(array $criteria, bool [$removeTables], bool [$removeUploads])` : `void`
* `dontHaveCommentInDatabase(array $criteria, bool [$purgeMeta])` : `void`
* `dontHaveCommentMetaInDatabase(array $criteria)` : `void`
* `dontHaveInDatabase(string $table, array $criteria)` : `void`
* `dontHaveLinkInDatabase(array $criteria)` : `void`
* `dontHaveOptionInDatabase(string $key, mixed [$value])` : `void`
* `dontHavePostInDatabase(array $criteria, bool [$purgeMeta])` : `void`
* `dontHavePostMetaInDatabase(array $criteria)` : `void`
* `dontHavePostThumbnailInDatabase(int $postId)` : `void`
* `dontHaveSiteOptionInDatabase(string $key, mixed [$value])` : `void`
* `dontHaveSiteTransientInDatabase(string $key)` : `void`
* `dontHaveTableInDatabase(string $fullTableName)` : `void`
* `dontHaveTermInDatabase(array $criteria, bool [$purgeMeta])` : `void`
* `dontHaveTermMetaInDatabase(array $criteria)` : `void`
* `dontHaveTermRelationshipInDatabase(array $criteria)` : `void`
* `dontHaveTermTaxonomyInDatabase(array $criteria)` : `void`
* `dontHaveTransientInDatabase(string $transient)` : `void`
* `dontHaveUserInDatabase(string|int $userIdOrLogin, bool [$purgeMeta])` : `void`
* `dontHaveUserInDatabaseWithEmail(string $userEmail, bool [$purgeMeta])` : `array`
* `dontHaveUserMetaInDatabase(array $criteria)` : `void`
* `dontSeeAttachmentInDatabase(array $criteria)` : `void`
* `dontSeeBlogInDatabase(array $criteria)` : `void`
* `dontSeeCommentInDatabase(array $criteria)` : `void`
* `dontSeeCommentMetaInDatabase(array $criteria)` : `void`
* `dontSeeInDatabase(string $table, array [$criteria])` : `void`
* `dontSeeLinkInDatabase(array $criteria)` : `void`
* `dontSeeOptionInDatabase(array|string $criteriaOrName, mixed [$value])` : `void`
* `dontSeePageInDatabase(array $criteria)` : `void`
* `dontSeePostInDatabase(array $criteria)` : `void`
* `dontSeePostMetaInDatabase(array $criteria)` : `void`
* `dontSeePostWithTermInDatabase(int $post_id, int $term_taxonomy_id, ?int [$term_order], ?string [$taxonomy])` : `void`
* `dontSeeSiteOptionInDatabase(array|string $criteriaOrName, mixed [$value])` : `void`
* `dontSeeTableInDatabase(string $table)` : `void`
* `dontSeeTermInDatabase(array $criteria)` : `void`
* `dontSeeTermMetaInDatabase(array $criteria)` : `void`
* `dontSeeTermTaxonomyInDatabase(array $criteria)` : `void`
* `dontSeeUserInDatabase(array $criteria)` : `void`
* `dontSeeUserMetaInDatabase(array $criteria)` : `void`
* `getSiteDomain()` : `string`
* `getUsersTableName()` : `string`
* `grabAllFromDatabase(string $table, string $column, array $criteria)` : `array`
* `grabAttachmentAttachedFile(int $attachmentPostId)` : `string`
* `grabAttachmentMetadata(int $attachmentPostId)` : `array`
* `grabBlogDomain(int $blogId)` : `string`
* `grabBlogPath(int $blogId)` : `string`
* `grabBlogTableName(int $blogId, string $table)` : `string`
* `grabBlogTableNames(int $blogId)` : `array`
* `grabBlogTablePrefix(int $blogId)` : `string`
* `grabBlogVersionsTableName()` : `string`
* `grabBlogsTableName()` : `string`
* `grabColumnFromDatabase(string $table, string $column, array [$criteria])` : `array`
* `grabCommentmetaTableName()` : `string`
* `grabCommentsTableName()` : `string`
* `grabEntriesFromDatabase(string $table, array [$criteria])` : `array`
* `grabEntryFromDatabase(string $table, array [$criteria])` : `array`
* `grabFromDatabase(string $table, string $column, array [$criteria])` : `void`
* `grabLatestEntryByFromDatabase(string $tableName, string [$idColumn])` : `int`
* `grabLinksTableName()` : `string`
* `grabNumRecords(string $table, array [$criteria])` : `int`
* `grabOptionFromDatabase(string $option_name)` : `mixed`
* `grabPostFieldFromDatabase(int $postId, string $field)` : `mixed`
* `grabPostMetaFromDatabase(int $postId, string $metaKey, bool [$single])` : `mixed`
* `grabPostmetaTableName()` : `string`
* `grabPostsTableName()` : `string`
* `grabPrefixedTableNameFor(string [$tableName])` : `string`
* `grabRegistrationLogTableName()` : `string`
* `grabSignupsTableName()` : `string`
* `grabSiteMetaFromDatabase(int $blogId, string $key, bool $single)` : `mixed`
* `grabSiteMetaTableName()` : `string`
* `grabSiteOptionFromDatabase(string $key)` : `mixed`
* `grabSiteTableName()` : `string`
* `grabSiteTransientFromDatabase(string $key)` : `mixed`
* `grabSiteUrl(?string [$path])` : `string`
* `grabTablePrefix()` : `string`
* `grabTermIdFromDatabase(array $criteria)` : `int|false`
* `grabTermMetaTableName()` : `string`
* `grabTermRelationshipsTableName()` : `string`
* `grabTermTaxonomyIdFromDatabase(array $criteria)` : `int|false`
* `grabTermTaxonomyTableName()` : `string`
* `grabTermsTableName()` : `string`
* `grabUserIdFromDatabase(string $userLogin)` : `int|false`
* `grabUserMetaFromDatabase(int $userId, string $meta_key, bool [$single])` : `mixed`
* `grabUsermetaTableName()` : `string`
* `grabUsersTableName()` : `string`
* `haveAttachmentInDatabase(string $file, string|int [$date], array [$overrides], ?array [$imageSizes])` : `int`
* `haveBlogInDatabase(string $domainOrPath, array [$overrides], bool [$subdomain])` : `int`
* `haveCommentInDatabase(int $comment_post_ID, array [$data])` : `int`
* `haveCommentMetaInDatabase(int $comment_id, string $meta_key, mixed $meta_value)` : `int`
* `haveInDatabase(string $table, array $data)` : `int`
* `haveLinkInDatabase(array [$overrides])` : `int`
* `haveManyBlogsInDatabase(int $count, array [$overrides], bool [$subdomain])` : `array`
* `haveManyCommentsInDatabase(int $count, int $comment_post_ID, array [$overrides])` : `array`
* `haveManyLinksInDatabase(int $count, array [$overrides])` : `array`
* `haveManyPostsInDatabase(int $count, array [$overrides])` : `array`
* `haveManyTermsInDatabase(int $count, string $name, string $taxonomy, array [$overrides])` : `array`
* `haveManyUsersInDatabase(int $count, string $user_login, string [$role], array [$overrides])` : `array`
* `haveMenuInDatabase(string $slug, string $location, array [$overrides])` : `array`
* `haveMenuItemInDatabase(string $menuSlug, string $title, ?int [$menuOrder], array [$meta])` : `int`
* `haveOptionInDatabase(string $option_name, mixed $option_value, string [$autoload])` : `int`
* `havePageInDatabase(array [$overrides])` : `int`
* `havePostInDatabase(array [$data])` : `int`
* `havePostThumbnailInDatabase(int $postId, int $thumbnailId)` : `int`
* `havePostmetaInDatabase(int $postId, string $meta_key, mixed $meta_value)` : `int`
* `haveSiteMetaInDatabase(int $blogId, string $string, mixed $value)` : `int`
* `haveSiteOptionInDatabase(string $key, mixed $value)` : `int`
* `haveSiteTransientInDatabase(string $key, mixed $value)` : `int`
* `haveTermInDatabase(string $name, string $taxonomy, array [$overrides])` : `array`
* `haveTermMetaInDatabase(int $term_id, string $meta_key, mixed $meta_value)` : `int`
* `haveTermRelationshipInDatabase(int $object_id, int $term_taxonomy_id, int [$term_order])` : `void`
* `haveTransientInDatabase(string $transient, mixed $value)` : `int`
* `haveUserCapabilitiesInDatabase(int $userId, array|string $role)` : `array`
* `haveUserInDatabase(string $user_login, array|string [$role], array [$overrides])` : `int`
* `haveUserLevelsInDatabase(int $userId, array|string $role)` : `array`
* `haveUserMetaInDatabase(int $userId, string $meta_key, mixed $meta_value)` : `array`
* `importSql(array $sql)` : `void`
* `importSqlDumpFile(?string [$dumpFile])` : `void`
* `performInDatabase($databaseKey, $actions)` : `void`
* `seeAttachmentInDatabase(array $criteria)` : `void`
* `seeBlogInDatabase(array $criteria)` : `void`
* `seeCommentInDatabase(array $criteria)` : `void`
* `seeCommentMetaInDatabase(array $criteria)` : `void`
* `seeInDatabase(string $table, array [$criteria])` : `void`
* `seeLinkInDatabase(array $criteria)` : `void`
* `seeNumRecords(int $expectedNumber, string $table, array [$criteria])` : `void`
* `seeOptionInDatabase(array|string $criteriaOrName, mixed [$value])` : `void`
* `seePageInDatabase(array $criteria)` : `void`
* `seePostInDatabase(array $criteria)` : `void`
* `seePostMetaInDatabase(array $criteria)` : `void`
* `seePostWithTermInDatabase(int $post_id, int $term_taxonomy_id, ?int [$term_order], ?string [$taxonomy])` : `void`
* `seeSiteOptionInDatabase(array|string $criteriaOrName, mixed [$value])` : `void`
* `seeSiteSiteTransientInDatabase(string $key, mixed [$value])` : `void`
* `seeTableInDatabase(string $table)` : `void`
* `seeTermInDatabase(array $criteria)` : `void`
* `seeTermMetaInDatabase(array $criteria)` : `void`
* `seeTermRelationshipInDatabase(array $criteria)` : `void`
* `seeTermTaxonomyInDatabase(array $criteria)` : `void`
* `seeUserInDatabase(array $criteria)` : `void`
* `seeUserMetaInDatabase(array $criteria)` : `void`
* `updateInDatabase(string $table, array $data, array [$criteria])` : `void`
* `useBlog(int [$blogId])` : `void`
* `useMainBlog()` : `void`
* `useTheme(string $stylesheet, ?string [$template], ?string [$themeName])` : `void`

Read more [in Codeception documentation for the Db module.][1]

[1]: https://codeception.com/docs/modules/Db

[2]: https://codeception.com/docs/modules/Db#Populator

[3]: https://codeception.com/docs/AcceptanceTests

[4]: https://codeception.com/docs/AdvancedUsage#Cest-Classes 

# WPDb module
This module should be used in acceptance and functional tests, see [levels of testing for more information](./../levels-of-testing.md).  
This module extends the [Db module](https://codeception.com/docs/modules/Db) adding WordPress-specific configuration parameters and methods.  
The module provides methods to read, write and update the WordPress database **directly**, without relying on WordPress methods, using WordPress functions or triggering WordPress filters.  

## Backup your content
This module, like the [Codeception Db](https://codeception.com/docs/modules/Db) one it extends, by default **will load a databse dump in the database it's using.**  
This means that **the database contents will be replaced by the dump contents** on each run of a suite using the module.  
You can set the `populate` and `cleanup` parameters to `false` to prevent this default behavior but it's usually not what you need in an automated test.  
**Make a backup of any database you're using in tests that contains any information you care about before you run any test!**

## Change the databse used depending on whether you're running tests or not
The chore of having to plug different databases, or backup them, depending on whether you're manually testing the site or automatically testing can be mitigated switching them automatically depending on the browser user agent or request headers.  
This module was born to be used in acceptance and functional tests (see [levels of testing for more information](./../levels-of-testing.md)) and will often be coupled with modules like the [WPBrowser](WPBrowser.md) one or the [WPWebDriver](WPWebDriver.md) one.  
Depending on which of the two modules is being used in the suite there are different ways to automate the "database switching".

### Automatically changing database based on the browser user agent
If you would like to automate the "switching above" below you will find an example setup.  
Update the test site `wp-config.php` file from this:
```php
define( 'DB_NAME', 'wordpress' );
```
 to this:
```php
<?php
if ( 
    // Custom header.
    isset( $_SERVER['HTTP_X_TESTING'] )
    // Custom user agent.
    || ( isset( $_SERVER['HTTP_USER_AGENT'] ) && $_SERVER['HTTP_USER_AGENT'] === 'wp-browser' )
    // The env var set by the WPClIr or WordPress modules.
    || getenv( 'WPBROWSER_HOST_REQUEST' )
) {
    // Use the test database if the request comes from a test.
    define( 'DB_NAME', 'wordpress_test' );
} else {
    // Else use the default one.
    define( 'DB_NAME', 'wordpress' );
}
```

If you're using the [WPWebDriver](WPWebDriver.md) module set the user agent in the browser, in this example I'm setting the user agent in Chromedriver:
```yaml
class_name: AcceptanceTester
modules:
    enabled:
        - \Helper\Acceptance
        - WPDb
        - WPWebDriver
    config:
        WPDb:
            dsn: 'mysql:host=%WP_DB_HOST%;dbname=%WP_DB_NAME%'
            user: %WP_DB_USER%
            password: %WP_DB_PASSWORD%
            dump: tests/_data/dump.sql
            populate: true
            cleanup: false
            url: '%WP_URL%'
            tablePrefix: %WP_TABLE_PREFIX%
            urlReplacement: true
        WPWebDriver:
            url: '%WP_URL%'
            adminUsername: '%WP_ADMIN_USERNAME%'
            adminPassword: '%WP_ADMIN_PASSWORD%'
            adminPath: '%WP_ADMIN_PATH%'
            browser: chrome
            host: localhost
            port: 4444
            window_size: false
            wait: 5
            capabilities:
                # Used in more recent releases of Selenium.
                "goog:chromeOptions":
                    args: ["--no-sandbox", "--headless", "--disable-gpu", "--user-agent=wp-browser"]
                # Support the old format for back-compatibility purposes. 
                "chromeOptions":
                    args: ["--no-sandbox", "--headless", "--disable-gpu", "--user-agent=wp-browser"]
```

If you're using the [WPBrowser](WPBrowser.md) module send a specific header in the context of test requests: 
```yaml
class_name: AcceptanceTester
modules:
    enabled:
        - \Helper\Acceptance
        - WPDb
        - WPBrowser
    config:
        WPDb:
              dsn: 'mysql:host=%DB_HOST%;dbname=%WP_DB_NAME%'
              user: %WP_DB_USER%
              password: %WP_DB_PASSWORD%
              dump: 'tests/_data/dump.sql'
              populate: true
              cleanup: true
              reconnect: false
              url: '%WP_URL%'
              tablePrefix: 'wp_'
        WPBrowser:
              url: '%WP_URL%'
              adminUsername: 'admin'
              adminPassword: 'admin'
              adminPath: '/wp-admin'
              headers: 
                X-Testing: 'wp-browser'
```

## Configuration

* `dsn` *required* - the database POD DSN connection details; read more [on PHP PDO documentation](https://secure.php.net/manual/en/ref.pdo-mysql.connection.php).
* `user` *required* - the database user.
* `password` *required* - the database password.
* `url` *required* - the full URL, including the HTTP scheme, of the website whose database is being accessed. WordPress uses hard-codece URLs in the databas, that URL will be set by this module when applying the SQL dump file during population or cleanup.
* `dump` *required* - defaults to `null`; sets the path, relative to the project root folder, or absolute to the SQL dump file that will be used to set the tests initial database fixture. If set to `null` then the `populate`, `cleanup` and `populator` parameters will be ignored.
* `populate` - defaults to `true` to empty the target database and import the SQL dump specified in the `dump` argument once, before any test starts.
* `cleanup` - defaults to `true` empty the target database and import the SQL dump specified in the `dump` argument before each test starts. 
* `urlReplacement` - defaults to `true` to replace, while using the built-in, PHP-based, dump import solution the hard-coded WordPress URL in the database with the specified one.
* `populator` - defaults to `null`, if set to an executable shell command then that command will be used to populate the database in place of the built-in PHP solution; URL replacement will not apply in this case. Read more about this [on Codeception documentation](https://codeception.com/docs/modules/Db#Populator).
* `reconnect` - defaults to `true` to force the module to reconnect to the database before each test in place of only connecting at the start of the tests.
* `waitlock` - defaults to `10`; wait lock (in seconds) that the database session should use for DDL statements.
* `tablePrefix` - defaults to `wp_`; sets the prefix of the tables that the module will manipulate.

### Example configuration
```yaml
modules:
  enabled:
      - WPDb
  config:
      WPDb:
          dsn: 'mysql:host=localhost;dbname=wordpress'
          user: 'root'
          password: 'password'
          dump: 'tests/_data/dump.sql'
          populate: true
          cleanup: true
          waitlock: 10
          url: 'http://wordpress.localhost'
          urlReplacement: true
          tablePrefix: 'wp_'
```

## Using the module with the WPLoader one
This module is often used in conjunction with the [WPLoader one](WPLoader.md) to use WordPress-defined functions, classes and methods in acceptance or functional tests.  
The WPLoader module should be [set to only load WordPress](WPLoader.md#wploader-to-only-bootstrap-wordpress) and this module should be listed, in the `modules.eanbled` section of the suite configuration file **before** the `WPLoader` one:

```yaml
modules:
  enabled:
      - WPDb # this before...
      - WPLoader # ...this one.
  config:
      WPDb:
        # ...
      WPLoader:
        loadOnly: true
        # ... 
```
This will avoid issues where the `WPLoader` module could `exit`, terminating the test run, due to an inconsistent database state.

<!--doc-->


## Public API
<nav>
	<ul>
		<li>
			<a href="#countrowsindatabase">countRowsInDatabase</a>
		</li>
		<li>
			<a href="#donthaveattachmentfilesindatabase">dontHaveAttachmentFilesInDatabase</a>
		</li>
		<li>
			<a href="#donthaveattachmentindatabase">dontHaveAttachmentInDatabase</a>
		</li>
		<li>
			<a href="#donthaveblogindatabase">dontHaveBlogInDatabase</a>
		</li>
		<li>
			<a href="#donthavecommentindatabase">dontHaveCommentInDatabase</a>
		</li>
		<li>
			<a href="#donthavecommentmetaindatabase">dontHaveCommentMetaInDatabase</a>
		</li>
		<li>
			<a href="#donthaveindatabase">dontHaveInDatabase</a>
		</li>
		<li>
			<a href="#donthavelinkindatabase">dontHaveLinkInDatabase</a>
		</li>
		<li>
			<a href="#donthaveoptionindatabase">dontHaveOptionInDatabase</a>
		</li>
		<li>
			<a href="#donthavepostindatabase">dontHavePostInDatabase</a>
		</li>
		<li>
			<a href="#donthavepostmetaindatabase">dontHavePostMetaInDatabase</a>
		</li>
		<li>
			<a href="#donthavesiteoptionindatabase">dontHaveSiteOptionInDatabase</a>
		</li>
		<li>
			<a href="#donthavesitetransientindatabase">dontHaveSiteTransientInDatabase</a>
		</li>
		<li>
			<a href="#donthavetableindatabase">dontHaveTableInDatabase</a>
		</li>
		<li>
			<a href="#donthavetermindatabase">dontHaveTermInDatabase</a>
		</li>
		<li>
			<a href="#donthavetermmetaindatabase">dontHaveTermMetaInDatabase</a>
		</li>
		<li>
			<a href="#donthavetermrelationshipindatabase">dontHaveTermRelationshipInDatabase</a>
		</li>
		<li>
			<a href="#donthavetermtaxonomyindatabase">dontHaveTermTaxonomyInDatabase</a>
		</li>
		<li>
			<a href="#donthavetransientindatabase">dontHaveTransientInDatabase</a>
		</li>
		<li>
			<a href="#donthaveuserindatabase">dontHaveUserInDatabase</a>
		</li>
		<li>
			<a href="#donthaveuserindatabasewithemail">dontHaveUserInDatabaseWithEmail</a>
		</li>
		<li>
			<a href="#donthaveusermetaindatabase">dontHaveUserMetaInDatabase</a>
		</li>
		<li>
			<a href="#dontseeattachmentindatabase">dontSeeAttachmentInDatabase</a>
		</li>
		<li>
			<a href="#dontseeblogindatabase">dontSeeBlogInDatabase</a>
		</li>
		<li>
			<a href="#dontseecommentindatabase">dontSeeCommentInDatabase</a>
		</li>
		<li>
			<a href="#dontseecommentmetaindatabase">dontSeeCommentMetaInDatabase</a>
		</li>
		<li>
			<a href="#dontseelinkindatabase">dontSeeLinkInDatabase</a>
		</li>
		<li>
			<a href="#dontseeoptionindatabase">dontSeeOptionInDatabase</a>
		</li>
		<li>
			<a href="#dontseepageindatabase">dontSeePageInDatabase</a>
		</li>
		<li>
			<a href="#dontseepostindatabase">dontSeePostInDatabase</a>
		</li>
		<li>
			<a href="#dontseepostmetaindatabase">dontSeePostMetaInDatabase</a>
		</li>
		<li>
			<a href="#dontseepostwithtermindatabase">dontSeePostWithTermInDatabase</a>
		</li>
		<li>
			<a href="#dontseetableindatabase">dontSeeTableInDatabase</a>
		</li>
		<li>
			<a href="#dontseetermindatabase">dontSeeTermInDatabase</a>
		</li>
		<li>
			<a href="#dontseetermmetaindatabase">dontSeeTermMetaInDatabase</a>
		</li>
		<li>
			<a href="#dontseetermtaxonomyindatabase">dontSeeTermTaxonomyInDatabase</a>
		</li>
		<li>
			<a href="#dontseeuserindatabase">dontSeeUserInDatabase</a>
		</li>
		<li>
			<a href="#dontseeusermetaindatabase">dontSeeUserMetaInDatabase</a>
		</li>
		<li>
			<a href="#getsitedomain">getSiteDomain</a>
		</li>
		<li>
			<a href="#getuserstablename">getUsersTableName</a>
		</li>
		<li>
			<a href="#graballfromdatabase">grabAllFromDatabase</a>
		</li>
		<li>
			<a href="#grabattachmentattachedfile">grabAttachmentAttachedFile</a>
		</li>
		<li>
			<a href="#grabattachmentmetadata">grabAttachmentMetadata</a>
		</li>
		<li>
			<a href="#grabblogdomain">grabBlogDomain</a>
		</li>
		<li>
			<a href="#grabblogpath">grabBlogPath</a>
		</li>
		<li>
			<a href="#grabblogtablename">grabBlogTableName</a>
		</li>
		<li>
			<a href="#grabblogtablenames">grabBlogTableNames</a>
		</li>
		<li>
			<a href="#grabblogtableprefix">grabBlogTablePrefix</a>
		</li>
		<li>
			<a href="#grabblogversionstablename">grabBlogVersionsTableName</a>
		</li>
		<li>
			<a href="#grabblogstablename">grabBlogsTableName</a>
		</li>
		<li>
			<a href="#grabcommentmetatablename">grabCommentmetaTableName</a>
		</li>
		<li>
			<a href="#grabcommentstablename">grabCommentsTableName</a>
		</li>
		<li>
			<a href="#grablatestentrybyfromdatabase">grabLatestEntryByFromDatabase</a>
		</li>
		<li>
			<a href="#grablinkstablename">grabLinksTableName</a>
		</li>
		<li>
			<a href="#graboptionfromdatabase">grabOptionFromDatabase</a>
		</li>
		<li>
			<a href="#grabpostmetafromdatabase">grabPostMetaFromDatabase</a>
		</li>
		<li>
			<a href="#grabpostmetatablename">grabPostmetaTableName</a>
		</li>
		<li>
			<a href="#grabpoststablename">grabPostsTableName</a>
		</li>
		<li>
			<a href="#grabprefixedtablenamefor">grabPrefixedTableNameFor</a>
		</li>
		<li>
			<a href="#grabregistrationlogtablename">grabRegistrationLogTableName</a>
		</li>
		<li>
			<a href="#grabsignupstablename">grabSignupsTableName</a>
		</li>
		<li>
			<a href="#grabsitemetatablename">grabSiteMetaTableName</a>
		</li>
		<li>
			<a href="#grabsiteoptionfromdatabase">grabSiteOptionFromDatabase</a>
		</li>
		<li>
			<a href="#grabsitetablename">grabSiteTableName</a>
		</li>
		<li>
			<a href="#grabsitetransientfromdatabase">grabSiteTransientFromDatabase</a>
		</li>
		<li>
			<a href="#grabsiteurl">grabSiteUrl</a>
		</li>
		<li>
			<a href="#grabtableprefix">grabTablePrefix</a>
		</li>
		<li>
			<a href="#grabtermidfromdatabase">grabTermIdFromDatabase</a>
		</li>
		<li>
			<a href="#grabtermmetatablename">grabTermMetaTableName</a>
		</li>
		<li>
			<a href="#grabtermrelationshipstablename">grabTermRelationshipsTableName</a>
		</li>
		<li>
			<a href="#grabtermtaxonomyidfromdatabase">grabTermTaxonomyIdFromDatabase</a>
		</li>
		<li>
			<a href="#grabtermtaxonomytablename">grabTermTaxonomyTableName</a>
		</li>
		<li>
			<a href="#grabtermstablename">grabTermsTableName</a>
		</li>
		<li>
			<a href="#grabuseridfromdatabase">grabUserIdFromDatabase</a>
		</li>
		<li>
			<a href="#grabusermetafromdatabase">grabUserMetaFromDatabase</a>
		</li>
		<li>
			<a href="#grabusermetatablename">grabUsermetaTableName</a>
		</li>
		<li>
			<a href="#grabuserstablename">grabUsersTableName</a>
		</li>
		<li>
			<a href="#haveattachmentindatabase">haveAttachmentInDatabase</a>
		</li>
		<li>
			<a href="#haveblogindatabase">haveBlogInDatabase</a>
		</li>
		<li>
			<a href="#havecommentindatabase">haveCommentInDatabase</a>
		</li>
		<li>
			<a href="#havecommentmetaindatabase">haveCommentMetaInDatabase</a>
		</li>
		<li>
			<a href="#havelinkindatabase">haveLinkInDatabase</a>
		</li>
		<li>
			<a href="#havemanyblogsindatabase">haveManyBlogsInDatabase</a>
		</li>
		<li>
			<a href="#havemanycommentsindatabase">haveManyCommentsInDatabase</a>
		</li>
		<li>
			<a href="#havemanylinksindatabase">haveManyLinksInDatabase</a>
		</li>
		<li>
			<a href="#havemanypostsindatabase">haveManyPostsInDatabase</a>
		</li>
		<li>
			<a href="#havemanytermsindatabase">haveManyTermsInDatabase</a>
		</li>
		<li>
			<a href="#havemanyusersindatabase">haveManyUsersInDatabase</a>
		</li>
		<li>
			<a href="#havemenuindatabase">haveMenuInDatabase</a>
		</li>
		<li>
			<a href="#havemenuitemindatabase">haveMenuItemInDatabase</a>
		</li>
		<li>
			<a href="#haveoptionindatabase">haveOptionInDatabase</a>
		</li>
		<li>
			<a href="#havepageindatabase">havePageInDatabase</a>
		</li>
		<li>
			<a href="#havepostindatabase">havePostInDatabase</a>
		</li>
		<li>
			<a href="#havepostmetaindatabase">havePostmetaInDatabase</a>
		</li>
		<li>
			<a href="#havesiteoptionindatabase">haveSiteOptionInDatabase</a>
		</li>
		<li>
			<a href="#havesitetransientindatabase">haveSiteTransientInDatabase</a>
		</li>
		<li>
			<a href="#havetermindatabase">haveTermInDatabase</a>
		</li>
		<li>
			<a href="#havetermmetaindatabase">haveTermMetaInDatabase</a>
		</li>
		<li>
			<a href="#havetermrelationshipindatabase">haveTermRelationshipInDatabase</a>
		</li>
		<li>
			<a href="#havetransientindatabase">haveTransientInDatabase</a>
		</li>
		<li>
			<a href="#haveusercapabilitiesindatabase">haveUserCapabilitiesInDatabase</a>
		</li>
		<li>
			<a href="#haveuserindatabase">haveUserInDatabase</a>
		</li>
		<li>
			<a href="#haveuserlevelsindatabase">haveUserLevelsInDatabase</a>
		</li>
		<li>
			<a href="#haveusermetaindatabase">haveUserMetaInDatabase</a>
		</li>
		<li>
			<a href="#importsqldumpfile">importSqlDumpFile</a>
		</li>
		<li>
			<a href="#seeattachmentindatabase">seeAttachmentInDatabase</a>
		</li>
		<li>
			<a href="#seeblogindatabase">seeBlogInDatabase</a>
		</li>
		<li>
			<a href="#seecommentindatabase">seeCommentInDatabase</a>
		</li>
		<li>
			<a href="#seecommentmetaindatabase">seeCommentMetaInDatabase</a>
		</li>
		<li>
			<a href="#seelinkindatabase">seeLinkInDatabase</a>
		</li>
		<li>
			<a href="#seeoptionindatabase">seeOptionInDatabase</a>
		</li>
		<li>
			<a href="#seepageindatabase">seePageInDatabase</a>
		</li>
		<li>
			<a href="#seepostindatabase">seePostInDatabase</a>
		</li>
		<li>
			<a href="#seepostmetaindatabase">seePostMetaInDatabase</a>
		</li>
		<li>
			<a href="#seepostwithtermindatabase">seePostWithTermInDatabase</a>
		</li>
		<li>
			<a href="#seesiteoptionindatabase">seeSiteOptionInDatabase</a>
		</li>
		<li>
			<a href="#seesitesitetransientindatabase">seeSiteSiteTransientInDatabase</a>
		</li>
		<li>
			<a href="#seetableindatabase">seeTableInDatabase</a>
		</li>
		<li>
			<a href="#seetermindatabase">seeTermInDatabase</a>
		</li>
		<li>
			<a href="#seetermmetaindatabase">seeTermMetaInDatabase</a>
		</li>
		<li>
			<a href="#seetermrelationshipindatabase">seeTermRelationshipInDatabase</a>
		</li>
		<li>
			<a href="#seetermtaxonomyindatabase">seeTermTaxonomyInDatabase</a>
		</li>
		<li>
			<a href="#seeuserindatabase">seeUserInDatabase</a>
		</li>
		<li>
			<a href="#seeusermetaindatabase">seeUserMetaInDatabase</a>
		</li>
		<li>
			<a href="#useblog">useBlog</a>
		</li>
		<li>
			<a href="#usemainblog">useMainBlog</a>
		</li>
		<li>
			<a href="#usetheme">useTheme</a>
		</li>
	</ul>
</nav>

<h3>countRowsInDatabase</h3>

<hr>

<p>Returns the number of table rows matching a criteria.</p>
<pre><code class="language-php">    $I-&gt;haveManyPostsInDatabase(3, ['post_status' =&gt; 'draft' ]);
    $I-&gt;haveManyPostsInDatabase(3, ['post_status' =&gt; 'private' ]);
    // Make sure there are now the expected number of draft posts.
    $postsTable = $I-&gt;grabPostsTableName();
    $draftsCount = $I-&gt;countRowsInDatabase($postsTable, ['post_status' =&gt; 'draft']);</code></pre>
<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$table</strong> - The table to count the rows in.</li>
<li><code>array</code> <strong>$criteria</strong> - Search criteria, if empty all table rows will be counted.</li></ul>
  

<h3>dontHaveAttachmentFilesInDatabase</h3>

<hr>

<p>Removes all the files attached with an attachment post, it will not remove the database entries. Requires the <code>WPFilesystem</code> module to be loaded in the suite.</p>
<pre><code class="language-php">    $posts = $I-&gt;grabPostsTableName();
    $attachmentIds = $I-&gt;grabColumnFromDatabase($posts, 'ID', ['post_type' =&gt; 'attachment']);
    // This will only remove the files, not the database entries.
    $I-&gt;dontHaveAttachmentFilesInDatabase($attachmentIds);</code></pre>
<h4>Parameters</h4>
<ul>
<li><code>array/int</code> <strong>$attachmentIds</strong> - An attachment post ID or an array of attachment post IDs.</li></ul>
  

<h3>dontHaveAttachmentInDatabase</h3>

<hr>

<p>Removes an attachment from the posts table.</p>
<pre><code>    $postmeta = $I-&gt;grabpostmetatablename();
    $thumbnailId = $I-&gt;grabFromDatabase($postmeta, 'meta_value', [
         'post_id' =&gt; $id,
         'meta_key'=&gt;'thumbnail_id'
    ]);
    // Remove only the database entry (including postmeta) but not the files.
    $I-&gt;dontHaveAttachmentInDatabase($thumbnailId);
    // Remove the database entry (including postmeta) and the files.
    $I-&gt;dontHaveAttachmentInDatabase($thumbnailId, true, true);</code></pre>
<h4>Parameters</h4>
<ul>
<li><code>array</code> <strong>$criteria</strong> - An array of search criteria to find the attachment post in the posts table.</li>
<li><code>bool</code> <strong>$purgeMeta</strong> - If set to <code>true</code> then the meta for the attachment will be purged too.</li>
<li><code>bool</code> <strong>$removeFiles</strong> - Remove all files too, requires the <code>WPFilesystem</code> module to be loaded in the suite.</li></ul>
  

<h3>dontHaveBlogInDatabase</h3>

<hr>

<p>Removes one ore more blogs frome the database.</p>
<pre><code class="language-php">    // Remove the blog, all its tables and files.
    $I-&gt;dontHaveBlogInDatabase(['path' =&gt; 'test/one']);
    // Remove the blog entry, not the tables though.
    $I-&gt;dontHaveBlogInDatabase(['blog_id' =&gt; $blogId]);
    // Remove multiple blogs.
    $I-&gt;dontHaveBlogInDatabase(['domain' =&gt; 'test']);</code></pre>
<h4>Parameters</h4>
<ul>
<li><code>array</code> <strong>$criteria</strong> - An array of search criteria to find the blog rows in the blogs table.</li>
<li><code>bool</code> <strong>$removeTables</strong> - Remove the blog tables.</li>
<li><code>bool</code> <strong>$removeUploads</strong> - Remove the blog uploads; requires the <code>WPFilesystem</code> module.</li></ul>
  

<h3>dontHaveCommentInDatabase</h3>

<hr>

<p>Removes an entry from the comments table.</p>
<pre><code class="language-php">    $I-&gt;dontHaveCommentInDatabase(['comment_post_ID' =&gt; 23, 'comment_url' =&gt; 'http://example.copm']);</code></pre>
<h4>Parameters</h4>
<ul>
<li><code>array</code> <strong>$criteria</strong> - An array of search criteria.</li>
<li><code>bool</code> <strong>$purgeMeta</strong> - If set to <code>true</code> then the meta for the comment will be purged too.</li></ul>
  

<h3>dontHaveCommentMetaInDatabase</h3>

<hr>

<p>Removes a post comment meta from the database</p>
<pre><code class="language-php">    // Remove all meta for the comment with an ID of 23.
    $I-&gt;dontHaveCommentMetaInDatabase(['comment_id' =&gt; 23]);
    // Remove the `count` comment meta for the comment with an ID of 23.
    $I-&gt;dontHaveCommentMetaInDatabase(['comment_id' =&gt; 23, 'meta_key' =&gt; 'count']);</code></pre>
<h4>Parameters</h4>
<ul>
<li><code>array</code> <strong>$criteria</strong> - An array of search criteria.</li></ul>
  

<h3>dontHaveInDatabase</h3>

<hr>

<p>Deletes a database entry.</p>
<pre><code class="language-php">    $I-&gt;dontHaveInDatabase('custom_table', ['book_ID' =&gt; 23, 'book_genre' =&gt; 'fiction']);</code></pre>
<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$table</strong> - The table name.</li>
<li><code>array</code> <strong>$criteria</strong> - An associative array of the column names and values to use as deletion criteria.</li></ul>
  

<h3>dontHaveLinkInDatabase</h3>

<hr>

<p>Removes a link from the database.</p>
<pre><code class="language-php">    $I-&gt;dontHaveLinkInDatabase(['link_url' =&gt; 'http://example.com']);</code></pre>
<h4>Parameters</h4>
<ul>
<li><code>array</code> <strong>$criteria</strong> - An array of search criteria.</li></ul>
  

<h3>dontHaveOptionInDatabase</h3>

<hr>

<p>Removes an entry from the options table.</p>
<pre><code class="language-php">    // Remove the `foo` option.
    $I-&gt;dontHaveOptionInDatabase('foo');
    // Remove the 'bar' option only if it has the `baz` value.
    $I-&gt;dontHaveOptionInDatabase('bar', 'baz');</code></pre>
<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$key</strong> - The option name.</li>
<li><code>null/mixed</code> <strong>$value</strong> - If set the option will only be removed if its value matches the passed one.</li></ul>
  

<h3>dontHavePostInDatabase</h3>

<hr>

<p>Removes an entry from the posts table.</p>
<pre><code class="language-php">    $posts = $I-&gt;haveManyPostsInDatabase(3, ['post_title' =&gt; 'Test {{n}}']);
    $I-&gt;dontHavePostInDatabase(['post_title' =&gt; 'Test 2']);</code></pre>
<h4>Parameters</h4>
<ul>
<li><code>array</code> <strong>$criteria</strong> - An array of search criteria.</li>
<li><code>bool</code> <strong>$purgeMeta</strong> - If set to <code>true</code> then the meta for the post will be purged too.</li></ul>
  

<h3>dontHavePostMetaInDatabase</h3>

<hr>

<p>Removes an entry from the postmeta table.</p>
<pre><code class="language-php">    $postId = $I-&gt;havePostInDatabase(['meta_input' =&gt; ['rating' =&gt; 23]]);
    $I-&gt;dontHavePostMetaInDatabase(['post_id' =&gt; $postId, 'meta_key' =&gt; 'rating']);</code></pre>
<h4>Parameters</h4>
<ul>
<li><code>array</code> <strong>$criteria</strong> - An array of search criteria.</li></ul>
  

<h3>dontHaveSiteOptionInDatabase</h3>

<hr>

<p>Removes a site option from the database.</p>
<pre><code class="language-php">    // Remove the `foo_count` option.
    $I-&gt;dontHaveSiteOptionInDatabase('foo_count');
    // Remove the `foo_count` option only if its value is `23`.
    $I-&gt;dontHaveSiteOptionInDatabase('foo_count', 23);</code></pre>
<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$key</strong> - The option name.</li>
<li><code>null/mixed</code> <strong>$value</strong> - If set the option will only be removed it its value matches the specified one.</li></ul>
  

<h3>dontHaveSiteTransientInDatabase</h3>

<hr>

<p>Removes a site transient from the database.</p>
<pre><code class="language-php">    $I-&gt;dontHaveSiteTransientInDatabase(['my_plugin_site_buffer']);</code></pre>
<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$key</strong> - The name of the transient to delete.</li></ul>
  

<h3>dontHaveTableInDatabase</h3>

<hr>

<p>Removes a table from the database. The case where a table does not exist is handled without raising an error.</p>
<pre><code class="language-php">    $ordersTable = $I-&gt;grabPrefixedTableNameFor('orders');
    $I-&gt;dontHaveTableInDatabase($ordersTable);</code></pre>
<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$fullTableName</strong> - The full table name, including the table prefix.</li></ul>
  

<h3>dontHaveTermInDatabase</h3>

<hr>

<p>Removes a term from the database.</p>
<pre><code class="language-php">    $I-&gt;dontHaveTermInDatabase(['name' =&gt; 'romance']);
    $I-&gt;dontHaveTermInDatabase(['slug' =&gt; 'genre--romance']);</code></pre>
<h4>Parameters</h4>
<ul>
<li><code>array</code> <strong>$criteria</strong> - An array of search criteria.</li>
<li><code>bool</code> <strong>$purgeMeta</strong> - Whether the terms meta should be purged along side with the meta or not.</li></ul>
  

<h3>dontHaveTermMetaInDatabase</h3>

<hr>

<p>Removes a term meta from the database.</p>
<pre><code class="language-php">    // Remove the "karma" key.
    $I-&gt;dontHaveTermMetaInDatabase(['term_id' =&gt; $termId, 'meta_key' =&gt; 'karma']);
    // Remove all meta for the term.
    $I-&gt;dontHaveTermMetaInDatabase(['term_id' =&gt; $termId]);</code></pre>
<h4>Parameters</h4>
<ul>
<li><code>array</code> <strong>$criteria</strong> - An array of search criteria.</li></ul>
  

<h3>dontHaveTermRelationshipInDatabase</h3>

<hr>

<p>Removes an entry from the term_relationships table.</p>
<pre><code class="language-php">    // Remove the relation between a post and a category.
    $I-&gt;dontHaveTermRelationshipInDatabase(['object_id' =&gt; $postId, 'term_taxonomy_id' =&gt; $ttaxId]);
    // Remove all terms for a post.
    $I-&gt;dontHaveTermMetaInDatabase(['object_id' =&gt; $postId]);</code></pre>
<h4>Parameters</h4>
<ul>
<li><code>array</code> <strong>$criteria</strong> - An array of search criteria.</li></ul>
  

<h3>dontHaveTermTaxonomyInDatabase</h3>

<hr>

<p>Removes an entry from the <code>term_taxonomy</code> table.</p>
<pre><code class="language-php">    // Remove a specific term from the genre taxonomy.
    $I-&gt;dontHaveTermTaxonomyInDatabase(['term_id' =&gt; $postId, 'taxonomy' =&gt; 'genre']);
    // Remove all terms for a taxonomy.
    $I-&gt;dontHaveTermTaxonomyInDatabase(['taxonomy' =&gt; 'genre']);</code></pre>
<h4>Parameters</h4>
<ul>
<li><code>array</code> <strong>$criteria</strong> - An array of search criteria.</li></ul>
  

<h3>dontHaveTransientInDatabase</h3>

<hr>

<p>Removes a transient from the database.</p>
<pre><code class="language-php">    // Removes the `tweets` transient from the database, if set.
    $I-&gt;dontHaveTransientInDatabase('tweets');</code></pre>
<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$transient</strong> - The name of the transient to delete.</li></ul>
  

<h3>dontHaveUserInDatabase</h3>

<hr>

<p>Removes a user from the database.</p>
<pre><code class="language-php">    $bob = $I-&gt;haveUserInDatabase('bob');
    $alice = $I-&gt;haveUserInDatabase('alice');
    // Remove Bob's user and meta.
    $I-&gt;dontHaveUserInDatabase('bob');
    // Remove Alice's user but not meta.
    $I-&gt;dontHaveUserInDatabase($alice);</code></pre>
<h4>Parameters</h4>
<ul>
<li><code>int/string</code> <strong>$userIdOrLogin</strong> - The user ID or login name.</li>
<li><code>bool</code> <strong>$purgeMeta</strong> - Whether the user meta should be purged alongside the user or not.</li></ul>
  

<h3>dontHaveUserInDatabaseWithEmail</h3>

<hr>

<p>Removes a user(s) from the database using the user email address.</p>
<pre><code class="language-php">
    $luca = $I-&gt;haveUserInDatabase('luca', 'editor', ['user_email' =&gt; 'luca@example.org']);</code></pre>
<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$userEmail</strong> - The email of the user to remove.</li>
<li><code>bool</code> <strong>$purgeMeta</strong> - Whether the user meta should be purged alongside the user or not.</li></ul>
  

<h3>dontHaveUserMetaInDatabase</h3>

<hr>

<p>Removes an entry from the usermeta table.</p>
<pre><code class="language-php">    // Remove the `karma` user meta for a user.
    $I-&gt;dontHaveUserMetaInDatabase(['user_id' =&gt; 23, 'meta_key' =&gt; 'karma']);
    // Remove all the user meta for a user.
    $I-&gt;dontHaveUserMetaInDatabase(['user_id' =&gt; 23]);</code></pre>
<h4>Parameters</h4>
<ul>
<li><code>array</code> <strong>$criteria</strong> - An array of search criteria.</li></ul>
  

<h3>dontSeeAttachmentInDatabase</h3>

<hr>

<p>Checks that an attachment is not in the database.</p>
<pre><code class="language-php">    $url = 'https://example.org/images/foo.png';
    $I-&gt;dontSeeAttachmentInDatabase(['guid' =&gt; $url]);</code></pre>
<h4>Parameters</h4>
<ul>
<li><code>array</code> <strong>$criteria</strong> - An array of search criteria.</li></ul>
  

<h3>dontSeeBlogInDatabase</h3>

<hr>

<p>Checks that a row is not present in the <code>blogs</code> table.</p>
<pre><code class="language-php">    $I-&gt;haveManyBlogsInDatabase(2, ['path' =&gt; 'test-{{n}}'], false)
    $I-&gt;dontSeeBlogInDatabase(['path' =&gt; '/test-3/'])</code></pre>
<h4>Parameters</h4>
<ul>
<li><code>array</code> <strong>$criteria</strong> - An array of search criteria.</li></ul>
  

<h3>dontSeeCommentInDatabase</h3>

<hr>

<p>Checks that a comment is not in the database. Will look up the &quot;comments&quot; table.</p>
<pre><code class="language-php">    // Checks for one comment.
    $I-&gt;dontSeeCommentInDatabase(['comment_ID' =&gt; 23]);
    // Checks for comments from a user.
    $I-&gt;dontSeeCommentInDatabase(['user_id' =&gt; 89]);</code></pre>
<h4>Parameters</h4>
<ul>
<li><code>array</code> <strong>$criteria</strong> - The serach criteria.</li></ul>
  

<h3>dontSeeCommentMetaInDatabase</h3>

<hr>

<p>Checks that a comment meta value is not in the database. Will look up the &quot;commentmeta&quot; table.</p>
<pre><code class="language-php">    // Delete a comment `karma` meta.
    $I-&gt;dontSeeCommentMetaInDatabase(['comment_id' =&gt; 23, 'meta_key' =&gt; 'karma']);
    // Delete all meta for a comment.
    $I-&gt;dontSeeCommentMetaInDatabase(['comment_id' =&gt; 23]);</code></pre>
<h4>Parameters</h4>
<ul>
<li><code>array</code> <strong>$criteria</strong> - An array of search criteria.</li></ul>
  

<h3>dontSeeLinkInDatabase</h3>

<hr>

<p>Checks that a link is not in the <code>links</code> database table.</p>
<pre><code class="language-php">    $I-&gt;dontSeeLinkInDatabase(['link_url' =&gt; 'http://example.com']);
    $I-&gt;dontSeeLinkInDatabase(['link_url' =&gt; 'http://example.com', 'link_name' =&gt; 'example']);</code></pre>
<h4>Parameters</h4>
<ul>
<li><code>array</code> <strong>$criteria</strong> - An array of search criteria.</li></ul>
  

<h3>dontSeeOptionInDatabase</h3>

<hr>

<p>Checks that an option is not in the database for the current blog. If the value is an object or an array then the serialized option will be checked.</p>
<pre><code class="language-php">    $I-&gt;dontHaveOptionInDatabase('posts_per_page');
    $I-&gt;dontSeeOptionInDatabase('posts_per_page');</code></pre>
<h4>Parameters</h4>
<ul>
<li><code>array</code> <strong>$criteria</strong> - An array of search criteria.</li></ul>
  

<h3>dontSeePageInDatabase</h3>

<hr>

<p>Checks that a page is not in the database.</p>
<pre><code class="language-php">    // Assert a page with an ID does not exist.
    $I-&gt;dontSeePageInDatabase(['ID' =&gt; 23]);
    // Assert a page with a slug and ID.
    $I-&gt;dontSeePageInDatabase(['post_name' =&gt; 'test', 'ID' =&gt; 23]);</code></pre>
<h4>Parameters</h4>
<ul>
<li><code>array</code> <strong>$criteria</strong> - An array of search criteria.</li></ul>
  

<h3>dontSeePostInDatabase</h3>

<hr>

<p>Checks that a post is not in the database.</p>
<pre><code class="language-php">    // Asserts a post with title 'Test' is not in the database.
    $I-&gt;dontSeePostInDatabase(['post_title' =&gt; 'Test']);
    // Asserts a post with title 'Test' and content 'Test content' is not in the database.
    $I-&gt;dontSeePostInDatabase(['post_title' =&gt; 'Test', 'post_content' =&gt; 'Test content']);</code></pre>
<h4>Parameters</h4>
<ul>
<li><code>array</code> <strong>$criteria</strong> - An array of search criteria.</li></ul>
  

<h3>dontSeePostMetaInDatabase</h3>

<hr>

<p>Checks that a post meta value does not exist. If the meta value is an object or an array then the check will be made on its serialized version.</p>
<pre><code class="language-php">    $postId = $I-&gt;havePostInDatabase(['meta_input' =&gt; ['foo' =&gt; 'bar']]);
    $I-&gt;dontSeePostMetaInDatabase(['post_id' =&gt; $postId, 'meta_key' =&gt; 'woot']);</code></pre>
<h4>Parameters</h4>
<ul>
<li><code>array</code> <strong>$criteria</strong> - An array of search criteria.</li></ul>
  

<h3>dontSeePostWithTermInDatabase</h3>

<hr>

<p>Checks that a post to term relation does not exist in the database. The method will check the &quot;term_relationships&quot; table.</p>
<pre><code class="language-php">    $fiction = $I-&gt;haveTermInDatabase('fiction', 'genre');
    $nonFiction = $I-&gt;haveTermInDatabase('non-fiction', 'genre');
    $postId = $I-&gt;havePostInDatabase(['tax_input' =&gt; ['genre' =&gt; ['fiction']]]);
    $I-&gt;dontSeePostWithTermInDatabase($postId, $nonFiction['term_taxonomy_id], );</code></pre>
<pre><code>                                        passed this parameter will be interpreted as a `term_id`, else as a
                                        `term_taxonomy_id`.
                                        the
                                        term order.
                                        to build a `taxonomy_term_id` from the `term_id`.</code></pre>
<h4>Parameters</h4>
<ul>
<li><code>int</code> <strong>$post_id</strong> - The post ID.</li>
<li><code>int</code> <strong>$term_taxonomy_id</strong> - The term <code>term_id</code> or <code>term_taxonomy_id</code>; if the <code>$taxonomy</code> argument is</li>
<li><code>int/null</code> <strong>$term_order</strong> - The order the term applies to the post, defaults to <code>null</code> to not use</li>
<li><code>string/null</code> <strong>$taxonomy</strong> - The taxonomy the <code>term_id</code> is for; if passed this parameter will be used</li></ul>
  

<h3>dontSeeTableInDatabase</h3>

<hr>

<p>Checks that a table is not in the database.</p>
<pre><code class="language-php">    $options = $I-&gt;grabPrefixedTableNameFor('options');
    $I-&gt;dontHaveTableInDatabase($options)
    $I-&gt;dontSeeTableInDatabase($options);</code></pre>
<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$table</strong> - The full table name, including the table prefix.</li></ul>
  

<h3>dontSeeTermInDatabase</h3>

<hr>

<p>Makes sure a term is not in the database. Looks up both the <code>terms</code> table and the <code>term_taxonomy</code> tables.</p>
<pre><code class="language-php">    // Asserts a 'fiction' term is not in the database.
    $I-&gt;dontSeeTermInDatabase(['name' =&gt; 'fiction']);
    // Asserts a 'fiction' term with slug 'genre--fiction' is not in the database.
    $I-&gt;dontSeeTermInDatabase(['name' =&gt; 'fiction', 'slug' =&gt; 'genre--fiction']);</code></pre>
<pre><code>                       `term_taxonomy` tables.</code></pre>
<h4>Parameters</h4>
<ul>
<li><code>array</code> <strong>$criteria</strong> - An array of criteria to search for the term, can be columns from the <code>terms</code> and the</li></ul>
  

<h3>dontSeeTermMetaInDatabase</h3>

<hr>

<p>Checks that a term meta is not in the database.</p>
<pre><code class="language-php">    list($termId, $termTaxonomyId) = $I-&gt;haveTermInDatabase('fiction', 'genre');
    $I-&gt;haveTermMetaInDatabase($termId, 'rating', 4);
    $I-&gt;dontSeeTermMetaInDatabase(['term_id' =&gt; $termId,'meta_key' =&gt; 'average_review']);</code></pre>
<h4>Parameters</h4>
<ul>
<li><code>array</code> <strong>$criteria</strong> - An array of search criteria.</li></ul>
  

<h3>dontSeeTermTaxonomyInDatabase</h3>

<hr>

<p>Checks that a term taxonomy is not in the database.</p>
<pre><code class="language-php">    list($termId, $termTaxonomyId) = $I-&gt;haveTermInDatabase('fiction', 'genre');
    $I-&gt;dontSeeTermTaxonomyInDatabase(['term_id' =&gt; $termId, 'taxonomy' =&gt; 'country']);</code></pre>
<h4>Parameters</h4>
<ul>
<li><code>array</code> <strong>$criteria</strong> - An array of search criteria.</li></ul>
  

<h3>dontSeeUserInDatabase</h3>

<hr>

<p>Checks that a user is not in the database.</p>
<pre><code class="language-php">    // Asserts a user does not exist in the database.
    $I-&gt;dontSeeUserInDatabase(['user_login' =&gt; 'luca']);
    // Asserts a user with email and login is not in the database.
    $I-&gt;dontSeeUserInDatabase(['user_login' =&gt; 'luca', 'user_email' =&gt; 'luca@theaveragedev.com']);</code></pre>
<h4>Parameters</h4>
<ul>
<li><code>array</code> <strong>$criteria</strong> - An array of search criteria.</li></ul>
  

<h3>dontSeeUserMetaInDatabase</h3>

<hr>

<p>Check that a user meta value is not in the database.</p>
<pre><code class="language-php">    // Asserts a user does not have a 'karma' meta assigned.
    $I-&gt;dontSeeUserMetaInDatabase(['user_id' =&gt; 23, 'meta_key' =&gt; 'karma']);
    // Asserts no user has any 'karma' meta assigned.
    $I-&gt;dontSeeUserMetaInDatabase(['meta_key' =&gt; 'karma']);</code></pre>
<h4>Parameters</h4>
<ul>
<li><code>array</code> <strong>$criteria</strong> - An array of search criteria.</li></ul>
  

<h3>getSiteDomain</h3>

<hr>

<p>Returns the site domain inferred from the <code>url</code> set in the config.</p>
<pre><code class="language-php">    $domain = $I-&gt;getSiteDomain();
    // We should be redirected to the HTTPS version when visiting the HTTP version.
    $I-&gt;amOnPage('http://' . $domain);
    $I-&gt;seeCurrentUrlEquals('https://' . $domain);</code></pre>
  

<h3>getUsersTableName</h3>

<hr>

<p>Returns the prefixed users table name.</p>
<pre><code class="language-php">    // Given a `wp_` table prefix returns `wp_users`.
    $usersTable = $I-&gt;getUsersTableName();
    // Given a `wp_` table prefix returns `wp_users`.
    $I-&gt;useBlog(23);
    $usersTable = $I-&gt;getUsersTableName();</code></pre>
  

<h3>grabAllFromDatabase</h3>

<hr>

<p>Returns all entries matching a criteria from the database.</p>
<pre><code class="language-php">    $books = $I-&gt;grabPrefixedTableNameFor('books');
    $I-&gt;grabAllFromDatabase($books, 'title', ['genre' =&gt; 'fiction']);</code></pre>
<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$table</strong> - The table to grab the values from.</li>
<li><code>string</code> <strong>$column</strong> - The column to fetch.</li>
<li><code>array</code> <strong>$criteria</strong> - The search criteria.</li></ul>
  

<h3>grabAttachmentAttachedFile</h3>

<hr>

<p>Returns the path, as stored in the database, of an attachment <code>_wp_attached_file</code> meta. The attached file is, usually, an attachment origal file.</p>
<pre><code class="language-php">    $file = $I-&gt;grabAttachmentAttachedFile($attachmentId);
    $fileInfo = new SplFileInfo($file);
    $I-&gt;assertEquals('jpg', $fileInfo-&gt;getExtension());</code></pre>
<h4>Parameters</h4>
<ul>
<li><code>int</code> <strong>$attachmentPostId</strong> - The attachment post ID.</li></ul>
  

<h3>grabAttachmentMetadata</h3>

<hr>

<p>Returns the metadata array for an attachment post. This is the value of the <code>_wp_attachment_metadata</code> meta.</p>
<pre><code class="language-php">    $metadata = $I-&gt;grabAttachmentMetadata($attachmentId);
    $I-&gt;assertEquals(['thumbnail', 'medium', 'medium_large'], array_keys($metadata['sizes']);</code></pre>
<h4>Parameters</h4>
<ul>
<li><code>int</code> <strong>$attachmentPostId</strong> - The attachment post ID.</li></ul>
  

<h3>grabBlogDomain</h3>

<hr>

<p>Returns a blog domain given its ID.</p>
<pre><code class="language-php">    $blogIds = $I-&gt;haveManyBlogsInDatabase(3);
    $domains = array_map(function($blogId){
         return $I-&gt;grabBlogDomain($blogId);
    }, $blogIds);</code></pre>
<h4>Parameters</h4>
<ul>
<li><code>int</code> <strong>$blogId</strong> - The blog ID.</li></ul>
  

<h3>grabBlogPath</h3>

<hr>

<p>Grabs a blog domain from the blogs table.</p>
<pre><code class="language-php">    $blogId = $I-&gt;haveBlogInDatabase('test');
    $path = $I-&gt;grabBlogDomain($blogId);
    $I-&gt;amOnSubdomain($path);
    $I-&gt;amOnPage('/');</code></pre>
<h4>Parameters</h4>
<ul>
<li><code>int</code> <strong>$blogId</strong> - The blog ID.</li></ul>
  

<h3>grabBlogTableName</h3>

<hr>

<p>Returns the full name of a table for a blog from a multisite installation database.</p>
<pre><code class="language-php">    $blogOptionTable = $I-&gt;grabBlogTableName($blogId, 'option');</code></pre>
<h4>Parameters</h4>
<ul>
<li><code>int</code> <strong>$blogId</strong> - The blog ID.</li>
<li><code>string</code> <strong>$table</strong> - The table name, without table prefix.</li></ul>
  

<h3>grabBlogTableNames</h3>

<hr>

<p>Returns a list of tables for a blog ID.</p>
<pre><code class="language-php">    $blogId = $I-&gt;haveBlogInDatabase('test');
    $tables = $I-&gt;grabBlogTableNames($blogId);
    $options = array_filter($tables, function($tableName){
         return str_pos($tableName, 'options') !== false;
    });</code></pre>
<h4>Parameters</h4>
<ul>
<li><code>int</code> <strong>$blogId</strong> - The ID of the blog to fetch the tables for.</li></ul>
  

<h3>grabBlogTablePrefix</h3>

<hr>

<p>Returns the table prefix for a blog.</p>
<pre><code class="language-php">    $blogId = $I-&gt;haveBlogInDatabase('test');
    $blogTablePrefix = $I-&gt;getBlogTablePrefix($blogId);
    $blogOrders = $I-&gt;blogTablePrefix . 'orders';</code></pre>
<h4>Parameters</h4>
<ul>
<li><code>int</code> <strong>$blogId</strong> - The blog ID.</li></ul>
  

<h3>grabBlogVersionsTableName</h3>

<hr>

<p>Gets the prefixed <code>blog_versions</code> table name.</p>
<pre><code class="language-php">    // Assuming a `wp_` table prefix it will return `wp_blog_versions`.
    $blogVersionsTable = $I-&gt;grabBlogVersionsTableName();
    $I-&gt;useBlog(23);
    // Assuming a `wp_` table prefix it will return `wp_blog_versions`.
    $blogVersionsTable = $I-&gt;grabBlogVersionsTableName();</code></pre>
  

<h3>grabBlogsTableName</h3>

<hr>

<p>Gets the prefixed <code>blogs</code> table name.</p>
<pre><code class="language-php">    // Assuming a `wp_` table prefix it will return `wp_blogs`.
    $blogVersionsTable = $I-&gt;grabBlogsTableName();
    $I-&gt;useBlog(23);
    // Assuming a `wp_` table prefix it will return `wp_blogs`.
    $blogVersionsTable = $I-&gt;grabBlogsTableName();</code></pre>
  

<h3>grabCommentmetaTableName</h3>

<hr>

<p>Returns the prefixed comment meta table name.</p>
<pre><code class="language-php">    // Get all the values of 'karma' for all comments.
    $commentMeta = $I-&gt;grabCommentmetaTableName();
    $I-&gt;grabAllFromDatabase($commentMeta, 'meta_value', ['meta_key' =&gt; 'karma']);</code></pre>
  

<h3>grabCommentsTableName</h3>

<hr>

<p>Gets the comments table name.</p>
<pre><code class="language-php">    // Will be `wp_comments`.
    $comments = $I-&gt;grabCommentsTableName();
    // Will be `wp_23_comments`.
    $I-&gt;useBlog(23);
    $comments = $I-&gt;grabCommentsTableName();</code></pre>
  

<h3>grabLatestEntryByFromDatabase</h3>

<hr>

<p>Returns the id value of the last table entry.</p>
<pre><code class="language-php">    $I-&gt;haveManyPostsInDatabase();
    $postsTable = $I-&gt;grabPostsTableName();
    $last = $I-&gt;grabLatestEntryByFromDatabase($postsTable, 'ID');</code></pre>
<pre><code>                        items.</code></pre>
<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$tableName</strong> - The table to fetch the last insertion for.</li>
<li><code>string</code> <strong>$idColumn</strong> - The column that is used, in the table, to uniquely identify</li></ul>
  

<h3>grabLinksTableName</h3>

<hr>

<p>Returns the prefixed links table name.</p>
<pre><code class="language-php">    // Given a `wp_` table prefix returns `wp_links`.
    $linksTable = $I-&gt;grabLinksTableName();
    // Given a `wp_` table prefix returns `wp_23_links`.
    $I-&gt;useBlog(23);
    $linksTable = $I-&gt;grabLinksTableName();</code></pre>
  

<h3>grabOptionFromDatabase</h3>

<hr>

<p>Gets an option value from the database.</p>
<pre><code class="language-php">    $count = $I-&gt;grabOptionFromDatabase('foo_count');</code></pre>
<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$option_name</strong> - The name of the option to grab from the database.</li></ul>
  

<h3>grabPostMetaFromDatabase</h3>

<hr>

<p>Gets the value of one or more post meta values from the database.</p>
<pre><code class="language-php">    $thumbnail_id = $I-&gt;grabPostMetaFromDatabase($postId, '_thumbnail_id', true);</code></pre>
<h4>Parameters</h4>
<ul>
<li><code>int</code> <strong>$postId</strong> - The post ID.</li>
<li><code>string</code> <strong>$metaKey</strong> - The key of the meta to retrieve.</li>
<li><code>bool</code> <strong>$single</strong> - Whether to return a single meta value or an arrya of all available meta values.</li></ul>
  

<h3>grabPostmetaTableName</h3>

<hr>

<p>Returns the prefixed post meta table name.</p>
<pre><code class="language-php">    // Returns 'wp_postmeta'.
    $I-&gt;grabPostmetaTableName();
    // Returns 'wp_23_postmeta'.
    $I-&gt;useBlog(23);
    $I-&gt;grabPostmetaTableName();</code></pre>
  

<h3>grabPostsTableName</h3>

<hr>

<p>Gets the posts prefixed table name.</p>
<pre><code class="language-php">    // Given a `wp_` table prefix returns `wp_posts`.
    $postsTable = $I-&gt;grabPostsTableName();
    // Given a `wp_` table prefix returns `wp_23_posts`.
    $I-&gt;useBlog(23);
    $postsTable = $I-&gt;grabPostsTableName();</code></pre>
  

<h3>grabPrefixedTableNameFor</h3>

<hr>

<p>Returns a prefixed table name for the current blog. If the table is not one to be prefixed (e.g. <code>users</code>) then the proper table name will be returned.</p>
<pre><code class="language-php">    // Will return wp_users.
    $usersTable = $I-&gt;grabPrefixedTableNameFor('users');
    // Will return wp_options.
    $optionsTable = $I-&gt;grabPrefixedTableNameFor('options');
    // Use a different blog and get its options table.
    $I-&gt;useBlog(2);
    $blogOptionsTable = $I-&gt;grabPrefixedTableNameFor('options');</code></pre>
<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$tableName</strong> - The table name, e.g. <code>options</code>.</li></ul>
  

<h3>grabRegistrationLogTableName</h3>

<hr>

<p>Gets the prefixed <code>registration_log</code> table name.</p>
<pre><code class="language-php">    // Assuming a `wp_` table prefix it will return `wp_registration_log`.
    $blogVersionsTable = $I-&gt;grabRegistrationLogTableName();
    $I-&gt;useBlog(23);
    // Assuming a `wp_` table prefix it will return `wp_registration_log`.
    $blogVersionsTable = $I-&gt;grabRegistrationLogTableName();</code></pre>
  

<h3>grabSignupsTableName</h3>

<hr>

<p>Gets the prefixed <code>signups</code> table name.</p>
<pre><code class="language-php">    // Assuming a `wp_` table prefix it will return `wp_signups`.
    $blogVersionsTable = $I-&gt;grabSignupsTableName();
    $I-&gt;useBlog(23);
    // Assuming a `wp_` table prefix it will return `wp_signups`.
    $blogVersionsTable = $I-&gt;grabSignupsTableName();</code></pre>
  

<h3>grabSiteMetaTableName</h3>

<hr>

<p>Gets the prefixed <code>sitemeta</code> table name.</p>
<pre><code class="language-php">    // Assuming a `wp_` table prefix it will return `wp_sitemeta`.
    $blogVersionsTable = $I-&gt;grabSiteMetaTableName();
    $I-&gt;useBlog(23);
    // Assuming a `wp_` table prefix it will return `wp_sitemeta`.
    $blogVersionsTable = $I-&gt;grabSiteMetaTableName();</code></pre>
  

<h3>grabSiteOptionFromDatabase</h3>

<hr>

<p>Gets a site option from the database.</p>
<pre><code class="language-php">    $fooCountOptionId = $I-&gt;haveSiteOptionInDatabase('foo_count','23');</code></pre>
<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$key</strong> - The name of the option to read from the database.</li></ul>
  

<h3>grabSiteTableName</h3>

<hr>

<p>Gets the prefixed <code>site</code> table name.</p>
<pre><code class="language-php">    // Assuming a `wp_` table prefix it will return `wp_site`.
    $blogVersionsTable = $I-&gt;grabSiteTableName();
    $I-&gt;useBlog(23);
    // Assuming a `wp_` table prefix it will return `wp_site`.
    $blogVersionsTable = $I-&gt;grabSiteTableName();</code></pre>
  

<h3>grabSiteTransientFromDatabase</h3>

<hr>

<p>Gets a site transient from the database.</p>
<pre><code class="language-php">    $I-&gt;grabSiteTransientFromDatabase('total_comments');
    $I-&gt;grabSiteTransientFromDatabase('api_data');</code></pre>
<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$key</strong> - The site transient to fetch the value for, w/o the <code>_site_transient_</code> prefix.</li></ul>
  

<h3>grabSiteUrl</h3>

<hr>

<p>Returns the current site URL as specified in the module configuration.</p>
<pre><code class="language-php">    $shopPath = $I-&gt;grabSiteUrl('/shop');</code></pre>
<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$path</strong> - A path that should be appended to the site URL.</li></ul>
  

<h3>grabTablePrefix</h3>

<hr>

<p>Returns the table prefix, namespaced for secondary blogs if selected.</p>
<pre><code class="language-php">    // Assuming a table prefix of `wp_` it will return `wp_`;
    $tablePrefix = $I-&gt;grabTablePrefix();
    $I-&gt;useBlog(23);
    // Assuming a table prefix of `wp_` it will return `wp_23_`;
    $tablePrefix = $I-&gt;grabTablePrefix();</code></pre>
  

<h3>grabTermIdFromDatabase</h3>

<hr>

<p>Gets a term ID from the database. Looks up the prefixed <code>terms</code> table, e.g. <code>wp_terms</code>.</p>
<pre><code class="language-php">    // Return the 'fiction' term 'term_id'.
    $termId = $I-&gt;grabTermIdFromDatabase(['name' =&gt; 'fiction']);
    // Get a term ID by more stringent criteria.
    $termId = $I-&gt;grabTermIdFromDatabase(['name' =&gt; 'fiction', 'slug' =&gt; 'genre--fiction']);
    // Return the 'term_id' of the first term for a group.
    $termId = $I-&gt;grabTermIdFromDatabase(['term_group' =&gt; 23]);</code></pre>
<h4>Parameters</h4>
<ul>
<li><code>array</code> <strong>$criteria</strong> - An array of search criteria.</li></ul>
  

<h3>grabTermMetaTableName</h3>

<hr>

<p>Gets the terms meta table prefixed name.</p>
<pre><code class="language-php">    // Returns 'wp_termmeta'.
    $I-&gt;grabTermMetaTableName();
    // Returns 'wp_23_termmeta'.
    $I-&gt;useBlog(23);
    $I-&gt;grabTermMetaTableName();</code></pre>
  

<h3>grabTermRelationshipsTableName</h3>

<hr>

<p>Gets the prefixed term relationships table name, e.g. <code>wp_term_relationships</code>.</p>
<pre><code class="language-php">    $I-&gt;grabTermRelationshipsTableName();</code></pre>
  

<h3>grabTermTaxonomyIdFromDatabase</h3>

<hr>

<p>Gets a <code>term_taxonomy_id</code> from the database. Looks up the prefixed <code>terms_relationships</code> table, e.g. <code>wp_term_relationships</code>.</p>
<pre><code class="language-php">    // Get the `term_taxonomy_id` for a term and a taxonomy.
    $I-&gt;grabTermTaxonomyIdFromDatabase(['term_id' =&gt; $fictionId, 'taxonomy' =&gt; 'genre']);
    // Get the `term_taxonomy_id` for the first term with a count of 23.
    $I-&gt;grabTermTaxonomyIdFromDatabase(['count' =&gt; 23]);</code></pre>
<h4>Parameters</h4>
<ul>
<li><code>array</code> <strong>$criteria</strong> - An array of search criteria.</li></ul>
  

<h3>grabTermTaxonomyTableName</h3>

<hr>

<p>Gets the prefixed term and taxonomy table name, e.g. <code>wp_term_taxonomy</code>.</p>
<pre><code class="language-php">    // Returns 'wp_term_taxonomy'.
    $I-&gt;grabTermTaxonomyTableName();
    // Returns 'wp_23_term_taxonomy'.
    $I-&gt;useBlog(23);
    $I-&gt;grabTermTaxonomyTableName();</code></pre>
  

<h3>grabTermsTableName</h3>

<hr>

<p>Gets the prefixed terms table name, e.g. <code>wp_terms</code>.</p>
<pre><code class="language-php">    // Returns 'wp_terms'.
    $I-&gt;grabTermsTableName();
    // Returns 'wp_23_terms'.
    $I-&gt;useBlog(23);
    $I-&gt;grabTermsTableName();</code></pre>
  

<h3>grabUserIdFromDatabase</h3>

<hr>

<p>Gets the a user ID from the database using the user login.</p>
<pre><code class="language-php">    $userId = $I-&gt;grabUserIdFromDatabase('luca');</code></pre>
<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$userLogin</strong> - The user login name.</li></ul>
  

<h3>grabUserMetaFromDatabase</h3>

<hr>

<p>Gets a user meta from the database.</p>
<pre><code class="language-php">    // Returns a user 'karma' value.
    $I-&gt;grabUserMetaFromDatabase($userId, 'karma');
    // Returns an array, the unserialized version of the value stored in the database.
    $I-&gt;grabUserMetaFromDatabase($userId, 'api_data');</code></pre>
<h4>Parameters</h4>
<ul>
<li><code>int</code> <strong>$userId</strong> - The ID of th user to get the meta for.</li>
<li><code>string</code> <strong>$meta_key</strong> - The meta key to fetch the value for.</li></ul>
  

<h3>grabUsermetaTableName</h3>

<hr>

<p>Returns the prefixed users meta table name.</p>
<pre><code class="language-php">    // Given a `wp_` table prefix returns `wp_usermeta`.
    $usermetaTable = $I-&gt;grabUsermetaTableName();
    // Given a `wp_` table prefix returns `wp_usermeta`.
    $I-&gt;useBlog(23);
    $usermetaTable = $I-&gt;grabUsermetaTableName();</code></pre>
  

<h3>grabUsersTableName</h3>

<hr>

<p>Returns the prefixed users table name.</p>
<pre><code class="language-php">    // Given a `wp_` table prefix returns `wp_users`.
    $usersTable = $I-&gt;grabUsersTableName();
    // Given a `wp_` table prefix returns `wp_users`.
    $I-&gt;useBlog(23);
    $usersTable = $I-&gt;grabUsersTableName();</code></pre>
  

<h3>haveAttachmentInDatabase</h3>

<hr>

<p>Creates the database entries representing an attachment and moves the attachment file to the right location.</p>
<pre><code class="language-php">    $file = codecept_data_dir('images/test.png');
    $attachmentId = $I-&gt;haveAttachmentInDatabase($file);
    $image = codecept_data_dir('images/test-2.png');
    $lastWeekAttachment = $I-&gt;haveAttachmentInDatabase($image, '-1 week');</code></pre>
<pre><code>    Requires the WPFilesystem module.
                              should be used to build the "year/time" uploads sub-folder structure.
                              image sizes created by default.</code></pre>
<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$file</strong> - The absolute path to the attachment file.</li>
<li><code>string/string/int</code> <strong>$date</strong> - Either a string supported by the <code>strtotime</code> function or a UNIX timestamp that</li>
<li><code>array</code> <strong>$overrides</strong> - An associative array of values overriding the default ones.</li>
<li><code>array</code> <strong>$imageSizes</strong> - An associative array in the format [ <size> =&gt; [<width>,<height>]] to override the</li></ul>
  

<h3>haveBlogInDatabase</h3>

<hr>

<p>Inserts a blog in the <code>blogs</code> table.</p>
<pre><code class="language-php">    // Create the `test` subdomain blog.
    $blogId = $I-&gt;haveBlogInDatabase('test', ['administrator' =&gt; $userId]);
    // Create the `/test` subfolder blog.
    $blogId = $I-&gt;haveBlogInDatabase('test', ['administrator' =&gt; $userId], false);</code></pre>
<pre><code>                                 or subfolder (`true`)</code></pre>
<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$domainOrPath</strong> - The subdomain or the path to the be used for the blog.</li>
<li><code>array</code> <strong>$overrides</strong> - An array of values to override the defaults.</li>
<li><code>bool</code> <strong>$subdomain</strong> - Whether the new blog should be created as a subdomain (<code>true</code>)</li></ul>
  

<h3>haveCommentInDatabase</h3>

<hr>

<p>Inserts a comment in the database.</p>
<pre><code class="language-php">    $I-&gt;haveCommentInDatabase($postId, ['comment_content' =&gt; 'Test Comment', 'comment_karma' =&gt; 23]);</code></pre>
<h4>Parameters</h4>
<ul>
<li><code>int</code> <strong>$comment_post_ID</strong> - The id of the post the comment refers to.</li>
<li><code>array</code> <strong>$data</strong> - The comment data overriding default and random generated values.</li></ul>
  

<h3>haveCommentMetaInDatabase</h3>

<hr>

<p>Inserts a comment meta field in the database. Array and object meta values will be serialized.</p>
<pre><code class="language-php">    $I-&gt;haveCommentMetaInDatabase($commentId, 'api_ID', 23);
    // The value will be serialized.
    $apiData = ['ID' =&gt; 23, 'user' =&gt; 89, 'origin' =&gt; 'twitter'];
    $I-&gt;haveCommentMetaInDatabase($commentId, 'api_data', $apiData);</code></pre>
<h4>Parameters</h4>
<ul>
<li><code>int</code> <strong>$comment_id</strong> - The ID of the comment to insert the meta for.</li>
<li><code>string</code> <strong>$meta_key</strong> - The key of the comment meta to insert.</li>
<li><code>mixed</code> <strong>$meta_value</strong> - The value of the meta to insert, if serializable it will be serialized.</li></ul>
  

<h3>haveLinkInDatabase</h3>

<hr>

<p>Inserts a link in the database.</p>
<pre><code class="language-php">    $linkId = $I-&gt;haveLinkInDatabase(['link_url' =&gt; 'http://example.org']);</code></pre>
<h4>Parameters</h4>
<ul>
<li><code>array</code> <strong>$overrides</strong> - The data to insert.</li></ul>
  

<h3>haveManyBlogsInDatabase</h3>

<hr>

<p>Inserts many blogs in the database.</p>
<pre><code class="language-php">    $blogIds = $I-&gt;haveManyBlogsInDatabase(3, ['domain' =&gt;'test-{{n}}']);
    foreach($blogIds as $blogId){
         $I-&gt;useBlog($blogId);
         $I-&gt;haveManuPostsInDatabase(3);
    }</code></pre>
<h4>Parameters</h4>
<ul>
<li><code>int</code> <strong>$count</strong> - The number of blogs to create.</li>
<li><code>array</code> <strong>$overrides</strong> - An array of values to override the default ones; <code>{{n}}</code> will be replaced by the count.</li>
<li><code>bool</code> <strong>$subdomain</strong> - Whether the new blogs should be created as a subdomain or subfolder.</li></ul>
  

<h3>haveManyCommentsInDatabase</h3>

<hr>

<p>Inserts many comments in the database.</p>
<pre><code class="language-php">    // Insert 3 random comments for a post.
    $I-&gt;haveManyCommentsInDatabase(3, $postId);
    // Insert 3 random comments for a post.
    $I-&gt;haveManyCommentsInDatabase(3, $postId, ['comment_content' =&gt; 'Comment {{n}}']);</code></pre>
<h4>Parameters</h4>
<ul>
<li><code>int</code> <strong>$count</strong> - The number of comments to insert.</li>
<li><code>int</code> <strong>$comment_post_ID</strong> - The comment parent post ID.</li>
<li><code>array</code> <strong>$overrides</strong> - An associative array to override the defaults.</li></ul>
  

<h3>haveManyLinksInDatabase</h3>

<hr>

<p>Inserts many links in the database <code>links</code> table.</p>
<pre><code class="language-php">    // Insert 3 randomly generated links in the database.
    $linkIds = $I-&gt;haveManyLinksInDatabase(3);
    // Inserts links in the database replacing the `n` placeholder.
    $linkIds = $I-&gt;haveManyLinksInDatabase(3, ['link_url' =&gt; 'http://example.org/test-{{n}}']);</code></pre>
<h4>Parameters</h4>
<ul>
<li><code>int</code> <strong>$count</strong> - The number of links to insert.</li>
<li><code>array/array/null/array</code> <strong>$overrides</strong> - Overrides for the default arguments.</li></ul>
  

<h3>haveManyPostsInDatabase</h3>

<hr>

<p>Inserts many posts in the database returning their IDs. An array of values to override the defaults. The <code>{{n}}</code> placeholder can be used to have the post count inserted in its place; e.g. <code>Post Title - {{n}}</code> will be set to <code>Post Title - 0</code> for the first post, <code>Post Title - 1</code> for the second one and so on. The same applies to meta values as well.</p>
<pre><code class="language-php">    // Insert 3 random posts.
    $I-&gt;haveManyPostsInDatabase(3);
    // Insert 3 posts with generated titles.
    $I-&gt;haveManyPostsInDatabase(3, ['post_title' =&gt; 'Test post {{n}}']);</code></pre>
<h4>Parameters</h4>
<ul>
<li><code>int</code> <strong>$count</strong> - The number of posts to insert.</li>
<li><code>array</code> <strong>$overrides</strong></li></ul>
  

<h3>haveManyTermsInDatabase</h3>

<hr>

<p>Inserts many terms in the database.</p>
<pre><code class="language-php">    $terms = $I-&gt;haveManyTermsInDatabase(3, 'genre-{{n}}', 'genre');
    $termIds = array_column($terms, 0);
    $termTaxonomyIds = array_column($terms, 1);</code></pre>
<h4>Parameters</h4>
<ul>
<li><code>int</code> <strong>$count</strong> - The number of terms to insert.</li>
<li><code>string</code> <strong>$name</strong> - The term name template, can include the <code>{{n}}</code> placeholder.</li>
<li><code>string</code> <strong>$taxonomy</strong> - The taxonomy to insert the terms for.</li>
<li><code>array</code> <strong>$overrides</strong> - An associative array of default overrides.</li></ul>
  

<h3>haveManyUsersInDatabase</h3>

<hr>

<p>Inserts many users in the database.</p>
<pre><code class="language-php">    $subscribers = $I-&gt;haveManyUsersInDatabase(5, 'user-{{n}}');
    $editors = $I-&gt;haveManyUsersInDatabase(
         5,
         'user-{{n}}',
         'editor',
         ['user_email' =&gt; 'user-{{n}}@example.org']
    );</code></pre>
<h4>Parameters</h4>
<ul>
<li><code>int</code> <strong>$count</strong> - The number of users to insert.</li>
<li><code>string</code> <strong>$user_login</strong> - The user login name.</li>
<li><code>string</code> <strong>$role</strong> - The user role.</li>
<li><code>array</code> <strong>$overrides</strong> - An array of values to override the default ones.</li></ul>
  

<h3>haveMenuInDatabase</h3>

<hr>

<p>Creates and adds a menu to a theme location in the database.</p>
<pre><code class="language-php">    list($termId, $termTaxId) = $I-&gt;haveMenuInDatabase('test', 'sidebar');</code></pre>
<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$slug</strong> - The menu slug.</li>
<li><code>string</code> <strong>$location</strong> - The theme menu location the menu will be assigned to.</li>
<li><code>array</code> <strong>$overrides</strong> - An array of values to override the defaults.</li></ul>
  

<h3>haveMenuItemInDatabase</h3>

<hr>

<p>Adds a menu element to a menu for the current theme.</p>
<pre><code class="language-php">    $I-&gt;haveMenuInDatabase('test', 'sidebar');
    $I-&gt;haveMenuItemInDatabase('test', 'Test one', 0);
    $I-&gt;haveMenuItemInDatabase('test', 'Test two', 1);</code></pre>
<pre><code>                             meta.</code></pre>
<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$menuSlug</strong> - The menu slug the item should be added to.</li>
<li><code>string</code> <strong>$title</strong> - The menu item title.</li>
<li><code>int/null</code> <strong>$menuOrder</strong> - An optional menu order, <code>1</code> based.</li>
<li><code>array/array/null/array</code> <strong>$meta</strong> - An associative array that will be prefixed with <code>_menu_item_</code> for the item post</li></ul>
  

<h3>haveOptionInDatabase</h3>

<hr>

<p>Inserts an option in the database.</p>
<pre><code class="language-php">    $I-&gt;haveOptionInDatabase('posts_per_page', 23);
    $I-&gt;haveOptionInDatabase('my_plugin_options', ['key_one' =&gt; 'value_one', 'key_two' =&gt; 89]);</code></pre>
<pre><code>    If the option value is an object or an array then the value will be serialized.</code></pre>
<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$option_name</strong> - The option name.</li>
<li><code>mixed</code> <strong>$option_value</strong> - The option value; if an array or object it will be serialized.</li>
<li><code>string</code> <strong>$autoload</strong> - Wether the option should be autoloaded by WordPress or not.</li></ul>
  

<h3>havePageInDatabase</h3>

<hr>

<p>Inserts a page in the database.</p>
<pre><code class="language-php">    // Creates a test page in the database with random values.
    $randomPageId = $I-&gt;havePageInDatabase();
    // Creates a test page in the database defining its title.
    $testPageId = $I-&gt;havePageInDatabase(['post_title' =&gt; 'Test page']);</code></pre>
<h4>Parameters</h4>
<ul>
<li><code>array</code> <strong>$overrides</strong> - An array of values to override the default ones.</li></ul>
  

<h3>havePostInDatabase</h3>

<hr>

<p>Inserts a post in the database.</p>
<pre><code class="language-php">    // Insert a post with random values in the database.
    $randomPostId = $I-&gt;havePostInDatabase();
    // Insert a post with specific values in the database.
    $I-&gt;havePostInDatabase([
            'post_type' =&gt; 'book',
            'post_title' =&gt; 'Alice in Wonderland',
            'meta_input' =&gt; [
                 'readers_count' =&gt; 23
             ],
            'tax_input' =&gt; [
                 ['genre' =&gt; 'fiction']
             ]
    ]);</code></pre>
<h4>Parameters</h4>
<ul>
<li><code>array</code> <strong>$data</strong> - An associative array of post data to override default and random generated values.</li></ul>
  

<h3>havePostmetaInDatabase</h3>

<hr>

<p>Adds one or more meta key and value couples in the database for a post.</p>
<pre><code class="language-php">    // Set the post-meta for a post.
    $I-&gt;havePostmetaInDatabase($postId, 'karma', 23);
    // Set an array post-meta for a post, it will be serialized in the db.
    $I-&gt;havePostmetaInDatabase($postId, 'data', ['one', 'two']);
    // Use a loop to insert one meta per row.
    foreach( ['one', 'two'] as $value){
         $I-&gt;havePostmetaInDatabase($postId, 'data', $value);
    }</code></pre>
<h4>Parameters</h4>
<ul>
<li><code>int</code> <strong>$postId</strong> - The post ID.</li>
<li><code>string</code> <strong>$meta_key</strong> - The meta key.</li>
<li><code>mixed</code> <strong>$meta_value</strong> - The value to insert in the database, objects and arrays will be serialized.</li></ul>
  

<h3>haveSiteOptionInDatabase</h3>

<hr>

<p>Inserts a site option in the database. If the value is an array or an object then the value will be serialized.</p>
<pre><code class="language-php">    $fooCountOptionId = $I-&gt;haveSiteOptionInDatabase('foo_count','23');</code></pre>
<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$key</strong> - The name of the option to insert.</li>
<li><code>mixed</code> <strong>$value</strong> - The value ot insert for the option.</li></ul>
  

<h3>haveSiteTransientInDatabase</h3>

<hr>

<p>Inserts a site transient in the database. If the value is an array or an object then the value will be serialized.</p>
<pre><code class="language-php">    $I-&gt;haveSiteTransientInDatabase('total_comments_count', 23);
    // This value will be serialized.
    $I-&gt;haveSiteTransientInDatabase('api_data', ['user' =&gt; 'luca', 'token' =&gt; '11ae3ijns-j83']);</code></pre>
<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$key</strong> - The key of the site transient to insert, w/o the <code>_site_transient_</code> prefix.</li>
<li><code>mixed</code> <strong>$value</strong> - The value to insert; if serializable the value will be serialized.</li></ul>
  

<h3>haveTermInDatabase</h3>

<hr>

<p>Inserts a term in the database.</p>
<pre><code class="language-php">    // Insert a random 'genre' term in the database.
    $I-&gt;haveTermInDatabase('non-fiction', 'genre');
    // Insert a term in the database with term meta.
    $I-&gt;haveTermInDatabase('fiction', 'genre', [
         'slug' =&gt; 'genre--fiction',
         'meta' =&gt; [
            'readers_count' =&gt; 23
         ]
    ]);</code></pre>
<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$name</strong> - The term name, e.g. &quot;Fuzzy&quot;.</li>
<li><code>string</code> <strong>$taxonomy</strong> - The term taxonomy</li>
<li><code>array</code> <strong>$overrides</strong> - An array of values to override the default ones.</li></ul>
  

<h3>haveTermMetaInDatabase</h3>

<hr>

<p>Inserts a term meta row in the database. Objects and array meta values will be serialized.</p>
<pre><code class="language-php">    $I-&gt;haveTermMetaInDatabase($fictionId, 'readers_count', 23);
    // Insert some meta that will be serialized.
    $I-&gt;haveTermMetaInDatabase($fictionId, 'flags', [3, 4, 89]);
    // Use a loop to insert one meta per row.
    foreach([3, 4, 89] as $value) {
         $I-&gt;haveTermMetaInDatabase($fictionId, 'flag', $value);
    }</code></pre>
<h4>Parameters</h4>
<ul>
<li><code>int</code> <strong>$term_id</strong> - The ID of the term to insert the meta for.</li>
<li><code>string</code> <strong>$meta_key</strong> - The key of the meta to insert.</li>
<li><code>mixed</code> <strong>$meta_value</strong> - The value of the meta to insert, if serializable it will be serialized.</li></ul>
  

<h3>haveTermRelationshipInDatabase</h3>

<hr>

<p>Creates a term relationship in the database. No check about the consistency of the insertion is made. E.g. a post could be assigned a term from a taxonomy that's not registered for that post type.</p>
<pre><code class="language-php">    // Assign the `fiction` term to a book.
    $I-&gt;haveTermRelationshipInDatabase($bookId, $fictionId);</code></pre>
<h4>Parameters</h4>
<ul>
<li><code>int</code> <strong>$object_id</strong> - A post ID, a user ID or anything that can be assigned a taxonomy term.</li>
<li><code>int</code> <strong>$term_taxonomy_id</strong> - The <code>term_taxonomy_id</code> of the term and taxonomy to create a relation with.</li>
<li><code>int</code> <strong>$term_order</strong> - Defaults to <code>0</code>.</li></ul>
  

<h3>haveTransientInDatabase</h3>

<hr>

<p>Inserts a transient in the database. If the value is an array or an object then the value will be serialized. Since the transients are set in the context of tests it's not possible to set an expiration directly.</p>
<pre><code class="language-php">    // Store an array in the `tweets` transient.
    $I-&gt;haveTransientInDatabase('tweets', $tweets);</code></pre>
<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$transient</strong> - The transient name.</li>
<li><code>mixed</code> <strong>$value</strong> - The transient value.</li></ul>
  

<h3>haveUserCapabilitiesInDatabase</h3>

<hr>

<p>Sets a user capabilities in the database.</p>
<pre><code class="language-php">    $blogId = $this-&gt;haveBlogInDatabase('test');
    $editor = $I-&gt;haveUserInDatabase('luca', 'editor');
    $capsIds = $I-&gt;haveUserCapabilitiesInDatabase($editor, [$blogId =&gt; 'editor']);</code></pre>
<pre><code>                          for a multisite installation (e.g. `[1 =&gt; 'administrator`, 2 =&gt; 'subscriber']`).</code></pre>
<h4>Parameters</h4>
<ul>
<li><code>int</code> <strong>$userId</strong> - The ID of the user to set the capabilities of.</li>
<li><code>string/array</code> <strong>$role</strong> - Either a role string (e.g. <code>administrator</code>) or an associative array of blog IDs/roles</li></ul>
  

<h3>haveUserInDatabase</h3>

<hr>

<p>Inserts a user and its meta in the database.</p>
<pre><code class="language-php">    $userId = $I-&gt;haveUserInDatabase('luca', 'editor', ['user_email' =&gt; 'luca@example.org']);
    $subscriberId = $I-&gt;haveUserInDatabase('test');
    $userWithMeta = $I-&gt;haveUserInDatabase('luca', 'editor', [
        'user_email' =&gt; 'luca@example.org'
        'meta' =&gt; ['a meta_key' =&gt; 'a_meta_value']
    ]);</code></pre>
<pre><code>                           and "usermeta" table.</code></pre>
<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$user_login</strong> - The user login name.</li>
<li><code>string</code> <strong>$role</strong> - The user role slug, e.g. &quot;administrator&quot;; defaults to &quot;subscriber&quot;.</li>
<li><code>array</code> <strong>$overrides</strong> - An associative array of column names and values overridind defaults in the &quot;users&quot;</li></ul>
  

<h3>haveUserLevelsInDatabase</h3>

<hr>

<p>Sets the user access level meta in the database for a user.</p>
<pre><code class="language-php">    $userId = $I-&gt;haveUserInDatabase('luca', 'editor');
    $moreThanAnEditorLessThanAnAdmin = 8;
    $I-&gt;haveUserLevelsInDatabase($userId, $moreThanAnEditorLessThanAnAdmin);</code></pre>
<pre><code>                          multisite installation (e.g. `[1 =&gt; 'administrator`, 2 =&gt; 'subscriber']`).</code></pre>
<h4>Parameters</h4>
<ul>
<li><code>int</code> <strong>$userId</strong> - The ID of the user to set the level for.</li>
<li><code>string/array</code> <strong>$role</strong> - Either a role string (e.g. <code>administrator</code>) or an array of blog IDs/roles for a</li></ul>
  

<h3>haveUserMetaInDatabase</h3>

<hr>

<p>Sets a user meta in the database.</p>
<pre><code class="language-php">    $userId = $I-&gt;haveUserInDatabase('luca', 'editor');
    $I-&gt;haveUserMetaInDatabase($userId, 'karma', 23);</code></pre>
<pre><code>                          values will trigger the insertion of multiple rows.</code></pre>
<h4>Parameters</h4>
<ul>
<li><code>int</code> <strong>$userId</strong> - The user ID.</li>
<li><code>string</code> <strong>$meta_key</strong> - The meta key to set the value for.</li>
<li><code>mixed</code> <strong>$meta_value</strong> - Either a single value or an array of values; objects will be serialized while array of</li></ul>
  

<h3>importSqlDumpFile</h3>

<hr>

<p>Import the SQL dump file if populate is enabled.</p>
<pre><code class="language-php">    // Import a dump file passing the absolute path.
    $I-&gt;importSqlDumpFile(codecept_data_dir('dumps/start.sql'));</code></pre>
<pre><code>    Specifying a dump file that file will be imported.</code></pre>
<h4>Parameters</h4>
<ul>
<li><code>null/string</code> <strong>$dumpFile</strong> - The dump file that should be imported in place of the default one.</li></ul>
  

<h3>seeAttachmentInDatabase</h3>

<hr>

<p>Checks for an attachment in the database.</p>
<pre><code class="language-php">    $url = 'https://example.org/images/foo.png';
    $I-&gt;seeAttachmentInDatabase(['guid' =&gt; $url]);</code></pre>
<h4>Parameters</h4>
<ul>
<li><code>array</code> <strong>$criteria</strong> - An array of search criteria.</li></ul>
  

<h3>seeBlogInDatabase</h3>

<hr>

<p>Checks for a blog in the <code>blogs</code> table.</p>
<pre><code class="language-php">    // Search for a blog by `blog_id`.
    $I-&gt;seeBlogInDatabase(['blog_id' =&gt; 23]);
    // Search for all blogs on a path.
    $I-&gt;seeBlogInDatabase(['path' =&gt; '/sub-path/']);</code></pre>
<h4>Parameters</h4>
<ul>
<li><code>array</code> <strong>$criteria</strong> - An array of search criteria.</li></ul>
  

<h3>seeCommentInDatabase</h3>

<hr>

<p>Checks for a comment in the database. Will look up the &quot;comments&quot; table.</p>
<pre><code class="language-php">    $I-&gt;seeCommentInDatabase(['comment_ID' =&gt; 23]);</code></pre>
<h4>Parameters</h4>
<ul>
<li><code>array</code> <strong>$criteria</strong> - An array of search criteria.</li></ul>
  

<h3>seeCommentMetaInDatabase</h3>

<hr>

<p>Checks that a comment meta value is in the database. Will look up the &quot;commentmeta&quot; table.</p>
<pre><code class="language-php">    // Assert a specifid meta for a comment exists.
    $I-&gt;seeCommentMetaInDatabase(['comment_ID' =&gt; $commentId, 'meta_key' =&gt; 'karma', 'meta_value' =&gt; 23]);
    // Assert the comment has at least one meta set.
    $I-&gt;seeCommentMetaInDatabase(['comment_ID' =&gt; $commentId]);</code></pre>
<h4>Parameters</h4>
<ul>
<li><code>array</code> <strong>$criteria</strong> - An array of search criteria.</li></ul>
  

<h3>seeLinkInDatabase</h3>

<hr>

<p>Checks for a link in the <code>links</code> table of the database.</p>
<pre><code class="language-php">    // Asserts a link exists by name.
    $I-&gt;seeLinkInDatabase(['link_name' =&gt; 'my-link']);
    // Asserts at least one link exists for the user.
    $I-&gt;seeLinkInDatabase(['link_owner' =&gt; $userId]);</code></pre>
<h4>Parameters</h4>
<ul>
<li><code>array</code> <strong>$criteria</strong> - An array of search criteria.</li></ul>
  

<h3>seeOptionInDatabase</h3>

<hr>

<p>Checks if an option is in the database for the current blog. If checking for an array or an object then the serialized version will be checked for.</p>
<pre><code class="language-php">    // Checks an option is in the database.
    $I-&gt;seeOptionInDatabase('tables_version');
    // Checks an option is in the database and has a specific value.
    $I-&gt;seeOptionInDatabase('tables_version', '1.0');</code></pre>
<h4>Parameters</h4>
<ul>
<li><code>array</code> <strong>$criteria</strong> - An array of search criteria.</li></ul>
  

<h3>seePageInDatabase</h3>

<hr>

<p>Checks for a page in the database.</p>
<pre><code class="language-php">    // Asserts a page with an exists in the database.
    $I-&gt;seePageInDatabase(['ID' =&gt; 23]);
    // Asserts a page with a slug and ID exists in the database.
    $I-&gt;seePageInDatabase(['post_title' =&gt; 'Test Page', 'ID' =&gt; 23]);</code></pre>
<h4>Parameters</h4>
<ul>
<li><code>array</code> <strong>$criteria</strong> - An array of search criteria.</li></ul>
  

<h3>seePostInDatabase</h3>

<hr>

<p>Checks for a post in the database.</p>
<pre><code class="language-php">    // Assert a post exists in the database.
    $I-&gt;seePostInDatabase(['ID' =&gt; 23]);
    // Assert a post with a slug and ID exists in the database.
    $I-&gt;seePostInDatabase(['post_content' =&gt; 'test content', 'ID' =&gt; 23]);</code></pre>
<h4>Parameters</h4>
<ul>
<li><code>array</code> <strong>$criteria</strong> - An array of search criteria.</li></ul>
  

<h3>seePostMetaInDatabase</h3>

<hr>

<p>Checks for a post meta value in the database for the current blog. If the <code>meta_value</code> is an object or an array then the check will be made for serialized values.</p>
<pre><code class="language-php">    $postId = $I-&gt;havePostInDatabase(['meta_input' =&gt; ['foo' =&gt; 'bar']];
    $I-&gt;seePostMetaInDatabase(['post_id' =&gt; '$postId', 'meta_key' =&gt; 'foo']);</code></pre>
<h4>Parameters</h4>
<ul>
<li><code>array</code> <strong>$criteria</strong> - An array of search criteria.</li></ul>
  

<h3>seePostWithTermInDatabase</h3>

<hr>

<p>Checks that a post to term relation exists in the database. The method will check the &quot;term_relationships&quot; table.</p>
<pre><code class="language-php">    $fiction = $I-&gt;haveTermInDatabase('fiction', 'genre');
    $postId = $I-&gt;havePostInDatabase(['tax_input' =&gt; ['genre' =&gt; ['fiction']]]);
    $I-&gt;seePostWithTermInDatabase($postId, $fiction['term_taxonomy_id']);</code></pre>
<pre><code>                                        passed this parameter will be interpreted as a `term_id`, else as a
                                        `term_taxonomy_id`.
                                        the
                                        term order.
                                        to build a `taxonomy_term_id` from the `term_id`.</code></pre>
<h4>Parameters</h4>
<ul>
<li><code>int</code> <strong>$post_id</strong> - The post ID.</li>
<li><code>int</code> <strong>$term_taxonomy_id</strong> - The term <code>term_id</code> or <code>term_taxonomy_id</code>; if the <code>$taxonomy</code> argument is</li>
<li><code>int/null</code> <strong>$term_order</strong> - The order the term applies to the post, defaults to <code>null</code> to not use</li>
<li><code>string/null</code> <strong>$taxonomy</strong> - The taxonomy the <code>term_id</code> is for; if passed this parameter will be used</li></ul>
  

<h3>seeSiteOptionInDatabase</h3>

<hr>

<p>Checks that a site option is in the database.</p>
<pre><code class="language-php">    // Check that the option is set in the database.
    $I-&gt;seeSiteOptionInDatabase('foo_count');
    // Check that the option is set and has a specific value.
    $I-&gt;seeSiteOptionInDatabase('foo_count', 23);</code></pre>
<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$key</strong> - The name of the otpion to check.</li>
<li><code>mixed/null</code> <strong>$value</strong> - If set the assertion will also check the option value.</li></ul>
  

<h3>seeSiteSiteTransientInDatabase</h3>

<hr>

<p>Checks that a site option is in the database.</p>
<pre><code class="language-php">    // Check a transient exists.
    $I-&gt;seeSiteSiteTransientInDatabase('total_counts');
    // Check a transient exists and has a specific value.
    $I-&gt;seeSiteSiteTransientInDatabase('total_counts', 23);</code></pre>
<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$key</strong> - The name of the transient to check for, w/o the <code>_site_transient_</code> prefix.</li>
<li><code>mixed/null</code> <strong>$value</strong> - If provided then the assertion will include the value.</li></ul>
  

<h3>seeTableInDatabase</h3>

<hr>

<p>Checks that a table is in the database.</p>
<pre><code class="language-php">    $options = $I-&gt;grabPrefixedTableNameFor('options');
    $I-&gt;seeTableInDatabase($options);</code></pre>
<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$table</strong> - The full table name, including the table prefix.</li></ul>
  

<h3>seeTermInDatabase</h3>

<hr>

<p>Checks for a term in the database. Looks up the <code>terms</code> and <code>term_taxonomy</code> prefixed tables.</p>
<pre><code class="language-php">    $I-&gt;seeTermInDatabase(['slug' =&gt; 'genre--fiction']);
    $I-&gt;seeTermInDatabase(['name' =&gt; 'Fiction', 'slug' =&gt; 'genre--fiction']);</code></pre>
<pre><code>                       `term_taxonomy` tables.</code></pre>
<h4>Parameters</h4>
<ul>
<li><code>array</code> <strong>$criteria</strong> - An array of criteria to search for the term, can be columns from the <code>terms</code> and the</li></ul>
  

<h3>seeTermMetaInDatabase</h3>

<hr>

<p>Checks for a term meta in the database.</p>
<pre><code class="language-php">    list($termId, $termTaxonomyId) = $I-&gt;haveTermInDatabase('fiction', 'genre');
    $I-&gt;haveTermMetaInDatabase($termId, 'rating', 4);
    $I-&gt;seeTermMetaInDatabase(['term_id' =&gt; $termId,'meta_key' =&gt; 'rating', 'meta_value' =&gt; 4]);</code></pre>
<h4>Parameters</h4>
<ul>
<li><code>array</code> <strong>$criteria</strong> - An array of search criteria.</li></ul>
  

<h3>seeTermRelationshipInDatabase</h3>

<hr>

<p>Checks for a term relationship in the database.</p>
<pre><code class="language-php">    $postId = $I-&gt;havePostInDatabase(['tax_input' =&gt; ['category' =&gt; 'one']]);
    $I-&gt;seeTermRelationshipInDatabase(['object_id' =&gt; $postId, 'term_taxonomy_id' =&gt; $oneTermTaxId]);</code></pre>
<h4>Parameters</h4>
<ul>
<li><code>array</code> <strong>$criteria</strong> - An array of search criteria.</li></ul>
  

<h3>seeTermTaxonomyInDatabase</h3>

<hr>

<p>Checks for a taxonomy taxonomy in the database.</p>
<pre><code class="language-php">    list($termId, $termTaxonomyId) = $I-&gt;haveTermInDatabase('fiction', 'genre');
    $I-&gt;seeTermTaxonomyInDatabase(['term_id' =&gt; $termId, 'taxonomy' =&gt; 'genre']);</code></pre>
<h4>Parameters</h4>
<ul>
<li><code>array</code> <strong>$criteria</strong> - An array of search criteria.</li></ul>
  

<h3>seeUserInDatabase</h3>

<hr>

<p>Checks that a user is in the database. The method will check the &quot;users&quot; table.</p>
<pre><code class="language-php">    $I-&gt;seeUserInDatabase([
        "user_email" =&gt; "test@example.org",
        "user_login" =&gt; "login name"
    ])</code></pre>
<h4>Parameters</h4>
<ul>
<li><code>array</code> <strong>$criteria</strong> - An array of search criteria.</li></ul>
  

<h3>seeUserMetaInDatabase</h3>

<hr>

<p>Checks for a user meta value in the database.</p>
<pre><code class="language-php">    $I-&gt;seeUserMetaInDatabase(['user_id' =&gt; 23, 'meta_key' =&gt; 'karma']);</code></pre>
<h4>Parameters</h4>
<ul>
<li><code>array</code> <strong>$criteria</strong> - An array of search criteria.</li></ul>
  

<h3>useBlog</h3>

<hr>

<p>Sets the blog to be used. This has nothing to do with WordPress <code>switch_to_blog</code> function, this code will affect the table prefixes used.</p>
<pre><code class="language-php">    // Switch to the blog with ID 23.
    $I-&gt;useBlog(23);
    // Switch back to the main blog.
    $I-&gt;useMainBlog();</code></pre>
<h4>Parameters</h4>
<ul>
<li><code>int</code> <strong>$blogId</strong> - The ID of the blog to use.</li></ul>
  

<h3>useMainBlog</h3>

<hr>

<p>Sets the current blog to the main one (<code>blog_id</code> 1).</p>
<pre><code class="language-php">    // Switch to the blog with ID 23.
    $I-&gt;useBlog(23);
    // Switch back to the main blog.
    $I-&gt;useMainBlog();</code></pre>
  

<h3>useTheme</h3>

<hr>

<p>Sets the current theme options.</p>
<pre><code class="language-php">    $I-&gt;useTheme('twentyseventeen');
    $I-&gt;useTheme('child-of-twentyseventeen', 'twentyseventeen');
    $I-&gt;useTheme('acme', 'acme', 'Acme Theme');</code></pre>
<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$stylesheet</strong> - The theme stylesheet slug, e.g. <code>twentysixteen</code>.</li>
<li><code>string/null</code> <strong>$template</strong> - The theme template slug, e.g. <code>twentysixteen</code>, defaults to <code>$stylesheet</code>.</li>
<li><code>string/null</code> <strong>$themeName</strong> - The theme name, e.g. <code>Acme</code>, defaults to the &quot;title&quot; version of <code>$stylesheet</code>.</li></ul>


*This class extends \Codeception\Module\Db*

*This class implements \Codeception\Lib\Interfaces\Db*

<!--/doc-->

# WPDb module
This module should be used in acceptance and functional tests, see [levels of testing for more information](./../levels-of-testing.md).  
This module extends the [Db module](https://codeception.com/docs/modules/Db) adding WordPress-specific configuration parameters and methods.  
The module provides methods to read, write and update the WordPress database **directly**, without relying on WordPress methods, using WordPress functions or triggering WordPress filters.  

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
      enabledr
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
<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$table</strong> - The table name.</li>
<li><code>array</code> <strong>$criteria</strong> - An associative array of the column names and values to use as deletion criteria.</li></ul>
  

<h3>dontHaveLinkInDatabase</h3>

<hr>

<p>Removes a link from the database.</p>
<h4>Parameters</h4>
<ul>
<li><code>array</code> <strong>$criteria</strong> - An array of search criteria.</li></ul>
  

<h3>dontHaveOptionInDatabase</h3>

<hr>

<p>Removes an entry from the options table.</p>
<h4>Parameters</h4>
<ul>
<li><code>mixed</code> <strong>$key</strong></li>
<li><code>null</code> <strong>$value</strong></li></ul>
  

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
<h4>Parameters</h4>
<ul>
<li><code>mixed</code> <strong>$key</strong></li>
<li><code>null</code> <strong>$value</strong></li></ul>
  

<h3>dontHaveSiteTransientInDatabase</h3>

<hr>

<p>Removes a site transient from the database.</p>
<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$key</strong></li></ul>
  

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
<h4>Parameters</h4>
<ul>
<li><code>array</code> <strong>$criteria</strong> - An array of search criteria.</li>
<li><code>bool</code> <strong>$purgeMeta</strong> - Whether the terms meta should be purged along side with the meta or not.</li></ul>
  

<h3>dontHaveTermMetaInDatabase</h3>

<hr>

<p>Removes a term meta from the database.</p>
<h4>Parameters</h4>
<ul>
<li><code>array</code> <strong>$criteria</strong> - An array of search criteria.</li></ul>
  

<h3>dontHaveTermRelationshipInDatabase</h3>

<hr>

<p>Removes an entry from the term_relationships table.</p>
<h4>Parameters</h4>
<ul>
<li><code>array</code> <strong>$criteria</strong> - An array of search criteria.</li></ul>
  

<h3>dontHaveTermTaxonomyInDatabase</h3>

<hr>

<p>Removes an entry from the term_taxonomy table.</p>
<h4>Parameters</h4>
<ul>
<li><code>array</code> <strong>$criteria</strong> - An array of search criteria.</li></ul>
  

<h3>dontHaveTransientInDatabase</h3>

<hr>

<p>Removes a transient from the database.</p>
<h4>Parameters</h4>
<ul>
<li><code>mixed</code> <strong>$transient</strong></li></ul>
  

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
<h4>Parameters</h4>
<ul>
<li><code>array</code> <strong>$criteria</strong></li></ul>
  

<h3>dontSeeCommentMetaInDatabase</h3>

<hr>

<p>Checks that a comment meta value is not in the database. Will look up the &quot;commentmeta&quot; table.</p>
<h4>Parameters</h4>
<ul>
<li><code>array</code> <strong>$criteria</strong></li></ul>
  

<h3>dontSeeLinkInDatabase</h3>

<hr>

<p>Checks that a link is not in the <code>links</code> database table.</p>
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
<h4>Parameters</h4>
<ul>
<li><code>array</code> <strong>$criteria</strong> - An array of search criteria.</li></ul>
  

<h3>dontSeePostInDatabase</h3>

<hr>

<p>Checks that a post is not in the database.</p>
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

<p>Makes sure a term is not in the database. Looks up both the <code>terms</code> table and the <code>term_taxonomy</code> tables. <code>term_taxonomy</code> tables.</p>
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
<h4>Parameters</h4>
<ul>
<li><code>array</code> <strong>$criteria</strong> - An array of search criteria.</li></ul>
  

<h3>dontSeeUserMetaInDatabase</h3>

<hr>

<p>Check that a user meta value is not in the database.</p>
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
<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$table</strong></li>
<li><code>string</code> <strong>$column</strong></li>
<li><code>array</code> <strong>$criteria</strong></li></ul>
  

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

<p>Returns the prefixed comment meta table name. E.g. <code>wp_commentmeta</code>.</p>
  

<h3>grabCommentsTableName</h3>

<hr>

<p>Gets the comments table name.</p>
  

<h3>grabLatestEntryByFromDatabase</h3>

<hr>

<p>Returns the id value of the last table entry.</p>
<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$tableName</strong></li>
<li><code>string</code> <strong>$idColumn</strong></li></ul>
  

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

<p>Gets an option from the database.</p>
<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$option_name</strong></li></ul>
  

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
<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$key</strong></li></ul>
  

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
<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$key</strong></li></ul>
  

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

<p>Gets a term from the database. Looks up the prefixed <code>terms</code> table, e.g. <code>wp_terms</code>.</p>
<h4>Parameters</h4>
<ul>
<li><code>array</code> <strong>$criteria</strong> - An array of search criteria.</li></ul>
  

<h3>grabTermMetaTableName</h3>

<hr>

<p>Gets the terms meta table prefixed name. E.g.: <code>wp_termmeta</code>.</p>
  

<h3>grabTermRelationshipsTableName</h3>

<hr>

<p>Gets the prefixed term relationships table name, e.g. <code>wp_term_relationships</code>.</p>
  

<h3>grabTermTaxonomyIdFromDatabase</h3>

<hr>

<p>Gets a <code>term_taxonomy_id</code> from the database. Looks up the prefixed <code>terms_relationships</code> table, e.g. <code>wp_term_relationships</code>.</p>
<h4>Parameters</h4>
<ul>
<li><code>array</code> <strong>$criteria</strong> - An array of search criteria.</li></ul>
  

<h3>grabTermTaxonomyTableName</h3>

<hr>

<p>Gets the prefixed term and taxonomy table name, e.g. <code>wp_term_taxonomy</code>.</p>
  

<h3>grabTermsTableName</h3>

<hr>

<p>Gets the prefixed terms table name, e.g. <code>wp_terms</code>.</p>
  

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
<h4>Parameters</h4>
<ul>
<li><code>int</code> <strong>$userId</strong></li>
<li><code>string</code> <strong>$meta_key</strong></li></ul>
  

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
<h4>Parameters</h4>
<ul>
<li><code>int</code> <strong>$comment_post_ID</strong> - The id of the post the comment refers to.</li>
<li><code>array</code> <strong>$data</strong> - The comment data overriding default and random generated values.</li></ul>
  

<h3>haveCommentMetaInDatabase</h3>

<hr>

<p>Inserts a comment meta field in the database. Array and object meta values will be serialized.</p>
<h4>Parameters</h4>
<ul>
<li><code>int</code> <strong>$comment_id</strong></li>
<li><code>string</code> <strong>$meta_key</strong></li>
<li><code>mixed</code> <strong>$meta_value</strong></li></ul>
  

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

<p>Inserts an option in the database. If the option value is an object or an array then the value will be serialized.</p>
<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$option_name</strong></li>
<li><code>mixed</code> <strong>$option_value</strong></li>
<li><code>string</code> <strong>$autoload</strong></li></ul>
  

<h3>havePageInDatabase</h3>

<hr>

<p>Inserts a page in the database.</p>
<h4>Parameters</h4>
<ul>
<li><code>array</code> <strong>$overrides</strong> - An array of values to override the default ones.</li></ul>
  

<h3>havePostInDatabase</h3>

<hr>

<p>Inserts a post in the database.</p>
<h4>Parameters</h4>
<ul>
<li><code>array</code> <strong>$data</strong> - An associative array of post data to override default and random generated values.</li></ul>
  

<h3>havePostmetaInDatabase</h3>

<hr>

<p>Adds one or more meta key and value couples in the database for a post.</p>
<h4>Parameters</h4>
<ul>
<li><code>int</code> <strong>$postId</strong> - The post ID.</li>
<li><code>string</code> <strong>$meta_key</strong> - The meta key.</li>
<li><code>mixed</code> <strong>$meta_value</strong> - The value to insert in the database, objects and arrays will be serialized.</li></ul>
  

<h3>haveSiteOptionInDatabase</h3>

<hr>

<p>Inserts a site option in the database. If the value is an array or an object then the value will be serialized.</p>
<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$key</strong></li>
<li><code>mixed</code> <strong>$value</strong></li></ul>
  

<h3>haveSiteTransientInDatabase</h3>

<hr>

<p>Inserts a site transient in the database. If the value is an array or an object then the value will be serialized.</p>
<h4>Parameters</h4>
<ul>
<li><code>mixed</code> <strong>$key</strong></li>
<li><code>mixed</code> <strong>$value</strong></li></ul>
  

<h3>haveTermInDatabase</h3>

<hr>

<p>Inserts a term in the database.</p>
<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$name</strong> - The term name, e.g. &quot;Fuzzy&quot;.</li>
<li><code>string</code> <strong>$taxonomy</strong> - The term taxonomy</li>
<li><code>array</code> <strong>$overrides</strong> - An array of values to override the default ones.</li></ul>
  

<h3>haveTermMetaInDatabase</h3>

<hr>

<p>Inserts a term meta row in the database. Objects and array meta values will be serialized.</p>
<h4>Parameters</h4>
<ul>
<li><code>int</code> <strong>$term_id</strong></li>
<li><code>string</code> <strong>$meta_key</strong></li>
<li><code>mixed</code> <strong>$meta_value</strong></li></ul>
  

<h3>haveTermRelationshipInDatabase</h3>

<hr>

<p>Creates a term relationship in the database. No check about the consistency of the insertion is made. E.g. a post could be assigned a term from a taxonomy that's not registered for that post type.</p>
<h4>Parameters</h4>
<ul>
<li><code>int</code> <strong>$object_id</strong> - A post ID, a user ID or anything that can be assigned a taxonomy term.</li>
<li><code>int</code> <strong>$term_taxonomy_id</strong></li>
<li><code>int</code> <strong>$term_order</strong> - Defaults to <code>0</code>.</li></ul>
  

<h3>haveTransientInDatabase</h3>

<hr>

<p>Inserts a transient in the database. If the value is an array or an object then the value will be serialized.</p>
<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$transient</strong></li>
<li><code>mixed</code> <strong>$value</strong></li></ul>
  

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
    $subscriberId = $I-&gt;haveUserInDatabase('test');</code></pre>
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

<p>Import the SQL dump file if populate is enabled. Specifying a dump file that file will be imported.</p>
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
<h4>Parameters</h4>
<ul>
<li><code>array</code> <strong>$criteria</strong></li></ul>
  

<h3>seeCommentMetaInDatabase</h3>

<hr>

<p>Checks that a comment meta value is in the database. Will look up the &quot;commentmeta&quot; table.</p>
<h4>Parameters</h4>
<ul>
<li><code>array</code> <strong>$criteria</strong></li></ul>
  

<h3>seeLinkInDatabase</h3>

<hr>

<p>Checks for a link in the <code>links</code> table of the database.</p>
<h4>Parameters</h4>
<ul>
<li><code>array</code> <strong>$criteria</strong> - An array of search criteria.</li></ul>
  

<h3>seeOptionInDatabase</h3>

<hr>

<p>Checks if an option is in the database for the current blog. If checking for an array or an object then the serialized version will be checked for.</p>
<h4>Parameters</h4>
<ul>
<li><code>array</code> <strong>$criteria</strong> - An array of search criteria.</li></ul>
  

<h3>seePageInDatabase</h3>

<hr>

<p>Checks for a page in the database.</p>
<h4>Parameters</h4>
<ul>
<li><code>array</code> <strong>$criteria</strong> - An array of search criteria.</li></ul>
  

<h3>seePostInDatabase</h3>

<hr>

<p>Checks for a post in the database.</p>
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
<pre><code class="language-php">    list($fiction) = $I-&gt;haveTermInDatabase('fiction', 'genre');
    $postId = $I-&gt;havePostInDatabase(['tax_input' =&gt; ['genre' =&gt; [$fiction]]]);
    $I-&gt;seePostWithTermInDatabase($postId, $fiction);</code></pre>
<h4>Parameters</h4>
<ul>
<li><code>int</code> <strong>$post_id</strong> - The post ID.</li>
<li><code>int</code> <strong>$term_id</strong> - The term ID.</li>
<li><code>integer</code> <strong>$term_order</strong> - The order the term applies to the post, defaults to 0.</li></ul>
  

<h3>seeSiteOptionInDatabase</h3>

<hr>

<p>Checks that a site option is in the database.</p>
<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$key</strong></li>
<li><code>mixed/null</code> <strong>$value</strong></li></ul>
  

<h3>seeSiteSiteTransientInDatabase</h3>

<hr>

<p>Checks that a site option is in the database.</p>
<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$key</strong></li>
<li><code>mixed/null</code> <strong>$value</strong></li></ul>
  

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

<p>Checks for a term in the database. Looks up the <code>terms</code> and <code>term_taxonomy</code> prefixed tables. <code>term_taxonomy</code> tables.</p>
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
<pre><code class="language-php">    $userId = $I-&gt;haveUserInDatabase(['])</code></pre>
<h4>Parameters</h4>
<ul>
<li><code>array</code> <strong>$criteria</strong> - An array of search criteria.</li></ul>
  

<h3>seeUserMetaInDatabase</h3>

<hr>

<p>Checks for a user meta value in the database.</p>
<h4>Parameters</h4>
<ul>
<li><code>array</code> <strong>$criteria</strong> - An array of search criteria.</li></ul>
  

<h3>useBlog</h3>

<hr>

<p>Sets the blog to be used.</p>
<h4>Parameters</h4>
<ul>
<li><code>int</code> <strong>$id</strong></li></ul>
  

<h3>useMainBlog</h3>

<hr>

<p>Sets the current blog to the main one (<code>blog_id</code> 1).</p>
  

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

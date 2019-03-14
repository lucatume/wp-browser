# WPDb module
This module should be used in acceptance and functional tests, see [levels of testing for more information](./../levels-of-testing.md).  
This module extends the [Db module](https://codeception.com/docs/modules/Db) adding WordPress-specific configuration parameters and methods.  
The module provides methods to read, write and update the WordPress database **directly**, without relying on WordPress methods, using WordPress functions or triggering WordPress filters.  

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
***
Returns the number of table rows matching a criteria.
<pre><code class="language-php">    $I-&gt;haveManyPostsInDatabase(3, ['post_status' =&gt; 'draft' ]);
    $I-&gt;haveManyPostsInDatabase(3, ['post_status' =&gt; 'private' ]);
    // Make sure there are now the expected number of draft posts.
    $postsTable = $I-&gt;grabPostsTableName();
    $draftsCount = $I-&gt;countRowsInDatabase($postsTable, ['post_status' =&gt; 'draft']);</code></pre>
#### Parameters

* `string` **$table** - The table to count the rows in.
* `array` **$criteria** - Search criteria, if empty all table rows will be counted.
  

<h3>dontHaveAttachmentFilesInDatabase</h3>
***
Removes all the files attached with an attachment post, it will not remove the database entries. Requires the `WPFilesystem` module to be loaded in the suite.
<pre><code class="language-php">    $posts = $I-&gt;grabPostsTableName();
    $attachmentIds = $I-&gt;grabColumnFromDatabase($posts, 'ID', ['post_type' =&gt; 'attachment']);
    // This will only remove the files, not the database entries.
    $I-&gt;dontHaveAttachmentFilesInDatabase($attachmentIds);</code></pre>
#### Parameters

* `array/int` **$attachmentIds** - An attachment post ID or an array of attachment post IDs.
  

<h3>dontHaveAttachmentInDatabase</h3>
***
Removes an attachment from the posts table.
<pre><code>    $postmeta = $I-&gt;grabpostmetatablename();
    $thumbnailId = $I-&gt;grabFromDatabase($postmeta, 'meta_value', [
         'post_id' =&gt; $id,
         'meta_key'=&gt;'thumbnail_id'
    ]);
    // Remove only the database entry (including postmeta) but not the files.
    $I-&gt;dontHaveAttachmentInDatabase($thumbnailId);
    // Remove the database entry (including postmeta) and the files.
    $I-&gt;dontHaveAttachmentInDatabase($thumbnailId, true, true);</code></pre>
#### Parameters

* `array` **$criteria** - An array of search criteria to find the attachment post in the posts table.
* `bool` **$purgeMeta** - If set to <code>true</code> then the meta for the attachment will be purged too.
* `bool` **$removeFiles** - Remove all files too, requires the <code>WPFilesystem</code> module to be loaded in the suite.
  

<h3>dontHaveBlogInDatabase</h3>
***
Removes one ore more blogs frome the database.
<pre><code class="language-php">    // Remove the blog, all its tables and files.
    $I-&gt;dontHaveBlogInDatabase(['path' =&gt; 'test/one']);
    // Remove the blog entry, not the tables though.
    $I-&gt;dontHaveBlogInDatabase(['blog_id' =&gt; $blogId]);
    // Remove multiple blogs.
    $I-&gt;dontHaveBlogInDatabase(['domain' =&gt; 'test']);</code></pre>
#### Parameters

* `array` **$criteria** - An array of search criteria to find the blog rows in the blogs table.
* `bool` **$removeTables** - Remove the blog tables.
* `bool` **$removeUploads** - Remove the blog uploads; requires the <code>WPFilesystem</code> module to be loaded in the suite.
  

<h3>dontHaveCommentInDatabase</h3>
***
Removes an entry from the comments table.
#### Parameters

* `array` **$criteria** - An array of search criteria.
* `bool` **$purgeMeta** - If set to <code>true</code> then the meta for the comment will be purged too.
  

<h3>dontHaveCommentMetaInDatabase</h3>
***
Removes an entry from the commentmeta table.
#### Parameters

* `array` **$criteria** - An array of search criteria.
  

<h3>dontHaveInDatabase</h3>
***
Deletes a database entry.
#### Parameters

* `string` **$table** - The table name.
* `array` **$criteria** - An associative array of the column names and values to use as deletion criteria.
  

<h3>dontHaveLinkInDatabase</h3>
***
Removes a link from the database.
#### Parameters

* `array` **$criteria** - An array of search criteria.
  

<h3>dontHaveOptionInDatabase</h3>
***
Removes an entry from the options table.
#### Parameters

* `mixed` **$key**
* `null` **$value**
  

<h3>dontHavePostInDatabase</h3>
***
Removes an entry from the posts table.
#### Parameters

* `array` **$criteria** - An array of search criteria.
* `bool` **$purgeMeta** - If set to <code>true</code> then the meta for the post will be purged too.
  

<h3>dontHavePostMetaInDatabase</h3>
***
Removes an entry from the postmeta table.
#### Parameters

* `array` **$criteria** - An array of search criteria.
  

<h3>dontHaveSiteOptionInDatabase</h3>
***
Removes a site option from the database.
#### Parameters

* `mixed` **$key**
* `null` **$value**
  

<h3>dontHaveSiteTransientInDatabase</h3>
***
Removes a site transient from the database.
#### Parameters

* `string` **$key**
  

<h3>dontHaveTableInDatabase</h3>
***
Removes a table from the database. The case where a table does not exist is handled without raising an error.
<pre><code class="language-php">    $ordersTable = $I-&gt;grabPrefixedTableNameFor('orders');
    $I-&gt;dontHaveTableInDatabase($ordersTable);</code></pre>
#### Parameters

* `string` **$fullTableName** - The full table name, including the table prefix.
  

<h3>dontHaveTermInDatabase</h3>
***
Removes a term from the database.
#### Parameters

* `array` **$criteria** - An array of search criteria.
* `bool` **$purgeMeta** - Whether the terms meta should be purged along side with the meta or not.
  

<h3>dontHaveTermMetaInDatabase</h3>
***
Removes a term meta from the database.
#### Parameters

* `array` **$criteria** - An array of search criteria.
  

<h3>dontHaveTermRelationshipInDatabase</h3>
***
Removes an entry from the term_relationships table.
#### Parameters

* `array` **$criteria** - An array of search criteria.
  

<h3>dontHaveTermTaxonomyInDatabase</h3>
***
Removes an entry from the term_taxonomy table.
#### Parameters

* `array` **$criteria** - An array of search criteria.
  

<h3>dontHaveTransientInDatabase</h3>
***
Removes a transient from the database.
#### Parameters

* `mixed` **$transient**
  

<h3>dontHaveUserInDatabase</h3>
***
Removes a user from the database.
#### Parameters

* `int/string` **$userIdOrLogin**
* `bool` **$purgeMeta** - Whether the user meta should be purged alongside the user or not.
  

<h3>dontHaveUserInDatabaseWithEmail</h3>
***
Removes a user(s) from the database using the user email address.
#### Parameters

* `string` **$userEmail**
* `bool` **$purgeMeta** - Whether the user meta should be purged alongside the user or not.
  

<h3>dontHaveUserMetaInDatabase</h3>
***
Removes an entry from the usermeta table.
#### Parameters

* `array` **$criteria** - An array of search criteria.
  

<h3>dontSeeAttachmentInDatabase</h3>
***
Checks that an attachment is not in the database.
#### Parameters

* `array` **$criteria** - An array of search criteria.
  

<h3>dontSeeBlogInDatabase</h3>
***
Checks that a row is not present in the `blogs` table.
#### Parameters

* `array` **$criteria** - An array of search criteria.
  

<h3>dontSeeCommentInDatabase</h3>
***
Checks that a comment is not in the database. Will look up the "comments" table.
#### Parameters

* `array` **$criteria**
  

<h3>dontSeeCommentMetaInDatabase</h3>
***
Checks that a comment meta value is not in the database. Will look up the "commentmeta" table.
#### Parameters

* `array` **$criteria**
  

<h3>dontSeeLinkInDatabase</h3>
***
Checks that a link is not in the database. Will look up the "links" table.
#### Parameters

* `array` **$criteria** - An array of search criteria.
  

<h3>dontSeeOptionInDatabase</h3>
***
Checks that an option is not in the database for the current blog. If the value is an object or an array then the serialized option will be checked for.
#### Parameters

* `array` **$criteria** - An array of search criteria.
  

<h3>dontSeePageInDatabase</h3>
***
Checks that a page is not in the database.
#### Parameters

* `array` **$criteria** - An array of search criteria.
  

<h3>dontSeePostInDatabase</h3>
***
Checks that a post is not in the database.
#### Parameters

* `array` **$criteria** - An array of search criteria.
  

<h3>dontSeePostMetaInDatabase</h3>
***
Checks that a post meta value is not there. If the meta value is an object or an array then the serialized version will be checked for.
#### Parameters

* `array` **$criteria** - An array of search criteria.
  

<h3>dontSeeTableInDatabase</h3>
***
Checks that a table is not in the database.
<pre><code class="language-php">    $options = $I-&gt;grabPrefixedTableNameFor('options');
    $I-&gt;dontHaveTableInDatabase($options)
    $I-&gt;dontSeeTableInDatabase($options);</code></pre>
#### Parameters

* `string` **$table** - The full table name, including the table prefix.
  

<h3>dontSeeTermInDatabase</h3>
***
Makes sure a term is not in the database. Looks up both the `terms` table and the `term_taxonomy` tables. `term_taxonomy` tables.
#### Parameters

* `array` **$criteria** - An array of criteria to search for the term, can be columns from the <code>terms</code> and the
  

<h3>dontSeeTermMetaInDatabase</h3>
***
Checks that a term meta is not in the database.
#### Parameters

* `array` **$criteria** - An array of search criteria.
  

<h3>dontSeeTermTaxonomyInDatabase</h3>
***
Checks that a term taxonomy is not in the database. Will look up the prefixed `term_taxonomy` table, e.g. `wp_term_taxonomy`.
#### Parameters

* `array` **$criteria** - An array of search criteria.
  

<h3>dontSeeUserInDatabase</h3>
***
Checks that a user is not in the database.
#### Parameters

* `array` **$criteria** - An array of search criteria.
  

<h3>dontSeeUserMetaInDatabase</h3>
***
Check that a user meta value is not in the database.
#### Parameters

* `array` **$criteria** - An array of search criteria.
  

<h3>getSiteDomain</h3>
***
Returns the site domain inferred from the `url` set in the config.
  

<h3>grabAllFromDatabase</h3>
***
Returns all entries matching a criteria from the database.
#### Parameters

* `string` **$table**
* `string` **$column**
* `array` **$criteria**
  

<h3>grabAttachmentAttachedFile</h3>
***
Returns the path, as stored in the database, of an attachment `_wp_attached_file` meta. The attached file is, usually, an attachment origal file.
<pre><code class="language-php">    $file = $I-&gt;grabAttachmentAttachedFile($attachmentId);
    $fileInfo = new SplFileInfo($file);
    $I-&gt;assertEquals('jpg', $fileInfo-&gt;getExtension());</code></pre>
#### Parameters

* `int` **$attachmentPostId** - The attachment post ID.
  

<h3>grabAttachmentMetadata</h3>
***
Returns the metadata array for an attachment post. This is the value of the `_wp_attachment_metadata` meta.
<pre><code class="language-php">    $metadata = $I-&gt;grabAttachmentMetadata($attachmentId);
    $I-&gt;assertEquals(['thumbnail', 'medium', 'medium_large'], array_keys($metadata['sizes']);</code></pre>
#### Parameters

* `int` **$attachmentPostId** - The attachment post ID.
  

<h3>grabBlogDomain</h3>
***

#### Parameters

* `mixed` **$blogId**
  

<h3>grabBlogPath</h3>
***
Grabs a blog domain from the blogs table.
<pre><code class="language-php">    $blogId = $I-&gt;haveBlogInDatabase('test');
    $path = $I-&gt;grabBlogDomain($blogId);
    $I-&gt;amOnSubdomain($path);
    $I-&gt;amOnPage('/');</code></pre>
#### Parameters

* `int` **$blogId** - The blog ID.
  

<h3>grabBlogTableName</h3>
***
Returns the full name of a table for a blog from a multisite installation database.
<pre><code class="language-php">    $blogOptionTable = $I-&gt;grabBlogTableName($blogId, 'option');</code></pre>
#### Parameters

* `int` **$blogId** - The blog ID.
* `string` **$table** - The table name, without table prefix.
  

<h3>grabBlogTableNames</h3>
***
Returns a list of tables for a blog ID.
<pre><code class="language-php">    $blogId = $I-&gt;haveBlogInDatabase('test');
    $tables = $I-&gt;grabBlogTableNames($blogId);
    $options = array_filter($tables, function($tableName){
         return str_pos($tableName, 'options') !== false;
    });</code></pre>
#### Parameters

* `int` **$blogId** - The ID of the blog to fetch the tables for.
  

<h3>grabBlogTablePrefix</h3>
***
Returns the table prefix for a blog.
<pre><code class="language-php">    $blogId = $I-&gt;haveBlogInDatabase('test');
    $blogTablePrefix = $I-&gt;getBlogTablePrefix($blogId);
    $blogOrders = $I-&gt;blogTablePrefix . 'orders';</code></pre>
#### Parameters

* `int` **$blogId** - The blog ID.
  

<h3>grabBlogVersionsTableName</h3>
***
Gets the prefixed `blog_versions` table name.
  

<h3>grabBlogsTableName</h3>
***
Gets the prefixed `blogs` table name.
  

<h3>grabCommentmetaTableName</h3>
***
Returns the prefixed comment meta table name. E.g. `wp_commentmeta`.
  

<h3>grabCommentsTableName</h3>
***
Gets the comments table name.
  

<h3>grabLatestEntryByFromDatabase</h3>
***
Returns the id value of the last table entry.
#### Parameters

* `string` **$tableName**
* `string` **$idColumn**
  

<h3>grabLinksTableName</h3>
***
Returns the prefixed links table name. E.g. `wp_links`.
  

<h3>grabOptionFromDatabase</h3>
***
Gets an option from the database.
#### Parameters

* `string` **$option_name**
  

<h3>grabPostMetaFromDatabase</h3>
***
Gets the value of one or more post meta values from the database.
<pre><code class="language-php">    $thumbnail_id = $I-&gt;grabPostMetaFromDatabase($postId, '_thumbnail_id', true);</code></pre>
#### Parameters

* `int` **$postId** - The post ID.
* `string` **$metaKey** - The key of the meta to retrieve.
* `bool` **$single** - Whether to return a single meta value or an arrya of all available meta values.
  

<h3>grabPostmetaTableName</h3>
***
Returns the prefixed post meta table name.
  

<h3>grabPostsTableName</h3>
***
Gets the posts table name.
  

<h3>grabPrefixedTableNameFor</h3>
***
Returns a prefixed table name for the current blog. If the table is not one to be prefixed (e.g. `users`) then the proper table name will be returned.
#### Parameters

* `string` **$tableName** - The table name, e.g. <code>options</code>.
  

<h3>grabRegistrationLogTableName</h3>
***
Gets the prefixed `registration_log` table name.
  

<h3>grabSignupsTableName</h3>
***
Gets the prefixed `signups` table name.
  

<h3>grabSiteMetaTableName</h3>
***
Gets the prefixed `sitemeta` table name.
  

<h3>grabSiteOptionFromDatabase</h3>
***
Gets a site option from the database.
#### Parameters

* `string` **$key**
  

<h3>grabSiteTableName</h3>
***
Gets the prefixed `site` table name.
  

<h3>grabSiteTransientFromDatabase</h3>
***
Gets a site transient from the database.
#### Parameters

* `string` **$key**
  

<h3>grabSiteUrl</h3>
***
Returns the current site url as specified in the module configuration.
#### Parameters

* `string` **$path** - A path that should be appended to the site URL.
  

<h3>grabTablePrefix</h3>
***
Returns the table prefix, namespaced for secondary blogs if selected.
  

<h3>grabTermIdFromDatabase</h3>
***
Gets a term from the database. Looks up the prefixed `terms` table, e.g. `wp_terms`.
#### Parameters

* `array` **$criteria** - An array of search criteria.
  

<h3>grabTermMetaTableName</h3>
***
Gets the terms meta table prefixed name. E.g.: `wp_termmeta`.
  

<h3>grabTermRelationshipsTableName</h3>
***
Gets the prefixed term relationships table name, e.g. `wp_term_relationships`.
  

<h3>grabTermTaxonomyIdFromDatabase</h3>
***
Gets a `term_taxonomy_id` from the database. Looks up the prefixed `terms_relationships` table, e.g. `wp_term_relationships`.
#### Parameters

* `array` **$criteria** - An array of search criteria.
  

<h3>grabTermTaxonomyTableName</h3>
***
Gets the prefixed term and taxonomy table name, e.g. `wp_term_taxonomy`.
  

<h3>grabTermsTableName</h3>
***
Gets the prefixed terms table name, e.g. `wp_terms`.
  

<h3>grabUserIdFromDatabase</h3>
***
Gets the a user ID from the database using the user login.
#### Parameters

* `string` **$userLogin**
  

<h3>grabUserMetaFromDatabase</h3>
***
Gets a user meta from the database.
#### Parameters

* `int` **$userId**
* `string` **$meta_key**
  

<h3>grabUsermetaTableName</h3>
***
Returns the prefixed `usermeta` table name, e.g. `wp_usermeta`.
  

<h3>grabUsersTableName</h3>
***
Gets the users table name.
  

<h3>haveAttachmentInDatabase</h3>
***
Creates the database entries representing an attachment and moves the attachment file to the right location. Requires the WPFilesystem module. should be used to build the "year/time" uploads sub-folder structure. image sizes created by default.
#### Parameters

* `string` **$file** - The absolute path to the attachment file.
* `string/string/int` **$date** - Either a string supported by the <code>strtotime</code> function or a UNIX timestamp that
* `array` **$overrides** - An associative array of values overriding the default ones.
* `array` **$imageSizes** - An associative array in the format [ <size> =&gt; [<width>,<height>]] to override the
  

<h3>haveBlogInDatabase</h3>
***
Inserts a blog in the `blogs` table. or subfolder (`true`)
#### Parameters

* `string` **$domainOrPath** - The subdomain or the path to the be used for the blog.
* `array` **$overrides** - An array of values to override the defaults.
* `bool` **$subdomain** - Whether the new blog should be created as a subdomain (<code>true</code>)
  

<h3>haveCommentInDatabase</h3>
***
Inserts a comment in the database.
#### Parameters

* `int` **$comment_post_ID** - The id of the post the comment refers to.
* `array` **$data** - The comment data overriding default and random generated values.
  

<h3>haveCommentMetaInDatabase</h3>
***
Inserts a comment meta field in the database. Array and object meta values will be serialized.
#### Parameters

* `int` **$comment_id**
* `string` **$meta_key**
* `mixed` **$meta_value**
  

<h3>haveLinkInDatabase</h3>
***
Inserts a link in the database.
#### Parameters

* `array` **$overrides** - The data to insert.
  

<h3>haveManyBlogsInDatabase</h3>
***
Inserts many blogs in the database.
<pre><code class="language-php">    $blogIds = $I-&gt;haveManyBlogsInDatabase(3, ['domain' =&gt;'test-{{n}}']);
    foreach($blogIds as $blogId){
         $I-&gt;useBlog($blogId);
         $I-&gt;haveManuPostsInDatabase(3);
    }</code></pre>
#### Parameters

* `int` **$count** - The number of blogs to create.
* `array` **$overrides** - An array of values to override the default ones; <code>{{n}}</code> will be replaced by the count.
* `bool` **$subdomain** - Whether the new blogs should be created as a subdomain or subfolder.
  

<h3>haveManyCommentsInDatabase</h3>
***
Inserts many comments in the database.
#### Parameters

* `int` **$count** - The number of comments to insert.
* `int` **$comment_post_ID** - The comment parent post ID.
* `array` **$overrides** - An associative array to override the defaults.
  

<h3>haveManyLinksInDatabase</h3>
***
Inserts many links in the database.
#### Parameters

* `int` **$count**
* `array/array/null/array` **$overrides**
  

<h3>haveManyPostsInDatabase</h3>
***
Inserts many posts in the database returning their IDs. An array of values to override the defaults. The `{{n}}` placeholder can be used to have the post count inserted in its place; e.g. `Post Title - {{n}}` will be set to `Post Title - 0` for the first post, `Post Title - 1` for the second one and so on. The same applies to meta values as well.
#### Parameters

* `int` **$count** - The number of posts to insert.
* `array` **$overrides**
  

<h3>haveManyTermsInDatabase</h3>
***
Inserts many terms in the database.
#### Parameters

* `int` **$count**
* `string` **$name** - The term name.
* `string` **$taxonomy** - The taxonomy name.
* `array` **$overrides** - An associative array of default overrides.
  

<h3>haveManyUsersInDatabase</h3>
***

#### Parameters

* `mixed` **$count**
* `mixed` **$user_login**
* `string` **$role**
* `array` **$overrides**
  

<h3>haveMenuInDatabase</h3>
***
Creates and adds a menu to a theme location in the database.
#### Parameters

* `string` **$slug** - The menu slug.
* `string` **$location** - The theme menu location the menu will be assigned to.
* `array` **$overrides** - An array of values to override the defaults.
  

<h3>haveMenuItemInDatabase</h3>
***
Adds a menu element to a menu for the current theme. meta.
#### Parameters

* `string` **$menuSlug** - The menu slug the item should be added to.
* `string` **$title** - The menu item title.
* `int/null` **$menuOrder** - An optional menu order, <code>1</code> based.
* `array/array/null/array` **$meta** - An associative array that will be prefixed with <code>_menu_item_</code> for the item post
  

<h3>haveOptionInDatabase</h3>
***
Inserts an option in the database. If the option value is an object or an array then the value will be serialized.
#### Parameters

* `string` **$option_name**
* `mixed` **$option_value**
* `string` **$autoload**
  

<h3>havePageInDatabase</h3>
***
Inserts a page in the database.
#### Parameters

* `array` **$overrides** - An array of values to override the default ones.
  

<h3>havePostInDatabase</h3>
***
Inserts a post in the database.
#### Parameters

* `array` **$data** - An associative array of post data to override default and random generated values.
  

<h3>havePostmetaInDatabase</h3>
***
Adds one or more meta key and value couples in the database for a post.
#### Parameters

* `int` **$postId** - The post ID.
* `string` **$meta_key** - The meta key.
* `mixed` **$meta_value** - The value to insert in the database, objects and arrays will be serialized.
  

<h3>haveSiteOptionInDatabase</h3>
***
Inserts a site option in the database. If the value is an array or an object then the value will be serialized.
#### Parameters

* `string` **$key**
* `mixed` **$value**
  

<h3>haveSiteTransientInDatabase</h3>
***
Inserts a site transient in the database. If the value is an array or an object then the value will be serialized.
#### Parameters

* `mixed` **$key**
* `mixed` **$value**
  

<h3>haveTermInDatabase</h3>
***
Inserts a term in the database.
#### Parameters

* `string` **$name** - The term name, e.g. &quot;Fuzzy&quot;.
* `string` **$taxonomy** - The term taxonomy
* `array` **$overrides** - An array of values to override the default ones.
  

<h3>haveTermMetaInDatabase</h3>
***
Inserts a term meta row in the database. Objects and array meta values will be serialized.
#### Parameters

* `int` **$term_id**
* `string` **$meta_key**
* `mixed` **$meta_value**
  

<h3>haveTermRelationshipInDatabase</h3>
***
Creates a term relationship in the database. No check about the consistency of the insertion is made. E.g. a post could be assigned a term from a taxonomy that's not registered for that post type.
#### Parameters

* `int` **$object_id** - A post ID, a user ID or anything that can be assigned a taxonomy term.
* `int` **$term_taxonomy_id**
* `int` **$term_order** - Defaults to <code>0</code>.
  

<h3>haveTransientInDatabase</h3>
***
Inserts a transient in the database. If the value is an array or an object then the value will be serialized.
#### Parameters

* `string` **$transient**
* `mixed` **$value**
  

<h3>haveUserCapabilitiesInDatabase</h3>
***
Sets a user capabilities. for a multisite installation; e.g. `[1 => 'administrator`, 2 => 'subscriber']`.
#### Parameters

* `int` **$userId**
* `string/array` **$role** - Either a role string (e.g. <code>administrator</code>) or an associative array of blog IDs/roles
  

<h3>haveUserInDatabase</h3>
***
Inserts a user and appropriate meta in the database. and "usermeta" table.
#### Parameters

* `string` **$user_login** - The user login slug
* `string` **$role** - The user role slug, e.g. &quot;administrator&quot;; defaults to &quot;subscriber&quot;.
* `array` **$overrides** - An associative array of column names and values overridind defaults in the &quot;users&quot;
  

<h3>haveUserLevelsInDatabase</h3>
***
Sets the user level in the database for a user. multisite installation.
#### Parameters

* `int` **$userId**
* `string/array` **$role** - Either a role string (e.g. <code>administrator</code>) or an array of blog IDs/roles for a
  

<h3>haveUserMetaInDatabase</h3>
***
Sets a user meta. values will trigger the insertion of multiple rows.
#### Parameters

* `int` **$userId**
* `string` **$meta_key**
* `mixed` **$meta_value** - Either a single value or an array of values; objects will be serialized while array of
  

<h3>importSqlDumpFile</h3>
***
Import the SQL dump file if populate is enabled. Specifying a dump file that file will be imported.
#### Parameters

* `null/string` **$dumpFile** - The dump file that should be imported in place of the default one.
  

<h3>seeAttachmentInDatabase</h3>
***
Checks for an attachment in the database.
#### Parameters

* `array` **$criteria** - An array of search criteria.
  

<h3>seeBlogInDatabase</h3>
***
Checks for a blog in the database, looks up the `blogs` table.
#### Parameters

* `array` **$criteria** - An array of search criteria.
  

<h3>seeCommentInDatabase</h3>
***
Checks for a comment in the database. Will look up the "comments" table.
#### Parameters

* `array` **$criteria**
  

<h3>seeCommentMetaInDatabase</h3>
***
Checks that a comment meta value is in the database. Will look up the "commentmeta" table.
#### Parameters

* `array` **$criteria**
  

<h3>seeLinkInDatabase</h3>
***
Checks for a link in the database. Will look up the "links" table.
#### Parameters

* `array` **$criteria** - An array of search criteria.
  

<h3>seeOptionInDatabase</h3>
***
Checks if an option is in the database for the current blog. If checking for an array or an object then the serialized version will be checked for.
#### Parameters

* `array` **$criteria** - An array of search criteria.
  

<h3>seePageInDatabase</h3>
***
Checks for a page in the database.
#### Parameters

* `array` **$criteria** - An array of search criteria.
  

<h3>seePostInDatabase</h3>
***
Checks for a post in the database.
#### Parameters

* `array` **$criteria** - An array of search criteria.
  

<h3>seePostMetaInDatabase</h3>
***
Checks for a post meta value in the database for the current blog. If the `meta_value` is an object or an array then the serialized value will be checked for.
#### Parameters

* `array` **$criteria** - An array of search criteria.
  

<h3>seePostWithTermInDatabase</h3>
***
Checks that a post to term relation exists in the database. Will look up the "term_relationships" table.
#### Parameters

* `int` **$post_id** - The post ID.
* `int` **$term_id** - The term ID.
* `integer` **$term_order** - The order the term applies to the post, defaults to 0.
  

<h3>seeSiteOptionInDatabase</h3>
***
Checks that a site option is in the database.
#### Parameters

* `string` **$key**
* `mixed/null` **$value**
  

<h3>seeSiteSiteTransientInDatabase</h3>
***
Checks that a site option is in the database.
#### Parameters

* `string` **$key**
* `mixed/null` **$value**
  

<h3>seeTableInDatabase</h3>
***
Checks that a table is in the database.
<pre><code class="language-php">    $options = $I-&gt;grabPrefixedTableNameFor('options');
    $I-&gt;seeTableInDatabase($options);</code></pre>
#### Parameters

* `string` **$table** - The full table name, including the table prefix.
  

<h3>seeTermInDatabase</h3>
***
Checks for a term in the database. Looks up the `terms` and `term_taxonomy` prefixed tables. `term_taxonomy` tables.
#### Parameters

* `array` **$criteria** - An array of criteria to search for the term, can be columns from the <code>terms</code> and the
  

<h3>seeTermMetaInDatabase</h3>
***
Checks for a term meta in the database.
#### Parameters

* `array` **$criteria** - An array of search criteria.
  

<h3>seeTermRelationshipInDatabase</h3>
***
Checks for a term relationship in the database.
#### Parameters

* `array` **$criteria** - An array of search criteria.
  

<h3>seeTermTaxonomyInDatabase</h3>
***
Checks for a term taxonomy in the database. Will look up the prefixed `term_taxonomy` table, e.g. `wp_term_taxonomy`.
#### Parameters

* `array` **$criteria** - An array of search criteria.
  

<h3>seeUserInDatabase</h3>
***
Checks that a user is in the database. Will look up the "users" table.
#### Parameters

* `array` **$criteria**
  

<h3>seeUserMetaInDatabase</h3>
***
Checks for a user meta value in the database.
#### Parameters

* `array` **$criteria** - An array of search criteria.
  

<h3>useBlog</h3>
***
Sets the blog to be used.
#### Parameters

* `int` **$id**
  

<h3>useMainBlog</h3>
***
Sets the current blog to the main one (`blog_id` 1).
  

<h3>useTheme</h3>
***
Sets the current theme options.
#### Parameters

* `string` **$stylesheet** - The theme stylesheet slug, e.g. <code>twentysixteen</code>.
* `string/null` **$template** - The theme template slug, e.g. <code>twentysixteen</code>, defaults to <code>$stylesheet</code>.
* `string/null` **$themeName** - The theme name, e.g. <code>Twentysixteen</code>, defaults to title version of <code>$stylesheet</code>.
</br>

*This class extends \Codeception\Module\Db*

*This class implements \Codeception\Lib\Interfaces\Db*

<!--/doc-->

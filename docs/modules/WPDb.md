# WPDb module
This module should be used in acceptance and functional tests, see [levels of testing for more information](./../levels-of-testing.md).  
This module extends the [Db module](https://codeception.com/docs/modules/Db) adding WordPress-specific configuration parameters and methods.  
The module provides methods to read, write and update the WordPress database **directly**, without relying on WordPress methods, using WordPress functions or triggering WordPress filters.  

<!--doc-->


## Public API
<nav>
	<ul>
		<li>
			<a href="#countRowsInDatabase">countRowsInDatabase</a>
		</li>
		<li>
			<a href="#dontHaveAttachmentFilesInDatabase">dontHaveAttachmentFilesInDatabase</a>
		</li>
		<li>
			<a href="#dontHaveAttachmentInDatabase">dontHaveAttachmentInDatabase</a>
		</li>
		<li>
			<a href="#dontHaveBlogInDatabase">dontHaveBlogInDatabase</a>
		</li>
		<li>
			<a href="#dontHaveCommentInDatabase">dontHaveCommentInDatabase</a>
		</li>
		<li>
			<a href="#dontHaveCommentMetaInDatabase">dontHaveCommentMetaInDatabase</a>
		</li>
		<li>
			<a href="#dontHaveInDatabase">dontHaveInDatabase</a>
		</li>
		<li>
			<a href="#dontHaveLinkInDatabase">dontHaveLinkInDatabase</a>
		</li>
		<li>
			<a href="#dontHaveOptionInDatabase">dontHaveOptionInDatabase</a>
		</li>
		<li>
			<a href="#dontHavePostInDatabase">dontHavePostInDatabase</a>
		</li>
		<li>
			<a href="#dontHavePostMetaInDatabase">dontHavePostMetaInDatabase</a>
		</li>
		<li>
			<a href="#dontHaveSiteOptionInDatabase">dontHaveSiteOptionInDatabase</a>
		</li>
		<li>
			<a href="#dontHaveSiteTransientInDatabase">dontHaveSiteTransientInDatabase</a>
		</li>
		<li>
			<a href="#dontHaveTableInDatabase">dontHaveTableInDatabase</a>
		</li>
		<li>
			<a href="#dontHaveTermInDatabase">dontHaveTermInDatabase</a>
		</li>
		<li>
			<a href="#dontHaveTermMetaInDatabase">dontHaveTermMetaInDatabase</a>
		</li>
		<li>
			<a href="#dontHaveTermRelationshipInDatabase">dontHaveTermRelationshipInDatabase</a>
		</li>
		<li>
			<a href="#dontHaveTermTaxonomyInDatabase">dontHaveTermTaxonomyInDatabase</a>
		</li>
		<li>
			<a href="#dontHaveTransientInDatabase">dontHaveTransientInDatabase</a>
		</li>
		<li>
			<a href="#dontHaveUserInDatabase">dontHaveUserInDatabase</a>
		</li>
		<li>
			<a href="#dontHaveUserInDatabaseWithEmail">dontHaveUserInDatabaseWithEmail</a>
		</li>
		<li>
			<a href="#dontHaveUserMetaInDatabase">dontHaveUserMetaInDatabase</a>
		</li>
		<li>
			<a href="#dontSeeAttachmentInDatabase">dontSeeAttachmentInDatabase</a>
		</li>
		<li>
			<a href="#dontSeeBlogInDatabase">dontSeeBlogInDatabase</a>
		</li>
		<li>
			<a href="#dontSeeCommentInDatabase">dontSeeCommentInDatabase</a>
		</li>
		<li>
			<a href="#dontSeeCommentMetaInDatabase">dontSeeCommentMetaInDatabase</a>
		</li>
		<li>
			<a href="#dontSeeLinkInDatabase">dontSeeLinkInDatabase</a>
		</li>
		<li>
			<a href="#dontSeeOptionInDatabase">dontSeeOptionInDatabase</a>
		</li>
		<li>
			<a href="#dontSeePageInDatabase">dontSeePageInDatabase</a>
		</li>
		<li>
			<a href="#dontSeePostInDatabase">dontSeePostInDatabase</a>
		</li>
		<li>
			<a href="#dontSeePostMetaInDatabase">dontSeePostMetaInDatabase</a>
		</li>
		<li>
			<a href="#dontSeeTableInDatabase">dontSeeTableInDatabase</a>
		</li>
		<li>
			<a href="#dontSeeTermInDatabase">dontSeeTermInDatabase</a>
		</li>
		<li>
			<a href="#dontSeeTermMetaInDatabase">dontSeeTermMetaInDatabase</a>
		</li>
		<li>
			<a href="#dontSeeTermTaxonomyInDatabase">dontSeeTermTaxonomyInDatabase</a>
		</li>
		<li>
			<a href="#dontSeeUserInDatabase">dontSeeUserInDatabase</a>
		</li>
		<li>
			<a href="#dontSeeUserMetaInDatabase">dontSeeUserMetaInDatabase</a>
		</li>
		<li>
			<a href="#getSiteDomain">getSiteDomain</a>
		</li>
		<li>
			<a href="#grabAllFromDatabase">grabAllFromDatabase</a>
		</li>
		<li>
			<a href="#grabAttachmentAttachedFile">grabAttachmentAttachedFile</a>
		</li>
		<li>
			<a href="#grabAttachmentMetadata">grabAttachmentMetadata</a>
		</li>
		<li>
			<a href="#grabBlogDomain">grabBlogDomain</a>
		</li>
		<li>
			<a href="#grabBlogPath">grabBlogPath</a>
		</li>
		<li>
			<a href="#grabBlogTableName">grabBlogTableName</a>
		</li>
		<li>
			<a href="#grabBlogTableNames">grabBlogTableNames</a>
		</li>
		<li>
			<a href="#grabBlogTablePrefix">grabBlogTablePrefix</a>
		</li>
		<li>
			<a href="#grabBlogVersionsTableName">grabBlogVersionsTableName</a>
		</li>
		<li>
			<a href="#grabBlogsTableName">grabBlogsTableName</a>
		</li>
		<li>
			<a href="#grabCommentmetaTableName">grabCommentmetaTableName</a>
		</li>
		<li>
			<a href="#grabCommentsTableName">grabCommentsTableName</a>
		</li>
		<li>
			<a href="#grabLatestEntryByFromDatabase">grabLatestEntryByFromDatabase</a>
		</li>
		<li>
			<a href="#grabLinksTableName">grabLinksTableName</a>
		</li>
		<li>
			<a href="#grabOptionFromDatabase">grabOptionFromDatabase</a>
		</li>
		<li>
			<a href="#grabPostMetaFromDatabase">grabPostMetaFromDatabase</a>
		</li>
		<li>
			<a href="#grabPostmetaTableName">grabPostmetaTableName</a>
		</li>
		<li>
			<a href="#grabPostsTableName">grabPostsTableName</a>
		</li>
		<li>
			<a href="#grabPrefixedTableNameFor">grabPrefixedTableNameFor</a>
		</li>
		<li>
			<a href="#grabRegistrationLogTableName">grabRegistrationLogTableName</a>
		</li>
		<li>
			<a href="#grabSignupsTableName">grabSignupsTableName</a>
		</li>
		<li>
			<a href="#grabSiteMetaTableName">grabSiteMetaTableName</a>
		</li>
		<li>
			<a href="#grabSiteOptionFromDatabase">grabSiteOptionFromDatabase</a>
		</li>
		<li>
			<a href="#grabSiteTableName">grabSiteTableName</a>
		</li>
		<li>
			<a href="#grabSiteTransientFromDatabase">grabSiteTransientFromDatabase</a>
		</li>
		<li>
			<a href="#grabSiteUrl">grabSiteUrl</a>
		</li>
		<li>
			<a href="#grabTablePrefix">grabTablePrefix</a>
		</li>
		<li>
			<a href="#grabTermIdFromDatabase">grabTermIdFromDatabase</a>
		</li>
		<li>
			<a href="#grabTermMetaTableName">grabTermMetaTableName</a>
		</li>
		<li>
			<a href="#grabTermRelationshipsTableName">grabTermRelationshipsTableName</a>
		</li>
		<li>
			<a href="#grabTermTaxonomyIdFromDatabase">grabTermTaxonomyIdFromDatabase</a>
		</li>
		<li>
			<a href="#grabTermTaxonomyTableName">grabTermTaxonomyTableName</a>
		</li>
		<li>
			<a href="#grabTermsTableName">grabTermsTableName</a>
		</li>
		<li>
			<a href="#grabUserIdFromDatabase">grabUserIdFromDatabase</a>
		</li>
		<li>
			<a href="#grabUserMetaFromDatabase">grabUserMetaFromDatabase</a>
		</li>
		<li>
			<a href="#grabUsermetaTableName">grabUsermetaTableName</a>
		</li>
		<li>
			<a href="#grabUsersTableName">grabUsersTableName</a>
		</li>
		<li>
			<a href="#haveAttachmentInDatabase">haveAttachmentInDatabase</a>
		</li>
		<li>
			<a href="#haveBlogInDatabase">haveBlogInDatabase</a>
		</li>
		<li>
			<a href="#haveCommentInDatabase">haveCommentInDatabase</a>
		</li>
		<li>
			<a href="#haveCommentMetaInDatabase">haveCommentMetaInDatabase</a>
		</li>
		<li>
			<a href="#haveLinkInDatabase">haveLinkInDatabase</a>
		</li>
		<li>
			<a href="#haveManyBlogsInDatabase">haveManyBlogsInDatabase</a>
		</li>
		<li>
			<a href="#haveManyCommentsInDatabase">haveManyCommentsInDatabase</a>
		</li>
		<li>
			<a href="#haveManyLinksInDatabase">haveManyLinksInDatabase</a>
		</li>
		<li>
			<a href="#haveManyPostsInDatabase">haveManyPostsInDatabase</a>
		</li>
		<li>
			<a href="#haveManyTermsInDatabase">haveManyTermsInDatabase</a>
		</li>
		<li>
			<a href="#haveManyUsersInDatabase">haveManyUsersInDatabase</a>
		</li>
		<li>
			<a href="#haveMenuInDatabase">haveMenuInDatabase</a>
		</li>
		<li>
			<a href="#haveMenuItemInDatabase">haveMenuItemInDatabase</a>
		</li>
		<li>
			<a href="#haveOptionInDatabase">haveOptionInDatabase</a>
		</li>
		<li>
			<a href="#havePageInDatabase">havePageInDatabase</a>
		</li>
		<li>
			<a href="#havePostInDatabase">havePostInDatabase</a>
		</li>
		<li>
			<a href="#havePostmetaInDatabase">havePostmetaInDatabase</a>
		</li>
		<li>
			<a href="#haveSiteOptionInDatabase">haveSiteOptionInDatabase</a>
		</li>
		<li>
			<a href="#haveSiteTransientInDatabase">haveSiteTransientInDatabase</a>
		</li>
		<li>
			<a href="#haveTermInDatabase">haveTermInDatabase</a>
		</li>
		<li>
			<a href="#haveTermMetaInDatabase">haveTermMetaInDatabase</a>
		</li>
		<li>
			<a href="#haveTermRelationshipInDatabase">haveTermRelationshipInDatabase</a>
		</li>
		<li>
			<a href="#haveTransientInDatabase">haveTransientInDatabase</a>
		</li>
		<li>
			<a href="#haveUserCapabilitiesInDatabase">haveUserCapabilitiesInDatabase</a>
		</li>
		<li>
			<a href="#haveUserInDatabase">haveUserInDatabase</a>
		</li>
		<li>
			<a href="#haveUserLevelsInDatabase">haveUserLevelsInDatabase</a>
		</li>
		<li>
			<a href="#haveUserMetaInDatabase">haveUserMetaInDatabase</a>
		</li>
		<li>
			<a href="#importSqlDumpFile">importSqlDumpFile</a>
		</li>
		<li>
			<a href="#seeAttachmentInDatabase">seeAttachmentInDatabase</a>
		</li>
		<li>
			<a href="#seeBlogInDatabase">seeBlogInDatabase</a>
		</li>
		<li>
			<a href="#seeCommentInDatabase">seeCommentInDatabase</a>
		</li>
		<li>
			<a href="#seeCommentMetaInDatabase">seeCommentMetaInDatabase</a>
		</li>
		<li>
			<a href="#seeLinkInDatabase">seeLinkInDatabase</a>
		</li>
		<li>
			<a href="#seeOptionInDatabase">seeOptionInDatabase</a>
		</li>
		<li>
			<a href="#seePageInDatabase">seePageInDatabase</a>
		</li>
		<li>
			<a href="#seePostInDatabase">seePostInDatabase</a>
		</li>
		<li>
			<a href="#seePostMetaInDatabase">seePostMetaInDatabase</a>
		</li>
		<li>
			<a href="#seePostWithTermInDatabase">seePostWithTermInDatabase</a>
		</li>
		<li>
			<a href="#seeSiteOptionInDatabase">seeSiteOptionInDatabase</a>
		</li>
		<li>
			<a href="#seeSiteSiteTransientInDatabase">seeSiteSiteTransientInDatabase</a>
		</li>
		<li>
			<a href="#seeTableInDatabase">seeTableInDatabase</a>
		</li>
		<li>
			<a href="#seeTermInDatabase">seeTermInDatabase</a>
		</li>
		<li>
			<a href="#seeTermMetaInDatabase">seeTermMetaInDatabase</a>
		</li>
		<li>
			<a href="#seeTermRelationshipInDatabase">seeTermRelationshipInDatabase</a>
		</li>
		<li>
			<a href="#seeTermTaxonomyInDatabase">seeTermTaxonomyInDatabase</a>
		</li>
		<li>
			<a href="#seeUserInDatabase">seeUserInDatabase</a>
		</li>
		<li>
			<a href="#seeUserMetaInDatabase">seeUserMetaInDatabase</a>
		</li>
		<li>
			<a href="#useBlog">useBlog</a>
		</li>
		<li>
			<a href="#useMainBlog">useMainBlog</a>
		</li>
		<li>
			<a href="#useTheme">useTheme</a>
		</li>
	</ul>
</nav>

###countRowsInDatabase
***
Returns the number of table rows matching a criteria.
<pre><code class="language-php">    $I-&gt;haveManyPostsInDatabase(3, ['post_status' =&gt; 'draft' ]);
    $I-&gt;haveManyPostsInDatabase(3, ['post_status' =&gt; 'private' ]);
    // Make sure there are now the expected number of draft posts.
    $postsTable = $I-&gt;grabPostsTableName();
    $I-&gt;countRowsInDatabase($postsTable, ['post_status' =&gt; 'draft']);</code></pre>
#### Parameters
<ul>
<li><em>string</em> <strong>$table</strong> - The table to count the rows in.</li>
<li><em>array</em> <strong>$criteria</strong> = <em>array()</em> - Search criteria, if empty all table rows will be counted.</li></ul>
</br>

###dontHaveAttachmentFilesInDatabase
***
Removes all the files attached with an attachment post, it will not remove the database entries. Requires the `WPFilesystem` module to be loaded in the suite.
<pre><code class="language-php">    $posts = $I-&gt;grabPostsTableName();
    $attachmentIds = $I-&gt;grabColumnFromDatabase($posts, 'ID', ['post_type' =&gt; 'attachment']);
    // This will only remove the files, not the database entries.
    $I-&gt;dontHaveAttachmentFilesInDatabase($attachmentIds);</code></pre>
#### Parameters
<ul>
<li><em>array/int</em> <strong>$attachmentIds</strong> - An attachment post ID or an array of attachment post IDs.</li></ul>
</br>

###dontHaveAttachmentInDatabase
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
<ul>
<li><em>array</em> <strong>$criteria</strong> - An array of search criteria to find the attachment post in the posts table.</li>
<li><em>bool</em> <strong>$purgeMeta</strong> = <em>true</em> - If set to <code>true</code> then the meta for the attachment will be purged too.</li>
<li><em>bool</em> <strong>$removeFiles</strong> = <em>false</em> - Remove all files too, requires the <code>WPFilesystem</code> module to be loaded in the suite.</li></ul>
</br>

###dontHaveBlogInDatabase
***
Removes a blog entry and tables from the database.
#### Parameters
<ul>
<li><em>array</em> <strong>$criteria</strong> - An array of search criteria to find the blog row in the blogs table.</li>
<li><em>bool</em> <strong>$removeTables</strong> = <em>true</em> - Remove the blog tables.</li>
<li><em>bool</em> <strong>$removeUploads</strong> = <em>true</em> - Remove the blog uploads; requires the <code>WPFilesystem</code> module to be loaded in the suite.</li></ul>
</br>

###dontHaveCommentInDatabase
***
Removes an entry from the comments table.
#### Parameters
<ul>
<li><em>array</em> <strong>$criteria</strong> - An array of search criteria.</li>
<li><em>bool</em> <strong>$purgeMeta</strong> = <em>true</em> - If set to <code>true</code> then the meta for the comment will be purged too.</li></ul>
</br>

###dontHaveCommentMetaInDatabase
***
Removes an entry from the commentmeta table.
#### Parameters
<ul>
<li><em>array</em> <strong>$criteria</strong> - An array of search criteria.</li></ul>
</br>

###dontHaveInDatabase
***
Deletes a database entry.
#### Parameters
<ul>
<li><em>string</em> <strong>$table</strong> - The table name.</li>
<li><em>array</em> <strong>$criteria</strong> - An associative array of the column names and values to use as deletion criteria.</li></ul>
</br>

###dontHaveLinkInDatabase
***
Removes a link from the database.
#### Parameters
<ul>
<li><em>array</em> <strong>$criteria</strong> - An array of search criteria.</li></ul>
</br>

###dontHaveOptionInDatabase
***
Removes an entry from the options table.
#### Parameters
<ul>
<li><em>mixed</em> <strong>$key</strong></li>
<li><em>null</em> <strong>$value</strong> = <em>null</em></li></ul>
</br>

###dontHavePostInDatabase
***
Removes an entry from the posts table.
#### Parameters
<ul>
<li><em>array</em> <strong>$criteria</strong> - An array of search criteria.</li>
<li><em>bool</em> <strong>$purgeMeta</strong> = <em>true</em> - If set to <code>true</code> then the meta for the post will be purged too.</li></ul>
</br>

###dontHavePostMetaInDatabase
***
Removes an entry from the postmeta table.
#### Parameters
<ul>
<li><em>array</em> <strong>$criteria</strong> - An array of search criteria.</li></ul>
</br>

###dontHaveSiteOptionInDatabase
***
Removes a site option from the database.
#### Parameters
<ul>
<li><em>mixed</em> <strong>$key</strong></li>
<li><em>null</em> <strong>$value</strong> = <em>null</em></li></ul>
</br>

###dontHaveSiteTransientInDatabase
***
Removes a site transient from the database.
#### Parameters
<ul>
<li><em>string</em> <strong>$key</strong></li></ul>
</br>

###dontHaveTableInDatabase
***
Removes a table from the database. The case where a table does not exist is handled without raising an error.
<pre><code class="language-php">    $ordersTable = $I-&gt;grabPrefixedTableNameFor('orders');
    $I-&gt;dontHaveTableInDatabase($ordersTable);</code></pre>
#### Parameters
<ul>
<li><em>string</em> <strong>$fullTableName</strong> - The full table name, including the table prefix.</li></ul>
</br>

###dontHaveTermInDatabase
***
Removes a term from the database.
#### Parameters
<ul>
<li><em>array</em> <strong>$criteria</strong> - An array of search criteria.</li>
<li><em>bool</em> <strong>$purgeMeta</strong> = <em>true</em> - Whether the terms meta should be purged along side with the meta or not.</li></ul>
</br>

###dontHaveTermMetaInDatabase
***
Removes a term meta from the database.
#### Parameters
<ul>
<li><em>array</em> <strong>$criteria</strong> - An array of search criteria.</li></ul>
</br>

###dontHaveTermRelationshipInDatabase
***
Removes an entry from the term_relationships table.
#### Parameters
<ul>
<li><em>array</em> <strong>$criteria</strong> - An array of search criteria.</li></ul>
</br>

###dontHaveTermTaxonomyInDatabase
***
Removes an entry from the term_taxonomy table.
#### Parameters
<ul>
<li><em>array</em> <strong>$criteria</strong> - An array of search criteria.</li></ul>
</br>

###dontHaveTransientInDatabase
***
Removes a transient from the database.
#### Parameters
<ul>
<li><em>mixed</em> <strong>$transient</strong></li></ul>
</br>

###dontHaveUserInDatabase
***
Removes a user from the database.
#### Parameters
<ul>
<li><em>int/string</em> <strong>$userIdOrLogin</strong></li>
<li><em>bool</em> <strong>$purgeMeta</strong> = <em>true</em> - Whether the user meta should be purged alongside the user or not.</li></ul>
</br>

###dontHaveUserInDatabaseWithEmail
***
Removes a user(s) from the database using the user email address.
#### Parameters
<ul>
<li><em>string</em> <strong>$userEmail</strong></li>
<li><em>bool</em> <strong>$purgeMeta</strong> = <em>true</em> - Whether the user meta should be purged alongside the user or not.</li></ul>
</br>

###dontHaveUserMetaInDatabase
***
Removes an entry from the usermeta table.
#### Parameters
<ul>
<li><em>array</em> <strong>$criteria</strong> - An array of search criteria.</li></ul>
</br>

###dontSeeAttachmentInDatabase
***
Checks that an attachment is not in the database.
#### Parameters
<ul>
<li><em>array</em> <strong>$criteria</strong> - An array of search criteria.</li></ul>
</br>

###dontSeeBlogInDatabase
***
Checks that a row is not present in the `blogs` table.
#### Parameters
<ul>
<li><em>array</em> <strong>$criteria</strong> - An array of search criteria.</li></ul>
</br>

###dontSeeCommentInDatabase
***
Checks that a comment is not in the database. Will look up the "comments" table.
#### Parameters
<ul>
<li><em>array</em> <strong>$criteria</strong></li></ul>
</br>

###dontSeeCommentMetaInDatabase
***
Checks that a comment meta value is not in the database. Will look up the "commentmeta" table.
#### Parameters
<ul>
<li><em>array</em> <strong>$criteria</strong></li></ul>
</br>

###dontSeeLinkInDatabase
***
Checks that a link is not in the database. Will look up the "links" table.
#### Parameters
<ul>
<li><em>array</em> <strong>$criteria</strong> - An array of search criteria.</li></ul>
</br>

###dontSeeOptionInDatabase
***
Checks that an option is not in the database for the current blog. If the value is an object or an array then the serialized option will be checked for.
#### Parameters
<ul>
<li><em>array</em> <strong>$criteria</strong> - An array of search criteria.</li></ul>
</br>

###dontSeePageInDatabase
***
Checks that a page is not in the database.
#### Parameters
<ul>
<li><em>array</em> <strong>$criteria</strong> - An array of search criteria.</li></ul>
</br>

###dontSeePostInDatabase
***
Checks that a post is not in the database.
#### Parameters
<ul>
<li><em>array</em> <strong>$criteria</strong> - An array of search criteria.</li></ul>
</br>

###dontSeePostMetaInDatabase
***
Checks that a post meta value is not there. If the meta value is an object or an array then the serialized version will be checked for.
#### Parameters
<ul>
<li><em>array</em> <strong>$criteria</strong> - An array of search criteria.</li></ul>
</br>

###dontSeeTableInDatabase
***
Checks that a table is not in the database.
<pre><code class="language-php">    $options = $I-&gt;grabPrefixedTableNameFor('options');
    $I-&gt;dontHaveTableInDatabase($options)
    $I-&gt;dontSeeTableInDatabase($options);</code></pre>
#### Parameters
<ul>
<li><em>string</em> <strong>$table</strong> - The full table name, including the table prefix.</li></ul>
</br>

###dontSeeTermInDatabase
***
Makes sure a term is not in the database. Looks up both the `terms` table and the `term_taxonomy` tables. `term_taxonomy` tables.
#### Parameters
<ul>
<li><em>array</em> <strong>$criteria</strong> - An array of criteria to search for the term, can be columns from the <code>terms</code> and the</li></ul>
</br>

###dontSeeTermMetaInDatabase
***
Checks that a term meta is not in the database.
#### Parameters
<ul>
<li><em>array</em> <strong>$criteria</strong> - An array of search criteria.</li></ul>
</br>

###dontSeeTermTaxonomyInDatabase
***
Checks that a term taxonomy is not in the database. Will look up the prefixed `term_taxonomy` table, e.g. `wp_term_taxonomy`.
#### Parameters
<ul>
<li><em>array</em> <strong>$criteria</strong> - An array of search criteria.</li></ul>
</br>

###dontSeeUserInDatabase
***
Checks that a user is not in the database.
#### Parameters
<ul>
<li><em>array</em> <strong>$criteria</strong> - An array of search criteria.</li></ul>
</br>

###dontSeeUserMetaInDatabase
***
Check that a user meta value is not in the database.
#### Parameters
<ul>
<li><em>array</em> <strong>$criteria</strong> - An array of search criteria.</li></ul>
</br>

###getSiteDomain
***
Returns the site domain inferred from the `url` set in the config.
</br>

###grabAllFromDatabase
***
Returns all entries matching a criteria from the database.
#### Parameters
<ul>
<li><em>string</em> <strong>$table</strong></li>
<li><em>string</em> <strong>$column</strong></li>
<li><em>array</em> <strong>$criteria</strong></li></ul>
</br>

###grabAttachmentAttachedFile
***
Returns the path, as stored in the database, of an attachment `_wp_attached_file` meta. The attached file is, usually, an attachment origal file.
<pre><code class="language-php">    $file = $I-&gt;grabAttachmentAttachedFile($attachmentId);
    $fileInfo = new SplFileInfo($file);
    $I-&gt;assertEquals('jpg', $fileInfo-&gt;getExtension());</code></pre>
#### Parameters
<ul>
<li><em>int</em> <strong>$attachmentPostId</strong> - The attachment post ID.</li></ul>
</br>

###grabAttachmentMetadata
***
Returns the metadata array for an attachment post. This is the value of the `_wp_attachment_metadata` meta.
<pre><code class="language-php">    $metadata = $I-&gt;grabAttachmentMetadata($attachmentId);
    $I-&gt;assertEquals(['thumbnail', 'medium', 'medium_large'], array_keys($metadata['sizes']);</code></pre>
#### Parameters
<ul>
<li><em>int</em> <strong>$attachmentPostId</strong> - The attachment post ID.</li></ul>
</br>

###grabBlogDomain
***

#### Parameters
<ul>
<li><em>mixed</em> <strong>$blogId</strong></li></ul>
</br>

###grabBlogPath
***
Grabs a blog domain from the blogs table.
<pre><code class="language-php">    $blogId = $I-&gt;haveBlogInDatabase('test');
    $path = $I-&gt;grabBlogDomain($blogId);
    $I-&gt;amOnSubdomain($path);
    $I-&gt;amOnPage('/');</code></pre>
#### Parameters
<ul>
<li><em>int</em> <strong>$blogId</strong> - The blog ID.</li></ul>
</br>

###grabBlogTableName
***
Returns the full name of a table for a blog from a multisite installation database.
<pre><code class="language-php">    $blogOptionTable = $I-&gt;grabBlogTableName($blogId, 'option');</code></pre>
#### Parameters
<ul>
<li><em>int</em> <strong>$blogId</strong> - The blog ID.</li>
<li><em>string</em> <strong>$table</strong> - The table name, without table prefix.</li></ul>
</br>

###grabBlogTableNames
***
Returns a list of tables for a blog ID.
<pre><code class="language-php">    $blogId = $I-&gt;haveBlogInDatabase('test');
    $tables = $I-&gt;grabBlogTableNames($blogId);
    $options = array_filter($tables, function($tableName){
         return str_pos($tableName, 'options') !== false;
    });</code></pre>
#### Parameters
<ul>
<li><em>int</em> <strong>$blogId</strong> - The ID of the blog to fetch the tables for.</li></ul>
</br>

###grabBlogTablePrefix
***
Returns the table prefix for a blog.
<pre><code class="language-php">    $blogId = $I-&gt;haveBlogInDatabase('test');
    $blogTablePrefix = $I-&gt;getBlogTablePrefix($blogId);
    $blogOrders = $I-&gt;blogTablePrefix . 'orders';</code></pre>
#### Parameters
<ul>
<li><em>int</em> <strong>$blogId</strong> - The blog ID.</li></ul>
</br>

###grabBlogVersionsTableName
***
Gets the prefixed `blog_versions` table name.
</br>

###grabBlogsTableName
***
Gets the prefixed `blogs` table name.
</br>

###grabCommentmetaTableName
***
Returns the prefixed comment meta table name. E.g. `wp_commentmeta`.
</br>

###grabCommentsTableName
***
Gets the comments table name.
</br>

###grabLatestEntryByFromDatabase
***
Returns the id value of the last table entry.
#### Parameters
<ul>
<li><em>string</em> <strong>$tableName</strong></li>
<li><em>string</em> <strong>$idColumn</strong> = <em>`'ID'`</em></li></ul>
</br>

###grabLinksTableName
***
Returns the prefixed links table name. E.g. `wp_links`.
</br>

###grabOptionFromDatabase
***
Gets an option from the database.
#### Parameters
<ul>
<li><em>string</em> <strong>$option_name</strong></li></ul>
</br>

###grabPostMetaFromDatabase
***
Gets the value of one or more post meta values from the database.
<pre><code class="language-php">    $thumbnail_id = $I-&gt;grabPostMetaFromDatabase($postId, '_thumbnail_id', true);</code></pre>
#### Parameters
<ul>
<li><em>int</em> <strong>$postId</strong> - The post ID.</li>
<li><em>string</em> <strong>$metaKey</strong> - The key of the meta to retrieve.</li>
<li><em>bool</em> <strong>$single</strong> = <em>false</em> - Whether to return a single meta value or an arrya of all available meta values.</li></ul>
</br>

###grabPostmetaTableName
***
Returns the prefixed post meta table name.
</br>

###grabPostsTableName
***
Gets the posts table name.
</br>

###grabPrefixedTableNameFor
***
Returns a prefixed table name for the current blog. If the table is not one to be prefixed (e.g. `users`) then the proper table name will be returned.
#### Parameters
<ul>
<li><em>string</em> <strong>$tableName</strong> = <em>`''`</em> - The table name, e.g. <code>options</code>.</li></ul>
</br>

###grabRegistrationLogTableName
***
Gets the prefixed `registration_log` table name.
</br>

###grabSignupsTableName
***
Gets the prefixed `signups` table name.
</br>

###grabSiteMetaTableName
***
Gets the prefixed `sitemeta` table name.
</br>

###grabSiteOptionFromDatabase
***
Gets a site option from the database.
#### Parameters
<ul>
<li><em>string</em> <strong>$key</strong></li></ul>
</br>

###grabSiteTableName
***
Gets the prefixed `site` table name.
</br>

###grabSiteTransientFromDatabase
***
Gets a site transient from the database.
#### Parameters
<ul>
<li><em>string</em> <strong>$key</strong></li></ul>
</br>

###grabSiteUrl
***
Returns the current site url as specified in the module configuration.
#### Parameters
<ul>
<li><em>string</em> <strong>$path</strong> = <em>null</em> - A path that should be appended to the site URL.</li></ul>
</br>

###grabTablePrefix
***
Returns the table prefix, namespaced for secondary blogs if selected.
</br>

###grabTermIdFromDatabase
***
Gets a term from the database. Looks up the prefixed `terms` table, e.g. `wp_terms`.
#### Parameters
<ul>
<li><em>array</em> <strong>$criteria</strong> - An array of search criteria.</li></ul>
</br>

###grabTermMetaTableName
***
Gets the terms meta table prefixed name. E.g.: `wp_termmeta`.
</br>

###grabTermRelationshipsTableName
***
Gets the prefixed term relationships table name, e.g. `wp_term_relationships`.
</br>

###grabTermTaxonomyIdFromDatabase
***
Gets a `term_taxonomy_id` from the database. Looks up the prefixed `terms_relationships` table, e.g. `wp_term_relationships`.
#### Parameters
<ul>
<li><em>array</em> <strong>$criteria</strong> - An array of search criteria.</li></ul>
</br>

###grabTermTaxonomyTableName
***
Gets the prefixed term and taxonomy table name, e.g. `wp_term_taxonomy`.
</br>

###grabTermsTableName
***
Gets the prefixed terms table name, e.g. `wp_terms`.
</br>

###grabUserIdFromDatabase
***
Gets the a user ID from the database using the user login.
#### Parameters
<ul>
<li><em>string</em> <strong>$userLogin</strong></li></ul>
</br>

###grabUserMetaFromDatabase
***
Gets a user meta from the database.
#### Parameters
<ul>
<li><em>int</em> <strong>$userId</strong></li>
<li><em>string</em> <strong>$meta_key</strong></li></ul>
</br>

###grabUsermetaTableName
***
Returns the prefixed `usermeta` table name, e.g. `wp_usermeta`.
</br>

###grabUsersTableName
***
Gets the users table name.
</br>

###haveAttachmentInDatabase
***
Creates the database entries representing an attachment and moves the attachment file to the right location. Requires the WPFilesystem module. should be used to build the "year/time" uploads sub-folder structure. image sizes created by default.
#### Parameters
<ul>
<li><em>string</em> <strong>$file</strong> - The absolute path to the attachment file.</li>
<li><em>string/string/int</em> <strong>$date</strong> = <em>`'now'`</em> - Either a string supported by the <code>strtotime</code> function or a UNIX timestamp that</li>
<li><em>array</em> <strong>$overrides</strong> = <em>array()</em> - An associative array of values overriding the default ones.</li>
<li><em>array</em> <strong>$imageSizes</strong> = <em>null</em> - An associative array in the format [ <size> =&gt; [<width>,<height>]] to override the</li></ul>
</br>

###haveBlogInDatabase
***
Inserts a blog in the `blogs` table. or subfolder (`true`)
#### Parameters
<ul>
<li><em>string</em> <strong>$domainOrPath</strong> - The subdomain or the path to the be used for the blog.</li>
<li><em>array</em> <strong>$overrides</strong> = <em>array()</em> - An array of values to override the defaults.</li>
<li><em>bool</em> <strong>$subdomain</strong> = <em>true</em> - Whether the new blog should be created as a subdomain (<code>true</code>)</li></ul>
</br>

###haveCommentInDatabase
***
Inserts a comment in the database.
#### Parameters
<ul>
<li><em>int</em> <strong>$comment_post_ID</strong> - The id of the post the comment refers to.</li>
<li><em>array</em> <strong>$data</strong> = <em>array()</em> - The comment data overriding default and random generated values.</li></ul>
</br>

###haveCommentMetaInDatabase
***
Inserts a comment meta field in the database. Array and object meta values will be serialized.
#### Parameters
<ul>
<li><em>int</em> <strong>$comment_id</strong></li>
<li><em>string</em> <strong>$meta_key</strong></li>
<li><em>mixed</em> <strong>$meta_value</strong></li></ul>
</br>

###haveLinkInDatabase
***
Inserts a link in the database.
#### Parameters
<ul>
<li><em>array</em> <strong>$overrides</strong> = <em>array()</em> - The data to insert.</li></ul>
</br>

###haveManyBlogsInDatabase
***
Inserts many blogs in the database.
<pre><code class="language-php">    $blogIds = $I-&gt;haveManyBlogsInDatabase(3, ['domain' =&gt;'test-{{n}}']);
    foreach($blogIds as $blogId){
         $I-&gt;useBlog($blogId);
         $I-&gt;haveManuPostsInDatabase(3);
    }</code></pre>
#### Parameters
<ul>
<li><em>int</em> <strong>$count</strong> - The number of blogs to create.</li>
<li><em>array</em> <strong>$overrides</strong> = <em>array()</em> - An array of values to override the default ones; <code>{{n}}</code> will be replaced by the count.</li>
<li><em>bool</em> <strong>$subdomain</strong> = <em>true</em> - Whether the new blogs should be created as a subdomain or subfolder.</li></ul>
</br>

###haveManyCommentsInDatabase
***
Inserts many comments in the database.
#### Parameters
<ul>
<li><em>int</em> <strong>$count</strong> - The number of comments to insert.</li>
<li><em>int</em> <strong>$comment_post_ID</strong> - The comment parent post ID.</li>
<li><em>array</em> <strong>$overrides</strong> = <em>array()</em> - An associative array to override the defaults.</li></ul>
</br>

###haveManyLinksInDatabase
***
Inserts many links in the database.
#### Parameters
<ul>
<li><em>int</em> <strong>$count</strong></li>
<li><em>array/array/null/array</em> <strong>$overrides</strong> = <em>array()</em></li></ul>
</br>

###haveManyPostsInDatabase
***
Inserts many posts in the database returning their IDs. An array of values to override the defaults. The `{{n}}` placeholder can be used to have the post count inserted in its place; e.g. `Post Title - {{n}}` will be set to `Post Title - 0` for the first post, `Post Title - 1` for the second one and so on. The same applies to meta values as well.
#### Parameters
<ul>
<li><em>int</em> <strong>$count</strong> - The number of posts to insert.</li>
<li><em>array</em> <strong>$overrides</strong> = <em>array()</em></li></ul>
</br>

###haveManyTermsInDatabase
***
Inserts many terms in the database.
#### Parameters
<ul>
<li><em>int</em> <strong>$count</strong></li>
<li><em>string</em> <strong>$name</strong> - The term name.</li>
<li><em>string</em> <strong>$taxonomy</strong> - The taxonomy name.</li>
<li><em>array</em> <strong>$overrides</strong> = <em>array()</em> - An associative array of default overrides.</li></ul>
</br>

###haveManyUsersInDatabase
***

#### Parameters
<ul>
<li><em>mixed</em> <strong>$count</strong></li>
<li><em>mixed</em> <strong>$user_login</strong></li>
<li><em>string</em> <strong>$role</strong> = <em>`'subscriber'`</em></li>
<li><em>array</em> <strong>$overrides</strong> = <em>array()</em></li></ul>
</br>

###haveMenuInDatabase
***
Creates and adds a menu to a theme location in the database.
#### Parameters
<ul>
<li><em>string</em> <strong>$slug</strong> - The menu slug.</li>
<li><em>string</em> <strong>$location</strong> - The theme menu location the menu will be assigned to.</li>
<li><em>array</em> <strong>$overrides</strong> = <em>array()</em> - An array of values to override the defaults.</li></ul>
</br>

###haveMenuItemInDatabase
***
Adds a menu element to a menu for the current theme. meta.
#### Parameters
<ul>
<li><em>string</em> <strong>$menuSlug</strong> - The menu slug the item should be added to.</li>
<li><em>string</em> <strong>$title</strong> - The menu item title.</li>
<li><em>int/null</em> <strong>$menuOrder</strong> = <em>null</em> - An optional menu order, <code>1</code> based.</li>
<li><em>array/array/null/array</em> <strong>$meta</strong> = <em>array()</em> - An associative array that will be prefixed with <code>_menu_item_</code> for the item post</li></ul>
</br>

###haveOptionInDatabase
***
Inserts an option in the database. If the option value is an object or an array then the value will be serialized.
#### Parameters
<ul>
<li><em>string</em> <strong>$option_name</strong></li>
<li><em>mixed</em> <strong>$option_value</strong></li>
<li><em>string</em> <strong>$autoload</strong> = <em>`'yes'`</em></li></ul>
</br>

###havePageInDatabase
***
Inserts a page in the database.
#### Parameters
<ul>
<li><em>array</em> <strong>$overrides</strong> = <em>array()</em> - An array of values to override the default ones.</li></ul>
</br>

###havePostInDatabase
***
Inserts a post in the database.
#### Parameters
<ul>
<li><em>array</em> <strong>$data</strong> = <em>array()</em> - An associative array of post data to override default and random generated values.</li></ul>
</br>

###havePostmetaInDatabase
***
Adds one or more meta key and value couples in the database for a post.
#### Parameters
<ul>
<li><em>int</em> <strong>$postId</strong> - The post ID.</li>
<li><em>string</em> <strong>$meta_key</strong> - The meta key.</li>
<li><em>mixed</em> <strong>$meta_value</strong> - The value to insert in the database, objects and arrays will be serialized.</li></ul>
</br>

###haveSiteOptionInDatabase
***
Inserts a site option in the database. If the value is an array or an object then the value will be serialized.
#### Parameters
<ul>
<li><em>string</em> <strong>$key</strong></li>
<li><em>mixed</em> <strong>$value</strong></li></ul>
</br>

###haveSiteTransientInDatabase
***
Inserts a site transient in the database. If the value is an array or an object then the value will be serialized.
#### Parameters
<ul>
<li><em>mixed</em> <strong>$key</strong></li>
<li><em>mixed</em> <strong>$value</strong></li></ul>
</br>

###haveTermInDatabase
***
Inserts a term in the database.
#### Parameters
<ul>
<li><em>string</em> <strong>$name</strong> - The term name, e.g. &quot;Fuzzy&quot;.</li>
<li><em>string</em> <strong>$taxonomy</strong> - The term taxonomy</li>
<li><em>array</em> <strong>$overrides</strong> = <em>array()</em> - An array of values to override the default ones.</li></ul>
</br>

###haveTermMetaInDatabase
***
Inserts a term meta row in the database. Objects and array meta values will be serialized.
#### Parameters
<ul>
<li><em>int</em> <strong>$term_id</strong></li>
<li><em>string</em> <strong>$meta_key</strong></li>
<li><em>mixed</em> <strong>$meta_value</strong></li></ul>
</br>

###haveTermRelationshipInDatabase
***
Creates a term relationship in the database. No check about the consistency of the insertion is made. E.g. a post could be assigned a term from a taxonomy that's not registered for that post type.
#### Parameters
<ul>
<li><em>int</em> <strong>$object_id</strong> - A post ID, a user ID or anything that can be assigned a taxonomy term.</li>
<li><em>int</em> <strong>$term_taxonomy_id</strong></li>
<li><em>int</em> <strong>$term_order</strong> - Defaults to <code>0</code>.</li></ul>
</br>

###haveTransientInDatabase
***
Inserts a transient in the database. If the value is an array or an object then the value will be serialized.
#### Parameters
<ul>
<li><em>string</em> <strong>$transient</strong></li>
<li><em>mixed</em> <strong>$value</strong></li></ul>
</br>

###haveUserCapabilitiesInDatabase
***
Sets a user capabilities. for a multisite installation; e.g. `[1 => 'administrator`, 2 => 'subscriber']`.
#### Parameters
<ul>
<li><em>int</em> <strong>$userId</strong></li>
<li><em>string/array</em> <strong>$role</strong> - Either a role string (e.g. <code>administrator</code>) or an associative array of blog IDs/roles</li></ul>
</br>

###haveUserInDatabase
***
Inserts a user and appropriate meta in the database. and "usermeta" table.
#### Parameters
<ul>
<li><em>string</em> <strong>$user_login</strong> - The user login slug</li>
<li><em>string</em> <strong>$role</strong> = <em>`'subscriber'`</em> - The user role slug, e.g. &quot;administrator&quot;; defaults to &quot;subscriber&quot;.</li>
<li><em>array</em> <strong>$overrides</strong> = <em>array()</em> - An associative array of column names and values overridind defaults in the &quot;users&quot;</li></ul>
</br>

###haveUserLevelsInDatabase
***
Sets the user level in the database for a user. multisite installation.
#### Parameters
<ul>
<li><em>int</em> <strong>$userId</strong></li>
<li><em>string/array</em> <strong>$role</strong> - Either a role string (e.g. <code>administrator</code>) or an array of blog IDs/roles for a</li></ul>
</br>

###haveUserMetaInDatabase
***
Sets a user meta. values will trigger the insertion of multiple rows.
#### Parameters
<ul>
<li><em>int</em> <strong>$userId</strong></li>
<li><em>string</em> <strong>$meta_key</strong></li>
<li><em>mixed</em> <strong>$meta_value</strong> - Either a single value or an array of values; objects will be serialized while array of</li></ul>
</br>

###importSqlDumpFile
***
Import the SQL dump file if populate is enabled. Specifying a dump file that file will be imported.
#### Parameters
<ul>
<li><em>null/string</em> <strong>$dumpFile</strong> = <em>null</em> - The dump file that should be imported in place of the default one.</li></ul>
</br>

###seeAttachmentInDatabase
***
Checks for an attachment in the database.
#### Parameters
<ul>
<li><em>array</em> <strong>$criteria</strong> - An array of search criteria.</li></ul>
</br>

###seeBlogInDatabase
***
Checks for a blog in the database, looks up the `blogs` table.
#### Parameters
<ul>
<li><em>array</em> <strong>$criteria</strong> - An array of search criteria.</li></ul>
</br>

###seeCommentInDatabase
***
Checks for a comment in the database. Will look up the "comments" table.
#### Parameters
<ul>
<li><em>array</em> <strong>$criteria</strong></li></ul>
</br>

###seeCommentMetaInDatabase
***
Checks that a comment meta value is in the database. Will look up the "commentmeta" table.
#### Parameters
<ul>
<li><em>array</em> <strong>$criteria</strong></li></ul>
</br>

###seeLinkInDatabase
***
Checks for a link in the database. Will look up the "links" table.
#### Parameters
<ul>
<li><em>array</em> <strong>$criteria</strong> - An array of search criteria.</li></ul>
</br>

###seeOptionInDatabase
***
Checks if an option is in the database for the current blog. If checking for an array or an object then the serialized version will be checked for.
#### Parameters
<ul>
<li><em>array</em> <strong>$criteria</strong> - An array of search criteria.</li></ul>
</br>

###seePageInDatabase
***
Checks for a page in the database.
#### Parameters
<ul>
<li><em>array</em> <strong>$criteria</strong> - An array of search criteria.</li></ul>
</br>

###seePostInDatabase
***
Checks for a post in the database.
#### Parameters
<ul>
<li><em>array</em> <strong>$criteria</strong> - An array of search criteria.</li></ul>
</br>

###seePostMetaInDatabase
***
Checks for a post meta value in the database for the current blog. If the `meta_value` is an object or an array then the serialized value will be checked for.
#### Parameters
<ul>
<li><em>array</em> <strong>$criteria</strong> - An array of search criteria.</li></ul>
</br>

###seePostWithTermInDatabase
***
Checks that a post to term relation exists in the database. Will look up the "term_relationships" table.
#### Parameters
<ul>
<li><em>int</em> <strong>$post_id</strong> - The post ID.</li>
<li><em>int</em> <strong>$term_id</strong> - The term ID.</li>
<li><em>integer</em> <strong>$term_order</strong> - The order the term applies to the post, defaults to 0.</li></ul>
</br>

###seeSiteOptionInDatabase
***
Checks that a site option is in the database.
#### Parameters
<ul>
<li><em>string</em> <strong>$key</strong></li>
<li><em>mixed/null</em> <strong>$value</strong> = <em>null</em></li></ul>
</br>

###seeSiteSiteTransientInDatabase
***
Checks that a site option is in the database.
#### Parameters
<ul>
<li><em>string</em> <strong>$key</strong></li>
<li><em>mixed/null</em> <strong>$value</strong> = <em>null</em></li></ul>
</br>

###seeTableInDatabase
***
Checks that a table is in the database.
<pre><code class="language-php">    $options = $I-&gt;grabPrefixedTableNameFor('options');
    $I-&gt;seeTableInDatabase($options);</code></pre>
#### Parameters
<ul>
<li><em>string</em> <strong>$table</strong> - The full table name, including the table prefix.</li></ul>
</br>

###seeTermInDatabase
***
Checks for a term in the database. Looks up the `terms` and `term_taxonomy` prefixed tables. `term_taxonomy` tables.
#### Parameters
<ul>
<li><em>array</em> <strong>$criteria</strong> - An array of criteria to search for the term, can be columns from the <code>terms</code> and the</li></ul>
</br>

###seeTermMetaInDatabase
***
Checks for a term meta in the database.
#### Parameters
<ul>
<li><em>array</em> <strong>$criteria</strong> - An array of search criteria.</li></ul>
</br>

###seeTermRelationshipInDatabase
***
Checks for a term relationship in the database.
#### Parameters
<ul>
<li><em>array</em> <strong>$criteria</strong> - An array of search criteria.</li></ul>
</br>

###seeTermTaxonomyInDatabase
***
Checks for a term taxonomy in the database. Will look up the prefixed `term_taxonomy` table, e.g. `wp_term_taxonomy`.
#### Parameters
<ul>
<li><em>array</em> <strong>$criteria</strong> - An array of search criteria.</li></ul>
</br>

###seeUserInDatabase
***
Checks that a user is in the database. Will look up the "users" table.
#### Parameters
<ul>
<li><em>array</em> <strong>$criteria</strong></li></ul>
</br>

###seeUserMetaInDatabase
***
Checks for a user meta value in the database.
#### Parameters
<ul>
<li><em>array</em> <strong>$criteria</strong> - An array of search criteria.</li></ul>
</br>

###useBlog
***
Sets the blog to be used.
#### Parameters
<ul>
<li><em>int</em> <strong>$id</strong></li></ul>
</br>

###useMainBlog
***
Sets the current blog to the main one (`blog_id` 1).
</br>

###useTheme
***
Sets the current theme options.
#### Parameters
<ul>
<li><em>string</em> <strong>$stylesheet</strong> - The theme stylesheet slug, e.g. <code>twentysixteen</code>.</li>
<li><em>string/null</em> <strong>$template</strong> = <em>null</em> - The theme template slug, e.g. <code>twentysixteen</code>, defaults to <code>$stylesheet</code>.</li>
<li><em>string/null</em> <strong>$themeName</strong> = <em>null</em> - The theme name, e.g. <code>Twentysixteen</code>, defaults to title version of <code>$stylesheet</code>.</li></ul>
</br>
</br>

*This class extends \Codeception\Module\Db*

*This class implements \Codeception\Lib\Interfaces\Db*

<!--/doc-->

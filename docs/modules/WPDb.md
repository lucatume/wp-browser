# WPDb module
This module should be used in acceptance and functional tests, see [levels of testing for more information](./../levels-of-testing.md).  
This module extends the [Db module](https://codeception.com/docs/modules/Db) adding WordPress-specific configuration parameters and methods.  
The module provides methods to read, write and update the WordPress database **directly**, without relying on WordPress methods, using WordPress functions or triggering WordPress filters.  

<!--doc-->


<h2>Public API</h2>
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

<h4 id="countRowsInDatabase">countRowsInDatabase</h4>

***

Returns the number of table rows matching a criteria.
<pre><code class="language-php">    $I-&gt;haveManyPostsInDatabase(3, ['post_status' =&gt; 'draft' ]);
    $I-&gt;haveManyPostsInDatabase(3, ['post_status' =&gt; 'private' ]);
    // Make sure there are now the expected number of draft posts.
    $postsTable = $I-&gt;grabPostsTableName();
    $I-&gt;countRowsInDatabase($postsTable, ['post_status' =&gt; 'draft']);</code></pre>
<h5>Parameters</h5><ul>
<li><em>string</em> <strong>$table</strong> - The table to count the rows in.</li>
<li><em>array</em> <strong>$criteria</strong> = <em>array()</em> - Search criteria, if empty all table rows will be counted.</li></ul>

<h4 id="dontHaveAttachmentFilesInDatabase">dontHaveAttachmentFilesInDatabase</h4>

***

Removes all the files attached with an attachment post, it will not remove the database entries. Requires the `WPFilesystem` module to be loaded in the suite.
<pre><code class="language-php">    $posts = $I-&gt;grabPostsTableName();
    $attachmentIds = $I-&gt;grabColumnFromDatabase($posts, 'ID', ['post_type' =&gt; 'attachment']);
    // This will only remove the files, not the database entries.
    $I-&gt;dontHaveAttachmentFilesInDatabase($attachmentIds);</code></pre>
<h5>Parameters</h5><ul>
<li><em>array/int</em> <strong>$attachmentIds</strong> - An attachment post ID or an array of attachment post IDs.</li></ul>

<h4 id="dontHaveAttachmentInDatabase">dontHaveAttachmentInDatabase</h4>

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
<h5>Parameters</h5><ul>
<li><em>array</em> <strong>$criteria</strong> - An array of search criteria to find the attachment post in the posts table.</li>
<li><em>bool</em> <strong>$purgeMeta</strong> = <em>true</em> - If set to <code>true</code> then the meta for the attachment will be purged too.</li>
<li><em>bool</em> <strong>$removeFiles</strong> = <em>false</em> - Remove all files too, requires the <code>WPFilesystem</code> module to be loaded in the suite.</li></ul>

<h4 id="dontHaveBlogInDatabase">dontHaveBlogInDatabase</h4>

***

Removes a blog entry and tables from the database.
<h5>Parameters</h5><ul>
<li><em>array</em> <strong>$criteria</strong> - An array of search criteria to find the blog row in the blogs table.</li>
<li><em>bool</em> <strong>$removeTables</strong> = <em>true</em> - Remove the blog tables.</li>
<li><em>bool</em> <strong>$removeUploads</strong> = <em>true</em> - Remove the blog uploads; requires the <code>WPFilesystem</code> module to be loaded in the suite.</li></ul>

<h4 id="dontHaveCommentInDatabase">dontHaveCommentInDatabase</h4>

***

Removes an entry from the comments table.
<h5>Parameters</h5><ul>
<li><em>array</em> <strong>$criteria</strong> - An array of search criteria.</li>
<li><em>bool</em> <strong>$purgeMeta</strong> = <em>true</em> - If set to <code>true</code> then the meta for the comment will be purged too.</li></ul>

<h4 id="dontHaveCommentMetaInDatabase">dontHaveCommentMetaInDatabase</h4>

***

Removes an entry from the commentmeta table.
<h5>Parameters</h5><ul>
<li><em>array</em> <strong>$criteria</strong> - An array of search criteria.</li></ul>

<h4 id="dontHaveInDatabase">dontHaveInDatabase</h4>

***

Deletes a database entry.
<h5>Parameters</h5><ul>
<li><em>string</em> <strong>$table</strong> - The table name.</li>
<li><em>array</em> <strong>$criteria</strong> - An associative array of the column names and values to use as deletion criteria.</li></ul>

<h4 id="dontHaveLinkInDatabase">dontHaveLinkInDatabase</h4>

***

Removes a link from the database.
<h5>Parameters</h5><ul>
<li><em>array</em> <strong>$criteria</strong> - An array of search criteria.</li></ul>

<h4 id="dontHaveOptionInDatabase">dontHaveOptionInDatabase</h4>

***

Removes an entry from the options table.
<h5>Parameters</h5><ul>
<li><em>mixed</em> <strong>$key</strong></li>
<li><em>null</em> <strong>$value</strong> = <em>null</em></li></ul>

<h4 id="dontHavePostInDatabase">dontHavePostInDatabase</h4>

***

Removes an entry from the posts table.
<h5>Parameters</h5><ul>
<li><em>array</em> <strong>$criteria</strong> - An array of search criteria.</li>
<li><em>bool</em> <strong>$purgeMeta</strong> = <em>true</em> - If set to <code>true</code> then the meta for the post will be purged too.</li></ul>

<h4 id="dontHavePostMetaInDatabase">dontHavePostMetaInDatabase</h4>

***

Removes an entry from the postmeta table.
<h5>Parameters</h5><ul>
<li><em>array</em> <strong>$criteria</strong> - An array of search criteria.</li></ul>

<h4 id="dontHaveSiteOptionInDatabase">dontHaveSiteOptionInDatabase</h4>

***

Removes a site option from the database.
<h5>Parameters</h5><ul>
<li><em>mixed</em> <strong>$key</strong></li>
<li><em>null</em> <strong>$value</strong> = <em>null</em></li></ul>

<h4 id="dontHaveSiteTransientInDatabase">dontHaveSiteTransientInDatabase</h4>

***

Removes a site transient from the database.
<h5>Parameters</h5><ul>
<li><em>string</em> <strong>$key</strong></li></ul>

<h4 id="dontHaveTableInDatabase">dontHaveTableInDatabase</h4>

***

Removes a table from the database. The case where a table does not exist is handled without raising an error.
<pre><code class="language-php">    $ordersTable = $I-&gt;grabPrefixedTableNameFor('orders');
    $I-&gt;dontHaveTableInDatabase($ordersTable);</code></pre>
<h5>Parameters</h5><ul>
<li><em>string</em> <strong>$fullTableName</strong> - The full table name, including the table prefix.</li></ul>

<h4 id="dontHaveTermInDatabase">dontHaveTermInDatabase</h4>

***

Removes a term from the database.
<h5>Parameters</h5><ul>
<li><em>array</em> <strong>$criteria</strong> - An array of search criteria.</li>
<li><em>bool</em> <strong>$purgeMeta</strong> = <em>true</em> - Whether the terms meta should be purged along side with the meta or not.</li></ul>

<h4 id="dontHaveTermMetaInDatabase">dontHaveTermMetaInDatabase</h4>

***

Removes a term meta from the database.
<h5>Parameters</h5><ul>
<li><em>array</em> <strong>$criteria</strong> - An array of search criteria.</li></ul>

<h4 id="dontHaveTermRelationshipInDatabase">dontHaveTermRelationshipInDatabase</h4>

***

Removes an entry from the term_relationships table.
<h5>Parameters</h5><ul>
<li><em>array</em> <strong>$criteria</strong> - An array of search criteria.</li></ul>

<h4 id="dontHaveTermTaxonomyInDatabase">dontHaveTermTaxonomyInDatabase</h4>

***

Removes an entry from the term_taxonomy table.
<h5>Parameters</h5><ul>
<li><em>array</em> <strong>$criteria</strong> - An array of search criteria.</li></ul>

<h4 id="dontHaveTransientInDatabase">dontHaveTransientInDatabase</h4>

***

Removes a transient from the database.
<h5>Parameters</h5><ul>
<li><em>mixed</em> <strong>$transient</strong></li></ul>

<h4 id="dontHaveUserInDatabase">dontHaveUserInDatabase</h4>

***

Removes a user from the database.
<h5>Parameters</h5><ul>
<li><em>int/string</em> <strong>$userIdOrLogin</strong></li>
<li><em>bool</em> <strong>$purgeMeta</strong> = <em>true</em> - Whether the user meta should be purged alongside the user or not.</li></ul>

<h4 id="dontHaveUserInDatabaseWithEmail">dontHaveUserInDatabaseWithEmail</h4>

***

Removes a user(s) from the database using the user email address.
<h5>Parameters</h5><ul>
<li><em>string</em> <strong>$userEmail</strong></li>
<li><em>bool</em> <strong>$purgeMeta</strong> = <em>true</em> - Whether the user meta should be purged alongside the user or not.</li></ul>

<h4 id="dontHaveUserMetaInDatabase">dontHaveUserMetaInDatabase</h4>

***

Removes an entry from the usermeta table.
<h5>Parameters</h5><ul>
<li><em>array</em> <strong>$criteria</strong> - An array of search criteria.</li></ul>

<h4 id="dontSeeAttachmentInDatabase">dontSeeAttachmentInDatabase</h4>

***

Checks that an attachment is not in the database.
<h5>Parameters</h5><ul>
<li><em>array</em> <strong>$criteria</strong> - An array of search criteria.</li></ul>

<h4 id="dontSeeBlogInDatabase">dontSeeBlogInDatabase</h4>

***

Checks that a row is not present in the `blogs` table.
<h5>Parameters</h5><ul>
<li><em>array</em> <strong>$criteria</strong> - An array of search criteria.</li></ul>

<h4 id="dontSeeCommentInDatabase">dontSeeCommentInDatabase</h4>

***

Checks that a comment is not in the database. Will look up the "comments" table.
<h5>Parameters</h5><ul>
<li><em>array</em> <strong>$criteria</strong></li></ul>

<h4 id="dontSeeCommentMetaInDatabase">dontSeeCommentMetaInDatabase</h4>

***

Checks that a comment meta value is not in the database. Will look up the "commentmeta" table.
<h5>Parameters</h5><ul>
<li><em>array</em> <strong>$criteria</strong></li></ul>

<h4 id="dontSeeLinkInDatabase">dontSeeLinkInDatabase</h4>

***

Checks that a link is not in the database. Will look up the "links" table.
<h5>Parameters</h5><ul>
<li><em>array</em> <strong>$criteria</strong> - An array of search criteria.</li></ul>

<h4 id="dontSeeOptionInDatabase">dontSeeOptionInDatabase</h4>

***

Checks that an option is not in the database for the current blog. If the value is an object or an array then the serialized option will be checked for.
<h5>Parameters</h5><ul>
<li><em>array</em> <strong>$criteria</strong> - An array of search criteria.</li></ul>

<h4 id="dontSeePageInDatabase">dontSeePageInDatabase</h4>

***

Checks that a page is not in the database.
<h5>Parameters</h5><ul>
<li><em>array</em> <strong>$criteria</strong> - An array of search criteria.</li></ul>

<h4 id="dontSeePostInDatabase">dontSeePostInDatabase</h4>

***

Checks that a post is not in the database.
<h5>Parameters</h5><ul>
<li><em>array</em> <strong>$criteria</strong> - An array of search criteria.</li></ul>

<h4 id="dontSeePostMetaInDatabase">dontSeePostMetaInDatabase</h4>

***

Checks that a post meta value is not there. If the meta value is an object or an array then the serialized version will be checked for.
<h5>Parameters</h5><ul>
<li><em>array</em> <strong>$criteria</strong> - An array of search criteria.</li></ul>

<h4 id="dontSeeTableInDatabase">dontSeeTableInDatabase</h4>

***

Checks that a table is not in the database.
<pre><code class="language-php">    $options = $I-&gt;grabPrefixedTableNameFor('options');
    $I-&gt;dontHaveTableInDatabase($options)
    $I-&gt;dontSeeTableInDatabase($options);</code></pre>
<h5>Parameters</h5><ul>
<li><em>string</em> <strong>$table</strong> - The full table name, including the table prefix.</li></ul>

<h4 id="dontSeeTermInDatabase">dontSeeTermInDatabase</h4>

***

Makes sure a term is not in the database. Looks up both the `terms` table and the `term_taxonomy` tables. `term_taxonomy` tables.
<h5>Parameters</h5><ul>
<li><em>array</em> <strong>$criteria</strong> - An array of criteria to search for the term, can be columns from the <code>terms</code> and the</li></ul>

<h4 id="dontSeeTermMetaInDatabase">dontSeeTermMetaInDatabase</h4>

***

Checks that a term meta is not in the database.
<h5>Parameters</h5><ul>
<li><em>array</em> <strong>$criteria</strong> - An array of search criteria.</li></ul>

<h4 id="dontSeeTermTaxonomyInDatabase">dontSeeTermTaxonomyInDatabase</h4>

***

Checks that a term taxonomy is not in the database. Will look up the prefixed `term_taxonomy` table, e.g. `wp_term_taxonomy`.
<h5>Parameters</h5><ul>
<li><em>array</em> <strong>$criteria</strong> - An array of search criteria.</li></ul>

<h4 id="dontSeeUserInDatabase">dontSeeUserInDatabase</h4>

***

Checks that a user is not in the database.
<h5>Parameters</h5><ul>
<li><em>array</em> <strong>$criteria</strong> - An array of search criteria.</li></ul>

<h4 id="dontSeeUserMetaInDatabase">dontSeeUserMetaInDatabase</h4>

***

Check that a user meta value is not in the database.
<h5>Parameters</h5><ul>
<li><em>array</em> <strong>$criteria</strong> - An array of search criteria.</li></ul>

<h4 id="getSiteDomain">getSiteDomain</h4>

***

Returns the site domain inferred from the `url` set in the config.

<h4 id="grabAllFromDatabase">grabAllFromDatabase</h4>

***

Returns all entries matching a criteria from the database.
<h5>Parameters</h5><ul>
<li><em>string</em> <strong>$table</strong></li>
<li><em>string</em> <strong>$column</strong></li>
<li><em>array</em> <strong>$criteria</strong></li></ul>

<h4 id="grabAttachmentAttachedFile">grabAttachmentAttachedFile</h4>

***

Returns the path, as stored in the database, of an attachment `_wp_attached_file` meta. The attached file is, usually, an attachment origal file.
<pre><code class="language-php">    $file = $I-&gt;grabAttachmentAttachedFile($attachmentId);
    $fileInfo = new SplFileInfo($file);
    $I-&gt;assertEquals('jpg', $fileInfo-&gt;getExtension());</code></pre>
<h5>Parameters</h5><ul>
<li><em>int</em> <strong>$attachmentPostId</strong> - The attachment post ID.</li></ul>

<h4 id="grabAttachmentMetadata">grabAttachmentMetadata</h4>

***

Returns the metadata array for an attachment post. This is the value of the `_wp_attachment_metadata` meta.
<pre><code class="language-php">    $metadata = $I-&gt;grabAttachmentMetadata($attachmentId);
    $I-&gt;assertEquals(['thumbnail', 'medium', 'medium_large'], array_keys($metadata['sizes']);</code></pre>
<h5>Parameters</h5><ul>
<li><em>int</em> <strong>$attachmentPostId</strong> - The attachment post ID.</li></ul>

<h4 id="grabBlogDomain">grabBlogDomain</h4>

***


<h5>Parameters</h5><ul>
<li><em>mixed</em> <strong>$blogId</strong></li></ul>

<h4 id="grabBlogPath">grabBlogPath</h4>

***

Grabs a blog domain from the blogs table.
<pre><code class="language-php">    $blogId = $I-&gt;haveBlogInDatabase('test');
    $path = $I-&gt;grabBlogDomain($blogId);
    $I-&gt;amOnSubdomain($path);
    $I-&gt;amOnPage('/');</code></pre>
<h5>Parameters</h5><ul>
<li><em>int</em> <strong>$blogId</strong> - The blog ID.</li></ul>

<h4 id="grabBlogTableName">grabBlogTableName</h4>

***

Returns the full name of a table for a blog from a multisite installation database.
<pre><code class="language-php">    $blogOptionTable = $I-&gt;grabBlogTableName($blogId, 'option');</code></pre>
<h5>Parameters</h5><ul>
<li><em>int</em> <strong>$blogId</strong> - The blog ID.</li>
<li><em>string</em> <strong>$table</strong> - The table name, without table prefix.</li></ul>

<h4 id="grabBlogTableNames">grabBlogTableNames</h4>

***

Returns a list of tables for a blog ID.
<pre><code class="language-php">    $blogId = $I-&gt;haveBlogInDatabase('test');
    $tables = $I-&gt;grabBlogTableNames($blogId);
    $options = array_filter($tables, function($tableName){
         return str_pos($tableName, 'options') !== false;
    });</code></pre>
<h5>Parameters</h5><ul>
<li><em>int</em> <strong>$blogId</strong> - The ID of the blog to fetch the tables for.</li></ul>

<h4 id="grabBlogTablePrefix">grabBlogTablePrefix</h4>

***

Returns the table prefix for a blog.
<pre><code class="language-php">    $blogId = $I-&gt;haveBlogInDatabase('test');
    $blogTablePrefix = $I-&gt;getBlogTablePrefix($blogId);
    $blogOrders = $I-&gt;blogTablePrefix . 'orders';</code></pre>
<h5>Parameters</h5><ul>
<li><em>int</em> <strong>$blogId</strong> - The blog ID.</li></ul>

<h4 id="grabBlogVersionsTableName">grabBlogVersionsTableName</h4>

***

Gets the prefixed `blog_versions` table name.

<h4 id="grabBlogsTableName">grabBlogsTableName</h4>

***

Gets the prefixed `blogs` table name.

<h4 id="grabCommentmetaTableName">grabCommentmetaTableName</h4>

***

Returns the prefixed comment meta table name. E.g. `wp_commentmeta`.

<h4 id="grabCommentsTableName">grabCommentsTableName</h4>

***

Gets the comments table name.

<h4 id="grabLatestEntryByFromDatabase">grabLatestEntryByFromDatabase</h4>

***

Returns the id value of the last table entry.
<h5>Parameters</h5><ul>
<li><em>string</em> <strong>$tableName</strong></li>
<li><em>string</em> <strong>$idColumn</strong> = <em>`'ID'`</em></li></ul>

<h4 id="grabLinksTableName">grabLinksTableName</h4>

***

Returns the prefixed links table name. E.g. `wp_links`.

<h4 id="grabOptionFromDatabase">grabOptionFromDatabase</h4>

***

Gets an option from the database.
<h5>Parameters</h5><ul>
<li><em>string</em> <strong>$option_name</strong></li></ul>

<h4 id="grabPostMetaFromDatabase">grabPostMetaFromDatabase</h4>

***

Gets the value of one or more post meta values from the database.
<pre><code class="language-php">    $thumbnail_id = $I-&gt;grabPostMetaFromDatabase($postId, '_thumbnail_id', true);</code></pre>
<h5>Parameters</h5><ul>
<li><em>int</em> <strong>$postId</strong> - The post ID.</li>
<li><em>string</em> <strong>$metaKey</strong> - The key of the meta to retrieve.</li>
<li><em>bool</em> <strong>$single</strong> = <em>false</em> - Whether to return a single meta value or an arrya of all available meta values.</li></ul>

<h4 id="grabPostmetaTableName">grabPostmetaTableName</h4>

***

Returns the prefixed post meta table name.

<h4 id="grabPostsTableName">grabPostsTableName</h4>

***

Gets the posts table name.

<h4 id="grabPrefixedTableNameFor">grabPrefixedTableNameFor</h4>

***

Returns a prefixed table name for the current blog. If the table is not one to be prefixed (e.g. `users`) then the proper table name will be returned.
<h5>Parameters</h5><ul>
<li><em>string</em> <strong>$tableName</strong> = <em>`''`</em> - The table name, e.g. <code>options</code>.</li></ul>

<h4 id="grabRegistrationLogTableName">grabRegistrationLogTableName</h4>

***

Gets the prefixed `registration_log` table name.

<h4 id="grabSignupsTableName">grabSignupsTableName</h4>

***

Gets the prefixed `signups` table name.

<h4 id="grabSiteMetaTableName">grabSiteMetaTableName</h4>

***

Gets the prefixed `sitemeta` table name.

<h4 id="grabSiteOptionFromDatabase">grabSiteOptionFromDatabase</h4>

***

Gets a site option from the database.
<h5>Parameters</h5><ul>
<li><em>string</em> <strong>$key</strong></li></ul>

<h4 id="grabSiteTableName">grabSiteTableName</h4>

***

Gets the prefixed `site` table name.

<h4 id="grabSiteTransientFromDatabase">grabSiteTransientFromDatabase</h4>

***

Gets a site transient from the database.
<h5>Parameters</h5><ul>
<li><em>string</em> <strong>$key</strong></li></ul>

<h4 id="grabSiteUrl">grabSiteUrl</h4>

***

Returns the current site url as specified in the module configuration.
<h5>Parameters</h5><ul>
<li><em>string</em> <strong>$path</strong> = <em>null</em> - A path that should be appended to the site URL.</li></ul>

<h4 id="grabTablePrefix">grabTablePrefix</h4>

***

Returns the table prefix, namespaced for secondary blogs if selected.

<h4 id="grabTermIdFromDatabase">grabTermIdFromDatabase</h4>

***

Gets a term from the database. Looks up the prefixed `terms` table, e.g. `wp_terms`.
<h5>Parameters</h5><ul>
<li><em>array</em> <strong>$criteria</strong> - An array of search criteria.</li></ul>

<h4 id="grabTermMetaTableName">grabTermMetaTableName</h4>

***

Gets the terms meta table prefixed name. E.g.: `wp_termmeta`.

<h4 id="grabTermRelationshipsTableName">grabTermRelationshipsTableName</h4>

***

Gets the prefixed term relationships table name, e.g. `wp_term_relationships`.

<h4 id="grabTermTaxonomyIdFromDatabase">grabTermTaxonomyIdFromDatabase</h4>

***

Gets a `term_taxonomy_id` from the database. Looks up the prefixed `terms_relationships` table, e.g. `wp_term_relationships`.
<h5>Parameters</h5><ul>
<li><em>array</em> <strong>$criteria</strong> - An array of search criteria.</li></ul>

<h4 id="grabTermTaxonomyTableName">grabTermTaxonomyTableName</h4>

***

Gets the prefixed term and taxonomy table name, e.g. `wp_term_taxonomy`.

<h4 id="grabTermsTableName">grabTermsTableName</h4>

***

Gets the prefixed terms table name, e.g. `wp_terms`.

<h4 id="grabUserIdFromDatabase">grabUserIdFromDatabase</h4>

***

Gets the a user ID from the database using the user login.
<h5>Parameters</h5><ul>
<li><em>string</em> <strong>$userLogin</strong></li></ul>

<h4 id="grabUserMetaFromDatabase">grabUserMetaFromDatabase</h4>

***

Gets a user meta from the database.
<h5>Parameters</h5><ul>
<li><em>int</em> <strong>$userId</strong></li>
<li><em>string</em> <strong>$meta_key</strong></li></ul>

<h4 id="grabUsermetaTableName">grabUsermetaTableName</h4>

***

Returns the prefixed `usermeta` table name, e.g. `wp_usermeta`.

<h4 id="grabUsersTableName">grabUsersTableName</h4>

***

Gets the users table name.

<h4 id="haveAttachmentInDatabase">haveAttachmentInDatabase</h4>

***

Creates the database entries representing an attachment and moves the attachment file to the right location. Requires the WPFilesystem module. should be used to build the "year/time" uploads sub-folder structure. image sizes created by default.
<h5>Parameters</h5><ul>
<li><em>string</em> <strong>$file</strong> - The absolute path to the attachment file.</li>
<li><em>string/string/int</em> <strong>$date</strong> = <em>`'now'`</em> - Either a string supported by the <code>strtotime</code> function or a UNIX timestamp that</li>
<li><em>array</em> <strong>$overrides</strong> = <em>array()</em> - An associative array of values overriding the default ones.</li>
<li><em>array</em> <strong>$imageSizes</strong> = <em>null</em> - An associative array in the format [ <size> =&gt; [<width>,<height>]] to override the</li></ul>

<h4 id="haveBlogInDatabase">haveBlogInDatabase</h4>

***

Inserts a blog in the `blogs` table. or subfolder (`true`)
<h5>Parameters</h5><ul>
<li><em>string</em> <strong>$domainOrPath</strong> - The subdomain or the path to the be used for the blog.</li>
<li><em>array</em> <strong>$overrides</strong> = <em>array()</em> - An array of values to override the defaults.</li>
<li><em>bool</em> <strong>$subdomain</strong> = <em>true</em> - Whether the new blog should be created as a subdomain (<code>true</code>)</li></ul>

<h4 id="haveCommentInDatabase">haveCommentInDatabase</h4>

***

Inserts a comment in the database.
<h5>Parameters</h5><ul>
<li><em>int</em> <strong>$comment_post_ID</strong> - The id of the post the comment refers to.</li>
<li><em>array</em> <strong>$data</strong> = <em>array()</em> - The comment data overriding default and random generated values.</li></ul>

<h4 id="haveCommentMetaInDatabase">haveCommentMetaInDatabase</h4>

***

Inserts a comment meta field in the database. Array and object meta values will be serialized.
<h5>Parameters</h5><ul>
<li><em>int</em> <strong>$comment_id</strong></li>
<li><em>string</em> <strong>$meta_key</strong></li>
<li><em>mixed</em> <strong>$meta_value</strong></li></ul>

<h4 id="haveLinkInDatabase">haveLinkInDatabase</h4>

***

Inserts a link in the database.
<h5>Parameters</h5><ul>
<li><em>array</em> <strong>$overrides</strong> = <em>array()</em> - The data to insert.</li></ul>

<h4 id="haveManyBlogsInDatabase">haveManyBlogsInDatabase</h4>

***

Inserts many blogs in the database.
<pre><code class="language-php">    $blogIds = $I-&gt;haveManyBlogsInDatabase(3, ['domain' =&gt;'test-{{n}}']);
    foreach($blogIds as $blogId){
         $I-&gt;useBlog($blogId);
         $I-&gt;haveManuPostsInDatabase(3);
    }</code></pre>
<h5>Parameters</h5><ul>
<li><em>int</em> <strong>$count</strong> - The number of blogs to create.</li>
<li><em>array</em> <strong>$overrides</strong> = <em>array()</em> - An array of values to override the default ones; <code>{{n}}</code> will be replaced by the count.</li>
<li><em>bool</em> <strong>$subdomain</strong> = <em>true</em> - Whether the new blogs should be created as a subdomain or subfolder.</li></ul>

<h4 id="haveManyCommentsInDatabase">haveManyCommentsInDatabase</h4>

***

Inserts many comments in the database.
<h5>Parameters</h5><ul>
<li><em>int</em> <strong>$count</strong> - The number of comments to insert.</li>
<li><em>int</em> <strong>$comment_post_ID</strong> - The comment parent post ID.</li>
<li><em>array</em> <strong>$overrides</strong> = <em>array()</em> - An associative array to override the defaults.</li></ul>

<h4 id="haveManyLinksInDatabase">haveManyLinksInDatabase</h4>

***

Inserts many links in the database.
<h5>Parameters</h5><ul>
<li><em>int</em> <strong>$count</strong></li>
<li><em>array/array/null/array</em> <strong>$overrides</strong> = <em>array()</em></li></ul>

<h4 id="haveManyPostsInDatabase">haveManyPostsInDatabase</h4>

***

Inserts many posts in the database returning their IDs. An array of values to override the defaults. The `{{n}}` placeholder can be used to have the post count inserted in its place; e.g. `Post Title - {{n}}` will be set to `Post Title - 0` for the first post, `Post Title - 1` for the second one and so on. The same applies to meta values as well.
<h5>Parameters</h5><ul>
<li><em>int</em> <strong>$count</strong> - The number of posts to insert.</li>
<li><em>array</em> <strong>$overrides</strong> = <em>array()</em></li></ul>

<h4 id="haveManyTermsInDatabase">haveManyTermsInDatabase</h4>

***

Inserts many terms in the database.
<h5>Parameters</h5><ul>
<li><em>int</em> <strong>$count</strong></li>
<li><em>string</em> <strong>$name</strong> - The term name.</li>
<li><em>string</em> <strong>$taxonomy</strong> - The taxonomy name.</li>
<li><em>array</em> <strong>$overrides</strong> = <em>array()</em> - An associative array of default overrides.</li></ul>

<h4 id="haveManyUsersInDatabase">haveManyUsersInDatabase</h4>

***


<h5>Parameters</h5><ul>
<li><em>mixed</em> <strong>$count</strong></li>
<li><em>mixed</em> <strong>$user_login</strong></li>
<li><em>string</em> <strong>$role</strong> = <em>`'subscriber'`</em></li>
<li><em>array</em> <strong>$overrides</strong> = <em>array()</em></li></ul>

<h4 id="haveMenuInDatabase">haveMenuInDatabase</h4>

***

Creates and adds a menu to a theme location in the database.
<h5>Parameters</h5><ul>
<li><em>string</em> <strong>$slug</strong> - The menu slug.</li>
<li><em>string</em> <strong>$location</strong> - The theme menu location the menu will be assigned to.</li>
<li><em>array</em> <strong>$overrides</strong> = <em>array()</em> - An array of values to override the defaults.</li></ul>

<h4 id="haveMenuItemInDatabase">haveMenuItemInDatabase</h4>

***

Adds a menu element to a menu for the current theme. meta.
<h5>Parameters</h5><ul>
<li><em>string</em> <strong>$menuSlug</strong> - The menu slug the item should be added to.</li>
<li><em>string</em> <strong>$title</strong> - The menu item title.</li>
<li><em>int/null</em> <strong>$menuOrder</strong> = <em>null</em> - An optional menu order, <code>1</code> based.</li>
<li><em>array/array/null/array</em> <strong>$meta</strong> = <em>array()</em> - An associative array that will be prefixed with <code>_menu_item_</code> for the item post</li></ul>

<h4 id="haveOptionInDatabase">haveOptionInDatabase</h4>

***

Inserts an option in the database. If the option value is an object or an array then the value will be serialized.
<h5>Parameters</h5><ul>
<li><em>string</em> <strong>$option_name</strong></li>
<li><em>mixed</em> <strong>$option_value</strong></li>
<li><em>string</em> <strong>$autoload</strong> = <em>`'yes'`</em></li></ul>

<h4 id="havePageInDatabase">havePageInDatabase</h4>

***

Inserts a page in the database.
<h5>Parameters</h5><ul>
<li><em>array</em> <strong>$overrides</strong> = <em>array()</em> - An array of values to override the default ones.</li></ul>

<h4 id="havePostInDatabase">havePostInDatabase</h4>

***

Inserts a post in the database.
<h5>Parameters</h5><ul>
<li><em>array</em> <strong>$data</strong> = <em>array()</em> - An associative array of post data to override default and random generated values.</li></ul>

<h4 id="havePostmetaInDatabase">havePostmetaInDatabase</h4>

***

Adds one or more meta key and value couples in the database for a post.
<h5>Parameters</h5><ul>
<li><em>int</em> <strong>$postId</strong> - The post ID.</li>
<li><em>string</em> <strong>$meta_key</strong> - The meta key.</li>
<li><em>mixed</em> <strong>$meta_value</strong> - The value to insert in the database, objects and arrays will be serialized.</li></ul>

<h4 id="haveSiteOptionInDatabase">haveSiteOptionInDatabase</h4>

***

Inserts a site option in the database. If the value is an array or an object then the value will be serialized.
<h5>Parameters</h5><ul>
<li><em>string</em> <strong>$key</strong></li>
<li><em>mixed</em> <strong>$value</strong></li></ul>

<h4 id="haveSiteTransientInDatabase">haveSiteTransientInDatabase</h4>

***

Inserts a site transient in the database. If the value is an array or an object then the value will be serialized.
<h5>Parameters</h5><ul>
<li><em>mixed</em> <strong>$key</strong></li>
<li><em>mixed</em> <strong>$value</strong></li></ul>

<h4 id="haveTermInDatabase">haveTermInDatabase</h4>

***

Inserts a term in the database.
<h5>Parameters</h5><ul>
<li><em>string</em> <strong>$name</strong> - The term name, e.g. &quot;Fuzzy&quot;.</li>
<li><em>string</em> <strong>$taxonomy</strong> - The term taxonomy</li>
<li><em>array</em> <strong>$overrides</strong> = <em>array()</em> - An array of values to override the default ones.</li></ul>

<h4 id="haveTermMetaInDatabase">haveTermMetaInDatabase</h4>

***

Inserts a term meta row in the database. Objects and array meta values will be serialized.
<h5>Parameters</h5><ul>
<li><em>int</em> <strong>$term_id</strong></li>
<li><em>string</em> <strong>$meta_key</strong></li>
<li><em>mixed</em> <strong>$meta_value</strong></li></ul>

<h4 id="haveTermRelationshipInDatabase">haveTermRelationshipInDatabase</h4>

***

Creates a term relationship in the database. No check about the consistency of the insertion is made. E.g. a post could be assigned a term from a taxonomy that's not registered for that post type.
<h5>Parameters</h5><ul>
<li><em>int</em> <strong>$object_id</strong> - A post ID, a user ID or anything that can be assigned a taxonomy term.</li>
<li><em>int</em> <strong>$term_taxonomy_id</strong></li>
<li><em>int</em> <strong>$term_order</strong> - Defaults to <code>0</code>.</li></ul>

<h4 id="haveTransientInDatabase">haveTransientInDatabase</h4>

***

Inserts a transient in the database. If the value is an array or an object then the value will be serialized.
<h5>Parameters</h5><ul>
<li><em>string</em> <strong>$transient</strong></li>
<li><em>mixed</em> <strong>$value</strong></li></ul>

<h4 id="haveUserCapabilitiesInDatabase">haveUserCapabilitiesInDatabase</h4>

***

Sets a user capabilities. for a multisite installation; e.g. `[1 => 'administrator`, 2 => 'subscriber']`.
<h5>Parameters</h5><ul>
<li><em>int</em> <strong>$userId</strong></li>
<li><em>string/array</em> <strong>$role</strong> - Either a role string (e.g. <code>administrator</code>) or an associative array of blog IDs/roles</li></ul>

<h4 id="haveUserInDatabase">haveUserInDatabase</h4>

***

Inserts a user and appropriate meta in the database. and "usermeta" table.
<h5>Parameters</h5><ul>
<li><em>string</em> <strong>$user_login</strong> - The user login slug</li>
<li><em>string</em> <strong>$role</strong> = <em>`'subscriber'`</em> - The user role slug, e.g. &quot;administrator&quot;; defaults to &quot;subscriber&quot;.</li>
<li><em>array</em> <strong>$overrides</strong> = <em>array()</em> - An associative array of column names and values overridind defaults in the &quot;users&quot;</li></ul>

<h4 id="haveUserLevelsInDatabase">haveUserLevelsInDatabase</h4>

***

Sets the user level in the database for a user. multisite installation.
<h5>Parameters</h5><ul>
<li><em>int</em> <strong>$userId</strong></li>
<li><em>string/array</em> <strong>$role</strong> - Either a role string (e.g. <code>administrator</code>) or an array of blog IDs/roles for a</li></ul>

<h4 id="haveUserMetaInDatabase">haveUserMetaInDatabase</h4>

***

Sets a user meta. values will trigger the insertion of multiple rows.
<h5>Parameters</h5><ul>
<li><em>int</em> <strong>$userId</strong></li>
<li><em>string</em> <strong>$meta_key</strong></li>
<li><em>mixed</em> <strong>$meta_value</strong> - Either a single value or an array of values; objects will be serialized while array of</li></ul>

<h4 id="importSqlDumpFile">importSqlDumpFile</h4>

***

Import the SQL dump file if populate is enabled. Specifying a dump file that file will be imported.
<h5>Parameters</h5><ul>
<li><em>null/string</em> <strong>$dumpFile</strong> = <em>null</em> - The dump file that should be imported in place of the default one.</li></ul>

<h4 id="seeAttachmentInDatabase">seeAttachmentInDatabase</h4>

***

Checks for an attachment in the database.
<h5>Parameters</h5><ul>
<li><em>array</em> <strong>$criteria</strong> - An array of search criteria.</li></ul>

<h4 id="seeBlogInDatabase">seeBlogInDatabase</h4>

***

Checks for a blog in the database, looks up the `blogs` table.
<h5>Parameters</h5><ul>
<li><em>array</em> <strong>$criteria</strong> - An array of search criteria.</li></ul>

<h4 id="seeCommentInDatabase">seeCommentInDatabase</h4>

***

Checks for a comment in the database. Will look up the "comments" table.
<h5>Parameters</h5><ul>
<li><em>array</em> <strong>$criteria</strong></li></ul>

<h4 id="seeCommentMetaInDatabase">seeCommentMetaInDatabase</h4>

***

Checks that a comment meta value is in the database. Will look up the "commentmeta" table.
<h5>Parameters</h5><ul>
<li><em>array</em> <strong>$criteria</strong></li></ul>

<h4 id="seeLinkInDatabase">seeLinkInDatabase</h4>

***

Checks for a link in the database. Will look up the "links" table.
<h5>Parameters</h5><ul>
<li><em>array</em> <strong>$criteria</strong> - An array of search criteria.</li></ul>

<h4 id="seeOptionInDatabase">seeOptionInDatabase</h4>

***

Checks if an option is in the database for the current blog. If checking for an array or an object then the serialized version will be checked for.
<h5>Parameters</h5><ul>
<li><em>array</em> <strong>$criteria</strong> - An array of search criteria.</li></ul>

<h4 id="seePageInDatabase">seePageInDatabase</h4>

***

Checks for a page in the database.
<h5>Parameters</h5><ul>
<li><em>array</em> <strong>$criteria</strong> - An array of search criteria.</li></ul>

<h4 id="seePostInDatabase">seePostInDatabase</h4>

***

Checks for a post in the database.
<h5>Parameters</h5><ul>
<li><em>array</em> <strong>$criteria</strong> - An array of search criteria.</li></ul>

<h4 id="seePostMetaInDatabase">seePostMetaInDatabase</h4>

***

Checks for a post meta value in the database for the current blog. If the `meta_value` is an object or an array then the serialized value will be checked for.
<h5>Parameters</h5><ul>
<li><em>array</em> <strong>$criteria</strong> - An array of search criteria.</li></ul>

<h4 id="seePostWithTermInDatabase">seePostWithTermInDatabase</h4>

***

Checks that a post to term relation exists in the database. Will look up the "term_relationships" table.
<h5>Parameters</h5><ul>
<li><em>int</em> <strong>$post_id</strong> - The post ID.</li>
<li><em>int</em> <strong>$term_id</strong> - The term ID.</li>
<li><em>integer</em> <strong>$term_order</strong> - The order the term applies to the post, defaults to 0.</li></ul>

<h4 id="seeSiteOptionInDatabase">seeSiteOptionInDatabase</h4>

***

Checks that a site option is in the database.
<h5>Parameters</h5><ul>
<li><em>string</em> <strong>$key</strong></li>
<li><em>mixed/null</em> <strong>$value</strong> = <em>null</em></li></ul>

<h4 id="seeSiteSiteTransientInDatabase">seeSiteSiteTransientInDatabase</h4>

***

Checks that a site option is in the database.
<h5>Parameters</h5><ul>
<li><em>string</em> <strong>$key</strong></li>
<li><em>mixed/null</em> <strong>$value</strong> = <em>null</em></li></ul>

<h4 id="seeTableInDatabase">seeTableInDatabase</h4>

***

Checks that a table is in the database.
<pre><code class="language-php">    $options = $I-&gt;grabPrefixedTableNameFor('options');
    $I-&gt;seeTableInDatabase($options);</code></pre>
<h5>Parameters</h5><ul>
<li><em>string</em> <strong>$table</strong> - The full table name, including the table prefix.</li></ul>

<h4 id="seeTermInDatabase">seeTermInDatabase</h4>

***

Checks for a term in the database. Looks up the `terms` and `term_taxonomy` prefixed tables. `term_taxonomy` tables.
<h5>Parameters</h5><ul>
<li><em>array</em> <strong>$criteria</strong> - An array of criteria to search for the term, can be columns from the <code>terms</code> and the</li></ul>

<h4 id="seeTermMetaInDatabase">seeTermMetaInDatabase</h4>

***

Checks for a term meta in the database.
<h5>Parameters</h5><ul>
<li><em>array</em> <strong>$criteria</strong> - An array of search criteria.</li></ul>

<h4 id="seeTermRelationshipInDatabase">seeTermRelationshipInDatabase</h4>

***

Checks for a term relationship in the database.
<h5>Parameters</h5><ul>
<li><em>array</em> <strong>$criteria</strong> - An array of search criteria.</li></ul>

<h4 id="seeTermTaxonomyInDatabase">seeTermTaxonomyInDatabase</h4>

***

Checks for a term taxonomy in the database. Will look up the prefixed `term_taxonomy` table, e.g. `wp_term_taxonomy`.
<h5>Parameters</h5><ul>
<li><em>array</em> <strong>$criteria</strong> - An array of search criteria.</li></ul>

<h4 id="seeUserInDatabase">seeUserInDatabase</h4>

***

Checks that a user is in the database. Will look up the "users" table.
<h5>Parameters</h5><ul>
<li><em>array</em> <strong>$criteria</strong></li></ul>

<h4 id="seeUserMetaInDatabase">seeUserMetaInDatabase</h4>

***

Checks for a user meta value in the database.
<h5>Parameters</h5><ul>
<li><em>array</em> <strong>$criteria</strong> - An array of search criteria.</li></ul>

<h4 id="useBlog">useBlog</h4>

***

Sets the blog to be used.
<h5>Parameters</h5><ul>
<li><em>int</em> <strong>$id</strong></li></ul>

<h4 id="useMainBlog">useMainBlog</h4>

***

Sets the current blog to the main one (`blog_id` 1).

<h4 id="useTheme">useTheme</h4>

***

Sets the current theme options.
<h5>Parameters</h5><ul>
<li><em>string</em> <strong>$stylesheet</strong> - The theme stylesheet slug, e.g. <code>twentysixteen</code>.</li>
<li><em>string/null</em> <strong>$template</strong> = <em>null</em> - The theme template slug, e.g. <code>twentysixteen</code>, defaults to <code>$stylesheet</code>.</li>
<li><em>string/null</em> <strong>$themeName</strong> = <em>null</em> - The theme name, e.g. <code>Twentysixteen</code>, defaults to title version of <code>$stylesheet</code>.</li></ul>
</br>

*This class extends \Codeception\Module\Db*

*This class implements \Codeception\Lib\Interfaces\Db*

<!--/doc-->

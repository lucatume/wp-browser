
### Class: \Codeception\Module\WordPress

> A module dedicated to functional testing using acceptance-like methods. Differently from WPBrowser or WpWebDriver modules WordPress will be loaded in the same scope as the tests.

<table style="width: 100%;">
        <thead>
        <tr>
            <th>Method</th>
            <th>Example</th>
        </tr>
        </thead>
<tr><td><strong>activatePlugin(</strong><em>string/array</em> <strong>$pluginSlug</strong>)</strong> : <em>void</em><br /><br /><em>In the plugin administration screen activates a plugin clicking the "Activate" link. The method will not navigate to the plugins management page.</em><p><strong>Parameters:</strong><ul>string/array <strong>$pluginSlug</strong>: The plugin slug or a list of plugin slugs.</ul></p></td><td><pre><code class="language-php">    // Activate a plugin.
    $I-&gt;loginAsAdmin();
    $I-&gt;amOnPluginsPage();
    $I-&gt;activatePlugin('hello-dolly');
    // Activate a list of plugins.
    $I-&gt;loginAsAdmin();
    $I-&gt;amOnPluginsPage();
    $I-&gt;activatePlugin(['hello-dolly','another-plugin']);</code></pre></td></tr>
<tr><td><strong>amEditingPostWithId(</strong><em>int</em> <strong>$id</strong>)</strong> : <em>void</em><br /><br /><em>Goes to the post edit page for the post with the specified post ID.</em><p><strong>Parameters:</strong><ul>int <strong>$id</strong>: </ul></p></td><td></td></tr>
<tr><td><strong>amOnAdminAjaxPage()</strong> : <em>null/string</em><br /><br /><em>Goes to the `admin-ajax.php` page.</em></td><td></td></tr>
<tr><td><strong>amOnAdminPage(</strong><em>string</em> <strong>$page</strong>)</strong> : <em>void</em><br /><br /><em>Goes to an admin (wp-admin) page on the site.</em><p><strong>Parameters:</strong><ul>string <strong>$page</strong>: The relative path to an admin page.</ul></p></td><td><p>Will this comment show up in the output?
And can I use <code>HTML</code> tags? Like <em>this</em> <stron>one</strong>?
Or <strong>Markdown</strong> tags? <em>Please...</em></p>
<pre><code class="language-php">    $I-&gt;loginAs('user', 'password');
    // Go to the plugins management screen.
    $I-&gt;amOnAdminPage('/plugins.php');</code></pre></td></tr>
<tr><td><strong>amOnCronPage()</strong> : <em>null/string</em><br /><br /><em>Goes to the cron page. Useful to trigger cron jobs.</em></td><td></td></tr>
<tr><td><strong>amOnPage(</strong><em>string</em> <strong>$page</strong>)</strong> : <em>void</em><br /><br /><em>Goes to a page on the site.</em><p><strong>Parameters:</strong><ul>string <strong>$page</strong>: The path to the page, relative to the the root URL.</ul></p></td><td><p>The module will try to reach the page, relative to the URL specified in the module configuration, without
applying any permalink resolution.</p>
<pre><code class="language-php">    // Go the the homepage.
    $I-&gt;amOnPage('/');
    // Go to the single page of post with ID 23.
    $I-&gt;amOnPage('/?p=23');
    // Go to search page for the string "foo".
    $I-&gt;amOnPage('/?s=foo');</code></pre></td></tr>
<tr><td><strong>amOnPagesPage()</strong> : <em>void</em><br /><br /><em>Navigates the browser to the Pages administration screen. Makes no check about the user being logged in and authorized to do so.</em></td><td></td></tr>
<tr><td><strong>amOnPluginsPage()</strong> : <em>void</em><br /><br /><em>Navigates the browser to the plugins administration screen. Makes no check about the user being logged in and authorized to do so.</em></td><td></td></tr>
<tr><td><strong>deactivatePlugin(</strong><em>string/array</em> <strong>$pluginSlug</strong>)</strong> : <em>void</em><br /><br /><em>In the plugin administration screen deactivates a plugin clicking the "Deactivate" link. The method will presume the browser is in the plugin screen already.</em><p><strong>Parameters:</strong><ul>string/array <strong>$pluginSlug</strong>: The plugin slug, like "hello-dolly" or a list of plugin slugs.</ul></p></td><td></td></tr>
<tr><td><strong>dontSeePluginInstalled(</strong><em>string</em> <strong>$pluginSlug</strong>)</strong> : <em>void</em><br /><br /><em>Looks for a missing plugin in the plugin administration screen. Will not navigate to the plugin administration screen.</em><p><strong>Parameters:</strong><ul>string <strong>$pluginSlug</strong>: The plugin slug, like "hello-dolly".</ul></p></td><td></td></tr>
<tr><td><strong>extractCookie(</strong><em>string</em> <strong>$cookie</strong>, <em>array</em> <strong>$params=array()</strong>)</strong> : <em>void</em><br /><br /><em>Gets a cookie value and sets it on the current $_COOKIE array.</em><p><strong>Parameters:</strong><ul>string <strong>$cookie</strong>: The cookie name
array <strong>$params</strong>: Parameter to filter the cookie value</ul></p></td><td></td></tr>
<tr><td><strong>getInternalDomains()</strong> : <em>array</em><br /><br /><em>Returns a list of recognized domain names for the test site.</em></td><td></td></tr>
<tr><td><strong>getResponseContent()</strong> : <em>string</em><br /><br /><em>Returns the raw response content.</em></td><td></td></tr>
<tr><td><strong>getWpRootFolder()</strong> : <em>string</em><br /><br /><em>Returns the absolute path to the WordPress root folder.</em></td><td></td></tr>
<tr><td><strong>grabWordPressAuthCookie(</strong><em>null</em> <strong>$pattern=null</strong>)</strong> : <em>mixed Either a cookie or null.</em><br /><br /><em>Returns WordPress default auth cookie if present.</em><p><strong>Parameters:</strong><ul>null <strong>$pattern</strong>: Optional, overrides the default cookie name.</ul></p></td><td></td></tr>
<tr><td><strong>grabWordPressLoginCookie(</strong><em>null</em> <strong>$pattern=null</strong>)</strong> : <em>mixed Either a cookie or null.</em><br /><br /><em>Returns WordPress default login cookie if present.</em><p><strong>Parameters:</strong><ul>null <strong>$pattern</strong>: Optional, overrides the default cookie name.</ul></p></td><td></td></tr>
<tr><td><strong>grabWordPressTestCookie(</strong><em>null</em> <strong>$pattern=null</strong>)</strong> : <em>mixed Either a cookie or null.</em><br /><br /><em>Returns WordPress default test cookie if present.</em><p><strong>Parameters:</strong><ul>null <strong>$pattern</strong>: Optional, overrides the default cookie name.</ul></p></td><td></td></tr>
<tr><td><strong>loginAs(</strong><em>string</em> <strong>$username</strong>, <em>string</em> <strong>$password</strong>)</strong> : <em>void</em><br /><br /><em>Logs in as the specified user.</em><p><strong>Parameters:</strong><ul>string <strong>$username</strong>: 
string <strong>$password</strong>: </ul></p></td><td></td></tr>
<tr><td><strong>loginAsAdmin()</strong> : <em>array An array of login credentials and auth cookies.</em><br /><br /><em>Goes to the login page and logs in as the site admin.</em></td><td></td></tr>
<tr><td><strong>seeErrorMessage(</strong><em>string/array/string</em> <strong>$classes=`''`</strong>)</strong> : <em>void</em><br /><br /><em>In an administration screen will look for an error message. Allows for class-based error checking to decouple from internationalization.</em><p><strong>Parameters:</strong><ul>string/array/string <strong>$classes</strong>: A list of classes the error notice should have.</ul></p></td><td></td></tr>
<tr><td><strong>seeMessage(</strong><em>string/array/string</em> <strong>$classes=`''`</strong>)</strong> : <em>void</em><br /><br /><em>In an administration screen will look for a message. Allows for class-based error checking to decouple from internationalization.</em><p><strong>Parameters:</strong><ul>string/array/string <strong>$classes</strong>: A list of classes the message should have.</ul></p></td><td></td></tr>
<tr><td><strong>seePluginActivated(</strong><em>string</em> <strong>$pluginSlug</strong>)</strong> : <em>void</em><br /><br /><em>Looks for an activated plugin in the plugin administration screen. Will not navigate to the plugin administration screen.</em><p><strong>Parameters:</strong><ul>string <strong>$pluginSlug</strong>: The plugin slug, like "hello-dolly".</ul></p></td><td></td></tr>
<tr><td><strong>seePluginDeactivated(</strong><em>string</em> <strong>$pluginSlug</strong>)</strong> : <em>void</em><br /><br /><em>Looks for a deactivated plugin in the plugin administration screen. Will not navigate to the plugin administration screen.</em><p><strong>Parameters:</strong><ul>string <strong>$pluginSlug</strong>: The plugin slug, like "hello-dolly".</ul></p></td><td></td></tr>
<tr><td><strong>seePluginInstalled(</strong><em>string</em> <strong>$pluginSlug</strong>)</strong> : <em>void</em><br /><br /><em>Looks for a plugin in the plugin administration screen. Will not navigate to the plugin administration screen.</em><p><strong>Parameters:</strong><ul>string <strong>$pluginSlug</strong>: The plugin slug, like "hello-dolly".</ul></p></td><td></td></tr>
<tr><td><strong>seeWpDiePage()</strong> : <em>void</em><br /><br /><em>Checks that the current page is a wp_die generated one.</em></td><td></td></tr></table>

*This class extends \Codeception\Lib\Framework*

*This class implements \Codeception\Lib\Interfaces\Web, \Codeception\Lib\Interfaces\PageSourceSaver, \Codeception\Lib\Interfaces\ElementLocator, \Codeception\Lib\Interfaces\ConflictsWithModule, \Codeception\Lib\Interfaces\DependsOnModule*


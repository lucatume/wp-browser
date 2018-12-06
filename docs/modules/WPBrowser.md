
### Class: \Codeception\Module\WPBrowser

> A Codeception module offering specific WordPress browsing methods.

<table style="width: 100%;">
        <thead>
        <tr>
            <th>Method</th>
            <th>Example</th>
        </tr>
        </thead>
<tr><td><strong>activatePlugin(</strong><em>string/array</em> <strong>$pluginSlug</strong>)</strong> : <em>void</em><br /><br /><em>In the plugin administration screen activates a plugin clicking the "Activate" link. The method will presume the browser is in the plugin screen already.</em><p><strong>Parameters:</strong><ul>string/array <strong>$pluginSlug</strong>: The plugin slug, like "hello-dolly" or a list of plugin slugs.</ul></p></td><td></td></tr>
<tr><td><strong>amEditingPostWithId(</strong><em>int</em> <strong>$id</strong>)</strong> : <em>void</em><br /><br /><em>Goes to the post edit page for the post with the specified post ID.</em><p><strong>Parameters:</strong><ul>int <strong>$id</strong>: </ul></p></td><td></td></tr>
<tr><td><strong>amOnAdminAjaxPage()</strong> : <em>null/string</em><br /><br /><em>Goes to the `admin-ajax.php` page.</em></td><td></td></tr>
<tr><td><strong>amOnAdminPage(</strong><em>string</em> <strong>$path</strong>)</strong> : <em>void</em><br /><br /><em>Goes to a page relative to the admin URL.</em><p><strong>Parameters:</strong><ul>string <strong>$path</strong>: </ul></p></td><td></td></tr>
<tr><td><strong>amOnCronPage()</strong> : <em>null/string</em><br /><br /><em>Goes to the cron page. Useful to trigger cron jobs.</em></td><td></td></tr>
<tr><td><strong>amOnPagesPage()</strong> : <em>void</em><br /><br /><em>Navigates the browser to the Pages administration screen. Makes no check about the user being logged in and authorized to do so.</em></td><td></td></tr>
<tr><td><strong>amOnPluginsPage()</strong> : <em>void</em><br /><br /><em>Navigates the browser to the plugins administration screen. Makes no check about the user being logged in and authorized to do so.</em></td><td></td></tr>
<tr><td><strong>deactivatePlugin(</strong><em>string/array</em> <strong>$pluginSlug</strong>)</strong> : <em>void</em><br /><br /><em>In the plugin administration screen deactivates a plugin clicking the "Deactivate" link. The method will presume the browser is in the plugin screen already.</em><p><strong>Parameters:</strong><ul>string/array <strong>$pluginSlug</strong>: The plugin slug, like "hello-dolly" or a list of plugin slugs.</ul></p></td><td></td></tr>
<tr><td><strong>dontSeePluginInstalled(</strong><em>string</em> <strong>$pluginSlug</strong>)</strong> : <em>void</em><br /><br /><em>Looks for a missing plugin in the plugin administration screen. Will not navigate to the plugin administration screen.</em><p><strong>Parameters:</strong><ul>string <strong>$pluginSlug</strong>: The plugin slug, like "hello-dolly".</ul></p></td><td></td></tr>
<tr><td><strong>grabCookiesWithPattern(</strong><em>string</em> <strong>$cookiePattern</strong>)</strong> : <em>\Codeception\Module\Cookie/null</em><br /><br /><em>Returns all the cookies whose name matches a regex pattern.</em><p><strong>Parameters:</strong><ul>string <strong>$cookiePattern</strong>: </ul></p></td><td></td></tr>
<tr><td><strong>grabWordPressAuthCookie(</strong><em>null</em> <strong>$pattern=null</strong>)</strong> : <em>mixed Either a cookie or null.</em><br /><br /><em>Returns WordPress default auth cookie if present.</em><p><strong>Parameters:</strong><ul>null <strong>$pattern</strong>: Optional, overrides the default cookie name.</ul></p></td><td></td></tr>
<tr><td><strong>grabWordPressLoginCookie(</strong><em>null</em> <strong>$pattern=null</strong>)</strong> : <em>mixed Either a cookie or null.</em><br /><br /><em>Returns WordPress default login cookie if present.</em><p><strong>Parameters:</strong><ul>null <strong>$pattern</strong>: Optional, overrides the default cookie name.</ul></p></td><td></td></tr>
<tr><td><strong>grabWordPressTestCookie(</strong><em>null</em> <strong>$pattern=null</strong>)</strong> : <em>mixed Either a cookie or null.</em><br /><br /><em>Returns WordPress default test cookie if present.</em><p><strong>Parameters:</strong><ul>null <strong>$pattern</strong>: Optional, overrides the default cookie name.</ul></p></td><td></td></tr>
<tr><td><strong>loginAs(</strong><em>string</em> <strong>$username</strong>, <em>string</em> <strong>$password</strong>)</strong> : <em>array An array of login credentials and auth cookies.</em><br /><br /><em>Goes to the login page and logs in using the given credentials.</em><p><strong>Parameters:</strong><ul>string <strong>$username</strong>: 
string <strong>$password</strong>: </ul></p></td><td></td></tr>
<tr><td><strong>loginAsAdmin()</strong> : <em>array An array of login credentials and auth cookies.</em><br /><br /><em>Goes to the login page and logs in as the site admin.</em></td><td></td></tr>
<tr><td><strong>seeErrorMessage(</strong><em>string/array/string</em> <strong>$classes=`''`</strong>)</strong> : <em>void</em><br /><br /><em>In an administration screen will look for an error message. Allows for class-based error checking to decouple from internationalization.</em><p><strong>Parameters:</strong><ul>string/array/string <strong>$classes</strong>: A list of classes the error notice should have.</ul></p></td><td></td></tr>
<tr><td><strong>seeMessage(</strong><em>string/array/string</em> <strong>$classes=`''`</strong>)</strong> : <em>void</em><br /><br /><em>In an administration screen will look for a message. Allows for class-based error checking to decouple from internationalization.</em><p><strong>Parameters:</strong><ul>string/array/string <strong>$classes</strong>: A list of classes the message should have.</ul></p></td><td></td></tr>
<tr><td><strong>seePluginActivated(</strong><em>string</em> <strong>$pluginSlug</strong>)</strong> : <em>void</em><br /><br /><em>Looks for an activated plugin in the plugin administration screen. Will not navigate to the plugin administration screen.</em><p><strong>Parameters:</strong><ul>string <strong>$pluginSlug</strong>: The plugin slug, like "hello-dolly".</ul></p></td><td></td></tr>
<tr><td><strong>seePluginDeactivated(</strong><em>string</em> <strong>$pluginSlug</strong>)</strong> : <em>void</em><br /><br /><em>Looks for a deactivated plugin in the plugin administration screen. Will not navigate to the plugin administration screen.</em><p><strong>Parameters:</strong><ul>string <strong>$pluginSlug</strong>: The plugin slug, like "hello-dolly".</ul></p></td><td></td></tr>
<tr><td><strong>seePluginInstalled(</strong><em>string</em> <strong>$pluginSlug</strong>)</strong> : <em>void</em><br /><br /><em>Looks for a plugin in the plugin administration screen. Will not navigate to the plugin administration screen.</em><p><strong>Parameters:</strong><ul>string <strong>$pluginSlug</strong>: The plugin slug, like "hello-dolly".</ul></p></td><td></td></tr>
<tr><td><strong>seeWpDiePage()</strong> : <em>void</em><br /><br /><em>Checks that the current page is a wp_die generated one.</em></td><td></td></tr></table>

*This class extends \Codeception\Module\PhpBrowser*

*This class implements \Codeception\Lib\Interfaces\RequiresPackage, \Codeception\Lib\Interfaces\MultiSession, \Codeception\Lib\Interfaces\Remote, \Codeception\Lib\Interfaces\Web, \Codeception\Lib\Interfaces\PageSourceSaver, \Codeception\Lib\Interfaces\ElementLocator, \Codeception\Lib\Interfaces\ConflictsWithModule*


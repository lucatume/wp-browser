
### Class: \Codeception\Module\WordPress

> A module dedicated to functional testing using acceptance-like methods. Differently from WPBrowser or WpWebDriver modules the WordPress code will be loaded in the same scope as the tests.

<h3>Methods</h3><nav><ul><li><a href="#activatePlugin">activatePlugin</a></li><li><a href="#amEditingPostWithId">amEditingPostWithId</a></li><li><a href="#amOnAdminAjaxPage">amOnAdminAjaxPage</a></li><li><a href="#amOnAdminPage">amOnAdminPage</a></li><li><a href="#amOnCronPage">amOnCronPage</a></li><li><a href="#amOnPage">amOnPage</a></li><li><a href="#amOnPagesPage">amOnPagesPage</a></li><li><a href="#amOnPluginsPage">amOnPluginsPage</a></li><li><a href="#deactivatePlugin">deactivatePlugin</a></li><li><a href="#dontSeePluginInstalled">dontSeePluginInstalled</a></li><li><a href="#extractCookie">extractCookie</a></li><li><a href="#getResponseContent">getResponseContent</a></li><li><a href="#getWpRootFolder">getWpRootFolder</a></li><li><a href="#grabWordPressTestCookie">grabWordPressTestCookie</a></li><li><a href="#loginAs">loginAs</a></li><li><a href="#loginAsAdmin">loginAsAdmin</a></li><li><a href="#seeErrorMessage">seeErrorMessage</a></li><li><a href="#seeMessage">seeMessage</a></li><li><a href="#seePluginActivated">seePluginActivated</a></li><li><a href="#seePluginDeactivated">seePluginDeactivated</a></li><li><a href="#seePluginInstalled">seePluginInstalled</a></li><li><a href="#seeWpDiePage">seeWpDiePage</a></li></ul></nav><h4 id="activatePlugin">activatePlugin</h4>
- - -
In the plugin administration screen activates a plugin clicking the "Activate" link. The method will **not** handle authentication to the admin area.
<pre><code class="language-php">    // Activate a plugin.
    $I-&gt;loginAsAdmin();
    $I-&gt;amOnPluginsPage();
    $I-&gt;activatePlugin('hello-dolly');
    // Activate a list of plugins.
    $I-&gt;loginAsAdmin();
    $I-&gt;amOnPluginsPage();
    $I-&gt;activatePlugin(['hello-dolly','another-plugin']);</code></pre>
<h5>Parameters</h5><ul>
<li><em>string/array</em> <strong>$pluginSlug</strong> - The plugin slug, like "hello-dolly" or a list of plugin slugs.</li></ul>
<h4 id="amEditingPostWithId">amEditingPostWithId</h4>
- - -
Go to the admin page to edit the post with the specified ID. The method will **not** handle authentication the admin area.
<pre><code class="language-php">    $I-&gt;loginAsAdmin();
    $postId = $I-&gt;havePostInDatabase();
    $I-&gt;amEditingPostWithId($postId);
    $I-&gt;fillField('post_title', 'Post title');</code></pre>
<h5>Parameters</h5><ul>
<li><em>int</em> <strong>$id</strong> - The post ID.</li></ul>
<h4 id="amOnAdminAjaxPage">amOnAdminAjaxPage</h4>
- - -
Go to the `admin-ajax.php` page to start a synchronous, and blocking, `GET` AJAX request.
<pre><code class="language-php">    $I-&gt;amOnAdminAjaxPage(['action' =&gt; 'my-action', 'data' =&gt; ['id' =&gt; 23]]);</code></pre>
<h5>Parameters</h5><ul>
<li><em>array/string</em> <strong>$queryVars</strong> = <em>null</em> - A string or array of query variables to append to the AJAX path.</li></ul>
<h4 id="amOnAdminPage">amOnAdminPage</h4>
- - -
Go to a page in the admininstration area of the site.
<p>Will this comment show up in the output?
And can I use <code>HTML</code> tags? Like <em>this</em> <stron>one</strong>?
Or <strong>Markdown</strong> tags? <em>Please...</em></p>
<pre><code class="language-php">    $I-&gt;loginAs('user', 'password');
    // Go to the plugins management screen.
    $I-&gt;amOnAdminPage('/plugins.php');</code></pre>
<h5>Parameters</h5><ul>
<li><em>string</em> <strong>$page</strong> - The path, relative to the admin area URL, to the page.</li></ul>
<h4 id="amOnCronPage">amOnCronPage</h4>
- - -
Go to the cron page to start a synchronous, and blocking, `GET` request to the cron script.
<pre><code class="language-php">    // Triggers the cron job with an optional query argument.
    $I-&gt;amOnCronPage('?some-query-var=some-value');</code></pre>
<h5>Parameters</h5><ul>
<li><em>array/string</em> <strong>$queryVars</strong> = <em>null</em> - A string or array of query variables to append to the AJAX path.</li></ul>
<h4 id="amOnPage">amOnPage</h4>
- - -
Go to a page on the site. The module will try to reach the page, relative to the URL specified in the module configuration, without applying any permalink resolution.
<pre><code class="language-php">    // Go the the homepage.
    $I-&gt;amOnPage('/');
    // Go to the single page of post with ID 23.
    $I-&gt;amOnPage('/?p=23');
    // Go to search page for the string "foo".
    $I-&gt;amOnPage('/?s=foo');</code></pre>
<h5>Parameters</h5><ul>
<li><em>string</em> <strong>$page</strong> - The path to the page, relative to the the root URL.</li></ul>
<h4 id="amOnPagesPage">amOnPagesPage</h4>
- - -
Go the "Pages" administration screen. The method will **not** handle authentication to the admin area.
<pre><code class="language-php">    $I-&gt;loginAsAdmin();
    $I-&gt;amOnPagesPage();
    $I-&gt;see('Add New');</code></pre>
<h4 id="amOnPluginsPage">amOnPluginsPage</h4>
- - -
Go to the plugins administration screen. The method will **not** handle authentication to the admin area.
<pre><code class="language-php">    $I-&gt;loginAsAdmin();
    $I-&gt;amOnPluginsPage();
    $I-&gt;activatePlugin('hello-dolly');</code></pre>
<h4 id="deactivatePlugin">deactivatePlugin</h4>
- - -
On to the plugin administration screen and deactivate a plugin clicking the "Deactivate" link. The method will not **handle** authentication to the admin area.
<pre><code class="language-php">    // Deactivate one plugin.
    $I-&gt;loginAsAdmin();
    $I-&gt;amOnPluginsPage();
    $I-&gt;deactivatePlugin('hello-dolly');
    // Deactivate a list of plugins.
    $I-&gt;loginAsAdmin();
    $I-&gt;amOnPluginsPage();
    $I-&gt;deactivatePlugin(['hello-dolly', 'my-plugin']);</code></pre>
<h5>Parameters</h5><ul>
<li><em>string/array</em> <strong>$pluginSlug</strong> - The plugin slug, like "hello-dolly" or a list of plugin slugs.</li></ul>
<h4 id="dontSeePluginInstalled">dontSeePluginInstalled</h4>
- - -
Assert a plugin is not installed in the plugins list. The method will **not** navigate to the plugin administration screen.
<pre><code class="language-php">    $I-&gt;loginAsAdmin();
    $I-&gt;amOnPluginsPage();
    $I-&gt;dontSeePluginInstalled('my-plugin');</code></pre>
<h5>Parameters</h5><ul>
<li><em>string</em> <strong>$pluginSlug</strong> - The plugin slug, like "hello-dolly".</li></ul>
<h4 id="extractCookie">extractCookie</h4>
- - -
Grab a cookie value from the current session, sets it in the $_COOKIE array and returns its value. This method utility is to get, in the scope of test code, the value of a cookie set during the tests.
<pre><code class="language-php">    $id = $I-&gt;haveUserInDatabase('user', 'subscriber', ['user_pass' =&gt; 'pass']);
    $I-&gt;loginAs('user', 'pass');
    // The cookie is now set in the `$_COOKIE` super-global.
    $I-&gt;extractCookie(LOGGED_IN_COOKIE);
    // Generate a nonce using WordPress methods (see WPLoader in loadOnly mode) with correctly set context.
    wp_set_current_user($id);
    $nonce = wp_create_nonce('wp_rest');
    // Use the generated nonce to make a request to the the REST API.
    $I-&gt;haveHttpHeader('X-WP-Nonce', $nonce);</code></pre>
<h5>Parameters</h5><ul>
<li><em>string</em> <strong>$cookie</strong> - The cookie name.</li>
<li><em>array</em> <strong>$params</strong> = <em>array()</em> - Parameters to filter the cookie value.</li></ul>
<h4 id="getResponseContent">getResponseContent</h4>
- - -
Returns content of the last response. This method exposes an underlying API for custom assertions.
<pre><code class="language-php">    // In test class.
    $this-&gt;assertContains($text, $this-&gt;getResponseContent(), "foo-bar");</code></pre>
<h4 id="getWpRootFolder">getWpRootFolder</h4>
- - -
Returns the absolute path to the WordPress root folder.
<pre><code class="language-php">    $root = $I-&gt;getWpRootFolder();
    $this-&gt;assertFileExists($root . '/someFile.txt');</code></pre>
<h4 id="grabWordPressTestCookie">grabWordPressTestCookie</h4>
- - -
Returns WordPress default test cookie object if present.
<h5>Parameters</h5><ul>
<li><em>string</em> <strong>$name</strong> = <em>null</em> - Optional, overrides the default cookie name.</li></ul>
<h4 id="loginAs">loginAs</h4>
- - -
Login as the specified user. The method will **not** follow redirection, after the login, to any page.
<pre><code class="language-php">    $I-&gt;loginAs('user', 'password');
    $I-&gt;amOnAdminPage('/');
    $I-&gt;seeElement('.admin');</code></pre>
<h5>Parameters</h5><ul>
<li><em>string</em> <strong>$username</strong></li>
<li><em>string</em> <strong>$password</strong></li></ul>
<h4 id="loginAsAdmin">loginAsAdmin</h4>
- - -
Login as the administrator user using the credentials specified in the module configuration. The method will **not** follow redirection, after the login, to any page.
<pre><code class="language-php">    $I-&gt;loginAsAdmin();
    $I-&gt;amOnAdminPage('/');
    $I-&gt;see('Dashboard');</code></pre>
<h4 id="seeErrorMessage">seeErrorMessage</h4>
- - -
In an administration screen will look for an error admin notice. Allows for class-based error checking to decouple from internationalization.
<pre><code class="language-php">    $I-&gt;loginAsAdmin()ja
    $I-&gt;amOnAdminPage('/');
    $I-&gt;seeErrorMessage('.my-plugin');</code></pre>
<h5>Parameters</h5><ul>
<li><em>string/array/string</em> <strong>$classes</strong> = <em>`''`</em> - A list of classes the error notice should have in addition to the `.notice.notice-error` ones.</li></ul>
<h4 id="seeMessage">seeMessage</h4>
- - -
In an administration screen will look for an admin notice. Allows for class-based error checking to decouple from internationalization.
<pre><code class="language-php">    $I-&gt;loginAsAdmin()ja
    $I-&gt;amOnAdminPage('/');
    $I-&gt;seeMessage('.notice-warning.my-plugin');</code></pre>
<h5>Parameters</h5><ul>
<li><em>string/array/string</em> <strong>$classes</strong> = <em>`''`</em> - A list of classes the message should have in addition to the `.notice` one.</li></ul>
<h4 id="seePluginActivated">seePluginActivated</h4>
- - -
Assert a plugin is activated in the plugins list. The method will **not** navigate to the plugin administration screen.
<pre><code class="language-php">    $I-&gt;loginAsAdmin();
    $I-&gt;amOnPluginsPage();
    $I-&gt;seePluginActivated('my-plugin');</code></pre>
<h5>Parameters</h5><ul>
<li><em>string</em> <strong>$pluginSlug</strong> - The plugin slug, like "hello-dolly".</li></ul>
<h4 id="seePluginDeactivated">seePluginDeactivated</h4>
- - -
Assert a plugin is not activated in the plugins list. The method will **not** navigate to the plugin administration screen.
<pre><code class="language-php">    $I-&gt;loginAsAdmin();
    $I-&gt;amOnPluginsPage();
    $I-&gt;seePluginDeactivated('my-plugin');</code></pre>
<h5>Parameters</h5><ul>
<li><em>string</em> <strong>$pluginSlug</strong> - The plugin slug, like "hello-dolly".</li></ul>
<h4 id="seePluginInstalled">seePluginInstalled</h4>
- - -
Assert a plugin is installed, no matter its activation status, in the plugins list. The method will **not** navigate to the plugin administration screen.
<pre><code class="language-php">    $I-&gt;loginAsAdmin();
    $I-&gt;amOnPluginsPage();
    $I-&gt;seePluginInstalled('my-plugin');</code></pre>
<h5>Parameters</h5><ul>
<li><em>string</em> <strong>$pluginSlug</strong> - The plugin slug, like "hello-dolly".</li></ul>
<h4 id="seeWpDiePage">seeWpDiePage</h4>
- - -
Checks that the current page is a `wp_die` generated one. The method will try to identify the page based on the default WordPress die page markup.
<pre><code class="language-php">    $I-&gt;loginAs('user', 'password');
    $I-&gt;amOnAdminPage('/forbidden');
    $I-&gt;seeWpDiePage();</code></pre></br>

*This class extends \Codeception\Lib\Framework*

*This class implements \Codeception\Lib\Interfaces\Web, \Codeception\Lib\Interfaces\PageSourceSaver, \Codeception\Lib\Interfaces\ElementLocator, \Codeception\Lib\Interfaces\ConflictsWithModule, \Codeception\Lib\Interfaces\DependsOnModule*


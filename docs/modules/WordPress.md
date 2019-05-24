# WordPress module
**This module requires good knowledge and attention to be used effectively; you can replace it with a combination of the [WPBrowser](WPBrowser.md) module together with the [WPLoader module in loadOnly mode](WPLoader.md#wploader-to-only-bootstrap-wordpress).**
This module should be used in functional tests, see [levels of testing for more information](./../levels-of-testing.md).  
This module provides a middle-ground, in terms of testing and effects, between the fully isolated approach of the [WPBrowser](WPBrowser.md) module and the fully integrated approach of the [WPLoader module with loadOnly set to `false`](WPLoader.md).  
It allows to interact with WordPress on a very high level, using methods like `$I->loginAs()` or `$I->amOnPage()` as you could do with the `WPBrowser` module while also loading WordPress in the same variable scope as the tests as the `WPLoader` module would.
Due to WordPress reliance on constants, globals and side-effects this module will make requests to WordPress in an insualted manner and reproduce WordPress environment (globals and super-globals) after each response in the tests variable scope.
The module simulates a user interaction with the site **without Javascript support**, use the [WPWebDriver module](WPWebDriver.md) for any kind of testing that requires Javascript-based interaction with the site.

## Detecting requests coming from this module 
When it runs this module will set the `WPBROWSER_HOST_REQUEST` environment variable.  
You can detect and use that information to, as an example, use the correct database in your test site `wp-config.php` file:
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

## Configuration
* `wpRootFolder` *required* The absolute, or relative to the project root folder, path to the root WordPress installation folder. The WordPress installation root folder is the one that contains the `wp-load.php` file.
* `adminUsername` *required* - This is the login name, not the "nice" name, of the administrator user of the WordPress test site. This will be used to fill the username field in WordPress login page.  
* `adminPassword` *required* - This is the the password of the administrator use of the WordPress test site. This will be used to fill the password in WordPress login page.  
* `adminPath` *required* - The path, relative to the WordPress test site home URL, to the administration area, usually `/wp-admin`.

### Example configuration
```yaml
  modules:
      enabled:
          - WordPress
      config:
          WordPress:
              wpRootFolder: "/var/www/wordpress"
              adminUsername: 'admin'
              adminPassword: 'password'
              adminPath: '/wp-admin'
```
<!--doc-->


## Public API
<nav>
	<ul>
		<li>
			<a href="#activateplugin">activatePlugin</a>
		</li>
		<li>
			<a href="#ameditingpostwithid">amEditingPostWithId</a>
		</li>
		<li>
			<a href="#amonadminajaxpage">amOnAdminAjaxPage</a>
		</li>
		<li>
			<a href="#amonadminpage">amOnAdminPage</a>
		</li>
		<li>
			<a href="#amoncronpage">amOnCronPage</a>
		</li>
		<li>
			<a href="#amonpage">amOnPage</a>
		</li>
		<li>
			<a href="#amonpagespage">amOnPagesPage</a>
		</li>
		<li>
			<a href="#amonpluginspage">amOnPluginsPage</a>
		</li>
		<li>
			<a href="#deactivateplugin">deactivatePlugin</a>
		</li>
		<li>
			<a href="#dontseeplugininstalled">dontSeePluginInstalled</a>
		</li>
		<li>
			<a href="#extractcookie">extractCookie</a>
		</li>
		<li>
			<a href="#getresponsecontent">getResponseContent</a>
		</li>
		<li>
			<a href="#getwprootfolder">getWpRootFolder</a>
		</li>
		<li>
			<a href="#grabwordpresstestcookie">grabWordPressTestCookie</a>
		</li>
		<li>
			<a href="#loginas">loginAs</a>
		</li>
		<li>
			<a href="#loginasadmin">loginAsAdmin</a>
		</li>
		<li>
			<a href="#seeerrormessage">seeErrorMessage</a>
		</li>
		<li>
			<a href="#seemessage">seeMessage</a>
		</li>
		<li>
			<a href="#seepluginactivated">seePluginActivated</a>
		</li>
		<li>
			<a href="#seeplugindeactivated">seePluginDeactivated</a>
		</li>
		<li>
			<a href="#seeplugininstalled">seePluginInstalled</a>
		</li>
		<li>
			<a href="#seewpdiepage">seeWpDiePage</a>
		</li>
	</ul>
</nav>

<h3>activatePlugin</h3>

<hr>

<p>In the plugin administration screen activates one or more plugins clicking the &quot;Activate&quot; link. The method will <strong>not</strong> handle authentication and navigation to the plugins administration page.</p>
<pre><code class="language-php">    // Activate a plugin.
    $I-&gt;loginAsAdmin();
    $I-&gt;amOnPluginsPage();
    $I-&gt;activatePlugin('hello-dolly');
    // Activate a list of plugins.
    $I-&gt;loginAsAdmin();
    $I-&gt;amOnPluginsPage();
    $I-&gt;activatePlugin(['hello-dolly','another-plugin']);</code></pre>
<h4>Parameters</h4>
<ul>
<li><code>string/array</code> <strong>$pluginSlug</strong> - The plugin slug, like &quot;hello-dolly&quot; or a list of plugin slugs.</li></ul>
  

<h3>amEditingPostWithId</h3>

<hr>

<p>Go to the admin page to edit the post with the specified ID. The method will <strong>not</strong> handle authentication the admin area.</p>
<pre><code class="language-php">    $I-&gt;loginAsAdmin();
    $postId = $I-&gt;havePostInDatabase();
    $I-&gt;amEditingPostWithId($postId);
    $I-&gt;fillField('post_title', 'Post title');</code></pre>
<h4>Parameters</h4>
<ul>
<li><code>int</code> <strong>$id</strong> - The post ID.</li></ul>
  

<h3>amOnAdminAjaxPage</h3>

<hr>

<p>Go to the <code>admin-ajax.php</code> page to start a synchronous, and blocking, <code>GET</code> AJAX request. The method will <strong>not</strong> handle authentication, nonces or authorization.</p>
<pre><code class="language-php">    $I-&gt;amOnAdminAjaxPage(['action' =&gt; 'my-action', 'data' =&gt; ['id' =&gt; 23], 'nonce' =&gt; $nonce]);</code></pre>
<h4>Parameters</h4>
<ul>
<li><code>array/string</code> <strong>$queryVars</strong> - A string or array of query variables to append to the AJAX path.</li></ul>
  

<h3>amOnAdminPage</h3>

<hr>

<p>Go to a page in the admininstration area of the site.</p>
<p>Will this comment show up in the output?
And can I use <code>HTML</code> tags? Like <em>this</em> <stron>one</strong>?
Or <strong>Markdown</strong> tags? <em>Please...</em></p>
<pre><code class="language-php">    $I-&gt;loginAs('user', 'password');
    // Go to the plugins management screen.
    $I-&gt;amOnAdminPage('/plugins.php');</code></pre>
<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$page</strong> - The path, relative to the admin area URL, to the page.</li></ul>
  

<h3>amOnCronPage</h3>

<hr>

<p>Go to the cron page to start a synchronous, and blocking, <code>GET</code> request to the cron script.</p>
<pre><code class="language-php">    // Triggers the cron job with an optional query argument.
    $I-&gt;amOnCronPage('/?some-query-var=some-value');</code></pre>
<h4>Parameters</h4>
<ul>
<li><code>array/string</code> <strong>$queryVars</strong> - A string or array of query variables to append to the AJAX path.</li></ul>
  

<h3>amOnPage</h3>

<hr>

<p>Go to a page on the site. The module will try to reach the page, relative to the URL specified in the module configuration, without applying any permalink resolution.</p>
<pre><code class="language-php">    // Go the the homepage.
    $I-&gt;amOnPage('/');
    // Go to the single page of post with ID 23.
    $I-&gt;amOnPage('/?p=23');
    // Go to search page for the string "foo".
    $I-&gt;amOnPage('/?s=foo');</code></pre>
<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$page</strong> - The path to the page, relative to the the root URL.</li></ul>
  

<h3>amOnPagesPage</h3>

<hr>

<p>Go the &quot;Pages&quot; administration screen. The method will <strong>not</strong> handle authentication.</p>
<pre><code class="language-php">    $I-&gt;loginAsAdmin();
    $I-&gt;amOnPagesPage();
    $I-&gt;see('Add New');</code></pre>
  

<h3>amOnPluginsPage</h3>

<hr>

<p>Go to the plugins administration screen. The method will <strong>not</strong> handle authentication.</p>
<pre><code class="language-php">    $I-&gt;loginAsAdmin();
    $I-&gt;amOnPluginsPage();
    $I-&gt;activatePlugin('hello-dolly');</code></pre>
  

<h3>deactivatePlugin</h3>

<hr>

<p>In the plugin administration screen deactivate a plugin clicking the &quot;Deactivate&quot; link. The method will <strong>not</strong> handle authentication and navigation to the plugins administration page.</p>
<pre><code class="language-php">    // Deactivate one plugin.
    $I-&gt;loginAsAdmin();
    $I-&gt;amOnPluginsPage();
    $I-&gt;deactivatePlugin('hello-dolly');
    // Deactivate a list of plugins.
    $I-&gt;loginAsAdmin();
    $I-&gt;amOnPluginsPage();
    $I-&gt;deactivatePlugin(['hello-dolly', 'my-plugin']);</code></pre>
<h4>Parameters</h4>
<ul>
<li><code>string/array</code> <strong>$pluginSlug</strong> - The plugin slug, like &quot;hello-dolly&quot;, or a list of plugin slugs.</li></ul>
  

<h3>dontSeePluginInstalled</h3>

<hr>

<p>Assert a plugin is not installed in the plugins administration screen. The method will <strong>not</strong> handle authentication and navigation to the plugin administration screen.</p>
<pre><code class="language-php">    $I-&gt;loginAsAdmin();
    $I-&gt;amOnPluginsPage();
    $I-&gt;dontSeePluginInstalled('my-plugin');</code></pre>
<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$pluginSlug</strong> - The plugin slug, like &quot;hello-dolly&quot;.</li></ul>
  

<h3>extractCookie</h3>

<hr>

<p>Grab a cookie value from the current session, sets it in the $_COOKIE array and returns its value. This method utility is to get, in the scope of test code, the value of a cookie set during the tests.</p>
<pre><code class="language-php">    $id = $I-&gt;haveUserInDatabase('user', 'subscriber', ['user_pass' =&gt; 'pass']);
    $I-&gt;loginAs('user', 'pass');
    // The cookie is now set in the `$_COOKIE` super-global.
    $I-&gt;extractCookie(LOGGED_IN_COOKIE);
    // Generate a nonce using WordPress methods (see WPLoader in loadOnly mode) with correctly set context.
    wp_set_current_user($id);
    $nonce = wp_create_nonce('wp_rest');
    // Use the generated nonce to make a request to the the REST API.
    $I-&gt;haveHttpHeader('X-WP-Nonce', $nonce);</code></pre>
<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$cookie</strong> - The cookie name.</li>
<li><code>array</code> <strong>$params</strong> - Parameters to filter the cookie value.</li></ul>
  

<h3>getResponseContent</h3>

<hr>

<p>Returns content of the last response. This method exposes an underlying API for custom assertions.</p>
<pre><code class="language-php">    // In test class.
    $this-&gt;assertContains($text, $this-&gt;getResponseContent(), "foo-bar");</code></pre>
  

<h3>getWpRootFolder</h3>

<hr>

<p>Returns the absolute path to the WordPress root folder.</p>
<pre><code class="language-php">    $root = $I-&gt;getWpRootFolder();
    $this-&gt;assertFileExists($root . '/someFile.txt');</code></pre>
  

<h3>grabWordPressTestCookie</h3>

<hr>

<p>Returns WordPress default test cookie object if present.</p>
<pre><code class="language-php">    // Grab the default WordPress test cookie.
    $wpTestCookie = $I-&gt;grabWordPressTestCookie();
    // Grab a customized version of the test cookie.
    $myTestCookie = $I-&gt;grabWordPressTestCookie('my_test_cookie');</code></pre>
<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$name</strong> - Optional, overrides the default cookie name.</li></ul>
  

<h3>loginAs</h3>

<hr>

<p>Login as the specified user. The method will <strong>not</strong> follow redirection, after the login, to any page.</p>
<pre><code class="language-php">    $I-&gt;loginAs('user', 'password');
    $I-&gt;amOnAdminPage('/');
    $I-&gt;seeElement('.admin');</code></pre>
<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$username</strong> - The user login name.</li>
<li><code>string</code> <strong>$password</strong> - The user password in plain text.</li></ul>
  

<h3>loginAsAdmin</h3>

<hr>

<p>Login as the administrator user using the credentials specified in the module configuration. The method will <strong>not</strong> follow redirection, after the login, to any page.</p>
<pre><code class="language-php">    $I-&gt;loginAsAdmin();
    $I-&gt;amOnAdminPage('/');
    $I-&gt;see('Dashboard');</code></pre>
  

<h3>seeErrorMessage</h3>

<hr>

<p>In an administration screen look for an error admin notice. The check is class-based to decouple from internationalization. The method will <strong>not</strong> handle authentication and navigation the administration area.</p>
<pre><code class="language-php">    $I-&gt;loginAsAdmin()ja
    $I-&gt;amOnAdminPage('/');
    $I-&gt;seeErrorMessage('.my-plugin');</code></pre>
<h4>Parameters</h4>
<ul>
<li><code>string/array/string</code> <strong>$classes</strong> - A list of classes the notice should have other than the <code>.notice.notice-error</code> ones.</li></ul>
  

<h3>seeMessage</h3>

<hr>

<p>In an administration screen look for an admin notice. The check is class-based to decouple from internationalization. The method will <strong>not</strong> handle authentication and navigation the administration area.</p>
<pre><code class="language-php">    $I-&gt;loginAsAdmin()ja
    $I-&gt;amOnAdminPage('/');
    $I-&gt;seeMessage('.missing-api-token.my-plugin');</code></pre>
<h4>Parameters</h4>
<ul>
<li><code>string/array/string</code> <strong>$classes</strong> - A list of classes the message should have in addition to the <code>.notice</code> one.</li></ul>
  

<h3>seePluginActivated</h3>

<hr>

<p>Assert a plugin is activated in the plugin administration screen. The method will <strong>not</strong> handle authentication and navigation to the plugin administration screen.</p>
<pre><code class="language-php">    $I-&gt;loginAsAdmin();
    $I-&gt;amOnPluginsPage();
    $I-&gt;seePluginActivated('my-plugin');</code></pre>
<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$pluginSlug</strong> - The plugin slug, like &quot;hello-dolly&quot;.</li></ul>
  

<h3>seePluginDeactivated</h3>

<hr>

<p>Assert a plugin is not activated in the plugins administration screen. The method will <strong>not</strong> handle authentication and navigation to the plugin administration screen.</p>
<pre><code class="language-php">    $I-&gt;loginAsAdmin();
    $I-&gt;amOnPluginsPage();
    $I-&gt;seePluginDeactivated('my-plugin');</code></pre>
<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$pluginSlug</strong> - The plugin slug, like &quot;hello-dolly&quot;.</li></ul>
  

<h3>seePluginInstalled</h3>

<hr>

<p>Assert a plugin is installed, no matter its activation status, in the plugin adminstration screen. The method will <strong>not</strong> handle authentication and navigation to the plugin administration screen.</p>
<pre><code class="language-php">    $I-&gt;loginAsAdmin();
    $I-&gt;amOnPluginsPage();
    $I-&gt;seePluginInstalled('my-plugin');</code></pre>
<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$pluginSlug</strong> - The plugin slug, like &quot;hello-dolly&quot;.</li></ul>
  

<h3>seeWpDiePage</h3>

<hr>

<p>Checks that the current page is one generated by the <code>wp_die</code> function. The method will try to identify the page based on the default WordPress die page HTML attributes.</p>
<pre><code class="language-php">    $I-&gt;loginAs('user', 'password');
    $I-&gt;amOnAdminPage('/forbidden');
    $I-&gt;seeWpDiePage();</code></pre>


*This class extends \Codeception\Lib\Framework*

*This class implements \Codeception\Lib\Interfaces\Web, \Codeception\Lib\Interfaces\PageSourceSaver, \Codeception\Lib\Interfaces\ElementLocator, \Codeception\Lib\Interfaces\ConflictsWithModule, \Codeception\Lib\Interfaces\DependsOnModule*

<!--/doc-->

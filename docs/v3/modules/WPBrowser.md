> **This is the documentation for version 3 of the project.**
> **The current version is version 4 and the documentation can be found [here](./../README.md).**

# WPBrowser module

This module should be used in acceptance and functional tests, see [levels of testing for more information](./../levels-of-testing.md).  

This module extends the [PHPBrowser module](https://codeception.com/docs/modules/PhpBrowser) adding WordPress-specific configuration parameters and methods.  

The module simulates a user interaction with the site **without Javascript support**; if you need to test your project with Javascript support use the [WPWebDriver module](WPWebDriver.md).  

## Module requirements for Codeception 4.0+

This module requires the `codeception/module-phpbrowser` Composer package to work when wp-browser is used with Codeception 4.0.  

To install the package run: 

```bash
composer require --dev codeception/module-phpbrowser:^1.0
```

## Configuration

Since this module extends the `PHPBrowser` module provided by Codeception, please refer to the [PHPBrowser configuration section](https://codeception.com/docs/modules/PhpBrowser#Configuration) for more information about the base configuration parameters.  

* `url` *required* - Start URL of your WordPress project, e.g. `http://wp.test`.
* `headers` - Default headers are set before each test; this might be useful to simulate a specific user agent during the tests or to identify the request source. Note that the headers defined in the config should be prefaced with `HTTP_` in your `wp-config.php` file. [This can be used to select which database to use](https://wpbrowser.wptestkit.dev/tutorials/automatically-change-db-in-tests).
* `handler` (default: `curl`) - The [Guzzle handler](http://docs.guzzlephp.org/en/stable/handlers-and-middleware.html) to use. By default `curl` is used, also possible to pass `stream`, or any valid class name as Handler.
* `middleware` - The Guzzle middlewares to add. An array of valid callables is required; see [here for more information](http://docs.guzzlephp.org/en/stable/handlers-and-middleware.html#middleware).
* `curl` - curl options; only applied if using the `curl` handler; [more options are available](http://docs.guzzlephp.org/en/stable/request-options.html).
* `adminUsername` *required* - This is the login name, not the "nice" name, of the administrator user of the WordPress test site. This will be used to fill the username field in WordPress login page.  
* `adminPassword` *required* - This is the the password of the administrator use of the WordPress test site. This will be used to fill the password in WordPress login page.  
* `adminPath` *required* - The path, relative to the WordPress test site home URL, to the administration area, usually `/wp-admin`.

### Example configuration

```yaml
  modules:
      enabled:
          - WPBrowser
      config:
          WPBrowser:
              url: 'http://wordpress.localhost'
              adminUsername: 'admin'
              adminPassword: 'password'
              adminPath: '/wp-admin'
              headers:
                X_TEST_REQUEST: 1
                X_WPBROWSER_REQUEST: 1
```

[Read here how to use the headers information to automatically change the database during acceptance and functional tests](https://wpbrowser.wptestkit.dev/tutorials/automatically-change-db-in-tests).

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
			<a href="#grabcookieswithpattern">grabCookiesWithPattern</a>
		</li>
		<li>
			<a href="#grabwordpresstestcookie">grabWordPressTestCookie</a>
		</li>
		<li>
			<a href="#logout">logOut</a>
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

<p>In the plugin administration screen activates a plugin clicking the &quot;Activate&quot; link. The method will <strong>not</strong> handle authentication to the admin area.</p>
```php
// Activate a plugin.
  $I->loginAsAdmin();
  $I->amOnPluginsPage();
  $I->activatePlugin('hello-dolly');
  // Activate a list of plugins.
  $I->loginAsAdmin();
  $I->amOnPluginsPage();
  $I->activatePlugin(['hello-dolly','another-plugin']);
```

<h4>Parameters</h4>
<ul>
<li><code>string/\Codeception\Module\array<string></code> <strong>$pluginSlug</strong> - The plugin slug, like &quot;hello-dolly&quot; or a list of plugin slugs.</li></ul>
  

<h3>amEditingPostWithId</h3>

<hr>

<p>Go to the admin page to edit the post with the specified ID. The method will <strong>not</strong> handle authentication the admin area.</p>
```php
$I->loginAsAdmin();
  $postId = $I->havePostInDatabase();
  $I->amEditingPostWithId($postId);
  $I->fillField('post_title', 'Post title');
```

<h4>Parameters</h4>
<ul>
<li><code>int</code> <strong>$id</strong> - The post ID.</li></ul>
  

<h3>amOnAdminAjaxPage</h3>

<hr>

<p>Go to the <code>admin-ajax.php</code> page to start a synchronous, and blocking, <code>GET</code> AJAX request. The method will <strong>not</strong> handle authentication, nonces or authorization.</p>
```php
$I->amOnAdminAjaxPage(['action' => 'my-action', 'data' => ['id' => 23], 'nonce' => $nonce]);
```

<h4>Parameters</h4>
<ul>
<li><code>string/\Codeception\Module\array<string,mixed></code> <strong>$queryVars</strong> - A string or array of query variables to append to the AJAX path.</li></ul>
  

<h3>amOnAdminPage</h3>

<hr>

<p>Go to a page in the admininstration area of the site. This method will <strong>not</strong> handle authentication to the administration area.</p>
```php
$I->loginAs('user', 'password');
  // Go to the plugins management screen.
  $I->amOnAdminPage('/plugins.php');
```

<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$page</strong> - The path, relative to the admin area URL, to the page.</li></ul>
  

<h3>amOnCronPage</h3>

<hr>

<p>Go to the cron page to start a synchronous, and blocking, <code>GET</code> request to the cron script.</p>
```php
// Triggers the cron job with an optional query argument.
  $I->amOnCronPage('/?some-query-var=some-value');
```

<h4>Parameters</h4>
<ul>
<li><code>string/\Codeception\Module\array<string,mixed></code> <strong>$queryVars</strong> - A string or array of query variables to append to the AJAX path.</li></ul>
  

<h3>amOnPagesPage</h3>

<hr>

<p>Go the &quot;Pages&quot; administration screen. The method will <strong>not</strong> handle authentication.</p>
```php
$I->loginAsAdmin();
  $I->amOnPagesPage();
  $I->see('Add New');
```

  

<h3>amOnPluginsPage</h3>

<hr>

<p>Go to the plugins administration screen. The method will <strong>not</strong> handle authentication.</p>
```php
$I->loginAsAdmin();
  $I->amOnPluginsPage();
  $I->activatePlugin('hello-dolly');
```

  

<h3>deactivatePlugin</h3>

<hr>

<p>In the plugin administration screen deactivate a plugin clicking the &quot;Deactivate&quot; link. The method will <strong>not</strong> handle authentication and navigation to the plugins administration page.</p>
```php
// Deactivate one plugin.
  $I->loginAsAdmin();
  $I->amOnPluginsPage();
  $I->deactivatePlugin('hello-dolly');
  // Deactivate a list of plugins.
  $I->loginAsAdmin();
  $I->amOnPluginsPage();
  $I->deactivatePlugin(['hello-dolly', 'my-plugin']);
```

<h4>Parameters</h4>
<ul>
<li><code>string/\Codeception\Module\array<string></code> <strong>$pluginSlug</strong> - The plugin slug, like &quot;hello-dolly&quot;, or a list of plugin slugs.</li></ul>
  

<h3>dontSeePluginInstalled</h3>

<hr>

<p>Assert a plugin is not installed in the plugins administration screen. The method will <strong>not</strong> handle authentication and navigation to the plugin administration screen.</p>
```php
$I->loginAsAdmin();
  $I->amOnPluginsPage();
  $I->dontSeePluginInstalled('my-plugin');
```

<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$pluginSlug</strong> - The plugin slug, like &quot;hello-dolly&quot;.</li></ul>
  

<h3>grabCookiesWithPattern</h3>

<hr>

<p>Returns all the cookies whose name matches a regex pattern.</p>
```php
$I->loginAs('customer','password');
  $I->amOnPage('/shop');
  $cartCookies = $I->grabCookiesWithPattern("#^shop_cart\\.*#");
```

<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$cookiePattern</strong> - The regular expression pattern to use for the matching.</li></ul>
  

<h3>grabWordPressTestCookie</h3>

<hr>

<p>Returns WordPress default test cookie object if present.</p>
```php
// Grab the default WordPress test cookie.
  $wpTestCookie = $I->grabWordPressTestCookie();
  // Grab a customized version of the test cookie.
  $myTestCookie = $I->grabWordPressTestCookie('my_test_cookie');
```

<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$name</strong> - Optional, overrides the default cookie name.</li></ul>
  

<h3>logOut</h3>

<hr>

<p>Navigate to the default WordPress logout page and click the logout link.</p>
```php
// Log out using the `wp-login.php` form and return to the current page.
  $I->logOut(true);
  // Log out using the `wp-login.php` form and remain there.
  $I->logOut(false);
  // Log out using the `wp-login.php` form and move to another page.
  $I->logOut('/some-other-page');
```

<h4>Parameters</h4>
<ul>
<li><code>bool/bool/string</code> <strong>$redirectTo</strong> - Whether to redirect to another (optionally specified) page after the logout.</li></ul>
  

<h3>loginAs</h3>

<hr>

<p>Login as the specified user. The method will <strong>not</strong> follow redirection, after the login, to any page.</p>
```php
$I->loginAs('user', 'password');
  $I->amOnAdminPage('/');
  $I->see('Dashboard');
```

<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$username</strong> - The user login name.</li>
<li><code>string</code> <strong>$password</strong> - The user password in plain text.</li></ul>
  

<h3>loginAsAdmin</h3>

<hr>

<p>Login as the administrator user using the credentials specified in the module configuration. The method will <strong>not</strong> follow redirection, after the login, to any page.</p>
```php
$I->loginAsAdmin();
  $I->amOnAdminPage('/');
  $I->see('Dashboard');
```

  

<h3>seeErrorMessage</h3>

<hr>

<p>In an administration screen look for an error admin notice. The check is class-based to decouple from internationalization. The method will <strong>not</strong> handle authentication and navigation the administration area. <code>.notice.notice-error</code> ones.</p>
```php
$I->loginAsAdmin()
  $I->amOnAdminPage('/');
  $I->seeErrorMessage('.my-plugin');
```

<h4>Parameters</h4>
<ul>
<li><code>string/string/\Codeception\Module\array<string></code> <strong>$classes</strong> - A list of classes the notice should have other than the</li></ul>
  

<h3>seeMessage</h3>

<hr>

<p>In an administration screen look for an admin notice. The check is class-based to decouple from internationalization. The method will <strong>not</strong> handle authentication and navigation the administration area.</p>
```php
$I->loginAsAdmin()
  $I->amOnAdminPage('/');
  $I->seeMessage('.missing-api-token.my-plugin');
```

<h4>Parameters</h4>
<ul>
<li><code>string/\Codeception\Module\array<string>/string</code> <strong>$classes</strong> - A list of classes the message should have in addition to the <code>.notice</code> one.</li></ul>
  

<h3>seePluginActivated</h3>

<hr>

<p>Assert a plugin is activated in the plugin administration screen. The method will <strong>not</strong> handle authentication and navigation to the plugin administration screen.</p>
```php
$I->loginAsAdmin();
  $I->amOnPluginsPage();
  $I->seePluginActivated('my-plugin');
```

<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$pluginSlug</strong> - The plugin slug, like &quot;hello-dolly&quot;.</li></ul>
  

<h3>seePluginDeactivated</h3>

<hr>

<p>Assert a plugin is not activated in the plugins administration screen. The method will <strong>not</strong> handle authentication and navigation to the plugin administration screen.</p>
```php
$I->loginAsAdmin();
  $I->amOnPluginsPage();
  $I->seePluginDeactivated('my-plugin');
```

<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$pluginSlug</strong> - The plugin slug, like &quot;hello-dolly&quot;.</li></ul>
  

<h3>seePluginInstalled</h3>

<hr>

<p>Assert a plugin is installed, no matter its activation status, in the plugin administration screen. The method will <strong>not</strong> handle authentication and navigation to the plugin administration screen.</p>
```php
$I->loginAsAdmin();
  $I->amOnPluginsPage();
  $I->seePluginInstalled('my-plugin');
```

<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$pluginSlug</strong> - The plugin slug, like &quot;hello-dolly&quot;.</li></ul>
  

<h3>seeWpDiePage</h3>

<hr>

<p>Checks that the current page is one generated by the <code>wp_die</code> function. The method will try to identify the page based on the default WordPress die page HTML attributes.</p>
```php
$I->loginAs('user', 'password');
  $I->amOnAdminPage('/forbidden');
  $I->seeWpDiePage();
```


*This class extends \Codeception\Module\PhpBrowser*

*This class implements \Codeception\Lib\Interfaces\MultiSession, \Codeception\Lib\Interfaces\Remote, \Codeception\Lib\Interfaces\Web, \Codeception\Lib\Interfaces\PageSourceSaver, \Codeception\Lib\Interfaces\ElementLocator, \Codeception\Lib\Interfaces\ConflictsWithModule*

<!--/doc-->

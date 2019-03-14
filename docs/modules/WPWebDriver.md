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
			<a href="#grabfullurl">grabFullUrl</a>
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
		<li>
			<a href="#waitforjqueryajax">waitForJqueryAjax</a>
		</li>
	</ul>
</nav>

<h3>activatePlugin</h3>
***
In the plugin administration screen activates a plugin clicking the "Activate" link. The method will **not** handle authentication to the admin area.
<pre><code class="language-php">    // Activate a plugin.
    $I-&gt;loginAsAdmin();
    $I-&gt;amOnPluginsPage();
    $I-&gt;activatePlugin('hello-dolly');
    // Activate a list of plugins.
    $I-&gt;loginAsAdmin();
    $I-&gt;amOnPluginsPage();
    $I-&gt;activatePlugin(['hello-dolly','another-plugin']);</code></pre>
#### Parameters

* `string/array` **$pluginSlug** - The plugin slug, like &quot;hello-dolly&quot; or a list of plugin slugs.
  

<h3>amEditingPostWithId</h3>
***
Go to the admin page to edit the post with the specified ID. The method will **not** handle authentication the admin area.
<pre><code class="language-php">    $I-&gt;loginAsAdmin();
    $postId = $I-&gt;havePostInDatabase();
    $I-&gt;amEditingPostWithId($postId);
    $I-&gt;fillField('post_title', 'Post title');</code></pre>
#### Parameters

* `int` **$id** - The post ID.
  

<h3>amOnAdminAjaxPage</h3>
***
Go to the `admin-ajax.php` page to start a synchronous, and blocking, `GET` AJAX request.
<pre><code class="language-php">    $I-&gt;amOnAdminAjaxPage(['action' =&gt; 'my-action', 'data' =&gt; ['id' =&gt; 23]]);</code></pre>
#### Parameters

* `array/string` **$queryVars** - A string or array of query variables to append to the AJAX path.
  

<h3>amOnAdminPage</h3>
***
Go to a page in the admininstration area of the site.
<p>Will this comment show up in the output?
And can I use <code>HTML</code> tags? Like <em>this</em> <stron>one</strong>?
Or <strong>Markdown</strong> tags? <em>Please...</em></p>
<pre><code class="language-php">    $I-&gt;loginAs('user', 'password');
    // Go to the plugins management screen.
    $I-&gt;amOnAdminPage('/plugins.php');</code></pre>
#### Parameters

* `string` **$page** - The path, relative to the admin area URL, to the page.
  

<h3>amOnCronPage</h3>
***
Go to the cron page to start a synchronous, and blocking, `GET` request to the cron script.
<pre><code class="language-php">    // Triggers the cron job with an optional query argument.
    $I-&gt;amOnCronPage('?some-query-var=some-value');</code></pre>
#### Parameters

* `array/string` **$queryVars** - A string or array of query variables to append to the AJAX path.
  

<h3>amOnPagesPage</h3>
***
Go the "Pages" administration screen. The method will **not** handle authentication to the admin area.
<pre><code class="language-php">    $I-&gt;loginAsAdmin();
    $I-&gt;amOnPagesPage();
    $I-&gt;see('Add New');</code></pre>
  

<h3>amOnPluginsPage</h3>
***
Go to the plugins administration screen. The method will **not** handle authentication to the admin area.
<pre><code class="language-php">    $I-&gt;loginAsAdmin();
    $I-&gt;amOnPluginsPage();
    $I-&gt;activatePlugin('hello-dolly');</code></pre>
  

<h3>deactivatePlugin</h3>
***
In the plugin administration screen deactivate a plugin clicking the "Deactivate" link. The method will **not** handle authentication and navigation to the plugins administration page.
<pre><code class="language-php">    // Deactivate one plugin.
    $I-&gt;loginAsAdmin();
    $I-&gt;amOnPluginsPage();
    $I-&gt;deactivatePlugin('hello-dolly');
    // Deactivate a list of plugins.
    $I-&gt;loginAsAdmin();
    $I-&gt;amOnPluginsPage();
    $I-&gt;deactivatePlugin(['hello-dolly', 'my-plugin']);</code></pre>
#### Parameters

* `string/array` **$pluginSlug** - The plugin slug, like &quot;hello-dolly&quot;, or a list of plugin slugs.
  

<h3>dontSeePluginInstalled</h3>
***
Assert a plugin is not installed in the plugins list. The method will **not** navigate to the plugin administration screen.
<pre><code class="language-php">    $I-&gt;loginAsAdmin();
    $I-&gt;amOnPluginsPage();
    $I-&gt;dontSeePluginInstalled('my-plugin');</code></pre>
#### Parameters

* `string` **$pluginSlug** - The plugin slug, like &quot;hello-dolly&quot;.
  

<h3>grabCookiesWithPattern</h3>
***
Returns all the cookies whose name matches a regex pattern.
<pre><code class="language-php">    $I-&gt;loginAs('customer','password');
    $I-&gt;amOnPage('/shop');
    $cartCookies = $I-&gt;grabCookiesWithPattern("#^shop_cart\\.*#");</code></pre>
#### Parameters

* `string` **$cookiePattern**
  

<h3>grabFullUrl</h3>
***
Grabs the current page full URL including the query vars.
<pre><code class="language-php">    $today = date('Y-m-d');
    $I-&gt;amOnPage('/concerts?date=' . $today);
    $I-&gt;assertRegExp('#\\/concerts$#', $I-&gt;grabFullUrl());</code></pre>
  

<h3>grabWordPressTestCookie</h3>
***
Returns WordPress default test cookie object if present.
#### Parameters

* `string` **$name** - Optional, overrides the default cookie name.
  

<h3>loginAs</h3>
***
Login as the specified user. The method will **not** follow redirection, after the login, to any page. Depending on the driven browser the login might be "too fast" and the server might have not replied with valid cookies yet; in that case the method will re-attempt the login to obtain the cookies. * @example ```php $I->loginAs('user', 'password'); $I->amOnAdminPage('/'); $I->see('Dashboard'); ```
#### Parameters

* `string` **$username**
* `string` **$password**
* `int` **$timeout** - The max time, in seconds, to try to login.
* `int` **$maxAttempts** - The max number of attempts to try to login.
  

<h3>loginAsAdmin</h3>
***
Login as the administrator user using the credentials specified in the module configuration. The method will **not** follow redirection, after the login, to any page.
<pre><code class="language-php">    $I-&gt;loginAsAdmin();
    $I-&gt;amOnAdminPage('/');
    $I-&gt;see('Dashboard');</code></pre>
#### Parameters

* `int` **$timeout** - The max time, in seconds, to try to login.
* `int` **$maxAttempts** - The max number of attempts to try to login.
  

<h3>seeErrorMessage</h3>
***
In an administration screen will look for an error admin notice. Allows for class-based error checking to decouple from internationalization.
<pre><code class="language-php">    $I-&gt;loginAsAdmin()ja
    $I-&gt;amOnAdminPage('/');
    $I-&gt;seeErrorMessage('.my-plugin');</code></pre>
#### Parameters

* `string/array/string` **$classes** - A list of classes the error notice should have in addition to the <code>.notice.notice-error</code> ones.
  

<h3>seeMessage</h3>
***
In an administration screen will look for an admin notice. Allows for class-based error checking to decouple from internationalization.
<pre><code class="language-php">    $I-&gt;loginAsAdmin()ja
    $I-&gt;amOnAdminPage('/');
    $I-&gt;seeMessage('.notice-warning.my-plugin');</code></pre>
#### Parameters

* `string/array/string` **$classes** - A list of classes the message should have in addition to the <code>.notice</code> one.
  

<h3>seePluginActivated</h3>
***
Assert a plugin is activated in the plugins list. The method will **not** navigate to the plugin administration screen.
<pre><code class="language-php">    $I-&gt;loginAsAdmin();
    $I-&gt;amOnPluginsPage();
    $I-&gt;seePluginActivated('my-plugin');</code></pre>
#### Parameters

* `string` **$pluginSlug** - The plugin slug, like &quot;hello-dolly&quot;.
  

<h3>seePluginDeactivated</h3>
***
Assert a plugin is not activated in the plugins list. The method will **not** navigate to the plugin administration screen.
<pre><code class="language-php">    $I-&gt;loginAsAdmin();
    $I-&gt;amOnPluginsPage();
    $I-&gt;seePluginDeactivated('my-plugin');</code></pre>
#### Parameters

* `string` **$pluginSlug** - The plugin slug, like &quot;hello-dolly&quot;.
  

<h3>seePluginInstalled</h3>
***
Assert a plugin is installed, no matter its activation status, in the plugins list. The method will **not** navigate to the plugin administration screen.
<pre><code class="language-php">    $I-&gt;loginAsAdmin();
    $I-&gt;amOnPluginsPage();
    $I-&gt;seePluginInstalled('my-plugin');</code></pre>
#### Parameters

* `string` **$pluginSlug** - The plugin slug, like &quot;hello-dolly&quot;.
  

<h3>seeWpDiePage</h3>
***
Checks that the current page is a `wp_die` generated one. The method will try to identify the page based on the default WordPress die page markup.
<pre><code class="language-php">    $I-&gt;loginAs('user', 'password');
    $I-&gt;amOnAdminPage('/forbidden');
    $I-&gt;seeWpDiePage();</code></pre>
  

<h3>waitForJqueryAjax</h3>
***
Waits for any jQuery triggered AJAX request to be resolved.
<pre><code class="language-php">    $I-&gt;amOnPage('/triggering-ajax-requests');
    $I-&gt;waitForJqueryAjax();
    $I-&gt;see('From AJAX');</code></pre>
#### Parameters

* `int` **$time** - The max time to wait for AJAX requests to complete.
</br>

*This class extends \Codeception\Module\WebDriver*

*This class implements \Codeception\Lib\Interfaces\RequiresPackage, \Codeception\Lib\Interfaces\ConflictsWithModule, \Codeception\Lib\Interfaces\ElementLocator, \Codeception\Lib\Interfaces\PageSourceSaver, \Codeception\Lib\Interfaces\ScreenshotSaver, \Codeception\Lib\Interfaces\SessionSnapshot, \Codeception\Lib\Interfaces\MultiSession, \Codeception\Lib\Interfaces\Remote, \Codeception\Lib\Interfaces\Web*

<!--/doc-->

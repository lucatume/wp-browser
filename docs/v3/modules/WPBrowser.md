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
* `activatePlugin($pluginSlug)` : `void`  
  In the plugin administration screen activates a plugin clicking the "Activate" link.

  The method will **not** handle authentication to the admin area.


* `activateTheme($slug)` : `void`  
  Activates a theme.

  The method will **not** handle authentication and navigation to the themes administration page.


* `amEditingPostWithId($id)` : `void`  
  Go to the admin page to edit the post with the specified ID.

  The method will **not** handle authentication the admin area.


* `amEditingUserWithId($id)` : `void`  
  Go to the admin page to edit the user with the specified ID.

  The method will **not** handle authentication the admin area.


* `amHttpAuthenticated($username, $password)` : `void`


* `amOnAdminAjaxPage([$queryVars])` : `void`  
  Go to the `admin-ajax.php` page to start a synchronous, and blocking, `GET` AJAX request.

  The method will **not** handle authentication, nonces or authorization.


* `amOnAdminPage($page)` : `void`  
  Go to a page in the admininstration area of the site.

  This method will **not** handle authentication to the administration area.


* `amOnCronPage([$queryVars])` : `void`  
  Go to the cron page to start a synchronous, and blocking, `GET` request to the cron script.


* `amOnPage($page)` : `void`


* `amOnPagesPage()` : `void`  
  Go the "Pages" administration screen.

  The method will **not** handle authentication.


* `amOnPluginsPage()` : `void`  
  Go to the plugins administration screen.

  The method will **not** handle authentication.


* `amOnSubdomain($subdomain)` : `void`


* `amOnThemesPage()` : `void`  
  Moves to the themes administration page.


* `amOnUrl($url)` : `void`


* `attachFile($field, $filename)` : `void`


* `checkOption($option)` : `void`


* `click($link, [$context])` : `void`


* `deactivatePlugin($pluginSlug)` : `void`  
  In the plugin administration screen deactivate a plugin clicking the "Deactivate" link.

  The method will **not** handle authentication and navigation to the plugins administration page.


* `deleteHeader($name)` : `void`  
  Deletes the header with the passed name.  Subsequent requests
  will not have the deleted header in its request.

  Example:
  ```php
  <?php
  $I->haveHttpHeader('X-Requested-With', 'Codeception');
  $I->amOnPage('test-headers.php');
  // ...
  $I->deleteHeader('X-Requested-With');
  $I->amOnPage('some-other-page.php');
  ```


* `dontSee($text, [$selector])` : `void`


* `dontSeeCheckboxIsChecked($checkbox)` : `void`


* `dontSeeCookie($cookie, [array $params])` : `void`


* `dontSeeCurrentUrlEquals($uri)` : `void`


* `dontSeeCurrentUrlMatches($uri)` : `void`


* `dontSeeElement($selector, [$attributes])` : `void`


* `dontSeeInCurrentUrl($uri)` : `void`


* `dontSeeInField($field, $value)` : `void`


* `dontSeeInFormFields($formSelector, array $params)` : `void`


* `dontSeeInSource($raw)` : `void`


* `dontSeeInTitle($title)` : `void`


* `dontSeeLink($text, [$url])` : `void`


* `dontSeeOptionIsSelected($selector, $optionText)` : `void`


* `dontSeePluginInstalled($pluginSlug)` : `void`  
  Assert a plugin is not installed in the plugins administration screen.

  The method will **not** handle authentication and navigation to the plugin administration screen.


* `dontSeeResponseCodeIs($code)` : `void`  
  Checks that response code is equal to value provided.

  ```php
  <?php
  $I->dontSeeResponseCodeIs(200);
  
  // recommended \Codeception\Util\HttpCode
  $I->dontSeeResponseCodeIs(\Codeception\Util\HttpCode::OK);
  ```

* `executeInGuzzle(Closure $function)` : `void`  
  Low-level API method.
  If Codeception commands are not enough, use [Guzzle HTTP Client](http://guzzlephp.org/) methods directly

  Example:

  ``` php
  <?php
  $I->executeInGuzzle(function (\GuzzleHttp\Client $client) {
       $client->get('/get', ['query' => ['foo' => 'bar']]);
  });
  ?>
  ```

  It is not recommended to use this command on a regular basis.
  If Codeception lacks important Guzzle Client methods, implement them and submit patches.


* `fillField($field, $value)` : `void`


* `followRedirect()` : `void`  
  Follow pending redirect if there is one.

  ```php
  <?php
  $I->followRedirect();
  ```


* `grabActiveTheme()` : `void`  
  Returns the slug of the currently active themes.

  The method will **not** handle authentication and navigation to the themes administration page.


* `grabAttributeFrom($cssOrXpath, $attribute)` : `void`


* `grabAvailableThemes([$classes])` : `void`  
  Returns the list of available themes.

  The method will **not** handle authentication and navigation to the themes administration page.


* `grabCookie($cookie, [array $params])` : `void`


* `grabCookiesWithPattern($cookiePattern)` : `void`  
  Returns all the cookies whose name matches a regex pattern.


* `grabFromCurrentUrl([$uri])` : `void`


* `grabMultiple($cssOrXpath, [$attribute])` : `void`


* `grabPageSource()` : `void`  
  Grabs current page source code.


* `grabTextFrom($cssOrXPathOrRegex)` : `void`


* `grabValueFrom($field)` : `void`

* `grabWordPressTestCookie([$name])` : `void`  
  Returns WordPress default test cookie object if present.

* `haveHttpHeader($name, $value)` : `void`  
  Sets the HTTP header to the passed value - which is used on
  subsequent HTTP requests through PhpBrowser.

  Example:
  ```php
  <?php
  $I->haveHttpHeader('X-Requested-With', 'Codeception');
  $I->amOnPage('test-headers.php');
  ```

  To use special chars in Header Key use HTML Character Entities:
  Example:
  Header with underscore - 'Client_Id'
  should be represented as - 'Client&#x0005F;Id' or 'Client&#95;Id'

  ```php
  <?php
  $I->haveHttpHeader('Client&#95;Id', 'Codeception');
  ```


* `haveServerParameter($name, $value)` : `void`  
  Sets SERVER parameter valid for all next requests.

  ```php
  $I->haveServerParameter('name', 'value');
  ```

* `logOut([$redirectTo])` : `void`  
  Navigate to the default WordPress logout page and click the logout link.


* `loginAs($username, $password)` : `void`  
  Login as the specified user.

  The method will **not** follow redirection, after the login, to any page.


* `loginAsAdmin()` : `void`  
  Login as the administrator user using the credentials specified in the module configuration.

  The method will **not** follow redirection, after the login, to any page.


* `makeHtmlSnapshot([$name])` : `void`


* `moveBack([$numberOfSteps])` : `void`  
  Moves back in history.


* `resetCookie($cookie, [array $params])` : `void`


* `see($text, [$selector])` : `void`


* `seeCheckboxIsChecked($checkbox)` : `void`


* `seeCookie($cookie, [array $params])` : `void`


* `seeCurrentUrlEquals($uri)` : `void`


* `seeCurrentUrlMatches($uri)` : `void`


* `seeElement($selector, [$attributes])` : `void`


* `seeErrorMessage([$classes])` : `void`  
  In an administration screen look for an error admin notice.

  The check is class-based to decouple from internationalization.
  The method will **not** handle authentication and navigation the administration area.


* `seeInCurrentUrl($uri)` : `void`


* `seeInField($field, $value)` : `void`


* `seeInFormFields($formSelector, array $params)` : `void`


* `seeInSource($raw)` : `void`


* `seeInTitle($title)` : `void`


* `seeLink($text, [$url])` : `void`


* `seeMessage([$classes])` : `void`  
  In an administration screen look for an admin notice.

  The check is class-based to decouple from internationalization.
  The method will **not** handle authentication and navigation the administration area.


* `seeNumberOfElements($selector, $expected)` : `void`


* `seeOptionIsSelected($selector, $optionText)` : `void`


* `seePageNotFound()` : `void`  
  Asserts that current page has 404 response status code.

* `seePluginActivated($pluginSlug)` : `void`  
  Assert a plugin is activated in the plugin administration screen.

  The method will **not** handle authentication and navigation to the plugin administration screen.


* `seePluginDeactivated($pluginSlug)` : `void`  
  Assert a plugin is not activated in the plugins administration screen.

  The method will **not** handle authentication and navigation to the plugin administration screen.


* `seePluginInstalled($pluginSlug)` : `void`  
  Assert a plugin is installed, no matter its activation status, in the plugin administration screen.

  The method will **not** handle authentication and navigation to the plugin administration screen.


* `seeResponseCodeIs($code)` : `void`  
  Checks that response code is equal to value provided.

  ```php
  <?php
  $I->seeResponseCodeIs(200);
  
  // recommended \Codeception\Util\HttpCode
  $I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK);
  ```


* `seeResponseCodeIsBetween($from, $to)` : `void`  
  Checks that response code is between a certain range. Between actually means [from <= CODE <= to]


* `seeResponseCodeIsClientError()` : `void`  
  Checks that the response code is 4xx

* `seeResponseCodeIsRedirection()` : `void`  
  Checks that the response code 3xx

* `seeResponseCodeIsServerError()` : `void`  
  Checks that the response code is 5xx

* `seeResponseCodeIsSuccessful()` : `void`  
  Checks that the response code 2xx

* `seeThemeActivated($slug)` : `void`  
  Verifies that a theme is active.

  The method will **not** handle authentication and navigation to the themes administration page.


* `seeWpDiePage()` : `void`  
  Checks that the current page is one generated by the `wp_die` function.

  The method will try to identify the page based on the default WordPress die page HTML attributes.


* `selectOption($select, $option)` : `void`


* `sendAjaxGetRequest($uri, [$params])` : `void`  
  Sends an ajax GET request with the passed parameters.
  See `sendAjaxPostRequest()`


* `sendAjaxPostRequest($uri, [$params])` : `void`  
  Sends an ajax POST request with the passed parameters.
  The appropriate HTTP header is added automatically:
  `X-Requested-With: XMLHttpRequest`
  Example:
  ``` php
  <?php
  $I->sendAjaxPostRequest('/add-task', ['task' => 'lorem ipsum']);
  ```
  Some frameworks (e.g. Symfony) create field names in the form of an "array":
  `<input type="text" name="form[task]">`
  In this case you need to pass the fields like this:
  ``` php
  <?php
  $I->sendAjaxPostRequest('/add-task', ['form' => [
      'task' => 'lorem ipsum',
      'category' => 'miscellaneous',
  ]]);
  ```


* `sendAjaxRequest($method, $uri, [$params])` : `void`  
  Sends an ajax request, using the passed HTTP method.
  See `sendAjaxPostRequest()`
  Example:
  ``` php
  <?php
  $I->sendAjaxRequest('PUT', '/posts/7', ['title' => 'new title']);
  ```


* `setCookie($name, $val, [array $params])` : `void`


* `setHeader($name, $value)` : `void`  
  Alias to `haveHttpHeader`


* `setMaxRedirects($maxRedirects)` : `void`  
  Sets the maximum number of redirects that the Client can follow.

  ```php
  <?php
  $I->setMaxRedirects(2);
  ```


* `setServerParameters(array $params)` : `void`  
  Sets SERVER parameters valid for all next requests.
  this will remove old ones.

  ```php
  $I->setServerParameters([]);
  ```

* `startFollowingRedirects()` : `void`  
  Enables automatic redirects to be followed by the client.

  ```php
  <?php
  $I->startFollowingRedirects();
  ```


* `stopFollowingRedirects()` : `void`  
  Prevents automatic redirects to be followed by the client.

  ```php
  <?php
  $I->stopFollowingRedirects();
  ```


* `submitForm($selector, array $params, [$button])` : `void`


* `switchToIframe($name)` : `void`  
  Switch to iframe or frame on the page.

  Example:
  ``` html
  <iframe name="another_frame" src="http://example.com">
  ```

  ``` php
  <?php
  # switch to iframe
  $I->switchToIframe("another_frame");
  ```


* `uncheckOption($option)` : `void`

*This class extends \Codeception\Module\PhpBrowser*

*This class implements \Codeception\Lib\Interfaces\MultiSession, \Codeception\Lib\Interfaces\Remote, \Codeception\Lib\Interfaces\Web, \Codeception\Lib\Interfaces\PageSourceSaver, \Codeception\Lib\Interfaces\ElementLocator, \Codeception\Lib\Interfaces\ConflictsWithModule*

<!--/doc-->

## WPBrowser module

Browse and test the site HTML with a fast browser without Javascript support.

This module trades the [WPWebDriver module](WPWebDriver.md) Javascript support for speed and stability. It is a good
choice for testing sites that don't use Javascript or to make assertions that do not require Javascript support like:

* testing HTTP return codes
* testing HTML structure
* testing JSON and XML responses from APIs

This module is used together with [the WPDb module](WPDb.md) and [the WPFilesystem module](WPFilesystem.md) to
control the site state, the database, and the site file structure.

This module is an extension of [the Codeception PHPBrowser module][1], you can reference to the Codeception module
documentation for more information on the module configuration and usage.

This module should be with [Cest][4] and [Cept][5] test cases.

## Configuration

* `url` - **required**; the start URL of your WordPress project.
* `adminUsername` - **required**; the site administrator username to use in actions like `loginAsAdmin`.
* `adminPassword` - **required**; the site administrator password to use in actions like `loginAsAdmin`.
* `adminPath` - the path to the WordPress admin directory; defaults to `/wp-admin`.

More [Guzzle request options][2] are available like:

`headers` - default headers are set before each test.  
`cookies` - default cookies are set before each test.  
`auth` - default authentication to be set before each test.

[... and more.][2]

The following is an example of the module configuration to run tests on the`http://localhost:8080` site:

```yaml
modules:
  enabled:
    lucatume\WPBrowser\Module\WPBrowser:
      url: 'http://localhost:8080'
      adminUsername: 'admin'
      adminPassword: 'password'
      adminPath: '/wp-admin'
      headers:
        X_WPBROWSER_REQUEST: 1
        X_TEST_REQUEST: 1
        X_APM_REQUEST: 1
```

The following configuration uses [dynamic configuration parameters][3] to set the module configuration:

```yaml
modules:
  enabled:
    lucatume\WPBrowser\Module\WPBrowser:
      url: '%WORDPRESS_URL%'
      adminUsername: '%WORDPRESS_ADMIN_USER%'
      adminPassword: '%WORDPRESS_ADMIN_PASSWORD%'
      adminPath: '/wp-admin'
      headers:
        X_WPBROWSER_REQUEST: 1
        X_TEST_REQUEST: 1
        X_APM_REQUEST: 1
```

## Methods

The module provides the following methods:

<!-- methods -->

#### activatePlugin
Signature: `activatePlugin(array|string $pluginSlug)` : `void`  

In the plugin administration screen activates a plugin clicking the "Activate" link.

The method will **not** handle authentication to the admin area.

```php
<?php
// Activate a plugin.
$I->loginAsAdmin();
$I->amOnPluginsPage();
$I->activatePlugin('hello-dolly');
// Activate a list of plugins.
$I->loginAsAdmin();
$I->amOnPluginsPage();
$I->activatePlugin(['hello-dolly','another-plugin']);
```

#### activateTheme
Signature: `activateTheme(string $slug)` : `void`  

Activates a theme.

The method will **not** handle authentication and navigation to the themes administration page.

#### amEditingPostWithId
Signature: `amEditingPostWithId(int $id)` : `void`  

Go to the admin page to edit the post with the specified ID.

The method will **not** handle authentication the admin area.

```php
<?php
$I->loginAsAdmin();
$postId = $I->havePostInDatabase();
$I->amEditingPostWithId($postId);
$I->fillField('post_title', 'Post title');
```

#### amHttpAuthenticated
Signature: `amHttpAuthenticated($username, $password)` : `void`  


#### amOnAdminAjaxPage
Signature: `amOnAdminAjaxPage([array|string|null $queryVars])` : `void`  

Go to the `admin-ajax.php` page to start a synchronous, and blocking, `GET` AJAX request.

The method will **not** handle authentication, nonces or authorization.

```php
<?php
$I->amOnAdminAjaxPage(['action' => 'my-action', 'data' => ['id' => 23], 'nonce' => $nonce]);
```

#### amOnAdminPage
Signature: `amOnAdminPage(string $page)` : `void`  

Go to a page in the admininstration area of the site.

This method will **not** handle authentication to the administration area.


```php
<?php
$I->loginAs('user', 'password');
// Go to the plugins management screen.
$I->amOnAdminPage('/plugins.php');
```

#### amOnCronPage
Signature: `amOnCronPage([array|string|null $queryVars])` : `void`  

Go to the cron page to start a synchronous, and blocking, `GET` request to the cron script.

```php
<?php
// Triggers the cron job with an optional query argument.
$I->amOnCronPage('/?some-query-var=some-value');
```

#### amOnPage
Signature: `amOnPage(string $page)` : `void`  


#### amOnPagesPage
Signature: `amOnPagesPage()` : `void`  

Go the "Pages" administration screen.

The method will **not** handle authentication.

```php
<?php
$I->loginAsAdmin();
$I->amOnPagesPage();
$I->see('Add New');
```
#### amOnPluginsPage
Signature: `amOnPluginsPage()` : `void`  

Go to the plugins administration screen.

 The method will **not** handle authentication.

```php
<?php
$I->loginAsAdmin();
$I->amOnPluginsPage();
$I->activatePlugin('hello-dolly');
```


#### amOnSubdomain
Signature: `amOnSubdomain($subdomain)` : `void`  


#### amOnThemesPage
Signature: `amOnThemesPage()` : `void`  

Moves to the themes administration page.

#### amOnUrl
Signature: `amOnUrl($url)` : `void`  


#### attachFile
Signature: `attachFile($field, string $filename)` : `void`  


#### checkOption
Signature: `checkOption($option)` : `void`  


#### click
Signature: `click($link, [$context])` : `void`  


#### deactivatePlugin
Signature: `deactivatePlugin(array|string $pluginSlug)` : `void`  

In the plugin administration screen deactivate a plugin clicking the "Deactivate" link.

The method will **not** handle authentication and navigation to the plugins administration page.

```php
<?php
// Deactivate one plugin.
$I->loginAsAdmin();
$I->amOnPluginsPage();
$I->deactivatePlugin('hello-dolly');
// Deactivate a list of plugins.
$I->loginAsAdmin();
$I->amOnPluginsPage();
$I->deactivatePlugin(['hello-dolly', 'my-plugin']);
```

#### deleteHeader
Signature: `deleteHeader(string $name)` : `void`  

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

#### dontSee
Signature: `dontSee(string $text, [$selector])` : `void`  


#### dontSeeCheckboxIsChecked
Signature: `dontSeeCheckboxIsChecked($checkbox)` : `void`  


#### dontSeeCookie
Signature: `dontSeeCookie($cookie, [$params])` : `void`  


#### dontSeeCurrentUrlEquals
Signature: `dontSeeCurrentUrlEquals(string $uri)` : `void`  


#### dontSeeCurrentUrlMatches
Signature: `dontSeeCurrentUrlMatches(string $uri)` : `void`  


#### dontSeeElement
Signature: `dontSeeElement($selector, [array $attributes])` : `void`  


#### dontSeeInCurrentUrl
Signature: `dontSeeInCurrentUrl(string $uri)` : `void`  


#### dontSeeInField
Signature: `dontSeeInField($field, $value)` : `void`  


#### dontSeeInFormFields
Signature: `dontSeeInFormFields($formSelector, array $params)` : `void`  


#### dontSeeInSource
Signature: `dontSeeInSource(string $raw)` : `void`  


#### dontSeeInTitle
Signature: `dontSeeInTitle($title)` : `void`  


#### dontSeeLink
Signature: `dontSeeLink(string $text, [string $url])` : `void`  


#### dontSeeOptionIsSelected
Signature: `dontSeeOptionIsSelected($selector, $optionText)` : `void`  


#### dontSeePluginInstalled
Signature: `dontSeePluginInstalled(string $pluginSlug)` : `void`  

Assert a plugin is not installed in the plugins administration screen.

The method will **not** handle authentication and navigation to the plugin administration screen.

```php
<?php
$I->loginAsAdmin();
$I->amOnPluginsPage();
$I->dontSeePluginInstalled('my-plugin');
```

#### dontSeeResponseCodeIs
Signature: `dontSeeResponseCodeIs(int $code)` : `void`  

Checks that response code is equal to value provided.

```php
<?php
$I->dontSeeResponseCodeIs(200);

// recommended \Codeception\Util\HttpCode
$I->dontSeeResponseCodeIs(\Codeception\Util\HttpCode::OK);
```
#### executeInGuzzle
Signature: `executeInGuzzle(Closure $function)` : `void`  

Low-level API method.
If Codeception commands are not enough, use [Guzzle HTTP Client](https://guzzlephp.org/) methods directly

Example:

``` php
<?php
$I->executeInGuzzle(function (\GuzzleHttp\Client $client) {
     $client->get('/get', ['query' => ['foo' => 'bar']]);
});
```

It is not recommended to use this command on a regular basis.
If Codeception lacks important Guzzle Client methods, implement them and submit patches.

#### fillField
Signature: `fillField($field, $value)` : `void`  


#### followRedirect
Signature: `followRedirect()` : `void`  

Follow pending redirect if there is one.

```php
<?php
$I->followRedirect();
```
#### grabActiveTheme
Signature: `grabActiveTheme()` : `?string`  

Returns the slug of the currently active themes.

The method will **not** handle authentication and navigation to the themes administration page.

#### grabAttributeFrom
Signature: `grabAttributeFrom($cssOrXpath, string $attribute)` : `mixed`  


#### grabAvailableThemes
Signature: `grabAvailableThemes([string $classes])` : `array`  

Returns the list of available themes.

The method will **not** handle authentication and navigation to the themes administration page.

#### grabCookie
Signature: `grabCookie(string $cookie, [array $params])` : `mixed`  


#### grabCookiesWithPattern
Signature: `grabCookiesWithPattern(string $cookiePattern)` : `?array`  

Returns all the cookies whose name matches a regex pattern.

```php
<?php
$I->loginAs('customer','password');
$I->amOnPage('/shop');
$cartCookies = $I->grabCookiesWithPattern("#^shop_cart\\.*#");
```
#### grabFromCurrentUrl
Signature: `grabFromCurrentUrl([?string $uri])` : `mixed`  


#### grabMultiple
Signature: `grabMultiple($cssOrXpath, [?string $attribute])` : `array`  


#### grabPageSource
Signature: `grabPageSource()` : `string`  

Grabs current page source code.

#### grabTextFrom
Signature: `grabTextFrom($cssOrXPathOrRegex)` : `mixed`  


#### grabValueFrom
Signature: `grabValueFrom($field)` : `mixed`  


#### grabWordPressTestCookie
Signature: `grabWordPressTestCookie([?string $name])` : `?Symfony\Component\BrowserKit\Cookie`  

Returns WordPress default test cookie object if present.

```php
<?php
// Grab the default WordPress test cookie.
$wpTestCookie = $I->grabWordPressTestCookie();
// Grab a customized version of the test cookie.
$myTestCookie = $I->grabWordPressTestCookie('my_test_cookie');
```

#### haveHttpHeader
Signature: `haveHttpHeader(string $name, string $value)` : `void`  

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

#### haveServerParameter
Signature: `haveServerParameter(string $name, string $value)` : `void`  

Sets SERVER parameter valid for all next requests.

```php
<?php
$I->haveServerParameter('name', 'value');
```
#### logOut
Signature: `logOut([string|bool $redirectTo])` : `void`  

Navigate to the default WordPress logout page and click the logout link.

```php
<?php
// Log out using the `wp-login.php` form and return to the current page.
$I->logOut(true);
// Log out using the `wp-login.php` form and remain there.
$I->logOut(false);
// Log out using the `wp-login.php` form and move to another page.
$I->logOut('/some-other-page');
```

#### loginAs
Signature: `loginAs(string $username, string $password)` : `void`  

Login as the specified user.

The method will **not** follow redirection, after the login, to any page.

```php
<?php
$I->loginAs('user', 'password');
$I->amOnAdminPage('/');
$I->see('Dashboard');
```

#### loginAsAdmin
Signature: `loginAsAdmin()` : `void`  

Login as the administrator user using the credentials specified in the module configuration.

The method will **not** follow redirection, after the login, to any page.

```php
<?php
$I->loginAsAdmin();
$I->amOnAdminPage('/');
$I->see('Dashboard');
```
#### makeHtmlSnapshot
Signature: `makeHtmlSnapshot([?string $name])` : `void`  


#### moveBack
Signature: `moveBack([int $numberOfSteps])` : `void`  

Moves back in history.

#### resetCookie
Signature: `resetCookie($cookie, [$params])` : `void`  


#### see
Signature: `see(string $text, [$selector])` : `void`  


#### seeCheckboxIsChecked
Signature: `seeCheckboxIsChecked($checkbox)` : `void`  


#### seeCookie
Signature: `seeCookie($cookie, [$params])` : `void`  


#### seeCurrentUrlEquals
Signature: `seeCurrentUrlEquals(string $uri)` : `void`  


#### seeCurrentUrlMatches
Signature: `seeCurrentUrlMatches(string $uri)` : `void`  


#### seeElement
Signature: `seeElement($selector, [array $attributes])` : `void`  


#### seeErrorMessage
Signature: `seeErrorMessage([array|string $classes])` : `void`  

In an administration screen look for an error admin notice.

The check is class-based to decouple from internationalization.
The method will **not** handle authentication and navigation the administration area.

```php
<?php
$I->loginAsAdmin()
$I->amOnAdminPage('/');
$I->seeErrorMessage('.my-plugin');
```

#### seeInCurrentUrl
Signature: `seeInCurrentUrl(string $uri)` : `void`  


#### seeInField
Signature: `seeInField($field, $value)` : `void`  


#### seeInFormFields
Signature: `seeInFormFields($formSelector, array $params)` : `void`  


#### seeInSource
Signature: `seeInSource(string $raw)` : `void`  


#### seeInTitle
Signature: `seeInTitle($title)` : `void`  


#### seeLink
Signature: `seeLink(string $text, [?string $url])` : `void`  


#### seeMessage
Signature: `seeMessage([array|string $classes])` : `void`  

In an administration screen look for an admin notice.

The check is class-based to decouple from internationalization.
The method will **not** handle authentication and navigation the administration area.

```php
<?php
$I->loginAsAdmin()
$I->amOnAdminPage('/');
$I->seeMessage('.missing-api-token.my-plugin');
```

#### seeNumberOfElements
Signature: `seeNumberOfElements($selector, $expected)` : `void`  


#### seeOptionIsSelected
Signature: `seeOptionIsSelected($selector, $optionText)` : `void`  


#### seePageNotFound
Signature: `seePageNotFound()` : `void`  

Asserts that current page has 404 response status code.
#### seePluginActivated
Signature: `seePluginActivated(string $pluginSlug)` : `void`  

Assert a plugin is activated in the plugin administration screen.

The method will **not** handle authentication and navigation to the plugin administration screen.

```php
<?php
$I->loginAsAdmin();
$I->amOnPluginsPage();
$I->seePluginActivated('my-plugin');
```

#### seePluginDeactivated
Signature: `seePluginDeactivated(string $pluginSlug)` : `void`  

Assert a plugin is not activated in the plugins administration screen.

The method will **not** handle authentication and navigation to the plugin administration screen.

```php
<?php
$I->loginAsAdmin();
$I->amOnPluginsPage();
$I->seePluginDeactivated('my-plugin');
```

#### seePluginInstalled
Signature: `seePluginInstalled(string $pluginSlug)` : `void`  

Assert a plugin is installed, no matter its activation status, in the plugin administration screen.

The method will **not** handle authentication and navigation to the plugin administration screen.

```php
<?php
$I->loginAsAdmin();
$I->amOnPluginsPage();
$I->seePluginInstalled('my-plugin');
```

#### seeResponseCodeIs
Signature: `seeResponseCodeIs(int $code)` : `void`  

Checks that response code is equal to value provided.

```php
<?php
$I->seeResponseCodeIs(200);

// recommended \Codeception\Util\HttpCode
$I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK);
```
#### seeResponseCodeIsBetween
Signature: `seeResponseCodeIsBetween(int $from, int $to)` : `void`  

Checks that response code is between a certain range. Between actually means [from <= CODE <= to]
#### seeResponseCodeIsClientError
Signature: `seeResponseCodeIsClientError()` : `void`  

Checks that the response code is 4xx
#### seeResponseCodeIsRedirection
Signature: `seeResponseCodeIsRedirection()` : `void`  

Checks that the response code 3xx
#### seeResponseCodeIsServerError
Signature: `seeResponseCodeIsServerError()` : `void`  

Checks that the response code is 5xx
#### seeResponseCodeIsSuccessful
Signature: `seeResponseCodeIsSuccessful()` : `void`  

Checks that the response code 2xx
#### seeThemeActivated
Signature: `seeThemeActivated(string $slug)` : `void`  

Verifies that a theme is active.

The method will **not** handle authentication and navigation to the themes administration page.

#### seeWpDiePage
Signature: `seeWpDiePage()` : `void`  

Checks that the current page is one generated by the `wp_die` function.

The method will try to identify the page based on the default WordPress die page HTML attributes.

```php
<?php
$I->loginAs('user', 'password');
$I->amOnAdminPage('/forbidden');
$I->seeWpDiePage();
```
#### selectOption
Signature: `selectOption($select, $option)` : `void`  


#### sendAjaxGetRequest
Signature: `sendAjaxGetRequest(string $uri, [array $params])` : `void`  

Sends an ajax GET request with the passed parameters.
See `sendAjaxPostRequest()`
#### sendAjaxPostRequest
Signature: `sendAjaxPostRequest(string $uri, [array $params])` : `void`  

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
#### sendAjaxRequest
Signature: `sendAjaxRequest(string $method, string $uri, [array $params])` : `void`  

Sends an ajax request, using the passed HTTP method.
See `sendAjaxPostRequest()`
Example:
``` php
<?php
$I->sendAjaxRequest('PUT', '/posts/7', ['title' => 'new title']);
```
#### setCookie
Signature: `setCookie($name, $val, [$params])` : `void`  


#### setHeader
Signature: `setHeader(string $name, string $value)` : `void`  

Alias to `haveHttpHeader`
#### setMaxRedirects
Signature: `setMaxRedirects(int $maxRedirects)` : `void`  

Sets the maximum number of redirects that the Client can follow.

```php
<?php
$I->setMaxRedirects(2);
```
#### setServerParameters
Signature: `setServerParameters(array $params)` : `void`  

Sets SERVER parameters valid for all next requests.
this will remove old ones.

```php
<?php
$I->setServerParameters([]);
```
#### startFollowingRedirects
Signature: `startFollowingRedirects()` : `void`  

Enables automatic redirects to be followed by the client.

```php
<?php
$I->startFollowingRedirects();
```
#### stopFollowingRedirects
Signature: `stopFollowingRedirects()` : `void`  

Prevents automatic redirects to be followed by the client.

```php
<?php
$I->stopFollowingRedirects();
```
#### submitForm
Signature: `submitForm($selector, array $params, [?string $button])` : `void`  


#### switchToIframe
Signature: `switchToIframe(string $name)` : `void`  

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
#### uncheckOption
Signature: `uncheckOption($option)` : `void`  

<!-- /methods -->

Read more [in Codeception documentation.][1]

[1]: https://codeception.com/docs/modules/PhpBrowser

[2]: https://docs.guzzlephp.org/en/latest/request-options.html

[3]: https://codeception.com/docs/ModulesAndHelpers#Dynamic-Configuration-With-Parameters

[4]: https://codeception.com/docs/AcceptanceTests

[5]: https://codeception.com/docs/AdvancedUsage#Cest-Classes 

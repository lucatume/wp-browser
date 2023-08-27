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

* `activatePlugin(array|string $pluginSlug)` : `void`
* `amEditingPostWithId(int $id)` : `void`
* `amHttpAuthenticated($username, $password)` : `void`
* `amOnAdminAjaxPage(array|string|null [$queryVars])` : `void`
* `amOnAdminPage(string $page)` : `void`
* `amOnCronPage(array|string|null [$queryVars])` : `void`
* `amOnPage(string $page)` : `void`
* `amOnPagesPage()` : `void`
* `amOnPluginsPage()` : `void`
* `amOnSubdomain($subdomain)` : `void`
* `amOnUrl($url)` : `void`
* `attachFile($field, string $filename)` : `void`
* `checkOption($option)` : `void`
* `click($link, [$context])` : `void`
* `deactivatePlugin(array|string $pluginSlug)` : `void`
* `deleteHeader(string $name)` : `void`
* `dontSee(string $text, [$selector])` : `void`
* `dontSeeCheckboxIsChecked($checkbox)` : `void`
* `dontSeeCookie($cookie, [$params])` : `void`
* `dontSeeCurrentUrlEquals(string $uri)` : `void`
* `dontSeeCurrentUrlMatches(string $uri)` : `void`
* `dontSeeElement($selector, array [$attributes])` : `void`
* `dontSeeInCurrentUrl(string $uri)` : `void`
* `dontSeeInField($field, $value)` : `void`
* `dontSeeInFormFields($formSelector, array $params)` : `void`
* `dontSeeInSource(string $raw)` : `void`
* `dontSeeInTitle($title)` : `void`
* `dontSeeLink(string $text, string [$url])` : `void`
* `dontSeeOptionIsSelected($selector, $optionText)` : `void`
* `dontSeePluginInstalled(string $pluginSlug)` : `void`
* `dontSeeResponseCodeIs(int $code)` : `void`
* `executeInGuzzle(Closure $function)` : `void`
* `fillField($field, $value)` : `void`
* `followRedirect()` : `void`
* `grabAttributeFrom($cssOrXpath, string $attribute)` : `mixed`
* `grabCookie(string $cookie, array [$params])` : `mixed`
* `grabCookiesWithPattern(string $cookiePattern)` : `?array`
* `grabFromCurrentUrl(?string [$uri])` : `mixed`
* `grabMultiple($cssOrXpath, ?string [$attribute])` : `array`
* `grabPageSource()` : `string`
* `grabTextFrom($cssOrXPathOrRegex)` : `mixed`
* `grabValueFrom($field)` : `mixed`
* `grabWordPressTestCookie(?string [$name])` : `?Symfony\Component\BrowserKit\Cookie`
* `haveHttpHeader(string $name, string $value)` : `void`
* `haveServerParameter(string $name, string $value)` : `void`
* `logOut(string|bool [$redirectTo])` : `void`
* `loginAs(string $username, string $password)` : `void`
* `loginAsAdmin()` : `void`
* `makeHtmlSnapshot(?string [$name])` : `void`
* `moveBack(int [$numberOfSteps])` : `void`
* `resetCookie($cookie, [$params])` : `void`
* `see(string $text, [$selector])` : `void`
* `seeCheckboxIsChecked($checkbox)` : `void`
* `seeCookie($cookie, [$params])` : `void`
* `seeCurrentUrlEquals(string $uri)` : `void`
* `seeCurrentUrlMatches(string $uri)` : `void`
* `seeElement($selector, array [$attributes])` : `void`
* `seeErrorMessage(array|string [$classes])` : `void`
* `seeInCurrentUrl(string $uri)` : `void`
* `seeInField($field, $value)` : `void`
* `seeInFormFields($formSelector, array $params)` : `void`
* `seeInSource(string $raw)` : `void`
* `seeInTitle($title)` : `void`
* `seeLink(string $text, ?string [$url])` : `void`
* `seeMessage(array|string [$classes])` : `void`
* `seeNumberOfElements($selector, $expected)` : `void`
* `seeOptionIsSelected($selector, $optionText)` : `void`
* `seePageNotFound()` : `void`
* `seePluginActivated(string $pluginSlug)` : `void`
* `seePluginDeactivated(string $pluginSlug)` : `void`
* `seePluginInstalled(string $pluginSlug)` : `void`
* `seeResponseCodeIs(int $code)` : `void`
* `seeResponseCodeIsBetween(int $from, int $to)` : `void`
* `seeResponseCodeIsClientError()` : `void`
* `seeResponseCodeIsRedirection()` : `void`
* `seeResponseCodeIsServerError()` : `void`
* `seeResponseCodeIsSuccessful()` : `void`
* `seeWpDiePage()` : `void`
* `selectOption($select, $option)` : `void`
* `sendAjaxGetRequest(string $uri, array [$params])` : `void`
* `sendAjaxPostRequest(string $uri, array [$params])` : `void`
* `sendAjaxRequest(string $method, string $uri, array [$params])` : `void`
* `setCookie($name, $val, [$params])` : `void`
* `setHeader(string $name, string $value)` : `void`
* `setMaxRedirects(int $maxRedirects)` : `void`
* `setServerParameters(array $params)` : `void`
* `startFollowingRedirects()` : `void`
* `stopFollowingRedirects()` : `void`
* `submitForm($selector, array $params, ?string [$button])` : `void`
* `switchToIframe(string $name)` : `void`
* `uncheckOption($option)` : `void`

Read more [in Codeception documentation.][1]

[1]: https://codeception.com/docs/modules/PhpBrowser

[2]: https://docs.guzzlephp.org/en/latest/request-options.html

[3]: https://codeception.com/docs/ModulesAndHelpers#Dynamic-Configuration-With-Parameters

[4]: https://codeception.com/docs/AcceptanceTests

[5]: https://codeception.com/docs/AdvancedUsage#Cest-Classes 

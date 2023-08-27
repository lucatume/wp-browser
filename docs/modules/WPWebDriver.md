## WPWebDriver module

This module drives a browser using a solution like [Selenium][1] or [Chromedriver][2] to simulate user interactions with
the WordPress project.

The module has full Javascript support, differently from [the WPBrowser module](WPBrowser.md), and can be used to test
sites that use Javascript to render the page or to make assertions that require Javascript support.

The method extends [the Codeception WebDriver module][3] and is used in the context of [Cest][4] and [Cept][5] test
cases.

## Configuration

* `browser` - the browser to use; e.g. 'chrome'
* `url` - **required**; the start URL of your WordPress project.
* `adminUsername` - **required**; the site administrator username to use in actions like `loginAsAdmin`.
* `adminPassword` - **required**; the site administrator password to use in actions like `loginAsAdmin`.
* `adminPath` - the path to the WordPress admin directory; defaults to `/wp-admin`.

More configuration options, and their explanation, are available in [the Codeception WebDriver module documentation][3].

The following is an example of the module configuration to run tests on the`http://localhost:8080` site:

```yaml
modules:
  enabled:
    lucatume\WPBrowser\Module\WPBrowser:
      url: 'http://localhost:8080'
      adminUsername: 'admin'
      adminPassword: 'password'
      adminPath: '/wp-admin'
      browser: chrome
      host: 'localhost'
      port: '4444'
      window_size: false
      capabilities:
        chromeOptions:
          args: [ "--headless", "--disable-gpu", "--proxy-server='direct://'", "--proxy-bypass-list=*", "--no-sandbox", "--disable-dev-shm-usage" ]
```

The following configuration uses [dynamic configuration parameters][3] to set the module configuration:

```yaml
modules:
  enabled:
    lucatume\WPBrowser\Module\WPBrowser:
      url: 'http://localhost:8080'
      adminUsername: 'admin'
      adminPassword: 'password'
      adminPath: '/wp-admin'
      browser: chrome
      host: '%CHROME_HOST%'
      port: '%CHROME_PORT%'
      window_size: `1920,1080`
      capabilities:
        chromeOptions:
          args: [ "--disable-gpu", "--proxy-server='direct://'", "--proxy-bypass-list=*", "--no-sandbox", "--disable-dev-shm-usage" ]
```

Furthermore, the above configuration will **not** run Chrome in headless mode: the browser window will be visible.

## Methods

The module provides the following methods:

* `acceptPopup()` : `void`
* `activatePlugin(array|string $pluginSlug)` : `void`
* `amEditingPostWithId(int $id)` : `void`
* `amOnAdminAjaxPage(array|string|null [$queryVars])` : `void`
* `amOnAdminPage(string $page)` : `void`
* `amOnCronPage(array|string|null [$queryVars])` : `void`
* `amOnPage($page)` : `void`
* `amOnPagesPage()` : `void`
* `amOnPluginsPage()` : `void`
* `amOnSubdomain(string $subdomain)` : `void`
* `amOnUrl($url)` : `void`
* `appendField($field, string $value)` : `void`
* `attachFile($field, string $filename)` : `void`
* `cancelPopup()` : `void`
* `checkOption($option)` : `void`
* `clearField($field)` : `void`
* `click($link, [$context])` : `void`
* `clickWithLeftButton([$cssOrXPath], ?int [$offsetX], ?int [$offsetY])` : `void`
* `clickWithRightButton([$cssOrXPath], ?int [$offsetX], ?int [$offsetY])` : `void`
* `closeTab()` : `void`
* `deactivatePlugin(array|string $pluginSlug)` : `void`
* `debugWebDriverLogs(?Codeception\TestInterface [$test])` : `void`
* `deleteSessionSnapshot($name)` : `void`
* `dontSee($text, [$selector])` : `void`
* `dontSeeCheckboxIsChecked($checkbox)` : `void`
* `dontSeeCookie($cookie, array [$params], bool [$showDebug])` : `void`
* `dontSeeCurrentUrlEquals(string $uri)` : `void`
* `dontSeeCurrentUrlMatches(string $uri)` : `void`
* `dontSeeElement($selector, array [$attributes])` : `void`
* `dontSeeElementInDOM($selector, array [$attributes])` : `void`
* `dontSeeInCurrentUrl(string $uri)` : `void`
* `dontSeeInField($field, $value)` : `void`
* `dontSeeInFormFields($formSelector, array $params)` : `void`
* `dontSeeInPageSource(string $text)` : `void`
* `dontSeeInPopup(string $text)` : `void`
* `dontSeeInSource($raw)` : `void`
* `dontSeeInTitle($title)` : `void`
* `dontSeeLink(string $text, string [$url])` : `void`
* `dontSeeOptionIsSelected($selector, $optionText)` : `void`
* `dontSeePluginInstalled(string $pluginSlug)` : `void`
* `doubleClick($cssOrXPath)` : `void`
* `dragAndDrop($source, $target)` : `void`
* `executeAsyncJS(string $script, array [$arguments])` : `void`
* `executeInSelenium(Closure $function)` : `void`
* `executeJS(string $script, array [$arguments])` : `void`
* `fillField($field, $value)` : `void`
* `grabAttributeFrom($cssOrXpath, $attribute)` : `?string`
* `grabCookie($cookie, array [$params])` : `mixed`
* `grabCookiesWithPattern(string $cookiePattern)` : `?array`
* `grabFromCurrentUrl([$uri])` : `mixed`
* `grabFullUrl()` : `string`
* `grabMultiple($cssOrXpath, [$attribute])` : `array`
* `grabPageSource()` : `string`
* `grabTextFrom($cssOrXPathOrRegex)` : `mixed`
* `grabValueFrom($field)` : `?string`
* `grabWordPressTestCookie(?string [$name])` : `?Symfony\Component\BrowserKit\Cookie`
* `loadSessionSnapshot($name, bool [$showDebug])` : `bool`
* `logOut(string|bool [$redirectTo])` : `void`
* `loginAs(string $username, string $password, int [$timeout], int [$maxAttempts])` : `void`
* `loginAsAdmin(int [$timeout], int [$maxAttempts])` : `void`
* `makeElementScreenshot($selector, ?string [$name])` : `void`
* `makeHtmlSnapshot(?string [$name])` : `void`
* `makeScreenshot(?string [$name])` : `void`
* `maximizeWindow()` : `void`
* `moveBack()` : `void`
* `moveForward()` : `void`
* `moveMouseOver([$cssOrXPath], ?int [$offsetX], ?int [$offsetY])` : `void`
* `openNewTab()` : `void`
* `performOn($element, $actions, int [$timeout])` : `void`
* `pressKey($element, ...[$chars])` : `void`
* `reloadPage()` : `void`
* `resetCookie($cookie, array [$params], bool [$showDebug])` : `void`
* `resizeWindow(int $width, int $height)` : `void`
* `saveSessionSnapshot($name)` : `void`
* `scrollTo($selector, ?int [$offsetX], ?int [$offsetY])` : `void`
* `see($text, [$selector])` : `void`
* `seeCheckboxIsChecked($checkbox)` : `void`
* `seeCookie($cookie, array [$params], bool [$showDebug])` : `void`
* `seeCurrentUrlEquals(string $uri)` : `void`
* `seeCurrentUrlMatches(string $uri)` : `void`
* `seeElement($selector, array [$attributes])` : `void`
* `seeElementInDOM($selector, array [$attributes])` : `void`
* `seeErrorMessage(array|string [$classes])` : `void`
* `seeInCurrentUrl(string $uri)` : `void`
* `seeInField($field, $value)` : `void`
* `seeInFormFields($formSelector, array $params)` : `void`
* `seeInPageSource(string $text)` : `void`
* `seeInPopup(string $text)` : `void`
* `seeInSource($raw)` : `void`
* `seeInTitle($title)` : `void`
* `seeLink(string $text, ?string [$url])` : `void`
* `seeMessage(array|string [$classes])` : `void`
* `seeNumberOfElements($selector, $expected)` : `void`
* `seeNumberOfElementsInDOM($selector, $expected)` : `void`
* `seeNumberOfTabs(int $number)` : `void`
* `seeOptionIsSelected($selector, $optionText)` : `void`
* `seePluginActivated(string $pluginSlug)` : `void`
* `seePluginDeactivated(string $pluginSlug)` : `void`
* `seePluginInstalled(string $pluginSlug)` : `void`
* `seeWpDiePage()` : `void`
* `selectOption($select, $option)` : `void`
* `setCookie($name, $value, array [$params], [$showDebug])` : `void`
* `submitForm($selector, array $params, [$button])` : `void`
* `switchToFrame(?string [$locator])` : `void`
* `switchToIFrame(?string [$locator])` : `void`
* `switchToNextTab(int [$offset])` : `void`
* `switchToPreviousTab(int [$offset])` : `void`
* `switchToWindow(?string [$name])` : `void`
* `type(string $text, int [$delay])` : `void`
* `typeInPopup(string $keys)` : `void`
* `uncheckOption($option)` : `void`
* `unselectOption($select, $option)` : `void`
* `wait($timeout)` : `void`
* `waitForElement($element, int [$timeout])` : `void`
* `waitForElementChange($element, Closure $callback, int [$timeout])` : `void`
* `waitForElementClickable($element, int [$timeout])` : `void`
* `waitForElementNotVisible($element, int [$timeout])` : `void`
* `waitForElementVisible($element, int [$timeout])` : `void`
* `waitForJS(string $script, int [$timeout])` : `void`
* `waitForJqueryAjax(int [$time])` : `void`
* `waitForText(string $text, int [$timeout], [$selector])` : `void`

Read more [in Codeception documentation.][3]

[1]: https://www.seleniumhq.org/

[2]: https://sites.google.com/a/chromium.org/chromedriver/

[3]: https://codeception.com/docs/modules/WebDriver

[4]: https://codeception.com/docs/02-GettingStarted#Cest

[5]: https://codeception.com/docs/02-GettingStarted#Cept

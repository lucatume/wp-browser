> **This is the documentation for version 3 of the project.**
> **The current version is version 4 and the documentation can be found [here](./../README.md).**

# WpWebDriver module
This module should be used in acceptance tests, see [levels of testing for more information](./../levels-of-testing.md).  

This module extends the [WebDriver module](https://codeception.com/docs/modules/WebDriver) adding WordPress-specific configuration parameters and methods.  

The module simulates a user interaction with the site **with Javascript support**; if you don't need to test your project with Javascript support use the [WPBrowser module](WPBrowser.md).  

## Module requirements for Codeception 4.0+

This module requires the `codeception/module-webdriver` Composer package to work when wp-browser is used with Codeception 4.0.  

To install the package run: 

```bash
composer require --dev codeception/module-webdriver:^1.0
```

## Configuration

Due to the combination of possible browsers, capabilities and configurations, it's not possible to provide an exhaustive coverage of all the possible configuration parameteters here.  

Please refer to [WebDriver documentation](https://codeception.com/docs/modules/WebDriver) for more information.

* `url` *required* - Start URL of your WordPress project, e.g. `http://wp.test`.
* `adminUsername` *required* - This is the login name, not the "nice" name, of the administrator user of the WordPress test site. This will be used to fill the username field in WordPress login page.  
* `adminPassword` *required* - This is the the password of the administrator use of the WordPress test site. This will be used to fill the password in WordPress login page.  
* `adminPath` *required* - The path, relative to the WordPress test site home URL, to the administration area, usually `/wp-admin`.
* `browser` - The browser to use for the tests, e.g. `chrome` or `firefox`.
* `capabilities` - Depending on the browser set in `browser` this is a list of browser-specific capabilities.

## Example configuration

```yaml
modules:
  enabled:
    - WPWebDriver
  config:
    WPWebDriver:
      url: 'http://wp.test'
      adminUsername: 'admin'
      adminPassword: 'password'
      adminPath: '/wp-admin'
      browser: chrome
      host: localhost
      port: 4444
      window_size: false #disabled for Chrome driver
      capabilities:
        "goog:chromeOptions":
          args:
            - "--headless"
            - "--disable-gpu"
            - "--disable-dev-shm-usage"
            - "--proxy-server='direct://'"
            - "--proxy-bypass-list=*"
            - "--no-sandbox"
```

<!--doc-->


## Public API
* `acceptPopup()` : `void`  
  Accepts the active JavaScript native popup window, as created by `window.alert`|`window.confirm`|`window.prompt`.
  Don't confuse popups with modal windows,
  as created by [various libraries](http://jster.net/category/windows-modals-popups).

* `activatePlugin($pluginSlug)` : `void`  
  In the plugin administration screen activates one or more plugins clicking the "Activate" link.

  The method will **not** handle authentication and navigation to the plugins administration page.


* `activateTheme($slug)` : `void`  
  Activates a theme.

  The method will **not** handle authentication and navigation to the themes administration page.


* `amEditingPostWithId($id)` : `void`  
  Go to the admin page to edit the post with the specified ID.

  The method will **not** handle authentication the admin area.


* `amEditingUserWithId($id)` : `void`  
  Go to the admin page to edit the user with the specified ID.

  The method will **not** handle authentication the admin area.


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


* `appendField($field, $value)` : `void`  
  Append the given text to the given element.
  Can also add a selection to a select box.

  ``` php
  <?php
  $I->appendField('#mySelectbox', 'SelectValue');
  $I->appendField('#myTextField', 'appended');
  ?>
  ```


* `attachFile($field, $filename)` : `void`


* `cancelPopup()` : `void`  
  Dismisses the active JavaScript popup, as created by `window.alert`, `window.confirm`, or `window.prompt`.

* `checkOption($option)` : `void`


* `clearField($field)` : `void`  
  Clears given field which isn't empty.

  ``` php
  <?php
  $I->clearField('#username');
  ```


* `click($link, [$context])` : `void`


* `clickWithLeftButton([$cssOrXPath], [$offsetX], [$offsetY])` : `void`  
  Performs click with the left mouse button on an element.
  If the first parameter `null` then the offset is relative to the actual mouse position.
  If the second and third parameters are given,
  then the mouse is moved to an offset of the element's top-left corner.
  Otherwise, the mouse is moved to the center of the element.

  ``` php
  <?php
  $I->clickWithLeftButton(['css' => '.checkout']);
  $I->clickWithLeftButton(null, 20, 50);
  $I->clickWithLeftButton(['css' => '.checkout'], 20, 50);
  ?>
  ```


* `clickWithRightButton([$cssOrXPath], [$offsetX], [$offsetY])` : `void`  
  Performs contextual click with the right mouse button on an element.
  If the first parameter `null` then the offset is relative to the actual mouse position.
  If the second and third parameters are given,
  then the mouse is moved to an offset of the element's top-left corner.
  Otherwise, the mouse is moved to the center of the element.

  ``` php
  <?php
  $I->clickWithRightButton(['css' => '.checkout']);
  $I->clickWithRightButton(null, 20, 50);
  $I->clickWithRightButton(['css' => '.checkout'], 20, 50);
  ?>
  ```


* `closeTab()` : `void`  
  Closes current browser tab and switches to previous active tab.

  ```php
  <?php
  $I->closeTab();
  ```

* `deactivatePlugin($pluginSlug)` : `void`  
  In the plugin administration screen deactivate a plugin clicking the "Deactivate" link.

  The method will **not** handle authentication and navigation to the plugins administration page.


* `debugWebDriverLogs([?Codeception\TestInterface $test])` : `void`  
  Print out latest Selenium Logs in debug mode


* `deleteSessionSnapshot($name)` : `void`


* `dontSee($text, [$selector])` : `void`


* `dontSeeCheckboxIsChecked($checkbox)` : `void`


* `dontSeeCookie($cookie, [array $params])` : `void`


* `dontSeeCurrentUrlEquals($uri)` : `void`


* `dontSeeCurrentUrlMatches($uri)` : `void`


* `dontSeeElement($selector, [$attributes])` : `void`


* `dontSeeElementInDOM($selector, [$attributes])` : `void`  
  Opposite of `seeElementInDOM`.


* `dontSeeInCurrentUrl($uri)` : `void`


* `dontSeeInField($field, $value)` : `void`


* `dontSeeInFormFields($formSelector, array $params)` : `void`


* `dontSeeInPageSource($text)` : `void`  
  Checks that the page source doesn't contain the given string.


* `dontSeeInPopup($text)` : `void`  
  Checks that the active JavaScript popup,
  as created by `window.alert`|`window.confirm`|`window.prompt`, does NOT contain the given string.


* `dontSeeInSource($raw)` : `void`


* `dontSeeInTitle($title)` : `void`


* `dontSeeLink($text, [$url])` : `void`


* `dontSeeOptionIsSelected($selector, $optionText)` : `void`


* `dontSeePluginInstalled($pluginSlug)` : `void`  
  Assert a plugin is not installed in the plugins administration screen.

  The method will **not** handle authentication and navigation to the plugin administration screen.


* `doubleClick($cssOrXPath)` : `void`  
  Performs a double-click on an element matched by CSS or XPath.


* `dragAndDrop($source, $target)` : `void`  
  Performs a simple mouse drag-and-drop operation.

  ``` php
  <?php
  $I->dragAndDrop('#drag', '#drop');
  ?>
  ```


* `executeAsyncJS($script, [array $arguments])` : `void`  
  Executes asynchronous JavaScript.
  A callback should be executed by JavaScript to exit from a script.
  Callback is passed as a last element in `arguments` array.
  Additional arguments can be passed as array in second parameter.

  ```js
  // wait for 1200 milliseconds my running `setTimeout`
  * $I->executeAsyncJS('setTimeout(arguments[0], 1200)');
  
  $seconds = 1200; // or seconds are passed as argument
  $I->executeAsyncJS('setTimeout(arguments[1], arguments[0])', [$seconds]);
  ```


* `executeInSelenium(Closure $function)` : `void`  
  Low-level API method.
  If Codeception commands are not enough, this allows you to use Selenium WebDriver methods directly:

  ``` php
  $I->executeInSelenium(function(\Facebook\WebDriver\Remote\RemoteWebDriver $webdriver) {
    $webdriver->get('http://google.com');
  });
  ```

  This runs in the context of the
  [RemoteWebDriver class](https://github.com/php-webdriver/php-webdriver/blob/master/lib/remote/RemoteWebDriver.php).
  Try not to use this command on a regular basis.
  If Codeception lacks a feature you need, please implement it and submit a patch.


* `executeJS($script, [array $arguments])` : `void`  
  Executes custom JavaScript.

  This example uses jQuery to get a value and assigns that value to a PHP variable:

  ```php
  <?php
  $myVar = $I->executeJS('return $("#myField").val()');
  
  // additional arguments can be passed as array
  // Example shows `Hello World` alert:
  $I->executeJS("window.alert(arguments[0])", ['Hello world']);
  ```


* `fillField($field, $value)` : `void`


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


* `grabFullUrl()` : `void`  
  Grabs the current page full URL including the query vars.


* `grabMultiple($cssOrXpath, [$attribute])` : `void`


* `grabPageSource()` : `void`  
  Grabs current page source code.


* `grabTextFrom($cssOrXPathOrRegex)` : `void`


* `grabValueFrom($field)` : `void`


* `grabWordPressTestCookie([$name])` : `void`  
  Returns WordPress default test cookie object if present.

* `loadSessionSnapshot($name)` : `void`


* `logOut([$redirectTo])` : `void`  
  Navigate to the default WordPress logout page and click the logout link.


* `loginAs($username, $password, [$timeout], [$maxAttempts])` : `void`  
  Login as the specified user.

  The method will **not** follow redirection, after the login, to any page.
  Depending on the driven browser the login might be "too fast" and the server might have not
  replied with valid cookies yet; in that case the method will re-attempt the login to obtain
  the cookies.


* `loginAsAdmin([$timeout], [$maxAttempts])` : `void`  
  Login as the administrator user using the credentials specified in the module configuration.

  The method will **not** follow redirection, after the login, to any page.


* `makeElementScreenshot($selector, [$name])` : `void`  
  Takes a screenshot of an element of the current window and saves it to `tests/_output/debug`.

  ``` php
  <?php
  $I->amOnPage('/user/edit');
  $I->makeElementScreenshot('#dialog', 'edit_page');
  // saved to: tests/_output/debug/edit_page.png
  $I->makeElementScreenshot('#dialog');
  // saved to: tests/_output/debug/2017-05-26_14-24-11_4b3403665fea6.png
  ```


* `makeHtmlSnapshot([$name])` : `void`


* `makeScreenshot([$name])` : `void`  
  Takes a screenshot of the current window and saves it to `tests/_output/debug`.

  ``` php
  <?php
  $I->amOnPage('/user/edit');
  $I->makeScreenshot('edit_page');
  // saved to: tests/_output/debug/edit_page.png
  $I->makeScreenshot();
  // saved to: tests/_output/debug/2017-05-26_14-24-11_4b3403665fea6.png
  ```


* `maximizeWindow()` : `void`  
  Maximizes the current window.

* `moveBack()` : `void`  
  Moves back in history.

* `moveForward()` : `void`  
  Moves forward in history.

* `moveMouseOver([$cssOrXPath], [$offsetX], [$offsetY])` : `void`  
  Move mouse over the first element matched by the given locator.
  If the first parameter null then the page is used.
  If the second and third parameters are given,
  then the mouse is moved to an offset of the element's top-left corner.
  Otherwise, the mouse is moved to the center of the element.

  ``` php
  <?php
  $I->moveMouseOver(['css' => '.checkout']);
  $I->moveMouseOver(null, 20, 50);
  $I->moveMouseOver(['css' => '.checkout'], 20, 50);
  ?>
  ```


* `openNewTab()` : `void`  
  Opens a new browser tab and switches to it.

  ```php
  <?php
  $I->openNewTab();
  ```
  The tab is opened with JavaScript's `window.open()`, which means:
  * Some adblockers might restrict it.
  * The sessionStorage is copied to the new tab (contrary to a tab that was manually opened by the user)

* `performOn($element, $actions, [$timeout])` : `void`  
  Waits for element and runs a sequence of actions inside its context.
  Actions can be defined with array, callback, or `Codeception\Util\ActionSequence` instance.

  Actions as array are recommended for simple to combine "waitForElement" with assertions;
  `waitForElement($el)` and `see('text', $el)` can be simplified to:

  ```php
  <?php
  $I->performOn($el, ['see' => 'text']);
  ```

  List of actions can be pragmatically build using `Codeception\Util\ActionSequence`:

  ```php
  <?php
  $I->performOn('.model', ActionSequence::build()
      ->see('Warning')
      ->see('Are you sure you want to delete this?')
      ->click('Yes')
  );
  ```

  Actions executed from array or ActionSequence will print debug output for actions, and adds an action name to
  exception on failure.

  Whenever you need to define more actions a callback can be used. A WebDriver module is passed for argument:

  ```php
  <?php
  $I->performOn('.rememberMe', function (WebDriver $I) {
       $I->see('Remember me next time');
       $I->seeElement('#LoginForm_rememberMe');
       $I->dontSee('Login');
  });
  ```

  In 3rd argument you can set number a seconds to wait for element to appear


* `pressKey($element, $char)` : `void`  
  Presses the given key on the given element.
  To specify a character and modifier (e.g. <kbd>Ctrl</kbd>, Alt, Shift, Meta), pass an array for `$char` with
  the modifier as the first element and the character as the second.
  For special keys, use the constants from [`Facebook\WebDriver\WebDriverKeys`](https://github.com/php-webdriver/php-webdriver/blob/main/lib/WebDriverKeys.php).

  ``` php
  <?php
  // <input id="page" value="old" />
  $I->pressKey('#page','a'); // => olda
  $I->pressKey('#page',array('ctrl','a'),'new'); //=> new
  $I->pressKey('#page',array('shift','111'),'1','x'); //=> old!!!1x
  $I->pressKey('descendant-or-self::*[@id='page']','u'); //=> oldu
  $I->pressKey('#name', array('ctrl', 'a'), \Facebook\WebDriver\WebDriverKeys::DELETE); //=>''
  ?>
  ```


* `reloadPage()` : `void`  
  Reloads the current page.

* `resetCookie($cookie, [array $params])` : `void`


* `resizeWindow($width, $height)` : `void`  
  Resize the current window.

  ``` php
  <?php
  $I->resizeWindow(800, 600);
  
  ```


* `saveSessionSnapshot($name)` : `void`


* `scrollTo($selector, [$offsetX], [$offsetY])` : `void`  
  Move to the middle of the given element matched by the given locator.
  Extra shift, calculated from the top-left corner of the element,
  can be set by passing $offsetX and $offsetY parameters.

  ``` php
  <?php
  $I->scrollTo(['css' => '.checkout'], 20, 50);
  ?>
  ```


* `see($text, [$selector])` : `void`


* `seeCheckboxIsChecked($checkbox)` : `void`


* `seeCookie($cookie, [array $params])` : `void`


* `seeCurrentUrlEquals($uri)` : `void`


* `seeCurrentUrlMatches($uri)` : `void`


* `seeElement($selector, [$attributes])` : `void`


* `seeElementInDOM($selector, [$attributes])` : `void`  
  Checks that the given element exists on the page, even it is invisible.

  ``` php
  <?php
  $I->seeElementInDOM('//form/input[type=hidden]');
  ?>
  ```


* `seeErrorMessage([$classes])` : `void`  
  In an administration screen look for an error admin notice.

  The check is class-based to decouple from internationalization.
  The method will **not** handle authentication and navigation the administration area.


* `seeInCurrentUrl($uri)` : `void`


* `seeInField($field, $value)` : `void`


* `seeInFormFields($formSelector, array $params)` : `void`


* `seeInPageSource($text)` : `void`  
  Checks that the page source contains the given string.

  ```php
  <?php
  $I->seeInPageSource('<link rel="apple-touch-icon"');
  ```


* `seeInPopup($text)` : `void`  
  Checks that the active JavaScript popup,
  as created by `window.alert`|`window.confirm`|`window.prompt`, contains the given string.


* `seeInSource($raw)` : `void`


* `seeInTitle($title)` : `void`


* `seeLink($text, [$url])` : `void`


* `seeMessage([$classes])` : `void`  
  In an administration screen look for an admin notice.

  The check is class-based to decouple from internationalization.
  The method will **not** handle authentication and navigation the administration area.


* `seeNumberOfElements($selector, $expected)` : `void`


* `seeNumberOfElementsInDOM($selector, $expected)` : `void`


* `seeNumberOfTabs($number)` : `void`  
  Checks current number of opened tabs

  ```php
  <?php
  $I->seeNumberOfTabs(2);
  ```

* `seeOptionIsSelected($selector, $optionText)` : `void`


* `seePluginActivated($pluginSlug)` : `void`  
  Assert a plugin is activated in the plugin administration screen.

  The method will **not** handle authentication and navigation to the plugin administration screen.


* `seePluginDeactivated($pluginSlug)` : `void`  
  Assert a plugin is not activated in the plugins administration screen.

  The method will **not** handle authentication and navigation to the plugin administration screen.


* `seePluginInstalled($pluginSlug)` : `void`  
  Assert a plugin is installed, no matter its activation status, in the plugin adminstration screen.

  The method will **not** handle authentication and navigation to the plugin administration screen.


* `seeThemeActivated($slug)` : `void`  
  Verifies that a theme is active.

  The method will **not** handle authentication and navigation to the themes administration page.


* `seeWpDiePage()` : `void`  
  Checks that the current page is one generated by the `wp_die` function.

  The method will try to identify the page based on the default WordPress die page HTML attributes.


* `selectOption($select, $option)` : `void`


* `setCookie($cookie, $value, [array $params], [$showDebug])` : `void`


* `submitForm($selector, array $params, [$button])` : `void`  
  Submits the given form on the page, optionally with the given form
  values.  Give the form fields values as an array. Note that hidden fields
  can't be accessed.

  Skipped fields will be filled by their values from the page.
  You don't need to click the 'Submit' button afterwards.
  This command itself triggers the request to form's action.

  You can optionally specify what button's value to include
  in the request with the last parameter as an alternative to
  explicitly setting its value in the second parameter, as
  button values are not otherwise included in the request.

  Examples:

  ``` php
  <?php
  $I->submitForm('#login', [
      'login' => 'davert',
      'password' => '123456'
  ]);
  // or
  $I->submitForm('#login', [
      'login' => 'davert',
      'password' => '123456'
  ], 'submitButtonName');
  
  ```

  For example, given this sample "Sign Up" form:

  ``` html
  <form action="/sign_up">
      Login:
      <input type="text" name="user[login]" /><br/>
      Password:
      <input type="password" name="user[password]" /><br/>
      Do you agree to our terms?
      <input type="checkbox" name="user[agree]" /><br/>
      Select pricing plan:
      <select name="plan">
          <option value="1">Free</option>
          <option value="2" selected="selected">Paid</option>
      </select>
      <input type="submit" name="submitButton" value="Submit" />
  </form>
  ```

  You could write the following to submit it:

  ``` php
  <?php
  $I->submitForm(
      '#userForm',
      [
          'user[login]' => 'Davert',
          'user[password]' => '123456',
          'user[agree]' => true
      ],
      'submitButton'
  );
  ```
  Note that "2" will be the submitted value for the "plan" field, as it is
  the selected option.

  Also note that this differs from PhpBrowser, in that
  ```'user' => [ 'login' => 'Davert' ]``` is not supported at the moment.
  Named array keys *must* be included in the name as above.

  Pair this with seeInFormFields for quick testing magic.

  ``` php
  <?php
  $form = [
       'field1' => 'value',
       'field2' => 'another value',
       'checkbox1' => true,
       // ...
  ];
  $I->submitForm('//form[@id=my-form]', $form, 'submitButton');
  // $I->amOnPage('/path/to/form-page') may be needed
  $I->seeInFormFields('//form[@id=my-form]', $form);
  ?>
  ```

  Parameter values must be set to arrays for multiple input fields
  of the same name, or multi-select combo boxes.  For checkboxes,
  either the string value can be used, or boolean values which will
  be replaced by the checkbox's value in the DOM.

  ``` php
  <?php
  $I->submitForm('#my-form', [
       'field1' => 'value',
       'checkbox' => [
           'value of first checkbox',
           'value of second checkbox',
       ],
       'otherCheckboxes' => [
           true,
           false,
           false,
       ],
       'multiselect' => [
           'first option value',
           'second option value',
       ]
  ]);
  ?>
  ```

  Mixing string and boolean values for a checkbox's value is not supported
  and may produce unexpected results.

  Field names ending in "[]" must be passed without the trailing square
  bracket characters, and must contain an array for its value.  This allows
  submitting multiple values with the same name, consider:

  ```php
  $I->submitForm('#my-form', [
      'field[]' => 'value',
      'field[]' => 'another value', // 'field[]' is already a defined key
  ]);
  ```

  The solution is to pass an array value:

  ```php
  // this way both values are submitted
  $I->submitForm('#my-form', [
      'field' => [
          'value',
          'another value',
      ]
  ]);
  ```

  The `$button` parameter can be either a string, an array or an instance
  of Facebook\WebDriver\WebDriverBy. When it is a string, the
  button will be found by its "name" attribute. If $button is an
  array then it will be treated as a strict selector and a WebDriverBy
  will be used verbatim.

  For example, given the following HTML:

  ``` html
  <input type="submit" name="submitButton" value="Submit" />
  ```

  `$button` could be any one of the following:
  - 'submitButton'
  - ['name' => 'submitButton']
  - WebDriverBy::name('submitButton')


* `switchToFrame([$locator])` : `void`  
  Switch to another frame on the page.

  Example:
  ``` html
  <frame name="another_frame" id="fr1" src="http://example.com">
  
  ```

  ``` php
  <?php
  # switch to frame by name
  $I->switchToFrame("another_frame");
  # switch to frame by CSS or XPath
  $I->switchToFrame("#fr1");
  # switch to parent page
  $I->switchToFrame();
  
  ```


* `switchToIFrame([$locator])` : `void`  
  Switch to another iframe on the page.

  Example:
  ``` html
  <iframe name="another_frame" id="fr1" src="http://example.com">
  
  ```

  ``` php
  <?php
  # switch to iframe by name
  $I->switchToIFrame("another_frame");
  # switch to iframe by CSS or XPath
  $I->switchToIFrame("#fr1");
  # switch to parent page
  $I->switchToIFrame();
  
  ```


* `switchToNextTab([$offset])` : `void`  
  Switches to next browser tab.
  An offset can be specified.

  ```php
  <?php
  // switch to next tab
  $I->switchToNextTab();
  // switch to 2nd next tab
  $I->switchToNextTab(2);
  ```

* `switchToPreviousTab([$offset])` : `void`  
  Switches to previous browser tab.
  An offset can be specified.

  ```php
  <?php
  // switch to previous tab
  $I->switchToPreviousTab();
  // switch to 2nd previous tab
  $I->switchToPreviousTab(2);
  ```

* `switchToWindow([$name])` : `void`  
  Switch to another window identified by name.

  The window can only be identified by name. If the $name parameter is blank, the parent window will be used.

  Example:
  ``` html
  <input type="button" value="Open window" onclick="window.open('http://example.com', 'another_window')">
  ```

  ``` php
  <?php
  $I->click("Open window");
  # switch to another window
  $I->switchToWindow("another_window");
  # switch to parent window
  $I->switchToWindow();
  ?>
  ```

  If the window has no name, match it by switching to next active tab using `switchToNextTab` method.

  Or use native Selenium functions to get access to all opened windows:

  ``` php
  <?php
  $I->executeInSelenium(function (\Facebook\WebDriver\Remote\RemoteWebDriver $webdriver) {
       $handles=$webdriver->getWindowHandles();
       $last_window = end($handles);
       $webdriver->switchTo()->window($last_window);
  });
  ?>
  ```


* `type($text, [$delay])` : `void`  
  Type in characters on active element.
  With a second parameter you can specify delay between key presses.

  ```php
  <?php
  // activate input element
  $I->click('#input');
  
  // type text in active element
  $I->type('Hello world');
  
  // type text with a 1sec delay between chars
  $I->type('Hello World', 1);
  ```

  This might be useful when you an input reacts to typing and you need to slow it down to emulate human behavior.
  For instance, this is how Credit Card fields can be filled in.


* `typeInPopup($keys)` : `void`  
  Enters text into a native JavaScript prompt popup, as created by `window.prompt`.


* `uncheckOption($option)` : `void`


* `unselectOption($select, $option)` : `void`  
  Unselect an option in the given select box.


* `wait($timeout)` : `void`  
  Wait for $timeout seconds.


* `waitForElement($element, [$timeout])` : `void`  
  Waits up to $timeout seconds for an element to appear on the page.
  If the element doesn't appear, a timeout exception is thrown.

  ``` php
  <?php
  $I->waitForElement('#agree_button', 30); // secs
  $I->click('#agree_button');
  ?>
  ```


* `waitForElementChange($element, Closure $callback, [$timeout])` : `void`  
  Waits up to $timeout seconds for the given element to change.
  Element "change" is determined by a callback function which is called repeatedly
  until the return value evaluates to true.

  ``` php
  <?php
  use \Facebook\WebDriver\WebDriverElement
  $I->waitForElementChange('#menu', function(WebDriverElement $el) {
      return $el->isDisplayed();
  }, 100);
  ?>
  ```


* `waitForElementClickable($element, [$timeout])` : `void`  
  Waits up to $timeout seconds for the given element to be clickable.
  If element doesn't become clickable, a timeout exception is thrown.

  ``` php
  <?php
  $I->waitForElementClickable('#agree_button', 30); // secs
  $I->click('#agree_button');
  ?>
  ```


* `waitForElementNotVisible($element, [$timeout])` : `void`  
  Waits up to $timeout seconds for the given element to become invisible.
  If element stays visible, a timeout exception is thrown.

  ``` php
  <?php
  $I->waitForElementNotVisible('#agree_button', 30); // secs
  ?>
  ```


* `waitForElementVisible($element, [$timeout])` : `void`  
  Waits up to $timeout seconds for the given element to be visible on the page.
  If element doesn't appear, a timeout exception is thrown.

  ``` php
  <?php
  $I->waitForElementVisible('#agree_button', 30); // secs
  $I->click('#agree_button');
  ?>
  ```


* `waitForJS($script, [$timeout])` : `void`  
  Executes JavaScript and waits up to $timeout seconds for it to return true.

  In this example we will wait up to 60 seconds for all jQuery AJAX requests to finish.

  ``` php
  <?php
  $I->waitForJS("return $.active == 0;", 60);
  ?>
  ```


* `waitForJqueryAjax([$time])` : `void`  
  Waits for any jQuery triggered AJAX request to be resolved.


* `waitForText($text, [$timeout], [$selector])` : `void`  
  Waits up to $timeout seconds for the given string to appear on the page.

  Can also be passed a selector to search in, be as specific as possible when using selectors.
  waitForText() will only watch the first instance of the matching selector / text provided.
  If the given text doesn't appear, a timeout exception is thrown.

  ``` php
  <?php
  $I->waitForText('foo', 30); // secs
  $I->waitForText('foo', 30, '.title'); // secs
  ?>
  ```



*This class extends \Codeception\Module\WebDriver*

*This class implements \Codeception\Lib\Interfaces\RequiresPackage, \Codeception\Lib\Interfaces\ConflictsWithModule, \Codeception\Lib\Interfaces\ElementLocator, \Codeception\Lib\Interfaces\PageSourceSaver, \Codeception\Lib\Interfaces\ScreenshotSaver, \Codeception\Lib\Interfaces\SessionSnapshot, \Codeception\Lib\Interfaces\MultiSession, \Codeception\Lib\Interfaces\Remote, \Codeception\Lib\Interfaces\Web*

<!--/doc-->

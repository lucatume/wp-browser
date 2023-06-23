---
title: Codeception 4.0 support
---
odeception [version `4.0` introduced a number of new features to the framework][1] and it's the version of wp-browser that will be maintained from now on.  

I've made an effort to keep `wp-browser` compatible with PHP 5.6 and Codeception versions from `2.5` up.  

One the biggest changes of Codeception version `4.0` is that modules have been broken out into separate packages.  
To use `wp-browser` with Codeception `4.0` all you need to do is to add this to your project `composer.json` file:

```json
{
  "require-dev": {
    "lucatume/wp-browser": "^2.4",
    "codeception/module-asserts": "^1.0",
    "codeception/module-phpbrowser": "^1.0",
    "codeception/module-webdriver": "^1.0",
    "codeception/module-db": "^1.0",
    "codeception/module-filesystem": "^1.0",
    "codeception/module-cli": "^1.0",
    "codeception/util-universalframework": "^1.0"
  }
}
```

You *might* not need all the modules listed here, depending on the wp-browser modules you use in your test suites.  
This is a scheme of what Codeception modules you will need for which wp-browser module to help you choose only the required modules:

* "codeception/module-asserts" -  Required for Codeception 4.0 compatibility.
* "codeception/module-phpbrowser" -  Required by the `WPBrowser` module.
* "codeception/module-webdriver" - Required by the `WPWebDriver` module.
* "codeception/module-db" - Required by the `WPDb` module.
* "codeception/module-filesystem" - Required by the `WPFilesystem` module.
* "codeception/module-cli" - Required by the `WPCLI` module.
* "codeception/util-universalframework" - Required by the `WordPress` framework module.

[1]: https://codeception.com/12-18-2019/codeception-4.html

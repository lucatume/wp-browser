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
        chromeOptions:
          args: ["--headless", "--disable-gpu", "--proxy-server='direct://'", "--proxy-bypass-list=*"]
```

<!--doc-->

Deprecated: Function ReflectionParameter::export() is deprecated in /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php on line 344

Call Stack:
    0.0142     404600   1. {main}() /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:0
    0.0599    2236352   2. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:12
    0.0684    2842184   3. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/CLI.php:29
    0.0867    3094184   4. PHPDocsMD\Console\CLI->doRun(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:166
    0.0887    3107856   5. PHPDocsMD\Console\CLI->doRunCommand(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:290
    0.0887    3107856   6. PHPDocsMD\Console\PHPDocsMDCommand->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:971
    0.0898    3110488   7. PHPDocsMD\Console\PHPDocsMDCommand->execute(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Command/Command.php:255
    0.1040    4394400   8. PHPDocsMD\Console\PHPDocsMDCommand->getClassEntity(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:169
    0.1071    4503520   9. PHPDocsMD\Reflector->getClassEntity() /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:57
    0.1085    4524792  10. PHPDocsMD\Reflector->getClassFunctions(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:83
    0.1099    4547608  11. PHPDocsMD\Reflector->createFunctionEntity(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:118
    0.1112    4560976  12. PHPDocsMD\Reflector->getParams(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:161
    0.1113    4562008  13. PHPDocsMD\Reflector->createParameterEntity(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:438
    0.1113    4562008  14. PHPDocsMD\Reflector::getParamType(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:252


Deprecated: Function ReflectionParameter::export() is deprecated in /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php on line 344

Call Stack:
    0.0142     404600   1. {main}() /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:0
    0.0599    2236352   2. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:12
    0.0684    2842184   3. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/CLI.php:29
    0.0867    3094184   4. PHPDocsMD\Console\CLI->doRun(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:166
    0.0887    3107856   5. PHPDocsMD\Console\CLI->doRunCommand(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:290
    0.0887    3107856   6. PHPDocsMD\Console\PHPDocsMDCommand->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:971
    0.0898    3110488   7. PHPDocsMD\Console\PHPDocsMDCommand->execute(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Command/Command.php:255
    0.1040    4394400   8. PHPDocsMD\Console\PHPDocsMDCommand->getClassEntity(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:169
    0.1071    4503520   9. PHPDocsMD\Reflector->getClassEntity() /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:57
    0.1085    4524792  10. PHPDocsMD\Reflector->getClassFunctions(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:83
    0.1099    4547608  11. PHPDocsMD\Reflector->createFunctionEntity(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:118
    0.1112    4560976  12. PHPDocsMD\Reflector->getParams(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:161
    0.1127    4567736  13. PHPDocsMD\Reflector->createParameterEntity(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:438
    0.1127    4567736  14. PHPDocsMD\Reflector::getParamType(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:252


Deprecated: Function ReflectionParameter::export() is deprecated in /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php on line 344

Call Stack:
    0.0142     404600   1. {main}() /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:0
    0.0599    2236352   2. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:12
    0.0684    2842184   3. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/CLI.php:29
    0.0867    3094184   4. PHPDocsMD\Console\CLI->doRun(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:166
    0.0887    3107856   5. PHPDocsMD\Console\CLI->doRunCommand(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:290
    0.0887    3107856   6. PHPDocsMD\Console\PHPDocsMDCommand->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:971
    0.0898    3110488   7. PHPDocsMD\Console\PHPDocsMDCommand->execute(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Command/Command.php:255
    0.1040    4394400   8. PHPDocsMD\Console\PHPDocsMDCommand->getClassEntity(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:169
    0.1071    4503520   9. PHPDocsMD\Reflector->getClassEntity() /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:57
    0.1085    4524792  10. PHPDocsMD\Reflector->getClassFunctions(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:83
    0.1133    4564928  11. PHPDocsMD\Reflector->createFunctionEntity(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:118
    0.1144    4569544  12. PHPDocsMD\Reflector->getParams(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:161
    0.1144    4570816  13. PHPDocsMD\Reflector->createParameterEntity(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:438
    0.1144    4570816  14. PHPDocsMD\Reflector::getParamType(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:252


Deprecated: Function ReflectionParameter::export() is deprecated in /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php on line 344

Call Stack:
    0.0142     404600   1. {main}() /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:0
    0.0599    2236352   2. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:12
    0.0684    2842184   3. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/CLI.php:29
    0.0867    3094184   4. PHPDocsMD\Console\CLI->doRun(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:166
    0.0887    3107856   5. PHPDocsMD\Console\CLI->doRunCommand(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:290
    0.0887    3107856   6. PHPDocsMD\Console\PHPDocsMDCommand->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:971
    0.0898    3110488   7. PHPDocsMD\Console\PHPDocsMDCommand->execute(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Command/Command.php:255
    0.1040    4394400   8. PHPDocsMD\Console\PHPDocsMDCommand->getClassEntity(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:169
    0.1071    4503520   9. PHPDocsMD\Reflector->getClassEntity() /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:57
    0.1085    4524792  10. PHPDocsMD\Reflector->getClassFunctions(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:83
    0.1133    4564928  11. PHPDocsMD\Reflector->createFunctionEntity(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:118
    0.1144    4569544  12. PHPDocsMD\Reflector->getParams(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:161
    0.1150    4571888  13. PHPDocsMD\Reflector->createParameterEntity(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:438
    0.1150    4571888  14. PHPDocsMD\Reflector::getParamType(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:252


Deprecated: Function ReflectionParameter::export() is deprecated in /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php on line 344

Call Stack:
    0.0142     404600   1. {main}() /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:0
    0.0599    2236352   2. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:12
    0.0684    2842184   3. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/CLI.php:29
    0.0867    3094184   4. PHPDocsMD\Console\CLI->doRun(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:166
    0.0887    3107856   5. PHPDocsMD\Console\CLI->doRunCommand(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:290
    0.0887    3107856   6. PHPDocsMD\Console\PHPDocsMDCommand->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:971
    0.0898    3110488   7. PHPDocsMD\Console\PHPDocsMDCommand->execute(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Command/Command.php:255
    0.1040    4394400   8. PHPDocsMD\Console\PHPDocsMDCommand->getClassEntity(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:169
    0.1071    4503520   9. PHPDocsMD\Reflector->getClassEntity() /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:57
    0.1085    4524792  10. PHPDocsMD\Reflector->getClassFunctions(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:83
    0.1133    4564928  11. PHPDocsMD\Reflector->createFunctionEntity(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:118
    0.1144    4569544  12. PHPDocsMD\Reflector->getParams(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:161
    0.1156    4572584  13. PHPDocsMD\Reflector->createParameterEntity(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:438
    0.1157    4572584  14. PHPDocsMD\Reflector::getParamType(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:252


Deprecated: Function ReflectionParameter::export() is deprecated in /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php on line 344

Call Stack:
    0.0142     404600   1. {main}() /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:0
    0.0599    2236352   2. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:12
    0.0684    2842184   3. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/CLI.php:29
    0.0867    3094184   4. PHPDocsMD\Console\CLI->doRun(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:166
    0.0887    3107856   5. PHPDocsMD\Console\CLI->doRunCommand(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:290
    0.0887    3107856   6. PHPDocsMD\Console\PHPDocsMDCommand->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:971
    0.0898    3110488   7. PHPDocsMD\Console\PHPDocsMDCommand->execute(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Command/Command.php:255
    0.1040    4394400   8. PHPDocsMD\Console\PHPDocsMDCommand->getClassEntity(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:169
    0.1071    4503520   9. PHPDocsMD\Reflector->getClassEntity() /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:57
    0.1085    4524792  10. PHPDocsMD\Reflector->getClassFunctions(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:83
    0.1133    4564928  11. PHPDocsMD\Reflector->createFunctionEntity(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:118
    0.1144    4569544  12. PHPDocsMD\Reflector->getParams(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:161
    0.1161    4573192  13. PHPDocsMD\Reflector->createParameterEntity(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:438
    0.1162    4573192  14. PHPDocsMD\Reflector::getParamType(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:252


Deprecated: Function ReflectionParameter::export() is deprecated in /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php on line 344

Call Stack:
    0.0142     404600   1. {main}() /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:0
    0.0599    2236352   2. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:12
    0.0684    2842184   3. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/CLI.php:29
    0.0867    3094184   4. PHPDocsMD\Console\CLI->doRun(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:166
    0.0887    3107856   5. PHPDocsMD\Console\CLI->doRunCommand(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:290
    0.0887    3107856   6. PHPDocsMD\Console\PHPDocsMDCommand->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:971
    0.0898    3110488   7. PHPDocsMD\Console\PHPDocsMDCommand->execute(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Command/Command.php:255
    0.1040    4394400   8. PHPDocsMD\Console\PHPDocsMDCommand->getClassEntity(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:169
    0.1071    4503520   9. PHPDocsMD\Reflector->getClassEntity() /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:57
    0.1085    4524792  10. PHPDocsMD\Reflector->getClassFunctions(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:83
    0.1168    4568088  11. PHPDocsMD\Reflector->createFunctionEntity(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:118
    0.1175    4571000  12. PHPDocsMD\Reflector->getParams(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:161
    0.1175    4571912  13. PHPDocsMD\Reflector->createParameterEntity(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:438
    0.1175    4571912  14. PHPDocsMD\Reflector::getParamType(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:252


Deprecated: Function ReflectionParameter::export() is deprecated in /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php on line 344

Call Stack:
    0.0142     404600   1. {main}() /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:0
    0.0599    2236352   2. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:12
    0.0684    2842184   3. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/CLI.php:29
    0.0867    3094184   4. PHPDocsMD\Console\CLI->doRun(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:166
    0.0887    3107856   5. PHPDocsMD\Console\CLI->doRunCommand(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:290
    0.0887    3107856   6. PHPDocsMD\Console\PHPDocsMDCommand->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:971
    0.0898    3110488   7. PHPDocsMD\Console\PHPDocsMDCommand->execute(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Command/Command.php:255
    0.1040    4394400   8. PHPDocsMD\Console\PHPDocsMDCommand->getClassEntity(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:169
    0.1071    4503520   9. PHPDocsMD\Reflector->getClassEntity() /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:57
    0.1085    4524792  10. PHPDocsMD\Reflector->getClassFunctions(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:83
    0.1183    4570048  11. PHPDocsMD\Reflector->createFunctionEntity(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:118
    0.1189    4572696  12. PHPDocsMD\Reflector->getParams(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:161
    0.1189    4573600  13. PHPDocsMD\Reflector->createParameterEntity(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:438
    0.1189    4573600  14. PHPDocsMD\Reflector::getParamType(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:252


Deprecated: Function ReflectionParameter::export() is deprecated in /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php on line 344

Call Stack:
    0.0142     404600   1. {main}() /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:0
    0.0599    2236352   2. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:12
    0.0684    2842184   3. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/CLI.php:29
    0.0867    3094184   4. PHPDocsMD\Console\CLI->doRun(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:166
    0.0887    3107856   5. PHPDocsMD\Console\CLI->doRunCommand(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:290
    0.0887    3107856   6. PHPDocsMD\Console\PHPDocsMDCommand->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:971
    0.0898    3110488   7. PHPDocsMD\Console\PHPDocsMDCommand->execute(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Command/Command.php:255
    0.1040    4394400   8. PHPDocsMD\Console\PHPDocsMDCommand->getClassEntity(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:169
    0.1071    4503520   9. PHPDocsMD\Reflector->getClassEntity() /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:57
    0.1085    4524792  10. PHPDocsMD\Reflector->getClassFunctions(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:83
    0.1201    4572888  11. PHPDocsMD\Reflector->createFunctionEntity(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:118
    0.1209    4575880  12. PHPDocsMD\Reflector->getParams(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:161
    0.1209    4576792  13. PHPDocsMD\Reflector->createParameterEntity(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:438
    0.1209    4576792  14. PHPDocsMD\Reflector::getParamType(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:252


Deprecated: Function ReflectionParameter::export() is deprecated in /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php on line 344

Call Stack:
    0.0142     404600   1. {main}() /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:0
    0.0599    2236352   2. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:12
    0.0684    2842184   3. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/CLI.php:29
    0.0867    3094184   4. PHPDocsMD\Console\CLI->doRun(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:166
    0.0887    3107856   5. PHPDocsMD\Console\CLI->doRunCommand(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:290
    0.0887    3107856   6. PHPDocsMD\Console\PHPDocsMDCommand->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:971
    0.0898    3110488   7. PHPDocsMD\Console\PHPDocsMDCommand->execute(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Command/Command.php:255
    0.1040    4394400   8. PHPDocsMD\Console\PHPDocsMDCommand->getClassEntity(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:169
    0.1071    4503520   9. PHPDocsMD\Reflector->getClassEntity() /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:57
    0.1085    4524792  10. PHPDocsMD\Reflector->getClassFunctions(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:83
    0.1217    4575032  11. PHPDocsMD\Reflector->createFunctionEntity(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:118
    0.1225    4578024  12. PHPDocsMD\Reflector->getParams(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:161
    0.1225    4578936  13. PHPDocsMD\Reflector->createParameterEntity(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:438
    0.1225    4578936  14. PHPDocsMD\Reflector::getParamType(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:252


Deprecated: Function ReflectionParameter::export() is deprecated in /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php on line 344

Call Stack:
    0.0142     404600   1. {main}() /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:0
    0.0599    2236352   2. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:12
    0.0684    2842184   3. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/CLI.php:29
    0.0867    3094184   4. PHPDocsMD\Console\CLI->doRun(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:166
    0.0887    3107856   5. PHPDocsMD\Console\CLI->doRunCommand(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:290
    0.0887    3107856   6. PHPDocsMD\Console\PHPDocsMDCommand->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:971
    0.0898    3110488   7. PHPDocsMD\Console\PHPDocsMDCommand->execute(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Command/Command.php:255
    0.1040    4394400   8. PHPDocsMD\Console\PHPDocsMDCommand->getClassEntity(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:169
    0.1071    4503520   9. PHPDocsMD\Reflector->getClassEntity() /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:57
    0.1085    4524792  10. PHPDocsMD\Reflector->getClassFunctions(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:83
    0.1632    4613184  11. PHPDocsMD\Reflector->createFunctionEntity(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:118
    0.1639    4616112  12. PHPDocsMD\Reflector->getParams(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:161
    0.1639    4617024  13. PHPDocsMD\Reflector->createParameterEntity(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:438
    0.1639    4617024  14. PHPDocsMD\Reflector::getParamType(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:252


Deprecated: Function ReflectionParameter::export() is deprecated in /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php on line 344

Call Stack:
    0.0142     404600   1. {main}() /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:0
    0.0599    2236352   2. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:12
    0.0684    2842184   3. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/CLI.php:29
    0.0867    3094184   4. PHPDocsMD\Console\CLI->doRun(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:166
    0.0887    3107856   5. PHPDocsMD\Console\CLI->doRunCommand(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:290
    0.0887    3107856   6. PHPDocsMD\Console\PHPDocsMDCommand->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:971
    0.0898    3110488   7. PHPDocsMD\Console\PHPDocsMDCommand->execute(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Command/Command.php:255
    0.1040    4394400   8. PHPDocsMD\Console\PHPDocsMDCommand->getClassEntity(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:169
    0.1071    4503520   9. PHPDocsMD\Reflector->getClassEntity() /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:57
    0.1085    4524792  10. PHPDocsMD\Reflector->getClassFunctions(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:83
    0.1659    4617536  11. PHPDocsMD\Reflector->createFunctionEntity(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:118
    0.1665    4620304  12. PHPDocsMD\Reflector->getParams(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:161
    0.1666    4621216  13. PHPDocsMD\Reflector->createParameterEntity(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:438
    0.1666    4621216  14. PHPDocsMD\Reflector::getParamType(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:252


Deprecated: Function ReflectionParameter::export() is deprecated in /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php on line 344

Call Stack:
    0.0142     404600   1. {main}() /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:0
    0.0599    2236352   2. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:12
    0.0684    2842184   3. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/CLI.php:29
    0.0867    3094184   4. PHPDocsMD\Console\CLI->doRun(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:166
    0.0887    3107856   5. PHPDocsMD\Console\CLI->doRunCommand(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:290
    0.0887    3107856   6. PHPDocsMD\Console\PHPDocsMDCommand->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:971
    0.0898    3110488   7. PHPDocsMD\Console\PHPDocsMDCommand->execute(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Command/Command.php:255
    0.1040    4394400   8. PHPDocsMD\Console\PHPDocsMDCommand->getClassEntity(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:169
    0.1071    4503520   9. PHPDocsMD\Reflector->getClassEntity() /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:57
    0.1085    4524792  10. PHPDocsMD\Reflector->getClassFunctions(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:83
    0.1674    4619368  11. PHPDocsMD\Reflector->createFunctionEntity(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:118
    0.1680    4622136  12. PHPDocsMD\Reflector->getParams(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:161
    0.1680    4623048  13. PHPDocsMD\Reflector->createParameterEntity(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:438
    0.1681    4623048  14. PHPDocsMD\Reflector::getParamType(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:252


Deprecated: Function ReflectionParameter::export() is deprecated in /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php on line 344

Call Stack:
    0.0142     404600   1. {main}() /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:0
    0.0599    2236352   2. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:12
    0.0684    2842184   3. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/CLI.php:29
    0.0867    3094184   4. PHPDocsMD\Console\CLI->doRun(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:166
    0.0887    3107856   5. PHPDocsMD\Console\CLI->doRunCommand(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:290
    0.0887    3107856   6. PHPDocsMD\Console\PHPDocsMDCommand->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:971
    0.0898    3110488   7. PHPDocsMD\Console\PHPDocsMDCommand->execute(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Command/Command.php:255
    0.1040    4394400   8. PHPDocsMD\Console\PHPDocsMDCommand->getClassEntity(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:169
    0.1071    4503520   9. PHPDocsMD\Reflector->getClassEntity() /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:57
    0.1085    4524792  10. PHPDocsMD\Reflector->getClassFunctions(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:83
    0.1687    4621200  11. PHPDocsMD\Reflector->createFunctionEntity(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:118
    0.1693    4623936  12. PHPDocsMD\Reflector->getParams(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:161
    0.1693    4624848  13. PHPDocsMD\Reflector->createParameterEntity(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:438
    0.1694    4624848  14. PHPDocsMD\Reflector::getParamType(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:252


Deprecated: Function ReflectionParameter::export() is deprecated in /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php on line 344

Call Stack:
    0.0142     404600   1. {main}() /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:0
    0.0599    2236352   2. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:12
    0.0684    2842184   3. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/CLI.php:29
    0.0867    3094184   4. PHPDocsMD\Console\CLI->doRun(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:166
    0.0887    3107856   5. PHPDocsMD\Console\CLI->doRunCommand(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:290
    0.0887    3107856   6. PHPDocsMD\Console\PHPDocsMDCommand->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:971
    0.0898    3110488   7. PHPDocsMD\Console\PHPDocsMDCommand->execute(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Command/Command.php:255
    0.1040    4394400   8. PHPDocsMD\Console\PHPDocsMDCommand->getClassEntity(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:169
    0.1071    4503520   9. PHPDocsMD\Reflector->getClassEntity() /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:57
    0.1085    4524792  10. PHPDocsMD\Reflector->getClassFunctions(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:83
    0.1700    4623000  11. PHPDocsMD\Reflector->createFunctionEntity(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:118
    0.1707    4625768  12. PHPDocsMD\Reflector->getParams(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:161
    0.1707    4626680  13. PHPDocsMD\Reflector->createParameterEntity(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:438
    0.1707    4626680  14. PHPDocsMD\Reflector::getParamType(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:252


Deprecated: Function ReflectionParameter::export() is deprecated in /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php on line 344

Call Stack:
    0.0142     404600   1. {main}() /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:0
    0.0599    2236352   2. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:12
    0.0684    2842184   3. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/CLI.php:29
    0.0867    3094184   4. PHPDocsMD\Console\CLI->doRun(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:166
    0.0887    3107856   5. PHPDocsMD\Console\CLI->doRunCommand(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:290
    0.0887    3107856   6. PHPDocsMD\Console\PHPDocsMDCommand->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:971
    0.0898    3110488   7. PHPDocsMD\Console\PHPDocsMDCommand->execute(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Command/Command.php:255
    0.1040    4394400   8. PHPDocsMD\Console\PHPDocsMDCommand->getClassEntity(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:169
    0.1071    4503520   9. PHPDocsMD\Reflector->getClassEntity() /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:57
    0.1085    4524792  10. PHPDocsMD\Reflector->getClassFunctions(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:83
    0.1713    4624832  11. PHPDocsMD\Reflector->createFunctionEntity(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:118
    0.1721    4627760  12. PHPDocsMD\Reflector->getParams(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:161
    0.1721    4628672  13. PHPDocsMD\Reflector->createParameterEntity(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:438
    0.1721    4628672  14. PHPDocsMD\Reflector::getParamType(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:252


Deprecated: Function ReflectionParameter::export() is deprecated in /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php on line 344

Call Stack:
    0.0142     404600   1. {main}() /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:0
    0.0599    2236352   2. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:12
    0.0684    2842184   3. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/CLI.php:29
    0.0867    3094184   4. PHPDocsMD\Console\CLI->doRun(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:166
    0.0887    3107856   5. PHPDocsMD\Console\CLI->doRunCommand(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:290
    0.0887    3107856   6. PHPDocsMD\Console\PHPDocsMDCommand->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:971
    0.0898    3110488   7. PHPDocsMD\Console\PHPDocsMDCommand->execute(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Command/Command.php:255
    0.1040    4394400   8. PHPDocsMD\Console\PHPDocsMDCommand->getClassEntity(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:169
    0.1071    4503520   9. PHPDocsMD\Reflector->getClassEntity() /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:57
    0.1085    4524792  10. PHPDocsMD\Reflector->getClassFunctions(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:83
    0.1734    4628696  11. PHPDocsMD\Reflector->createFunctionEntity(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:118
    0.1741    4631576  12. PHPDocsMD\Reflector->getParams(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:161
    0.1742    4632488  13. PHPDocsMD\Reflector->createParameterEntity(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:438
    0.1742    4632488  14. PHPDocsMD\Reflector::getParamType(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:252


Deprecated: Function ReflectionParameter::export() is deprecated in /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php on line 344

Call Stack:
    0.0142     404600   1. {main}() /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:0
    0.0599    2236352   2. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:12
    0.0684    2842184   3. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/CLI.php:29
    0.0867    3094184   4. PHPDocsMD\Console\CLI->doRun(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:166
    0.0887    3107856   5. PHPDocsMD\Console\CLI->doRunCommand(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:290
    0.0887    3107856   6. PHPDocsMD\Console\PHPDocsMDCommand->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:971
    0.0898    3110488   7. PHPDocsMD\Console\PHPDocsMDCommand->execute(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Command/Command.php:255
    0.1040    4394400   8. PHPDocsMD\Console\PHPDocsMDCommand->getClassEntity(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:169
    0.1071    4503520   9. PHPDocsMD\Reflector->getClassEntity() /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:57
    0.1085    4524792  10. PHPDocsMD\Reflector->getClassFunctions(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:83
    0.1749    4631312  11. PHPDocsMD\Reflector->createFunctionEntity(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:118
    0.1756    4634296  12. PHPDocsMD\Reflector->getParams(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:161
    0.1756    4635200  13. PHPDocsMD\Reflector->createParameterEntity(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:438
    0.1756    4635200  14. PHPDocsMD\Reflector::getParamType(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:252


Deprecated: Function ReflectionParameter::export() is deprecated in /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php on line 344

Call Stack:
    0.0142     404600   1. {main}() /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:0
    0.0599    2236352   2. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:12
    0.0684    2842184   3. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/CLI.php:29
    0.0867    3094184   4. PHPDocsMD\Console\CLI->doRun(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:166
    0.0887    3107856   5. PHPDocsMD\Console\CLI->doRunCommand(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:290
    0.0887    3107856   6. PHPDocsMD\Console\PHPDocsMDCommand->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:971
    0.0898    3110488   7. PHPDocsMD\Console\PHPDocsMDCommand->execute(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Command/Command.php:255
    0.1040    4394400   8. PHPDocsMD\Console\PHPDocsMDCommand->getClassEntity(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:169
    0.1071    4503520   9. PHPDocsMD\Reflector->getClassEntity() /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:57
    0.1085    4524792  10. PHPDocsMD\Reflector->getClassFunctions(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:83
    0.1763    4633336  11. PHPDocsMD\Reflector->createFunctionEntity(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:118
    0.1783    4636072  12. PHPDocsMD\Reflector->getParams(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:161
    0.1783    4636976  13. PHPDocsMD\Reflector->createParameterEntity(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:438
    0.1784    4636976  14. PHPDocsMD\Reflector::getParamType(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:252


Deprecated: Function ReflectionParameter::export() is deprecated in /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php on line 344

Call Stack:
    0.0142     404600   1. {main}() /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:0
    0.0599    2236352   2. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:12
    0.0684    2842184   3. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/CLI.php:29
    0.0867    3094184   4. PHPDocsMD\Console\CLI->doRun(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:166
    0.0887    3107856   5. PHPDocsMD\Console\CLI->doRunCommand(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:290
    0.0887    3107856   6. PHPDocsMD\Console\PHPDocsMDCommand->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:971
    0.0898    3110488   7. PHPDocsMD\Console\PHPDocsMDCommand->execute(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Command/Command.php:255
    0.1040    4394400   8. PHPDocsMD\Console\PHPDocsMDCommand->getClassEntity(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:169
    0.1071    4503520   9. PHPDocsMD\Reflector->getClassEntity() /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:57
    0.1085    4524792  10. PHPDocsMD\Reflector->getClassFunctions(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:83
    0.1791    4635224  11. PHPDocsMD\Reflector->createFunctionEntity(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:118
    0.1797    4638048  12. PHPDocsMD\Reflector->getParams(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:161
    0.1798    4638960  13. PHPDocsMD\Reflector->createParameterEntity(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:438
    0.1798    4638960  14. PHPDocsMD\Reflector::getParamType(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:252


Deprecated: Function ReflectionParameter::export() is deprecated in /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php on line 344

Call Stack:
    0.0142     404600   1. {main}() /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:0
    0.0599    2236352   2. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:12
    0.0684    2842184   3. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/CLI.php:29
    0.0867    3094184   4. PHPDocsMD\Console\CLI->doRun(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:166
    0.0887    3107856   5. PHPDocsMD\Console\CLI->doRunCommand(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:290
    0.0887    3107856   6. PHPDocsMD\Console\PHPDocsMDCommand->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:971
    0.0898    3110488   7. PHPDocsMD\Console\PHPDocsMDCommand->execute(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Command/Command.php:255
    0.1040    4394400   8. PHPDocsMD\Console\PHPDocsMDCommand->getClassEntity(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:169
    0.1071    4503520   9. PHPDocsMD\Reflector->getClassEntity() /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:57
    0.1085    4524792  10. PHPDocsMD\Reflector->getClassFunctions(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:83
    0.1805    4637200  11. PHPDocsMD\Reflector->createFunctionEntity(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:118
    0.1811    4639960  12. PHPDocsMD\Reflector->getParams(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:161
    0.1811    4640872  13. PHPDocsMD\Reflector->createParameterEntity(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:438
    0.1812    4640872  14. PHPDocsMD\Reflector::getParamType(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:252


Deprecated: Function ReflectionParameter::export() is deprecated in /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php on line 344

Call Stack:
    0.0142     404600   1. {main}() /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:0
    0.0599    2236352   2. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:12
    0.0684    2842184   3. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/CLI.php:29
    0.0867    3094184   4. PHPDocsMD\Console\CLI->doRun(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:166
    0.0887    3107856   5. PHPDocsMD\Console\CLI->doRunCommand(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:290
    0.0887    3107856   6. PHPDocsMD\Console\PHPDocsMDCommand->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:971
    0.0898    3110488   7. PHPDocsMD\Console\PHPDocsMDCommand->execute(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Command/Command.php:255
    0.1040    4394400   8. PHPDocsMD\Console\PHPDocsMDCommand->getClassEntity(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:169
    0.1071    4503520   9. PHPDocsMD\Reflector->getClassEntity() /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:57
    0.1085    4524792  10. PHPDocsMD\Reflector->getClassFunctions(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:83
    0.1819    4639024  11. PHPDocsMD\Reflector->createFunctionEntity(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:118
    0.1826    4641720  12. PHPDocsMD\Reflector->getParams(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:161
    0.1826    4642624  13. PHPDocsMD\Reflector->createParameterEntity(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:438
    0.1826    4642624  14. PHPDocsMD\Reflector::getParamType(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:252



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
		<li>
			<a href="#waitforjqueryajax">waitForJqueryAjax</a>
		</li>
	</ul>
</nav>

<h3>activatePlugin</h3>

<hr>

<p>In the plugin administration screen activates one or more plugins clicking the &quot;Activate&quot; link. The method will <strong>not</strong> handle authentication and navigation to the plugins administration page.</p>
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
  

<h3>grabFullUrl</h3>

<hr>

<p>Grabs the current page full URL including the query vars.</p>
```php
$today = date('Y-m-d');
  $I->amOnPage('/concerts?date=' . $today);
  $I->assertRegExp('#\\/concerts$#', $I->grabFullUrl());
```

  

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

<p>Login as the specified user. The method will <strong>not</strong> follow redirection, after the login, to any page. Depending on the driven browser the login might be &quot;too fast&quot; and the server might have not replied with valid cookies yet; in that case the method will re-attempt the login to obtain the cookies.</p>
```php
$I->loginAs('user', 'password');
  $I->amOnAdminPage('/');
  $I->see('Dashboard');
```

<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$username</strong> - The user login name.</li>
<li><code>string</code> <strong>$password</strong> - The user password in plain text.</li>
<li><code>int</code> <strong>$timeout</strong> - The max time, in seconds, to try to login.</li>
<li><code>int</code> <strong>$maxAttempts</strong> - The max number of attempts to try to login.</li></ul>
  

<h3>loginAsAdmin</h3>

<hr>

<p>Login as the administrator user using the credentials specified in the module configuration. The method will <strong>not</strong> follow redirection, after the login, to any page.</p>
```php
$I->loginAsAdmin();
  $I->amOnAdminPage('/');
  $I->see('Dashboard');
```

<h4>Parameters</h4>
<ul>
<li><code>int</code> <strong>$timeout</strong> - The max time, in seconds, to try to login.</li>
<li><code>int</code> <strong>$maxAttempts</strong> - The max number of attempts to try to login.</li></ul>
  

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

<p>Assert a plugin is installed, no matter its activation status, in the plugin adminstration screen. The method will <strong>not</strong> handle authentication and navigation to the plugin administration screen.</p>
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

  

<h3>waitForJqueryAjax</h3>

<hr>

<p>Waits for any jQuery triggered AJAX request to be resolved.</p>
```php
$I->amOnPage('/triggering-ajax-requests');
  $I->waitForJqueryAjax();
  $I->see('From AJAX');
```

<h4>Parameters</h4>
<ul>
<li><code>int</code> <strong>$time</strong> - The max time to wait for AJAX requests to complete.</li></ul>


*This class extends \Codeception\Module\WebDriver*

*This class implements \Codeception\Lib\Interfaces\RequiresPackage, \Codeception\Lib\Interfaces\ConflictsWithModule, \Codeception\Lib\Interfaces\ElementLocator, \Codeception\Lib\Interfaces\PageSourceSaver, \Codeception\Lib\Interfaces\ScreenshotSaver, \Codeception\Lib\Interfaces\SessionSnapshot, \Codeception\Lib\Interfaces\MultiSession, \Codeception\Lib\Interfaces\Remote, \Codeception\Lib\Interfaces\Web*

<!--/doc-->

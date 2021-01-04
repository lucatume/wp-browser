# WPFilesystem module
This module should be used in acceptance and functional tests, see [levels of testing for more information](./../levels-of-testing.md).  
This module extends the [Filesystem module](https://codeception.com/docs/modules/Filesystem) adding WordPress-specific configuration parameters and methods.  
The module provides methods to read, write and update the WordPress filesystem **directly**, without relying on WordPress methods, using WordPress functions or triggering WordPress filters.  
This module also provides methods to scaffold plugins and themes on the fly in the context of tests and auto-remove them after each test.

## Module requirements for Codeception 4.0+

This module requires the `codeception/module-filesystem` Composer package to work when wp-browser is used with Codeception 4.0.  

To install the package run: 

```bash
composer require --dev codeception/module-filesystem:^1.0
```

## Configuration

* `wpRootFolder` *required* The absolute, or relative to the project root folder, path to the root WordPress installation folder. The WordPress installation root folder is the one that contains the `wp-load.php` file.
* `themes` - defaults to `/wp-content/themes`; the path, relative to the the WordPress installaion root folder, to the themes folder.
* `plugins` - defaults to `/wp-content/plugins`; the path, relative to the WordPress installation root folder, to the plugins folder.
* `mu-plugins` - defaults to `wp-content/mu-plugins`; the path, relative to the WordPress installation root folder, to the must-use plugins folder.
* `uploads` - defaults to `/wp-content/uploads`; the path, relative to the WordPress installation root folder, to the uploads folder.

### Example configuration
```yaml
modules:
    enabled:
        - WPFilesystem
    config:
        WPFilesystem:
            wpRootFolder: "/var/www/wordpress"
```
<!--doc-->

Deprecated: Function ReflectionParameter::export() is deprecated in /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php on line 344

Call Stack:
    0.0114     404600   1. {main}() /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:0
    0.0551    2236352   2. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:12
    0.0631    2842184   3. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/CLI.php:29
    0.0813    3094184   4. PHPDocsMD\Console\CLI->doRun(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:166
    0.0833    3107856   5. PHPDocsMD\Console\CLI->doRunCommand(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:290
    0.0833    3107856   6. PHPDocsMD\Console\PHPDocsMDCommand->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:971
    0.0845    3110488   7. PHPDocsMD\Console\PHPDocsMDCommand->execute(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Command/Command.php:255
    0.0928    3932096   8. PHPDocsMD\Console\PHPDocsMDCommand->getClassEntity(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:169
    0.0958    4049448   9. PHPDocsMD\Reflector->getClassEntity() /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:57
    0.0970    4069352  10. PHPDocsMD\Reflector->getClassFunctions(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:83
    0.0980    4080192  11. PHPDocsMD\Reflector->createFunctionEntity(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:118
    0.0992    4092944  12. PHPDocsMD\Reflector->getParams(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:161
    0.0992    4093848  13. PHPDocsMD\Reflector->createParameterEntity(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:438
    0.0993    4093848  14. PHPDocsMD\Reflector::getParamType(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:252


Deprecated: Function ReflectionParameter::export() is deprecated in /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php on line 344

Call Stack:
    0.0114     404600   1. {main}() /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:0
    0.0551    2236352   2. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:12
    0.0631    2842184   3. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/CLI.php:29
    0.0813    3094184   4. PHPDocsMD\Console\CLI->doRun(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:166
    0.0833    3107856   5. PHPDocsMD\Console\CLI->doRunCommand(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:290
    0.0833    3107856   6. PHPDocsMD\Console\PHPDocsMDCommand->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:971
    0.0845    3110488   7. PHPDocsMD\Console\PHPDocsMDCommand->execute(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Command/Command.php:255
    0.0928    3932096   8. PHPDocsMD\Console\PHPDocsMDCommand->getClassEntity(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:169
    0.0958    4049448   9. PHPDocsMD\Reflector->getClassEntity() /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:57
    0.0970    4069352  10. PHPDocsMD\Reflector->getClassFunctions(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:83
    0.1008    4096968  11. PHPDocsMD\Reflector->createFunctionEntity(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:118
    0.1016    4100320  12. PHPDocsMD\Reflector->getParams(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:161
    0.1017    4101352  13. PHPDocsMD\Reflector->createParameterEntity(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:438
    0.1017    4101352  14. PHPDocsMD\Reflector::getParamType(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:252


Deprecated: Function ReflectionParameter::export() is deprecated in /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php on line 344

Call Stack:
    0.0114     404600   1. {main}() /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:0
    0.0551    2236352   2. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:12
    0.0631    2842184   3. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/CLI.php:29
    0.0813    3094184   4. PHPDocsMD\Console\CLI->doRun(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:166
    0.0833    3107856   5. PHPDocsMD\Console\CLI->doRunCommand(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:290
    0.0833    3107856   6. PHPDocsMD\Console\PHPDocsMDCommand->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:971
    0.0845    3110488   7. PHPDocsMD\Console\PHPDocsMDCommand->execute(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Command/Command.php:255
    0.0928    3932096   8. PHPDocsMD\Console\PHPDocsMDCommand->getClassEntity(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:169
    0.0958    4049448   9. PHPDocsMD\Reflector->getClassEntity() /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:57
    0.0970    4069352  10. PHPDocsMD\Reflector->getClassFunctions(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:83
    0.1008    4096968  11. PHPDocsMD\Reflector->createFunctionEntity(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:118
    0.1016    4100320  12. PHPDocsMD\Reflector->getParams(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:161
    0.1023    4102416  13. PHPDocsMD\Reflector->createParameterEntity(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:438
    0.1023    4102416  14. PHPDocsMD\Reflector::getParamType(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:252


Deprecated: Function ReflectionParameter::export() is deprecated in /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php on line 344

Call Stack:
    0.0114     404600   1. {main}() /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:0
    0.0551    2236352   2. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:12
    0.0631    2842184   3. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/CLI.php:29
    0.0813    3094184   4. PHPDocsMD\Console\CLI->doRun(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:166
    0.0833    3107856   5. PHPDocsMD\Console\CLI->doRunCommand(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:290
    0.0833    3107856   6. PHPDocsMD\Console\PHPDocsMDCommand->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:971
    0.0845    3110488   7. PHPDocsMD\Console\PHPDocsMDCommand->execute(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Command/Command.php:255
    0.0928    3932096   8. PHPDocsMD\Console\PHPDocsMDCommand->getClassEntity(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:169
    0.0958    4049448   9. PHPDocsMD\Reflector->getClassEntity() /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:57
    0.0970    4069352  10. PHPDocsMD\Reflector->getClassFunctions(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:83
    0.1030    4099376  11. PHPDocsMD\Reflector->createFunctionEntity(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:118
    0.1038    4102704  12. PHPDocsMD\Reflector->getParams(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:161
    0.1038    4103728  13. PHPDocsMD\Reflector->createParameterEntity(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:438
    0.1038    4103728  14. PHPDocsMD\Reflector::getParamType(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:252


Deprecated: Function ReflectionParameter::export() is deprecated in /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php on line 344

Call Stack:
    0.0114     404600   1. {main}() /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:0
    0.0551    2236352   2. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:12
    0.0631    2842184   3. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/CLI.php:29
    0.0813    3094184   4. PHPDocsMD\Console\CLI->doRun(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:166
    0.0833    3107856   5. PHPDocsMD\Console\CLI->doRunCommand(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:290
    0.0833    3107856   6. PHPDocsMD\Console\PHPDocsMDCommand->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:971
    0.0845    3110488   7. PHPDocsMD\Console\PHPDocsMDCommand->execute(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Command/Command.php:255
    0.0928    3932096   8. PHPDocsMD\Console\PHPDocsMDCommand->getClassEntity(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:169
    0.0958    4049448   9. PHPDocsMD\Reflector->getClassEntity() /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:57
    0.0970    4069352  10. PHPDocsMD\Reflector->getClassFunctions(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:83
    0.1030    4099376  11. PHPDocsMD\Reflector->createFunctionEntity(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:118
    0.1038    4102704  12. PHPDocsMD\Reflector->getParams(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:161
    0.1044    4104824  13. PHPDocsMD\Reflector->createParameterEntity(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:438
    0.1044    4104824  14. PHPDocsMD\Reflector::getParamType(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:252


Deprecated: Function ReflectionParameter::export() is deprecated in /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php on line 344

Call Stack:
    0.0114     404600   1. {main}() /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:0
    0.0551    2236352   2. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:12
    0.0631    2842184   3. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/CLI.php:29
    0.0813    3094184   4. PHPDocsMD\Console\CLI->doRun(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:166
    0.0833    3107856   5. PHPDocsMD\Console\CLI->doRunCommand(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:290
    0.0833    3107856   6. PHPDocsMD\Console\PHPDocsMDCommand->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:971
    0.0845    3110488   7. PHPDocsMD\Console\PHPDocsMDCommand->execute(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Command/Command.php:255
    0.0928    3932096   8. PHPDocsMD\Console\PHPDocsMDCommand->getClassEntity(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:169
    0.0958    4049448   9. PHPDocsMD\Reflector->getClassEntity() /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:57
    0.0970    4069352  10. PHPDocsMD\Reflector->getClassFunctions(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:83
    0.1051    4101704  11. PHPDocsMD\Reflector->createFunctionEntity(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:118
    0.1059    4105080  12. PHPDocsMD\Reflector->getParams(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:161
    0.1059    4106104  13. PHPDocsMD\Reflector->createParameterEntity(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:438
    0.1059    4106104  14. PHPDocsMD\Reflector::getParamType(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:252


Deprecated: Function ReflectionParameter::export() is deprecated in /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php on line 344

Call Stack:
    0.0114     404600   1. {main}() /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:0
    0.0551    2236352   2. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:12
    0.0631    2842184   3. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/CLI.php:29
    0.0813    3094184   4. PHPDocsMD\Console\CLI->doRun(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:166
    0.0833    3107856   5. PHPDocsMD\Console\CLI->doRunCommand(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:290
    0.0833    3107856   6. PHPDocsMD\Console\PHPDocsMDCommand->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:971
    0.0845    3110488   7. PHPDocsMD\Console\PHPDocsMDCommand->execute(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Command/Command.php:255
    0.0928    3932096   8. PHPDocsMD\Console\PHPDocsMDCommand->getClassEntity(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:169
    0.0958    4049448   9. PHPDocsMD\Reflector->getClassEntity() /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:57
    0.0970    4069352  10. PHPDocsMD\Reflector->getClassFunctions(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:83
    0.1051    4101704  11. PHPDocsMD\Reflector->createFunctionEntity(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:118
    0.1059    4105080  12. PHPDocsMD\Reflector->getParams(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:161
    0.1065    4107080  13. PHPDocsMD\Reflector->createParameterEntity(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:438
    0.1065    4107080  14. PHPDocsMD\Reflector::getParamType(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:252


Deprecated: Function ReflectionParameter::export() is deprecated in /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php on line 344

Call Stack:
    0.0114     404600   1. {main}() /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:0
    0.0551    2236352   2. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:12
    0.0631    2842184   3. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/CLI.php:29
    0.0813    3094184   4. PHPDocsMD\Console\CLI->doRun(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:166
    0.0833    3107856   5. PHPDocsMD\Console\CLI->doRunCommand(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:290
    0.0833    3107856   6. PHPDocsMD\Console\PHPDocsMDCommand->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:971
    0.0845    3110488   7. PHPDocsMD\Console\PHPDocsMDCommand->execute(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Command/Command.php:255
    0.0928    3932096   8. PHPDocsMD\Console\PHPDocsMDCommand->getClassEntity(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:169
    0.0958    4049448   9. PHPDocsMD\Reflector->getClassEntity() /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:57
    0.0970    4069352  10. PHPDocsMD\Reflector->getClassFunctions(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:83
    0.1071    4103960  11. PHPDocsMD\Reflector->createFunctionEntity(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:118
    0.1080    4107864  12. PHPDocsMD\Reflector->getParams(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:161
    0.1080    4109008  13. PHPDocsMD\Reflector->createParameterEntity(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:438
    0.1081    4109008  14. PHPDocsMD\Reflector::getParamType(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:252


Deprecated: Function ReflectionParameter::export() is deprecated in /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php on line 344

Call Stack:
    0.0114     404600   1. {main}() /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:0
    0.0551    2236352   2. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:12
    0.0631    2842184   3. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/CLI.php:29
    0.0813    3094184   4. PHPDocsMD\Console\CLI->doRun(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:166
    0.0833    3107856   5. PHPDocsMD\Console\CLI->doRunCommand(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:290
    0.0833    3107856   6. PHPDocsMD\Console\PHPDocsMDCommand->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:971
    0.0845    3110488   7. PHPDocsMD\Console\PHPDocsMDCommand->execute(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Command/Command.php:255
    0.0928    3932096   8. PHPDocsMD\Console\PHPDocsMDCommand->getClassEntity(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:169
    0.0958    4049448   9. PHPDocsMD\Reflector->getClassEntity() /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:57
    0.0970    4069352  10. PHPDocsMD\Reflector->getClassFunctions(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:83
    0.1071    4103960  11. PHPDocsMD\Reflector->createFunctionEntity(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:118
    0.1080    4107864  12. PHPDocsMD\Reflector->getParams(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:161
    0.1086    4109992  13. PHPDocsMD\Reflector->createParameterEntity(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:438
    0.1086    4109992  14. PHPDocsMD\Reflector::getParamType(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:252


Deprecated: Function ReflectionParameter::export() is deprecated in /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php on line 344

Call Stack:
    0.0114     404600   1. {main}() /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:0
    0.0551    2236352   2. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:12
    0.0631    2842184   3. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/CLI.php:29
    0.0813    3094184   4. PHPDocsMD\Console\CLI->doRun(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:166
    0.0833    3107856   5. PHPDocsMD\Console\CLI->doRunCommand(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:290
    0.0833    3107856   6. PHPDocsMD\Console\PHPDocsMDCommand->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:971
    0.0845    3110488   7. PHPDocsMD\Console\PHPDocsMDCommand->execute(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Command/Command.php:255
    0.0928    3932096   8. PHPDocsMD\Console\PHPDocsMDCommand->getClassEntity(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:169
    0.0958    4049448   9. PHPDocsMD\Reflector->getClassEntity() /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:57
    0.0970    4069352  10. PHPDocsMD\Reflector->getClassFunctions(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:83
    0.1071    4103960  11. PHPDocsMD\Reflector->createFunctionEntity(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:118
    0.1080    4107864  12. PHPDocsMD\Reflector->getParams(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:161
    0.1092    4110680  13. PHPDocsMD\Reflector->createParameterEntity(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:438
    0.1092    4110680  14. PHPDocsMD\Reflector::getParamType(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:252


Deprecated: Function ReflectionParameter::export() is deprecated in /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php on line 344

Call Stack:
    0.0114     404600   1. {main}() /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:0
    0.0551    2236352   2. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:12
    0.0631    2842184   3. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/CLI.php:29
    0.0813    3094184   4. PHPDocsMD\Console\CLI->doRun(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:166
    0.0833    3107856   5. PHPDocsMD\Console\CLI->doRunCommand(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:290
    0.0833    3107856   6. PHPDocsMD\Console\PHPDocsMDCommand->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:971
    0.0845    3110488   7. PHPDocsMD\Console\PHPDocsMDCommand->execute(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Command/Command.php:255
    0.0928    3932096   8. PHPDocsMD\Console\PHPDocsMDCommand->getClassEntity(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:169
    0.0958    4049448   9. PHPDocsMD\Reflector->getClassEntity() /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:57
    0.0970    4069352  10. PHPDocsMD\Reflector->getClassFunctions(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:83
    0.1098    4106648  11. PHPDocsMD\Reflector->createFunctionEntity(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:118
    0.1107    4110552  12. PHPDocsMD\Reflector->getParams(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:161
    0.1107    4111696  13. PHPDocsMD\Reflector->createParameterEntity(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:438
    0.1108    4111696  14. PHPDocsMD\Reflector::getParamType(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:252


Deprecated: Function ReflectionParameter::export() is deprecated in /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php on line 344

Call Stack:
    0.0114     404600   1. {main}() /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:0
    0.0551    2236352   2. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:12
    0.0631    2842184   3. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/CLI.php:29
    0.0813    3094184   4. PHPDocsMD\Console\CLI->doRun(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:166
    0.0833    3107856   5. PHPDocsMD\Console\CLI->doRunCommand(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:290
    0.0833    3107856   6. PHPDocsMD\Console\PHPDocsMDCommand->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:971
    0.0845    3110488   7. PHPDocsMD\Console\PHPDocsMDCommand->execute(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Command/Command.php:255
    0.0928    3932096   8. PHPDocsMD\Console\PHPDocsMDCommand->getClassEntity(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:169
    0.0958    4049448   9. PHPDocsMD\Reflector->getClassEntity() /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:57
    0.0970    4069352  10. PHPDocsMD\Reflector->getClassFunctions(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:83
    0.1098    4106648  11. PHPDocsMD\Reflector->createFunctionEntity(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:118
    0.1107    4110552  12. PHPDocsMD\Reflector->getParams(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:161
    0.1113    4112680  13. PHPDocsMD\Reflector->createParameterEntity(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:438
    0.1114    4112680  14. PHPDocsMD\Reflector::getParamType(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:252


Deprecated: Function ReflectionParameter::export() is deprecated in /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php on line 344

Call Stack:
    0.0114     404600   1. {main}() /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:0
    0.0551    2236352   2. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:12
    0.0631    2842184   3. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/CLI.php:29
    0.0813    3094184   4. PHPDocsMD\Console\CLI->doRun(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:166
    0.0833    3107856   5. PHPDocsMD\Console\CLI->doRunCommand(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:290
    0.0833    3107856   6. PHPDocsMD\Console\PHPDocsMDCommand->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:971
    0.0845    3110488   7. PHPDocsMD\Console\PHPDocsMDCommand->execute(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Command/Command.php:255
    0.0928    3932096   8. PHPDocsMD\Console\PHPDocsMDCommand->getClassEntity(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:169
    0.0958    4049448   9. PHPDocsMD\Reflector->getClassEntity() /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:57
    0.0970    4069352  10. PHPDocsMD\Reflector->getClassFunctions(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:83
    0.1098    4106648  11. PHPDocsMD\Reflector->createFunctionEntity(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:118
    0.1107    4110552  12. PHPDocsMD\Reflector->getParams(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:161
    0.1119    4113280  13. PHPDocsMD\Reflector->createParameterEntity(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:438
    0.1119    4113280  14. PHPDocsMD\Reflector::getParamType(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:252


Deprecated: Function ReflectionParameter::export() is deprecated in /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php on line 344

Call Stack:
    0.0114     404600   1. {main}() /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:0
    0.0551    2236352   2. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:12
    0.0631    2842184   3. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/CLI.php:29
    0.0813    3094184   4. PHPDocsMD\Console\CLI->doRun(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:166
    0.0833    3107856   5. PHPDocsMD\Console\CLI->doRunCommand(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:290
    0.0833    3107856   6. PHPDocsMD\Console\PHPDocsMDCommand->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:971
    0.0845    3110488   7. PHPDocsMD\Console\PHPDocsMDCommand->execute(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Command/Command.php:255
    0.0928    3932096   8. PHPDocsMD\Console\PHPDocsMDCommand->getClassEntity(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:169
    0.0958    4049448   9. PHPDocsMD\Reflector->getClassEntity() /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:57
    0.0970    4069352  10. PHPDocsMD\Reflector->getClassFunctions(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:83
    0.1125    4109248  11. PHPDocsMD\Reflector->createFunctionEntity(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:118
    0.1133    4112568  12. PHPDocsMD\Reflector->getParams(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:161
    0.1133    4113592  13. PHPDocsMD\Reflector->createParameterEntity(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:438
    0.1134    4113592  14. PHPDocsMD\Reflector::getParamType(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:252


Deprecated: Function ReflectionParameter::export() is deprecated in /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php on line 344

Call Stack:
    0.0114     404600   1. {main}() /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:0
    0.0551    2236352   2. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:12
    0.0631    2842184   3. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/CLI.php:29
    0.0813    3094184   4. PHPDocsMD\Console\CLI->doRun(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:166
    0.0833    3107856   5. PHPDocsMD\Console\CLI->doRunCommand(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:290
    0.0833    3107856   6. PHPDocsMD\Console\PHPDocsMDCommand->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:971
    0.0845    3110488   7. PHPDocsMD\Console\PHPDocsMDCommand->execute(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Command/Command.php:255
    0.0928    3932096   8. PHPDocsMD\Console\PHPDocsMDCommand->getClassEntity(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:169
    0.0958    4049448   9. PHPDocsMD\Reflector->getClassEntity() /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:57
    0.0970    4069352  10. PHPDocsMD\Reflector->getClassFunctions(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:83
    0.1125    4109248  11. PHPDocsMD\Reflector->createFunctionEntity(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:118
    0.1133    4112568  12. PHPDocsMD\Reflector->getParams(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:161
    0.1140    4114656  13. PHPDocsMD\Reflector->createParameterEntity(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:438
    0.1140    4114656  14. PHPDocsMD\Reflector::getParamType(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:252


Deprecated: Function ReflectionParameter::export() is deprecated in /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php on line 344

Call Stack:
    0.0114     404600   1. {main}() /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:0
    0.0551    2236352   2. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:12
    0.0631    2842184   3. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/CLI.php:29
    0.0813    3094184   4. PHPDocsMD\Console\CLI->doRun(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:166
    0.0833    3107856   5. PHPDocsMD\Console\CLI->doRunCommand(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:290
    0.0833    3107856   6. PHPDocsMD\Console\PHPDocsMDCommand->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:971
    0.0845    3110488   7. PHPDocsMD\Console\PHPDocsMDCommand->execute(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Command/Command.php:255
    0.0928    3932096   8. PHPDocsMD\Console\PHPDocsMDCommand->getClassEntity(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:169
    0.0958    4049448   9. PHPDocsMD\Reflector->getClassEntity() /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:57
    0.0970    4069352  10. PHPDocsMD\Reflector->getClassFunctions(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:83
    0.1146    4111536  11. PHPDocsMD\Reflector->createFunctionEntity(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:118
    0.1155    4114880  12. PHPDocsMD\Reflector->getParams(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:161
    0.1155    4115904  13. PHPDocsMD\Reflector->createParameterEntity(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:438
    0.1155    4115904  14. PHPDocsMD\Reflector::getParamType(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:252


Deprecated: Function ReflectionParameter::export() is deprecated in /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php on line 344

Call Stack:
    0.0114     404600   1. {main}() /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:0
    0.0551    2236352   2. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:12
    0.0631    2842184   3. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/CLI.php:29
    0.0813    3094184   4. PHPDocsMD\Console\CLI->doRun(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:166
    0.0833    3107856   5. PHPDocsMD\Console\CLI->doRunCommand(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:290
    0.0833    3107856   6. PHPDocsMD\Console\PHPDocsMDCommand->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:971
    0.0845    3110488   7. PHPDocsMD\Console\PHPDocsMDCommand->execute(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Command/Command.php:255
    0.0928    3932096   8. PHPDocsMD\Console\PHPDocsMDCommand->getClassEntity(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:169
    0.0958    4049448   9. PHPDocsMD\Reflector->getClassEntity() /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:57
    0.0970    4069352  10. PHPDocsMD\Reflector->getClassFunctions(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:83
    0.1146    4111536  11. PHPDocsMD\Reflector->createFunctionEntity(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:118
    0.1155    4114880  12. PHPDocsMD\Reflector->getParams(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:161
    0.1161    4116880  13. PHPDocsMD\Reflector->createParameterEntity(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:438
    0.1161    4116880  14. PHPDocsMD\Reflector::getParamType(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:252


Deprecated: Function ReflectionParameter::export() is deprecated in /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php on line 344

Call Stack:
    0.0114     404600   1. {main}() /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:0
    0.0551    2236352   2. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:12
    0.0631    2842184   3. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/CLI.php:29
    0.0813    3094184   4. PHPDocsMD\Console\CLI->doRun(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:166
    0.0833    3107856   5. PHPDocsMD\Console\CLI->doRunCommand(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:290
    0.0833    3107856   6. PHPDocsMD\Console\PHPDocsMDCommand->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:971
    0.0845    3110488   7. PHPDocsMD\Console\PHPDocsMDCommand->execute(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Command/Command.php:255
    0.0928    3932096   8. PHPDocsMD\Console\PHPDocsMDCommand->getClassEntity(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:169
    0.0958    4049448   9. PHPDocsMD\Reflector->getClassEntity() /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:57
    0.0970    4069352  10. PHPDocsMD\Reflector->getClassFunctions(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:83
    0.1167    4113760  11. PHPDocsMD\Reflector->createFunctionEntity(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:118
    0.1185    4117112  12. PHPDocsMD\Reflector->getParams(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:161
    0.1185    4118136  13. PHPDocsMD\Reflector->createParameterEntity(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:438
    0.1186    4118136  14. PHPDocsMD\Reflector::getParamType(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:252


Deprecated: Function ReflectionParameter::export() is deprecated in /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php on line 344

Call Stack:
    0.0114     404600   1. {main}() /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:0
    0.0551    2236352   2. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:12
    0.0631    2842184   3. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/CLI.php:29
    0.0813    3094184   4. PHPDocsMD\Console\CLI->doRun(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:166
    0.0833    3107856   5. PHPDocsMD\Console\CLI->doRunCommand(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:290
    0.0833    3107856   6. PHPDocsMD\Console\PHPDocsMDCommand->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:971
    0.0845    3110488   7. PHPDocsMD\Console\PHPDocsMDCommand->execute(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Command/Command.php:255
    0.0928    3932096   8. PHPDocsMD\Console\PHPDocsMDCommand->getClassEntity(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:169
    0.0958    4049448   9. PHPDocsMD\Reflector->getClassEntity() /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:57
    0.0970    4069352  10. PHPDocsMD\Reflector->getClassFunctions(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:83
    0.1167    4113760  11. PHPDocsMD\Reflector->createFunctionEntity(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:118
    0.1185    4117112  12. PHPDocsMD\Reflector->getParams(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:161
    0.1191    4119112  13. PHPDocsMD\Reflector->createParameterEntity(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:438
    0.1191    4119112  14. PHPDocsMD\Reflector::getParamType(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:252


Deprecated: Function ReflectionParameter::export() is deprecated in /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php on line 344

Call Stack:
    0.0114     404600   1. {main}() /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:0
    0.0551    2236352   2. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:12
    0.0631    2842184   3. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/CLI.php:29
    0.0813    3094184   4. PHPDocsMD\Console\CLI->doRun(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:166
    0.0833    3107856   5. PHPDocsMD\Console\CLI->doRunCommand(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:290
    0.0833    3107856   6. PHPDocsMD\Console\PHPDocsMDCommand->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:971
    0.0845    3110488   7. PHPDocsMD\Console\PHPDocsMDCommand->execute(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Command/Command.php:255
    0.0928    3932096   8. PHPDocsMD\Console\PHPDocsMDCommand->getClassEntity(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:169
    0.0958    4049448   9. PHPDocsMD\Reflector->getClassEntity() /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:57
    0.0970    4069352  10. PHPDocsMD\Reflector->getClassFunctions(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:83
    0.1198    4116312  11. PHPDocsMD\Reflector->createFunctionEntity(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:118
    0.1207    4120248  12. PHPDocsMD\Reflector->getParams(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:161
    0.1207    4121392  13. PHPDocsMD\Reflector->createParameterEntity(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:438
    0.1208    4121392  14. PHPDocsMD\Reflector::getParamType(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:252


Deprecated: Function ReflectionParameter::export() is deprecated in /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php on line 344

Call Stack:
    0.0114     404600   1. {main}() /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:0
    0.0551    2236352   2. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:12
    0.0631    2842184   3. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/CLI.php:29
    0.0813    3094184   4. PHPDocsMD\Console\CLI->doRun(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:166
    0.0833    3107856   5. PHPDocsMD\Console\CLI->doRunCommand(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:290
    0.0833    3107856   6. PHPDocsMD\Console\PHPDocsMDCommand->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:971
    0.0845    3110488   7. PHPDocsMD\Console\PHPDocsMDCommand->execute(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Command/Command.php:255
    0.0928    3932096   8. PHPDocsMD\Console\PHPDocsMDCommand->getClassEntity(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:169
    0.0958    4049448   9. PHPDocsMD\Reflector->getClassEntity() /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:57
    0.0970    4069352  10. PHPDocsMD\Reflector->getClassFunctions(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:83
    0.1198    4116312  11. PHPDocsMD\Reflector->createFunctionEntity(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:118
    0.1207    4120248  12. PHPDocsMD\Reflector->getParams(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:161
    0.1214    4122456  13. PHPDocsMD\Reflector->createParameterEntity(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:438
    0.1214    4122456  14. PHPDocsMD\Reflector::getParamType(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:252


Deprecated: Function ReflectionParameter::export() is deprecated in /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php on line 344

Call Stack:
    0.0114     404600   1. {main}() /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:0
    0.0551    2236352   2. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:12
    0.0631    2842184   3. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/CLI.php:29
    0.0813    3094184   4. PHPDocsMD\Console\CLI->doRun(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:166
    0.0833    3107856   5. PHPDocsMD\Console\CLI->doRunCommand(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:290
    0.0833    3107856   6. PHPDocsMD\Console\PHPDocsMDCommand->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:971
    0.0845    3110488   7. PHPDocsMD\Console\PHPDocsMDCommand->execute(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Command/Command.php:255
    0.0928    3932096   8. PHPDocsMD\Console\PHPDocsMDCommand->getClassEntity(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:169
    0.0958    4049448   9. PHPDocsMD\Reflector->getClassEntity() /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:57
    0.0970    4069352  10. PHPDocsMD\Reflector->getClassFunctions(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:83
    0.1198    4116312  11. PHPDocsMD\Reflector->createFunctionEntity(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:118
    0.1207    4120248  12. PHPDocsMD\Reflector->getParams(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:161
    0.1219    4123144  13. PHPDocsMD\Reflector->createParameterEntity(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:438
    0.1220    4123144  14. PHPDocsMD\Reflector::getParamType(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:252


Deprecated: Function ReflectionParameter::export() is deprecated in /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php on line 344

Call Stack:
    0.0114     404600   1. {main}() /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:0
    0.0551    2236352   2. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:12
    0.0631    2842184   3. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/CLI.php:29
    0.0813    3094184   4. PHPDocsMD\Console\CLI->doRun(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:166
    0.0833    3107856   5. PHPDocsMD\Console\CLI->doRunCommand(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:290
    0.0833    3107856   6. PHPDocsMD\Console\PHPDocsMDCommand->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:971
    0.0845    3110488   7. PHPDocsMD\Console\PHPDocsMDCommand->execute(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Command/Command.php:255
    0.0928    3932096   8. PHPDocsMD\Console\PHPDocsMDCommand->getClassEntity(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:169
    0.0958    4049448   9. PHPDocsMD\Reflector->getClassEntity() /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:57
    0.0970    4069352  10. PHPDocsMD\Reflector->getClassFunctions(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:83
    0.1225    4119120  11. PHPDocsMD\Reflector->createFunctionEntity(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:118
    0.1235    4123256  12. PHPDocsMD\Reflector->getParams(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:161
    0.1235    4124408  13. PHPDocsMD\Reflector->createParameterEntity(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:438
    0.1236    4124408  14. PHPDocsMD\Reflector::getParamType(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:252


Deprecated: Function ReflectionParameter::export() is deprecated in /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php on line 344

Call Stack:
    0.0114     404600   1. {main}() /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:0
    0.0551    2236352   2. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:12
    0.0631    2842184   3. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/CLI.php:29
    0.0813    3094184   4. PHPDocsMD\Console\CLI->doRun(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:166
    0.0833    3107856   5. PHPDocsMD\Console\CLI->doRunCommand(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:290
    0.0833    3107856   6. PHPDocsMD\Console\PHPDocsMDCommand->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:971
    0.0845    3110488   7. PHPDocsMD\Console\PHPDocsMDCommand->execute(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Command/Command.php:255
    0.0928    3932096   8. PHPDocsMD\Console\PHPDocsMDCommand->getClassEntity(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:169
    0.0958    4049448   9. PHPDocsMD\Reflector->getClassEntity() /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:57
    0.0970    4069352  10. PHPDocsMD\Reflector->getClassFunctions(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:83
    0.1225    4119120  11. PHPDocsMD\Reflector->createFunctionEntity(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:118
    0.1235    4123256  12. PHPDocsMD\Reflector->getParams(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:161
    0.1241    4125384  13. PHPDocsMD\Reflector->createParameterEntity(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:438
    0.1241    4125384  14. PHPDocsMD\Reflector::getParamType(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:252


Deprecated: Function ReflectionParameter::export() is deprecated in /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php on line 344

Call Stack:
    0.0114     404600   1. {main}() /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:0
    0.0551    2236352   2. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:12
    0.0631    2842184   3. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/CLI.php:29
    0.0813    3094184   4. PHPDocsMD\Console\CLI->doRun(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:166
    0.0833    3107856   5. PHPDocsMD\Console\CLI->doRunCommand(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:290
    0.0833    3107856   6. PHPDocsMD\Console\PHPDocsMDCommand->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:971
    0.0845    3110488   7. PHPDocsMD\Console\PHPDocsMDCommand->execute(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Command/Command.php:255
    0.0928    3932096   8. PHPDocsMD\Console\PHPDocsMDCommand->getClassEntity(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:169
    0.0958    4049448   9. PHPDocsMD\Reflector->getClassEntity() /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:57
    0.0970    4069352  10. PHPDocsMD\Reflector->getClassFunctions(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:83
    0.1225    4119120  11. PHPDocsMD\Reflector->createFunctionEntity(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:118
    0.1235    4123256  12. PHPDocsMD\Reflector->getParams(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:161
    0.1247    4126712  13. PHPDocsMD\Reflector->createParameterEntity(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:438
    0.1247    4126712  14. PHPDocsMD\Reflector::getParamType(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:252


Deprecated: Function ReflectionParameter::export() is deprecated in /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php on line 344

Call Stack:
    0.0114     404600   1. {main}() /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:0
    0.0551    2236352   2. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:12
    0.0631    2842184   3. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/CLI.php:29
    0.0813    3094184   4. PHPDocsMD\Console\CLI->doRun(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:166
    0.0833    3107856   5. PHPDocsMD\Console\CLI->doRunCommand(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:290
    0.0833    3107856   6. PHPDocsMD\Console\PHPDocsMDCommand->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:971
    0.0845    3110488   7. PHPDocsMD\Console\PHPDocsMDCommand->execute(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Command/Command.php:255
    0.0928    3932096   8. PHPDocsMD\Console\PHPDocsMDCommand->getClassEntity(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:169
    0.0958    4049448   9. PHPDocsMD\Reflector->getClassEntity() /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:57
    0.0970    4069352  10. PHPDocsMD\Reflector->getClassFunctions(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:83
    0.1253    4122488  11. PHPDocsMD\Reflector->createFunctionEntity(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:118
    0.1261    4125832  12. PHPDocsMD\Reflector->getParams(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:161
    0.1261    4126864  13. PHPDocsMD\Reflector->createParameterEntity(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:438
    0.1261    4126864  14. PHPDocsMD\Reflector::getParamType(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:252


Deprecated: Function ReflectionParameter::export() is deprecated in /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php on line 344

Call Stack:
    0.0114     404600   1. {main}() /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:0
    0.0551    2236352   2. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:12
    0.0631    2842184   3. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/CLI.php:29
    0.0813    3094184   4. PHPDocsMD\Console\CLI->doRun(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:166
    0.0833    3107856   5. PHPDocsMD\Console\CLI->doRunCommand(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:290
    0.0833    3107856   6. PHPDocsMD\Console\PHPDocsMDCommand->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:971
    0.0845    3110488   7. PHPDocsMD\Console\PHPDocsMDCommand->execute(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Command/Command.php:255
    0.0928    3932096   8. PHPDocsMD\Console\PHPDocsMDCommand->getClassEntity(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:169
    0.0958    4049448   9. PHPDocsMD\Reflector->getClassEntity() /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:57
    0.0970    4069352  10. PHPDocsMD\Reflector->getClassFunctions(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:83
    0.1253    4122488  11. PHPDocsMD\Reflector->createFunctionEntity(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:118
    0.1261    4125832  12. PHPDocsMD\Reflector->getParams(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:161
    0.1267    4127840  13. PHPDocsMD\Reflector->createParameterEntity(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:438
    0.1267    4127840  14. PHPDocsMD\Reflector::getParamType(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:252


Deprecated: Function ReflectionParameter::export() is deprecated in /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php on line 344

Call Stack:
    0.0114     404600   1. {main}() /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:0
    0.0551    2236352   2. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:12
    0.0631    2842184   3. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/CLI.php:29
    0.0813    3094184   4. PHPDocsMD\Console\CLI->doRun(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:166
    0.0833    3107856   5. PHPDocsMD\Console\CLI->doRunCommand(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:290
    0.0833    3107856   6. PHPDocsMD\Console\PHPDocsMDCommand->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:971
    0.0845    3110488   7. PHPDocsMD\Console\PHPDocsMDCommand->execute(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Command/Command.php:255
    0.0928    3932096   8. PHPDocsMD\Console\PHPDocsMDCommand->getClassEntity(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:169
    0.0958    4049448   9. PHPDocsMD\Reflector->getClassEntity() /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:57
    0.0970    4069352  10. PHPDocsMD\Reflector->getClassFunctions(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:83
    0.1272    4124712  11. PHPDocsMD\Reflector->createFunctionEntity(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:118
    0.1278    4127296  12. PHPDocsMD\Reflector->getParams(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:161
    0.1278    4128200  13. PHPDocsMD\Reflector->createParameterEntity(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:438
    0.1279    4128200  14. PHPDocsMD\Reflector::getParamType(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:252


Deprecated: Function ReflectionParameter::export() is deprecated in /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php on line 344

Call Stack:
    0.0114     404600   1. {main}() /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:0
    0.0551    2236352   2. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:12
    0.0631    2842184   3. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/CLI.php:29
    0.0813    3094184   4. PHPDocsMD\Console\CLI->doRun(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:166
    0.0833    3107856   5. PHPDocsMD\Console\CLI->doRunCommand(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:290
    0.0833    3107856   6. PHPDocsMD\Console\PHPDocsMDCommand->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:971
    0.0845    3110488   7. PHPDocsMD\Console\PHPDocsMDCommand->execute(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Command/Command.php:255
    0.0928    3932096   8. PHPDocsMD\Console\PHPDocsMDCommand->getClassEntity(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:169
    0.0958    4049448   9. PHPDocsMD\Reflector->getClassEntity() /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:57
    0.0970    4069352  10. PHPDocsMD\Reflector->getClassFunctions(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:83
    0.1285    4126360  11. PHPDocsMD\Reflector->createFunctionEntity(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:118
    0.1292    4129552  12. PHPDocsMD\Reflector->getParams(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:161
    0.1293    4130576  13. PHPDocsMD\Reflector->createParameterEntity(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:438
    0.1293    4130576  14. PHPDocsMD\Reflector::getParamType(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:252


Deprecated: Function ReflectionParameter::export() is deprecated in /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php on line 344

Call Stack:
    0.0114     404600   1. {main}() /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:0
    0.0551    2236352   2. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:12
    0.0631    2842184   3. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/CLI.php:29
    0.0813    3094184   4. PHPDocsMD\Console\CLI->doRun(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:166
    0.0833    3107856   5. PHPDocsMD\Console\CLI->doRunCommand(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:290
    0.0833    3107856   6. PHPDocsMD\Console\PHPDocsMDCommand->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:971
    0.0845    3110488   7. PHPDocsMD\Console\PHPDocsMDCommand->execute(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Command/Command.php:255
    0.0928    3932096   8. PHPDocsMD\Console\PHPDocsMDCommand->getClassEntity(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:169
    0.0958    4049448   9. PHPDocsMD\Reflector->getClassEntity() /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:57
    0.0970    4069352  10. PHPDocsMD\Reflector->getClassFunctions(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:83
    0.1285    4126360  11. PHPDocsMD\Reflector->createFunctionEntity(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:118
    0.1292    4129552  12. PHPDocsMD\Reflector->getParams(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:161
    0.1298    4131560  13. PHPDocsMD\Reflector->createParameterEntity(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:438
    0.1299    4131560  14. PHPDocsMD\Reflector::getParamType(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:252


Deprecated: Function ReflectionParameter::export() is deprecated in /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php on line 344

Call Stack:
    0.0114     404600   1. {main}() /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:0
    0.0551    2236352   2. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:12
    0.0631    2842184   3. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/CLI.php:29
    0.0813    3094184   4. PHPDocsMD\Console\CLI->doRun(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:166
    0.0833    3107856   5. PHPDocsMD\Console\CLI->doRunCommand(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:290
    0.0833    3107856   6. PHPDocsMD\Console\PHPDocsMDCommand->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:971
    0.0845    3110488   7. PHPDocsMD\Console\PHPDocsMDCommand->execute(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Command/Command.php:255
    0.0928    3932096   8. PHPDocsMD\Console\PHPDocsMDCommand->getClassEntity(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:169
    0.0958    4049448   9. PHPDocsMD\Reflector->getClassEntity() /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:57
    0.0970    4069352  10. PHPDocsMD\Reflector->getClassFunctions(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:83
    0.1305    4128520  11. PHPDocsMD\Reflector->createFunctionEntity(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:118
    0.1311    4131056  12. PHPDocsMD\Reflector->getParams(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:161
    0.1311    4131960  13. PHPDocsMD\Reflector->createParameterEntity(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:438
    0.1311    4131960  14. PHPDocsMD\Reflector::getParamType(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:252


Deprecated: Function ReflectionParameter::export() is deprecated in /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php on line 344

Call Stack:
    0.0114     404600   1. {main}() /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:0
    0.0551    2236352   2. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:12
    0.0631    2842184   3. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/CLI.php:29
    0.0813    3094184   4. PHPDocsMD\Console\CLI->doRun(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:166
    0.0833    3107856   5. PHPDocsMD\Console\CLI->doRunCommand(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:290
    0.0833    3107856   6. PHPDocsMD\Console\PHPDocsMDCommand->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:971
    0.0845    3110488   7. PHPDocsMD\Console\PHPDocsMDCommand->execute(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Command/Command.php:255
    0.0928    3932096   8. PHPDocsMD\Console\PHPDocsMDCommand->getClassEntity(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:169
    0.0958    4049448   9. PHPDocsMD\Reflector->getClassEntity() /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:57
    0.0970    4069352  10. PHPDocsMD\Reflector->getClassFunctions(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:83
    0.1318    4130120  11. PHPDocsMD\Reflector->createFunctionEntity(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:118
    0.1325    4133192  12. PHPDocsMD\Reflector->getParams(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:161
    0.1325    4134216  13. PHPDocsMD\Reflector->createParameterEntity(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:438
    0.1325    4134216  14. PHPDocsMD\Reflector::getParamType(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:252


Deprecated: Function ReflectionParameter::export() is deprecated in /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php on line 344

Call Stack:
    0.0114     404600   1. {main}() /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:0
    0.0551    2236352   2. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:12
    0.0631    2842184   3. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/CLI.php:29
    0.0813    3094184   4. PHPDocsMD\Console\CLI->doRun(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:166
    0.0833    3107856   5. PHPDocsMD\Console\CLI->doRunCommand(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:290
    0.0833    3107856   6. PHPDocsMD\Console\PHPDocsMDCommand->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:971
    0.0845    3110488   7. PHPDocsMD\Console\PHPDocsMDCommand->execute(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Command/Command.php:255
    0.0928    3932096   8. PHPDocsMD\Console\PHPDocsMDCommand->getClassEntity(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:169
    0.0958    4049448   9. PHPDocsMD\Reflector->getClassEntity() /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:57
    0.0970    4069352  10. PHPDocsMD\Reflector->getClassFunctions(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:83
    0.1318    4130120  11. PHPDocsMD\Reflector->createFunctionEntity(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:118
    0.1325    4133192  12. PHPDocsMD\Reflector->getParams(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:161
    0.1331    4135192  13. PHPDocsMD\Reflector->createParameterEntity(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:438
    0.1331    4135192  14. PHPDocsMD\Reflector::getParamType(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:252


Deprecated: Function ReflectionParameter::export() is deprecated in /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php on line 344

Call Stack:
    0.0114     404600   1. {main}() /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:0
    0.0551    2236352   2. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:12
    0.0631    2842184   3. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/CLI.php:29
    0.0813    3094184   4. PHPDocsMD\Console\CLI->doRun(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:166
    0.0833    3107856   5. PHPDocsMD\Console\CLI->doRunCommand(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:290
    0.0833    3107856   6. PHPDocsMD\Console\PHPDocsMDCommand->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:971
    0.0845    3110488   7. PHPDocsMD\Console\PHPDocsMDCommand->execute(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Command/Command.php:255
    0.0928    3932096   8. PHPDocsMD\Console\PHPDocsMDCommand->getClassEntity(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:169
    0.0958    4049448   9. PHPDocsMD\Reflector->getClassEntity() /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:57
    0.0970    4069352  10. PHPDocsMD\Reflector->getClassFunctions(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:83
    0.1337    4132072  11. PHPDocsMD\Reflector->createFunctionEntity(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:118
    0.1343    4134656  12. PHPDocsMD\Reflector->getParams(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:161
    0.1344    4135560  13. PHPDocsMD\Reflector->createParameterEntity(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:438
    0.1344    4135560  14. PHPDocsMD\Reflector::getParamType(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:252


Deprecated: Function ReflectionParameter::export() is deprecated in /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php on line 344

Call Stack:
    0.0114     404600   1. {main}() /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:0
    0.0551    2236352   2. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:12
    0.0631    2842184   3. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/CLI.php:29
    0.0813    3094184   4. PHPDocsMD\Console\CLI->doRun(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:166
    0.0833    3107856   5. PHPDocsMD\Console\CLI->doRunCommand(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:290
    0.0833    3107856   6. PHPDocsMD\Console\PHPDocsMDCommand->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:971
    0.0845    3110488   7. PHPDocsMD\Console\PHPDocsMDCommand->execute(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Command/Command.php:255
    0.0928    3932096   8. PHPDocsMD\Console\PHPDocsMDCommand->getClassEntity(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:169
    0.0958    4049448   9. PHPDocsMD\Reflector->getClassEntity() /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:57
    0.0970    4069352  10. PHPDocsMD\Reflector->getClassFunctions(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:83
    0.1351    4134360  11. PHPDocsMD\Reflector->createFunctionEntity(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:118
    0.1357    4136928  12. PHPDocsMD\Reflector->getParams(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:161
    0.1357    4137832  13. PHPDocsMD\Reflector->createParameterEntity(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:438
    0.1357    4137832  14. PHPDocsMD\Reflector::getParamType(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:252


Deprecated: Function ReflectionParameter::export() is deprecated in /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php on line 344

Call Stack:
    0.0114     404600   1. {main}() /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:0
    0.0551    2236352   2. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:12
    0.0631    2842184   3. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/CLI.php:29
    0.0813    3094184   4. PHPDocsMD\Console\CLI->doRun(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:166
    0.0833    3107856   5. PHPDocsMD\Console\CLI->doRunCommand(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:290
    0.0833    3107856   6. PHPDocsMD\Console\PHPDocsMDCommand->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:971
    0.0845    3110488   7. PHPDocsMD\Console\PHPDocsMDCommand->execute(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Command/Command.php:255
    0.0928    3932096   8. PHPDocsMD\Console\PHPDocsMDCommand->getClassEntity(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:169
    0.0958    4049448   9. PHPDocsMD\Reflector->getClassEntity() /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:57
    0.0970    4069352  10. PHPDocsMD\Reflector->getClassFunctions(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:83
    0.1364    4135992  11. PHPDocsMD\Reflector->createFunctionEntity(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:118
    0.1371    4139104  12. PHPDocsMD\Reflector->getParams(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:161
    0.1371    4140128  13. PHPDocsMD\Reflector->createParameterEntity(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:438
    0.1371    4140128  14. PHPDocsMD\Reflector::getParamType(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:252


Deprecated: Function ReflectionParameter::export() is deprecated in /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php on line 344

Call Stack:
    0.0114     404600   1. {main}() /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:0
    0.0551    2236352   2. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:12
    0.0631    2842184   3. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/CLI.php:29
    0.0813    3094184   4. PHPDocsMD\Console\CLI->doRun(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:166
    0.0833    3107856   5. PHPDocsMD\Console\CLI->doRunCommand(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:290
    0.0833    3107856   6. PHPDocsMD\Console\PHPDocsMDCommand->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:971
    0.0845    3110488   7. PHPDocsMD\Console\PHPDocsMDCommand->execute(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Command/Command.php:255
    0.0928    3932096   8. PHPDocsMD\Console\PHPDocsMDCommand->getClassEntity(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:169
    0.0958    4049448   9. PHPDocsMD\Reflector->getClassEntity() /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:57
    0.0970    4069352  10. PHPDocsMD\Reflector->getClassFunctions(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:83
    0.1364    4135992  11. PHPDocsMD\Reflector->createFunctionEntity(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:118
    0.1371    4139104  12. PHPDocsMD\Reflector->getParams(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:161
    0.1376    4141112  13. PHPDocsMD\Reflector->createParameterEntity(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:438
    0.1377    4141112  14. PHPDocsMD\Reflector::getParamType(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:252


Deprecated: Function ReflectionParameter::export() is deprecated in /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php on line 344

Call Stack:
    0.0114     404600   1. {main}() /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:0
    0.0551    2236352   2. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:12
    0.0631    2842184   3. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/CLI.php:29
    0.0813    3094184   4. PHPDocsMD\Console\CLI->doRun(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:166
    0.0833    3107856   5. PHPDocsMD\Console\CLI->doRunCommand(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:290
    0.0833    3107856   6. PHPDocsMD\Console\PHPDocsMDCommand->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:971
    0.0845    3110488   7. PHPDocsMD\Console\PHPDocsMDCommand->execute(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Command/Command.php:255
    0.0928    3932096   8. PHPDocsMD\Console\PHPDocsMDCommand->getClassEntity(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:169
    0.0958    4049448   9. PHPDocsMD\Reflector->getClassEntity() /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:57
    0.0970    4069352  10. PHPDocsMD\Reflector->getClassFunctions(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:83
    0.1383    4137984  11. PHPDocsMD\Reflector->createFunctionEntity(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:118
    0.1390    4141096  12. PHPDocsMD\Reflector->getParams(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:161
    0.1390    4142120  13. PHPDocsMD\Reflector->createParameterEntity(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:438
    0.1391    4142120  14. PHPDocsMD\Reflector::getParamType(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:252


Deprecated: Function ReflectionParameter::export() is deprecated in /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php on line 344

Call Stack:
    0.0114     404600   1. {main}() /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:0
    0.0551    2236352   2. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:12
    0.0631    2842184   3. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/CLI.php:29
    0.0813    3094184   4. PHPDocsMD\Console\CLI->doRun(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:166
    0.0833    3107856   5. PHPDocsMD\Console\CLI->doRunCommand(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:290
    0.0833    3107856   6. PHPDocsMD\Console\PHPDocsMDCommand->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:971
    0.0845    3110488   7. PHPDocsMD\Console\PHPDocsMDCommand->execute(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Command/Command.php:255
    0.0928    3932096   8. PHPDocsMD\Console\PHPDocsMDCommand->getClassEntity(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:169
    0.0958    4049448   9. PHPDocsMD\Reflector->getClassEntity() /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:57
    0.0970    4069352  10. PHPDocsMD\Reflector->getClassFunctions(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:83
    0.1383    4137984  11. PHPDocsMD\Reflector->createFunctionEntity(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:118
    0.1390    4141096  12. PHPDocsMD\Reflector->getParams(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:161
    0.1396    4143104  13. PHPDocsMD\Reflector->createParameterEntity(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:438
    0.1397    4143104  14. PHPDocsMD\Reflector::getParamType(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:252


Deprecated: Function ReflectionParameter::export() is deprecated in /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php on line 344

Call Stack:
    0.0114     404600   1. {main}() /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:0
    0.0551    2236352   2. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:12
    0.0631    2842184   3. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/CLI.php:29
    0.0813    3094184   4. PHPDocsMD\Console\CLI->doRun(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:166
    0.0833    3107856   5. PHPDocsMD\Console\CLI->doRunCommand(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:290
    0.0833    3107856   6. PHPDocsMD\Console\PHPDocsMDCommand->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:971
    0.0845    3110488   7. PHPDocsMD\Console\PHPDocsMDCommand->execute(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Command/Command.php:255
    0.0928    3932096   8. PHPDocsMD\Console\PHPDocsMDCommand->getClassEntity(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:169
    0.0958    4049448   9. PHPDocsMD\Reflector->getClassEntity() /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:57
    0.0970    4069352  10. PHPDocsMD\Reflector->getClassFunctions(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:83
    0.1403    4139976  11. PHPDocsMD\Reflector->createFunctionEntity(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:118
    0.1409    4142544  12. PHPDocsMD\Reflector->getParams(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:161
    0.1409    4143448  13. PHPDocsMD\Reflector->createParameterEntity(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:438
    0.1409    4143448  14. PHPDocsMD\Reflector::getParamType(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:252


Deprecated: Function ReflectionParameter::export() is deprecated in /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php on line 344

Call Stack:
    0.0114     404600   1. {main}() /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:0
    0.0551    2236352   2. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:12
    0.0631    2842184   3. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/CLI.php:29
    0.0813    3094184   4. PHPDocsMD\Console\CLI->doRun(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:166
    0.0833    3107856   5. PHPDocsMD\Console\CLI->doRunCommand(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:290
    0.0833    3107856   6. PHPDocsMD\Console\PHPDocsMDCommand->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:971
    0.0845    3110488   7. PHPDocsMD\Console\PHPDocsMDCommand->execute(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Command/Command.php:255
    0.0928    3932096   8. PHPDocsMD\Console\PHPDocsMDCommand->getClassEntity(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:169
    0.0958    4049448   9. PHPDocsMD\Reflector->getClassEntity() /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:57
    0.0970    4069352  10. PHPDocsMD\Reflector->getClassFunctions(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:83
    0.1415    4141608  11. PHPDocsMD\Reflector->createFunctionEntity(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:118
    0.1421    4144160  12. PHPDocsMD\Reflector->getParams(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:161
    0.1421    4145064  13. PHPDocsMD\Reflector->createParameterEntity(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:438
    0.1422    4145064  14. PHPDocsMD\Reflector::getParamType(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:252


Deprecated: Function ReflectionParameter::export() is deprecated in /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php on line 344

Call Stack:
    0.0114     404600   1. {main}() /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:0
    0.0551    2236352   2. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:12
    0.0631    2842184   3. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/CLI.php:29
    0.0813    3094184   4. PHPDocsMD\Console\CLI->doRun(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:166
    0.0833    3107856   5. PHPDocsMD\Console\CLI->doRunCommand(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:290
    0.0833    3107856   6. PHPDocsMD\Console\PHPDocsMDCommand->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:971
    0.0845    3110488   7. PHPDocsMD\Console\PHPDocsMDCommand->execute(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Command/Command.php:255
    0.0928    3932096   8. PHPDocsMD\Console\PHPDocsMDCommand->getClassEntity(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:169
    0.0958    4049448   9. PHPDocsMD\Reflector->getClassEntity() /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:57
    0.0970    4069352  10. PHPDocsMD\Reflector->getClassFunctions(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:83
    0.1427    4143224  11. PHPDocsMD\Reflector->createFunctionEntity(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:118
    0.1434    4146312  12. PHPDocsMD\Reflector->getParams(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:161
    0.1435    4147336  13. PHPDocsMD\Reflector->createParameterEntity(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:438
    0.1435    4147336  14. PHPDocsMD\Reflector::getParamType(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:252


Deprecated: Function ReflectionParameter::export() is deprecated in /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php on line 344

Call Stack:
    0.0114     404600   1. {main}() /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:0
    0.0551    2236352   2. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:12
    0.0631    2842184   3. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/CLI.php:29
    0.0813    3094184   4. PHPDocsMD\Console\CLI->doRun(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:166
    0.0833    3107856   5. PHPDocsMD\Console\CLI->doRunCommand(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:290
    0.0833    3107856   6. PHPDocsMD\Console\PHPDocsMDCommand->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:971
    0.0845    3110488   7. PHPDocsMD\Console\PHPDocsMDCommand->execute(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Command/Command.php:255
    0.0928    3932096   8. PHPDocsMD\Console\PHPDocsMDCommand->getClassEntity(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:169
    0.0958    4049448   9. PHPDocsMD\Reflector->getClassEntity() /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:57
    0.0970    4069352  10. PHPDocsMD\Reflector->getClassFunctions(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:83
    0.1427    4143224  11. PHPDocsMD\Reflector->createFunctionEntity(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:118
    0.1434    4146312  12. PHPDocsMD\Reflector->getParams(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:161
    0.1439    4148320  13. PHPDocsMD\Reflector->createParameterEntity(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:438
    0.1440    4148320  14. PHPDocsMD\Reflector::getParamType(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:252


Deprecated: Function ReflectionParameter::export() is deprecated in /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php on line 344

Call Stack:
    0.0114     404600   1. {main}() /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:0
    0.0551    2236352   2. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:12
    0.0631    2842184   3. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/CLI.php:29
    0.0813    3094184   4. PHPDocsMD\Console\CLI->doRun(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:166
    0.0833    3107856   5. PHPDocsMD\Console\CLI->doRunCommand(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:290
    0.0833    3107856   6. PHPDocsMD\Console\PHPDocsMDCommand->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:971
    0.0845    3110488   7. PHPDocsMD\Console\PHPDocsMDCommand->execute(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Command/Command.php:255
    0.0928    3932096   8. PHPDocsMD\Console\PHPDocsMDCommand->getClassEntity(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:169
    0.0958    4049448   9. PHPDocsMD\Reflector->getClassEntity() /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:57
    0.0970    4069352  10. PHPDocsMD\Reflector->getClassFunctions(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:83
    0.1446    4145280  11. PHPDocsMD\Reflector->createFunctionEntity(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:118
    0.1452    4147832  12. PHPDocsMD\Reflector->getParams(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:161
    0.1452    4148736  13. PHPDocsMD\Reflector->createParameterEntity(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:438
    0.1453    4148736  14. PHPDocsMD\Reflector::getParamType(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:252


Deprecated: Function ReflectionParameter::export() is deprecated in /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php on line 344

Call Stack:
    0.0114     404600   1. {main}() /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:0
    0.0551    2236352   2. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:12
    0.0631    2842184   3. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/CLI.php:29
    0.0813    3094184   4. PHPDocsMD\Console\CLI->doRun(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:166
    0.0833    3107856   5. PHPDocsMD\Console\CLI->doRunCommand(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:290
    0.0833    3107856   6. PHPDocsMD\Console\PHPDocsMDCommand->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:971
    0.0845    3110488   7. PHPDocsMD\Console\PHPDocsMDCommand->execute(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Command/Command.php:255
    0.0928    3932096   8. PHPDocsMD\Console\PHPDocsMDCommand->getClassEntity(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:169
    0.0958    4049448   9. PHPDocsMD\Reflector->getClassEntity() /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:57
    0.0970    4069352  10. PHPDocsMD\Reflector->getClassFunctions(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:83
    0.1458    4146896  11. PHPDocsMD\Reflector->createFunctionEntity(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:118
    0.1465    4149984  12. PHPDocsMD\Reflector->getParams(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:161
    0.1465    4151008  13. PHPDocsMD\Reflector->createParameterEntity(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:438
    0.1466    4151008  14. PHPDocsMD\Reflector::getParamType(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:252


Deprecated: Function ReflectionParameter::export() is deprecated in /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php on line 344

Call Stack:
    0.0114     404600   1. {main}() /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:0
    0.0551    2236352   2. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:12
    0.0631    2842184   3. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/CLI.php:29
    0.0813    3094184   4. PHPDocsMD\Console\CLI->doRun(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:166
    0.0833    3107856   5. PHPDocsMD\Console\CLI->doRunCommand(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:290
    0.0833    3107856   6. PHPDocsMD\Console\PHPDocsMDCommand->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:971
    0.0845    3110488   7. PHPDocsMD\Console\PHPDocsMDCommand->execute(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Command/Command.php:255
    0.0928    3932096   8. PHPDocsMD\Console\PHPDocsMDCommand->getClassEntity(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:169
    0.0958    4049448   9. PHPDocsMD\Reflector->getClassEntity() /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:57
    0.0970    4069352  10. PHPDocsMD\Reflector->getClassFunctions(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:83
    0.1458    4146896  11. PHPDocsMD\Reflector->createFunctionEntity(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:118
    0.1465    4149984  12. PHPDocsMD\Reflector->getParams(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:161
    0.1470    4151984  13. PHPDocsMD\Reflector->createParameterEntity(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:438
    0.1471    4151984  14. PHPDocsMD\Reflector::getParamType(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:252


Deprecated: Function ReflectionParameter::export() is deprecated in /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php on line 344

Call Stack:
    0.0114     404600   1. {main}() /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:0
    0.0551    2236352   2. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:12
    0.0631    2842184   3. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/CLI.php:29
    0.0813    3094184   4. PHPDocsMD\Console\CLI->doRun(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:166
    0.0833    3107856   5. PHPDocsMD\Console\CLI->doRunCommand(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:290
    0.0833    3107856   6. PHPDocsMD\Console\PHPDocsMDCommand->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:971
    0.0845    3110488   7. PHPDocsMD\Console\PHPDocsMDCommand->execute(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Command/Command.php:255
    0.0928    3932096   8. PHPDocsMD\Console\PHPDocsMDCommand->getClassEntity(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:169
    0.0958    4049448   9. PHPDocsMD\Reflector->getClassEntity() /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:57
    0.0970    4069352  10. PHPDocsMD\Reflector->getClassFunctions(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:83
    0.1476    4148864  11. PHPDocsMD\Reflector->createFunctionEntity(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:118
    0.1482    4151448  12. PHPDocsMD\Reflector->getParams(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:161
    0.1482    4152352  13. PHPDocsMD\Reflector->createParameterEntity(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:438
    0.1483    4152352  14. PHPDocsMD\Reflector::getParamType(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:252


Deprecated: Function ReflectionParameter::export() is deprecated in /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php on line 344

Call Stack:
    0.0114     404600   1. {main}() /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:0
    0.0551    2236352   2. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:12
    0.0631    2842184   3. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/CLI.php:29
    0.0813    3094184   4. PHPDocsMD\Console\CLI->doRun(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:166
    0.0833    3107856   5. PHPDocsMD\Console\CLI->doRunCommand(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:290
    0.0833    3107856   6. PHPDocsMD\Console\PHPDocsMDCommand->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:971
    0.0845    3110488   7. PHPDocsMD\Console\PHPDocsMDCommand->execute(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Command/Command.php:255
    0.0928    3932096   8. PHPDocsMD\Console\PHPDocsMDCommand->getClassEntity(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:169
    0.0958    4049448   9. PHPDocsMD\Reflector->getClassEntity() /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:57
    0.0970    4069352  10. PHPDocsMD\Reflector->getClassFunctions(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:83
    0.1489    4150512  11. PHPDocsMD\Reflector->createFunctionEntity(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:118
    0.1495    4153080  12. PHPDocsMD\Reflector->getParams(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:161
    0.1495    4153984  13. PHPDocsMD\Reflector->createParameterEntity(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:438
    0.1495    4153984  14. PHPDocsMD\Reflector::getParamType(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:252


Deprecated: Function ReflectionParameter::export() is deprecated in /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php on line 344

Call Stack:
    0.0114     404600   1. {main}() /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:0
    0.0551    2236352   2. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:12
    0.0631    2842184   3. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/CLI.php:29
    0.0813    3094184   4. PHPDocsMD\Console\CLI->doRun(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:166
    0.0833    3107856   5. PHPDocsMD\Console\CLI->doRunCommand(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:290
    0.0833    3107856   6. PHPDocsMD\Console\PHPDocsMDCommand->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:971
    0.0845    3110488   7. PHPDocsMD\Console\PHPDocsMDCommand->execute(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Command/Command.php:255
    0.0928    3932096   8. PHPDocsMD\Console\PHPDocsMDCommand->getClassEntity(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:169
    0.0958    4049448   9. PHPDocsMD\Reflector->getClassEntity() /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:57
    0.0970    4069352  10. PHPDocsMD\Reflector->getClassFunctions(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:83
    0.1501    4152144  11. PHPDocsMD\Reflector->createFunctionEntity(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:118
    0.1508    4155256  12. PHPDocsMD\Reflector->getParams(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:161
    0.1509    4156280  13. PHPDocsMD\Reflector->createParameterEntity(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:438
    0.1509    4156280  14. PHPDocsMD\Reflector::getParamType(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:252


Deprecated: Function ReflectionParameter::export() is deprecated in /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php on line 344

Call Stack:
    0.0114     404600   1. {main}() /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:0
    0.0551    2236352   2. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:12
    0.0631    2842184   3. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/CLI.php:29
    0.0813    3094184   4. PHPDocsMD\Console\CLI->doRun(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:166
    0.0833    3107856   5. PHPDocsMD\Console\CLI->doRunCommand(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:290
    0.0833    3107856   6. PHPDocsMD\Console\PHPDocsMDCommand->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:971
    0.0845    3110488   7. PHPDocsMD\Console\PHPDocsMDCommand->execute(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Command/Command.php:255
    0.0928    3932096   8. PHPDocsMD\Console\PHPDocsMDCommand->getClassEntity(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:169
    0.0958    4049448   9. PHPDocsMD\Reflector->getClassEntity() /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:57
    0.0970    4069352  10. PHPDocsMD\Reflector->getClassFunctions(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:83
    0.1501    4152144  11. PHPDocsMD\Reflector->createFunctionEntity(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:118
    0.1508    4155256  12. PHPDocsMD\Reflector->getParams(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:161
    0.1515    4157264  13. PHPDocsMD\Reflector->createParameterEntity(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:438
    0.1515    4157264  14. PHPDocsMD\Reflector::getParamType(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:252


Deprecated: Function ReflectionParameter::export() is deprecated in /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php on line 344

Call Stack:
    0.0114     404600   1. {main}() /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:0
    0.0551    2236352   2. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:12
    0.0631    2842184   3. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/CLI.php:29
    0.0813    3094184   4. PHPDocsMD\Console\CLI->doRun(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:166
    0.0833    3107856   5. PHPDocsMD\Console\CLI->doRunCommand(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:290
    0.0833    3107856   6. PHPDocsMD\Console\PHPDocsMDCommand->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:971
    0.0845    3110488   7. PHPDocsMD\Console\PHPDocsMDCommand->execute(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Command/Command.php:255
    0.0928    3932096   8. PHPDocsMD\Console\PHPDocsMDCommand->getClassEntity(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:169
    0.0958    4049448   9. PHPDocsMD\Reflector->getClassEntity() /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:57
    0.0970    4069352  10. PHPDocsMD\Reflector->getClassFunctions(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:83
    0.1534    4154136  11. PHPDocsMD\Reflector->createFunctionEntity(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:118
    0.1541    4157248  12. PHPDocsMD\Reflector->getParams(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:161
    0.1541    4158272  13. PHPDocsMD\Reflector->createParameterEntity(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:438
    0.1541    4158272  14. PHPDocsMD\Reflector::getParamType(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:252


Deprecated: Function ReflectionParameter::export() is deprecated in /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php on line 344

Call Stack:
    0.0114     404600   1. {main}() /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:0
    0.0551    2236352   2. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:12
    0.0631    2842184   3. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/CLI.php:29
    0.0813    3094184   4. PHPDocsMD\Console\CLI->doRun(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:166
    0.0833    3107856   5. PHPDocsMD\Console\CLI->doRunCommand(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:290
    0.0833    3107856   6. PHPDocsMD\Console\PHPDocsMDCommand->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:971
    0.0845    3110488   7. PHPDocsMD\Console\PHPDocsMDCommand->execute(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Command/Command.php:255
    0.0928    3932096   8. PHPDocsMD\Console\PHPDocsMDCommand->getClassEntity(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:169
    0.0958    4049448   9. PHPDocsMD\Reflector->getClassEntity() /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:57
    0.0970    4069352  10. PHPDocsMD\Reflector->getClassFunctions(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:83
    0.1534    4154136  11. PHPDocsMD\Reflector->createFunctionEntity(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:118
    0.1541    4157248  12. PHPDocsMD\Reflector->getParams(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:161
    0.1548    4159256  13. PHPDocsMD\Reflector->createParameterEntity(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:438
    0.1548    4159256  14. PHPDocsMD\Reflector::getParamType(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:252


Deprecated: Function ReflectionParameter::export() is deprecated in /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php on line 344

Call Stack:
    0.0114     404600   1. {main}() /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:0
    0.0551    2236352   2. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:12
    0.0631    2842184   3. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/CLI.php:29
    0.0813    3094184   4. PHPDocsMD\Console\CLI->doRun(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:166
    0.0833    3107856   5. PHPDocsMD\Console\CLI->doRunCommand(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:290
    0.0833    3107856   6. PHPDocsMD\Console\PHPDocsMDCommand->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:971
    0.0845    3110488   7. PHPDocsMD\Console\PHPDocsMDCommand->execute(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Command/Command.php:255
    0.0928    3932096   8. PHPDocsMD\Console\PHPDocsMDCommand->getClassEntity(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:169
    0.0958    4049448   9. PHPDocsMD\Reflector->getClassEntity() /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:57
    0.0970    4069352  10. PHPDocsMD\Reflector->getClassFunctions(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:83
    0.1554    4156128  11. PHPDocsMD\Reflector->createFunctionEntity(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:118
    0.1560    4158680  12. PHPDocsMD\Reflector->getParams(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:161
    0.1560    4159584  13. PHPDocsMD\Reflector->createParameterEntity(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:438
    0.1560    4159584  14. PHPDocsMD\Reflector::getParamType(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:252


Deprecated: Function ReflectionParameter::export() is deprecated in /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php on line 344

Call Stack:
    0.0114     404600   1. {main}() /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:0
    0.0551    2236352   2. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:12
    0.0631    2842184   3. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/CLI.php:29
    0.0813    3094184   4. PHPDocsMD\Console\CLI->doRun(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:166
    0.0833    3107856   5. PHPDocsMD\Console\CLI->doRunCommand(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:290
    0.0833    3107856   6. PHPDocsMD\Console\PHPDocsMDCommand->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:971
    0.0845    3110488   7. PHPDocsMD\Console\PHPDocsMDCommand->execute(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Command/Command.php:255
    0.0928    3932096   8. PHPDocsMD\Console\PHPDocsMDCommand->getClassEntity(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:169
    0.0958    4049448   9. PHPDocsMD\Reflector->getClassEntity() /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:57
    0.0970    4069352  10. PHPDocsMD\Reflector->getClassFunctions(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:83
    0.1567    4157744  11. PHPDocsMD\Reflector->createFunctionEntity(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:118
    0.1572    4160312  12. PHPDocsMD\Reflector->getParams(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:161
    0.1573    4161216  13. PHPDocsMD\Reflector->createParameterEntity(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:438
    0.1573    4161216  14. PHPDocsMD\Reflector::getParamType(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:252


Deprecated: Function ReflectionParameter::export() is deprecated in /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php on line 344

Call Stack:
    0.0114     404600   1. {main}() /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:0
    0.0551    2236352   2. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:12
    0.0631    2842184   3. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/CLI.php:29
    0.0813    3094184   4. PHPDocsMD\Console\CLI->doRun(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:166
    0.0833    3107856   5. PHPDocsMD\Console\CLI->doRunCommand(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:290
    0.0833    3107856   6. PHPDocsMD\Console\PHPDocsMDCommand->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:971
    0.0845    3110488   7. PHPDocsMD\Console\PHPDocsMDCommand->execute(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Command/Command.php:255
    0.0928    3932096   8. PHPDocsMD\Console\PHPDocsMDCommand->getClassEntity(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:169
    0.0958    4049448   9. PHPDocsMD\Reflector->getClassEntity() /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:57
    0.0970    4069352  10. PHPDocsMD\Reflector->getClassFunctions(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:83
    0.1579    4159376  11. PHPDocsMD\Reflector->createFunctionEntity(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:118
    0.1586    4162488  12. PHPDocsMD\Reflector->getParams(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:161
    0.1586    4163512  13. PHPDocsMD\Reflector->createParameterEntity(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:438
    0.1586    4163512  14. PHPDocsMD\Reflector::getParamType(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:252


Deprecated: Function ReflectionParameter::export() is deprecated in /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php on line 344

Call Stack:
    0.0114     404600   1. {main}() /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:0
    0.0551    2236352   2. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:12
    0.0631    2842184   3. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/CLI.php:29
    0.0813    3094184   4. PHPDocsMD\Console\CLI->doRun(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:166
    0.0833    3107856   5. PHPDocsMD\Console\CLI->doRunCommand(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:290
    0.0833    3107856   6. PHPDocsMD\Console\PHPDocsMDCommand->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:971
    0.0845    3110488   7. PHPDocsMD\Console\PHPDocsMDCommand->execute(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Command/Command.php:255
    0.0928    3932096   8. PHPDocsMD\Console\PHPDocsMDCommand->getClassEntity(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:169
    0.0958    4049448   9. PHPDocsMD\Reflector->getClassEntity() /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:57
    0.0970    4069352  10. PHPDocsMD\Reflector->getClassFunctions(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:83
    0.1579    4159376  11. PHPDocsMD\Reflector->createFunctionEntity(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:118
    0.1586    4162488  12. PHPDocsMD\Reflector->getParams(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:161
    0.1592    4164496  13. PHPDocsMD\Reflector->createParameterEntity(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:438
    0.1592    4164496  14. PHPDocsMD\Reflector::getParamType(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:252


Deprecated: Function ReflectionParameter::export() is deprecated in /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php on line 344

Call Stack:
    0.0114     404600   1. {main}() /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:0
    0.0551    2236352   2. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:12
    0.0631    2842184   3. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/CLI.php:29
    0.0813    3094184   4. PHPDocsMD\Console\CLI->doRun(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:166
    0.0833    3107856   5. PHPDocsMD\Console\CLI->doRunCommand(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:290
    0.0833    3107856   6. PHPDocsMD\Console\PHPDocsMDCommand->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:971
    0.0845    3110488   7. PHPDocsMD\Console\PHPDocsMDCommand->execute(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Command/Command.php:255
    0.0928    3932096   8. PHPDocsMD\Console\PHPDocsMDCommand->getClassEntity(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:169
    0.0958    4049448   9. PHPDocsMD\Reflector->getClassEntity() /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:57
    0.0970    4069352  10. PHPDocsMD\Reflector->getClassFunctions(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:83
    0.1598    4161368  11. PHPDocsMD\Reflector->createFunctionEntity(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:118
    0.1604    4163920  12. PHPDocsMD\Reflector->getParams(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:161
    0.1604    4164824  13. PHPDocsMD\Reflector->createParameterEntity(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:438
    0.1605    4164824  14. PHPDocsMD\Reflector::getParamType(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:252


Deprecated: Function ReflectionParameter::export() is deprecated in /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php on line 344

Call Stack:
    0.0114     404600   1. {main}() /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:0
    0.0551    2236352   2. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:12
    0.0631    2842184   3. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/CLI.php:29
    0.0813    3094184   4. PHPDocsMD\Console\CLI->doRun(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:166
    0.0833    3107856   5. PHPDocsMD\Console\CLI->doRunCommand(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:290
    0.0833    3107856   6. PHPDocsMD\Console\PHPDocsMDCommand->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:971
    0.0845    3110488   7. PHPDocsMD\Console\PHPDocsMDCommand->execute(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Command/Command.php:255
    0.0928    3932096   8. PHPDocsMD\Console\PHPDocsMDCommand->getClassEntity(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:169
    0.0958    4049448   9. PHPDocsMD\Reflector->getClassEntity() /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:57
    0.0970    4069352  10. PHPDocsMD\Reflector->getClassFunctions(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:83
    0.1610    4164264  11. PHPDocsMD\Reflector->createFunctionEntity(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:118
    0.1617    4167352  12. PHPDocsMD\Reflector->getParams(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:161
    0.1618    4168376  13. PHPDocsMD\Reflector->createParameterEntity(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:438
    0.1618    4168376  14. PHPDocsMD\Reflector::getParamType(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:252


Deprecated: Function ReflectionParameter::export() is deprecated in /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php on line 344

Call Stack:
    0.0114     404600   1. {main}() /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:0
    0.0551    2236352   2. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:12
    0.0631    2842184   3. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/CLI.php:29
    0.0813    3094184   4. PHPDocsMD\Console\CLI->doRun(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:166
    0.0833    3107856   5. PHPDocsMD\Console\CLI->doRunCommand(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:290
    0.0833    3107856   6. PHPDocsMD\Console\PHPDocsMDCommand->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:971
    0.0845    3110488   7. PHPDocsMD\Console\PHPDocsMDCommand->execute(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Command/Command.php:255
    0.0928    3932096   8. PHPDocsMD\Console\PHPDocsMDCommand->getClassEntity(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:169
    0.0958    4049448   9. PHPDocsMD\Reflector->getClassEntity() /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:57
    0.0970    4069352  10. PHPDocsMD\Reflector->getClassFunctions(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:83
    0.1610    4164264  11. PHPDocsMD\Reflector->createFunctionEntity(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:118
    0.1617    4167352  12. PHPDocsMD\Reflector->getParams(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:161
    0.1624    4169352  13. PHPDocsMD\Reflector->createParameterEntity(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:438
    0.1624    4169352  14. PHPDocsMD\Reflector::getParamType(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:252


Deprecated: Function ReflectionParameter::export() is deprecated in /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php on line 344

Call Stack:
    0.0114     404600   1. {main}() /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:0
    0.0551    2236352   2. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:12
    0.0631    2842184   3. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/CLI.php:29
    0.0813    3094184   4. PHPDocsMD\Console\CLI->doRun(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:166
    0.0833    3107856   5. PHPDocsMD\Console\CLI->doRunCommand(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:290
    0.0833    3107856   6. PHPDocsMD\Console\PHPDocsMDCommand->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:971
    0.0845    3110488   7. PHPDocsMD\Console\PHPDocsMDCommand->execute(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Command/Command.php:255
    0.0928    3932096   8. PHPDocsMD\Console\PHPDocsMDCommand->getClassEntity(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:169
    0.0958    4049448   9. PHPDocsMD\Reflector->getClassEntity() /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:57
    0.0970    4069352  10. PHPDocsMD\Reflector->getClassFunctions(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:83
    0.1630    4166232  11. PHPDocsMD\Reflector->createFunctionEntity(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:118
    0.1636    4168816  12. PHPDocsMD\Reflector->getParams(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:161
    0.1636    4169720  13. PHPDocsMD\Reflector->createParameterEntity(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:438
    0.1636    4169720  14. PHPDocsMD\Reflector::getParamType(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:252


Deprecated: Function ReflectionParameter::export() is deprecated in /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php on line 344

Call Stack:
    0.0114     404600   1. {main}() /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:0
    0.0551    2236352   2. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:12
    0.0631    2842184   3. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/CLI.php:29
    0.0813    3094184   4. PHPDocsMD\Console\CLI->doRun(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:166
    0.0833    3107856   5. PHPDocsMD\Console\CLI->doRunCommand(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:290
    0.0833    3107856   6. PHPDocsMD\Console\PHPDocsMDCommand->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:971
    0.0845    3110488   7. PHPDocsMD\Console\PHPDocsMDCommand->execute(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Command/Command.php:255
    0.0928    3932096   8. PHPDocsMD\Console\PHPDocsMDCommand->getClassEntity(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:169
    0.0958    4049448   9. PHPDocsMD\Reflector->getClassEntity() /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:57
    0.0970    4069352  10. PHPDocsMD\Reflector->getClassFunctions(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:83
    0.1642    4167880  11. PHPDocsMD\Reflector->createFunctionEntity(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:118
    0.1648    4170464  12. PHPDocsMD\Reflector->getParams(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:161
    0.1649    4171368  13. PHPDocsMD\Reflector->createParameterEntity(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:438
    0.1649    4171368  14. PHPDocsMD\Reflector::getParamType(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:252


Deprecated: Function ReflectionParameter::export() is deprecated in /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php on line 344

Call Stack:
    0.0114     404600   1. {main}() /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:0
    0.0551    2236352   2. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:12
    0.0631    2842184   3. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/CLI.php:29
    0.0813    3094184   4. PHPDocsMD\Console\CLI->doRun(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:166
    0.0833    3107856   5. PHPDocsMD\Console\CLI->doRunCommand(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:290
    0.0833    3107856   6. PHPDocsMD\Console\PHPDocsMDCommand->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:971
    0.0845    3110488   7. PHPDocsMD\Console\PHPDocsMDCommand->execute(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Command/Command.php:255
    0.0928    3932096   8. PHPDocsMD\Console\PHPDocsMDCommand->getClassEntity(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:169
    0.0958    4049448   9. PHPDocsMD\Reflector->getClassEntity() /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:57
    0.0970    4069352  10. PHPDocsMD\Reflector->getClassFunctions(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:83
    0.1654    4169528  11. PHPDocsMD\Reflector->createFunctionEntity(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:118
    0.1662    4172640  12. PHPDocsMD\Reflector->getParams(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:161
    0.1662    4173664  13. PHPDocsMD\Reflector->createParameterEntity(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:438
    0.1662    4173664  14. PHPDocsMD\Reflector::getParamType(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:252


Deprecated: Function ReflectionParameter::export() is deprecated in /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php on line 344

Call Stack:
    0.0114     404600   1. {main}() /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:0
    0.0551    2236352   2. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:12
    0.0631    2842184   3. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/CLI.php:29
    0.0813    3094184   4. PHPDocsMD\Console\CLI->doRun(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:166
    0.0833    3107856   5. PHPDocsMD\Console\CLI->doRunCommand(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:290
    0.0833    3107856   6. PHPDocsMD\Console\PHPDocsMDCommand->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:971
    0.0845    3110488   7. PHPDocsMD\Console\PHPDocsMDCommand->execute(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Command/Command.php:255
    0.0928    3932096   8. PHPDocsMD\Console\PHPDocsMDCommand->getClassEntity(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:169
    0.0958    4049448   9. PHPDocsMD\Reflector->getClassEntity() /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:57
    0.0970    4069352  10. PHPDocsMD\Reflector->getClassFunctions(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:83
    0.1654    4169528  11. PHPDocsMD\Reflector->createFunctionEntity(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:118
    0.1662    4172640  12. PHPDocsMD\Reflector->getParams(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:161
    0.1667    4174648  13. PHPDocsMD\Reflector->createParameterEntity(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:438
    0.1667    4174648  14. PHPDocsMD\Reflector::getParamType(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:252


Deprecated: Function ReflectionParameter::export() is deprecated in /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php on line 344

Call Stack:
    0.0114     404600   1. {main}() /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:0
    0.0551    2236352   2. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:12
    0.0631    2842184   3. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/CLI.php:29
    0.0813    3094184   4. PHPDocsMD\Console\CLI->doRun(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:166
    0.0833    3107856   5. PHPDocsMD\Console\CLI->doRunCommand(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:290
    0.0833    3107856   6. PHPDocsMD\Console\PHPDocsMDCommand->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:971
    0.0845    3110488   7. PHPDocsMD\Console\PHPDocsMDCommand->execute(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Command/Command.php:255
    0.0928    3932096   8. PHPDocsMD\Console\PHPDocsMDCommand->getClassEntity(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:169
    0.0958    4049448   9. PHPDocsMD\Reflector->getClassEntity() /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:57
    0.0970    4069352  10. PHPDocsMD\Reflector->getClassFunctions(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:83
    0.1675    4171520  11. PHPDocsMD\Reflector->createFunctionEntity(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:118
    0.1681    4174632  12. PHPDocsMD\Reflector->getParams(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:161
    0.1682    4175656  13. PHPDocsMD\Reflector->createParameterEntity(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:438
    0.1682    4175656  14. PHPDocsMD\Reflector::getParamType(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:252


Deprecated: Function ReflectionParameter::export() is deprecated in /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php on line 344

Call Stack:
    0.0114     404600   1. {main}() /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:0
    0.0551    2236352   2. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:12
    0.0631    2842184   3. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/CLI.php:29
    0.0813    3094184   4. PHPDocsMD\Console\CLI->doRun(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:166
    0.0833    3107856   5. PHPDocsMD\Console\CLI->doRunCommand(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:290
    0.0833    3107856   6. PHPDocsMD\Console\PHPDocsMDCommand->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:971
    0.0845    3110488   7. PHPDocsMD\Console\PHPDocsMDCommand->execute(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Command/Command.php:255
    0.0928    3932096   8. PHPDocsMD\Console\PHPDocsMDCommand->getClassEntity(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:169
    0.0958    4049448   9. PHPDocsMD\Reflector->getClassEntity() /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:57
    0.0970    4069352  10. PHPDocsMD\Reflector->getClassFunctions(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:83
    0.1675    4171520  11. PHPDocsMD\Reflector->createFunctionEntity(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:118
    0.1681    4174632  12. PHPDocsMD\Reflector->getParams(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:161
    0.1687    4176640  13. PHPDocsMD\Reflector->createParameterEntity(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:438
    0.1687    4176640  14. PHPDocsMD\Reflector::getParamType(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:252


Deprecated: Function ReflectionParameter::export() is deprecated in /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php on line 344

Call Stack:
    0.0114     404600   1. {main}() /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:0
    0.0551    2236352   2. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:12
    0.0631    2842184   3. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/CLI.php:29
    0.0813    3094184   4. PHPDocsMD\Console\CLI->doRun(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:166
    0.0833    3107856   5. PHPDocsMD\Console\CLI->doRunCommand(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:290
    0.0833    3107856   6. PHPDocsMD\Console\PHPDocsMDCommand->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:971
    0.0845    3110488   7. PHPDocsMD\Console\PHPDocsMDCommand->execute(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Command/Command.php:255
    0.0928    3932096   8. PHPDocsMD\Console\PHPDocsMDCommand->getClassEntity(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:169
    0.0958    4049448   9. PHPDocsMD\Reflector->getClassEntity() /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:57
    0.0970    4069352  10. PHPDocsMD\Reflector->getClassFunctions(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:83
    0.1694    4173512  11. PHPDocsMD\Reflector->createFunctionEntity(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:118
    0.1700    4176096  12. PHPDocsMD\Reflector->getParams(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:161
    0.1700    4177000  13. PHPDocsMD\Reflector->createParameterEntity(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:438
    0.1701    4177000  14. PHPDocsMD\Reflector::getParamType(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:252


Deprecated: Function ReflectionParameter::export() is deprecated in /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php on line 344

Call Stack:
    0.0114     404600   1. {main}() /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:0
    0.0551    2236352   2. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:12
    0.0631    2842184   3. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/CLI.php:29
    0.0813    3094184   4. PHPDocsMD\Console\CLI->doRun(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:166
    0.0833    3107856   5. PHPDocsMD\Console\CLI->doRunCommand(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:290
    0.0833    3107856   6. PHPDocsMD\Console\PHPDocsMDCommand->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:971
    0.0845    3110488   7. PHPDocsMD\Console\PHPDocsMDCommand->execute(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Command/Command.php:255
    0.0928    3932096   8. PHPDocsMD\Console\PHPDocsMDCommand->getClassEntity(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:169
    0.0958    4049448   9. PHPDocsMD\Reflector->getClassEntity() /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:57
    0.0970    4069352  10. PHPDocsMD\Reflector->getClassFunctions(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:83
    0.1707    4175160  11. PHPDocsMD\Reflector->createFunctionEntity(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:118
    0.1715    4178744  12. PHPDocsMD\Reflector->getParams(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:161
    0.1716    4179768  13. PHPDocsMD\Reflector->createParameterEntity(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:438
    0.1716    4179768  14. PHPDocsMD\Reflector::getParamType(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:252


Deprecated: Function ReflectionParameter::export() is deprecated in /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php on line 344

Call Stack:
    0.0114     404600   1. {main}() /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:0
    0.0551    2236352   2. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:12
    0.0631    2842184   3. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/CLI.php:29
    0.0813    3094184   4. PHPDocsMD\Console\CLI->doRun(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:166
    0.0833    3107856   5. PHPDocsMD\Console\CLI->doRunCommand(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:290
    0.0833    3107856   6. PHPDocsMD\Console\PHPDocsMDCommand->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:971
    0.0845    3110488   7. PHPDocsMD\Console\PHPDocsMDCommand->execute(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Command/Command.php:255
    0.0928    3932096   8. PHPDocsMD\Console\PHPDocsMDCommand->getClassEntity(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:169
    0.0958    4049448   9. PHPDocsMD\Reflector->getClassEntity() /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:57
    0.0970    4069352  10. PHPDocsMD\Reflector->getClassFunctions(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:83
    0.1707    4175160  11. PHPDocsMD\Reflector->createFunctionEntity(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:118
    0.1715    4178744  12. PHPDocsMD\Reflector->getParams(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:161
    0.1722    4180744  13. PHPDocsMD\Reflector->createParameterEntity(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:438
    0.1722    4180744  14. PHPDocsMD\Reflector::getParamType(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:252


Deprecated: Function ReflectionParameter::export() is deprecated in /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php on line 344

Call Stack:
    0.0114     404600   1. {main}() /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:0
    0.0551    2236352   2. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:12
    0.0631    2842184   3. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/CLI.php:29
    0.0813    3094184   4. PHPDocsMD\Console\CLI->doRun(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:166
    0.0833    3107856   5. PHPDocsMD\Console\CLI->doRunCommand(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:290
    0.0833    3107856   6. PHPDocsMD\Console\PHPDocsMDCommand->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:971
    0.0845    3110488   7. PHPDocsMD\Console\PHPDocsMDCommand->execute(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Command/Command.php:255
    0.0928    3932096   8. PHPDocsMD\Console\PHPDocsMDCommand->getClassEntity(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:169
    0.0958    4049448   9. PHPDocsMD\Reflector->getClassEntity() /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:57
    0.0970    4069352  10. PHPDocsMD\Reflector->getClassFunctions(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:83
    0.1729    4177568  11. PHPDocsMD\Reflector->createFunctionEntity(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:118
    0.1737    4181144  12. PHPDocsMD\Reflector->getParams(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:161
    0.1737    4182176  13. PHPDocsMD\Reflector->createParameterEntity(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:438
    0.1738    4182176  14. PHPDocsMD\Reflector::getParamType(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:252


Deprecated: Function ReflectionParameter::export() is deprecated in /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php on line 344

Call Stack:
    0.0114     404600   1. {main}() /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:0
    0.0551    2236352   2. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:12
    0.0631    2842184   3. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/CLI.php:29
    0.0813    3094184   4. PHPDocsMD\Console\CLI->doRun(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:166
    0.0833    3107856   5. PHPDocsMD\Console\CLI->doRunCommand(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:290
    0.0833    3107856   6. PHPDocsMD\Console\PHPDocsMDCommand->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:971
    0.0845    3110488   7. PHPDocsMD\Console\PHPDocsMDCommand->execute(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Command/Command.php:255
    0.0928    3932096   8. PHPDocsMD\Console\PHPDocsMDCommand->getClassEntity(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:169
    0.0958    4049448   9. PHPDocsMD\Reflector->getClassEntity() /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:57
    0.0970    4069352  10. PHPDocsMD\Reflector->getClassFunctions(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:83
    0.1729    4177568  11. PHPDocsMD\Reflector->createFunctionEntity(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:118
    0.1737    4181144  12. PHPDocsMD\Reflector->getParams(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:161
    0.1743    4183152  13. PHPDocsMD\Reflector->createParameterEntity(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:438
    0.1743    4183152  14. PHPDocsMD\Reflector::getParamType(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:252


Deprecated: Function ReflectionParameter::export() is deprecated in /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php on line 344

Call Stack:
    0.0114     404600   1. {main}() /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:0
    0.0551    2236352   2. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:12
    0.0631    2842184   3. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/CLI.php:29
    0.0813    3094184   4. PHPDocsMD\Console\CLI->doRun(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:166
    0.0833    3107856   5. PHPDocsMD\Console\CLI->doRunCommand(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:290
    0.0833    3107856   6. PHPDocsMD\Console\PHPDocsMDCommand->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:971
    0.0845    3110488   7. PHPDocsMD\Console\PHPDocsMDCommand->execute(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Command/Command.php:255
    0.0928    3932096   8. PHPDocsMD\Console\PHPDocsMDCommand->getClassEntity(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:169
    0.0958    4049448   9. PHPDocsMD\Reflector->getClassEntity() /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:57
    0.0970    4069352  10. PHPDocsMD\Reflector->getClassFunctions(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:83
    0.1749    4179880  11. PHPDocsMD\Reflector->createFunctionEntity(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:118
    0.1759    4184232  12. PHPDocsMD\Reflector->getParams(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:161
    0.1759    4185376  13. PHPDocsMD\Reflector->createParameterEntity(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:438
    0.1760    4185376  14. PHPDocsMD\Reflector::getParamType(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:252


Deprecated: Function ReflectionParameter::export() is deprecated in /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php on line 344

Call Stack:
    0.0114     404600   1. {main}() /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:0
    0.0551    2236352   2. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:12
    0.0631    2842184   3. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/CLI.php:29
    0.0813    3094184   4. PHPDocsMD\Console\CLI->doRun(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:166
    0.0833    3107856   5. PHPDocsMD\Console\CLI->doRunCommand(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:290
    0.0833    3107856   6. PHPDocsMD\Console\PHPDocsMDCommand->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:971
    0.0845    3110488   7. PHPDocsMD\Console\PHPDocsMDCommand->execute(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Command/Command.php:255
    0.0928    3932096   8. PHPDocsMD\Console\PHPDocsMDCommand->getClassEntity(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:169
    0.0958    4049448   9. PHPDocsMD\Reflector->getClassEntity() /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:57
    0.0970    4069352  10. PHPDocsMD\Reflector->getClassFunctions(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:83
    0.1749    4179880  11. PHPDocsMD\Reflector->createFunctionEntity(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:118
    0.1759    4184232  12. PHPDocsMD\Reflector->getParams(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:161
    0.1766    4186448  13. PHPDocsMD\Reflector->createParameterEntity(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:438
    0.1766    4186448  14. PHPDocsMD\Reflector::getParamType(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:252


Deprecated: Function ReflectionParameter::export() is deprecated in /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php on line 344

Call Stack:
    0.0114     404600   1. {main}() /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:0
    0.0551    2236352   2. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:12
    0.0631    2842184   3. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/CLI.php:29
    0.0813    3094184   4. PHPDocsMD\Console\CLI->doRun(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:166
    0.0833    3107856   5. PHPDocsMD\Console\CLI->doRunCommand(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:290
    0.0833    3107856   6. PHPDocsMD\Console\PHPDocsMDCommand->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:971
    0.0845    3110488   7. PHPDocsMD\Console\PHPDocsMDCommand->execute(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Command/Command.php:255
    0.0928    3932096   8. PHPDocsMD\Console\PHPDocsMDCommand->getClassEntity(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:169
    0.0958    4049448   9. PHPDocsMD\Reflector->getClassEntity() /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:57
    0.0970    4069352  10. PHPDocsMD\Reflector->getClassFunctions(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:83
    0.1749    4179880  11. PHPDocsMD\Reflector->createFunctionEntity(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:118
    0.1759    4184232  12. PHPDocsMD\Reflector->getParams(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:161
    0.1772    4187160  13. PHPDocsMD\Reflector->createParameterEntity(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:438
    0.1772    4187160  14. PHPDocsMD\Reflector::getParamType(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:252


Deprecated: Function ReflectionParameter::export() is deprecated in /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php on line 344

Call Stack:
    0.0114     404600   1. {main}() /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:0
    0.0551    2236352   2. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:12
    0.0631    2842184   3. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/CLI.php:29
    0.0813    3094184   4. PHPDocsMD\Console\CLI->doRun(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:166
    0.0833    3107856   5. PHPDocsMD\Console\CLI->doRunCommand(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:290
    0.0833    3107856   6. PHPDocsMD\Console\PHPDocsMDCommand->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:971
    0.0845    3110488   7. PHPDocsMD\Console\PHPDocsMDCommand->execute(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Command/Command.php:255
    0.0928    3932096   8. PHPDocsMD\Console\PHPDocsMDCommand->getClassEntity(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:169
    0.0958    4049448   9. PHPDocsMD\Reflector->getClassEntity() /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:57
    0.0970    4069352  10. PHPDocsMD\Reflector->getClassFunctions(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:83
    0.1784    4184144  11. PHPDocsMD\Reflector->createFunctionEntity(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:118
    0.1794    4188536  12. PHPDocsMD\Reflector->getParams(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:161
    0.1795    4189680  13. PHPDocsMD\Reflector->createParameterEntity(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:438
    0.1795    4189680  14. PHPDocsMD\Reflector::getParamType(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:252


Deprecated: Function ReflectionParameter::export() is deprecated in /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php on line 344

Call Stack:
    0.0114     404600   1. {main}() /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:0
    0.0551    2236352   2. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:12
    0.0631    2842184   3. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/CLI.php:29
    0.0813    3094184   4. PHPDocsMD\Console\CLI->doRun(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:166
    0.0833    3107856   5. PHPDocsMD\Console\CLI->doRunCommand(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:290
    0.0833    3107856   6. PHPDocsMD\Console\PHPDocsMDCommand->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:971
    0.0845    3110488   7. PHPDocsMD\Console\PHPDocsMDCommand->execute(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Command/Command.php:255
    0.0928    3932096   8. PHPDocsMD\Console\PHPDocsMDCommand->getClassEntity(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:169
    0.0958    4049448   9. PHPDocsMD\Reflector->getClassEntity() /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:57
    0.0970    4069352  10. PHPDocsMD\Reflector->getClassFunctions(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:83
    0.1784    4184144  11. PHPDocsMD\Reflector->createFunctionEntity(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:118
    0.1794    4188536  12. PHPDocsMD\Reflector->getParams(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:161
    0.1800    4190744  13. PHPDocsMD\Reflector->createParameterEntity(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:438
    0.1801    4190744  14. PHPDocsMD\Reflector::getParamType(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:252


Deprecated: Function ReflectionParameter::export() is deprecated in /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php on line 344

Call Stack:
    0.0114     404600   1. {main}() /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:0
    0.0551    2236352   2. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:12
    0.0631    2842184   3. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/CLI.php:29
    0.0813    3094184   4. PHPDocsMD\Console\CLI->doRun(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:166
    0.0833    3107856   5. PHPDocsMD\Console\CLI->doRunCommand(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:290
    0.0833    3107856   6. PHPDocsMD\Console\PHPDocsMDCommand->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:971
    0.0845    3110488   7. PHPDocsMD\Console\PHPDocsMDCommand->execute(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Command/Command.php:255
    0.0928    3932096   8. PHPDocsMD\Console\PHPDocsMDCommand->getClassEntity(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:169
    0.0958    4049448   9. PHPDocsMD\Reflector->getClassEntity() /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:57
    0.0970    4069352  10. PHPDocsMD\Reflector->getClassFunctions(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:83
    0.1784    4184144  11. PHPDocsMD\Reflector->createFunctionEntity(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:118
    0.1794    4188536  12. PHPDocsMD\Reflector->getParams(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:161
    0.1807    4191376  13. PHPDocsMD\Reflector->createParameterEntity(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:438
    0.1807    4191376  14. PHPDocsMD\Reflector::getParamType(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:252


Deprecated: Function ReflectionParameter::export() is deprecated in /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php on line 344

Call Stack:
    0.0114     404600   1. {main}() /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:0
    0.0551    2236352   2. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:12
    0.0631    2842184   3. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/CLI.php:29
    0.0813    3094184   4. PHPDocsMD\Console\CLI->doRun(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:166
    0.0833    3107856   5. PHPDocsMD\Console\CLI->doRunCommand(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:290
    0.0833    3107856   6. PHPDocsMD\Console\PHPDocsMDCommand->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:971
    0.0845    3110488   7. PHPDocsMD\Console\PHPDocsMDCommand->execute(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Command/Command.php:255
    0.0928    3932096   8. PHPDocsMD\Console\PHPDocsMDCommand->getClassEntity(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:169
    0.0958    4049448   9. PHPDocsMD\Reflector->getClassEntity() /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:57
    0.0970    4069352  10. PHPDocsMD\Reflector->getClassFunctions(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:83
    0.1815    4187240  11. PHPDocsMD\Reflector->createFunctionEntity(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:118
    0.1821    4189992  12. PHPDocsMD\Reflector->getParams(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:161
    0.1821    4190896  13. PHPDocsMD\Reflector->createParameterEntity(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:438
    0.1822    4190896  14. PHPDocsMD\Reflector::getParamType(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:252



## Public API
<nav>
	<ul>
		<li>
			<a href="#aminmupluginpath">amInMuPluginPath</a>
		</li>
		<li>
			<a href="#aminpluginpath">amInPluginPath</a>
		</li>
		<li>
			<a href="#aminthemepath">amInThemePath</a>
		</li>
		<li>
			<a href="#aminuploadspath">amInUploadsPath</a>
		</li>
		<li>
			<a href="#cleanmuplugindir">cleanMuPluginDir</a>
		</li>
		<li>
			<a href="#cleanplugindir">cleanPluginDir</a>
		</li>
		<li>
			<a href="#cleanthemedir">cleanThemeDir</a>
		</li>
		<li>
			<a href="#cleanuploadsdir">cleanUploadsDir</a>
		</li>
		<li>
			<a href="#copydirtomuplugin">copyDirToMuPlugin</a>
		</li>
		<li>
			<a href="#copydirtoplugin">copyDirToPlugin</a>
		</li>
		<li>
			<a href="#copydirtotheme">copyDirToTheme</a>
		</li>
		<li>
			<a href="#copydirtouploads">copyDirToUploads</a>
		</li>
		<li>
			<a href="#deletemupluginfile">deleteMuPluginFile</a>
		</li>
		<li>
			<a href="#deletepluginfile">deletePluginFile</a>
		</li>
		<li>
			<a href="#deletethemefile">deleteThemeFile</a>
		</li>
		<li>
			<a href="#deleteuploadeddir">deleteUploadedDir</a>
		</li>
		<li>
			<a href="#deleteuploadedfile">deleteUploadedFile</a>
		</li>
		<li>
			<a href="#dontseeinmupluginfile">dontSeeInMuPluginFile</a>
		</li>
		<li>
			<a href="#dontseeinpluginfile">dontSeeInPluginFile</a>
		</li>
		<li>
			<a href="#dontseeinthemefile">dontSeeInThemeFile</a>
		</li>
		<li>
			<a href="#dontseeinuploadedfile">dontSeeInUploadedFile</a>
		</li>
		<li>
			<a href="#dontseemupluginfilefound">dontSeeMuPluginFileFound</a>
		</li>
		<li>
			<a href="#dontseepluginfilefound">dontSeePluginFileFound</a>
		</li>
		<li>
			<a href="#dontseethemefilefound">dontSeeThemeFileFound</a>
		</li>
		<li>
			<a href="#dontseeuploadedfilefound">dontSeeUploadedFileFound</a>
		</li>
		<li>
			<a href="#getbloguploadspath">getBlogUploadsPath</a>
		</li>
		<li>
			<a href="#getuploadspath">getUploadsPath</a>
		</li>
		<li>
			<a href="#getwprootfolder">getWpRootFolder</a>
		</li>
		<li>
			<a href="#havemuplugin">haveMuPlugin</a>
		</li>
		<li>
			<a href="#haveplugin">havePlugin</a>
		</li>
		<li>
			<a href="#havetheme">haveTheme</a>
		</li>
		<li>
			<a href="#makeuploadsdir">makeUploadsDir</a>
		</li>
		<li>
			<a href="#openuploadedfile">openUploadedFile</a>
		</li>
		<li>
			<a href="#seeinmupluginfile">seeInMuPluginFile</a>
		</li>
		<li>
			<a href="#seeinpluginfile">seeInPluginFile</a>
		</li>
		<li>
			<a href="#seeinthemefile">seeInThemeFile</a>
		</li>
		<li>
			<a href="#seeinuploadedfile">seeInUploadedFile</a>
		</li>
		<li>
			<a href="#seemupluginfilefound">seeMuPluginFileFound</a>
		</li>
		<li>
			<a href="#seepluginfilefound">seePluginFileFound</a>
		</li>
		<li>
			<a href="#seethemefilefound">seeThemeFileFound</a>
		</li>
		<li>
			<a href="#seeuploadedfilefound">seeUploadedFileFound</a>
		</li>
		<li>
			<a href="#writetomupluginfile">writeToMuPluginFile</a>
		</li>
		<li>
			<a href="#writetopluginfile">writeToPluginFile</a>
		</li>
		<li>
			<a href="#writetothemefile">writeToThemeFile</a>
		</li>
		<li>
			<a href="#writetouploadedfile">writeToUploadedFile</a>
		</li>
	</ul>
</nav>

<h3>amInMuPluginPath</h3>

<hr>

<p>Sets the current working folder to a folder in a mu-plugin.</p>
```php
$I->amInMuPluginPath('mu-plugin');
```

<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$path</strong> - The path to the folder, relative to the mu-plugins root folder.</li></ul>
  

<h3>amInPluginPath</h3>

<hr>

<p>Sets the current working folder to a folder in a plugin.</p>
```php
$I->amInPluginPath('my-plugin');
```

<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$path</strong> - The folder path, relative to the root uploads folder, to change to.</li></ul>
  

<h3>amInThemePath</h3>

<hr>

<p>Sets the current working folder to a folder in a theme.</p>
```php
$I->amInThemePath('my-theme');
```

<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$path</strong> - The path to the theme folder, relative to themes root folder.</li></ul>
  

<h3>amInUploadsPath</h3>

<hr>

<p>Enters, changing directory, to the uploads folder in the local filesystem.</p>
```php
$I->amInUploadsPath('/logs');
  $I->seeFileFound('shop.log');
```

<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$path</strong> - The path, relative to the site uploads folder.</li></ul>
  

<h3>cleanMuPluginDir</h3>

<hr>

<p>Cleans, emptying it, a folder in a mu-plugin folder.</p>
```php
$I->cleanMuPluginDir('mu-plugin1/foo');
```

<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$dir</strong> - The path to the directory, relative to the mu-plugins root folder.</li></ul>
  

<h3>cleanPluginDir</h3>

<hr>

<p>Cleans, emptying it, a folder in a plugin folder.</p>
```php
$I->cleanPluginDir('my-plugin/foo');
```

<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$dir</strong> - The path to the folder, relative to the plugins root folder.</li></ul>
  

<h3>cleanThemeDir</h3>

<hr>

<p>Clears, emptying it, a folder in a theme folder.</p>
```php
$I->cleanThemeDir('my-theme/foo');
```

<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$dir</strong> - The path to the folder, relative to the themese root folder.</li></ul>
  

<h3>cleanUploadsDir</h3>

<hr>

<p>Clears a folder in the uploads folder. The date argument can be a string compatible with <code>strtotime</code> or a Unix timestamp that will be used to build the <code>Y/m</code> uploads subfolder path.</p>
```php
$I->cleanUploadsDir('some/folder');
  $I->cleanUploadsDir('some/folder', 'today');
```

<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$dir</strong> - The path to the directory to delete, relative to the uploads folder.</li>
<li><code>string/int/[\DateTime](http://php.net/manual/en/class.datetime.php)</code> <strong>$date</strong> - The date of the uploads to delete, will default to <code>now</code>.</li></ul>
  

<h3>copyDirToMuPlugin</h3>

<hr>

<p>Copies a folder to a folder in a mu-plugin.</p>
```php
$I->copyDirToMuPlugin(codecept_data_dir('foo'), 'mu-plugin/foo');
```

<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$src</strong> - The path to the source file to copy.</li>
<li><code>string</code> <strong>$pluginDst</strong> - The path to the destination folder, relative to the mu-plugins root folder.</li></ul>
  

<h3>copyDirToPlugin</h3>

<hr>

<p>Copies a folder to a folder in a plugin.</p>
```php
// Copy the 'foo' folder to the 'foo' folder in the plugin.
  $I->copyDirToPlugin(codecept_data_dir('foo'), 'my-plugin/foo');
```

<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$src</strong> - The path to the source directory to copy.</li>
<li><code>string</code> <strong>$pluginDst</strong> - The destination path, relative to the plugins root folder.</li></ul>
  

<h3>copyDirToTheme</h3>

<hr>

<p>Copies a folder in a theme folder.</p>
```php
$I->copyDirToTheme(codecept_data_dir('foo'), 'my-theme');
```

<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$src</strong> - The path to the source file.</li>
<li><code>string</code> <strong>$themeDst</strong> - The path to the destination folder, relative to the themes root folder.</li></ul>
  

<h3>copyDirToUploads</h3>

<hr>

<p>Copies a folder to the uploads folder. The date argument can be a string compatible with <code>strtotime</code> or a Unix timestamp that will be used to build the <code>Y/m</code> uploads subfolder path.</p>
```php
$I->copyDirToUploads(codecept_data_dir('foo'), 'uploadsFoo');
  $I->copyDirToUploads(codecept_data_dir('foo'), 'uploadsFoo', 'today');
```

<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$src</strong> - The path to the source file, relative to the current uploads folder.</li>
<li><code>string</code> <strong>$dst</strong> - The path to the destination file, relative to the current uploads folder.</li>
<li><code>string/int/[\DateTime](http://php.net/manual/en/class.datetime.php)</code> <strong>$date</strong> - The date of the uploads to delete, will default to <code>now</code>.</li></ul>
  

<h3>deleteMuPluginFile</h3>

<hr>

<p>Deletes a file in a mu-plugin folder.</p>
```php
$I->deleteMuPluginFile('mu-plugin1/some-file.txt');
```

<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$file</strong> - The path to the file, relative to the mu-plugins root folder.</li></ul>
  

<h3>deletePluginFile</h3>

<hr>

<p>Deletes a file in a plugin folder.</p>
```php
$I->deletePluginFile('my-plugin/some-file.txt');
```

<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$file</strong> - The folder path, relative to the plugins root folder.</li></ul>
  

<h3>deleteThemeFile</h3>

<hr>

<p>Deletes a file in a theme folder.</p>
```php
$I->deleteThemeFile('my-theme/some-file.txt');
```

<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$file</strong> - The path to the file to delete, relative to the themes root folder.</li></ul>
  

<h3>deleteUploadedDir</h3>

<hr>

<p>Deletes a dir in the uploads folder. The date argument can be a string compatible with <code>strtotime</code> or a Unix timestamp that will be used to build the <code>Y/m</code> uploads subfolder path.</p>
```php
$I->deleteUploadedDir('folder');
  $I->deleteUploadedDir('folder', 'today');
```

<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$dir</strong> - The path to the directory to delete, relative to the uploads folder.</li>
<li><code>string/int/[\DateTime](http://php.net/manual/en/class.datetime.php)</code> <strong>$date</strong> - The date of the uploads to delete, will default to <code>now</code>.</li></ul>
  

<h3>deleteUploadedFile</h3>

<hr>

<p>Deletes a file in the uploads folder. The date argument can be a string compatible with <code>strtotime</code> or a Unix timestamp that will be used to build the <code>Y/m</code> uploads subfolder path.</p>
```php
$I->deleteUploadedFile('some-file.txt');
  $I->deleteUploadedFile('some-file.txt', 'today');
```

<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$file</strong> - The file path, relative to the uploads folder or the current folder.</li>
<li><code>string/int</code> <strong>$date</strong> - A string compatible with <code>strtotime</code> or a Unix timestamp.</li></ul>
  

<h3>dontSeeInMuPluginFile</h3>

<hr>

<p>Checks that a file in a mu-plugin folder does not contain a string.</p>
```php
$I->dontSeeInMuPluginFile('mu-plugin1/some-file.txt', 'foo');
```

<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$file</strong> - The path to the file, relative to the mu-plugins root folder.</li>
<li><code>string</code> <strong>$contents</strong> - The contents to check the file for.</li></ul>
  

<h3>dontSeeInPluginFile</h3>

<hr>

<p>Checks that a file in a plugin folder does not contain a string.</p>
```php
$I->dontSeeInPluginFile('my-plugin/some-file.txt', 'foo');
```

<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$file</strong> - The path to the file, relative to the plugins root folder.</li>
<li><code>string</code> <strong>$contents</strong> - The contents to check the file for.</li></ul>
  

<h3>dontSeeInThemeFile</h3>

<hr>

<p>Checks that a file in a theme folder does not contain a string.</p>
```php
$I->dontSeeInThemeFile('my-theme/some-file.txt', 'foo');
```

<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$file</strong> - The path to the file, relative to the themes root folder.</li>
<li><code>string</code> <strong>$contents</strong> - The contents to check the file for.</li></ul>
  

<h3>dontSeeInUploadedFile</h3>

<hr>

<p>Checks that a file in the uploads folder does contain a string. The date argument can be a string compatible with <code>strtotime</code> or a Unix timestamp that will be used to build the <code>Y/m</code> uploads subfolder path.</p>
```php
$I->dontSeeInUploadedFile('some-file.txt', 'foo');
  $I->dontSeeInUploadedFile('some-file.txt','foo', 'today');
```

<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$file</strong> - The file path, relative to the uploads folder or the current folder.</li>
<li><code>string</code> <strong>$contents</strong> - The not expected file contents or part of them.</li>
<li><code>string/int</code> <strong>$date</strong> - A string compatible with <code>strtotime</code> or a Unix timestamp.</li></ul>
  

<h3>dontSeeMuPluginFileFound</h3>

<hr>

<p>Checks that a file is not found in a mu-plugin folder.</p>
```php
$I->dontSeeMuPluginFileFound('mu-plugin1/some-file.txt');
```

<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$file</strong> - The path to the file, relative to the mu-plugins folder.</li></ul>
  

<h3>dontSeePluginFileFound</h3>

<hr>

<p>Checks that a file is not found in a plugin folder.</p>
```php
$I->dontSeePluginFileFound('my-plugin/some-file.txt');
```

<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$file</strong> - The path to the file, relative to the plugins root folder.</li></ul>
  

<h3>dontSeeThemeFileFound</h3>

<hr>

<p>Checks that a file is not found in a theme folder.</p>
```php
$I->dontSeeThemeFileFound('my-theme/some-file.txt');
```

<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$file</strong> - The path to the file, relative to the themes root folder.</li></ul>
  

<h3>dontSeeUploadedFileFound</h3>

<hr>

<p>Checks thata a file does not exist in the uploads folder. The date argument can be a string compatible with <code>strtotime</code> or a Unix timestamp that will be used to build the <code>Y/m</code> uploads subfolder path.</p>
```php
$I->dontSeeUploadedFileFound('some-file.txt');
  $I->dontSeeUploadedFileFound('some-file.txt','today');
```

<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$file</strong> - The file path, relative to the uploads folder or the current folder.</li>
<li><code>string/int</code> <strong>$date</strong> - A string compatible with <code>strtotime</code> or a Unix timestamp.</li></ul>
  

<h3>getBlogUploadsPath</h3>

<hr>

<p>Returns the absolute path to a blog uploads folder or file.</p>
```php
$blogId = $I->haveBlogInDatabase('test');
  $testTodayUploads = $I->getBlogUploadsPath($blogId);
  $testLastMonthLogs = $I->getBlogUploadsPath($blogId, '/logs', '-1 month');
  file or folder.
  sub-folders in the year/month format; a UNIX timestamp or
  a string supported by the `strtotime` function; defaults
  to `now`.
```

<h4>Parameters</h4>
<ul>
<li><code>int</code> <strong>$blogId</strong> - The blog ID to get the path for.</li>
<li><code>string</code> <strong>$file</strong> - The path, relatitve to the blog uploads folder, to the</li>
<li><code>null/string/[\DateTime](http://php.net/manual/en/class.datetime.php)/[\DateTime](http://php.net/manual/en/class.datetime.php)Immutable</code> <strong>$date</strong> - The date that should be used to build the uploads</li></ul>
  

<h3>getUploadsPath</h3>

<hr>

<p>Returns the path to the specified uploads file of folder. Not providing a value for <code>$file</code> and <code>$date</code> will return the uploads folder path.</p>
```php
$todaysPath = $I->getUploadsPath();
  $lastWeek = $I->getUploadsPath('', '-1 week');
```

<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$file</strong> - The file path, relative to the uploads folder.</li>
<li><code>mixed</code> <strong>$date</strong> - A string compatible with <code>strtotime</code>, a Unix timestamp or a Date object.</li></ul>
  

<h3>getWpRootFolder</h3>

<hr>

<p>Returns the absolute path to WordPress root folder without trailing slash.</p>
```php
$rootFolder = $I->getWpRootFolder();
  $I->assertFileExists($rootFolder . 'wp-load.php');
```

  

<h3>haveMuPlugin</h3>

<hr>

<p>Creates a mu-plugin file, including plugin header, in the mu-plugins folder. The code can not contain the opening '&lt;?php' tag.</p>
```php
$code = 'echo "Hello world!"';
  $I->haveMuPlugin('foo-mu-plugin.php', $code);
  // Load the code from a file.
  $code = file_get_contents(codecept_data_dir('code/mu-plugin.php'));
  $I->haveMuPlugin('foo-mu-plugin.php', $code);
```

<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$filename</strong> - The path to the file to create, relative to the plugins root folder.</li>
<li><code>string</code> <strong>$code</strong> - The content of the plugin file with or without the opening PHP tag.</li></ul>
  

<h3>havePlugin</h3>

<hr>

<p>Creates a plugin file, including plugin header, in the plugins folder. The plugin is just created and not activated; the code can not contain the opening '&lt;?php' tag.</p>
```php
$code = 'echo "Hello world!"';
  $I->havePlugin('foo/plugin.php', $code);
  // Load the code from a file.
  $code = file_get_contents(codecept_data_dir('code/plugin.php'));
  $I->havePlugin('foo/plugin.php', $code);
```

<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$path</strong> - The path to the file to create, relative to the plugins folder.</li>
<li><code>string</code> <strong>$code</strong> - The content of the plugin file with or without the opening PHP tag.</li></ul>
  

<h3>haveTheme</h3>

<hr>

<p>Creates a theme file structure, including theme style file and index, in the themes folder. The theme is just created and not activated; the code can not contain the opening '&lt;?php' tag.</p>
```php
$code = 'sayHi();';
  $functionsCode  = 'function sayHi(){echo "Hello world";};';
  $I->haveTheme('foo', $indexCode, $functionsCode);
  // Load the code from a file.
  $indexCode = file_get_contents(codecept_data_dir('code/index.php'));
  $functionsCode = file_get_contents(codecept_data_dir('code/functions.php'));
  $I->haveTheme('foo', $indexCode, $functionsCode);
```

<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$folder</strong> - The path to the theme to create, relative to the themes root folder.</li>
<li><code>string</code> <strong>$indexFileCode</strong> - The content of the theme index.php file with or without the opening PHP tag.</li>
<li><code>string</code> <strong>$functionsFileCode</strong> - The content of the theme functions.php file with or without the opening PHP tag.</li></ul>
  

<h3>makeUploadsDir</h3>

<hr>

<p>Creates an empty folder in the WordPress installation uploads folder.</p>
```php
$logsDir = $I->makeUploadsDir('logs/acme');
  to create.
```

<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$path</strong> - The path, relative to the WordPress installation uploads folder, of the folder</li></ul>
  

<h3>openUploadedFile</h3>

<hr>

<p>Opens a file in the the uploads folder. The date argument can be a string compatible with <code>strtotime</code> or a Unix timestamp that will be used to build the <code>Y/m</code> uploads subfolder path.</p>
```php
$I->openUploadedFile('some-file.txt');
  $I->openUploadedFile('some-file.txt', 'time');
```

<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$filename</strong> - The path to the file, relative to the current uploads folder.</li>
<li><code>string/int/[\DateTime](http://php.net/manual/en/class.datetime.php)</code> <strong>$date</strong> - The date of the uploads to delete, will default to <code>now</code>.</li></ul>
  

<h3>seeInMuPluginFile</h3>

<hr>

<p>Checks that a file in a mu-plugin folder contains a string.</p>
```php
$I->seeInMuPluginFile('mu-plugin1/some-file.txt', 'foo');
```

<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$file</strong> - The path the file, relative to the mu-plugins root folder.</li>
<li><code>string</code> <strong>$contents</strong> - The contents to check the file for.</li></ul>
  

<h3>seeInPluginFile</h3>

<hr>

<p>Checks that a file in a plugin folder contains a string.</p>
```php
$I->seeInPluginFile('my-plugin/some-file.txt', 'foo');
```

<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$file</strong> - The path to the file, relative to the plugins root folder.</li>
<li><code>string</code> <strong>$contents</strong> - The contents to check the file for.</li></ul>
  

<h3>seeInThemeFile</h3>

<hr>

<p>Checks that a file in a theme folder contains a string.</p>
```php
<?php
  $I->seeInThemeFile('my-theme/some-file.txt', 'foo');
  ?>
```

<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$file</strong> - The path to the file, relative to the themes root folder.</li>
<li><code>string</code> <strong>$contents</strong> - The contents to check the file for.</li></ul>
  

<h3>seeInUploadedFile</h3>

<hr>

<p>Checks that a file in the uploads folder contains a string. The date argument can be a string compatible with <code>strtotime</code> or a Unix timestamp that will be used to build the <code>Y/m</code> uploads subfolder path.</p>
```php
$I->seeInUploadedFile('some-file.txt', 'foo');
  $I->seeInUploadedFile('some-file.txt','foo', 'today');
```

<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$file</strong> - The file path, relative to the uploads folder or the current folder.</li>
<li><code>string</code> <strong>$contents</strong> - The expected file contents or part of them.</li>
<li><code>string/int</code> <strong>$date</strong> - A string compatible with <code>strtotime</code> or a Unix timestamp.</li></ul>
  

<h3>seeMuPluginFileFound</h3>

<hr>

<p>Checks that a file is found in a mu-plugin folder.</p>
```php
$I->seeMuPluginFileFound('mu-plugin1/some-file.txt');
```

<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$file</strong> - The path to the file, relative to the mu-plugins folder.</li></ul>
  

<h3>seePluginFileFound</h3>

<hr>

<p>Checks that a file is found in a plugin folder.</p>
```php
$I->seePluginFileFound('my-plugin/some-file.txt');
```

<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$file</strong> - The path to the file, relative to thep plugins root folder.</li></ul>
  

<h3>seeThemeFileFound</h3>

<hr>

<p>Checks that a file is found in a theme folder.</p>
```php
$I->seeThemeFileFound('my-theme/some-file.txt');
```

<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$file</strong> - The path to the file, relative to the themes root folder.</li></ul>
  

<h3>seeUploadedFileFound</h3>

<hr>

<p>Checks if file exists in the uploads folder. The date argument can be a string compatible with <code>strtotime</code> or a Unix timestamp that will be used to build the <code>Y/m</code> uploads subfolder path.</p>
```php
$I->seeUploadedFileFound('some-file.txt');
  $I->seeUploadedFileFound('some-file.txt','today');
  ?>
```

<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$filename</strong> - The file path, relative to the uploads folder or the current folder.</li>
<li><code>string/int</code> <strong>$date</strong> - A string compatible with <code>strtotime</code> or a Unix timestamp.</li></ul>
  

<h3>writeToMuPluginFile</h3>

<hr>

<p>Writes a file in a mu-plugin folder.</p>
```php
$I->writeToMuPluginFile('mu-plugin1/some-file.txt', 'foo');
```

<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$file</strong> - The path to the destination file, relative to the mu-plugins root folder.</li>
<li><code>string</code> <strong>$data</strong> - The data to write to the file.</li></ul>
  

<h3>writeToPluginFile</h3>

<hr>

<p>Writes a file in a plugin folder.</p>
```php
$I->writeToPluginFile('my-plugin/some-file.txt', 'foo');
```

<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$file</strong> - The path to the file, relative to the plugins root folder.</li>
<li><code>string</code> <strong>$data</strong> - The data to write in the file.</li></ul>
  

<h3>writeToThemeFile</h3>

<hr>

<p>Writes a string to a file in a theme folder.</p>
```php
$I->writeToThemeFile('my-theme/some-file.txt', 'foo');
```

<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$file</strong> - The path to the file, relative to the themese root folder.</li>
<li><code>string</code> <strong>$data</strong> - The data to write to the file.</li></ul>
  

<h3>writeToUploadedFile</h3>

<hr>

<p>Writes a string to a file in the the uploads folder. The date argument can be a string compatible with <code>strtotime</code> or a Unix timestamp that will be used to build the <code>Y/m</code> uploads subfolder path.</p>
```php
$I->writeToUploadedFile('some-file.txt', 'foo bar');
  $I->writeToUploadedFile('some-file.txt', 'foo bar', 'today');
```

<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$filename</strong> - The path to the destination file, relative to the current uploads folder.</li>
<li><code>string</code> <strong>$data</strong> - The data to write to the file.</li>
<li><code>string/int/[\DateTime](http://php.net/manual/en/class.datetime.php)</code> <strong>$date</strong> - The date of the uploads to delete, will default to <code>now</code>.</li></ul>


*This class extends \Codeception\Module\Filesystem*

<!--/doc-->

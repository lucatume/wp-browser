# WPQueries module
This module should be used in integration tests, see [levels of testing for more information](./../levels-of-testing.md), to make assertions on the database queries made by the global `$wpdb` object.  
This module **requires** the [WPLoader module](/WPLoader.md) to work.  
The module will set, if not set already, the `SAVEQUERIES` constant to `true` and will throw an exception if the contstant is already set to a falsy value.  

## Configuration
This module does not require any configuration, but requires the [WPLoader module](WPLoader.md) to work correctly. 

## Usage
This module must be used in a test case extending the `\Codeception\TestCase\WPTestCase` class.  

The module public API is accessible calling via the `\Codeception\TestCase\WPTestCase::queries()` method:

```php
<?php

use Codeception\Module\WPQueries;

class WPQueriesUsageTest extends \Codeception\TestCase\WPTestCase
{
    public function test_queries_made_by_factory_are_not_tracked()
    {
        $currentQueriesCount = $this->queries()->countQueries();

        $this->assertNotEmpty($currentQueriesCount);

        static::factory()->post->create_many(3);

        $this->assertNotEmpty($currentQueriesCount);
        $this->assertEquals($currentQueriesCount, $this->queries()->countQueries());
    }

    public function test_count_queries()
    {
        $currentQueriesCount = $this->queries()->countQueries();

        $this->assertNotEmpty($currentQueriesCount);

        foreach (range(1, 3) as $i) {
            wp_insert_post(['post_title' => 'Post ' . $i, 'post_content' => str_repeat('test', $i)]);
        }

        $this->assertNotEmpty($currentQueriesCount);
        $this->assertGreaterThan($currentQueriesCount, $this->queries()->countQueries());
    }
}
```
<!--doc-->

Deprecated: Function ReflectionParameter::export() is deprecated in /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php on line 344

Call Stack:
    0.0117     404600   1. {main}() /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:0
    0.0523    2236352   2. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:12
    0.0606    2842184   3. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/CLI.php:29
    0.0783    3094184   4. PHPDocsMD\Console\CLI->doRun(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:166
    0.0802    3107856   5. PHPDocsMD\Console\CLI->doRunCommand(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:290
    0.0803    3107856   6. PHPDocsMD\Console\PHPDocsMDCommand->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:971
    0.0815    3110488   7. PHPDocsMD\Console\PHPDocsMDCommand->execute(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Command/Command.php:255
    0.0878    3743728   8. PHPDocsMD\Console\PHPDocsMDCommand->getClassEntity(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:169
    0.0909    3926688   9. PHPDocsMD\Reflector->getClassEntity() /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:57
    0.0923    3946568  10. PHPDocsMD\Reflector->getClassFunctions(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:83
    0.0931    3953584  11. PHPDocsMD\Reflector->createFunctionEntity(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:118
    0.0942    3966344  12. PHPDocsMD\Reflector->getParams(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:161
    0.0943    3967256  13. PHPDocsMD\Reflector->createParameterEntity(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:438
    0.0943    3967256  14. PHPDocsMD\Reflector::getParamType(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:252


Deprecated: Function ReflectionParameter::export() is deprecated in /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php on line 344

Call Stack:
    0.0117     404600   1. {main}() /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:0
    0.0523    2236352   2. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:12
    0.0606    2842184   3. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/CLI.php:29
    0.0783    3094184   4. PHPDocsMD\Console\CLI->doRun(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:166
    0.0802    3107856   5. PHPDocsMD\Console\CLI->doRunCommand(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:290
    0.0803    3107856   6. PHPDocsMD\Console\PHPDocsMDCommand->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:971
    0.0815    3110488   7. PHPDocsMD\Console\PHPDocsMDCommand->execute(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Command/Command.php:255
    0.0878    3743728   8. PHPDocsMD\Console\PHPDocsMDCommand->getClassEntity(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:169
    0.0909    3926688   9. PHPDocsMD\Reflector->getClassEntity() /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:57
    0.0923    3946568  10. PHPDocsMD\Reflector->getClassFunctions(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:83
    0.0958    3970560  11. PHPDocsMD\Reflector->createFunctionEntity(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:118
    0.0965    3973424  12. PHPDocsMD\Reflector->getParams(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:161
    0.0965    3974336  13. PHPDocsMD\Reflector->createParameterEntity(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:438
    0.0966    3974336  14. PHPDocsMD\Reflector::getParamType(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:252


Deprecated: Function ReflectionParameter::export() is deprecated in /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php on line 344

Call Stack:
    0.0117     404600   1. {main}() /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:0
    0.0523    2236352   2. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:12
    0.0606    2842184   3. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/CLI.php:29
    0.0783    3094184   4. PHPDocsMD\Console\CLI->doRun(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:166
    0.0802    3107856   5. PHPDocsMD\Console\CLI->doRunCommand(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:290
    0.0803    3107856   6. PHPDocsMD\Console\PHPDocsMDCommand->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:971
    0.0815    3110488   7. PHPDocsMD\Console\PHPDocsMDCommand->execute(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Command/Command.php:255
    0.0878    3743728   8. PHPDocsMD\Console\PHPDocsMDCommand->getClassEntity(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:169
    0.0909    3926688   9. PHPDocsMD\Reflector->getClassEntity() /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:57
    0.0923    3946568  10. PHPDocsMD\Reflector->getClassFunctions(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:83
    0.0984    3972520  11. PHPDocsMD\Reflector->createFunctionEntity(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:118
    0.0992    3976112  12. PHPDocsMD\Reflector->getParams(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:161
    0.0993    3977136  13. PHPDocsMD\Reflector->createParameterEntity(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:438
    0.0993    3977136  14. PHPDocsMD\Reflector::getParamType(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:252


Deprecated: Function ReflectionParameter::export() is deprecated in /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php on line 344

Call Stack:
    0.0117     404600   1. {main}() /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:0
    0.0523    2236352   2. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:12
    0.0606    2842184   3. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/CLI.php:29
    0.0783    3094184   4. PHPDocsMD\Console\CLI->doRun(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:166
    0.0802    3107856   5. PHPDocsMD\Console\CLI->doRunCommand(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:290
    0.0803    3107856   6. PHPDocsMD\Console\PHPDocsMDCommand->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:971
    0.0815    3110488   7. PHPDocsMD\Console\PHPDocsMDCommand->execute(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Command/Command.php:255
    0.0878    3743728   8. PHPDocsMD\Console\PHPDocsMDCommand->getClassEntity(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:169
    0.0909    3926688   9. PHPDocsMD\Reflector->getClassEntity() /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:57
    0.0923    3946568  10. PHPDocsMD\Reflector->getClassFunctions(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:83
    0.0984    3972520  11. PHPDocsMD\Reflector->createFunctionEntity(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:118
    0.0992    3976112  12. PHPDocsMD\Reflector->getParams(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:161
    0.0999    3978200  13. PHPDocsMD\Reflector->createParameterEntity(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:438
    0.1000    3978200  14. PHPDocsMD\Reflector::getParamType(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:252


Deprecated: Function ReflectionParameter::export() is deprecated in /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php on line 344

Call Stack:
    0.0117     404600   1. {main}() /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:0
    0.0523    2236352   2. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:12
    0.0606    2842184   3. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/CLI.php:29
    0.0783    3094184   4. PHPDocsMD\Console\CLI->doRun(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:166
    0.0802    3107856   5. PHPDocsMD\Console\CLI->doRunCommand(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:290
    0.0803    3107856   6. PHPDocsMD\Console\PHPDocsMDCommand->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:971
    0.0815    3110488   7. PHPDocsMD\Console\PHPDocsMDCommand->execute(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Command/Command.php:255
    0.0878    3743728   8. PHPDocsMD\Console\PHPDocsMDCommand->getClassEntity(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:169
    0.0909    3926688   9. PHPDocsMD\Reflector->getClassEntity() /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:57
    0.0923    3946568  10. PHPDocsMD\Reflector->getClassFunctions(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:83
    0.1006    3975104  11. PHPDocsMD\Reflector->createFunctionEntity(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:118
    0.1014    3978464  12. PHPDocsMD\Reflector->getParams(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:161
    0.1014    3979496  13. PHPDocsMD\Reflector->createParameterEntity(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:438
    0.1014    3979496  14. PHPDocsMD\Reflector::getParamType(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:252


Deprecated: Function ReflectionParameter::export() is deprecated in /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php on line 344

Call Stack:
    0.0117     404600   1. {main}() /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:0
    0.0523    2236352   2. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:12
    0.0606    2842184   3. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/CLI.php:29
    0.0783    3094184   4. PHPDocsMD\Console\CLI->doRun(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:166
    0.0802    3107856   5. PHPDocsMD\Console\CLI->doRunCommand(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:290
    0.0803    3107856   6. PHPDocsMD\Console\PHPDocsMDCommand->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:971
    0.0815    3110488   7. PHPDocsMD\Console\PHPDocsMDCommand->execute(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Command/Command.php:255
    0.0878    3743728   8. PHPDocsMD\Console\PHPDocsMDCommand->getClassEntity(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:169
    0.0909    3926688   9. PHPDocsMD\Reflector->getClassEntity() /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:57
    0.0923    3946568  10. PHPDocsMD\Reflector->getClassFunctions(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:83
    0.1006    3975104  11. PHPDocsMD\Reflector->createFunctionEntity(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:118
    0.1014    3978464  12. PHPDocsMD\Reflector->getParams(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:161
    0.1021    3980568  13. PHPDocsMD\Reflector->createParameterEntity(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:438
    0.1021    3980568  14. PHPDocsMD\Reflector::getParamType(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:252


Deprecated: Function ReflectionParameter::export() is deprecated in /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php on line 344

Call Stack:
    0.0117     404600   1. {main}() /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:0
    0.0523    2236352   2. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:12
    0.0606    2842184   3. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/CLI.php:29
    0.0783    3094184   4. PHPDocsMD\Console\CLI->doRun(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:166
    0.0802    3107856   5. PHPDocsMD\Console\CLI->doRunCommand(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:290
    0.0803    3107856   6. PHPDocsMD\Console\PHPDocsMDCommand->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:971
    0.0815    3110488   7. PHPDocsMD\Console\PHPDocsMDCommand->execute(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Command/Command.php:255
    0.0878    3743728   8. PHPDocsMD\Console\PHPDocsMDCommand->getClassEntity(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:169
    0.0909    3926688   9. PHPDocsMD\Reflector->getClassEntity() /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:57
    0.0923    3946568  10. PHPDocsMD\Reflector->getClassFunctions(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:83
    0.1028    3977464  11. PHPDocsMD\Reflector->createFunctionEntity(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:118
    0.1037    3981256  12. PHPDocsMD\Reflector->getParams(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:161
    0.1037    3982400  13. PHPDocsMD\Reflector->createParameterEntity(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:438
    0.1037    3982400  14. PHPDocsMD\Reflector::getParamType(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:252


Deprecated: Function ReflectionParameter::export() is deprecated in /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php on line 344

Call Stack:
    0.0117     404600   1. {main}() /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:0
    0.0523    2236352   2. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:12
    0.0606    2842184   3. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/CLI.php:29
    0.0783    3094184   4. PHPDocsMD\Console\CLI->doRun(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:166
    0.0802    3107856   5. PHPDocsMD\Console\CLI->doRunCommand(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:290
    0.0803    3107856   6. PHPDocsMD\Console\PHPDocsMDCommand->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:971
    0.0815    3110488   7. PHPDocsMD\Console\PHPDocsMDCommand->execute(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Command/Command.php:255
    0.0878    3743728   8. PHPDocsMD\Console\PHPDocsMDCommand->getClassEntity(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:169
    0.0909    3926688   9. PHPDocsMD\Reflector->getClassEntity() /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:57
    0.0923    3946568  10. PHPDocsMD\Reflector->getClassFunctions(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:83
    0.1028    3977464  11. PHPDocsMD\Reflector->createFunctionEntity(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:118
    0.1037    3981256  12. PHPDocsMD\Reflector->getParams(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:161
    0.1044    3983464  13. PHPDocsMD\Reflector->createParameterEntity(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:438
    0.1044    3983464  14. PHPDocsMD\Reflector::getParamType(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:252


Deprecated: Function ReflectionParameter::export() is deprecated in /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php on line 344

Call Stack:
    0.0117     404600   1. {main}() /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:0
    0.0523    2236352   2. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:12
    0.0606    2842184   3. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/CLI.php:29
    0.0783    3094184   4. PHPDocsMD\Console\CLI->doRun(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:166
    0.0802    3107856   5. PHPDocsMD\Console\CLI->doRunCommand(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:290
    0.0803    3107856   6. PHPDocsMD\Console\PHPDocsMDCommand->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:971
    0.0815    3110488   7. PHPDocsMD\Console\PHPDocsMDCommand->execute(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Command/Command.php:255
    0.0878    3743728   8. PHPDocsMD\Console\PHPDocsMDCommand->getClassEntity(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:169
    0.0909    3926688   9. PHPDocsMD\Reflector->getClassEntity() /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:57
    0.0923    3946568  10. PHPDocsMD\Reflector->getClassFunctions(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:83
    0.1028    3977464  11. PHPDocsMD\Reflector->createFunctionEntity(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:118
    0.1037    3981256  12. PHPDocsMD\Reflector->getParams(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:161
    0.1049    3984160  13. PHPDocsMD\Reflector->createParameterEntity(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:438
    0.1050    3984160  14. PHPDocsMD\Reflector::getParamType(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:252


Deprecated: Function ReflectionParameter::export() is deprecated in /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php on line 344

Call Stack:
    0.0117     404600   1. {main}() /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:0
    0.0523    2236352   2. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:12
    0.0606    2842184   3. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/CLI.php:29
    0.0783    3094184   4. PHPDocsMD\Console\CLI->doRun(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:166
    0.0802    3107856   5. PHPDocsMD\Console\CLI->doRunCommand(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:290
    0.0803    3107856   6. PHPDocsMD\Console\PHPDocsMDCommand->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:971
    0.0815    3110488   7. PHPDocsMD\Console\PHPDocsMDCommand->execute(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Command/Command.php:255
    0.0878    3743728   8. PHPDocsMD\Console\PHPDocsMDCommand->getClassEntity(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:169
    0.0909    3926688   9. PHPDocsMD\Reflector->getClassEntity() /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:57
    0.0923    3946568  10. PHPDocsMD\Reflector->getClassFunctions(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:83
    0.1056    3980160  11. PHPDocsMD\Reflector->createFunctionEntity(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:118
    0.1063    3983680  12. PHPDocsMD\Reflector->getParams(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:161
    0.1064    3984712  13. PHPDocsMD\Reflector->createParameterEntity(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:438
    0.1064    3984712  14. PHPDocsMD\Reflector::getParamType(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:252


Deprecated: Function ReflectionParameter::export() is deprecated in /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php on line 344

Call Stack:
    0.0117     404600   1. {main}() /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:0
    0.0523    2236352   2. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:12
    0.0606    2842184   3. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/CLI.php:29
    0.0783    3094184   4. PHPDocsMD\Console\CLI->doRun(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:166
    0.0802    3107856   5. PHPDocsMD\Console\CLI->doRunCommand(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:290
    0.0803    3107856   6. PHPDocsMD\Console\PHPDocsMDCommand->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:971
    0.0815    3110488   7. PHPDocsMD\Console\PHPDocsMDCommand->execute(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Command/Command.php:255
    0.0878    3743728   8. PHPDocsMD\Console\PHPDocsMDCommand->getClassEntity(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:169
    0.0909    3926688   9. PHPDocsMD\Reflector->getClassEntity() /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:57
    0.0923    3946568  10. PHPDocsMD\Reflector->getClassFunctions(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:83
    0.1056    3980160  11. PHPDocsMD\Reflector->createFunctionEntity(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:118
    0.1063    3983680  12. PHPDocsMD\Reflector->getParams(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:161
    0.1069    3985696  13. PHPDocsMD\Reflector->createParameterEntity(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:438
    0.1070    3985696  14. PHPDocsMD\Reflector::getParamType(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:252


Deprecated: Function ReflectionParameter::export() is deprecated in /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php on line 344

Call Stack:
    0.0117     404600   1. {main}() /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:0
    0.0523    2236352   2. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:12
    0.0606    2842184   3. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/CLI.php:29
    0.0783    3094184   4. PHPDocsMD\Console\CLI->doRun(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:166
    0.0802    3107856   5. PHPDocsMD\Console\CLI->doRunCommand(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:290
    0.0803    3107856   6. PHPDocsMD\Console\PHPDocsMDCommand->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:971
    0.0815    3110488   7. PHPDocsMD\Console\PHPDocsMDCommand->execute(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Command/Command.php:255
    0.0878    3743728   8. PHPDocsMD\Console\PHPDocsMDCommand->getClassEntity(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:169
    0.0909    3926688   9. PHPDocsMD\Reflector->getClassEntity() /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:57
    0.0923    3946568  10. PHPDocsMD\Reflector->getClassFunctions(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:83
    0.1075    3982592  11. PHPDocsMD\Reflector->createFunctionEntity(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:118
    0.1085    3986872  12. PHPDocsMD\Reflector->getParams(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:161
    0.1085    3988016  13. PHPDocsMD\Reflector->createParameterEntity(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:438
    0.1085    3988016  14. PHPDocsMD\Reflector::getParamType(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:252


Deprecated: Function ReflectionParameter::export() is deprecated in /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php on line 344

Call Stack:
    0.0117     404600   1. {main}() /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:0
    0.0523    2236352   2. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:12
    0.0606    2842184   3. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/CLI.php:29
    0.0783    3094184   4. PHPDocsMD\Console\CLI->doRun(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:166
    0.0802    3107856   5. PHPDocsMD\Console\CLI->doRunCommand(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:290
    0.0803    3107856   6. PHPDocsMD\Console\PHPDocsMDCommand->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:971
    0.0815    3110488   7. PHPDocsMD\Console\PHPDocsMDCommand->execute(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Command/Command.php:255
    0.0878    3743728   8. PHPDocsMD\Console\PHPDocsMDCommand->getClassEntity(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:169
    0.0909    3926688   9. PHPDocsMD\Reflector->getClassEntity() /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:57
    0.0923    3946568  10. PHPDocsMD\Reflector->getClassFunctions(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:83
    0.1075    3982592  11. PHPDocsMD\Reflector->createFunctionEntity(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:118
    0.1085    3986872  12. PHPDocsMD\Reflector->getParams(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:161
    0.1091    3989000  13. PHPDocsMD\Reflector->createParameterEntity(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:438
    0.1091    3989000  14. PHPDocsMD\Reflector::getParamType(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:252


Deprecated: Function ReflectionParameter::export() is deprecated in /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php on line 344

Call Stack:
    0.0117     404600   1. {main}() /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:0
    0.0523    2236352   2. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:12
    0.0606    2842184   3. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/CLI.php:29
    0.0783    3094184   4. PHPDocsMD\Console\CLI->doRun(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:166
    0.0802    3107856   5. PHPDocsMD\Console\CLI->doRunCommand(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:290
    0.0803    3107856   6. PHPDocsMD\Console\PHPDocsMDCommand->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:971
    0.0815    3110488   7. PHPDocsMD\Console\PHPDocsMDCommand->execute(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Command/Command.php:255
    0.0878    3743728   8. PHPDocsMD\Console\PHPDocsMDCommand->getClassEntity(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:169
    0.0909    3926688   9. PHPDocsMD\Reflector->getClassEntity() /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:57
    0.0923    3946568  10. PHPDocsMD\Reflector->getClassFunctions(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:83
    0.1075    3982592  11. PHPDocsMD\Reflector->createFunctionEntity(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:118
    0.1085    3986872  12. PHPDocsMD\Reflector->getParams(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:161
    0.1096    3989608  13. PHPDocsMD\Reflector->createParameterEntity(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:438
    0.1096    3989608  14. PHPDocsMD\Reflector::getParamType(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:252


Deprecated: Function ReflectionParameter::export() is deprecated in /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php on line 344

Call Stack:
    0.0117     404600   1. {main}() /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:0
    0.0523    2236352   2. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:12
    0.0606    2842184   3. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/CLI.php:29
    0.0783    3094184   4. PHPDocsMD\Console\CLI->doRun(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:166
    0.0802    3107856   5. PHPDocsMD\Console\CLI->doRunCommand(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:290
    0.0803    3107856   6. PHPDocsMD\Console\PHPDocsMDCommand->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:971
    0.0815    3110488   7. PHPDocsMD\Console\PHPDocsMDCommand->execute(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Command/Command.php:255
    0.0878    3743728   8. PHPDocsMD\Console\PHPDocsMDCommand->getClassEntity(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:169
    0.0909    3926688   9. PHPDocsMD\Reflector->getClassEntity() /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:57
    0.0923    3946568  10. PHPDocsMD\Reflector->getClassFunctions(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:83
    0.1102    3985600  11. PHPDocsMD\Reflector->createFunctionEntity(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:118
    0.1111    3989392  12. PHPDocsMD\Reflector->getParams(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:161
    0.1111    3990536  13. PHPDocsMD\Reflector->createParameterEntity(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:438
    0.1111    3990536  14. PHPDocsMD\Reflector::getParamType(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:252


Deprecated: Function ReflectionParameter::export() is deprecated in /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php on line 344

Call Stack:
    0.0117     404600   1. {main}() /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:0
    0.0523    2236352   2. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:12
    0.0606    2842184   3. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/CLI.php:29
    0.0783    3094184   4. PHPDocsMD\Console\CLI->doRun(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:166
    0.0802    3107856   5. PHPDocsMD\Console\CLI->doRunCommand(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:290
    0.0803    3107856   6. PHPDocsMD\Console\PHPDocsMDCommand->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:971
    0.0815    3110488   7. PHPDocsMD\Console\PHPDocsMDCommand->execute(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Command/Command.php:255
    0.0878    3743728   8. PHPDocsMD\Console\PHPDocsMDCommand->getClassEntity(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:169
    0.0909    3926688   9. PHPDocsMD\Reflector->getClassEntity() /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:57
    0.0923    3946568  10. PHPDocsMD\Reflector->getClassFunctions(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:83
    0.1102    3985600  11. PHPDocsMD\Reflector->createFunctionEntity(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:118
    0.1111    3989392  12. PHPDocsMD\Reflector->getParams(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:161
    0.1117    3991512  13. PHPDocsMD\Reflector->createParameterEntity(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:438
    0.1117    3991512  14. PHPDocsMD\Reflector::getParamType(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:252


Deprecated: Function ReflectionParameter::export() is deprecated in /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php on line 344

Call Stack:
    0.0117     404600   1. {main}() /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:0
    0.0523    2236352   2. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:12
    0.0606    2842184   3. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/CLI.php:29
    0.0783    3094184   4. PHPDocsMD\Console\CLI->doRun(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:166
    0.0802    3107856   5. PHPDocsMD\Console\CLI->doRunCommand(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:290
    0.0803    3107856   6. PHPDocsMD\Console\PHPDocsMDCommand->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:971
    0.0815    3110488   7. PHPDocsMD\Console\PHPDocsMDCommand->execute(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Command/Command.php:255
    0.0878    3743728   8. PHPDocsMD\Console\PHPDocsMDCommand->getClassEntity(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:169
    0.0909    3926688   9. PHPDocsMD\Reflector->getClassEntity() /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:57
    0.0923    3946568  10. PHPDocsMD\Reflector->getClassFunctions(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:83
    0.1102    3985600  11. PHPDocsMD\Reflector->createFunctionEntity(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:118
    0.1111    3989392  12. PHPDocsMD\Reflector->getParams(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:161
    0.1123    3992120  13. PHPDocsMD\Reflector->createParameterEntity(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:438
    0.1123    3992120  14. PHPDocsMD\Reflector::getParamType(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:252


Deprecated: Function ReflectionParameter::export() is deprecated in /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php on line 344

Call Stack:
    0.0117     404600   1. {main}() /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:0
    0.0523    2236352   2. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:12
    0.0606    2842184   3. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/CLI.php:29
    0.0783    3094184   4. PHPDocsMD\Console\CLI->doRun(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:166
    0.0802    3107856   5. PHPDocsMD\Console\CLI->doRunCommand(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:290
    0.0803    3107856   6. PHPDocsMD\Console\PHPDocsMDCommand->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:971
    0.0815    3110488   7. PHPDocsMD\Console\PHPDocsMDCommand->execute(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Command/Command.php:255
    0.0878    3743728   8. PHPDocsMD\Console\PHPDocsMDCommand->getClassEntity(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:169
    0.0909    3926688   9. PHPDocsMD\Reflector->getClassEntity() /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:57
    0.0923    3946568  10. PHPDocsMD\Reflector->getClassFunctions(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:83
    0.1129    3988120  11. PHPDocsMD\Reflector->createFunctionEntity(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:118
    0.1139    3992608  12. PHPDocsMD\Reflector->getParams(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:161
    0.1139    3993872  13. PHPDocsMD\Reflector->createParameterEntity(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:438
    0.1140    3993872  14. PHPDocsMD\Reflector::getParamType(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:252


Deprecated: Function ReflectionParameter::export() is deprecated in /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php on line 344

Call Stack:
    0.0117     404600   1. {main}() /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:0
    0.0523    2236352   2. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:12
    0.0606    2842184   3. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/CLI.php:29
    0.0783    3094184   4. PHPDocsMD\Console\CLI->doRun(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:166
    0.0802    3107856   5. PHPDocsMD\Console\CLI->doRunCommand(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:290
    0.0803    3107856   6. PHPDocsMD\Console\PHPDocsMDCommand->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:971
    0.0815    3110488   7. PHPDocsMD\Console\PHPDocsMDCommand->execute(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Command/Command.php:255
    0.0878    3743728   8. PHPDocsMD\Console\PHPDocsMDCommand->getClassEntity(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:169
    0.0909    3926688   9. PHPDocsMD\Reflector->getClassEntity() /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:57
    0.0923    3946568  10. PHPDocsMD\Reflector->getClassFunctions(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:83
    0.1129    3988120  11. PHPDocsMD\Reflector->createFunctionEntity(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:118
    0.1139    3992608  12. PHPDocsMD\Reflector->getParams(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:161
    0.1145    3994848  13. PHPDocsMD\Reflector->createParameterEntity(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:438
    0.1145    3994848  14. PHPDocsMD\Reflector::getParamType(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:252


Deprecated: Function ReflectionParameter::export() is deprecated in /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php on line 344

Call Stack:
    0.0117     404600   1. {main}() /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:0
    0.0523    2236352   2. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:12
    0.0606    2842184   3. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/CLI.php:29
    0.0783    3094184   4. PHPDocsMD\Console\CLI->doRun(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:166
    0.0802    3107856   5. PHPDocsMD\Console\CLI->doRunCommand(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:290
    0.0803    3107856   6. PHPDocsMD\Console\PHPDocsMDCommand->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:971
    0.0815    3110488   7. PHPDocsMD\Console\PHPDocsMDCommand->execute(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Command/Command.php:255
    0.0878    3743728   8. PHPDocsMD\Console\PHPDocsMDCommand->getClassEntity(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:169
    0.0909    3926688   9. PHPDocsMD\Reflector->getClassEntity() /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:57
    0.0923    3946568  10. PHPDocsMD\Reflector->getClassFunctions(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:83
    0.1129    3988120  11. PHPDocsMD\Reflector->createFunctionEntity(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:118
    0.1139    3992608  12. PHPDocsMD\Reflector->getParams(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:161
    0.1150    3995448  13. PHPDocsMD\Reflector->createParameterEntity(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:438
    0.1150    3995448  14. PHPDocsMD\Reflector::getParamType(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:252


Deprecated: Function ReflectionParameter::export() is deprecated in /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php on line 344

Call Stack:
    0.0117     404600   1. {main}() /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:0
    0.0523    2236352   2. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:12
    0.0606    2842184   3. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/CLI.php:29
    0.0783    3094184   4. PHPDocsMD\Console\CLI->doRun(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:166
    0.0802    3107856   5. PHPDocsMD\Console\CLI->doRunCommand(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:290
    0.0803    3107856   6. PHPDocsMD\Console\PHPDocsMDCommand->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:971
    0.0815    3110488   7. PHPDocsMD\Console\PHPDocsMDCommand->execute(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Command/Command.php:255
    0.0878    3743728   8. PHPDocsMD\Console\PHPDocsMDCommand->getClassEntity(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:169
    0.0909    3926688   9. PHPDocsMD\Reflector->getClassEntity() /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:57
    0.0923    3946568  10. PHPDocsMD\Reflector->getClassFunctions(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:83
    0.1129    3988120  11. PHPDocsMD\Reflector->createFunctionEntity(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:118
    0.1139    3992608  12. PHPDocsMD\Reflector->getParams(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:161
    0.1156    3996056  13. PHPDocsMD\Reflector->createParameterEntity(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:438
    0.1156    3996056  14. PHPDocsMD\Reflector::getParamType(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:252


Deprecated: Function ReflectionParameter::export() is deprecated in /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php on line 344

Call Stack:
    0.0117     404600   1. {main}() /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:0
    0.0523    2236352   2. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:12
    0.0606    2842184   3. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/CLI.php:29
    0.0783    3094184   4. PHPDocsMD\Console\CLI->doRun(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:166
    0.0802    3107856   5. PHPDocsMD\Console\CLI->doRunCommand(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:290
    0.0803    3107856   6. PHPDocsMD\Console\PHPDocsMDCommand->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:971
    0.0815    3110488   7. PHPDocsMD\Console\PHPDocsMDCommand->execute(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Command/Command.php:255
    0.0878    3743728   8. PHPDocsMD\Console\PHPDocsMDCommand->getClassEntity(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:169
    0.0909    3926688   9. PHPDocsMD\Reflector->getClassEntity() /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:57
    0.0923    3946568  10. PHPDocsMD\Reflector->getClassFunctions(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:83
    0.1162    3991472  11. PHPDocsMD\Reflector->createFunctionEntity(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:118
    0.1169    3994704  12. PHPDocsMD\Reflector->getParams(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:161
    0.1169    3995736  13. PHPDocsMD\Reflector->createParameterEntity(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:438
    0.1170    3995736  14. PHPDocsMD\Reflector::getParamType(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:252


Deprecated: Function ReflectionParameter::export() is deprecated in /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php on line 344

Call Stack:
    0.0117     404600   1. {main}() /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:0
    0.0523    2236352   2. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:12
    0.0606    2842184   3. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/CLI.php:29
    0.0783    3094184   4. PHPDocsMD\Console\CLI->doRun(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:166
    0.0802    3107856   5. PHPDocsMD\Console\CLI->doRunCommand(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:290
    0.0803    3107856   6. PHPDocsMD\Console\PHPDocsMDCommand->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:971
    0.0815    3110488   7. PHPDocsMD\Console\PHPDocsMDCommand->execute(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Command/Command.php:255
    0.0878    3743728   8. PHPDocsMD\Console\PHPDocsMDCommand->getClassEntity(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:169
    0.0909    3926688   9. PHPDocsMD\Reflector->getClassEntity() /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:57
    0.0923    3946568  10. PHPDocsMD\Reflector->getClassFunctions(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:83
    0.1162    3991472  11. PHPDocsMD\Reflector->createFunctionEntity(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:118
    0.1169    3994704  12. PHPDocsMD\Reflector->getParams(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:161
    0.1175    3996808  13. PHPDocsMD\Reflector->createParameterEntity(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:438
    0.1176    3996808  14. PHPDocsMD\Reflector::getParamType(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:252


Deprecated: Function ReflectionParameter::export() is deprecated in /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php on line 344

Call Stack:
    0.0117     404600   1. {main}() /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:0
    0.0523    2236352   2. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:12
    0.0606    2842184   3. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/CLI.php:29
    0.0783    3094184   4. PHPDocsMD\Console\CLI->doRun(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:166
    0.0802    3107856   5. PHPDocsMD\Console\CLI->doRunCommand(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:290
    0.0803    3107856   6. PHPDocsMD\Console\PHPDocsMDCommand->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:971
    0.0815    3110488   7. PHPDocsMD\Console\PHPDocsMDCommand->execute(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Command/Command.php:255
    0.0878    3743728   8. PHPDocsMD\Console\PHPDocsMDCommand->getClassEntity(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:169
    0.0909    3926688   9. PHPDocsMD\Reflector->getClassEntity() /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:57
    0.0923    3946568  10. PHPDocsMD\Reflector->getClassFunctions(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:83
    0.1181    3993704  11. PHPDocsMD\Reflector->createFunctionEntity(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:118
    0.1188    3997032  12. PHPDocsMD\Reflector->getParams(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:161
    0.1189    3998064  13. PHPDocsMD\Reflector->createParameterEntity(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:438
    0.1189    3998064  14. PHPDocsMD\Reflector::getParamType(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:252


Deprecated: Function ReflectionParameter::export() is deprecated in /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php on line 344

Call Stack:
    0.0117     404600   1. {main}() /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:0
    0.0523    2236352   2. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:12
    0.0606    2842184   3. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/CLI.php:29
    0.0783    3094184   4. PHPDocsMD\Console\CLI->doRun(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:166
    0.0802    3107856   5. PHPDocsMD\Console\CLI->doRunCommand(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:290
    0.0803    3107856   6. PHPDocsMD\Console\PHPDocsMDCommand->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:971
    0.0815    3110488   7. PHPDocsMD\Console\PHPDocsMDCommand->execute(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Command/Command.php:255
    0.0878    3743728   8. PHPDocsMD\Console\PHPDocsMDCommand->getClassEntity(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:169
    0.0909    3926688   9. PHPDocsMD\Reflector->getClassEntity() /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:57
    0.0923    3946568  10. PHPDocsMD\Reflector->getClassFunctions(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:83
    0.1181    3993704  11. PHPDocsMD\Reflector->createFunctionEntity(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:118
    0.1188    3997032  12. PHPDocsMD\Reflector->getParams(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:161
    0.1194    3999048  13. PHPDocsMD\Reflector->createParameterEntity(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:438
    0.1195    3999048  14. PHPDocsMD\Reflector::getParamType(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:252


Deprecated: Function ReflectionParameter::export() is deprecated in /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php on line 344

Call Stack:
    0.0117     404600   1. {main}() /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:0
    0.0523    2236352   2. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:12
    0.0606    2842184   3. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/CLI.php:29
    0.0783    3094184   4. PHPDocsMD\Console\CLI->doRun(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:166
    0.0802    3107856   5. PHPDocsMD\Console\CLI->doRunCommand(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:290
    0.0803    3107856   6. PHPDocsMD\Console\PHPDocsMDCommand->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:971
    0.0815    3110488   7. PHPDocsMD\Console\PHPDocsMDCommand->execute(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Command/Command.php:255
    0.0878    3743728   8. PHPDocsMD\Console\PHPDocsMDCommand->getClassEntity(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:169
    0.0909    3926688   9. PHPDocsMD\Reflector->getClassEntity() /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:57
    0.0923    3946568  10. PHPDocsMD\Reflector->getClassFunctions(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:83
    0.1201    3995944  11. PHPDocsMD\Reflector->createFunctionEntity(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:118
    0.1209    3999776  12. PHPDocsMD\Reflector->getParams(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:161
    0.1209    4000920  13. PHPDocsMD\Reflector->createParameterEntity(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:438
    0.1209    4000920  14. PHPDocsMD\Reflector::getParamType(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:252


Deprecated: Function ReflectionParameter::export() is deprecated in /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php on line 344

Call Stack:
    0.0117     404600   1. {main}() /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:0
    0.0523    2236352   2. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:12
    0.0606    2842184   3. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/CLI.php:29
    0.0783    3094184   4. PHPDocsMD\Console\CLI->doRun(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:166
    0.0802    3107856   5. PHPDocsMD\Console\CLI->doRunCommand(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:290
    0.0803    3107856   6. PHPDocsMD\Console\PHPDocsMDCommand->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:971
    0.0815    3110488   7. PHPDocsMD\Console\PHPDocsMDCommand->execute(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Command/Command.php:255
    0.0878    3743728   8. PHPDocsMD\Console\PHPDocsMDCommand->getClassEntity(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:169
    0.0909    3926688   9. PHPDocsMD\Reflector->getClassEntity() /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:57
    0.0923    3946568  10. PHPDocsMD\Reflector->getClassFunctions(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:83
    0.1201    3995944  11. PHPDocsMD\Reflector->createFunctionEntity(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:118
    0.1209    3999776  12. PHPDocsMD\Reflector->getParams(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:161
    0.1215    4001904  13. PHPDocsMD\Reflector->createParameterEntity(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:438
    0.1215    4001904  14. PHPDocsMD\Reflector::getParamType(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:252


Deprecated: Function ReflectionParameter::export() is deprecated in /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php on line 344

Call Stack:
    0.0117     404600   1. {main}() /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:0
    0.0523    2236352   2. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:12
    0.0606    2842184   3. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/CLI.php:29
    0.0783    3094184   4. PHPDocsMD\Console\CLI->doRun(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:166
    0.0802    3107856   5. PHPDocsMD\Console\CLI->doRunCommand(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:290
    0.0803    3107856   6. PHPDocsMD\Console\PHPDocsMDCommand->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:971
    0.0815    3110488   7. PHPDocsMD\Console\PHPDocsMDCommand->execute(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Command/Command.php:255
    0.0878    3743728   8. PHPDocsMD\Console\PHPDocsMDCommand->getClassEntity(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:169
    0.0909    3926688   9. PHPDocsMD\Reflector->getClassEntity() /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:57
    0.0923    3946568  10. PHPDocsMD\Reflector->getClassFunctions(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:83
    0.1201    3995944  11. PHPDocsMD\Reflector->createFunctionEntity(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:118
    0.1209    3999776  12. PHPDocsMD\Reflector->getParams(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:161
    0.1220    4002512  13. PHPDocsMD\Reflector->createParameterEntity(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:438
    0.1221    4002512  14. PHPDocsMD\Reflector::getParamType(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:252


Deprecated: Function ReflectionParameter::export() is deprecated in /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php on line 344

Call Stack:
    0.0117     404600   1. {main}() /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:0
    0.0523    2236352   2. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:12
    0.0606    2842184   3. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/CLI.php:29
    0.0783    3094184   4. PHPDocsMD\Console\CLI->doRun(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:166
    0.0802    3107856   5. PHPDocsMD\Console\CLI->doRunCommand(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:290
    0.0803    3107856   6. PHPDocsMD\Console\PHPDocsMDCommand->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:971
    0.0815    3110488   7. PHPDocsMD\Console\PHPDocsMDCommand->execute(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Command/Command.php:255
    0.0878    3743728   8. PHPDocsMD\Console\PHPDocsMDCommand->getClassEntity(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:169
    0.0909    3926688   9. PHPDocsMD\Reflector->getClassEntity() /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:57
    0.0923    3946568  10. PHPDocsMD\Reflector->getClassFunctions(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:83
    0.1227    3998504  11. PHPDocsMD\Reflector->createFunctionEntity(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:118
    0.1237    4003016  12. PHPDocsMD\Reflector->getParams(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:161
    0.1238    4004288  13. PHPDocsMD\Reflector->createParameterEntity(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:438
    0.1238    4004288  14. PHPDocsMD\Reflector::getParamType(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:252


Deprecated: Function ReflectionParameter::export() is deprecated in /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php on line 344

Call Stack:
    0.0117     404600   1. {main}() /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:0
    0.0523    2236352   2. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:12
    0.0606    2842184   3. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/CLI.php:29
    0.0783    3094184   4. PHPDocsMD\Console\CLI->doRun(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:166
    0.0802    3107856   5. PHPDocsMD\Console\CLI->doRunCommand(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:290
    0.0803    3107856   6. PHPDocsMD\Console\PHPDocsMDCommand->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:971
    0.0815    3110488   7. PHPDocsMD\Console\PHPDocsMDCommand->execute(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Command/Command.php:255
    0.0878    3743728   8. PHPDocsMD\Console\PHPDocsMDCommand->getClassEntity(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:169
    0.0909    3926688   9. PHPDocsMD\Reflector->getClassEntity() /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:57
    0.0923    3946568  10. PHPDocsMD\Reflector->getClassFunctions(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:83
    0.1227    3998504  11. PHPDocsMD\Reflector->createFunctionEntity(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:118
    0.1237    4003016  12. PHPDocsMD\Reflector->getParams(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:161
    0.1244    4005264  13. PHPDocsMD\Reflector->createParameterEntity(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:438
    0.1244    4005264  14. PHPDocsMD\Reflector::getParamType(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:252


Deprecated: Function ReflectionParameter::export() is deprecated in /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php on line 344

Call Stack:
    0.0117     404600   1. {main}() /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:0
    0.0523    2236352   2. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:12
    0.0606    2842184   3. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/CLI.php:29
    0.0783    3094184   4. PHPDocsMD\Console\CLI->doRun(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:166
    0.0802    3107856   5. PHPDocsMD\Console\CLI->doRunCommand(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:290
    0.0803    3107856   6. PHPDocsMD\Console\PHPDocsMDCommand->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:971
    0.0815    3110488   7. PHPDocsMD\Console\PHPDocsMDCommand->execute(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Command/Command.php:255
    0.0878    3743728   8. PHPDocsMD\Console\PHPDocsMDCommand->getClassEntity(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:169
    0.0909    3926688   9. PHPDocsMD\Reflector->getClassEntity() /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:57
    0.0923    3946568  10. PHPDocsMD\Reflector->getClassFunctions(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:83
    0.1227    3998504  11. PHPDocsMD\Reflector->createFunctionEntity(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:118
    0.1237    4003016  12. PHPDocsMD\Reflector->getParams(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:161
    0.1250    4005864  13. PHPDocsMD\Reflector->createParameterEntity(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:438
    0.1250    4005864  14. PHPDocsMD\Reflector::getParamType(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:252


Deprecated: Function ReflectionParameter::export() is deprecated in /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php on line 344

Call Stack:
    0.0117     404600   1. {main}() /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:0
    0.0523    2236352   2. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:12
    0.0606    2842184   3. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/CLI.php:29
    0.0783    3094184   4. PHPDocsMD\Console\CLI->doRun(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:166
    0.0802    3107856   5. PHPDocsMD\Console\CLI->doRunCommand(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:290
    0.0803    3107856   6. PHPDocsMD\Console\PHPDocsMDCommand->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:971
    0.0815    3110488   7. PHPDocsMD\Console\PHPDocsMDCommand->execute(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Command/Command.php:255
    0.0878    3743728   8. PHPDocsMD\Console\PHPDocsMDCommand->getClassEntity(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:169
    0.0909    3926688   9. PHPDocsMD\Reflector->getClassEntity() /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:57
    0.0923    3946568  10. PHPDocsMD\Reflector->getClassFunctions(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:83
    0.1227    3998504  11. PHPDocsMD\Reflector->createFunctionEntity(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:118
    0.1237    4003016  12. PHPDocsMD\Reflector->getParams(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:161
    0.1255    4006472  13. PHPDocsMD\Reflector->createParameterEntity(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:438
    0.1256    4006472  14. PHPDocsMD\Reflector::getParamType(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:252


Deprecated: Function ReflectionParameter::export() is deprecated in /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php on line 344

Call Stack:
    0.0117     404600   1. {main}() /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:0
    0.0523    2236352   2. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:12
    0.0606    2842184   3. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/CLI.php:29
    0.0783    3094184   4. PHPDocsMD\Console\CLI->doRun(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:166
    0.0802    3107856   5. PHPDocsMD\Console\CLI->doRunCommand(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:290
    0.0803    3107856   6. PHPDocsMD\Console\PHPDocsMDCommand->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:971
    0.0815    3110488   7. PHPDocsMD\Console\PHPDocsMDCommand->execute(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Command/Command.php:255
    0.0878    3743728   8. PHPDocsMD\Console\PHPDocsMDCommand->getClassEntity(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:169
    0.0909    3926688   9. PHPDocsMD\Reflector->getClassEntity() /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:57
    0.0923    3946568  10. PHPDocsMD\Reflector->getClassFunctions(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:83
    0.1261    4001560  11. PHPDocsMD\Reflector->createFunctionEntity(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:118
    0.1271    4006072  12. PHPDocsMD\Reflector->getParams(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:161
    0.1271    4007344  13. PHPDocsMD\Reflector->createParameterEntity(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:438
    0.1272    4007344  14. PHPDocsMD\Reflector::getParamType(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:252


Deprecated: Function ReflectionParameter::export() is deprecated in /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php on line 344

Call Stack:
    0.0117     404600   1. {main}() /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:0
    0.0523    2236352   2. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:12
    0.0606    2842184   3. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/CLI.php:29
    0.0783    3094184   4. PHPDocsMD\Console\CLI->doRun(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:166
    0.0802    3107856   5. PHPDocsMD\Console\CLI->doRunCommand(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:290
    0.0803    3107856   6. PHPDocsMD\Console\PHPDocsMDCommand->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:971
    0.0815    3110488   7. PHPDocsMD\Console\PHPDocsMDCommand->execute(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Command/Command.php:255
    0.0878    3743728   8. PHPDocsMD\Console\PHPDocsMDCommand->getClassEntity(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:169
    0.0909    3926688   9. PHPDocsMD\Reflector->getClassEntity() /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:57
    0.0923    3946568  10. PHPDocsMD\Reflector->getClassFunctions(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:83
    0.1261    4001560  11. PHPDocsMD\Reflector->createFunctionEntity(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:118
    0.1271    4006072  12. PHPDocsMD\Reflector->getParams(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:161
    0.1277    4008320  13. PHPDocsMD\Reflector->createParameterEntity(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:438
    0.1277    4008320  14. PHPDocsMD\Reflector::getParamType(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:252


Deprecated: Function ReflectionParameter::export() is deprecated in /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php on line 344

Call Stack:
    0.0117     404600   1. {main}() /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:0
    0.0523    2236352   2. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:12
    0.0606    2842184   3. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/CLI.php:29
    0.0783    3094184   4. PHPDocsMD\Console\CLI->doRun(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:166
    0.0802    3107856   5. PHPDocsMD\Console\CLI->doRunCommand(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:290
    0.0803    3107856   6. PHPDocsMD\Console\PHPDocsMDCommand->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:971
    0.0815    3110488   7. PHPDocsMD\Console\PHPDocsMDCommand->execute(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Command/Command.php:255
    0.0878    3743728   8. PHPDocsMD\Console\PHPDocsMDCommand->getClassEntity(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:169
    0.0909    3926688   9. PHPDocsMD\Reflector->getClassEntity() /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:57
    0.0923    3946568  10. PHPDocsMD\Reflector->getClassFunctions(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:83
    0.1261    4001560  11. PHPDocsMD\Reflector->createFunctionEntity(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:118
    0.1271    4006072  12. PHPDocsMD\Reflector->getParams(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:161
    0.1282    4008920  13. PHPDocsMD\Reflector->createParameterEntity(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:438
    0.1282    4008920  14. PHPDocsMD\Reflector::getParamType(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:252


Deprecated: Function ReflectionParameter::export() is deprecated in /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php on line 344

Call Stack:
    0.0117     404600   1. {main}() /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:0
    0.0523    2236352   2. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:12
    0.0606    2842184   3. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/CLI.php:29
    0.0783    3094184   4. PHPDocsMD\Console\CLI->doRun(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:166
    0.0802    3107856   5. PHPDocsMD\Console\CLI->doRunCommand(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:290
    0.0803    3107856   6. PHPDocsMD\Console\PHPDocsMDCommand->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:971
    0.0815    3110488   7. PHPDocsMD\Console\PHPDocsMDCommand->execute(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Command/Command.php:255
    0.0878    3743728   8. PHPDocsMD\Console\PHPDocsMDCommand->getClassEntity(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:169
    0.0909    3926688   9. PHPDocsMD\Reflector->getClassEntity() /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:57
    0.0923    3946568  10. PHPDocsMD\Reflector->getClassFunctions(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:83
    0.1261    4001560  11. PHPDocsMD\Reflector->createFunctionEntity(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:118
    0.1271    4006072  12. PHPDocsMD\Reflector->getParams(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:161
    0.1288    4009528  13. PHPDocsMD\Reflector->createParameterEntity(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:438
    0.1288    4009528  14. PHPDocsMD\Reflector::getParamType(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:252


Deprecated: Function ReflectionParameter::export() is deprecated in /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php on line 344

Call Stack:
    0.0117     404600   1. {main}() /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:0
    0.0523    2236352   2. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:12
    0.0606    2842184   3. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/CLI.php:29
    0.0783    3094184   4. PHPDocsMD\Console\CLI->doRun(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:166
    0.0802    3107856   5. PHPDocsMD\Console\CLI->doRunCommand(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:290
    0.0803    3107856   6. PHPDocsMD\Console\PHPDocsMDCommand->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:971
    0.0815    3110488   7. PHPDocsMD\Console\PHPDocsMDCommand->execute(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Command/Command.php:255
    0.0878    3743728   8. PHPDocsMD\Console\PHPDocsMDCommand->getClassEntity(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:169
    0.0909    3926688   9. PHPDocsMD\Reflector->getClassEntity() /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:57
    0.0923    3946568  10. PHPDocsMD\Reflector->getClassFunctions(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:83
    0.1293    4004616  11. PHPDocsMD\Reflector->createFunctionEntity(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:118
    0.1304    4009760  12. PHPDocsMD\Reflector->getParams(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:161
    0.1305    4011144  13. PHPDocsMD\Reflector->createParameterEntity(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:438
    0.1305    4011144  14. PHPDocsMD\Reflector::getParamType(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:252


Deprecated: Function ReflectionParameter::export() is deprecated in /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php on line 344

Call Stack:
    0.0117     404600   1. {main}() /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:0
    0.0523    2236352   2. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:12
    0.0606    2842184   3. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/CLI.php:29
    0.0783    3094184   4. PHPDocsMD\Console\CLI->doRun(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:166
    0.0802    3107856   5. PHPDocsMD\Console\CLI->doRunCommand(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:290
    0.0803    3107856   6. PHPDocsMD\Console\PHPDocsMDCommand->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:971
    0.0815    3110488   7. PHPDocsMD\Console\PHPDocsMDCommand->execute(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Command/Command.php:255
    0.0878    3743728   8. PHPDocsMD\Console\PHPDocsMDCommand->getClassEntity(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:169
    0.0909    3926688   9. PHPDocsMD\Reflector->getClassEntity() /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:57
    0.0923    3946568  10. PHPDocsMD\Reflector->getClassFunctions(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:83
    0.1293    4004616  11. PHPDocsMD\Reflector->createFunctionEntity(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:118
    0.1304    4009760  12. PHPDocsMD\Reflector->getParams(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:161
    0.1310    4012128  13. PHPDocsMD\Reflector->createParameterEntity(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:438
    0.1310    4012128  14. PHPDocsMD\Reflector::getParamType(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:252


Deprecated: Function ReflectionParameter::export() is deprecated in /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php on line 344

Call Stack:
    0.0117     404600   1. {main}() /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:0
    0.0523    2236352   2. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:12
    0.0606    2842184   3. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/CLI.php:29
    0.0783    3094184   4. PHPDocsMD\Console\CLI->doRun(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:166
    0.0802    3107856   5. PHPDocsMD\Console\CLI->doRunCommand(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:290
    0.0803    3107856   6. PHPDocsMD\Console\PHPDocsMDCommand->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:971
    0.0815    3110488   7. PHPDocsMD\Console\PHPDocsMDCommand->execute(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Command/Command.php:255
    0.0878    3743728   8. PHPDocsMD\Console\PHPDocsMDCommand->getClassEntity(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:169
    0.0909    3926688   9. PHPDocsMD\Reflector->getClassEntity() /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:57
    0.0923    3946568  10. PHPDocsMD\Reflector->getClassFunctions(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:83
    0.1293    4004616  11. PHPDocsMD\Reflector->createFunctionEntity(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:118
    0.1304    4009760  12. PHPDocsMD\Reflector->getParams(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:161
    0.1315    4012728  13. PHPDocsMD\Reflector->createParameterEntity(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:438
    0.1315    4012728  14. PHPDocsMD\Reflector::getParamType(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:252


Deprecated: Function ReflectionParameter::export() is deprecated in /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php on line 344

Call Stack:
    0.0117     404600   1. {main}() /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:0
    0.0523    2236352   2. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:12
    0.0606    2842184   3. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/CLI.php:29
    0.0783    3094184   4. PHPDocsMD\Console\CLI->doRun(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:166
    0.0802    3107856   5. PHPDocsMD\Console\CLI->doRunCommand(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:290
    0.0803    3107856   6. PHPDocsMD\Console\PHPDocsMDCommand->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:971
    0.0815    3110488   7. PHPDocsMD\Console\PHPDocsMDCommand->execute(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Command/Command.php:255
    0.0878    3743728   8. PHPDocsMD\Console\PHPDocsMDCommand->getClassEntity(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:169
    0.0909    3926688   9. PHPDocsMD\Reflector->getClassEntity() /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:57
    0.0923    3946568  10. PHPDocsMD\Reflector->getClassFunctions(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:83
    0.1293    4004616  11. PHPDocsMD\Reflector->createFunctionEntity(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:118
    0.1304    4009760  12. PHPDocsMD\Reflector->getParams(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:161
    0.1319    4013328  13. PHPDocsMD\Reflector->createParameterEntity(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:438
    0.1320    4013328  14. PHPDocsMD\Reflector::getParamType(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:252


Deprecated: Function ReflectionParameter::export() is deprecated in /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php on line 344

Call Stack:
    0.0117     404600   1. {main}() /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:0
    0.0523    2236352   2. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:12
    0.0606    2842184   3. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/CLI.php:29
    0.0783    3094184   4. PHPDocsMD\Console\CLI->doRun(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:166
    0.0802    3107856   5. PHPDocsMD\Console\CLI->doRunCommand(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:290
    0.0803    3107856   6. PHPDocsMD\Console\PHPDocsMDCommand->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:971
    0.0815    3110488   7. PHPDocsMD\Console\PHPDocsMDCommand->execute(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Command/Command.php:255
    0.0878    3743728   8. PHPDocsMD\Console\PHPDocsMDCommand->getClassEntity(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:169
    0.0909    3926688   9. PHPDocsMD\Reflector->getClassEntity() /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:57
    0.0923    3946568  10. PHPDocsMD\Reflector->getClassFunctions(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:83
    0.1293    4004616  11. PHPDocsMD\Reflector->createFunctionEntity(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:118
    0.1304    4009760  12. PHPDocsMD\Reflector->getParams(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:161
    0.1324    4013936  13. PHPDocsMD\Reflector->createParameterEntity(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:438
    0.1324    4013936  14. PHPDocsMD\Reflector::getParamType(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:252


Deprecated: Function ReflectionParameter::export() is deprecated in /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php on line 344

Call Stack:
    0.0117     404600   1. {main}() /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:0
    0.0523    2236352   2. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:12
    0.0606    2842184   3. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/CLI.php:29
    0.0783    3094184   4. PHPDocsMD\Console\CLI->doRun(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:166
    0.0802    3107856   5. PHPDocsMD\Console\CLI->doRunCommand(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:290
    0.0803    3107856   6. PHPDocsMD\Console\PHPDocsMDCommand->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:971
    0.0815    3110488   7. PHPDocsMD\Console\PHPDocsMDCommand->execute(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Command/Command.php:255
    0.0878    3743728   8. PHPDocsMD\Console\PHPDocsMDCommand->getClassEntity(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:169
    0.0909    3926688   9. PHPDocsMD\Reflector->getClassEntity() /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:57
    0.0923    3946568  10. PHPDocsMD\Reflector->getClassFunctions(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:83
    0.1329    4008120  11. PHPDocsMD\Reflector->createFunctionEntity(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:118
    0.1338    4012120  12. PHPDocsMD\Reflector->getParams(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:161
    0.1338    4013272  13. PHPDocsMD\Reflector->createParameterEntity(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:438
    0.1338    4013272  14. PHPDocsMD\Reflector::getParamType(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:252


Deprecated: Function ReflectionParameter::export() is deprecated in /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php on line 344

Call Stack:
    0.0117     404600   1. {main}() /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:0
    0.0523    2236352   2. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:12
    0.0606    2842184   3. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/CLI.php:29
    0.0783    3094184   4. PHPDocsMD\Console\CLI->doRun(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:166
    0.0802    3107856   5. PHPDocsMD\Console\CLI->doRunCommand(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:290
    0.0803    3107856   6. PHPDocsMD\Console\PHPDocsMDCommand->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:971
    0.0815    3110488   7. PHPDocsMD\Console\PHPDocsMDCommand->execute(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Command/Command.php:255
    0.0878    3743728   8. PHPDocsMD\Console\PHPDocsMDCommand->getClassEntity(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:169
    0.0909    3926688   9. PHPDocsMD\Reflector->getClassEntity() /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:57
    0.0923    3946568  10. PHPDocsMD\Reflector->getClassFunctions(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:83
    0.1329    4008120  11. PHPDocsMD\Reflector->createFunctionEntity(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:118
    0.1338    4012120  12. PHPDocsMD\Reflector->getParams(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:161
    0.1343    4014256  13. PHPDocsMD\Reflector->createParameterEntity(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:438
    0.1343    4014256  14. PHPDocsMD\Reflector::getParamType(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:252


Deprecated: Function ReflectionParameter::export() is deprecated in /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php on line 344

Call Stack:
    0.0117     404600   1. {main}() /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:0
    0.0523    2236352   2. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:12
    0.0606    2842184   3. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/CLI.php:29
    0.0783    3094184   4. PHPDocsMD\Console\CLI->doRun(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:166
    0.0802    3107856   5. PHPDocsMD\Console\CLI->doRunCommand(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:290
    0.0803    3107856   6. PHPDocsMD\Console\PHPDocsMDCommand->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:971
    0.0815    3110488   7. PHPDocsMD\Console\PHPDocsMDCommand->execute(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Command/Command.php:255
    0.0878    3743728   8. PHPDocsMD\Console\PHPDocsMDCommand->getClassEntity(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:169
    0.0909    3926688   9. PHPDocsMD\Reflector->getClassEntity() /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:57
    0.0923    3946568  10. PHPDocsMD\Reflector->getClassFunctions(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:83
    0.1329    4008120  11. PHPDocsMD\Reflector->createFunctionEntity(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:118
    0.1338    4012120  12. PHPDocsMD\Reflector->getParams(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:161
    0.1348    4014864  13. PHPDocsMD\Reflector->createParameterEntity(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:438
    0.1348    4014864  14. PHPDocsMD\Reflector::getParamType(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:252


Deprecated: Function ReflectionParameter::export() is deprecated in /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php on line 344

Call Stack:
    0.0117     404600   1. {main}() /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:0
    0.0523    2236352   2. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:12
    0.0606    2842184   3. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/CLI.php:29
    0.0783    3094184   4. PHPDocsMD\Console\CLI->doRun(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:166
    0.0802    3107856   5. PHPDocsMD\Console\CLI->doRunCommand(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:290
    0.0803    3107856   6. PHPDocsMD\Console\PHPDocsMDCommand->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:971
    0.0815    3110488   7. PHPDocsMD\Console\PHPDocsMDCommand->execute(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Command/Command.php:255
    0.0878    3743728   8. PHPDocsMD\Console\PHPDocsMDCommand->getClassEntity(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:169
    0.0909    3926688   9. PHPDocsMD\Reflector->getClassEntity() /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:57
    0.0923    3946568  10. PHPDocsMD\Reflector->getClassFunctions(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:83
    0.1353    4010848  11. PHPDocsMD\Reflector->createFunctionEntity(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:118
    0.1362    4014944  12. PHPDocsMD\Reflector->getParams(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:161
    0.1362    4016096  13. PHPDocsMD\Reflector->createParameterEntity(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:438
    0.1362    4016096  14. PHPDocsMD\Reflector::getParamType(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:252


Deprecated: Function ReflectionParameter::export() is deprecated in /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php on line 344

Call Stack:
    0.0117     404600   1. {main}() /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:0
    0.0523    2236352   2. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:12
    0.0606    2842184   3. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/CLI.php:29
    0.0783    3094184   4. PHPDocsMD\Console\CLI->doRun(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:166
    0.0802    3107856   5. PHPDocsMD\Console\CLI->doRunCommand(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:290
    0.0803    3107856   6. PHPDocsMD\Console\PHPDocsMDCommand->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:971
    0.0815    3110488   7. PHPDocsMD\Console\PHPDocsMDCommand->execute(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Command/Command.php:255
    0.0878    3743728   8. PHPDocsMD\Console\PHPDocsMDCommand->getClassEntity(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:169
    0.0909    3926688   9. PHPDocsMD\Reflector->getClassEntity() /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:57
    0.0923    3946568  10. PHPDocsMD\Reflector->getClassFunctions(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:83
    0.1353    4010848  11. PHPDocsMD\Reflector->createFunctionEntity(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:118
    0.1362    4014944  12. PHPDocsMD\Reflector->getParams(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:161
    0.1367    4017080  13. PHPDocsMD\Reflector->createParameterEntity(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:438
    0.1367    4017080  14. PHPDocsMD\Reflector::getParamType(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:252


Deprecated: Function ReflectionParameter::export() is deprecated in /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php on line 344

Call Stack:
    0.0117     404600   1. {main}() /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:0
    0.0523    2236352   2. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:12
    0.0606    2842184   3. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/CLI.php:29
    0.0783    3094184   4. PHPDocsMD\Console\CLI->doRun(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:166
    0.0802    3107856   5. PHPDocsMD\Console\CLI->doRunCommand(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:290
    0.0803    3107856   6. PHPDocsMD\Console\PHPDocsMDCommand->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:971
    0.0815    3110488   7. PHPDocsMD\Console\PHPDocsMDCommand->execute(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Command/Command.php:255
    0.0878    3743728   8. PHPDocsMD\Console\PHPDocsMDCommand->getClassEntity(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:169
    0.0909    3926688   9. PHPDocsMD\Reflector->getClassEntity() /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:57
    0.0923    3946568  10. PHPDocsMD\Reflector->getClassFunctions(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:83
    0.1353    4010848  11. PHPDocsMD\Reflector->createFunctionEntity(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:118
    0.1362    4014944  12. PHPDocsMD\Reflector->getParams(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:161
    0.1372    4017688  13. PHPDocsMD\Reflector->createParameterEntity(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:438
    0.1372    4017688  14. PHPDocsMD\Reflector::getParamType(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:252


Deprecated: Function ReflectionParameter::export() is deprecated in /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php on line 344

Call Stack:
    0.0117     404600   1. {main}() /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:0
    0.0523    2236352   2. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:12
    0.0606    2842184   3. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/CLI.php:29
    0.0783    3094184   4. PHPDocsMD\Console\CLI->doRun(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:166
    0.0802    3107856   5. PHPDocsMD\Console\CLI->doRunCommand(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:290
    0.0803    3107856   6. PHPDocsMD\Console\PHPDocsMDCommand->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:971
    0.0815    3110488   7. PHPDocsMD\Console\PHPDocsMDCommand->execute(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Command/Command.php:255
    0.0878    3743728   8. PHPDocsMD\Console\PHPDocsMDCommand->getClassEntity(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:169
    0.0909    3926688   9. PHPDocsMD\Reflector->getClassEntity() /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:57
    0.0923    3946568  10. PHPDocsMD\Reflector->getClassFunctions(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:83
    0.1377    4014312  11. PHPDocsMD\Reflector->createFunctionEntity(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:118
    0.1387    4019008  12. PHPDocsMD\Reflector->getParams(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:161
    0.1388    4020272  13. PHPDocsMD\Reflector->createParameterEntity(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:438
    0.1388    4020272  14. PHPDocsMD\Reflector::getParamType(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:252


Deprecated: Function ReflectionParameter::export() is deprecated in /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php on line 344

Call Stack:
    0.0117     404600   1. {main}() /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:0
    0.0523    2236352   2. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:12
    0.0606    2842184   3. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/CLI.php:29
    0.0783    3094184   4. PHPDocsMD\Console\CLI->doRun(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:166
    0.0802    3107856   5. PHPDocsMD\Console\CLI->doRunCommand(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:290
    0.0803    3107856   6. PHPDocsMD\Console\PHPDocsMDCommand->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:971
    0.0815    3110488   7. PHPDocsMD\Console\PHPDocsMDCommand->execute(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Command/Command.php:255
    0.0878    3743728   8. PHPDocsMD\Console\PHPDocsMDCommand->getClassEntity(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:169
    0.0909    3926688   9. PHPDocsMD\Reflector->getClassEntity() /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:57
    0.0923    3946568  10. PHPDocsMD\Reflector->getClassFunctions(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:83
    0.1377    4014312  11. PHPDocsMD\Reflector->createFunctionEntity(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:118
    0.1387    4019008  12. PHPDocsMD\Reflector->getParams(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:161
    0.1393    4021256  13. PHPDocsMD\Reflector->createParameterEntity(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:438
    0.1393    4021256  14. PHPDocsMD\Reflector::getParamType(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:252


Deprecated: Function ReflectionParameter::export() is deprecated in /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php on line 344

Call Stack:
    0.0117     404600   1. {main}() /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:0
    0.0523    2236352   2. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:12
    0.0606    2842184   3. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/CLI.php:29
    0.0783    3094184   4. PHPDocsMD\Console\CLI->doRun(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:166
    0.0802    3107856   5. PHPDocsMD\Console\CLI->doRunCommand(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:290
    0.0803    3107856   6. PHPDocsMD\Console\PHPDocsMDCommand->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:971
    0.0815    3110488   7. PHPDocsMD\Console\PHPDocsMDCommand->execute(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Command/Command.php:255
    0.0878    3743728   8. PHPDocsMD\Console\PHPDocsMDCommand->getClassEntity(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:169
    0.0909    3926688   9. PHPDocsMD\Reflector->getClassEntity() /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:57
    0.0923    3946568  10. PHPDocsMD\Reflector->getClassFunctions(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:83
    0.1377    4014312  11. PHPDocsMD\Reflector->createFunctionEntity(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:118
    0.1387    4019008  12. PHPDocsMD\Reflector->getParams(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:161
    0.1398    4021864  13. PHPDocsMD\Reflector->createParameterEntity(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:438
    0.1399    4021864  14. PHPDocsMD\Reflector::getParamType(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:252


Deprecated: Function ReflectionParameter::export() is deprecated in /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php on line 344

Call Stack:
    0.0117     404600   1. {main}() /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:0
    0.0523    2236352   2. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:12
    0.0606    2842184   3. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/CLI.php:29
    0.0783    3094184   4. PHPDocsMD\Console\CLI->doRun(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:166
    0.0802    3107856   5. PHPDocsMD\Console\CLI->doRunCommand(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:290
    0.0803    3107856   6. PHPDocsMD\Console\PHPDocsMDCommand->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:971
    0.0815    3110488   7. PHPDocsMD\Console\PHPDocsMDCommand->execute(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Command/Command.php:255
    0.0878    3743728   8. PHPDocsMD\Console\PHPDocsMDCommand->getClassEntity(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:169
    0.0909    3926688   9. PHPDocsMD\Reflector->getClassEntity() /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:57
    0.0923    3946568  10. PHPDocsMD\Reflector->getClassFunctions(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:83
    0.1377    4014312  11. PHPDocsMD\Reflector->createFunctionEntity(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:118
    0.1387    4019008  12. PHPDocsMD\Reflector->getParams(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:161
    0.1404    4022472  13. PHPDocsMD\Reflector->createParameterEntity(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:438
    0.1405    4022472  14. PHPDocsMD\Reflector::getParamType(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:252


Deprecated: Function ReflectionParameter::export() is deprecated in /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php on line 344

Call Stack:
    0.0117     404600   1. {main}() /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:0
    0.0523    2236352   2. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:12
    0.0606    2842184   3. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/CLI.php:29
    0.0783    3094184   4. PHPDocsMD\Console\CLI->doRun(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:166
    0.0802    3107856   5. PHPDocsMD\Console\CLI->doRunCommand(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:290
    0.0803    3107856   6. PHPDocsMD\Console\PHPDocsMDCommand->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:971
    0.0815    3110488   7. PHPDocsMD\Console\PHPDocsMDCommand->execute(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Command/Command.php:255
    0.0878    3743728   8. PHPDocsMD\Console\PHPDocsMDCommand->getClassEntity(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:169
    0.0909    3926688   9. PHPDocsMD\Reflector->getClassEntity() /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:57
    0.0923    3946568  10. PHPDocsMD\Reflector->getClassFunctions(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:83
    0.1411    4017552  11. PHPDocsMD\Reflector->createFunctionEntity(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:118
    0.1419    4021048  12. PHPDocsMD\Reflector->getParams(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:161
    0.1419    4022072  13. PHPDocsMD\Reflector->createParameterEntity(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:438
    0.1419    4022072  14. PHPDocsMD\Reflector::getParamType(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:252


Deprecated: Function ReflectionParameter::export() is deprecated in /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php on line 344

Call Stack:
    0.0117     404600   1. {main}() /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:0
    0.0523    2236352   2. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:12
    0.0606    2842184   3. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/CLI.php:29
    0.0783    3094184   4. PHPDocsMD\Console\CLI->doRun(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:166
    0.0802    3107856   5. PHPDocsMD\Console\CLI->doRunCommand(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:290
    0.0803    3107856   6. PHPDocsMD\Console\PHPDocsMDCommand->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:971
    0.0815    3110488   7. PHPDocsMD\Console\PHPDocsMDCommand->execute(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Command/Command.php:255
    0.0878    3743728   8. PHPDocsMD\Console\PHPDocsMDCommand->getClassEntity(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:169
    0.0909    3926688   9. PHPDocsMD\Reflector->getClassEntity() /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:57
    0.0923    3946568  10. PHPDocsMD\Reflector->getClassFunctions(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:83
    0.1411    4017552  11. PHPDocsMD\Reflector->createFunctionEntity(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:118
    0.1419    4021048  12. PHPDocsMD\Reflector->getParams(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:161
    0.1425    4023144  13. PHPDocsMD\Reflector->createParameterEntity(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:438
    0.1425    4023144  14. PHPDocsMD\Reflector::getParamType(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:252


Deprecated: Function ReflectionParameter::export() is deprecated in /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php on line 344

Call Stack:
    0.0117     404600   1. {main}() /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:0
    0.0523    2236352   2. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:12
    0.0606    2842184   3. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/CLI.php:29
    0.0783    3094184   4. PHPDocsMD\Console\CLI->doRun(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:166
    0.0802    3107856   5. PHPDocsMD\Console\CLI->doRunCommand(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:290
    0.0803    3107856   6. PHPDocsMD\Console\PHPDocsMDCommand->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:971
    0.0815    3110488   7. PHPDocsMD\Console\PHPDocsMDCommand->execute(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Command/Command.php:255
    0.0878    3743728   8. PHPDocsMD\Console\PHPDocsMDCommand->getClassEntity(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:169
    0.0909    3926688   9. PHPDocsMD\Reflector->getClassEntity() /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:57
    0.0923    3946568  10. PHPDocsMD\Reflector->getClassFunctions(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:83
    0.1431    4020048  11. PHPDocsMD\Reflector->createFunctionEntity(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:118
    0.1439    4023448  12. PHPDocsMD\Reflector->getParams(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:161
    0.1439    4024472  13. PHPDocsMD\Reflector->createParameterEntity(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:438
    0.1439    4024472  14. PHPDocsMD\Reflector::getParamType(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:252


Deprecated: Function ReflectionParameter::export() is deprecated in /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php on line 344

Call Stack:
    0.0117     404600   1. {main}() /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:0
    0.0523    2236352   2. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:12
    0.0606    2842184   3. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/CLI.php:29
    0.0783    3094184   4. PHPDocsMD\Console\CLI->doRun(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:166
    0.0802    3107856   5. PHPDocsMD\Console\CLI->doRunCommand(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:290
    0.0803    3107856   6. PHPDocsMD\Console\PHPDocsMDCommand->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:971
    0.0815    3110488   7. PHPDocsMD\Console\PHPDocsMDCommand->execute(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Command/Command.php:255
    0.0878    3743728   8. PHPDocsMD\Console\PHPDocsMDCommand->getClassEntity(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:169
    0.0909    3926688   9. PHPDocsMD\Reflector->getClassEntity() /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:57
    0.0923    3946568  10. PHPDocsMD\Reflector->getClassFunctions(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:83
    0.1431    4020048  11. PHPDocsMD\Reflector->createFunctionEntity(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:118
    0.1439    4023448  12. PHPDocsMD\Reflector->getParams(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:161
    0.1444    4025456  13. PHPDocsMD\Reflector->createParameterEntity(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:438
    0.1445    4025456  14. PHPDocsMD\Reflector::getParamType(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:252


Deprecated: Function ReflectionParameter::export() is deprecated in /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php on line 344

Call Stack:
    0.0117     404600   1. {main}() /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:0
    0.0523    2236352   2. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:12
    0.0606    2842184   3. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/CLI.php:29
    0.0783    3094184   4. PHPDocsMD\Console\CLI->doRun(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:166
    0.0802    3107856   5. PHPDocsMD\Console\CLI->doRunCommand(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:290
    0.0803    3107856   6. PHPDocsMD\Console\PHPDocsMDCommand->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:971
    0.0815    3110488   7. PHPDocsMD\Console\PHPDocsMDCommand->execute(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Command/Command.php:255
    0.0878    3743728   8. PHPDocsMD\Console\PHPDocsMDCommand->getClassEntity(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:169
    0.0909    3926688   9. PHPDocsMD\Reflector->getClassEntity() /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:57
    0.0923    3946568  10. PHPDocsMD\Reflector->getClassFunctions(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:83
    0.1485    4022360  11. PHPDocsMD\Reflector->createFunctionEntity(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:118
    0.1495    4026456  12. PHPDocsMD\Reflector->getParams(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:161
    0.1495    4027600  13. PHPDocsMD\Reflector->createParameterEntity(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:438
    0.1495    4027600  14. PHPDocsMD\Reflector::getParamType(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:252


Deprecated: Function ReflectionParameter::export() is deprecated in /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php on line 344

Call Stack:
    0.0117     404600   1. {main}() /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:0
    0.0523    2236352   2. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:12
    0.0606    2842184   3. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/CLI.php:29
    0.0783    3094184   4. PHPDocsMD\Console\CLI->doRun(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:166
    0.0802    3107856   5. PHPDocsMD\Console\CLI->doRunCommand(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:290
    0.0803    3107856   6. PHPDocsMD\Console\PHPDocsMDCommand->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:971
    0.0815    3110488   7. PHPDocsMD\Console\PHPDocsMDCommand->execute(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Command/Command.php:255
    0.0878    3743728   8. PHPDocsMD\Console\PHPDocsMDCommand->getClassEntity(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:169
    0.0909    3926688   9. PHPDocsMD\Reflector->getClassEntity() /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:57
    0.0923    3946568  10. PHPDocsMD\Reflector->getClassFunctions(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:83
    0.1485    4022360  11. PHPDocsMD\Reflector->createFunctionEntity(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:118
    0.1495    4026456  12. PHPDocsMD\Reflector->getParams(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:161
    0.1501    4028576  13. PHPDocsMD\Reflector->createParameterEntity(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:438
    0.1501    4028576  14. PHPDocsMD\Reflector::getParamType(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:252


Deprecated: Function ReflectionParameter::export() is deprecated in /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php on line 344

Call Stack:
    0.0117     404600   1. {main}() /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:0
    0.0523    2236352   2. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:12
    0.0606    2842184   3. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/CLI.php:29
    0.0783    3094184   4. PHPDocsMD\Console\CLI->doRun(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:166
    0.0802    3107856   5. PHPDocsMD\Console\CLI->doRunCommand(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:290
    0.0803    3107856   6. PHPDocsMD\Console\PHPDocsMDCommand->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:971
    0.0815    3110488   7. PHPDocsMD\Console\PHPDocsMDCommand->execute(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Command/Command.php:255
    0.0878    3743728   8. PHPDocsMD\Console\PHPDocsMDCommand->getClassEntity(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:169
    0.0909    3926688   9. PHPDocsMD\Reflector->getClassEntity() /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:57
    0.0923    3946568  10. PHPDocsMD\Reflector->getClassFunctions(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:83
    0.1485    4022360  11. PHPDocsMD\Reflector->createFunctionEntity(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:118
    0.1495    4026456  12. PHPDocsMD\Reflector->getParams(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:161
    0.1507    4029184  13. PHPDocsMD\Reflector->createParameterEntity(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:438
    0.1507    4029184  14. PHPDocsMD\Reflector::getParamType(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:252


Deprecated: Function ReflectionParameter::export() is deprecated in /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php on line 344

Call Stack:
    0.0117     404600   1. {main}() /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:0
    0.0523    2236352   2. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:12
    0.0606    2842184   3. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/CLI.php:29
    0.0783    3094184   4. PHPDocsMD\Console\CLI->doRun(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:166
    0.0802    3107856   5. PHPDocsMD\Console\CLI->doRunCommand(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:290
    0.0803    3107856   6. PHPDocsMD\Console\PHPDocsMDCommand->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:971
    0.0815    3110488   7. PHPDocsMD\Console\PHPDocsMDCommand->execute(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Command/Command.php:255
    0.0878    3743728   8. PHPDocsMD\Console\PHPDocsMDCommand->getClassEntity(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:169
    0.0909    3926688   9. PHPDocsMD\Reflector->getClassEntity() /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:57
    0.0923    3946568  10. PHPDocsMD\Reflector->getClassFunctions(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:83
    0.1512    4025184  11. PHPDocsMD\Reflector->createFunctionEntity(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:118
    0.1522    4029304  12. PHPDocsMD\Reflector->getParams(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:161
    0.1522    4030456  13. PHPDocsMD\Reflector->createParameterEntity(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:438
    0.1522    4030456  14. PHPDocsMD\Reflector::getParamType(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:252


Deprecated: Function ReflectionParameter::export() is deprecated in /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php on line 344

Call Stack:
    0.0117     404600   1. {main}() /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:0
    0.0523    2236352   2. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:12
    0.0606    2842184   3. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/CLI.php:29
    0.0783    3094184   4. PHPDocsMD\Console\CLI->doRun(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:166
    0.0802    3107856   5. PHPDocsMD\Console\CLI->doRunCommand(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:290
    0.0803    3107856   6. PHPDocsMD\Console\PHPDocsMDCommand->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:971
    0.0815    3110488   7. PHPDocsMD\Console\PHPDocsMDCommand->execute(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Command/Command.php:255
    0.0878    3743728   8. PHPDocsMD\Console\PHPDocsMDCommand->getClassEntity(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:169
    0.0909    3926688   9. PHPDocsMD\Reflector->getClassEntity() /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:57
    0.0923    3946568  10. PHPDocsMD\Reflector->getClassFunctions(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:83
    0.1512    4025184  11. PHPDocsMD\Reflector->createFunctionEntity(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:118
    0.1522    4029304  12. PHPDocsMD\Reflector->getParams(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:161
    0.1527    4031432  13. PHPDocsMD\Reflector->createParameterEntity(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:438
    0.1527    4031432  14. PHPDocsMD\Reflector::getParamType(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:252


Deprecated: Function ReflectionParameter::export() is deprecated in /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php on line 344

Call Stack:
    0.0117     404600   1. {main}() /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:0
    0.0523    2236352   2. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:12
    0.0606    2842184   3. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/CLI.php:29
    0.0783    3094184   4. PHPDocsMD\Console\CLI->doRun(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:166
    0.0802    3107856   5. PHPDocsMD\Console\CLI->doRunCommand(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:290
    0.0803    3107856   6. PHPDocsMD\Console\PHPDocsMDCommand->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:971
    0.0815    3110488   7. PHPDocsMD\Console\PHPDocsMDCommand->execute(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Command/Command.php:255
    0.0878    3743728   8. PHPDocsMD\Console\PHPDocsMDCommand->getClassEntity(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:169
    0.0909    3926688   9. PHPDocsMD\Reflector->getClassEntity() /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:57
    0.0923    3946568  10. PHPDocsMD\Reflector->getClassFunctions(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:83
    0.1512    4025184  11. PHPDocsMD\Reflector->createFunctionEntity(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:118
    0.1522    4029304  12. PHPDocsMD\Reflector->getParams(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:161
    0.1533    4032040  13. PHPDocsMD\Reflector->createParameterEntity(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:438
    0.1533    4032040  14. PHPDocsMD\Reflector::getParamType(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:252


Deprecated: Function ReflectionParameter::export() is deprecated in /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php on line 344

Call Stack:
    0.0117     404600   1. {main}() /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:0
    0.0523    2236352   2. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:12
    0.0606    2842184   3. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/CLI.php:29
    0.0783    3094184   4. PHPDocsMD\Console\CLI->doRun(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:166
    0.0802    3107856   5. PHPDocsMD\Console\CLI->doRunCommand(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:290
    0.0803    3107856   6. PHPDocsMD\Console\PHPDocsMDCommand->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:971
    0.0815    3110488   7. PHPDocsMD\Console\PHPDocsMDCommand->execute(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Command/Command.php:255
    0.0878    3743728   8. PHPDocsMD\Console\PHPDocsMDCommand->getClassEntity(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:169
    0.0909    3926688   9. PHPDocsMD\Reflector->getClassEntity() /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:57
    0.0923    3946568  10. PHPDocsMD\Reflector->getClassFunctions(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:83
    0.1539    4028032  11. PHPDocsMD\Reflector->createFunctionEntity(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:118
    0.1548    4032152  12. PHPDocsMD\Reflector->getParams(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:161
    0.1549    4033304  13. PHPDocsMD\Reflector->createParameterEntity(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:438
    0.1549    4033304  14. PHPDocsMD\Reflector::getParamType(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:252


Deprecated: Function ReflectionParameter::export() is deprecated in /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php on line 344

Call Stack:
    0.0117     404600   1. {main}() /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:0
    0.0523    2236352   2. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:12
    0.0606    2842184   3. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/CLI.php:29
    0.0783    3094184   4. PHPDocsMD\Console\CLI->doRun(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:166
    0.0802    3107856   5. PHPDocsMD\Console\CLI->doRunCommand(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:290
    0.0803    3107856   6. PHPDocsMD\Console\PHPDocsMDCommand->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:971
    0.0815    3110488   7. PHPDocsMD\Console\PHPDocsMDCommand->execute(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Command/Command.php:255
    0.0878    3743728   8. PHPDocsMD\Console\PHPDocsMDCommand->getClassEntity(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:169
    0.0909    3926688   9. PHPDocsMD\Reflector->getClassEntity() /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:57
    0.0923    3946568  10. PHPDocsMD\Reflector->getClassFunctions(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:83
    0.1539    4028032  11. PHPDocsMD\Reflector->createFunctionEntity(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:118
    0.1548    4032152  12. PHPDocsMD\Reflector->getParams(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:161
    0.1555    4034280  13. PHPDocsMD\Reflector->createParameterEntity(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:438
    0.1555    4034280  14. PHPDocsMD\Reflector::getParamType(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:252


Deprecated: Function ReflectionParameter::export() is deprecated in /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php on line 344

Call Stack:
    0.0117     404600   1. {main}() /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:0
    0.0523    2236352   2. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:12
    0.0606    2842184   3. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/CLI.php:29
    0.0783    3094184   4. PHPDocsMD\Console\CLI->doRun(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:166
    0.0802    3107856   5. PHPDocsMD\Console\CLI->doRunCommand(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:290
    0.0803    3107856   6. PHPDocsMD\Console\PHPDocsMDCommand->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:971
    0.0815    3110488   7. PHPDocsMD\Console\PHPDocsMDCommand->execute(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Command/Command.php:255
    0.0878    3743728   8. PHPDocsMD\Console\PHPDocsMDCommand->getClassEntity(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:169
    0.0909    3926688   9. PHPDocsMD\Reflector->getClassEntity() /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:57
    0.0923    3946568  10. PHPDocsMD\Reflector->getClassFunctions(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:83
    0.1539    4028032  11. PHPDocsMD\Reflector->createFunctionEntity(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:118
    0.1548    4032152  12. PHPDocsMD\Reflector->getParams(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:161
    0.1560    4034888  13. PHPDocsMD\Reflector->createParameterEntity(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:438
    0.1561    4034888  14. PHPDocsMD\Reflector::getParamType(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:252


Deprecated: Function ReflectionParameter::export() is deprecated in /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php on line 344

Call Stack:
    0.0117     404600   1. {main}() /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:0
    0.0523    2236352   2. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:12
    0.0606    2842184   3. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/CLI.php:29
    0.0783    3094184   4. PHPDocsMD\Console\CLI->doRun(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:166
    0.0802    3107856   5. PHPDocsMD\Console\CLI->doRunCommand(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:290
    0.0803    3107856   6. PHPDocsMD\Console\PHPDocsMDCommand->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:971
    0.0815    3110488   7. PHPDocsMD\Console\PHPDocsMDCommand->execute(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Command/Command.php:255
    0.0878    3743728   8. PHPDocsMD\Console\PHPDocsMDCommand->getClassEntity(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:169
    0.0909    3926688   9. PHPDocsMD\Reflector->getClassEntity() /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:57
    0.0923    3946568  10. PHPDocsMD\Reflector->getClassFunctions(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:83
    0.1567    4030880  11. PHPDocsMD\Reflector->createFunctionEntity(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:118
    0.1578    4035728  12. PHPDocsMD\Reflector->getParams(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:161
    0.1578    4036992  13. PHPDocsMD\Reflector->createParameterEntity(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:438
    0.1578    4036992  14. PHPDocsMD\Reflector::getParamType(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:252


Deprecated: Function ReflectionParameter::export() is deprecated in /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php on line 344

Call Stack:
    0.0117     404600   1. {main}() /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:0
    0.0523    2236352   2. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:12
    0.0606    2842184   3. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/CLI.php:29
    0.0783    3094184   4. PHPDocsMD\Console\CLI->doRun(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:166
    0.0802    3107856   5. PHPDocsMD\Console\CLI->doRunCommand(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:290
    0.0803    3107856   6. PHPDocsMD\Console\PHPDocsMDCommand->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:971
    0.0815    3110488   7. PHPDocsMD\Console\PHPDocsMDCommand->execute(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Command/Command.php:255
    0.0878    3743728   8. PHPDocsMD\Console\PHPDocsMDCommand->getClassEntity(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:169
    0.0909    3926688   9. PHPDocsMD\Reflector->getClassEntity() /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:57
    0.0923    3946568  10. PHPDocsMD\Reflector->getClassFunctions(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:83
    0.1567    4030880  11. PHPDocsMD\Reflector->createFunctionEntity(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:118
    0.1578    4035728  12. PHPDocsMD\Reflector->getParams(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:161
    0.1584    4037976  13. PHPDocsMD\Reflector->createParameterEntity(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:438
    0.1584    4037976  14. PHPDocsMD\Reflector::getParamType(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:252


Deprecated: Function ReflectionParameter::export() is deprecated in /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php on line 344

Call Stack:
    0.0117     404600   1. {main}() /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:0
    0.0523    2236352   2. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:12
    0.0606    2842184   3. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/CLI.php:29
    0.0783    3094184   4. PHPDocsMD\Console\CLI->doRun(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:166
    0.0802    3107856   5. PHPDocsMD\Console\CLI->doRunCommand(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:290
    0.0803    3107856   6. PHPDocsMD\Console\PHPDocsMDCommand->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:971
    0.0815    3110488   7. PHPDocsMD\Console\PHPDocsMDCommand->execute(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Command/Command.php:255
    0.0878    3743728   8. PHPDocsMD\Console\PHPDocsMDCommand->getClassEntity(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:169
    0.0909    3926688   9. PHPDocsMD\Reflector->getClassEntity() /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:57
    0.0923    3946568  10. PHPDocsMD\Reflector->getClassFunctions(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:83
    0.1567    4030880  11. PHPDocsMD\Reflector->createFunctionEntity(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:118
    0.1578    4035728  12. PHPDocsMD\Reflector->getParams(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:161
    0.1589    4038576  13. PHPDocsMD\Reflector->createParameterEntity(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:438
    0.1590    4038576  14. PHPDocsMD\Reflector::getParamType(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:252


Deprecated: Function ReflectionParameter::export() is deprecated in /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php on line 344

Call Stack:
    0.0117     404600   1. {main}() /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:0
    0.0523    2236352   2. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:12
    0.0606    2842184   3. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/CLI.php:29
    0.0783    3094184   4. PHPDocsMD\Console\CLI->doRun(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:166
    0.0802    3107856   5. PHPDocsMD\Console\CLI->doRunCommand(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:290
    0.0803    3107856   6. PHPDocsMD\Console\PHPDocsMDCommand->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:971
    0.0815    3110488   7. PHPDocsMD\Console\PHPDocsMDCommand->execute(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Command/Command.php:255
    0.0878    3743728   8. PHPDocsMD\Console\PHPDocsMDCommand->getClassEntity(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:169
    0.0909    3926688   9. PHPDocsMD\Reflector->getClassEntity() /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:57
    0.0923    3946568  10. PHPDocsMD\Reflector->getClassFunctions(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:83
    0.1567    4030880  11. PHPDocsMD\Reflector->createFunctionEntity(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:118
    0.1578    4035728  12. PHPDocsMD\Reflector->getParams(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:161
    0.1595    4039184  13. PHPDocsMD\Reflector->createParameterEntity(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:438
    0.1596    4039184  14. PHPDocsMD\Reflector::getParamType(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:252


Deprecated: Function ReflectionParameter::export() is deprecated in /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php on line 344

Call Stack:
    0.0117     404600   1. {main}() /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:0
    0.0523    2236352   2. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:12
    0.0606    2842184   3. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/CLI.php:29
    0.0783    3094184   4. PHPDocsMD\Console\CLI->doRun(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:166
    0.0802    3107856   5. PHPDocsMD\Console\CLI->doRunCommand(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:290
    0.0803    3107856   6. PHPDocsMD\Console\PHPDocsMDCommand->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:971
    0.0815    3110488   7. PHPDocsMD\Console\PHPDocsMDCommand->execute(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Command/Command.php:255
    0.0878    3743728   8. PHPDocsMD\Console\PHPDocsMDCommand->getClassEntity(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:169
    0.0909    3926688   9. PHPDocsMD\Reflector->getClassEntity() /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:57
    0.0923    3946568  10. PHPDocsMD\Reflector->getClassFunctions(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:83
    0.1601    4034272  11. PHPDocsMD\Reflector->createFunctionEntity(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:118
    0.1610    4037848  12. PHPDocsMD\Reflector->getParams(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:161
    0.1610    4038872  13. PHPDocsMD\Reflector->createParameterEntity(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:438
    0.1610    4038872  14. PHPDocsMD\Reflector::getParamType(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:252


Deprecated: Function ReflectionParameter::export() is deprecated in /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php on line 344

Call Stack:
    0.0117     404600   1. {main}() /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:0
    0.0523    2236352   2. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:12
    0.0606    2842184   3. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/CLI.php:29
    0.0783    3094184   4. PHPDocsMD\Console\CLI->doRun(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:166
    0.0802    3107856   5. PHPDocsMD\Console\CLI->doRunCommand(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:290
    0.0803    3107856   6. PHPDocsMD\Console\PHPDocsMDCommand->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:971
    0.0815    3110488   7. PHPDocsMD\Console\PHPDocsMDCommand->execute(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Command/Command.php:255
    0.0878    3743728   8. PHPDocsMD\Console\PHPDocsMDCommand->getClassEntity(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:169
    0.0909    3926688   9. PHPDocsMD\Reflector->getClassEntity() /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:57
    0.0923    3946568  10. PHPDocsMD\Reflector->getClassFunctions(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:83
    0.1601    4034272  11. PHPDocsMD\Reflector->createFunctionEntity(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:118
    0.1610    4037848  12. PHPDocsMD\Reflector->getParams(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:161
    0.1616    4039944  13. PHPDocsMD\Reflector->createParameterEntity(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:438
    0.1616    4039944  14. PHPDocsMD\Reflector::getParamType(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:252


Deprecated: Function ReflectionParameter::export() is deprecated in /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php on line 344

Call Stack:
    0.0117     404600   1. {main}() /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:0
    0.0523    2236352   2. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:12
    0.0606    2842184   3. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/CLI.php:29
    0.0783    3094184   4. PHPDocsMD\Console\CLI->doRun(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:166
    0.0802    3107856   5. PHPDocsMD\Console\CLI->doRunCommand(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:290
    0.0803    3107856   6. PHPDocsMD\Console\PHPDocsMDCommand->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:971
    0.0815    3110488   7. PHPDocsMD\Console\PHPDocsMDCommand->execute(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Command/Command.php:255
    0.0878    3743728   8. PHPDocsMD\Console\PHPDocsMDCommand->getClassEntity(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:169
    0.0909    3926688   9. PHPDocsMD\Reflector->getClassEntity() /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:57
    0.0923    3946568  10. PHPDocsMD\Reflector->getClassFunctions(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:83
    0.1622    4036848  11. PHPDocsMD\Reflector->createFunctionEntity(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:118
    0.1631    4040392  12. PHPDocsMD\Reflector->getParams(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:161
    0.1631    4041416  13. PHPDocsMD\Reflector->createParameterEntity(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:438
    0.1632    4041416  14. PHPDocsMD\Reflector::getParamType(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:252


Deprecated: Function ReflectionParameter::export() is deprecated in /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php on line 344

Call Stack:
    0.0117     404600   1. {main}() /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:0
    0.0523    2236352   2. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:12
    0.0606    2842184   3. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/CLI.php:29
    0.0783    3094184   4. PHPDocsMD\Console\CLI->doRun(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:166
    0.0802    3107856   5. PHPDocsMD\Console\CLI->doRunCommand(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:290
    0.0803    3107856   6. PHPDocsMD\Console\PHPDocsMDCommand->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:971
    0.0815    3110488   7. PHPDocsMD\Console\PHPDocsMDCommand->execute(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Command/Command.php:255
    0.0878    3743728   8. PHPDocsMD\Console\PHPDocsMDCommand->getClassEntity(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:169
    0.0909    3926688   9. PHPDocsMD\Reflector->getClassEntity() /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:57
    0.0923    3946568  10. PHPDocsMD\Reflector->getClassFunctions(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:83
    0.1622    4036848  11. PHPDocsMD\Reflector->createFunctionEntity(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:118
    0.1631    4040392  12. PHPDocsMD\Reflector->getParams(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:161
    0.1637    4042400  13. PHPDocsMD\Reflector->createParameterEntity(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:438
    0.1637    4042400  14. PHPDocsMD\Reflector::getParamType(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:252


Deprecated: Function ReflectionParameter::export() is deprecated in /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php on line 344

Call Stack:
    0.0117     404600   1. {main}() /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:0
    0.0523    2236352   2. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:12
    0.0606    2842184   3. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/CLI.php:29
    0.0783    3094184   4. PHPDocsMD\Console\CLI->doRun(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:166
    0.0802    3107856   5. PHPDocsMD\Console\CLI->doRunCommand(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:290
    0.0803    3107856   6. PHPDocsMD\Console\PHPDocsMDCommand->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:971
    0.0815    3110488   7. PHPDocsMD\Console\PHPDocsMDCommand->execute(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Command/Command.php:255
    0.0878    3743728   8. PHPDocsMD\Console\PHPDocsMDCommand->getClassEntity(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:169
    0.0909    3926688   9. PHPDocsMD\Reflector->getClassEntity() /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:57
    0.0923    3946568  10. PHPDocsMD\Reflector->getClassFunctions(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:83
    0.1643    4039304  11. PHPDocsMD\Reflector->createFunctionEntity(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:118
    0.1653    4043544  12. PHPDocsMD\Reflector->getParams(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:161
    0.1653    4044688  13. PHPDocsMD\Reflector->createParameterEntity(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:438
    0.1653    4044688  14. PHPDocsMD\Reflector::getParamType(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:252


Deprecated: Function ReflectionParameter::export() is deprecated in /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php on line 344

Call Stack:
    0.0117     404600   1. {main}() /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:0
    0.0523    2236352   2. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:12
    0.0606    2842184   3. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/CLI.php:29
    0.0783    3094184   4. PHPDocsMD\Console\CLI->doRun(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:166
    0.0802    3107856   5. PHPDocsMD\Console\CLI->doRunCommand(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:290
    0.0803    3107856   6. PHPDocsMD\Console\PHPDocsMDCommand->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:971
    0.0815    3110488   7. PHPDocsMD\Console\PHPDocsMDCommand->execute(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Command/Command.php:255
    0.0878    3743728   8. PHPDocsMD\Console\PHPDocsMDCommand->getClassEntity(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:169
    0.0909    3926688   9. PHPDocsMD\Reflector->getClassEntity() /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:57
    0.0923    3946568  10. PHPDocsMD\Reflector->getClassFunctions(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:83
    0.1643    4039304  11. PHPDocsMD\Reflector->createFunctionEntity(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:118
    0.1653    4043544  12. PHPDocsMD\Reflector->getParams(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:161
    0.1658    4045664  13. PHPDocsMD\Reflector->createParameterEntity(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:438
    0.1659    4045664  14. PHPDocsMD\Reflector::getParamType(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:252


Deprecated: Function ReflectionParameter::export() is deprecated in /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php on line 344

Call Stack:
    0.0117     404600   1. {main}() /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:0
    0.0523    2236352   2. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:12
    0.0606    2842184   3. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/CLI.php:29
    0.0783    3094184   4. PHPDocsMD\Console\CLI->doRun(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:166
    0.0802    3107856   5. PHPDocsMD\Console\CLI->doRunCommand(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:290
    0.0803    3107856   6. PHPDocsMD\Console\PHPDocsMDCommand->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:971
    0.0815    3110488   7. PHPDocsMD\Console\PHPDocsMDCommand->execute(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Command/Command.php:255
    0.0878    3743728   8. PHPDocsMD\Console\PHPDocsMDCommand->getClassEntity(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:169
    0.0909    3926688   9. PHPDocsMD\Reflector->getClassEntity() /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:57
    0.0923    3946568  10. PHPDocsMD\Reflector->getClassFunctions(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:83
    0.1643    4039304  11. PHPDocsMD\Reflector->createFunctionEntity(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:118
    0.1653    4043544  12. PHPDocsMD\Reflector->getParams(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:161
    0.1664    4046272  13. PHPDocsMD\Reflector->createParameterEntity(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:438
    0.1664    4046272  14. PHPDocsMD\Reflector::getParamType(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:252


Deprecated: Function ReflectionParameter::export() is deprecated in /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php on line 344

Call Stack:
    0.0117     404600   1. {main}() /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:0
    0.0523    2236352   2. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:12
    0.0606    2842184   3. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/CLI.php:29
    0.0783    3094184   4. PHPDocsMD\Console\CLI->doRun(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:166
    0.0802    3107856   5. PHPDocsMD\Console\CLI->doRunCommand(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:290
    0.0803    3107856   6. PHPDocsMD\Console\PHPDocsMDCommand->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:971
    0.0815    3110488   7. PHPDocsMD\Console\PHPDocsMDCommand->execute(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Command/Command.php:255
    0.0878    3743728   8. PHPDocsMD\Console\PHPDocsMDCommand->getClassEntity(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:169
    0.0909    3926688   9. PHPDocsMD\Reflector->getClassEntity() /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:57
    0.0923    3946568  10. PHPDocsMD\Reflector->getClassFunctions(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:83
    0.1670    4042272  11. PHPDocsMD\Reflector->createFunctionEntity(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:118
    0.1680    4046600  12. PHPDocsMD\Reflector->getParams(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:161
    0.1681    4047752  13. PHPDocsMD\Reflector->createParameterEntity(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:438
    0.1681    4047752  14. PHPDocsMD\Reflector::getParamType(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:252


Deprecated: Function ReflectionParameter::export() is deprecated in /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php on line 344

Call Stack:
    0.0117     404600   1. {main}() /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:0
    0.0523    2236352   2. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:12
    0.0606    2842184   3. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/CLI.php:29
    0.0783    3094184   4. PHPDocsMD\Console\CLI->doRun(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:166
    0.0802    3107856   5. PHPDocsMD\Console\CLI->doRunCommand(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:290
    0.0803    3107856   6. PHPDocsMD\Console\PHPDocsMDCommand->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:971
    0.0815    3110488   7. PHPDocsMD\Console\PHPDocsMDCommand->execute(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Command/Command.php:255
    0.0878    3743728   8. PHPDocsMD\Console\PHPDocsMDCommand->getClassEntity(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:169
    0.0909    3926688   9. PHPDocsMD\Reflector->getClassEntity() /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:57
    0.0923    3946568  10. PHPDocsMD\Reflector->getClassFunctions(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:83
    0.1670    4042272  11. PHPDocsMD\Reflector->createFunctionEntity(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:118
    0.1680    4046600  12. PHPDocsMD\Reflector->getParams(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:161
    0.1686    4048728  13. PHPDocsMD\Reflector->createParameterEntity(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:438
    0.1686    4048728  14. PHPDocsMD\Reflector::getParamType(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:252


Deprecated: Function ReflectionParameter::export() is deprecated in /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php on line 344

Call Stack:
    0.0117     404600   1. {main}() /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:0
    0.0523    2236352   2. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:12
    0.0606    2842184   3. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/CLI.php:29
    0.0783    3094184   4. PHPDocsMD\Console\CLI->doRun(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:166
    0.0802    3107856   5. PHPDocsMD\Console\CLI->doRunCommand(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:290
    0.0803    3107856   6. PHPDocsMD\Console\PHPDocsMDCommand->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:971
    0.0815    3110488   7. PHPDocsMD\Console\PHPDocsMDCommand->execute(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Command/Command.php:255
    0.0878    3743728   8. PHPDocsMD\Console\PHPDocsMDCommand->getClassEntity(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:169
    0.0909    3926688   9. PHPDocsMD\Reflector->getClassEntity() /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:57
    0.0923    3946568  10. PHPDocsMD\Reflector->getClassFunctions(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:83
    0.1670    4042272  11. PHPDocsMD\Reflector->createFunctionEntity(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:118
    0.1680    4046600  12. PHPDocsMD\Reflector->getParams(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:161
    0.1691    4049336  13. PHPDocsMD\Reflector->createParameterEntity(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:438
    0.1692    4049336  14. PHPDocsMD\Reflector::getParamType(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:252


Deprecated: Function ReflectionParameter::export() is deprecated in /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php on line 344

Call Stack:
    0.0117     404600   1. {main}() /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:0
    0.0523    2236352   2. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:12
    0.0606    2842184   3. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/CLI.php:29
    0.0783    3094184   4. PHPDocsMD\Console\CLI->doRun(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:166
    0.0802    3107856   5. PHPDocsMD\Console\CLI->doRunCommand(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:290
    0.0803    3107856   6. PHPDocsMD\Console\PHPDocsMDCommand->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:971
    0.0815    3110488   7. PHPDocsMD\Console\PHPDocsMDCommand->execute(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Command/Command.php:255
    0.0878    3743728   8. PHPDocsMD\Console\PHPDocsMDCommand->getClassEntity(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:169
    0.0909    3926688   9. PHPDocsMD\Reflector->getClassEntity() /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:57
    0.0923    3946568  10. PHPDocsMD\Reflector->getClassFunctions(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:83
    0.1698    4045328  11. PHPDocsMD\Reflector->createFunctionEntity(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:118
    0.1708    4049656  12. PHPDocsMD\Reflector->getParams(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:161
    0.1708    4050808  13. PHPDocsMD\Reflector->createParameterEntity(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:438
    0.1708    4050808  14. PHPDocsMD\Reflector::getParamType(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:252


Deprecated: Function ReflectionParameter::export() is deprecated in /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php on line 344

Call Stack:
    0.0117     404600   1. {main}() /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:0
    0.0523    2236352   2. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:12
    0.0606    2842184   3. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/CLI.php:29
    0.0783    3094184   4. PHPDocsMD\Console\CLI->doRun(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:166
    0.0802    3107856   5. PHPDocsMD\Console\CLI->doRunCommand(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:290
    0.0803    3107856   6. PHPDocsMD\Console\PHPDocsMDCommand->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:971
    0.0815    3110488   7. PHPDocsMD\Console\PHPDocsMDCommand->execute(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Command/Command.php:255
    0.0878    3743728   8. PHPDocsMD\Console\PHPDocsMDCommand->getClassEntity(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:169
    0.0909    3926688   9. PHPDocsMD\Reflector->getClassEntity() /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:57
    0.0923    3946568  10. PHPDocsMD\Reflector->getClassFunctions(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:83
    0.1698    4045328  11. PHPDocsMD\Reflector->createFunctionEntity(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:118
    0.1708    4049656  12. PHPDocsMD\Reflector->getParams(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:161
    0.1714    4051784  13. PHPDocsMD\Reflector->createParameterEntity(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:438
    0.1714    4051784  14. PHPDocsMD\Reflector::getParamType(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:252


Deprecated: Function ReflectionParameter::export() is deprecated in /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php on line 344

Call Stack:
    0.0117     404600   1. {main}() /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:0
    0.0523    2236352   2. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:12
    0.0606    2842184   3. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/CLI.php:29
    0.0783    3094184   4. PHPDocsMD\Console\CLI->doRun(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:166
    0.0802    3107856   5. PHPDocsMD\Console\CLI->doRunCommand(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:290
    0.0803    3107856   6. PHPDocsMD\Console\PHPDocsMDCommand->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:971
    0.0815    3110488   7. PHPDocsMD\Console\PHPDocsMDCommand->execute(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Command/Command.php:255
    0.0878    3743728   8. PHPDocsMD\Console\PHPDocsMDCommand->getClassEntity(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:169
    0.0909    3926688   9. PHPDocsMD\Reflector->getClassEntity() /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:57
    0.0923    3946568  10. PHPDocsMD\Reflector->getClassFunctions(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:83
    0.1698    4045328  11. PHPDocsMD\Reflector->createFunctionEntity(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:118
    0.1708    4049656  12. PHPDocsMD\Reflector->getParams(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:161
    0.1719    4052392  13. PHPDocsMD\Reflector->createParameterEntity(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:438
    0.1719    4052392  14. PHPDocsMD\Reflector::getParamType(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:252


Deprecated: Function ReflectionParameter::export() is deprecated in /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php on line 344

Call Stack:
    0.0117     404600   1. {main}() /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:0
    0.0523    2236352   2. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:12
    0.0606    2842184   3. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/CLI.php:29
    0.0783    3094184   4. PHPDocsMD\Console\CLI->doRun(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:166
    0.0802    3107856   5. PHPDocsMD\Console\CLI->doRunCommand(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:290
    0.0803    3107856   6. PHPDocsMD\Console\PHPDocsMDCommand->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:971
    0.0815    3110488   7. PHPDocsMD\Console\PHPDocsMDCommand->execute(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Command/Command.php:255
    0.0878    3743728   8. PHPDocsMD\Console\PHPDocsMDCommand->getClassEntity(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:169
    0.0909    3926688   9. PHPDocsMD\Reflector->getClassEntity() /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:57
    0.0923    3946568  10. PHPDocsMD\Reflector->getClassFunctions(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:83
    0.1724    4048384  11. PHPDocsMD\Reflector->createFunctionEntity(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:118
    0.1736    4053504  12. PHPDocsMD\Reflector->getParams(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:161
    0.1737    4054768  13. PHPDocsMD\Reflector->createParameterEntity(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:438
    0.1737    4054768  14. PHPDocsMD\Reflector::getParamType(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:252


Deprecated: Function ReflectionParameter::export() is deprecated in /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php on line 344

Call Stack:
    0.0117     404600   1. {main}() /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:0
    0.0523    2236352   2. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:12
    0.0606    2842184   3. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/CLI.php:29
    0.0783    3094184   4. PHPDocsMD\Console\CLI->doRun(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:166
    0.0802    3107856   5. PHPDocsMD\Console\CLI->doRunCommand(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:290
    0.0803    3107856   6. PHPDocsMD\Console\PHPDocsMDCommand->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:971
    0.0815    3110488   7. PHPDocsMD\Console\PHPDocsMDCommand->execute(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Command/Command.php:255
    0.0878    3743728   8. PHPDocsMD\Console\PHPDocsMDCommand->getClassEntity(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:169
    0.0909    3926688   9. PHPDocsMD\Reflector->getClassEntity() /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:57
    0.0923    3946568  10. PHPDocsMD\Reflector->getClassFunctions(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:83
    0.1724    4048384  11. PHPDocsMD\Reflector->createFunctionEntity(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:118
    0.1736    4053504  12. PHPDocsMD\Reflector->getParams(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:161
    0.1742    4055752  13. PHPDocsMD\Reflector->createParameterEntity(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:438
    0.1742    4055752  14. PHPDocsMD\Reflector::getParamType(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:252


Deprecated: Function ReflectionParameter::export() is deprecated in /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php on line 344

Call Stack:
    0.0117     404600   1. {main}() /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:0
    0.0523    2236352   2. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:12
    0.0606    2842184   3. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/CLI.php:29
    0.0783    3094184   4. PHPDocsMD\Console\CLI->doRun(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:166
    0.0802    3107856   5. PHPDocsMD\Console\CLI->doRunCommand(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:290
    0.0803    3107856   6. PHPDocsMD\Console\PHPDocsMDCommand->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:971
    0.0815    3110488   7. PHPDocsMD\Console\PHPDocsMDCommand->execute(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Command/Command.php:255
    0.0878    3743728   8. PHPDocsMD\Console\PHPDocsMDCommand->getClassEntity(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:169
    0.0909    3926688   9. PHPDocsMD\Reflector->getClassEntity() /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:57
    0.0923    3946568  10. PHPDocsMD\Reflector->getClassFunctions(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:83
    0.1724    4048384  11. PHPDocsMD\Reflector->createFunctionEntity(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:118
    0.1736    4053504  12. PHPDocsMD\Reflector->getParams(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:161
    0.1747    4056352  13. PHPDocsMD\Reflector->createParameterEntity(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:438
    0.1748    4056352  14. PHPDocsMD\Reflector::getParamType(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:252


Deprecated: Function ReflectionParameter::export() is deprecated in /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php on line 344

Call Stack:
    0.0117     404600   1. {main}() /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:0
    0.0523    2236352   2. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:12
    0.0606    2842184   3. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/CLI.php:29
    0.0783    3094184   4. PHPDocsMD\Console\CLI->doRun(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:166
    0.0802    3107856   5. PHPDocsMD\Console\CLI->doRunCommand(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:290
    0.0803    3107856   6. PHPDocsMD\Console\PHPDocsMDCommand->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:971
    0.0815    3110488   7. PHPDocsMD\Console\PHPDocsMDCommand->execute(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Command/Command.php:255
    0.0878    3743728   8. PHPDocsMD\Console\PHPDocsMDCommand->getClassEntity(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:169
    0.0909    3926688   9. PHPDocsMD\Reflector->getClassEntity() /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:57
    0.0923    3946568  10. PHPDocsMD\Reflector->getClassFunctions(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:83
    0.1724    4048384  11. PHPDocsMD\Reflector->createFunctionEntity(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:118
    0.1736    4053504  12. PHPDocsMD\Reflector->getParams(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:161
    0.1753    4056960  13. PHPDocsMD\Reflector->createParameterEntity(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:438
    0.1753    4056960  14. PHPDocsMD\Reflector::getParamType(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:252


Deprecated: Function ReflectionParameter::export() is deprecated in /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php on line 344

Call Stack:
    0.0117     404600   1. {main}() /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:0
    0.0523    2236352   2. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:12
    0.0606    2842184   3. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/CLI.php:29
    0.0783    3094184   4. PHPDocsMD\Console\CLI->doRun(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:166
    0.0802    3107856   5. PHPDocsMD\Console\CLI->doRunCommand(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:290
    0.0803    3107856   6. PHPDocsMD\Console\PHPDocsMDCommand->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:971
    0.0815    3110488   7. PHPDocsMD\Console\PHPDocsMDCommand->execute(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Command/Command.php:255
    0.0878    3743728   8. PHPDocsMD\Console\PHPDocsMDCommand->getClassEntity(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:169
    0.0909    3926688   9. PHPDocsMD\Reflector->getClassEntity() /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:57
    0.0923    3946568  10. PHPDocsMD\Reflector->getClassFunctions(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:83
    0.1759    4052048  11. PHPDocsMD\Reflector->createFunctionEntity(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:118
    0.1766    4055000  12. PHPDocsMD\Reflector->getParams(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:161
    0.1766    4055904  13. PHPDocsMD\Reflector->createParameterEntity(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:438
    0.1766    4055904  14. PHPDocsMD\Reflector::getParamType(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:252


Deprecated: Function ReflectionParameter::export() is deprecated in /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php on line 344

Call Stack:
    0.0117     404600   1. {main}() /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:0
    0.0523    2236352   2. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:12
    0.0606    2842184   3. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/CLI.php:29
    0.0783    3094184   4. PHPDocsMD\Console\CLI->doRun(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:166
    0.0802    3107856   5. PHPDocsMD\Console\CLI->doRunCommand(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:290
    0.0803    3107856   6. PHPDocsMD\Console\PHPDocsMDCommand->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:971
    0.0815    3110488   7. PHPDocsMD\Console\PHPDocsMDCommand->execute(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Command/Command.php:255
    0.0878    3743728   8. PHPDocsMD\Console\PHPDocsMDCommand->getClassEntity(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:169
    0.0909    3926688   9. PHPDocsMD\Reflector->getClassEntity() /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:57
    0.0923    3946568  10. PHPDocsMD\Reflector->getClassFunctions(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:83
    0.1773    4054792  11. PHPDocsMD\Reflector->createFunctionEntity(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:118
    0.1779    4057696  12. PHPDocsMD\Reflector->getParams(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:161
    0.1780    4058600  13. PHPDocsMD\Reflector->createParameterEntity(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:438
    0.1780    4058600  14. PHPDocsMD\Reflector::getParamType(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:252



## Public API
<nav>
	<ul>
		<li>
			<a href="#assertcountqueries">assertCountQueries</a>
		</li>
		<li>
			<a href="#assertnotqueries">assertNotQueries</a>
		</li>
		<li>
			<a href="#assertnotqueriesbyaction">assertNotQueriesByAction</a>
		</li>
		<li>
			<a href="#assertnotqueriesbyfilter">assertNotQueriesByFilter</a>
		</li>
		<li>
			<a href="#assertnotqueriesbyfunction">assertNotQueriesByFunction</a>
		</li>
		<li>
			<a href="#assertnotqueriesbymethod">assertNotQueriesByMethod</a>
		</li>
		<li>
			<a href="#assertnotqueriesbystatement">assertNotQueriesByStatement</a>
		</li>
		<li>
			<a href="#assertnotqueriesbystatementandaction">assertNotQueriesByStatementAndAction</a>
		</li>
		<li>
			<a href="#assertnotqueriesbystatementandfilter">assertNotQueriesByStatementAndFilter</a>
		</li>
		<li>
			<a href="#assertnotqueriesbystatementandfunction">assertNotQueriesByStatementAndFunction</a>
		</li>
		<li>
			<a href="#assertnotqueriesbystatementandmethod">assertNotQueriesByStatementAndMethod</a>
		</li>
		<li>
			<a href="#assertqueries">assertQueries</a>
		</li>
		<li>
			<a href="#assertqueriesbyaction">assertQueriesByAction</a>
		</li>
		<li>
			<a href="#assertqueriesbyfilter">assertQueriesByFilter</a>
		</li>
		<li>
			<a href="#assertqueriesbyfunction">assertQueriesByFunction</a>
		</li>
		<li>
			<a href="#assertqueriesbymethod">assertQueriesByMethod</a>
		</li>
		<li>
			<a href="#assertqueriesbystatement">assertQueriesByStatement</a>
		</li>
		<li>
			<a href="#assertqueriesbystatementandaction">assertQueriesByStatementAndAction</a>
		</li>
		<li>
			<a href="#assertqueriesbystatementandfilter">assertQueriesByStatementAndFilter</a>
		</li>
		<li>
			<a href="#assertqueriesbystatementandfunction">assertQueriesByStatementAndFunction</a>
		</li>
		<li>
			<a href="#assertqueriesbystatementandmethod">assertQueriesByStatementAndMethod</a>
		</li>
		<li>
			<a href="#assertqueriescountbyaction">assertQueriesCountByAction</a>
		</li>
		<li>
			<a href="#assertqueriescountbyfilter">assertQueriesCountByFilter</a>
		</li>
		<li>
			<a href="#assertqueriescountbyfunction">assertQueriesCountByFunction</a>
		</li>
		<li>
			<a href="#assertqueriescountbymethod">assertQueriesCountByMethod</a>
		</li>
		<li>
			<a href="#assertqueriescountbystatement">assertQueriesCountByStatement</a>
		</li>
		<li>
			<a href="#assertqueriescountbystatementandaction">assertQueriesCountByStatementAndAction</a>
		</li>
		<li>
			<a href="#assertqueriescountbystatementandfilter">assertQueriesCountByStatementAndFilter</a>
		</li>
		<li>
			<a href="#assertqueriescountbystatementandfunction">assertQueriesCountByStatementAndFunction</a>
		</li>
		<li>
			<a href="#assertqueriescountbystatementandmethod">assertQueriesCountByStatementAndMethod</a>
		</li>
		<li>
			<a href="#countqueries">countQueries</a>
		</li>
		<li>
			<a href="#getqueries">getQueries</a>
		</li>
	</ul>
</nav>

<h3>assertCountQueries</h3>

<hr>

<p>Asserts that n queries have been made.</p>
```php
$posts = $this->factory()->post->create_many(3);
  $cachedUsers = $this->factory()->user->create_many(2);
  $nonCachedUsers = $this->factory()->user->create_many(2);
  foreach($cachedUsers as $userId){
  wp_cache_set('page-posts-for-user-' . $userId, $posts, 'acme');
  }
  // Run the same query as different users
  foreach(array_merge($cachedUsers, $nonCachedUsers) as $userId){
  $pagePosts = $plugin->getPagePostsForUser($userId);
  }
  $I->assertCountQueries(2, 'A query should be made for each user missing cached posts.')
```

<h4>Parameters</h4>
<ul>
<li><code>int</code> <strong>$n</strong> - The expected number of queries.</li>
<li><code>string</code> <strong>$message</strong> - An optional message to override the default one.</li></ul>
  

<h3>assertNotQueries</h3>

<hr>

<p>Asserts that no queries were made. Queries generated by <code>setUp</code>, <code>tearDown</code> and <code>factory</code> methods are excluded by default.</p>
```php
$posts = $this->factory()->post->create_many(3);
  wp_cache_set('page-posts', $posts, 'acme');
  $pagePosts = $plugin->getPagePosts();
  $I->assertNotQueries('Queries should not be made if the cache is set.')
```

<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$message</strong> - An optional message to override the default one.</li></ul>
  

<h3>assertNotQueriesByAction</h3>

<hr>

<p>Asserts that no queries were made as a consequence of the specified action. Queries generated by <code>setUp</code>, <code>tearDown</code> and <code>factory</code> methods are excluded by default.</p>
```php
add_action( 'edit_post', function($postId){
  $count = get_option('acme_title_updates_count');
  update_option('acme_title_updates_count', ++$count);
  } );
  wp_delete_post($bookId);
  $this->assertNotQueriesByAction('edit_post');
```

<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$action</strong> - The action name, e.g. 'init'.</li>
<li><code>string</code> <strong>$message</strong> - An optional message to override the default one.</li></ul>
  

<h3>assertNotQueriesByFilter</h3>

<hr>

<p>Asserts that no queries were made as a consequence of the specified filter. Queries generated by <code>setUp</code>, <code>tearDown</code> and <code>factory</code> methods are excluded by default.</p>
```php
add_filter('the_title', function($title, $postId){
  $post = get_post($postId);
  if($post->post_type !== 'book'){
  return $title;
  }
  $new = get_option('acme_new_prefix');
  return "{$new} - " . $title;
  });
  $title = apply_filters('the_title', get_post($notABookId)->post_title, $notABookId);
  $this->assertNotQueriesByFilter('the_title');
```

<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$filter</strong> - The filter name, e.g. 'posts_where'.</li>
<li><code>string</code> <strong>$message</strong> - An optional message to override the default one.</li></ul>
  

<h3>assertNotQueriesByFunction</h3>

<hr>

<p>Asserts that no queries were made by the specified function. Queries generated by <code>setUp</code>, <code>tearDown</code> and <code>factory</code> methods are excluded by default.</p>
```php
$this->assertEmpty(Acme\get_orphaned_posts());
  Acme\delete_orphaned_posts();
  $this->assertNotQueriesByFunction('Acme\delete_orphaned_posts');
```

<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$function</strong> - The fully qualified name of the function to check.</li>
<li><code>string</code> <strong>$message</strong> - An optional message to override the default one.</li></ul>
  

<h3>assertNotQueriesByMethod</h3>

<hr>

<p>Asserts that no queries have been made by the specified class method. Queries generated by <code>setUp</code>, <code>tearDown</code> and <code>factory</code> methods are excluded by default.</p>
```php
$options = new Acme\Options();
  $options->update('adsSource', 'not-a-real-url.org');
  $I->assertNotQueriesByMethod('Acme\Options', 'update');
```

<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$class</strong> - The fully qualified name of the class to check.</li>
<li><code>string</code> <strong>$method</strong> - The name of the method to check.</li>
<li><code>string</code> <strong>$message</strong> - An optional message to override the default one.</li></ul>
  

<h3>assertNotQueriesByStatement</h3>

<hr>

<p>Asserts that no queries have been made by the specified class method. Queries generated by <code>setUp</code>, <code>tearDown</code> and <code>factory</code> methods are excluded by default.</p>
```php
$bookRepository = new Acme\BookRepository();
  $repository->where('ID', 23)->set('title', 'Peter Pan', $deferred = true);
  $this->assertNotQueriesByStatement('INSERT', 'Deferred write should happen on __destruct');
  Regular expressions must contain delimiters.
```

<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$statement</strong> - A simple string the statement should start with or a valid regular expression.</li>
<li><code>string</code> <strong>$message</strong> - An optional message to override the default one.</li></ul>
  

<h3>assertNotQueriesByStatementAndAction</h3>

<hr>

<p>Asserts that no queries were made as a consequence of the specified action containing the SQL query. Queries generated by <code>setUp</code>, <code>tearDown</code> and <code>factory</code> methods are excluded by default.</p>
```php
add_action( 'edit_post', function($postId){
  $count = get_option('acme_title_updates_count');
  update_option('acme_title_updates_count', ++$count);
  } );
  wp_delete_post($bookId);
  $this->assertNotQueriesByStatementAndAction('DELETE', 'delete_post');
  Regular expressions must contain delimiters.
```

<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$statement</strong> - A simple string the statement should start with or a valid regular expression.</li>
<li><code>string</code> <strong>$action</strong> - The action name, e.g. 'init'.</li>
<li><code>string</code> <strong>$message</strong> - An optional message to override the default one.</li></ul>
  

<h3>assertNotQueriesByStatementAndFilter</h3>

<hr>

<p>Asserts that no queries were made as a consequence of the specified filter containing the specified SQL query. Queries generated by <code>setUp</code>, <code>tearDown</code> and <code>factory</code> methods are excluded by default.</p>
```php
add_filter('the_title', function($title, $postId){
  $post = get_post($postId);
  if($post->post_type !== 'book'){
  return $title;
  }
  $new = get_option('acme_new_prefix');
  return "{$new} - " . $title;
  });
  $title = apply_filters('the_title', get_post($notABookId)->post_title, $notABookId);
  $this->assertNotQueriesByStatementAndFilter('SELECT', 'the_title');
  Regular expressions must contain delimiters.
```

<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$statement</strong> - A simple string the statement should start with or a valid regular expression.</li>
<li><code>string</code> <strong>$filter</strong> - The filter name, e.g. 'posts_where'.</li>
<li><code>string</code> <strong>$message</strong> - An optional message to override the default one.</li></ul>
  

<h3>assertNotQueriesByStatementAndFunction</h3>

<hr>

<p>Asserts that no queries were made by the specified function starting with the specified SQL statement. Queries generated by <code>setUp</code>, <code>tearDown</code> and <code>factory</code> methods are excluded by default.</p>
```php
wp_insert_post(['ID' => $bookId, 'post_title' => 'The Call of the Wild']);
  $this->assertNotQueriesByStatementAndFunction('INSERT', 'wp_insert_post');
  $this->assertQueriesByStatementAndFunction('UPDATE', 'wp_insert_post');
  Regular expressions must contain delimiters.
```

<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$statement</strong> - A simple string the statement should start with or a valid regular expression.</li>
<li><code>string</code> <strong>$function</strong> - The name of the function to check the assertions for.</li>
<li><code>string</code> <strong>$message</strong> - An optional message to override the default one.</li></ul>
  

<h3>assertNotQueriesByStatementAndMethod</h3>

<hr>

<p>Asserts that no queries were made by the specified class method starting with the specified SQL statement. Queries generated by <code>setUp</code>, <code>tearDown</code> and <code>factory</code> methods are excluded by default.</p>
```php
Acme\BookRepository::new(['title' => 'Alice in Wonderland'])->commit();
  $this->assertQueriesByStatementAndMethod('INSERT', Acme\BookRepository::class, 'commit');
  Regular expressions must contain delimiters.
```

<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$statement</strong> - A simple string the statement should start with or a valid regular expression.</li>
<li><code>string</code> <strong>$class</strong> - The fully qualified name of the class to check.</li>
<li><code>string</code> <strong>$method</strong> - The name of the method to check.</li>
<li><code>string</code> <strong>$message</strong> - An optional message to override the default one.</li></ul>
  

<h3>assertQueries</h3>

<hr>

<p>Asserts that at least one query was made during the test. Queries generated by <code>setUp</code>, <code>tearDown</code> and <code>factory</code> methods are excluded by default.</p>
```php
wp_cache_delete('page-posts', 'acme');
  $pagePosts = $plugin->getPagePosts();
  $I->assertQueries('Queries should be made to set the cache.')
```

<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$message</strong> - An optional message to override the default one.</li></ul>
  

<h3>assertQueriesByAction</h3>

<hr>

<p>Asserts that at least one query was made as a consequence of the specified action. Queries generated by <code>setUp</code>, <code>tearDown</code> and <code>factory</code> methods are excluded by default.</p>
```php
add_action( 'edit_post', function($postId){
  $count = get_option('acme_title_updates_count');
  update_option('acme_title_updates_count', ++$count);
  } );
  wp_update_post(['ID' => $bookId, 'post_title' => 'New Title']);
  $this->assertQueriesByAction('edit_post');
```

<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$action</strong> - The action name, e.g. 'init'.</li>
<li><code>string</code> <strong>$message</strong> - An optional message to override the default one.</li></ul>
  

<h3>assertQueriesByFilter</h3>

<hr>

<p>Asserts that at least one query was made as a consequence of the specified filter. Queries generated by <code>setUp</code>, <code>tearDown</code> and <code>factory</code> methods are excluded by default.</p>
```php
add_filter('the_title', function($title, $postId){
  $post = get_post($postId);
  if($post->post_type !== 'book'){
  return $title;
  }
  $new = get_option('acme_new_prefix');
  return "{$new} - " . $title;
  });
  $title = apply_filters('the_title', get_post($bookId)->post_title, $bookId);
  $this->assertQueriesByFilter('the_title');
```

<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$filter</strong> - The filter name, e.g. 'posts_where'.</li>
<li><code>string</code> <strong>$message</strong> - An optional message to override the default one.</li></ul>
  

<h3>assertQueriesByFunction</h3>

<hr>

<p>Asserts that queries were made by the specified function. Queries generated by <code>setUp</code>, <code>tearDown</code> and <code>factory</code> methods are excluded by default.</p>
```php
acme_clean_queue();
  $this->assertQueriesByFunction('acme_clean_queue');
```

<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$function</strong> - The fully qualified name of the function to check.</li>
<li><code>string</code> <strong>$message</strong> - An optional message to override the default one.</li></ul>
  

<h3>assertQueriesByMethod</h3>

<hr>

<p>Asserts that at least one query has been made by the specified class method. Queries generated by <code>setUp</code>, <code>tearDown</code> and <code>factory</code> methods are excluded by default.</p>
```php
$options = new Acme\Options();
  $options->update('showAds', false);
  $I->assertQueriesByMethod('Acme\Options', 'update');
```

<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$class</strong> - The fully qualified name of the class to check.</li>
<li><code>string</code> <strong>$method</strong> - The name of the method to check.</li>
<li><code>string</code> <strong>$message</strong> - An optional message to override the default one.</li></ul>
  

<h3>assertQueriesByStatement</h3>

<hr>

<p>Asserts that at least a query starting with the specified statement was made. Queries generated by <code>setUp</code>, <code>tearDown</code> and <code>factory</code> methods are excluded by default.</p>
```php
wp_cache_flush();
  cached_get_posts($args);
  $I->assertQueriesByStatement('SELECT');
  Regular expressions must contain delimiters.
```

<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$statement</strong> - A simple string the statement should start with or a valid regular expression.</li>
<li><code>string</code> <strong>$message</strong> - An optional message to override the default one.</li></ul>
  

<h3>assertQueriesByStatementAndAction</h3>

<hr>

<p>Asserts that at least one query was made as a consequence of the specified action containing the SQL query. Queries generated by <code>setUp</code>, <code>tearDown</code> and <code>factory</code> methods are excluded by default.</p>
```php
add_action( 'edit_post', function($postId){
  $count = get_option('acme_title_updates_count');
  update_option('acme_title_updates_count', ++$count);
  } );
  wp_update_post(['ID' => $bookId, 'post_title' => 'New']);
  $this->assertQueriesByStatementAndAction('UPDATE', 'edit_post');
  Regular expressions must contain delimiters.
```

<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$statement</strong> - A simple string the statement should start with or a valid regular expression.</li>
<li><code>string</code> <strong>$action</strong> - The action name, e.g. 'init'.</li>
<li><code>string</code> <strong>$message</strong> - An optional message to override the default one.</li></ul>
  

<h3>assertQueriesByStatementAndFilter</h3>

<hr>

<p>Asserts that at least one query was made as a consequence of the specified filter containing the SQL query. Queries generated by <code>setUp</code>, <code>tearDown</code> and <code>factory</code> methods are excluded by default.</p>
```php
add_filter('the_title', function($title, $postId){
  $post = get_post($postId);
  if($post->post_type !== 'book'){
  return $title;
  }
  $new = get_option('acme_new_prefix');
  return "{$new} - " . $title;
  });
  $title = apply_filters('the_title', get_post($bookId)->post_title, $bookId);
  $this->assertQueriesByStatementAndFilter('SELECT', 'the_title');
  Regular expressions must contain delimiters.
```

<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$statement</strong> - A simple string the statement should start with or a valid regular expression.</li>
<li><code>string</code> <strong>$filter</strong> - The filter name, e.g. 'posts_where'.</li>
<li><code>string</code> <strong>$message</strong> - An optional message to override the default one.</li></ul>
  

<h3>assertQueriesByStatementAndFunction</h3>

<hr>

<p>Asserts that queries were made by the specified function starting with the specified SQL statement. Queries generated by <code>setUp</code>, <code>tearDown</code> and <code>factory</code> methods are excluded by default.</p>
```php
wp_insert_post(['post_type' => 'book', 'post_title' => 'Alice in Wonderland']);
  $this->assertQueriesByStatementAndFunction('INSERT', 'wp_insert_post');
  Regular expressions must contain delimiters.
```

<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$statement</strong> - A simple string the statement should start with or a valid regular expression.</li>
<li><code>string</code> <strong>$function</strong> - The fully qualified function name.</li>
<li><code>string</code> <strong>$message</strong> - An optional message to override the default one.</li></ul>
  

<h3>assertQueriesByStatementAndMethod</h3>

<hr>

<p>Asserts that queries were made by the specified class method starting with the specified SQL statement. Queries generated by <code>setUp</code>, <code>tearDown</code> and <code>factory</code> methods are excluded by default.</p>
```php
Acme\BookRepository::new(['title' => 'Alice in Wonderland'])->commit();
  $this->assertQueriesByStatementAndMethod('UPDATE', Acme\BookRepository::class, 'commit');
  Regular expressions must contain delimiters.
```

<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$statement</strong> - A simple string the statement should start with or a valid regular expression.</li>
<li><code>string</code> <strong>$class</strong> - The fully qualified name of the class to check.</li>
<li><code>string</code> <strong>$method</strong> - The name of the method to check.</li>
<li><code>string</code> <strong>$message</strong> - An optional message to override the default one.</li></ul>
  

<h3>assertQueriesCountByAction</h3>

<hr>

<p>Asserts that n queries were made as a consequence of the specified action. Queries generated by <code>setUp</code>, <code>tearDown</code> and <code>factory</code> methods are excluded by default.</p>
```php
add_action( 'edit_post', function($postId){
  $count = get_option('acme_title_updates_count');
  update_option('acme_title_updates_count', ++$count);
  } );
  wp_update_post(['ID' => $bookOneId, 'post_title' => 'One']);
  wp_update_post(['ID' => $bookTwoId, 'post_title' => 'Two']);
  wp_update_post(['ID' => $bookThreeId, 'post_title' => 'Three']);
  $this->assertQueriesCountByAction(3, 'edit_post');
```

<h4>Parameters</h4>
<ul>
<li><code>int</code> <strong>$n</strong> - The expected number of queries.</li>
<li><code>string</code> <strong>$action</strong> - The action name, e.g. 'init'.</li>
<li><code>string</code> <strong>$message</strong> - An optional message to override the default one.</li></ul>
  

<h3>assertQueriesCountByFilter</h3>

<hr>

<p>Asserts that n queries were made as a consequence of the specified filter. Queries generated by <code>setUp</code>, <code>tearDown</code> and <code>factory</code> methods are excluded by default.</p>
```php
add_filter('the_title', function($title, $postId){
  $post = get_post($postId);
  if($post->post_type !== 'book'){
  return $title;
  }
  $new = get_option('acme_new_prefix');
  return "{$new} - " . $title;
  });
  $title = apply_filters('the_title', get_post($bookOneId)->post_title, $bookOneId);
  $title = apply_filters('the_title', get_post($notABookId)->post_title, $notABookId);
  $title = apply_filters('the_title', get_post($bookTwoId)->post_title, $bookTwoId);
  $this->assertQueriesCountByFilter(2, 'the_title');
```

<h4>Parameters</h4>
<ul>
<li><code>int</code> <strong>$n</strong> - The expected number of queries.</li>
<li><code>string</code> <strong>$filter</strong> - The filter name, e.g. 'posts_where'.</li>
<li><code>string</code> <strong>$message</strong> - An optional message to override the default one.</li></ul>
  

<h3>assertQueriesCountByFunction</h3>

<hr>

<p>Asserts that n queries were made by the specified function. Queries generated by <code>setUp</code>, <code>tearDown</code> and <code>factory</code> methods are excluded by default.</p>
```php
$this->assertCount(3, Acme\get_orphaned_posts());
  Acme\delete_orphaned_posts();
  $this->assertQueriesCountByFunction(3, 'Acme\delete_orphaned_posts');
```

<h4>Parameters</h4>
<ul>
<li><code>int</code> <strong>$n</strong> - The expected number of queries.</li>
<li><code>string</code> <strong>$function</strong> - The function to check the queries for.</li>
<li><code>string</code> <strong>$message</strong> - An optional message to override the default one.</li></ul>
  

<h3>assertQueriesCountByMethod</h3>

<hr>

<p>Asserts that n queries have been made by the specified class method. Queries generated by <code>setUp</code>, <code>tearDown</code> and <code>factory</code> methods are excluded by default.</p>
```php
$bookRepository = new Acme\BookRepository();
  $repository->where('ID', 23)->commit('title', 'Peter Pan');
  $repository->where('ID', 89)->commit('title', 'Moby-dick');
  $repository->where('ID', 2389)->commit('title', 'The call of the wild');
  $this->assertQueriesCountByMethod(3, 'Acme\BookRepository', 'commit');
```

<h4>Parameters</h4>
<ul>
<li><code>int</code> <strong>$n</strong> - The expected number of queries.</li>
<li><code>string</code> <strong>$class</strong> - The fully qualified name of the class to check.</li>
<li><code>string</code> <strong>$method</strong> - The name of the method to check.</li>
<li><code>string</code> <strong>$message</strong> - An optional message to override the default one.</li></ul>
  

<h3>assertQueriesCountByStatement</h3>

<hr>

<p>Asserts that n queries starting with the specified statement were made. Queries generated by <code>setUp</code>, <code>tearDown</code> and <code>factory</code> methods are excluded by default.</p>
```php
$bookRepository = new Acme\BookRepository();
  $repository->where('ID', 23)->set('title', 'Peter Pan', $deferred = true);
  $repository->where('ID', 89)->set('title', 'Moby-dick', $deferred = true);
  $repository->where('ID', 2389)->set('title', 'The call of the wild', $deferred = false);
  $this->assertQueriesCountByStatement(1, 'INSERT', 'Deferred write should happen on __destruct');
  Regular expressions must contain delimiters.
```

<h4>Parameters</h4>
<ul>
<li><code>int</code> <strong>$n</strong> - The expected number of queries.</li>
<li><code>string</code> <strong>$statement</strong> - A simple string the statement should start with or a valid regular expression.</li>
<li><code>string</code> <strong>$message</strong> - An optional message to override the default one.</li></ul>
  

<h3>assertQueriesCountByStatementAndAction</h3>

<hr>

<p>Asserts that n queries were made as a consequence of the specified action containing the specified SQL statement. Queries generated by <code>setUp</code>, <code>tearDown</code> and <code>factory</code> methods are excluded by default.</p>
```php
add_action( 'edit_post', function($postId){
  $count = get_option('acme_title_updates_count');
  update_option('acme_title_updates_count', ++$count);
  } );
  wp_delete_post($bookOneId);
  wp_delete_post($bookTwoId);
  wp_update_post(['ID' => $bookThreeId, 'post_title' => 'New']);
  $this->assertQueriesCountByStatementAndAction(2, 'DELETE', 'delete_post');
  $this->assertQueriesCountByStatementAndAction(1, 'INSERT', 'edit_post');
  Regular expressions must contain delimiters.
```

<h4>Parameters</h4>
<ul>
<li><code>int</code> <strong>$n</strong> - The expected number of queries.</li>
<li><code>string</code> <strong>$statement</strong> - A simple string the statement should start with or a valid regular expression.</li>
<li><code>string</code> <strong>$action</strong> - The action name, e.g. 'init'.</li>
<li><code>string</code> <strong>$message</strong> - An optional message to override the default one.</li></ul>
  

<h3>assertQueriesCountByStatementAndFilter</h3>

<hr>

<p>Asserts that n queries were made as a consequence of the specified filter containing the specified SQL statement. Queries generated by <code>setUp</code>, <code>tearDown</code> and <code>factory</code> methods are excluded by default.</p>
```php
add_filter('the_title', function($title, $postId){
  $post = get_post($postId);
  if($post->post_type !== 'book'){
  return $title;
  }
  $new = get_option('acme_new_prefix');
  return "{$new} - " . $title;
  });
  // Warm up the cache.
  $title = apply_filters('the_title', get_post($bookOneId)->post_title, $bookOneId);
  // Cache is warmed up now.
  $title = apply_filters('the_title', get_post($bookTwoId)->post_title, $bookTwoId);
  $title = apply_filters('the_title', get_post($bookThreeId)->post_title, $bookThreeId);
  $this->assertQueriesCountByStatementAndFilter(1, 'SELECT', 'the_title');
  Regular expressions must contain delimiters.
```

<h4>Parameters</h4>
<ul>
<li><code>int</code> <strong>$n</strong> - The expected number of queries.</li>
<li><code>string</code> <strong>$statement</strong> - A simple string the statement should start with or a valid regular expression.</li>
<li><code>string</code> <strong>$filter</strong> - The filter name, e.g. 'posts_where'.</li>
<li><code>string</code> <strong>$message</strong> - An optional message to override the default one.</li></ul>
  

<h3>assertQueriesCountByStatementAndFunction</h3>

<hr>

<p>Asserts that n queries were made by the specified function starting with the specified SQL statement. Queries generated by <code>setUp</code>, <code>tearDown</code> and <code>factory</code> methods are excluded by default.</p>
```php
wp_insert_post(['post_type' => 'book', 'post_title' => 'The Call of the Wild']);
  wp_insert_post(['post_type' => 'book', 'post_title' => 'Alice in Wonderland']);
  wp_insert_post(['post_type' => 'book', 'post_title' => 'The Chocolate Factory']);
  $this->assertQueriesCountByStatementAndFunction(3, 'INSERT', 'wp_insert_post');
  Regular expressions must contain delimiters.
```

<h4>Parameters</h4>
<ul>
<li><code>int</code> <strong>$n</strong> - The expected number of queries.</li>
<li><code>string</code> <strong>$statement</strong> - A simple string the statement should start with or a valid regular expression.</li>
<li><code>string</code> <strong>$function</strong> - The fully-qualified function name.</li>
<li><code>string</code> <strong>$message</strong> - An optional message to override the default one.</li></ul>
  

<h3>assertQueriesCountByStatementAndMethod</h3>

<hr>

<p>Asserts that n queries were made by the specified class method starting with the specified SQL statement. Queries generated by <code>setUp</code>, <code>tearDown</code> and <code>factory</code> methods are excluded by default.</p>
```php
Acme\BookRepository::new(['title' => 'Alice in Wonderland'])->commit();
  Acme\BookRepository::new(['title' => 'Moby-Dick'])->commit();
  Acme\BookRepository::new(['title' => 'The Call of the Wild'])->commit();
  $this->assertQueriesCountByStatementAndMethod(3, 'INSERT', Acme\BookRepository::class, 'commit');
  Regular expressions must contain delimiters.
```

<h4>Parameters</h4>
<ul>
<li><code>int</code> <strong>$n</strong> - The expected number of queries.</li>
<li><code>string</code> <strong>$statement</strong> - A simple string the statement should start with or a valid regular expression.</li>
<li><code>string</code> <strong>$class</strong> - The fully qualified name of the class to check.</li>
<li><code>string</code> <strong>$method</strong> - The name of the method to check.</li>
<li><code>string</code> <strong>$message</strong> - An optional message to override the default one.</li></ul>
  

<h3>countQueries</h3>

<hr>

<p>Returns the current number of queries. Set-up and tear-down queries performed by the test case are filtered out.</p>
```php
// In a WPTestCase, using the global $wpdb object.
  $queriesCount = $this->queries()->countQueries();
  // In a WPTestCase, using a custom $wpdb object.
  $queriesCount = $this->queries()->countQueries($customWdbb);
```

<h4>Parameters</h4>
<ul>
<li><code>\wpdb/null</code> <strong>$wpdb</strong> - A specific instance of the <code>wpdb</code> class or <code>null</code> to use the global one.</li></ul>
  

<h3>getQueries</h3>

<hr>

<p>Returns the queries currently performed by the global database object or the specified one. Set-up and tear-down queries performed by the test case are filtered out.</p>
```php
// In a WPTestCase, using the global $wpdb object.
  $queries = $this->queries()->getQueries();
  // In a WPTestCase, using a custom $wpdb object.
  $queries = $this->queries()->getQueries($customWdbb);
```

<h4>Parameters</h4>
<ul>
<li><code>null/\wpdb</code> <strong>$wpdb</strong> - A specific instance of the <code>wpdb</code> class or <code>null</code> to use the global one.</li></ul>


*This class extends \Codeception\Module*

<!--/doc-->

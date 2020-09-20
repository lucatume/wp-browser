<?php

namespace Codeception\Command;

use Codeception\CustomCommandInterface;
use Codeception\Lib\Generator\WPUnit;

class GenerateWPRestController extends GenerateWPUnit implements CustomCommandInterface
{
    use Shared\FileSystem;
    use Shared\Config;

    /**
     * Returns the command description.
     *
     * @return string The command description.
     */
    public function getDescription()
    {
        return 'Generates a WPRestApiTestCase: a WP_Test_REST_Controller_Testcase '
               . 'extension with Codeception super-powers.';
    }

    /**
     * Returns the test case generator.
     *
     * @param array<string,mixed>  $config The generator configuration.
     * @param string $class The class to generate the test case for.
     *
     * @return WPUnit The generator instance.
     */
    protected function getGenerator($config, $class)
    {
        return new WPUnit($config, $class, '\\Codeception\\TestCase\\WPRestControllerTestCase');
    }

    /**
     * Returns the name of the command.
     *
     * @return string The commmand name.
     */
    public static function getCommandName()
    {
        return 'generate:wprestcontroller';
    }
}

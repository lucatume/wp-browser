<?php

namespace Codeception\Command;

use Codeception\CustomCommandInterface;
use Codeception\Lib\Generator\WPUnit;

class GenerateWPRestPostTypeController extends GenerateWPUnit implements CustomCommandInterface
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
        return 'Generates a WPRestApiTestCase: a WP_Test_REST_Post_Type_Controller_Testcase extension '
               . 'with Codeception super-powers.';
    }

    /**
     * Returns the command generator.
     *
     * @param array<string,mixed>  $config The generator configuration.
     * @param string $class The class to generate the template for.
     *
     * @return WPUnit The generator instance.
     */
    protected function getGenerator($config, $class)
    {
        return new WPUnit($config, $class, '\\Codeception\\TestCase\\WPRestPostTypeControllerTestCase');
    }

    /**
     * Returns the name of the command
     *
     * @return string The command name.
     */
    public static function getCommandName()
    {
        return 'generate:wprestposttypecontroller';
    }
}

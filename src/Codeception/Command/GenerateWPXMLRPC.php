<?php

namespace Codeception\Command;

use Codeception\CustomCommandInterface;
use Codeception\Lib\Generator\WPUnit;

class GenerateWPXMLRPC extends GenerateWPUnit implements CustomCommandInterface
{
    use Shared\FileSystem;
    use Shared\Config;

    /**
     * Returns the command name.
     *
     * @return string The command name.
     */
    public static function getCommandName()
    {
        return 'generate:wpxmlrpc';
    }

    /**
     * Returns the generator description.
     *
     * @return string The generator description.
     */
    public function getDescription()
    {
        return 'Generates a WPXMLRPCTestCase: a WP_XMLRPC_UnitTestCase extension with Codeception super-powers.';
    }

    /**
     * Returns the generator for the test case.
     *
     * @param array<string,mixed>  $config The current generator configuration.
     * @param string $class The class to generate the test case for.
     *
     * @return WPUnit The generator.
     */
    protected function getGenerator($config, $class)
    {
        return new WPUnit($config, $class, '\\Codeception\\TestCase\\WPXMLRPCTestCase');
    }
}

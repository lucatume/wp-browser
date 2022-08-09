<?php

namespace lucatume\WPBrowser\Command;

use Codeception\Command\Shared\ConfigTrait;
use Codeception\Command\Shared\FileSystemTrait;
use Codeception\CustomCommandInterface;
use lucatume\WPBrowser\Lib\Generator\WPUnit;
use lucatume\WPBrowser\TestCase\WPXMLRPCTestCase;

class GenerateWPXMLRPC extends GenerateWPUnit implements CustomCommandInterface
{
    use FileSystemTrait;
    use ConfigTrait;

    /**
     * Returns the command name.
     *
     * @return string The command name.
     */
    public static function getCommandName(): string
    {
        return 'generate:wpxmlrpc';
    }

    /**
     * Returns the generator description.
     *
     * @return string The generator description.
     */
    public function getDescription(): string
    {
        return 'Generates a WPXMLRPCTestCase: a WP_XMLRPC_UnitTestCase extension with Codeception super-powers.';
    }

    /**
     * Returns the generator for the test case.
     *
     * @param array $config The current generator configuration.
     * @param string $class The class to generate the test case for.
     *
     * @return \lucatume\WPBrowser\Lib\Generator\WPUnit The generator.
     */
    protected function getGenerator(array $config, string $class): WPUnit
    {
        return new WPUnit($config, $class, WPXMLRPCTestCase::class);
    }
}

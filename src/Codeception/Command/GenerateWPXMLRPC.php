<?php

namespace Codeception\Command;

use Codeception\CustomCommandInterface;
use Codeception\Lib\Generator\WPUnit;

class GenerateWPXMLRPC extends GenerateWPUnit implements CustomCommandInterface
{

    use Shared\FileSystem;
    use Shared\Config;

    public static function getCommandName()
    {
        return 'generate:wpxmlrpc';
    }

    public function getDescription()
    {
        return 'Generates a WPXMLRPCTestCase: a WP_XMLRPC_UnitTestCase extension with Codeception super-powers.';
    }

    protected function getGenerator($config, $class)
    {
        return new WPUnit($config, $class, '\\Codeception\\TestCase\\WPXMLRPCTestCase');
    }
}

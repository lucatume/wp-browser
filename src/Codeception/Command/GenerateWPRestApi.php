<?php

namespace Codeception\Command;

use Codeception\CustomCommandInterface;
use Codeception\Lib\Generator\WPUnit;

class GenerateWPRestApi extends GenerateWPUnit implements CustomCommandInterface
{
    use Shared\FileSystem;
    use Shared\Config;

    public function getDescription()
    {
        return 'Generates a WPRestApiTestCase: a WP_Test_REST_TestCase extension with Codeception super-powers.';
    }

    protected function getGenerator($config, $class)
    {
        return new WPUnit($config, $class, '\\Codeception\\TestCase\\WPRestApiTestCase');
    }

    /**
     * returns the name of the command
     *
     * @return string
     */
    public static function getCommandName()
    {
        return 'generate:wprest';
    }
}

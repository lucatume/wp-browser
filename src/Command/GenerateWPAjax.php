<?php

namespace lucatume\WPBrowser\Command;

use Codeception\CustomCommandInterface;
use lucatume\WPBrowser\Lib\Generator\WPUnit;

class GenerateWPAjax extends GenerateWPUnit implements CustomCommandInterface
{
    /**
     * Returns the command description.
     *
     * @return string The command description.
     */
    public function getDescription(): string
    {
        return 'Generates a WPAjaxTestCase: a WP_Ajax_UnitTestCase extension with Codeception super-powers.';
    }

    /**
     * Returns the built generator.
     *
     * @param array $config The generator configuration.
     * @param string $class The class to generate the test case for.
     *
     * @return WPUnit The built generator.
     */
    protected function getGenerator(array $config, string $class): \lucatume\WPBrowser\Lib\Generator\WPUnit
    {
        return new WPUnit($config, $class, '\\lucatume\\WPBrowser\\TestCase\\WPAjaxTestCase');
    }

    /**
     * Returns the name of the command.
     *
     * @return string The command name.
     */
    public static function getCommandName(): string
    {

        return 'generate:wpajax';
    }
}

<?php

namespace lucatume\WPBrowser\Command;

use lucatume\WPBrowser\Lib\Generator\WPUnit;
use lucatume\WPBrowser\TestCase\WPAjaxTestCase;

class GenerateWPAjax extends GenerateWPUnit
{
    /**
     * Returns the command description.
     *
     * @return string The command description.
     */
    public function getDescription(): string
    {
        return 'Generates a WPAjaxTestCase: a WP_Ajax_UnitTestCase with Codeception super-powers.';
    }

    /**
     * Returns the built generator.
     *
     * @param array<string,mixed> $config The generator configuration.
     * @param string $class The class to generate the test case for.
     *
     * @return WPUnit The built generator.
     */
    protected function getGenerator(array $config, string $class): \lucatume\WPBrowser\Lib\Generator\WPUnit
    {
        return new WPUnit($config, $class, WPAjaxTestCase::class);
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

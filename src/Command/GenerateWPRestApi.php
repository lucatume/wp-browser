<?php

namespace lucatume\WPBrowser\Command;

use lucatume\WPBrowser\Lib\Generator\WPUnit;
use lucatume\WPBrowser\TestCase\WPRestApiTestCase;

class GenerateWPRestApi extends GenerateWPUnit
{
    /**
     * Returns the command description.
     *
     * @return string The command description.
     */
    public function getDescription(): string
    {
        return 'Generates a WPRestApiTestCase: a WP_Test_REST_TestCase with Codeception super-powers.';
    }

    /**
     * Builds and returns the test case generator.
     *
     * @param array<string,mixed> $config The generator configuration.
     * @param string $class The class to generate the test case for.
     *
     * @return WPUnit The built generator.
     */
    protected function getGenerator(array $config, string $class): WPUnit
    {
        return new WPUnit($config, $class, WPRestApiTestCase::class);
    }

    /**
     * Returns the name of the command.
     *
     * @return string The command name.
     */
    public static function getCommandName(): string
    {
        return 'generate:wprest';
    }
}

<?php

namespace Codeception\Command;

use Codeception\Command\Shared\ConfigTrait;
use Codeception\Command\Shared\FileSystemTrait;
use Codeception\CustomCommandInterface;
use Codeception\Lib\Generator\WPUnit;
use Codeception\TestCase\WPRestApiTestCase;

class GenerateWPRestApi extends GenerateWPUnit implements CustomCommandInterface
{
    use FileSystemTrait;
    use ConfigTrait;

    /**
     * Returns the command description.
     *
     * @return string The command description.
     */
    public function getDescription(): string
    {
        return 'Generates a WPRestApiTestCase: a WP_Test_REST_TestCase extension with Codeception super-powers.';
    }

    /**
     * Builds and returns the test case generator.
     *
     * @param array $config The generator configuration.
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

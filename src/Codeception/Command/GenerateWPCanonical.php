<?php

namespace Codeception\Command;

use Codeception\CustomCommandInterface;
use Codeception\Lib\Generator\WPUnit;

class GenerateWPCanonical extends GenerateWPUnit implements CustomCommandInterface
{

    /**
     * Returns the command description.
     *
     * @return string The command description.
     */
    public function getDescription()
    {
        return 'Generates a WPCanonicalTestCase: a WP_Canonical_UnitTestCase extension with Codeception super-powers.';
    }

    /**
     * Returns the built generator.
     *
     * @param array<string,mixed>  $config The generator configuration.
     * @param string $class The class to generate the test case for.
     *
     * @return WPUnit The built generator.
     */
    protected function getGenerator($config, $class)
    {
        return new WPUnit($config, $class, '\\Codeception\\TestCase\\WPCanonicalTestCase');
    }

    /**
     * Returns the name of the command.
     *
     * @return string The command name.
     */
    public static function getCommandName()
    {
        return 'generate:wpcanonical';
    }
}

<?php

namespace Codeception\Command;

use Codeception\CustomCommandInterface;
use Codeception\Lib\Generator\WPUnit;

class GenerateWPCanonical extends GenerateWPUnit implements CustomCommandInterface
{
    public function getDescription()
    {
        return 'Generates a WPCanonicalTestCase: a WP_Canonical_UnitTestCase extension with Codeception super-powers.';
    }

    protected function getGenerator($config, $class)
    {
        return new WPUnit($config, $class, '\\Codeception\\TestCase\\WPCanonicalTestCase');
    }

    /**
     * returns the name of the command
     *
     * @return string
     */
    public static function getCommandName()
    {
        return 'generate:wpcanonical';
    }
}

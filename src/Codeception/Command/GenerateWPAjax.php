<?php

namespace Codeception\Command;

use Codeception\CustomCommandInterface;
use Codeception\Lib\Generator\WPUnit;

class GenerateWPAjax extends GenerateWPUnit implements CustomCommandInterface
{

    public function getDescription()
    {
        return 'Generates a WPAjaxTestCase: a WP_Ajax_UnitTestCase extension with Codeception super-powers.';
    }

    protected function getGenerator($config, $class)
    {
        return new WPUnit($config, $class, '\\Codeception\\TestCase\\WPAjaxTestCase');
    }

    /**
     * returns the name of the command
     *
     * @return string
     */
    public static function getCommandName()
    {

        return 'generate:wpajax';
    }
}

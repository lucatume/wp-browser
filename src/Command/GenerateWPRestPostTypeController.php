<?php

namespace lucatume\WPBrowser\Command;

use lucatume\WPBrowser\Lib\Generator\AbstractGenerator;
use lucatume\WPBrowser\Lib\Generator\WPRestPostTypeController;

class GenerateWPRestPostTypeController extends GenerateWPUnit
{
    public static function getCommandName(): string
    {
        return "generate:wprestpostcontroller";
    }

    public function getDescription(): string
    {
        return 'Generates a WPRestPostTypeControllerTestCase: a test case with Codeception super-powers ' .
            'to test REST post controllers.';
    }

    /**
     * @param array{namespace: string, actor: string} $config The generator configuration.
     */
    protected function getGenerator(array $config, string $class): AbstractGenerator
    {
        return new WPRestPostTypeController($config, $class);
    }
}

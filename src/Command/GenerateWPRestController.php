<?php

namespace lucatume\WPBrowser\Command;

use lucatume\WPBrowser\Lib\Generator\AbstractGenerator;
use lucatume\WPBrowser\Lib\Generator\WPRestController;

class GenerateWPRestController extends GenerateWPUnit
{
    public static function getCommandName(): string
    {
        return "generate:wprestcontroller";
    }

    public function getDescription(): string
    {
        return 'Generates a WPRestControllerTestCase: a test case with Codeception super-powers ' .
            'to test REST controllers.';
    }

    /**
     * @param array{namespace: string, actor: string} $config The generator configuration.
     */
    protected function getGenerator(array $config, string $class): AbstractGenerator
    {
        return new WPRestController($config, $class);
    }
}

<?php

namespace lucatume\WPBrowser\Command;

use lucatume\WPBrowser\Lib\Generator\AbstractGenerator;
use lucatume\WPBrowser\Lib\Generator\WPRestApi;

class GenerateWPRestApi extends GenerateWPUnit
{
    public static function getCommandName(): string
    {
        return "generate:wprestapi";
    }

    public function getDescription(): string
    {
        return 'Generates a WPRestApiTestCase: a test case with Codeception super-powers to test REST API handling.';
    }

    /**
     * @param array{namespace: string, actor: string} $config The generator configuration.
     */
    protected function getGenerator(array $config, string $class): AbstractGenerator
    {
        return new WPRestApi($config, $class);
    }
}

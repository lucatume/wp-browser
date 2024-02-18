<?php

namespace lucatume\WPBrowser\Command;

use lucatume\WPBrowser\Lib\Generator\AbstractGenerator;
use lucatume\WPBrowser\Lib\Generator\WPAjax;

class GenerateWPAjax extends GenerateWPUnit
{
    public static function getCommandName(): string
    {
        return "generate:wpajax";
    }

    public function getDescription(): string
    {
        return 'Generates a WPAjaxTestCase: a test case with Codeception super-powers to test AJAX handling.';
    }

    /**
     * @param array{namespace: string, actor: string} $config The generator configuration.
     */
    protected function getGenerator(array $config, string $class): AbstractGenerator
    {
        return new WPAjax($config, $class);
    }
}

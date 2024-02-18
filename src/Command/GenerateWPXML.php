<?php

namespace lucatume\WPBrowser\Command;

use lucatume\WPBrowser\Lib\Generator\AbstractGenerator;
use lucatume\WPBrowser\Lib\Generator\WPXML;

class GenerateWPXML extends GenerateWPUnit
{
    public static function getCommandName(): string
    {
        return "generate:wpxml";
    }

    public function getDescription(): string
    {
        return 'Generates a WPXMLTestCase: a test case with Codeception super-powers ' .
            'to test XML data produced by the site.';
    }

    /**
     * @param array{namespace: string, actor: string} $config The generator configuration.
     */
    protected function getGenerator(array $config, string $class): AbstractGenerator
    {
        return new WPXML($config, $class);
    }
}

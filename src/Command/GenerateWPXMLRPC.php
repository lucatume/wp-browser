<?php

namespace lucatume\WPBrowser\Command;

use lucatume\WPBrowser\Lib\Generator\AbstractGenerator;
use lucatume\WPBrowser\Lib\Generator\WPXMLRPC;

class GenerateWPXMLRPC extends GenerateWPUnit
{
    public static function getCommandName(): string
    {
        return "generate:wpxmlrpc";
    }

    public function getDescription(): string
    {
        return 'Generates a WPXMLRPCTestCase: a test case with Codeception super-powers ' .
            'to test XML-RPC handling.';
    }

    /**
     * @param array{namespace: string, actor: string} $config The generator configuration.
     */
    protected function getGenerator(array $config, string $class): AbstractGenerator
    {
        return new WPXMLRPC($config, $class);
    }
}

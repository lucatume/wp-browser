<?php

namespace lucatume\WPBrowser\Command;

use lucatume\WPBrowser\Lib\Generator\AbstractGenerator;
use lucatume\WPBrowser\Lib\Generator\WPCanonical;

class GenerateWPCanonical extends GenerateWPUnit
{
    public static function getCommandName(): string
    {
        return "generate:wpcanonical";
    }

    public function getDescription(): string
    {
        return 'Generates a WPCanonicalTestCase: a test case with Codeception super-powers to test rewrite handling.';
    }

    /**
     * @param array{namespace: string, actor: string} $config The generator configuration.
     */
    protected function getGenerator(array $config, string $class): AbstractGenerator
    {
        return new WPCanonical($config, $class);
    }
}

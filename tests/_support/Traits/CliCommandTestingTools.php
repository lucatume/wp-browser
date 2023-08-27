<?php

namespace lucatume\WPBrowser\Tests\Traits;

use Symfony\Component\Console\Input\StringInput;

trait CliCommandTestingTools
{
    protected function buildInteractiveInput(array $answers): StringInput
    {
        $input = new StringInput('');
        $inputStream = fopen('php://memory', 'rwb');
        fwrite($inputStream, implode(PHP_EOL, $answers));
        $input->setStream($inputStream);
        return $input;
    }
}

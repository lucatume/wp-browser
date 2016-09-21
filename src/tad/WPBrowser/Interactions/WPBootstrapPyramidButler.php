<?php

namespace tad\WPBrowser\Interactions;


use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use tad\WPBrowser\Console\Output\PyramidWordsOutput;

class WPBootstrapPyramidButler extends WPBootsrapButler
{
    public function askQuestions($helper, InputInterface $input, OutputInterface $output, $verbose = true)
    {
        $output = new PyramidWordsOutput($output);
        return parent::askQuestions($helper, $input, $output, $verbose);
    }
}

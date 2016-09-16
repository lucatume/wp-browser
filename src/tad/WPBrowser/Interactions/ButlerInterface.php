<?php

namespace tad\WPBrowser\Interactions;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

interface ButlerInterface
{
    /**
     * @param mixed $helper A question helper
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return array
     */
    public function askQuestions($helper, InputInterface $input, OutputInterface $output);
}
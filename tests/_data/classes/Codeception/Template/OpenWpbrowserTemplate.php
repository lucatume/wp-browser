<?php
/**
 * An open version of the Wpbrowser template class to test its methods.
 *
 * @package Codeception\Template
 */

namespace Codeception\Template;

use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class OpenWpbrowserTemplate
 *
 * @package Codeception\Template
 */
class OpenWpbrowserTemplate extends Wpbrowser
{

    public function __construct(InputInterface $input, OutputInterface $output)
    {
        parent::__construct(new ArrayInput([]), new NullOutput());
    }
}

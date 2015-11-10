<?php

namespace Codeception\Command;

use Codeception\Lib\Generator\WPUnit as WPUnitGenerator;
use Codeception\Lib\Generator\WPUnit;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Command\Command;


/**
 * Generates skeleton for unit test as in classical PHPUnit.
 *
 * * `codecept g:wpunit unit UserTest`
 * * `codecept g:wpunit unit User`
 * * `codecept g:wpunit unit "App\User`
 *
 */
class GenerateWPUnit extends Command
{
    use Shared\FileSystem;
    use Shared\Config;

    const SLUG = "generate:wpunit";

    protected function configure()
    {
        $this->setDefinition(array(

            new InputArgument('suite', InputArgument::REQUIRED, 'suite where tests will be put'),
            new InputArgument('class', InputArgument::REQUIRED, 'class name'),
            new InputOption('config', 'c', InputOption::VALUE_OPTIONAL, 'Use custom path for config'),
        ));
        parent::configure();
    }

    public function getDescription() {
        return 'Generates a WPTestCase: a WP_UnitTestCase extension with Codeception additions.';
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $suite = $input->getArgument('suite');
        $class = $input->getArgument('class');

        $config = $this->getSuiteConfig($suite, $input->getOption('config'));

        $path = $this->buildPath($config['path'], $class);

        $filename = $this->completeSuffix($this->getClassName($class), 'Test');
        $filename = $path.$filename;

        $gen = $this->getGenerator( $config, $class );

        $res = $this->save($filename, $gen->produce());
        if (!$res) {
            $output->writeln("<error>Test $filename already exists</error>");
            exit;
        }

        $output->writeln("<info>Test was created in $filename</info>");
    }

    /**
     * @param $config
     * @param $class
     *
     * @return WPUnitGenerator
     */
    protected function getGenerator( $config, $class ) {
        return new WPUnit( $config, $class, '\\Codeception\\TestCase\\WPTestCase' );
    }

}


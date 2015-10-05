<?php

namespace Codeception\Command;


use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;
use tad\WPBrowser\Utils\PathUtils;

class GeneratePhpunitBootstrap extends Command
{

    protected function configure()
    {
        $this->setDefinition([
            new InputArgument('suites', 's', InputArgument::OPTIONAL, 'A comma separated list of suites PHPUnit tests should run.', 'functional'),
            new InputArgument('suffix', 'x', InputArgument::OPTIONAL, 'The suffix of the test case file names.', 'Test')
        ]);
    }

    public function getDescription()
    {
        return 'Generates the files needed to run tests from a suite using PHPUnit';
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $suites = $input->getArgument('suites');

        if (empty($suites)) {
            $output->writeln("<error>\nPlease specify at least one suite\n</error>");
            return;
        }

        if (false !== strpos($suites, ',')) {
            $suites = explode(',', $suites);
        } else {
            $suites = [$suites];
        }


        $path = realpath('.');
        chdir($path);

        if (!file_exists('codeception.yml')) {
            $output->writeln("<error>\nMissing 'codeception.yml' file\n</error>");
            return;
        }

        // read test folder location from codeception.yml file
        $config = Yaml::parse('codeception.yml');

        $testsPath = empty($config['paths']['tests']) ? 'tests' : $config['paths']['tests'];

        if (!is_dir($testsPath)) {
            $output->writeln("<error>\nThe tests paths is not set or not accessible.\n</error>");
            return;
        }

        $testsPath = DIRECTORY_SEPARATOR . PathUtils::unleadslashit(PathUtils::untrailslashit($testsPath)) . DIRECTORY_SEPARATOR;

        if (count($suites)) {
            $output->writeln("<error>\nPlease specify at least one suite.\n</error>");
            return;
        }

        $suitesEntries = [];
        $suffix = $input->getArgument('testSuffix');
        foreach ($suites as $suite) {
            $suitePath = $testsPath . $suite;
            if (!file_exists($suitePath)) {
                $output->writeln("<error>\nThe suite'{$suite}' folder cannot be found\n</error>");
                return;
            }

            $args = ['name' => ucfirst($suite), 'suffix' => $suffix, 'pathToFiles' => $suitePath];
            $suitesEntries[] = $this->getTestsuiteEntry($args);
        }

        $xmlFileContents = $this->getXmlFileContent($suitesEntries);

        file_put_contents('phpunit.xml', $xmlFileContents);
    }

    /**
     * @param $args
     */
    protected function getTestsuiteEntry($args)
    {
        $entry = '<testsuite name="{{{name}}}">
      <directory suffix="{{{suffix}}}.php" phpVersion="5.3.0" phpVersionOperator=">=">{{{pathToFiles}}}</directory>
    </testsuite>';
        foreach ($args as $key => $value) {
            $entry = str_replace('{{{' . $key . '}}}', $value, $entry);
        }

        return $entry;
    }

    /**
     * @param $suitesEntries
     */
    protected function getXmlFileContent($suitesEntries)
    {
        $template = '<phpunit
         xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
         xsi:noNamespaceSchemaLocation="http://schema.phpunit.de/4.5/phpunit.xsd"
         backupGlobals="true"
         backupStaticAttributes="false"
         bootstrap="/tests/phpunit-bootstrap.php"
         cacheTokens="false"
         colors="false"
         convertErrorsToExceptions="true"
         convertNoticesToExceptions="true"
         convertWarningsToExceptions="true"
         forceCoversAnnotation="false"
         mapTestClassNameToCoveredClassName="false"
         printerClass="PHPUnit_TextUI_ResultPrinter"
         processIsolation="false"
         stopOnError="false"
         stopOnFailure="false"
         stopOnIncomplete="false"
         stopOnSkipped="false"
         stopOnRisky="false"
         testSuiteLoaderClass="PHPUnit_Runner_StandardTestSuiteLoader"
         timeoutForSmallTests="1"
         timeoutForMediumTests="10"
         timeoutForLargeTests="60"
         verbose="false">
 <testsuites>
    {{{testSuites}}}
</testsuites>
</phpunit>';
        $xlmFileContent = str_replace('{{{testSuites}}}', implode('', $suitesEntries), $template);
    }
}
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
            new InputArgument('suites', InputArgument::OPTIONAL, 'A comma separated list of suites PHPUnit tests should run.', 'functional'),
            new InputArgument('suffix', InputArgument::OPTIONAL, 'The suffix of the test case file names.', 'Test'),
            new InputArgument('vendor', InputArgument::OPTIONAL, 'The relative path to the vendor folder.', 'vendor')
        ]);
    }

    public function getDescription()
    {
        return 'Generates the files needed to run tests from the suites using PHPUnit';
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


        $path = $this->getRootPath();
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

        if (!count($suites)) {
            $output->writeln("<error>\nPlease specify at least one suite.\n</error>");

            return;
        }

        $suitesEntries = [];
        $suffix = $input->getArgument('suffix');
        foreach ($suites as $suite) {
            $suitePath = $path . $testsPath . $suite;
            if (!file_exists($suitePath)) {
                $output->writeln("<error>\nThe suite '{$suite}' folder cannot be found\n</error>");

                return;
            }

            $args = ['name' => ucfirst($suite), 'suffix' => $suffix, 'pathToFiles' => $testsPath . $suite];
            $suitesEntries[] = $this->getTestsuiteEntry($args);
        }

        $xmlFileContents = $this->getXmlFileContent($suitesEntries, $testsPath);

        $vendor = $input->getArgument('vendor');
        if (!is_dir($this->getRootPath() . DIRECTORY_SEPARATOR . PathUtils::unleadslashit($vendor))) {
            $output->writeln("<error>\nThe vendor folder {$vendor} does not exist\n</error>");

            return;
        }
        file_put_contents('phpunit.xml', $xmlFileContents);

        $bootstrapFileContents = $this->getBootstrapFileContents($vendor, $testsPath);
        file_put_contents($this->bootstrapFilePath($testsPath), $bootstrapFileContents);
    }

    protected function getTestsuiteEntry($args)
    {
        $template = <<<'XML'
<testsuite name="{{{name}}}">
      <directory suffix="{{{suffix}}}.php" phpVersion="5.3.0" phpVersionOperator=">=">{{{pathToFiles}}}</directory>
    </testsuite>
XML;
        $entry = $this->compileTemplate($args, $template);

        return $entry;
    }

    protected function getXmlFileContent($suitesEntries, $testsPath)
    {
        $template = <<<'XML'
<phpunit
         xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
         xsi:noNamespaceSchemaLocation="http://schema.phpunit.de/4.5/phpunit.xsd"
         backupGlobals="true"
         backupStaticAttributes="false"
         bootstrap="{{{bootstrapPath}}}"
         cacheTokens="false"
         colors="true"
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
</phpunit>
XML;

        return $this->compileTemplate(['testSuites' => implode("\n", $suitesEntries),
            'bootstrapPath' => $this->bootstrapFilePath($testsPath)], $template);
    }

    protected function compileTemplate($args, $template)
    {
        $entry = $template;
        foreach ($args as $key => $value) {
            $entry = str_replace('{{{' . $key . '}}}', $value, $entry);
        }

        return $entry;
    }

    private function bootstrapFilePath($testsPath)
    {
        return $this->getRootPath() . $testsPath . 'phpunit-bootstrap.php';
    }

    private function getBootstrapFileContents($vendor, $testsPath)
    {
        $template = <<< PHP
<?php

// include Composer autoload files
require_once dirname(__FILE__) . '/{{{autoloadFile}}}';
require_once dirname(__FILE__) . '/{{{wpBrowserAutoloadFile}}}';

// load WPLoader and let it do the heavy lifting of starting WordPress and requiring the plugins
\$configFile = dirname(__FILE__) . '/{{{codeceptionYml}}}';
\$config = Symfony\Component\Yaml\Yaml::parse(\$configFile);
\$moduleContainer = new Codeception\Lib\ModuleContainer(new Codeception\Lib\Di(), \$config);
\$loader = new Codeception\Module\WPLoader(\$moduleContainer, \$config['modules']['config']['WPLoader']);
\$loader->_initialize();
PHP;

        $args = [];
        $backHops = substr_count(PathUtils::untrailslashit(PathUtils::unleadslashit($testsPath)), '/');
        $relativeBackHopsPath = implode('/', array_map(function ($n) {
            return '..';
        }, range(0, $backHops)));
        $args['autoloadFile'] = sprintf("%s/%s/autoload.php", $relativeBackHopsPath, PathUtils::unleadslashit(PathUtils::untrailslashit($vendor)));
        $args['wpBrowserAutoloadFile'] = sprintf("%s/%s/lucatume/wp-browser/autoload.php", $relativeBackHopsPath, PathUtils::unleadslashit(PathUtils::untrailslashit($vendor)));
        $args['codeceptionYml'] = sprintf("%s/codeception.yml", $relativeBackHopsPath);

        return $this->compileTemplate($args, $template);
    }

    /**
     * @return string
     */
    protected function getRootPath()
    {
        $path = realpath('.');

        return $path;
    }
}
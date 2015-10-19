<?php

namespace Codeception\Command;


use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;
use tad\WPBrowser\Filesystem\Utils;

class GeneratePhpunitBootstrap extends Command
{

    public function getDescription()
    {
        return 'Generates the files needed to run tests from the suites using PHPUnit';
    }

    protected function configure()
    {
        $this->setDefinition([
            new InputArgument('suites', InputArgument::OPTIONAL, 'A comma separated list of suites PHPUnit tests should run.', 'functional'),
            new InputArgument('suffix', InputArgument::OPTIONAL, 'The suffix of the test case file names.', 'Test'),
            new InputArgument('vendor', InputArgument::OPTIONAL, 'The relative path to the vendor folder.', 'vendor')
        ]);
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

        $testsPath = DIRECTORY_SEPARATOR . Utils::unleadslashit(Utils::untrailslashit($testsPath)) . DIRECTORY_SEPARATOR;

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

            $args = ['name' => ucfirst($suite), 'suffix' => $suffix,
                     'pathToFiles' => Utils::unleadslashit($testsPath) . $suite
            ];
            $suitesEntries[] = $this->getTestsuiteEntry($args);
        }

        // maybe the user already did this and modified the default file?
        $xmlFile = 'phpunit.xml';
        $previousConfig = $this->loadPreviousConfig($xmlFile);

        $xmlFileContents = $this->getXmlFileContent($suitesEntries, $testsPath, $previousConfig);

        $vendor = $input->getArgument('vendor');
        if (!is_dir($this->getRootPath() . DIRECTORY_SEPARATOR . Utils::unleadslashit($vendor))) {
            $output->writeln("<error>\nThe vendor folder {$vendor} does not exist\n</error>");

            return;
        }
        file_put_contents($xmlFile, $xmlFileContents);

        $bootstrapFileContents = $this->getBootstrapFileContents($vendor, $testsPath);
        file_put_contents($this->bootstrapFilePath($testsPath), $bootstrapFileContents);
    }

    /**
     * @return string
     */
    protected function getRootPath()
    {
        $path = realpath('.');

        return $path;
    }

    protected function getTestsuiteEntry($args)
    {
        $template = <<<'XML'
<testsuite name="{{{name}}}">
      <directory suffix="{{{suffix}}}.php" phpVersion="5.4.0" phpVersionOperator=">=">{{{pathToFiles}}}</directory>
    </testsuite>
XML;
        $entry = $this->compileTemplate($args, $template);

        return $entry;
    }

    protected function compileTemplate($args,
        $template)
    {
        $entry = $template;
        foreach ($args as $key => $value) {
            $entry = str_replace('{{{' . $key . '}}}', $value, $entry);
        }

        return $entry;
    }

    protected function loadPreviousConfig($xmlFile)
    {
        if (file_exists($xmlFile)) {
            $doc = simplexml_load_file($xmlFile);
            return $doc;
        }
        return false;
    }

    protected function getXmlFileContent($suitesEntries, $testsPath, $previousConfig = null)
    {
        $template = <<<'XML'
<phpunit
         xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
         xsi:noNamespaceSchemaLocation="http://schema.phpunit.de/4.5/phpunit.xsd"
         backupGlobals="true"
         backupStaticAttributes="false"
         bootstrap="{{{bootstrapPath}}}"
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
</phpunit>
XML;

        if (is_a($previousConfig, 'SimpleXMLElement')) {
            /** @var \SimpleXMLElement $previousConfig */
            $attrs = (array)($previousConfig->attributes());
            $attrs = reset($attrs);
            if (!empty($attrs)) {
                $new = simplexml_load_string($template);
                $newAttrs = (array)$new->attributes();
                $newAttrs = reset($newAttrs);
                $keep = array_keys(array_intersect_key($attrs, $newAttrs));
                $attrs = array_merge($newAttrs, $attrs);
                array_walk($attrs, function ($value, $key) use (&$new, $keep) {
                    if (in_array($key, $keep)) {
                        $new->attributes()->$key = $value;
                    } else {
                        unset($new->attributes()->$key);
                    }
                });
                $template = $new->saveXML();
            }
        }

        return $this->compileTemplate(['testSuites' => implode("\n", $suitesEntries),
            'bootstrapPath' => $this->bootstrapFilePath($testsPath)], $template);
    }

    private function bootstrapFilePath($testsPath)
    {
        return Utils::unleadslashit($testsPath . 'phpunit-bootstrap.php');
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
        $backHops = substr_count(Utils::untrailslashit(Utils::unleadslashit($testsPath)), '/');
        $relativeBackHopsPath = implode('/', array_map(function () {
            return '..';
        }, range(0, $backHops)));
        $args['autoloadFile'] = sprintf("%s/%s/autoload.php", $relativeBackHopsPath, Utils::unleadslashit(Utils::untrailslashit($vendor)));
        $args['wpBrowserAutoloadFile'] = sprintf("%s/%s/lucatume/wp-browser/autoload.php", $relativeBackHopsPath, Utils::unleadslashit(Utils::untrailslashit($vendor)));
        $args['codeceptionYml'] = sprintf("%s/codeception.yml", $relativeBackHopsPath);

        return $this->compileTemplate($args, $template);
    }
}
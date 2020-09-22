<?php
/**
 * The template used for the `codecept init wpbrowser` command.
 *
 * @package Codeception\Template
 */

namespace Codeception\Template;

use Codeception\Codecept;
use Codeception\Exception\ModuleConfigException;
use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Yaml\Yaml;
use tad\WPBrowser\Template\Data;
use tad\WPBrowser\Utils\Map;
use function lucatume\WPBrowser\checkComposerDependencies;
use function lucatume\WPBrowser\composerFile;
use function tad\WPBrowser\dbDsnMap;
use function tad\WPBrowser\dbDsnString;
use function tad\WPBrowser\envFile;
use function tad\WPBrowser\loadEnvMap;
use function tad\WPBrowser\resolvePath;
use function tad\WPBrowser\rrmdir;
use function tad\WPBrowser\urlDomain;

/**
 * Class Wpbrowser
 *
 * @package Codeception\Template
 */
class Wpbrowser extends Bootstrap
{
    use WithInjectableHelpers;

    /**
     * Whether to output during the bootstrap process or not.
     *
     * @var bool
     */
    protected $quiet = false;

    /**
     * Whether to bootstrap with user interaction or not.
     *
     * @var bool
     */
    protected $noInteraction = false;

    /**
     * The name of the environment file to use.
     * @var string
     */
    protected $envFileName = '';

    /**
     * A map of the installation data either pre-set or compiled from the user answers.
     * @var Map
     */
    protected $installationData;
    /**
     * Whether to create the suites helpers or not.
     * @var bool
     */
    protected $createHelpers = true;
    /**
     * Whether to create the suite actors or not.
     * @var bool
     */
    protected $createActors = true;

    /**
     * Whether to create the suites configuration files or not.
     * @var bool
     */
    protected $createSuiteConfigFiles = true;

    /**
     * Whether to check the Composer configuration or not.
     *
     * @var bool
     */
    protected $checkComposerConfig = true;

    /**
     * Sets up wp-browser.
     *
     * @param bool $interactive Whether to set up wp-browser in interactive mode or not.
     *
     * @return void
     *
     * @throws \Exception If there's an error processing the installation information or context.
     */
    public function setup($interactive = true)
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $this->checkInstalled($this->workDir);
        $this->checkComposerConfig();

        $input = $this->input;

        $this->quiet         = (bool) $this->input->getOption('quiet');
        $this->noInteraction = (bool) $this->input->getOption('no-interaction');

        if ($this->noInteraction || $this->quiet) {
            $interactive = false;
            $this->input->setInteractive(false);
        } else {
            $interactive = true;
            $this->input->setInteractive(true);
        }

        if ($input->getOption('namespace')) {
            $namespace = $input->getOption('namespace');
            if (is_string($namespace)) {
                $this->namespace = trim($namespace, '\\') . '\\';
            }
        }

        if ($input->hasOption('actor') && $input->getOption('actor')) {
            $actor = $input->getOption('actor');
            if (is_string($actor)) {
                $this->actorSuffix = $actor;
            }
        }

        if ($interactive) {
            $this->askForAcknowledgment();
        }

        $this->say("<fg=white;bg=magenta> Bootstrapping Codeception for WordPress </fg=white;bg=magenta>\n");

        $this->say("File codeception.yml created       <- global configuration");

        $this->createDirs();

        if ($input->hasOption('empty') && $input->getOption('empty')) {
            return;
        }

        if ($interactive === true) {
            $this->say();
            $interactive = $this->ask('Would you like to set up the suites interactively now?', 'yes');
            $this->say(' --- ');
            $this->say();
            $interactive = preg_match('/^(n|N)/', $interactive) ? false : true;
        }

        $installationData      = $this->getInstallationData($interactive);
        $arrayInstallationData = $installationData->toArray();

        try {
            $this->createGlobalConfig();
            $this->writeEnvFile($installationData);
            loadEnvMap(envFile($this->envFileName));
            $this->createUnitSuite();
            $this->say("tests/unit created                 <- unit tests");
            $this->say("tests/unit.suite.yml written       <- unit tests suite configuration");
            $this->createWpUnitSuite(ucwords($installationData['wpunitSuite']), $arrayInstallationData);
            $this->say("tests/{$installationData['wpunitSuiteSlug']} created               "
                        . '<- WordPress unit and integration tests');
            $this->say("tests/{$installationData['wpunitSuiteSlug']}.suite.yml written     "
                        . '<- WordPress unit and integration tests suite configuration');
            $this->createFunctionalSuite(ucwords($installationData['functionalSuite']), $arrayInstallationData);
            $this->say("tests/{$installationData['functionalSuiteSlug']} created           "
                        . "<- {$installationData['functionalSuiteSlug']} tests");
            $this->say("tests/{$installationData['functionalSuiteSlug']}.suite.yml written "
                        . "<- {$installationData['functionalSuiteSlug']} tests suite configuration");
            $this->createAcceptanceSuite(ucwords($installationData['acceptanceSuite']), $arrayInstallationData);
            $this->say("tests/{$installationData['acceptanceSuiteSlug']} created           "
                        . "<- {$installationData['acceptanceSuiteSlug']} tests");
            $this->say("tests/{$installationData['acceptanceSuiteSlug']}.suite.yml written "
                        . "<- {$installationData['acceptanceSuiteSlug']} tests suite configuration");
        } catch (ModuleConfigException $e) {
            $this->removeCreatedFiles();
            $this->say('<error>Something is not ok in the modules configurations: '
                        . 'check your answers and try again.</error>');
            $this->say('<error>' . $e->getMessage() . '</error>');
            $this->sayInfo('All files and folders created by the initialization attempt have been removed.');

            return;
        }

        $this->say(" --- ");
        $this->say();
        if ($interactive) {
            $this->saySuccess("Codeception is installed for {$installationData['acceptanceSuiteSlug']}, "
                               . "{$installationData['functionalSuiteSlug']}, and WordPress unit testing");
        } else {
            $this->saySuccess("Codeception has created the files for the {$installationData['acceptanceSuiteSlug']}, "
                               . "{$installationData['functionalSuiteSlug']}, WordPress unit and unit suites "
                               . 'but the modules are not activated');
        }
        $this->say('Some commands have been added in the Codeception configuration file: '
                    . 'check them out using <comment>codecept --help</comment>');
        $this->say(" --- ");
        $this->say();

        $this->say("<bold>Next steps:</bold>");
        $this->say('0. <bold>Create the databases used by the modules</bold>; wp-browser will not do it for you!');
        $this->say('1. <bold>Install and configure WordPress</bold> activating the theme and plugins you need to create'
                    . ' a database dump in <comment>tests/_data/dump.sql</comment>');
        $this->say("2. Edit <bold>tests/{$installationData['acceptanceSuiteSlug']}.suite.yml</bold> to make sure WPDb "
                    . 'and WPBrowser configurations match your local setup; change WPBrowser to WPWebDriver to '
                    . 'enable browser testing');
        $this->say("3. Edit <bold>tests/{$installationData['functionalSuiteSlug']}.suite.yml</bold> to make sure "
                    . 'WordPress and WPDb configurations match your local setup');
        $this->say("4. Edit <bold>tests/{$installationData['wpunitSuiteSlug']}.suite.yml</bold> to make sure WPLoader "
                    . 'configuration matches your local setup');
        $this->say("5. Create your first {$installationData['acceptanceSuiteSlug']} tests using <comment>codecept "
                    . "g:cest {$installationData['acceptanceSuiteSlug']} WPFirst</comment>");
        $this->say("6. Write a test in <bold>tests/{$installationData['acceptanceSuiteSlug']}/WPFirstCest.php</bold>");
        $this->say("7. Run tests using: <comment>codecept run {$installationData['acceptanceSuiteSlug']}</comment>");
        $this->say(" --- ");
        $this->say();
        $this->sayWarning('Please note: due to WordPress extended use of globals and constants you should avoid running'
                           . ' all the suites at the same time.');
        $this->say('Run each suite separately, like this: <comment>codecept run unit && codecept run '
                    . "{$installationData['wpunitSuiteSlug']}</comment>, to avoid problems.");
    }

    /**
     * Says something to the user.
     *
     * @param string $message The message to tell to the user.
     *
     * @return void
     */
    protected function say($message = '')
    {
        if ($this->quiet) {
            return;
        }
        parent::say($message);
    }

    /**
     * Creates the global config.
     *
     * @return void
     *
     */
    public function createGlobalConfig()
    {
        $basicConfig = [
            'paths'        => [
                'tests'   => 'tests',
                'output'  => $this->outputDir,
                'data'    => $this->dataDir,
                'support' => $this->supportDir,
                'envs'    => $this->envsDir,
            ],
            'actor_suffix' => 'Tester',
            'extensions'   => [
                'enabled'  => [ 'Codeception\Extension\RunFailed' ],
                'commands' => $this->getAddtionalCommands(),
            ],
            'params'       => [
                trim($this->envFileName),
            ],
        ];

        $str = Yaml::dump($basicConfig, 4);
        if ($this->namespace) {
            $namespace = rtrim($this->namespace, '\\');
            $str       = "namespace: $namespace\n" . $str;
        }
        $this->createFile('codeception.dist.yml', $str);
    }

    /**
     * Returns a list of additional commands.
     *
     * @return array<string> The additonal commands provided by wp-browser.
     */
    protected function getAddtionalCommands()
    {
        return [
            'Codeception\\Command\\GenerateWPUnit',
            'Codeception\\Command\\GenerateWPRestApi',
            'Codeception\\Command\\GenerateWPRestController',
            'Codeception\\Command\\GenerateWPRestPostTypeController',
            'Codeception\\Command\\GenerateWPAjax',
            'Codeception\\Command\\GenerateWPCanonical',
            'Codeception\\Command\\GenerateWPXMLRPC',
        ];
    }

    /**
     * Builds, and returns, the installation data.
     *
     * @param bool $interactive Whether to build the installation data with user interactive input or not.
     *
     * @return Map The installation data.
     */
    protected function getInstallationData($interactive)
    {
        if ($this->installationData !== null) {
            return $this->installationData;
        }

        if (! $interactive) {
            $installationData = $this->getDefaultInstallationData();
        } else {
            $installationData = $this->askForInstallationData();
        }

        return $installationData instanceof Map ? $installationData : new Map($installationData);
    }

    /**
     * Asks the user for the installation data.
     *
     * @return array<string,mixed>|Map The installation data as provided by the user.
     */
    protected function askForInstallationData()
    {
        $installationData = [
            'activeModules' => [
                'WPDb'      => true,
                'WPBrowser' => true,
                'WordPress' => true,
                'WPLoader'  => true,
            ],
        ];

        $installationData['acceptanceSuite']     = 'acceptance';
        $installationData['functionalSuite']     = 'functional';
        $installationData['wpunitSuite']         = 'wpunit';
        $installationData['acceptanceSuiteSlug'] = strtolower($installationData['acceptanceSuite']);
        $installationData['functionalSuiteSlug'] = strtolower($installationData['functionalSuite']);
        $installationData['wpunitSuiteSlug']     = strtolower($installationData['wpunitSuite']);

        $this->say('---');
        $this->say();

        $this->envFileName = '.env.testing';

        $this->checkEnvFileExistence();

        echo PHP_EOL;
        $this->sayInfo('WPLoader and WordPress modules need to access the WordPress files to work.');
        echo PHP_EOL;

        $installationData['wpRootFolder']        = $this->normalizePath($this->ask(
            'Where is WordPress installed?',
            '/var/www/html'
        ));
        $installationData['testSiteWpAdminPath'] = $this->ask(
            'What is the path, relative to WordPress root URL, of the admin area of the test site?',
            '/wp-admin'
        );
        $normalizedAdminPath                     = trim(
            $this->normalizePath($installationData['testSiteWpAdminPath']),
            '/'
        );
        $installationData['testSiteWpAdminPath'] = '/' . $normalizedAdminPath;
        echo PHP_EOL;
        $this->sayInfo('The WPDb module needs the database details to access the test database used by the test site.');
        echo PHP_EOL;
        $installationData['testSiteDbName'] = $this->ask(
            'What is the name of the test database used by the test site?',
            'test'
        );
        $installationData['testSiteDbHost'] = $this->ask(
            'What is the host of the test database used by the test site?',
            'localhost'
        );

        $installationData['testSiteDbUser']      = $this->ask(
            'What is the user of the test database used by the test site?',
            'root'
        );
        $installationData['testSiteDbPassword']  = $this->ask(
            'What is the password of the test database used by the test site?',
            ''
        );
        $installationData['testSiteTablePrefix'] = $this->ask(
            'What is the table prefix of the test database used by the test site?',
            'wp_'
        );

        echo PHP_EOL;
        $this->sayInfo(
            'WPLoader will reinstall a fresh WordPress installation before the tests.' .
            PHP_EOL . 'It needs the details you would typically provide when installing WordPress from scratch.'
        );

        echo PHP_EOL;
        $this->sayWarning(implode(PHP_EOL, [
            'WPLoader should be configured to run on a dedicated database!',
            'The data stored on the database used by the WPLoader module will be lost!',
        ]));
        echo PHP_EOL;

        $installationData['testDbName'] = $this->ask(
            'What is the name of the test database WPLoader should use?',
            'test'
        );
        $installationData['testDbHost'] = $this->ask(
            'What is the host of the test database WPLoader should use?',
            'localhost'
        );

        $installationData['testDbUser']            = $this->ask(
            'What is the user of the test database WPLoader should use?',
            'root'
        );
        $installationData['testDbPassword']        = $this->ask(
            'What is the password of the test database WPLoader should use?',
            ''
        );
        $installationData['testTablePrefix']       = $this->ask(
            'What is the table prefix of the test database WPLoader should use?',
            'wp_'
        );
        $installationData['testSiteWpUrl']         = $this->ask(
            'What is the URL the test site?',
            'http://wordpress.test'
        );
        $installationData['testSiteWpUrl']         = rtrim($installationData['testSiteWpUrl'], '/');
        $installationData['testSiteWpDomain']      = urlDomain($installationData['testSiteWpUrl']);
        $adminEmailCandidate                       = "admin@{$installationData['testSiteWpDomain']}";
        $installationData['testSiteAdminEmail']    = $this->ask(
            'What is the email of the test site WordPress administrator?',
            $adminEmailCandidate
        );
        $installationData['title']                 = $this->ask('What is the title of the test site?', 'Test');
        $installationData['testSiteAdminUsername'] = $this->ask(
            'What is the login of the administrator user of the test site?',
            'admin'
        );
        $installationData['testSiteAdminPassword'] = $this->ask(
            'What is the password of the administrator user of the test site?',
            'password'
        );

        $sut = '';

        while (! in_array($sut, [ 'plugin', 'theme', 'both' ])) {
            $sut = $this->ask('Are you testing a plugin, a theme or a combination of both (both)?', 'plugin');
        }

        $installationData['plugins'] = [];
        if ($sut === 'plugin') {
            $installationData['mainPlugin'] = $this->ask(
                'What is the <comment>folder/plugin.php</comment> name of the plugin?',
                'my-plugin/my-plugin.php'
            );
        } elseif ($sut === 'theme') {
            $isChildTheme = $this->ask('Are you developing a child theme?', 'no');
            if (preg_match('/^(y|Y)/', $isChildTheme)) {
                $installationData['parentTheme'] = $this->ask(
                    'What is the slug of the parent theme?',
                    'twentyseventeen'
                );
            }
            $installationData['theme'] = $this->ask('What is the slug of the theme?', 'my-theme');
        } else {
            $isChildTheme = $this->ask('Are you using a child theme?', 'no');
            if (preg_match('/^(y|Y)/', $isChildTheme)) {
                $installationData['parentTheme'] = $this->ask(
                    'What is the slug of the parent theme?',
                    'twentyseventeen'
                );
            }
            $installationData['theme'] = $this->ask('What is the slug of the theme you are using?', 'my-theme');
        }

        $activateFurtherPlugins = $this->ask(
            'Does your project needs additional plugins to be activated to work?',
            'no'
        );

        if (preg_match('/^(y|Y)/', $activateFurtherPlugins)) {
            do {
                $plugin                        = $this->ask(
                    'Please enter the plugin <comment>folder/plugin.php</comment> (leave blank when done)',
                    ''
                );
                $installationData['plugins'][] = $plugin;
            } while (! empty($plugin));
        }

        $installationData['plugins'] = array_map('trim', array_filter($installationData['plugins']));
        if (! empty($installationData['mainPlugin'])) {
            $installationData['plugins'][] = $installationData['mainPlugin'];
        }

        return $installationData;
    }

    /**
     * Checks for the existence of an environment file.
     *
     * @return void
     *
     * @throws \RuntimeException If an environment file already exists.
     */
    protected function checkEnvFileExistence()
    {
        $filename = $this->workDir . DIRECTORY_SEPARATOR . $this->envFileName;

        if (file_exists($filename)) {
            $basename = basename($filename);
            $message  = "Found a previous {$basename} file."
                        . PHP_EOL . "Remove the existing {$basename} file or specify a " .
                        "different name for the env file.";
            throw new RuntimeException($message);
        }
    }

    /**
     * Normalizes a path.
     *
     * @param string $path The path to normalize.
     *
     * @return string The normalized path.
     */
    protected function normalizePath($path)
    {
        $pathFrags = preg_split('#([/\\\])#u', $path) ?: [];

        return implode('/', $pathFrags);
    }

    /**
     * Writes the testing environment configuration file.
     *
     * @param Map $installationData The installation data to use to build the env file contents.
     *
     * @return void
     */
    public function writeEnvFile(Map $installationData)
    {
        $filename = $this->workDir . DIRECTORY_SEPARATOR . $this->envFileName;
        $envVars  = $this->getEnvFileVars($installationData);

        $envFileLines = implode("\n", array_map(static function ($key, $value) {
            return "{$key}={$value}";
        }, array_keys($envVars), $envVars));

        $put = file_put_contents($filename, $envFileLines . "\n");

        if (! $put) {
            $this->removeCreatedFiles();
            throw new RuntimeException("Could not write {$this->envFileName} file!");
        }
    }

    /**
     * Removes the command scaffolded files.
     *
     * @return void
     */
    protected function removeCreatedFiles()
    {
        $files = [ 'codeception.yml', $this->envFileName ];
        $dirs  = [ 'tests' ];
        foreach ($files as $file) {
            if (file_exists(getcwd() . '/' . $file)) {
                unlink(getcwd() . '/' . $file);
            }
        }
        foreach ($dirs as $dir) {
            if (file_exists(getcwd() . '/' . $dir)) {
                rrmdir(getcwd() . '/' . $dir);
            }
        }
    }

    /**
     * Creates the WordPress unit test suite.
     *
     * @param string $actor The actor for the suite.
     * @param array<int|string,mixed>  $installationData The installation data.
     *
     * @return void
     */
    protected function createWpUnitSuite($actor = 'Wpunit', array $installationData = [])
    {
        $installationData = new Data($installationData);
        $WPLoader         = ! empty($installationData['activeModules']['WPLoader']) ? '- WPLoader' : '# - WPLoader';
        $suiteConfig      = <<<EOF
# Codeception Test Suite Configuration
#
# Suite for unit or integration tests that require WordPress functions and classes.

actor: $actor{$this->actorSuffix}
modules:
    enabled:
        {$WPLoader}
        - \\{$this->namespace}Helper\\$actor
    config:
        WPLoader:
            wpRootFolder: "%WP_ROOT_FOLDER%"
            dbName: "%TEST_DB_NAME%"
            dbHost: "%TEST_DB_HOST%"
            dbUser: "%TEST_DB_USER%"
            dbPassword: "%TEST_DB_PASSWORD%"
            tablePrefix: "%TEST_TABLE_PREFIX%"
            domain: "%TEST_SITE_WP_DOMAIN%"
            adminEmail: "%TEST_SITE_ADMIN_EMAIL%"
            title: "{$installationData['title']}"
EOF;

        if (! empty($installationData['theme'])) {
            $theme       = empty($installationData['parentTheme']) ?
                $installationData['theme']
                : "[{$installationData['parentTheme']}, {$installationData['theme']}]";
            $suiteConfig .= <<<EOF
            
            theme: {$theme}
EOF;
        }

        $plugins     = $installationData['plugins'];
        $plugins     = "'" . implode("', '", (array) $plugins) . "'";
        $suiteConfig .= <<< EOF
        
            plugins: [{$plugins}]
            activatePlugins: [{$plugins}]
EOF;

        $this->createSuite($installationData['wpunitSuiteSlug'], $actor, $suiteConfig);
    }

    /**
     * Overrides the base implementation to control what should be created.
     *
     * @param string $suite  The name of the suite to create.
     * @param string $actor  The name of the suite actor to create.
     * @param string $config The suite configuration.
     *
     * @return void
     */
    protected function createSuite($suite, $actor, $config)
    {
        $this->createDirectoryFor("tests/$suite", "$suite.suite.yml");
        if ($this->createHelpers) {
            $this->createHelper($actor, $this->supportDir);
        }
        if ($this->createActors) {
            $this->createActor($actor . $this->actorSuffix, $this->supportDir, Yaml::parse($config));
        }
        if ($this->createSuiteConfigFiles) {
            $this->createFile('tests' . DIRECTORY_SEPARATOR . "$suite.suite.yml", $config);
        }
    }

    /**
     * Creates the functional suite.
     *
     * @param string $actor The actor to use for the suite.
     * @param array<int|string,mixed>  $installationData The installation data.
     *
     * @return void
     */
    protected function createFunctionalSuite($actor = 'Functional', array $installationData = [])
    {
        $installationData = new Data($installationData);
        $WPDb             = ! empty($installationData['activeModules']['WPDb']) ? '- WPDb' : '# - WPDb';
        $WPBrowser        = ! empty($installationData['activeModules']['WPBrowser']) ? '- WPBrowser' : '# - WPBrowser';
        $WPFilesystem     = ! empty($installationData['activeModules']['WPFilesystem']) ?
            '- WPFilesystem'
            : '# - WPFilesystem';
        $suiteConfig      = <<<EOF
# Codeception Test Suite Configuration
#
# Suite for {$installationData['functionalSuiteSlug']} tests
# Emulate web requests and make WordPress process them

actor: $actor{$this->actorSuffix}
modules:
    enabled:
        {$WPDb}
        {$WPBrowser}
        {$WPFilesystem}
        - Asserts
        - \\{$this->namespace}Helper\\{$actor}
    config:
        WPDb:
            dsn: '%TEST_SITE_DB_DSN%'
            user: '%TEST_SITE_DB_USER%'
            password: '%TEST_SITE_DB_PASSWORD%'
            dump: 'tests/_data/dump.sql'
            populate: true
            cleanup: true
            waitlock: 10
            url: '%TEST_SITE_WP_URL%'
            urlReplacement: true
            tablePrefix: '%TEST_SITE_TABLE_PREFIX%'
        WPBrowser:
            url: '%TEST_SITE_WP_URL%'
            adminUsername: '%TEST_SITE_ADMIN_USERNAME%'
            adminPassword: '%TEST_SITE_ADMIN_PASSWORD%'
            adminPath: '%TEST_SITE_WP_ADMIN_PATH%'
            headers:
                X_TEST_REQUEST: 1
                X_WPBROWSER_REQUEST: 1

        WPFilesystem:
            wpRootFolder: '%WP_ROOT_FOLDER%'
            plugins: '/wp-content/plugins'
            mu-plugins: '/wp-content/mu-plugins'
            themes: '/wp-content/themes'
            uploads: '/wp-content/uploads'
EOF;
        $this->createSuite($installationData['functionalSuiteSlug'], $actor, $suiteConfig);
    }

    /**
     * Creates the acceptance suite files.
     *
     * @param string $actor The actor to use for the acceptance suite.
     * @param array<int|string,mixed>  $installationData The current installation data.
     *
     * @return void
     */
    protected function createAcceptanceSuite($actor = 'Acceptance', array $installationData = [])
    {
        $installationData = new Data($installationData);
        $WPDb             = ! empty($installationData['activeModules']['WPDb']) ? '- WPDb' : '# - WPDb';
        $WPBrowser        = ! empty($installationData['activeModules']['WPBrowser']) ? '- WPBrowser' : '# - WPBrowser';
        $suiteConfig      = <<<EOF
# Codeception Test Suite Configuration
#
# Suite for {$installationData['acceptanceSuiteSlug']} tests.
# Perform tests in browser using the WPWebDriver or WPBrowser.
# Use WPDb to set up your initial database fixture.
# If you need both WPWebDriver and WPBrowser tests - create a separate suite.

actor: $actor{$this->actorSuffix}
modules:
    enabled:
        {$WPDb}
        {$WPBrowser}
        - \\{$this->namespace}Helper\\{$actor}
    config:
        WPDb:
            dsn: '%TEST_SITE_DB_DSN%'
            user: '%TEST_SITE_DB_USER%'
            password: '%TEST_SITE_DB_PASSWORD%'
            dump: 'tests/_data/dump.sql'
            #import the dump before the tests; this means the test site database will be repopulated before the tests.
            populate: true
            # re-import the dump between tests; this means the test site database will be repopulated between the tests.
            cleanup: true
            waitlock: 10
            url: '%TEST_SITE_WP_URL%'
            urlReplacement: true #replace the hardcoded dump URL with the one above
            tablePrefix: '%TEST_SITE_TABLE_PREFIX%'
        WPBrowser:
            url: '%TEST_SITE_WP_URL%'
            adminUsername: '%TEST_SITE_ADMIN_USERNAME%'
            adminPassword: '%TEST_SITE_ADMIN_PASSWORD%'
            adminPath: '%TEST_SITE_WP_ADMIN_PATH%'
            headers:
                X_TEST_REQUEST: 1
                X_WPBROWSER_REQUEST: 1
EOF;
        $this->createSuite($installationData['acceptanceSuiteSlug'], $actor, $suiteConfig);
    }

    /**
     * Asks the user for acknowledgment.
     *
     * @return void
     */
    protected function askForAcknowledgment()
    {
        $this->say('<info>'
                    . 'Welcome to wp-browser, a complete testing suite for WordPress based on Codeception and PHPUnit!'
                    . '</info>');
        $this->say('<info>This command will guide you through the initial setup for your project.</info>');
        echo PHP_EOL;
        $this->say('<info>If you are new to wp-browser please take the time to read this guide:</info>');
        $this->say('<info>https://github.com/lucatume/wp-browser#initial-setup</info>');
        echo PHP_EOL;
        $acknowledge = $this->ask(
            '<warning>'
            . 'I acknowledge wp-browser should run on development servers only, '
            . 'that I have made a backup of my files and database contents before proceeding.'
            . '</warning>',
            true
        );
        echo PHP_EOL;
        if (! $acknowledge) {
            $this->say('<info>The command did not do anything, nothing changed.</info>');
            $this->say('<info>'
                        . 'Setup a WordPress installation and database dedicated to development and '
                        . 'restart this command when ready using `vendor/bin/codecept init wpbrowser`.'
                        . '</info>');
            echo PHP_EOL;
            $this->say('<info>See you soon!</info>');
            exit(0);
        }
        echo PHP_EOL;
    }

    /**
     * Sets the template working directory.
     *
     * @param string $workDir The path to the working directory the template should use.
     *
     * @return void
     */
    public function setWorkDir($workDir)
    {
        chdir($workDir);
        $this->workDir = $workDir;
    }

    /**
     * Sets the installation data the template should use.
     *
     * @param array<string,string> $installationData The installation data map.
     *
     * @return void
     */
    public function setInstallationData(array $installationData)
    {
        $this->installationData = new Map($installationData);
    }

    /**
     * Returns the default installation data.
     *
     * @return array<string,array<string,false>|string>|Map The template default installation data.
     */
    public function getDefaultInstallationData()
    {
        $installationData  = [
            'acceptanceSuite'       => 'acceptance',
            'functionalSuite'       => 'functional',
            'wpunitSuite'           => 'wpunit',
            'acceptanceSuiteSlug'   => 'acceptance',
            'functionalSuiteSlug'   => 'functional',
            'wpunitSuiteSlug'       => 'wpunit',
            'testSiteDbHost'        => 'localhost',
            'testSiteDbName'        => 'test',
            'testSiteDbUser'        => 'root',
            'testSiteDbPassword'    => 'password',
            'testSiteTablePrefix'   => 'wp_',
            'testSiteWpUrl'         => 'http://wordpress.test',
            'testSiteWpDomain'      => 'wordpress.test',
            'testSiteAdminUsername' => 'admin',
            'testSiteAdminPassword' => 'password',
            'testSiteAdminEmail'    => 'admin@wordpress.test',
            'testSiteWpAdminPath'   => '/wp-admin',
            'wpRootFolder'          => '/var/www/html',
            'testDbName'            => 'test',
            'testDbHost'            => 'localhost',
            'testDbUser'            => 'root',
            'testDbPassword'        => 'password',
            'testTablePrefix'       => 'wp_',
            'title'                 => 'WP Test',
            // deactivate all modules that could trigger exceptions when initialized with sudo values
            'activeModules'         => [ 'WPDb' => false, 'WordPress' => false, 'WPLoader' => false ],
        ];
        $this->envFileName = '.env.testing';

        return $installationData;
    }

    /**
     * Returns the env file lines that should be written in the env file given the current installation data.
     *
     * @param Map $installationData The installation data to generate the environment variables from.
     *
     * @return array<string,mixed> The interpolated environment variables.
     */
    public function getEnvFileVars(Map $installationData)
    {
        $testSiteDsnMap           = dbDsnMap($installationData['testSiteDbHost']);
        $testSiteDsnMap['dbname'] = $installationData['testSiteDbName'];
        $testDbDsnMap             = dbDsnMap($installationData['testDbHost']);
        $envVars                  = [
            'TEST_SITE_DB_DSN'         => dbDsnString($testSiteDsnMap),
            'TEST_SITE_DB_HOST'        => dbDsnString($testDbDsnMap, true),
            'TEST_SITE_DB_NAME'        => $testSiteDsnMap('dbname', 'wordpress'),
            'TEST_SITE_DB_USER'        => $installationData['testSiteDbUser'],
            'TEST_SITE_DB_PASSWORD'    => $installationData['testSiteDbPassword'],
            'TEST_SITE_TABLE_PREFIX'   => $installationData['testSiteTablePrefix'],
            'TEST_SITE_ADMIN_USERNAME' => $installationData['testSiteAdminUsername'],
            'TEST_SITE_ADMIN_PASSWORD' => $installationData['testSiteAdminPassword'],
            'TEST_SITE_WP_ADMIN_PATH'  => $installationData['testSiteWpAdminPath'],
            'WP_ROOT_FOLDER'           => $installationData['wpRootFolder'],
            'TEST_DB_NAME'             => $installationData['testDbName'],
            'TEST_DB_HOST'             => dbDsnString($testDbDsnMap, true),
            'TEST_DB_USER'             => $installationData['testDbUser'],
            'TEST_DB_PASSWORD'         => $installationData['testDbPassword'],
            'TEST_TABLE_PREFIX'        => $installationData['testTablePrefix'],
            'TEST_SITE_WP_URL'         => $installationData['testSiteWpUrl'],
            'TEST_SITE_WP_DOMAIN'      => urlDomain($installationData['testSiteWpUrl']),
            'TEST_SITE_ADMIN_EMAIL'    => $installationData['testSiteAdminEmail'],
        ];

        return $envVars;
    }

    /**
     * Sets whether suite helpers should be created or not.
     *
     * @param bool $createHelpers Whether suite helpers should be created or not.
     *
     * @return Wpbrowser For chaining.
     */
    public function setCreateHelpers($createHelpers)
    {
        $this->createHelpers = $createHelpers;

        return $this;
    }

    /**
     * Sets whether suite actors should be created or not.
     *
     * @param bool $createActors Whether suite actors should be created or not.
     *
     * @return Wpbrowser For chaining.
     */
    public function setCreateActors($createActors)
    {
        $this->createActors = $createActors;

        return $this;
    }

    /**
     * Sets whether suite configuration files should be created or not.
     *
     * @param bool $createSuiteConfigFiles Whether suite configuration files should be created or not.
     *
     * @return Wpbrowser For chaining.
     */
    public function setCreateSuiteConfigFiles($createSuiteConfigFiles)
    {
        $this->createSuiteConfigFiles = $createSuiteConfigFiles;

        return $this;
    }

    /**
     * Checks the composer.json file for requirements.
     *
     * @return void
     */
    protected function checkComposerConfig()
    {
        if (!$this->checkComposerConfig) {
            return;
        }

        /** @noinspection PhpUnhandledExceptionInspection */
        // @phpstan-ignore-next-line
        if (version_compare(Codecept::VERSION, '4.0.0', '>=')) {
            checkComposerDependencies(composerFile(resolvePath('composer.json', $this->workDir)), [
                'codeception/module-asserts'          => '^1.0',
                'codeception/module-phpbrowser'       => '^1.0',
                'codeception/module-webdriver'        => '^1.0',
                'codeception/module-db'               => '^1.0',
                'codeception/module-filesystem'       => '^1.0',
                'codeception/module-cli'              => '^1.0',
                'codeception/util-universalframework' => '^1.0'
            ], static function ($lines) {
                throw new \RuntimeException(
                    "wp-browser requires the following packages to work with Codeception v4.0:\n\n" .
                    implode(",\n", $lines) .
                    "\n\n1. Add these lines to the 'composer.json' file 'require-dev' section." .
                    "\n2. Run 'composer update'." .
                    "\n3. Run the 'codecept init wpbrowser' command again."
                );
            });
        }
    }

    /**
     * Sets the flag that controls the check of the Composer configuration file.
     *
     * @param bool $checkComposerConfig The flag that will control the check on the Composer configuration file.
     *
     * @return void
     */
    public function setCheckComposerConfig($checkComposerConfig)
    {
        $this->checkComposerConfig = (bool)$checkComposerConfig;
    }
}

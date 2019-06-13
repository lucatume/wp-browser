<?php

namespace Codeception\Template;

use Codeception\Exception\ModuleConfigException;
use Dotenv\Dotenv;
use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Yaml\Yaml;
use tad\WPBrowser\Template\Data;

class Wpbrowser extends Bootstrap
{

    /**
     * @var bool
     */
    protected $quiet = false;

    /**
     * @var bool
     */
    protected $noInteraction = false;

    /**
     * @var
     */
    protected $envFileName = '';

    /**
     * @param bool $interactive
     *
     * @return mixed|void
     * @throws \Exception
     */
    public function setup($interactive = true)
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $this->checkInstalled($this->workDir);

        $input = $this->input;

        $this->quiet = $this->input->getOption('quiet');
        $this->noInteraction = $this->input->getOption('no-interaction');

        if ($this->noInteraction || $this->quiet) {
            $interactive = false;
        }

        if ($input->getOption('namespace')) {
            $this->namespace = trim($input->getOption('namespace'), '\\') . '\\';
        }

        if ($input->hasOption('actor') && $input->getOption('actor')) {
            $this->actorSuffix = $input->getOption('actor');
        }

        if ($interactive) {
            $this->askForAcknowledgment();
        }

        $this->say("<fg=white;bg=magenta> Bootstrapping Codeception for WordPress </fg=white;bg=magenta>\n");

        $this->createGlobalConfig();

        $this->say("File codeception.yml created       <- global configuration");

        $this->createDirs();

        if ($input->hasOption('empty') && $input->getOption('empty')) {
            return;
        }

        if ($interactive === null) {
            $this->say();
            $interactive = $this->ask('Would you like to set up the suites interactively now?', 'yes');
            $this->say(' --- ');
            $this->say();
            $interactive = preg_match('/^(n|N)/', $interactive) ? false : true;
        }

        $installationData = $this->getInstallationData($interactive);

        try {
            $this->creatEnvFile($installationData);
            $this->loadEnvFile();
            $this->createUnitSuite();
            $this->say("tests/unit created                 <- unit tests");
            $this->say("tests/unit.suite.yml written       <- unit tests suite configuration");
            $this->createWpUnitSuite(ucwords($installationData['wpunitSuite']), $installationData);
            $this->say("tests/{$installationData['wpunitSuiteSlug']} created               "
                       . '<- WordPress unit and integration tests');
            $this->say("tests/{$installationData['wpunitSuiteSlug']}.suite.yml written     "
                       . '<- WordPress unit and integration tests suite configuration');
            $this->createFunctionalSuite(ucwords($installationData['functionalSuite']), $installationData);
            $this->say("tests/{$installationData['functionalSuiteSlug']} created           "
                       . "<- {$installationData['functionalSuiteSlug']} tests");
            $this->say("tests/{$installationData['functionalSuiteSlug']}.suite.yml written "
                       . "<- {$installationData['functionalSuiteSlug']} tests suite configuration");
            $this->createAcceptanceSuite(ucwords($installationData['acceptanceSuite']), $installationData);
            $this->say("tests/{$installationData['acceptanceSuiteSlug']} created           "
                       . "<- {$installationData['acceptanceSuiteSlug']} tests");
            $this->say("tests/{$installationData['acceptanceSuiteSlug']}.suite.yml written "
                       . "<- {$installationData['acceptanceSuiteSlug']} tests suite configuration");
        } catch (ModuleConfigException $e) {
            $this->removeCreatedFiles();
            $this->say('<error>Something is not ok in the modules configurations: '
                       .'check your answers and try again.</error>');
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
                   .'check them out using <comment>codecept --help</comment>');
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

    protected function say($message = '')
    {
        if ($this->quiet) {
            return;
        }
        parent::say($message);
    }

    public function createGlobalConfig()
    {
        $basicConfig = [
            'paths' => [
                'tests' => 'tests',
                'output' => $this->outputDir,
                'data' => $this->dataDir,
                'support' => $this->supportDir,
                'envs' => $this->envsDir,
            ],
            'actor_suffix' => 'Tester',
            'extensions' => [
                'enabled' => ['Codeception\Extension\RunFailed'],
                'commands' => $this->getAddtionalCommands(),
            ],
            'params' => [
                '.env.testing',
            ],
        ];

        $str = Yaml::dump($basicConfig, 4);
        if ($this->namespace) {
            $namespace = rtrim($this->namespace, '\\');
            $str = "namespace: $namespace\n" . $str;
        }
        $this->createFile('codeception.dist.yml', $str);
    }

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
     * @param $interactive
     *
     * @return array
     */
    protected function getInstallationData($interactive)
    {
        if (!$interactive) {
            $installationData = [
                'acceptanceSuite' => 'acceptance',
                'functionalSuite' => 'functional',
                'wpunitSuite' => 'wpunit',
                'acceptanceSuiteSlug' => 'acceptance',
                'functionalSuiteSlug' => 'functional',
                'wpunitSuiteSlug' => 'wpunit',
                'testSiteDbHost' => 'localhost',
                'testSiteDbName' => 'wp',
                'testSiteDbUser' => 'root',
                'testSiteDbPassword' => '',
                'testSiteTablePrefix' => 'wp_',
                'testSiteWpUrl' => 'http://wp.test',
                'testSiteWpDomain' => 'wp.test',
                'testSiteAdminUsername' => 'admin',
                'testSiteAdminPassword' => 'password',
                'testSiteAdminEmail' => 'admin@wp.test',
                'testSiteWpAdminPath' => '/wp-admin',
                'wpRootFolder' => '/var/www/html',
                'testDbName' => 'wpTests',
                'testDbHost' => 'localhost',
                'testDbUser' => 'root',
                'testDbPassword' => '',
                'testTablePrefix' => 'wp_',
                'title' => 'WP Test',
                // deactivate all modules that could trigger exceptions when initialized with sudo values
                'activeModules' => ['WPDb' => false, 'WordPress' => false, 'WPLoader' => false],
            ];
            $this->envFileName = '.env.testing';
        } else {
            $installationData = $this->askForInstallationData();
        }

        return $installationData;
    }

    protected function askForInstallationData()
    {
        $installationData = [
            'activeModules' => [
                'WPDb' => true,
                'WPBrowser' => true,
                'WordPress' => true,
                'WPLoader' => true,
            ],
        ];

        $installationData['acceptanceSuite'] = $this->ask(
            'How would you like the acceptance suite to be called?',
            'acceptance'
        );
        $installationData['functionalSuite'] = $this->ask(
            'How would you like the functional suite to be called?',
            'functional'
        );
        $installationData['wpunitSuite'] = $this->ask(
            'How would you like the WordPress unit and integration suite to be called?',
            'wpunit'
        );

        $installationData['acceptanceSuiteSlug'] = strtolower($installationData['acceptanceSuite']);
        $installationData['functionalSuiteSlug'] = strtolower($installationData['functionalSuite']);
        $installationData['wpunitSuiteSlug'] = strtolower($installationData['wpunitSuite']);

        $this->say('---');
        $this->say();

        while (strpos($this->envFileName, '.env.testing') !== 0) {
            $this->envFileName = $this->ask(
                'How would you like to call the env configuration file? (Should start with ".env")',
                '.env.testing'
            );
        }

        $this->checkEnvFileExistence();

        echo PHP_EOL;
        $this->sayInfo('WPLoader and WordPress modules need to access the WordPress files to work.');
        echo PHP_EOL;

        $installationData['wpRootFolder'] = $this->normalizePath($this->ask(
            'Where is WordPress installed?',
            '/var/www/wp'
        ));
        $installationData['testSiteWpAdminPath'] = $this->ask(
            'What is the path, relative to WordPress root URL, of the admin area of the test site?',
            '/wp-admin'
        );
        $normalizedAdminPath = trim($this->normalizePath($installationData['testSiteWpAdminPath']), '/');
        $installationData['testSiteWpAdminPath'] = '/' . $normalizedAdminPath;
        echo PHP_EOL;
        $this->sayInfo('The WPDb module needs the database details to access the test database used by the test site.');
        echo PHP_EOL;
        $installationData['testSiteDbName'] = $this->ask(
            'What is the name of the test database used by the test site?',
            'wp_test_site'
        );
        $installationData['testSiteDbHost'] = $this->ask(
            'What is the host of the test database used by the test site?',
            'localhost'
        );
        $installationData['testSiteDbUser'] = $this->ask(
            'What is the user of the test database used by the test site?',
            'root'
        );
        $installationData['testSiteDbPassword'] = $this->ask(
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
            'wp_test_integration'
        );
        $installationData['testDbHost'] = $this->ask(
            'What is the host of the test database WPLoader should use?',
            'localhost'
        );
        $installationData['testDbUser'] = $this->ask(
            'What is the user of the test database WPLoader should use?',
            'root'
        );
        $installationData['testDbPassword'] = $this->ask(
            'What is the password of the test database WPLoader should use?',
            ''
        );
        $installationData['testTablePrefix'] = $this->ask(
            'What is the table prefix of the test database WPLoader should use?',
            'wp_'
        );
        $installationData['testSiteWpUrl'] = $this->ask(
            'What is the URL the test site?',
            'http://wp.test'
        );
        $installationData['testSiteWpUrl'] = rtrim($installationData['testSiteWpUrl'], '/');
        $url = parse_url($installationData['testSiteWpUrl']);
        $installationData['urlScheme'] = empty($url['scheme']) ? 'http' : $url['scheme'];
        $installationData['testSiteWpDomain'] = empty($url['host']) ? 'example.com' : $url['host'];
        $installationData['urlPort'] = empty($url['port']) ? '' : ':' . $url['port'];
        $installationData['urlPath'] = empty($url['path']) ? '' : $url['path'];
        $adminEmailCandidate = "admin@{$installationData['testSiteWpDomain']}";
        $installationData['testSiteAdminEmail'] = $this->ask(
            'What is the email of the test site WordPress administrator?',
            $adminEmailCandidate
        );
        $installationData['title'] = $this->ask('What is the title of the test site?', 'Test');
        $installationData['testSiteAdminUsername'] = $this->ask(
            'What is the login of the administrator user of the test site?',
            'admin'
        );
        $installationData['testSiteAdminPassword'] = $this->ask(
            'What is the password of the administrator user of the test site?',
            'password'
        );

        $sut = '';

        while (!in_array($sut, ['plugin', 'theme', 'both'])) {
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
                $plugin = $this->ask(
                    'Please enter the plugin <comment>folder/plugin.php</comment> (leave blank when done)',
                    ''
                );
                $installationData['plugins'][] = $plugin;
            } while (!empty($plugin));
        }

        $installationData['plugins'] = array_filter($installationData['plugins']);
        if (!empty($installationData['mainPlugin'])) {
            $installationData['plugins'] = $installationData['mainPlugin'];
        }

        return $installationData;
    }

    protected function checkEnvFileExistence()
    {
        $filename = $this->workDir . DIRECTORY_SEPARATOR . $this->envFileName;

        if (file_exists($filename)) {
            $basename = basename($filename);
            $message = "Found a previous {$basename} file."
                       . PHP_EOL . "Remove the existing {$basename} file or specify a different name for the env file.";
            throw new RuntimeException($message);
        }
    }

    protected function normalizePath($path)
    {
        $pathFrags = preg_split('/(\\/|\\\\)/u', $path);
        return implode('/', $pathFrags);
    }

    protected function creatEnvFile(array $installationData = [])
    {
        $filename = $this->workDir . DIRECTORY_SEPARATOR . $this->envFileName;

        $envKeys = [
            'testSiteDbHost',
            'testSiteDbName',
            'testSiteDbUser',
            'testSiteDbPassword',
            'testSiteTablePrefix',
            'testSiteWpUrl',
            'testSiteAdminUsername',
            'testSiteAdminPassword',
            'testSiteWpAdminPath',
            'wpRootFolder',
            'testDbName',
            'testDbHost',
            'testDbUser',
            'testDbPassword',
            'testTablePrefix',
            'testSiteWpDomain',
            'testSiteAdminEmail',
        ];

        $envEntries = array_intersect_key($installationData, array_combine($envKeys, $envKeys));

        $envFileLines = [];

        foreach ($envEntries as $key => $value) {
            $key = strtoupper(preg_replace('/([A-Z])/u', '_$1', $key));
            if (is_bool($value)) {
                $value ? 'true' : 'false';
            } elseif (null === $value) {
                $value = 'null';
            } else {
                $value = '"' . trim($value) . '"';
            }
            $envFileLines[] = "{$key}={$value}";
        }
        $envFileContents = implode("\n", $envFileLines);
        $written = file_put_contents($filename, $envFileContents);
        if (!$written) {
            $this->removeCreatedFiles();
            throw new RuntimeException("Could not write {$this->envFileName} file!");
        }
    }

    protected function removeCreatedFiles()
    {
        $files = ['codeception.yml', $this->envFileName];
        $dirs = ['tests'];
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

    protected function loadEnvFile()
    {
        $dotEnv = Dotenv::create($this->workDir, $this->envFileName);
        $dotEnv->load();
    }

    protected function createWpUnitSuite($actor = 'Wpunit', array $installationData = [])
    {
        $installationData = new Data($installationData);
        $WPLoader = !empty($installationData['activeModules']['WPLoader']) ? '- WPLoader' : '# - WPLoader';
        $suiteConfig = <<<EOF
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

        if (!empty($installationData['theme'])) {
            $theme = empty($installationData['parentTheme']) ?
                $installationData['theme']
                : "[{$installationData['parentTheme']}, {$installationData['theme']}]";
            $suiteConfig .= <<<EOF
            
            theme: {$theme}
EOF;
        }

        $plugins = $installationData['plugins'];
        $plugins = "'" . implode("', '", (array) $plugins) . "'";
        $suiteConfig .= <<< EOF
        
            plugins: [{$plugins}]
            activatePlugins: [{$plugins}]
EOF;

        $this->createSuite($installationData['wpunitSuiteSlug'], $actor, $suiteConfig);
    }

    protected function createFunctionalSuite($actor = 'Functional', array $installationData = [])
    {
        $installationData = new Data($installationData);
        $WPDb = !empty($installationData['activeModules']['WPDb']) ? '- WPDb' : '# - WPDb';
        $WPBrowser = !empty($installationData['activeModules']['WPBrowser']) ? '- WPBrowser' : '# - WPBrowser';
        $WPFilesystem = !empty($installationData['activeModules']['WPFilesystem']) ?
            '- WPFilesystem'
            : '# - WPFilesystem';
        $suiteConfig = <<<EOF
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
            dsn: 'mysql:host=%TEST_SITE_DB_HOST%;dbname=%TEST_SITE_DB_NAME%'
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
        WPFilesystem:
            wpRootFolder: '%WP_ROOT_FOLDER%'
            plugins: '/wp-content/plugins'
            mu-plugins: '/wp-content/mu-plugins'
            themes: '/wp-content/themes'
            uploads: '/wp-content/uploads'
EOF;
        $this->createSuite($installationData['functionalSuiteSlug'], $actor, $suiteConfig);
    }

    protected function createAcceptanceSuite($actor = 'Acceptance', array $installationData = null)
    {
        $installationData = new Data($installationData);
        $WPDb = !empty($installationData['activeModules']['WPDb']) ? '- WPDb' : '# - WPDb';
        $WPBrowser = !empty($installationData['activeModules']['WPBrowser']) ? '- WPBrowser' : '# - WPBrowser';
        $suiteConfig = <<<EOF
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
            dsn: 'mysql:host=%TEST_SITE_DB_HOST%;dbname=%TEST_SITE_DB_NAME%'
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
EOF;
        $this->createSuite($installationData['acceptanceSuiteSlug'], $actor, $suiteConfig);
    }

    protected function getDefaultInstallationData()
    {
        return [];
    }

    protected function parseEnvName()
    {
        $envFileName = trim($this->envFileName);
        if (strpos($envFileName, '.env') !== 0) {
            $message = 'Please specify an env file name starting with ".env", e.g. ".env.testing" or '
                .'".env.development"';
            throw new RuntimeException($message);
        }
    }

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
            .'I acknowledge wp-browser should run on development servers only, '
            .'that I have made a backup of my files and database contents before proceeding.'
            .'</warning>',
            true
        );
        echo PHP_EOL;
        if (!$acknowledge) {
            $this->say('<info>The command did not do anything, nothing changed.</info>');
            $this->say('<info>'
                       .'Setup a WordPress installation and database dedicated to development and '
                       .'restart this command when ready using `vendor/bin/codecept init wpbrowser`.'
                       .'</info>');
            echo PHP_EOL;
            $this->say('<info>See you soon!</info>');
            exit(0);
        }
        echo PHP_EOL;
    }
}

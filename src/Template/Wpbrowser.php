<?php

namespace lucatume\WPBrowser\Template;

use Codeception\Template\Bootstrap;
use lucatume\WPBrowser\Utils\Db;
use lucatume\WPBrowser\Utils\Env;
use lucatume\WPBrowser\Utils\Filesystem;
use lucatume\WPBrowser\Utils\Url;
use RuntimeException;
use Symfony\Component\Yaml\Yaml;
use Codeception\Extension\RunFailed;

class Wpbrowser extends Bootstrap
{
    use WithInjectableHelpers;

    private bool $quiet = false;
    private string $envFileName = '';
    private ?array $installationData = null;
    private bool $createHelpers = true;
    private bool $createActors = true;
    private bool $createSuiteConfigFiles = true;

    public function setup(bool $interactive = true): void
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $this->checkInstalled($this->workDir);

        $input = $this->input;

        $this->quiet = (bool)$this->input->getOption('quiet');
        $noInteraction = (bool)$this->input->getOption('no-interaction');

        if ($noInteraction || $this->quiet) {
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
            $interactive = !preg_match('/^[nN]/', $interactive);
        }

        $installationData = $this->getInstallationData($interactive);
        $arrayInstallationData = $installationData;

        try {
            $this->createGlobalConfig();
            $this->writeEnvFile($installationData);
            Env::loadEnvMap(Env::envFile($this->envFileName));

            $this->createWpUnitSuite('wpunit', $arrayInstallationData);
            $this->say("tests/wpunit created               "
                . '<- WordPress unit and integration tests');
            $this->say("tests/wpunit.suite.yml written     "
                . '<- WordPress unit and integration tests suite configuration');

            $this->createAcceptanceSuite('end2end', $arrayInstallationData);
            $this->say("tests/end2end created           "
                . "<- end2end tests");
            $this->say("tests/end2end.suite.yml written "
                . "<- end2end tests suite configuration");
        } catch (\Exception $e) {
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
            $this->saySuccess("Codeception is installed for the end2end and wpunit suites. ");
        } else {
            $this->saySuccess("Codeception has created the files for the end2end and wpunit suites, "
                . 'but the modules are not activated');
        }
        $this->say('Some commands have been added in the Codeception configuration file: '
            . 'check them out using <comment>codecept --help</comment>');
        $this->say(" --- ");
        $this->say();

        // @todo review.
        $this->say("<bold>Next steps:</bold>");
        $this->say('0. <bold>Create the databases used by the modules</bold>; wp-browser will not do it for you!');
        $this->say('1. <bold>Install and configure WordPress</bold> activating the theme and plugins you need to create'
            . ' a database dump in <comment>tests/_data/dump.sql</comment>');
        $this->say("2. Edit <bold>tests/end2end.suite.yml</bold> to make sure WPDb "
            . 'and WPBrowser configurations match your local setup; change WPBrowser to WPWebDriver to '
            . 'enable browser testing');
        $this->say("3. Edit <bold>tests/wpunit.suite.yml</bold> to make sure WPLoader "
            . 'configuration matches your local setup');
        $this->say("4. Create your first end2end tests using <comment>codecept "
            . "g:cest end2end WPFirst</comment>");
        $this->say("5. Write a test in <bold>tests/end2end/WPFirstCest.php</bold>");
        $this->say("6. Run tests using: <comment>codecept run end2end</comment>");
        $this->say(" --- ");
        $this->say();
        $this->sayWarning('Please note: due to WordPress extended use of globals and constants you should avoid running'
            . ' all the suites at the same time.');
        $this->say('Run each suite separately, like this: <comment>codecept run unit && codecept run '
            . "wpunit</comment>, to avoid problems.");
    }

    protected function say(string $message = ''): void
    {
        if ($this->quiet) {
            return;
        }
        parent::say($message);
    }

    public function createGlobalConfig(): void
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
                'enabled' => [RunFailed::class],
                'commands' => $this->getAddtionalCommands(),
            ],
            'params' => [
                trim($this->envFileName),
            ],
        ];

        $str = Yaml::dump($basicConfig, 4);
        if ($this->namespace) {
            $namespace = rtrim($this->namespace, '\\');
            $str = "namespace: $namespace\n" . $str;
        }
        $this->createFile('codeception.dist.yml', $str);
    }

    private function getAddtionalCommands(): array
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

    private function getInstallationData(bool $interactive): array
    {
        if ($this->installationData !== null) {
            return $this->installationData;
        }

        if (!$interactive) {
            $this->installationData = $this->getDefaultInstallationData();
        } else {
            $this->installationData = $this->askForInstallationData();
        }

        return $this->installationData;
    }

    private function askForInstallationData(): array
    {
        $installationData = [
            'activeModules' => [
                'WPDb' => true,
                'WPBrowser' => true,
                'WordPress' => true,
                'WPLoader' => true,
            ],
        ];

        $installationData['acceptanceSuite'] = 'acceptance';

        $this->say('---');
        $this->say();

        $this->envFileName = '.env.testing';

        $this->checkEnvFileExistence();

        echo PHP_EOL;
        $this->sayInfo('WPLoader and WordPress modules need to access the WordPress files to work.');
        echo PHP_EOL;

        $installationData['wpRootFolder'] = $this->normalizePath($this->ask(
            'Where is WordPress installed?',
            '/var/www/html'
        ));
        $installationData['testSiteWpAdminPath'] = $this->ask(
            'What is the path, relative to WordPress root URL, of the admin area of the test site?',
            '/wp-admin'
        );
        $normalizedAdminPath = trim(
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
            'test'
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
            'http://wordpress.test'
        );
        $installationData['testSiteWpUrl'] = rtrim($installationData['testSiteWpUrl'], '/');
        $installationData['testSiteWpDomain'] = Url::getDomain($installationData['testSiteWpUrl']);
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
            if (preg_match('/^[y|Y]/', $isChildTheme)) {
                $installationData['parentTheme'] = $this->ask(
                    'What is the slug of the parent theme?',
                    'twentyseventeen'
                );
            }
            $installationData['theme'] = $this->ask('What is the slug of the theme?', 'my-theme');
        } else {
            $isChildTheme = $this->ask('Are you using a child theme?', 'no');
            if (preg_match('/^[y|Y]/', $isChildTheme)) {
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

        if (preg_match('/^[y|Y]/', $activateFurtherPlugins)) {
            do {
                $plugin = $this->ask(
                    'Please enter the plugin <comment>folder/plugin.php</comment> (leave blank when done)',
                    ''
                );
                $installationData['plugins'][] = $plugin;
            } while (!empty($plugin));
        }

        $installationData['plugins'] = array_map('trim', array_filter($installationData['plugins']));
        if (!empty($installationData['mainPlugin'])) {
            $installationData['plugins'][] = $installationData['mainPlugin'];
        }

        return $installationData;
    }

    private function checkEnvFileExistence(): void
    {
        $filename = $this->workDir . DIRECTORY_SEPARATOR . $this->envFileName;

        if (file_exists($filename)) {
            $basename = basename($filename);
            $message = "Found a previous $basename file."
                . PHP_EOL . "Remove the existing $basename file or specify a " .
                "different name for the env file.";
            throw new RuntimeException($message);
        }
    }

    private function normalizePath(string $path): string
    {
        $pathFrags = preg_split('#([/\\\])#u', $path) ?: [];

        return implode('/', $pathFrags);
    }

    public function writeEnvFile(array $installationData): void
    {
        $pwd = getcwd() ?: $this->workDir;
        $filename = $pwd . DIRECTORY_SEPARATOR . $this->envFileName;
        $envVars = $this->getEnvFileVars($installationData);

        $envFileLines = implode("\n",
            array_map(static function ($key, $value) {
                return "$key=$value";
            }, array_keys($envVars), $envVars));

        if (!file_put_contents($filename, $envFileLines . "\n")) {
            $this->removeCreatedFiles();
            throw new RuntimeException("Could not write $this->envFileName file!");
        }
    }

    private function removeCreatedFiles(): void
    {
        $files = ['codeception.yml', $this->envFileName];
        $dirs = ['tests'];
        foreach ($files as $file) {
            if (is_file(getcwd() . '/' . $file)) {
                unlink(getcwd() . '/' . $file);
            }
        }
        foreach ($dirs as $dir) {
            if (file_exists(getcwd() . '/' . $dir)) {
                Filesystem::rrmdir(getcwd() . '/' . $dir);
            }
        }
    }

    private function createWpUnitSuite(string $actor = 'Wpunit', array $installationData = []): void
    {
        $WPLoader = !empty($installationData['activeModules']['WPLoader']) ? '- WPLoader' : '# - WPLoader';
        $suiteConfig = <<<EOF
# Codeception Test Suite Configuration
#
# Suite for unit or integration tests that require WordPress functions and classes.

actor: $actor$this->actorSuffix
modules:
    enabled:
        $WPLoader
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

            theme: $theme
EOF;
        }

        $plugins = $installationData['plugins'];
        $plugins = "'" . implode("', '", (array)$plugins) . "'";
        $suiteConfig .= <<< EOF

            plugins: [$plugins]
            activatePlugins: [$plugins]
EOF;

        $this->createSuite('wpunit', $actor, $suiteConfig);
    }

    protected function createUnitSuite(string $actor = 'Unit'): void
    {
        $suiteConfig = <<<EOF
# Codeception Test Suite Configuration
#
# Suite for unit tests not relying WordPress code.

actor: $actor$this->actorSuffix
modules:
    enabled:
        - Asserts
    step_decorators: ~        
EOF;
        $this->createSuite('unit', $actor, $suiteConfig);
    }

    protected function createAcceptanceSuite(string $actor = 'Acceptance', array $installationData = []): void
    {
        $WPDb = !empty($installationData['activeModules']['WPDb']) ? '- WPDb' : '# - WPDb';
        $WPBrowser = !empty($installationData['activeModules']['WPBrowser']) ? '- WPBrowser' : '# - WPBrowser';
        $suiteConfig = <<<EOF
# Codeception Test Suite Configuration
#
# Suite for end2end tests.
# Perform tests in browser using the WPWebDriver or WPBrowser.
# Use WPDb to set up your initial database fixture.
# If you need both WPWebDriver and WPBrowser tests - create a separate suite.

actor: $actor$this->actorSuffix
modules:
    enabled:
        $WPDb
        $WPBrowser
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
        $this->createSuite('end2end', $actor, $suiteConfig);
    }

    private function askForAcknowledgment(): void
    {
        $this->say('Welcome to wp-browser, a complete testing suite for WordPress based on Codeception and PHPUnit!');
        $this->say('This command will guide you through the initial setup for your project.');
        echo PHP_EOL;
        $this->say('If you are new to wp-browser please take the time to read this guide:');
        $this->say('<info>https://github.com/lucatume/wp-browser#initial-setup</info>');
        echo PHP_EOL;
        $acknowledge = $this->ask(
            '<info>'
            . 'I acknowledge wp-browser should run on development servers only, '
            . 'that I have made a backup of my files and database contents before proceeding.'
            . '</info>',
            true
        );
        echo PHP_EOL;
        if (!$acknowledge) {
            $this->say('The command did not do anything, nothing changed.');
            $this->say('Setup a WordPress installation and database dedicated to development and '
                . 'restart this command when ready using `vendor/bin/codecept init wpbrowser`.');
            $this->say('See you soon!');
            echo PHP_EOL;
            exit(0);
        }
        echo PHP_EOL;
    }

    public function setWorkDir(string $workDir): void
    {
        chdir($workDir);
        $this->workDir = $workDir;
    }

    public function setInstallationData(array $installationData): void
    {
        $this->installationData = $installationData;
    }

    public function getDefaultInstallationData(): array
    {
        $installationData = [
            'testSiteDbHost' => 'localhost',
            'testSiteDbName' => 'test',
            'testSiteDbUser' => 'root',
            'testSiteDbPassword' => 'password',
            'testSiteTablePrefix' => 'wp_',
            'testSiteWpUrl' => 'http://wordpress.test',
            'testSiteWpDomain' => 'wordpress.test',
            'testSiteAdminUsername' => 'admin',
            'testSiteAdminPassword' => 'password',
            'testSiteAdminEmail' => 'admin@wordpress.test',
            'testSiteWpAdminPath' => '/wp-admin',
            'wpRootFolder' => '/var/www/html',
            'testDbName' => 'test',
            'testDbHost' => 'localhost',
            'testDbUser' => 'root',
            'testDbPassword' => 'password',
            'testTablePrefix' => 'wp_',
            'title' => 'WP Test',
            // deactivate all modules that could trigger exceptions when initialized with sudo values
            'activeModules' => ['WPDb' => false, 'WordPress' => false, 'WPLoader' => false],
        ];
        $this->envFileName = '.env.testing';

        return $installationData;
    }

    public function getEnvFileVars(array $installationData): array
    {
        $testSiteDsnMap = Db::dbDsnMap($installationData['testSiteDbHost']);
        $testSiteDsnMap['dbname'] = $installationData['testSiteDbName'];
        $testDbDsnMap = Db::dbDsnMap($installationData['testDbHost']);
        return [
            'TEST_SITE_DB_DSN' => Db::dbDsnString($testSiteDsnMap),
            'TEST_SITE_DB_HOST' => Db::dbDsnString($testDbDsnMap, true),
            'TEST_SITE_DB_NAME' => $testSiteDsnMap('dbname', 'wordpress'),
            'TEST_SITE_DB_USER' => $installationData['testSiteDbUser'],
            'TEST_SITE_DB_PASSWORD' => $installationData['testSiteDbPassword'],
            'TEST_SITE_TABLE_PREFIX' => $installationData['testSiteTablePrefix'],
            'TEST_SITE_ADMIN_USERNAME' => $installationData['testSiteAdminUsername'],
            'TEST_SITE_ADMIN_PASSWORD' => $installationData['testSiteAdminPassword'],
            'TEST_SITE_WP_ADMIN_PATH' => $installationData['testSiteWpAdminPath'],
            'WP_ROOT_FOLDER' => $installationData['wpRootFolder'],
            'TEST_DB_NAME' => $installationData['testDbName'],
            'TEST_DB_HOST' => Db::dbDsnString($testDbDsnMap, true),
            'TEST_DB_USER' => $installationData['testDbUser'],
            'TEST_DB_PASSWORD' => $installationData['testDbPassword'],
            'TEST_TABLE_PREFIX' => $installationData['testTablePrefix'],
            'TEST_SITE_WP_URL' => $installationData['testSiteWpUrl'],
            'TEST_SITE_WP_DOMAIN' => Url::getDomain($installationData['testSiteWpUrl']),
            'TEST_SITE_ADMIN_EMAIL' => $installationData['testSiteAdminEmail'],
        ];
    }

    public function setCreateHelpers(bool $createHelpers): static
    {
        $this->createHelpers = $createHelpers;

        return $this;
    }

    public function setCreateActors(bool $createActors): static
    {
        $this->createActors = $createActors;

        return $this;
    }

    public function setCreateSuiteConfigFiles(bool $createSuiteConfigFiles): static
    {
        $this->createSuiteConfigFiles = $createSuiteConfigFiles;

        return $this;
    }
}

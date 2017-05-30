<?php

namespace Codeception\Template;

use Symfony\Component\Yaml\Yaml;
use tad\WPBrowser\Template\Data;

class Wpbrowser extends Bootstrap {

    public function setup() {
        $this->checkInstalled($this->workDir);

        $input = $this->input;
        if ($input->getOption('namespace')) {
            $this->namespace = trim($input->getOption('namespace'), '\\') . '\\';
        }

        if ($input->hasOption('actor') && $input->getOption('actor')) {
            $this->actorSuffix = $input->getOption('actor');
        }

        $this->say(
            "<fg=white;bg=magenta> Bootstrapping wp-browser on top of Codeception </fg=white;bg=magenta>\n"
        );

        $this->createGlobalConfig();
        $this->say("File codeception.yml created       <- global configuration");

        $this->createDirs();

        if ($input->hasOption('empty') && $input->getOption('empty')) {
            return;
        }

        $interactive = $this->ask('wp-browser can set up the suites for you provided some information, would you like to set the suites up now?', 'yes');

        if (preg_match('/^(n|N).*/', $interactive)) {
            $installationData = [];
        } else {
            $installationData = $this->askForInstallationData();
        }

        $this->createUnitSuite();
        $this->say("tests/unit created                 <- unit tests");
        $this->say("tests/unit.suite.yml written       <- unit tests suite configuration");
        $this->createWpUnitSuite('Wpunit', $installationData);
        $this->say("tests/wpunit created               <- WordPress unit and integration tests");
        $this->say("tests/wpunit.suite.yml written     <- WordPress unit and integration tests suite configuration");
        $this->createFunctionalSuite('Functional', $installationData);
        $this->say("tests/functional created           <- functional tests");
        $this->say("tests/functional.suite.yml written <- functional tests suite configuration");
        $this->createAcceptanceSuite('Acceptance', $installationData);
        $this->say("tests/acceptance created           <- acceptance tests");
        $this->say("tests/acceptance.suite.yml written <- acceptance tests suite configuration");

        $this->say(" --- ");
        $this->say();
        $this->saySuccess('WPBrowser is installed for acceptance, functional, WordPress unit and unit testing');
        $this->say();

        $this->say("<bold>Next steps:</bold>");
        $this->say('0. <bold>Create the databases</bold> used by the modules: wp-browser will not do it for you!');
        $this->say('1. <bold>Install and configure WordPress</bold> activating the theme and plugins you need to create a database dump in <comment>tests/_data/dump.sql</comment>');
        $this->say('2. Edit <bold>tests/acceptance.suite.yml</bold> to configure WPDb and WPBrowser to match your local setup; change WPBrowser to WPWebDriver to enable browser testing');
        $this->say("3. Edit <bold>tests/functional.suite.yml</bold> to configure WordPress and WPDb to match your local setup");
        $this->say("4. Edit <bold>tests/wpunit.suite.yml</bold> to configure WPLoader to match your local setup");
        $this->say("5. Create your first acceptance tests using <comment>codecept g:cest acceptance First</comment>");
        $this->say("6. Write first test in <bold>tests/acceptance/FirstCest.php</bold>");
        $this->say("7. Run tests using: <comment>codecept run acceptance</comment>");
        $this->say();
        $this->sayWarning("Please note: due to WordPress extended use of globals and constants avoid running all the tests at the same time.");
        $this->say("Run each suite separately, like this: <comment>codecept run unit && codecept run integration</comment>, to avoid problems.");
    }

    public function createGlobalConfig() {
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
                'enabled'  => ['Codeception\Extension\RunFailed'],
                'commands' => $this->getAddtionalCommands(),
            ],
        ];

        $str = Yaml::dump($basicConfig, 4);
        if ($this->namespace) {
            $namespace = rtrim($this->namespace, '\\');
            $str = "namespace: $namespace\n" . $str;
        }
        $this->createFile('codeception.yml', $str);
    }

    protected function getAddtionalCommands() {
        return [
            'Codeception\\Command\\DbSnapshot',
            'Codeception\\Command\\GeneratePhpunitBootstrap',
            'Codeception\\Command\\GenerateWPAjax',
            'Codeception\\Command\\GenerateWPCanonical',
            'Codeception\\Command\\GenerateWPRestApi',
            'Codeception\\Command\\GenerateWPRestController',
            'Codeception\\Command\\GenerateWPRestPostTypeController',
            'Codeception\\Command\\GenerateWPUnit',
            'Codeception\\Command\\GenerateWPXMLRPC',
        ];
    }

    protected function askForInstallationData() {
        $installationData = [];
        $this->say('WPLoader and WordPress modules need to access the WordPress files to work');
        $installationData['wpRootFolder'] = $this->ask("Where is WordPress installed?", '/var/www/wp');
        $installationData['wpAdminPath'] = $this->ask('What is the path, relative to WordPress root folder, of the admin area?', '/wp-admin');
        $this->say('The WPDb module needs the database details to access the database used by WordPress');
        $installationData['dbName'] = $this->ask("What's the name of the database used by the WordPress installation?", 'wp');
        $installationData['dbHost'] = $this->ask("What's the host of the database used by the WordPress installation?", 'localhost');
        $installationData['dbUser'] = $this->ask("What's the user of the database used by the WordPress installation?", 'root');
        $installationData['dbPassword'] = $this->ask("What's the password of the database used by the WordPress installation?", '');
        $installationData['tablePrefix'] = $this->ask("What's the table prefix of the database used by the WordPress installation?", 'wp_');
        $this->say('WPLoader will reinstall a fresh WordPress installation before the tests; as such it needs the details you would typically provide when installing WordPress from scratch');
        $this->sayWarning('WPLoader should be configured to run on a dedicated database!');
        $installationData['wploaderDbName'] = $this->ask("What's the name of the database WPLoader should use?", 'wpTests');
        $installationData['wploaderDbHost'] = $this->ask("What's the host of the database WPLoader should use?", 'localhost');
        $installationData['wploaderDbUser'] = $this->ask("What's the user of the database WPLoader should use?", 'root');
        $installationData['wploaderDbPassword'] = $this->ask("What's the password of the database WPLoader should use?", '');
        $installationData['wplodaerTablePrefix'] = $this->ask("What's the table prefix of the database WPLoader should use?", 'wp_');

        $installationData['domain'] = $this->ask("What's the domain of  the WordPress installation?", 'wp.localhost');
        $url = parse_url($installationData['domain']);
        $url['host'] = empty($url['host']) ? 'example.com' : $url['host'];
        $url['port'] = empty($url['port']) ? '' : ':' . $url['port'];
        $url['path'] = empty($url['path']) ? '' : $url['path'];
        $adminEmailCandidate = "admin@{$url['host']}";
        $this->say('WordPress will use the administrator email to send notifications during the tests: you should use a mail address managed by a service like <bold>MailCatcher</bold>');
        $installationData['adminEmail'] = $this->ask("What's the email of the WordPress site administrator?", $adminEmailCandidate);
        $installationData['title'] = $this->ask("What's the title of the WordPress site?", 'Test');
        //			plugins: ['hello.php', 'my-plugin/my-plugin.php']
        $sut = $this->ask("Are you testing a plugin or a theme?", 'plugin');
        $installationData['plugins'] = [];
        if ($sut === 'plugin') {
            $installationData['mainPlugin'] = $this->ask('What is the <comment>folder/plugin.php</comment> name of the plugin?', 'my-plugin/my-plugin.php');
        } else {
            $isChildTheme = $this->ask('Are you developing a child theme?', 'no');
            if (preg_match('/^(y|Y)/', $isChildTheme)) {
                $installationData['parentTheme'] = $this->ask('What is the slug of the parent theme?', 'twentyseventeen');
            }
            $installationData['theme'] = $this->ask('What is the slug of the theme?', 'my-theme');

        }
        $activateFurtherPlugins = $this->ask('Does your plugin or theme needs additional plugins to be activated to work?', 'no');

        if (preg_match('/^(y|Y)/', $activateFurtherPlugins)) {
            do {
                $plugin = $this->ask('Please enter the plugin <comment>folder/plugin.php</comment> (leave blank when done)', '');
                $installationData['plugins'][] = $plugin;
            } while ( ! empty($plugin));
        }

        $installationData['plugins'] = array_filter($installationData['plugins']);
        if ( ! empty($installationData['mainPlugin'])) {
            $installationData['plugins'] = $installationData['mainPlugin'];
        }

        return $installationData;
    }

    protected function createWpUnitSuite($actor = 'Wpunit', array $installationData = []) {
        $installationData = new Data($installationData);
        $suiteConfig = <<<EOF
# Codeception Test Suite Configuration
#
# Suite for unit or integration tests that require WordPress functions and classes.

actor: $actor{$this->actorSuffix}
modules:
    enabled:
        - WPLoader
        - \\{$this->namespace}Helper\Wpunit
    config:
        WPLoader:
            wpRootFolder: "{$installationData['wpRootFolder']}"
            dbName: "{$installationData['wploaderDbName']}"
            dbHost: "{$installationData['wploaderDbHost']}"
            dbUser: "{$installationData['wploaderDbUser']}"
            dbPassword: "{$installationData['wploaderDbPassword']}"
            tablePrefix: "{$installationData['wploaderTablePrefix']}"
            domain: "{$installationData['domain']}"
            adminEmail: "{$installationData['adminEmail']}"
            title: "{$installationData['title']}"
EOF;

        if ( ! empty($installationData['theme'])) {
            $theme = empty($installationData['parentTheme']) ?
                $installationData['theme']
                : "[{$installationData['parentTheme']}, {$installationData['theme']}]";
            $suiteConfig .= <<<EOF
            
            theme: {$theme}
EOF;
        }

        $plugins = $installationData['plugins'];
        $plugins = "'" . implode("', '", (array)$plugins) . "'";
        $suiteConfig .= <<< EOF
        
            plugins: [{$plugins}]
            activatePlugins: [{$plugins}]
EOF;

        $this->createSuite('wpunit', $actor, $suiteConfig);
    }

    protected function createFunctionalSuite($actor = 'Functional', array $installationData = []) {
        $installationData = new Data($installationData);
        $suiteConfig = <<<EOF
# Codeception Test Suite Configuration
#
# Suite for functional tests
# Emulate web requests and make WordPress process them

actor: $actor{$this->actorSuffix}
modules:
    enabled:
        - WPDb
        - WordPress
        - Asserts
        - \\{$this->namespace}Helper\Functional
    config:
        WPDb:
            dsn: 'mysql:host={$installationData['dbHost']};dbname={$installationData['dbName']}'
            user: '{$installationData['dbUser']}'
            password: '{$installationData['dbPassword']}'
            dump: 'tests/_data/dump.sql'
            populate: true
            cleanup: true
            url: 'http://{$installationData['domain']}'
            urlReplacement: true
            tablePrefix: '{$installationData['tablePrefix']}'
        WordPress:
            depends: WPDb
            wpRootFolder: '{$installationData['wpRootFolder']}'
            adminUsername: '{$installationData['adminUsername']}'
            adminPassword: '{$installationData['adminPassword']}'
            adminPath: '{$installationData['wpAdminPath']}'
EOF;
        $this->createSuite('functional', $actor, $suiteConfig);
    }

    protected function createAcceptanceSuite($actor = 'Acceptance', array $installationData = null) {
        $installationData = new Data($installationData);
        $suiteConfig = <<<EOF
# Codeception Test Suite Configuration
#
# Suite for acceptance tests.
# Perform tests in browser using the WPWebDriver or WPBrowser.
# Use WPDb to set up your initial database fixture.
# If you need both WPWebDriver and WPBrowser tests - create a separate suite.

actor: $actor{$this->actorSuffix}
modules:
    enabled:
        - WPDb
        - WPBrowser
        - \\{$this->namespace}Helper\Acceptance
    config:
        WPDb:
            dsn: 'mysql:host={$installationData['dbHost']};dbname={$installationData['dbName']}'
            user: '{$installationData['dbUser']}'
            password: '{$installationData['dbPassword']}'
            dump: 'tests/_data/dump.sql'
            populate: true #import the dump before the tests
            cleanup: true #import the dump between tests
            url: 'http://{$installationData['domain']}'
            urlReplacement: true #replace the hardcoded dump URL with the one above
            tablePrefix: '{$installationData['tablePrefix']}'
        WPBrowser:
            url: 'http://{$installationData['domain']}'
            adminUsername: '{$installationData['adminUsername']}'
            adminPassword: '{$installationData['adminPassword']}'
            adminPath: '{$installationData['wpAdminPath']}'
EOF;
        $this->createSuite('acceptance', $actor, $suiteConfig);
    }

    protected function getDefaultInstallationData() {
        return [];
    }
}
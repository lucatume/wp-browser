<?php

namespace lucatume\WPBrowser\Template;

use Codeception\Extension\RunFailed;
use Codeception\Template\Bootstrap;
use lucatume\WPBrowser\Command\GenerateWPAjax;
use lucatume\WPBrowser\Command\GenerateWPCanonical;
use lucatume\WPBrowser\Command\GenerateWPRestApi;
use lucatume\WPBrowser\Command\GenerateWPRestController;
use lucatume\WPBrowser\Command\GenerateWPRestPostTypeController;
use lucatume\WPBrowser\Command\GenerateWPUnit;
use lucatume\WPBrowser\Command\GenerateWPXML;
use lucatume\WPBrowser\Command\GenerateWPXMLRPC;
use lucatume\WPBrowser\Command\RunAll;
use lucatume\WPBrowser\Exceptions\RuntimeException;
use lucatume\WPBrowser\Project\PluginProject;
use lucatume\WPBrowser\Project\ProjectFactory;
use lucatume\WPBrowser\Project\ProjectInterface;
use lucatume\WPBrowser\Project\ThemeProject;
use Symfony\Component\Yaml\Yaml;

class Wpbrowser extends Bootstrap
{
    /**
     * @throws RuntimeException
     */
    public function setup(): void
    {
        $this->say('Set up <info>wp-browser</info> to test your WordPress project.');
        $this->say('See Codeception documentation at <info>https://codeception.com/docs/Introduction</info>.');
        $this->say('See wp-browser documentation at <info>https://wpbrowser.wptestkit.dev.</info>');
        $this->say('You can quit this process at any time with <info>CTRL+C</info>.');
        $this->say('');
        $cwd = getcwd();

        if ($cwd === false) {
            throw new RuntimeException('Could not get the current working directory.');
        }

        $project = ProjectFactory::fromDir($cwd);
        $detectedProjectTypeCorrect = $this->ask(
            "This looks like <info>a WordPress {$project->getType()}</info>: is this correct?",
            true
        );

        if (!$detectedProjectTypeCorrect) {
            /** @var string $projectType */
            $projectType = $this->ask("What type of WordPress project is this?", [
                'plugin',
                'theme',
                'site',
            ]);
            $project = ProjectFactory::make($projectType, $cwd);
        }

        $input = $this->input;
        $namespace = $input->hasOption('namespace') ? $input->getOption('namespace') : null;
        if ($namespace && is_string($namespace)) {
            $this->namespace = trim($namespace, '\\');
        }

        $actor = $input->hasOption('actor') ? $input->getOption('actor') : null;
        if ($actor && is_string($actor)) {
            $this->actorSuffix = $actor;
        }

        $this->say("Bootstrapping <info>Codeception</info> and <info>wp-browser</info> " .
            "for a <info>{$project->getType()}</info> project ...");

        $this->createGlobalConfig();
        $this->say('Created Codeception configuration file <info>codeception.yml</info>.');

        $this->createDirs();
        $this->say('Created <info>tests</info> directory and sub-directories.');

        if ($input->hasOption('empty') && $input->getOption('empty')) {
            return;
        }

        $this->createEnvFile();
        $this->createIntegrationSuite($project);
        $this->createEndToEndSuite($project);

        $this->say('');
        $this->saySuccess('All done, time to test!');
        $this->say('Customize the <info>tests/.env</info> file to match your set up and start testing.');
    }

    public function createGlobalConfig(): void
    {
        $basicConfig = [
            'support_namespace' => $this->supportNamespace,
            'paths' => [
                'tests' => 'tests',
                'output' => $this->outputDir,
                'data' => $this->dataDir,
                'support' => $this->supportDir,
                'envs' => $this->envsDir,
            ],
            'actor_suffix' => 'Tester',
            'params' => ['tests/.env'],
            'extensions' => [
                'enabled' => [RunFailed::class],
                'commands' => [
                    RunAll::class,
                    GenerateWPUnit::class,
                    GenerateWPRestApi::class,
                    GenerateWPRestController::class,
                    GenerateWPRestPostTypeController::class,
                    GenerateWPAjax::class,
                    GenerateWPCanonical::class,
                    GenerateWPXML::class,
                    GenerateWPXMLRPC::class,
                ]
            ]
        ];

        $str = Yaml::dump($basicConfig, 4);
        if ($this->namespace !== '') {
            $namespace = rtrim($this->namespace, '\\');
            $str = "namespace: {$namespace}\n" . $str;
        }
        $this->createFile('codeception.yml', $str);
    }

    private function createIntegrationSuite(ProjectInterface $project): void
    {
        $plugins = '';
        if ($project instanceof PluginProject) {
            $plugins = $project->getPluginsString();
        }
        $theme = '';
        if ($project instanceof ThemeProject) {
            $theme = $project->getThemeString();
        }

        $suiteConfig = <<<EOF
# Integration suite configuration
#
# Run integration and "WordPress unit" tests.

actor: Integration{$this->actorSuffix}
bootstrap: _bootstrap.php
modules:
    enabled:
        - lucatume\WPBrowser\Module\WPLoader
    config:
        lucatume\WPBrowser\Module\WPLoader:
           wpRootFolder: "%WORDPRESS_ROOT_DIR%" 
           dbUrl: '%WORDPRESS_DB_URL%'
           wpDebug: true
           tablePrefix: '%TEST_TABLE_PREFIX%'
           domain: '%WORDPRESS_DOMAIN%'
           adminEmail: 'admin@%WORDPRESS_DOMAIN%'
           title: 'Integration Tests'
           plugins: ['$plugins']
           theme: '$theme' 
EOF;
        $this->createSuite('Integration', 'Integration', $suiteConfig);

        $this->say('Created <info>Integration</info> suite and configuration.');
    }

    private function createEnvFile(): void
    {
        $envFileContents = <<< ENV
# The path to the WordPress root directory, the one containing the wp-load.php file.
# This can be a relative path from the directory that contains the codeception.yml file,
# or an absolute path.
WORDPRESS_ROOT_DIR=vendor/wordpress/wordpress

# Tests will require a MySQL database to run.
# The database will be created if it does not exist.
# Do not use a database that contains important data!
WORDPRESS_DB_URL=mysql://User:secret!@127.0.0.1:3306/test

# The Integration suite will use this table prefix for the WordPress tables.
TEST_TABLE_PREFIX=test_

# This table prefix used by the WordPress site in end-to-end tests.
WORDPRESS_TABLE_PREFIX=wp_

# The URL and domain of the WordPress site used in end-to-end tests.
WORDPRESS_URL=http://wpbrowser.test
WORDPRESS_DOMAIN=wpbrowser.test

# The username and password of the administrator user of the WordPress site used in end-to-end tests.
WORDPRESS_ADMIN_USER=admin
WORDPRESS_ADMIN_PASSWORD=password

# The host and port of the ChromeDriver server that will be used in end-to-end tests.
CHROMEDRIVER_HOST=localhost
CHROMEDRIVER_PORT=4444
ENV;

        file_put_contents('tests/.env', $envFileContents);
        $this->say('Created <info>tests/.env</info> file.');
        putenv('WORDPRESS_DB_URL=mysql://User:secret!@127.0.0.1:3306/test');
        $_ENV['WORDPRESS_DB_URL'] = 'mysql://User:secret!@127.0.0.1:3306/test';
    }

    private function createEndToEndSuite(ProjectInterface $project): void
    {
        $suiteConfig = <<<EOF
# Integration suite configuration
#
# Run integration and "WordPress unit" tests.

actor: EndToEnd{$this->actorSuffix}
bootstrap: _bootstrap.php
modules:
    enabled:
        - lucatume\WPBrowser\Module\WPWebDriver
        - lucatume\WPBrowser\Module\WPDb
        - lucatume\WPBrowser\Module\WPFilesystem
    config:
        lucatume\WPBrowser\Module\WPWebDriver:
            url: '%WORDPRESS_URL%'
            adminUsername: '%WORDPRESS_ADMIN_USER%'
            adminPassword: '%WORDPRESS_ADMIN_PASSWORD%'
            adminPath: '/wp-admin'
            browser: chrome
            host: '%CHROMEDRIVER_HOST%'
            port: '%CHROMEDRIVER_PORT%'
            window_size: false
            capabilities:
                chromeOptions:
                    args: ["--headless", "--disable-gpu", "--proxy-server='direct://'", "--proxy-bypass-list=*"]
        lucatume\WPBrowser\Module\WPDb:
            dbUrl: '%WORDPRESS_DB_URL%'
            dump: 'tests/_data/dump.sql'
            populate: true
            cleanup: true
            reconnect: false
            url: '%WORDPRESS_URL%'
            tablePrefix: '%WORDPRESS_TABLE_PREFIX%'
        lucatume\WPBrowser\Module\WPFilesystem:
            wpRootFolder: '%WORDPRESS_ROOT_DIR%'
EOF;
        $this->createSuite('EndToEnd', 'EndToEnd', $suiteConfig);

        $this->say('Created <info>EndToEnd</info> suite and configuration.');
    }
}

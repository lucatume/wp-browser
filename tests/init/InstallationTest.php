<?php

namespace lucatume\WPBrowser\Tests;

use lucatume\WPBrowser\Template\Wpbrowser;
use lucatume\WPBrowser\Tests\Traits\WithUopz;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use tad\Codeception\SnapshotAssertions\SnapshotAssertions;

require_once __DIR__ . '/BaseTest.php';

class InstallationTest extends BaseTest
{
    use SnapshotAssertions;
    use WithUopz;

    /**
     * It should correctly scaffold quiet installation
     *
     * @test
     */
    public function should_correctly_scaffold_quiet_installation(): void
    {
        $input = $this->makeEmpty(ArrayInput::class, [
            'get_option' => static function ($option) {
                return $option === 'quiet' || $option === 'no-interaction' ? true : null;
            },
            'has_option' => false,
        ]);
        $init             = new Wpbrowser($input, new NullOutput());
        $workDir          = codecept_output_dir('init/installationTest/quiet');
        $this->createWorkDir($workDir);
        $this->createComposerJsonFile($workDir);
        $init->setWorkDir($workDir);
        $init->setCreateActors(false)->setCreateHelpers(false);
        $init->setup(false);

        $this->assertMatchesDirectorySnapshot($workDir);
    }

    /**
     * It should correctly scaffold with default values
     *
     * @test
     */
    public function should_correctly_scaffold_with_default_values(): void
    {
        $input = $this->makeEmpty(ArrayInput::class, [
            'get_option' => static function ($option) {
                return $option === 'quiet' || $option === 'no-interaction' ? true : null;
            },
            'has_option' => false,
        ]);
        $init             = new Wpbrowser($input, new NullOutput());
        $workDir          = codecept_output_dir('init/installationTest/default');
        $init->setInstallationData($init->getDefaultInstallationData()->toArray());
        $this->createWorkDir($workDir);
        $init->setWorkDir($workDir);
        $this->createComposerJsonFile($workDir);
        $init->setCreateActors(false)->setCreateHelpers(false);
        $init->setup(true);

        $this->assertMatchesDirectorySnapshot($workDir);
    }

    /**
     * It should correctly scaffold with mysql on localhost values
     *
     * @test
     */
    public function should_correctly_scaffold_with_mysql_on_localhost_values(): void
    {
        $input = $this->makeEmpty(ArrayInput::class, [
            'get_option' => static function ($option) {
                return $option === 'quiet' || $option === 'no-interaction' ? true : null;
            },
            'has_option' => false,
        ]);
        $init             = new Wpbrowser($input, new NullOutput());
        $workDir          = codecept_output_dir('init/installationTest/mysql_on_localhost');
        $init->setInstallationData(array_merge($init->getDefaultInstallationData()->toArray(), [
            'testSiteDbUser'     => 'root',
            'testSiteDbPassword' => 'secret',
            'testSiteDbName' => 'wordpress',
            'testSiteDbHost' => 'localhost',
            'testDbUser'     => 'root',
            'testDbPassword' => 'secret',
            'testDbName' => 'wordpress_tests',
            'testDbHost' => 'localhost'
        ]));
        $this->createWorkDir($workDir);
        $init->setWorkDir($workDir);
        $this->createComposerJsonFile($workDir);
        $init->setCreateActors(false)->setCreateHelpers(false);
        $init->setup(true);

        $this->assertMatchesDirectorySnapshot($workDir);
    }

    /**
     * It should correctly scaffold with mysql on ip address values
     *
     * @test
     */
    public function should_correctly_scaffold_with_mysql_on_ip_address_values(): void
    {
        $input = $this->makeEmpty(ArrayInput::class, [
            'get_option' => static function ($option) {
                return $option === 'quiet' || $option === 'no-interaction' ? true : null;
            },
            'has_option' => false,
        ]);
        $init             = new Wpbrowser($input, new NullOutput());
        $workDir          = codecept_output_dir('init/installationTest/mysql_on_ip_address');
        $init->setInstallationData(array_merge($init->getDefaultInstallationData()->toArray(), [
            'testSiteDbUser'     => 'root',
            'testSiteDbPassword' => 'secret',
            'testSiteDbName' => 'wordpress',
            'testSiteDbHost' => '1.2.3.4:4022',
            'testDbUser'     => 'root',
            'testDbPassword' => 'secret',
            'testDbName' => 'wordpress_tests',
            'testDbHost' => '1.2.3.4:4022'
        ]));
        $this->createWorkDir($workDir);
        $init->setWorkDir($workDir);
        $this->createComposerJsonFile($workDir);
        $init->setCreateActors(false)->setCreateHelpers(false);
        $init->setup(true);

        $this->assertMatchesDirectorySnapshot($workDir);
    }

    /**
     * It should correctly scaffold with mysql on unix socket values
     *
     * @test
     */
    public function should_correctly_scaffold_with_mysql_on_unix_socket_values(): void
    {
        $input = $this->makeEmpty(ArrayInput::class, [
            'get_option' => static function ($option) {
                return $option === 'quiet' || $option === 'no-interaction' ? true : null;
            },
            'has_option' => false,
        ]);
        $init             = new Wpbrowser($input, new NullOutput());
        $workDir          = codecept_output_dir('init/installationTest/mysql_on_unix_socket');
        $init->setInstallationData(array_merge($init->getDefaultInstallationData()->toArray(), [
            'testSiteDbUser'     => 'root',
            'testSiteDbPassword' => 'secret',
            'testSiteDbName' => 'wordpress',
            'testSiteDbHost' => '/var/mysql.sock',
            'testDbUser'     => 'root',
            'testDbPassword' => 'secret',
            'testDbName' => 'wordpress_tests',
            'testDbHost' => '/var/mysql.sock'
        ]));
        $this->createWorkDir($workDir);
        $init->setWorkDir($workDir);
        $this->createComposerJsonFile($workDir);
        $init->setCreateActors(false)->setCreateHelpers(false);
        $init->setup(true);

        $this->assertMatchesDirectorySnapshot($workDir);
    }

    /**
     * It should correctly scaffold with mysql on unix socket with home symbol in path
     *
     * @test
     */
    public function should_correctly_scaffold_with_mysql_on_unix_socket_with_home_symbol_in_path(): void
    {
        if (!extension_loaded('uopz')) {
            $this->markTestSkipped('This test requires the uopz extension to run.');
        }

        $workDir = codecept_output_dir('init/installationTest/mysql_on_unix_socket_w_home_symbol');

        $this->replacingWithUopz([
            'lucatume\WPBrowser\Utils\Filesystem::homeDir' => '/Users/test'
        ], function () use ($workDir) {
            $input = $this->makeEmpty(ArrayInput::class, [
                'get_option' => static function ($option) {
                    return $option === 'quiet' || $option === 'no-interaction' ? true : null;
                },
                'has_option' => false,
            ]);
            $init = new Wpbrowser($input, new NullOutput());
            $init->setInstallationData(array_merge($init->getDefaultInstallationData()->toArray(), [
                'testSiteDbUser' => 'root',
                'testSiteDbPassword' => 'secret',
                'testSiteDbName' => 'wordpress',
                'testSiteDbHost' => '~/some/app/mysql.sock',
                'testDbUser' => 'root',
                'testDbPassword' => 'secret',
                'testDbName' => 'wordpress_tests',
                'testDbHost' => '~/some/app/mysql.sock'
            ]));
            $this->createWorkDir($workDir);
            $init->setWorkDir($workDir);
            $this->createComposerJsonFile($workDir);
            $init->setCreateActors(false)->setCreateHelpers(false);
            $init->setup(true);
        });

        $this->assertMatchesDirectorySnapshot($workDir);
    }

    protected function createComposerJsonFile($dir): void
    {
        $contents = <<< JSON
{
  "name": "lucatume/wp-browser",
  "type": "library",
  "description": "WordPress extension of the PhpBrowser class.",
  "keywords": [
    "wordpress",
    "codeception"
  ],
  "homepage": "http://github.com/lucatume/wp-browser",
  "license": "MIT",
  "authors": [
    {
      "name": "theAverageDev (Luca Tumedei)",
      "email": "luca@theaveragedev.com",
      "homepage": "http://theaveragedev.com",
      "role": "Developer"
    }
  ],
  "require": {
    "php": ">=5.6.0",
    "ext-pdo": "*",
    "ext-fileinfo": "*",
    "ext-json": "*",
    "ext-iconv": "*",
    "antecedent/patchwork": "^2.0",
    "codeception/codeception": "^4.0",
    "dg/mysql-dump": "^1.3",
    "symfony/filesystem": "^3.0",
    "symfony/process": ">=2.7 <5.0",
    "mikemclin/laravel-wp-password": "~2.0.0",
    "wp-cli/wp-cli-bundle": ">=2.0 <3.0.0",
    "zordius/lightncandy": "^1.2",
    "vria/nodiacritic": "^0.1.2",
    "codeception/module-asserts": "^1.0",
    "codeception/module-phpbrowser": "^1.0",
    "codeception/module-webdriver": "^1.0",
    "codeception/module-db": "^1.0",
    "codeception/module-filesystem": "^1.0",
    "codeception/module-cli": "^1.0",
    "codeception/util-universalframework": "^1.0"
  },
  "require-dev": {
    "erusev/parsedown": "^1.7",
    "lucatume/codeception-snapshot-assertions": "^0.2",
    "mikey179/vfsstream": "^1.6",
    "victorjonsson/markdowndocs": "dev-master",
    "gumlet/php-image-resize": "^1.6",
    "vlucas/phpdotenv": "^3.0"
  },
  "autoload": {
    "psr-4": {
      "Codeception\\": "src/Codeception",
      "tad\\": "src/tad"
    },
    "files": [
      "src/tad/WPBrowser/utils.php",
      "src/tad/WPBrowser/wp-polyfills.php"
    ]
  },
  "autoload-dev": {
    "psr-4": {
      "tad\\Test\\": "tests/_support/lib",
      "Codeception\\": "tests/_data/classes/Codeception"
    },
    "files": [
      "tests/_support/functions.php"
    ]
  },
  "extra": {
    "_hash": "484f861f69198089cab0e642f27e5653"
  },
  "suggest": {
    "codeception/module-asserts": "Codeception 4.0 compatibility.",
    "codeception/module-phpbrowser": "Codeception 4.0 compatibility; required by the WPBrowser module.",
    "codeception/module-webdriver": "Codeception 4.0 compatibility; required by the WPWebDriver module.",
    "codeception/module-db": "Codeception 4.0 compatibility; required by the WPDb module.",
    "codeception/module-filesystem": "Codeception 4.0 compatibility; required by the WPFilesystem module.",
    "codeception/module-cli": "Codeception 4.0 compatibility; required by the WPCLI module.",
    "codeception/util-universalframework": "Codeception 4.0 compatibility; required by the WordPress framework module.",
    "gumlet/php-image-resize": "To handle runtime image modification in the WPDb::haveAttachmentInDatabase method.",
    "vlucas/phpdotenv": "To manage env file based configuration of the suites."
  }
}
JSON;

        if (!(file_put_contents($dir . '/composer.json', $contents))) {
            throw new \RuntimeException("Could not write file {$file}.");
        }
    }
}

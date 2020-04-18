<?php

namespace tad\WPBrowser\Tests;

use Codeception\Template\Wpbrowser;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use tad\Codeception\SnapshotAssertions\SnapshotAssertions;

require_once __DIR__ . '/BaseTest.php';

class InstallationTest extends BaseTest
{
    use SnapshotAssertions;

    /**
     * It should correctly scaffold quiet installation
     *
     * @test
     */
    public function should_correctly_scaffold_quiet_installation()
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
    public function should_correctly_scaffold_with_default_values()
    {
        $input = $this->makeEmpty(ArrayInput::class, [
            'get_option' => static function ($option) {
                return $option === 'quiet' || $option === 'no-interaction' ? true : null;
            },
            'has_option' => false,
        ]);
        $init             = new Wpbrowser($input, new NullOutput());
        $workDir          = codecept_output_dir('init/installationTest/default');
        $init->setInstallationData($init->getDefaultInstallationData());
        $this->createWorkDir($workDir);
        $init->setWorkDir($workDir);
        $init->setCreateActors(false)->setCreateHelpers(false);
        $init->setup(true);

        $this->assertMatchesDirectorySnapshot($workDir);
    }

    /**
     * It should correctly scaffold with mysql on localhost values
     *
     * @test
     */
    public function should_correctly_scaffold_with_mysql_on_localhost_values()
    {
        $input = $this->makeEmpty(ArrayInput::class, [
            'get_option' => static function ($option) {
                return $option === 'quiet' || $option === 'no-interaction' ? true : null;
            },
            'has_option' => false,
        ]);
        $init             = new Wpbrowser($input, new NullOutput());
        $workDir          = codecept_output_dir('init/installationTest/mysql_on_localhost');
        $init->setInstallationData(array_merge($init->getDefaultInstallationData(), [
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
        $init->setCreateActors(false)->setCreateHelpers(false);
        $init->setup(true);

        $this->assertMatchesDirectorySnapshot($workDir);
    }

    /**
     * It should correctly scaffold with mysql on ip address values
     *
     * @test
     */
    public function should_correctly_scaffold_with_mysql_on_ip_address_values()
    {
        $input = $this->makeEmpty(ArrayInput::class, [
            'get_option' => static function ($option) {
                return $option === 'quiet' || $option === 'no-interaction' ? true : null;
            },
            'has_option' => false,
        ]);
        $init             = new Wpbrowser($input, new NullOutput());
        $workDir          = codecept_output_dir('init/installationTest/mysql_on_ip_address');
        $init->setInstallationData(array_merge($init->getDefaultInstallationData(), [
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
        $init->setCreateActors(false)->setCreateHelpers(false);
        $init->setup(true);

        $this->assertMatchesDirectorySnapshot($workDir);
    }

    /**
     * It should correctly scaffold with mysql on unix socket values
     *
     * @test
     */
    public function should_correctly_scaffold_with_mysql_on_unix_socket_values()
    {
        $input = $this->makeEmpty(ArrayInput::class, [
            'get_option' => static function ($option) {
                return $option === 'quiet' || $option === 'no-interaction' ? true : null;
            },
            'has_option' => false,
        ]);
        $init             = new Wpbrowser($input, new NullOutput());
        $workDir          = codecept_output_dir('init/installationTest/mysql_on_unix_socket');
        $init->setInstallationData(array_merge($init->getDefaultInstallationData(), [
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
        $init->setCreateActors(false)->setCreateHelpers(false);
        $init->setup(true);

        $this->assertMatchesDirectorySnapshot($workDir);
    }
}

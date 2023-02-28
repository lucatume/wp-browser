<?php

namespace lucatume\WPBrowser\Tests;

use lucatume\WPBrowser\Template\Wpbrowser;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use tad\Codeception\SnapshotAssertions\SnapshotAssertions;

require_once __DIR__ . '/BaseTest.php';

class EnvFileTest extends BaseTest
{
    use SnapshotAssertions;

    /**
     * It should correctly scaffold for mysql on localhost
     *
     * @test
     */
    public function should_correctly_scaffold_for_mysql_on_localhost()
    {
        $init = new Wpbrowser(new ArrayInput([]), new NullOutput());
        $installationData = (array_merge($init->getDefaultInstallationData(), [
            'testSiteDbHost' => 'localhost',
            'testSiteDbName' => 'wp',
            'testSiteDbUser' => 'root',
            'testSiteDbPassword' => '',
            'testDbHost' => 'localhost',
            'testDbName' => 'wpTests',
            'testDbUser' => 'root',
            'testDbPassword' => '',
        ]));
        $workDir = codecept_output_dir('init/envFileTest/mysql_on_localhost');
        $this->createWorkDir($workDir);
        $init->setWorkDir($workDir);
        $init->writeEnvFile($installationData);

        $this->assertFileExists($workDir . '/tests/.env');
        $this->assertMatchesStringSnapshot(file_get_contents($workDir . '/tests/.env'));
    }

    /**
     * It should correctly scaffold for mysql on unix socket
     *
     * @test
     */
    public function should_correctly_scaffold_for_mysql_on_unix_socket()
    {
        $init = new Wpbrowser(new ArrayInput([]), new NullOutput());
        $installationData = (array_merge($init->getDefaultInstallationData(), [
            'testSiteDbHost' => '/var/mysql.sock',
            'testSiteDbName' => 'wp',
            'testSiteDbUser' => 'root',
            'testSiteDbPassword' => '',
            'testDbHost' => '/var/mysql.sock',
            'testDbName' => 'wpTests',
            'testDbUser' => 'root',
            'testDbPassword' => '',
        ]));
        $workDir = codecept_output_dir('init/envFileTest/mysql_on_unix_socket');
        $this->createWorkDir($workDir);
        $init->setWorkDir($workDir);
        $init->writeEnvFile($installationData);

        $this->assertFileExists($workDir . '/tests/.env');
        $this->assertMatchesStringSnapshot(file_get_contents($workDir . '/tests/.env'));
    }

    /**
     * It should correclty scaffold for mysql on IP address
     *
     * @test
     */
    public function should_correclty_scaffold_for_mysql_on_ip_address()
    {
        $init = new Wpbrowser(new ArrayInput([]), new NullOutput());
        $installationData = (array_merge($init->getDefaultInstallationData(), [
            'testSiteDbHost' => '1.2.3.4',
            'testSiteDbName' => 'test',
            'testSiteDbUser' => 'root',
            'testSiteDbPassword' => 'password',
            'testDbHost' => '1.2.3.4',
            'testDbName' => 'test',
            'testDbUser' => 'root',
            'testDbPassword' => 'password',
        ]));
        $workDir = codecept_output_dir('init/envFileTest/mysql_on_ip_address');
        $this->createWorkDir($workDir);
        $init->setWorkDir($workDir);
        $init->writeEnvFile($installationData);

        $this->assertFileExists($workDir . '/tests/.env');
        $this->assertMatchesStringSnapshot(file_get_contents($workDir . '/tests/.env'));
    }

    /**
     * It should correctly scaffold for mysql on IP address and port
     *
     * @test
     */
    public function should_correctly_scaffold_for_mysql_on_ip_address_and_port()
    {
        $init = new Wpbrowser(new ArrayInput([]), new NullOutput());
        $installationData = (array_merge($init->getDefaultInstallationData(), [
            'testSiteDbHost' => '1.2.3.4:2389',
            'testSiteDbName' => 'test',
            'testSiteDbUser' => 'root',
            'testSiteDbPassword' => 'password',
            'testDbHost' => '1.2.3.4:2389',
            'testDbName' => 'test',
            'testDbUser' => 'root',
            'testDbPassword' => 'password',
        ]));
        $workDir = codecept_output_dir('init/envFileTest/mysql_on_ip_address_and_port');
        $this->createWorkDir($workDir);
        $init->setWorkDir($workDir);
        $init->writeEnvFile($installationData);

        $this->assertFileExists($workDir . '/tests/.env');
        $this->assertMatchesStringSnapshot(file_get_contents($workDir . '/tests/.env'));
    }
}

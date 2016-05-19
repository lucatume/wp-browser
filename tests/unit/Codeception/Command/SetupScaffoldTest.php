<?php

namespace Codeception\Command\Tests\Unit;


use Codeception\Command\SetupScaffold;
use org\bovigo\vfs\vfsStream;
use Prophecy\Argument;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Yaml\Yaml;
use tad\WPBrowser\Filesystem\Filesystem;

class SetupScaffoldTest extends \PHPUnit_Framework_TestCase
{
    protected static $distFileWasThere = false;

    public static function setUpBeforeClass()
    {
        self::$distFileWasThere = file_exists(codecept_root_dir('codeception.dist.yml'));
    }

    public static function tearDownAfterClass()
    {
        if (!self::$distFileWasThere) {
            unlink(codecept_root_dir('codeception.dist.yml'));
        }
    }

    /**
     * @test
     * it should be instantiatable
     */
    public function it_should_be_instantiatable()
    {
        $sut = $this->make_instance();

        $this->assertInstanceOf('\Codeception\Command\SetupScaffold', $sut);
    }

    private function make_instance()
    {
        return new SetupScaffold();
    }

    /**
     * @test
     * it should create a setup.yml file in the root folder
     */
    public function it_should_create_a_setup_yml_file_in_the_root_folder()
    {
        $application = new Application();
        $application->add(new SetupScaffold());

        $command = $application->find('setup:scaffold');
        $commandTester = new CommandTester($command);
        $commandTester->execute(array(
            'command' => $command->getName(),
            '--skip-suites' => true
        ));

        $file = codecept_root_dir('setup.yml');

        $this->assertFileExists($file);

        unlink($file);
    }

    /**
     * @test
     * it should allow to specify a destination for the file
     */
    public function it_should_allow_to_specify_a_destination_for_the_file()
    {
        $fs = vfsStream::setup('fs');
        $file = $fs->url() . '/setup.yml';

        $application = new Application();
        $application->add(new SetupScaffold());

        $command = $application->find('setup:scaffold');
        $commandTester = new CommandTester($command);
        $commandTester->execute(array(
            'command' => $command->getName(),
            '--skip-suites' => true,
            '--destination' => $file
        ));

        $this->assertFileExists($file);
    }

    /**
     * @test
     * it should bail if codeception has not been bootstrapped yet
     */
    public function it_should_bail_if_codeception_has_not_been_bootstrapped_yet()
    {
        /** @var Filesystem $filesystem */
        $filesystem = $this->prophesize('tad\WPBrowser\Filesystem\Filesystem');
        $filesystem->file_exists(codecept_root_dir('codeception.yml'))->willReturn(false);

        $application = new Application();
        $application->add(new SetupScaffold(null, $filesystem->reveal()));

        $command = $application->find('setup:scaffold');

        $commandTester = new CommandTester($command);

        $this->expectException('Symfony\Component\Console\Exception\RuntimeException');

        $commandTester->execute(array(
            'command' => $command->getName()
        ));
    }

    /**
     * @test
     * it should create a dist version of codeception.yml file if not found
     */
    public function it_should_create_a_dist_version_of_codeception_yml_file_if_not_found()
    {
        $fs = vfsStream::setup('fs');
        $file = $fs->url() . '/setup.yml';
        /** @var Filesystem $filesystem */
        $filesystem = $this->prophesize('tad\WPBrowser\Filesystem\Filesystem');
        $filesystem->file_exists(codecept_root_dir('tests'))->willReturn(true);
        $filesystem->file_exists(codecept_root_dir('codeception.yml'))->willReturn(true);
        $filesystem->file_exists(codecept_root_dir('codeception.dist.yml'))->willReturn(false);
        $localCodeceptionConfigContents = 'foo';
        $filesystem->file_get_contents(codecept_root_dir('codeception.yml'))->willReturn($localCodeceptionConfigContents);
        $filesystem->file_put_contents(codecept_root_dir('codeception.dist.yml'), $localCodeceptionConfigContents)->shouldBeCalled();
        $filesystem->file_put_contents($file, Argument::type('string'))->shouldBeCalled();

        $application = new Application();
        $application->add(new SetupScaffold(null, $filesystem->reveal()));

        $command = $application->find('setup:scaffold');

        $commandTester = new CommandTester($command);
        $commandTester->execute(array(
            'command' => $command->getName(),
            '--destination' => $file,
            '--skip-suites' => true
        ));
    }

    /**
     * @test
     * it should throw if set tests directory does not exist
     */
    public function it_should_throw_if_set_tests_directory_does_not_exist()
    {
        /** @var Filesystem $filesystem */
        $filesystem = $this->prophesize('tad\WPBrowser\Filesystem\Filesystem');
        $filesystem->file_exists(codecept_root_dir('codeception.yml'))->willReturn(true);
        $filesystem->file_exists(codecept_root_dir('codeception.dist.yml'))->willReturn(true);
        $localCodeceptionConfigContents = Yaml::dump(['paths' => ['tests' => 'some-dir']]);
        $filesystem->file_get_contents(codecept_root_dir('codeception.yml'))->willReturn($localCodeceptionConfigContents);
        $filesystem->file_exists(codecept_root_dir('some-dir'))->willReturn(false);

        $application = new Application();
        $application->add(new SetupScaffold(null, $filesystem->reveal()));

        $command = $application->find('setup:scaffold');

        $this->expectException('Symfony\Component\Console\Exception\RuntimeException');

        $commandTester = new CommandTester($command);
        $commandTester->execute(array(
            'command' => $command->getName()
        ));
    }

    /**
     * @test
     * it should ask to create a dist version of each suite configuration file
     */
    public function it_should_ask_to_create_a_dist_version_of_each_suite_configuration_file()
    {
        $fs = vfsStream::setup('fs');
        $destinationFile = $fs->url() . '/setup.yml';

        $testsDir = codecept_root_dir('fake-tests-dir');
        mkdir($testsDir);
        $fooSuiteConfigFile = $testsDir . '/foo.suite.yml';
        $barSuiteConfigFile = $testsDir . '/bar.suite.yml';
        $fooSuiteConfigContents = 'foo suite config';
        file_put_contents($fooSuiteConfigFile, $fooSuiteConfigContents);
        $barSuiteConfigContents = 'bar suite config';
        file_put_contents($barSuiteConfigFile, $barSuiteConfigContents);

        /** @var Filesystem $filesystem */
        $filesystem = $this->prophesize('tad\WPBrowser\Filesystem\Filesystem');
        $filesystem->file_exists(codecept_root_dir('codeception.yml'))->willReturn(true);
        $filesystem->file_exists(codecept_root_dir('codeception.dist.yml'))->willReturn(true);
        $filesystem->file_exists($testsDir)->willReturn(true);
        $filesystem->file_exists($testsDir . '/foo.suite.dist.yml')->willReturn(false);
        $filesystem->file_exists($testsDir . '/bar.suite.dist.yml')->willReturn(false);
        $localCodeceptionConfigContents = Yaml::dump(['paths' => ['tests' => 'fake-tests-dir']]);
        $filesystem->file_get_contents(codecept_root_dir('codeception.yml'))->willReturn($localCodeceptionConfigContents);
        $filesystem->file_put_contents($destinationFile, Argument::type('string'))->shouldBeCalled();
        $filesystem->file_get_contents($fooSuiteConfigFile)->willReturn($fooSuiteConfigContents);
        $filesystem->file_get_contents($barSuiteConfigFile)->willReturn($barSuiteConfigContents);
        $filesystem->file_put_contents($testsDir . '/foo.suite.dist.yml', $fooSuiteConfigContents)->shouldBeCalled();
        $filesystem->file_put_contents($testsDir . '/bar.suite.dist.yml', $barSuiteConfigContents)->shouldBeCalled();

        $application = new Application();
        $application->add(new SetupScaffold(null, $filesystem->reveal()));

        $command = $application->find('setup:scaffold');

        $commandTester = new CommandTester($command);
        $commandTester->execute(array(
            'command' => $command->getName(),
            '--destination' => $destinationFile,
            '--yes' => true
        ));

        unlink($fooSuiteConfigFile);
        unlink($barSuiteConfigFile);
    }

    /**
     * @test
     * it should add a section stub for each suite
     */
    public function it_should_add_a_section_stub_for_each_suite()
    {
        $fs = vfsStream::setup('fs');
        $file = $fs->url() . '/setup.yml';

        $application = new Application();
        $application->add(new SetupScaffold());

        $command = $application->find('setup:scaffold');
        $commandTester = new CommandTester($command);
        $commandTester->execute(array(
            'command' => $command->getName(),
            '--skip-suites' => true,
            '--destination' => $file,
            '--yes' => true
        ));

        $config = Yaml::parse(file_get_contents(codecept_root_dir('codeception.yml')));
        $testsDir = $config['paths']['tests'];
        $suites = new \RegexIterator(new \FilesystemIterator($testsDir), '/\\.suite\\.yml$/i');

        $setup = Yaml::parse(file_get_contents($fs->url() . '/setup.yml'));

        foreach ($suites as $file => $fileInfo) {
            $this->assertArrayHasKey(basename($file, '.suite.yml'), $setup);
        }
    }

    protected function getInputStream($input)
    {
        $stream = fopen('php://memory', 'r+', false);
        fputs($stream, $input);
        rewind($stream);

        return $stream;
    }

    protected function tearDown()
    {
        if (file_exists(codecept_root_dir('fake-tests-dir'))) {
            unlinkDir(codecept_root_dir('fake-tests-dir'));
        }
    }
}

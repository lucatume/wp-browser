<?php

use Codeception\Command\SearchReplace;
use Codeception\Command\SetupLocal;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamFile;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class SetupLocalTest extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     * it should use the local.yml file from the cwd if config file is not specified
     */
    public function it_should_use_the_local_yml_file_from_the_cwd_if_config_file_is_not_specified()
    {
        $application = new Application();
        $application->add(new SetupLocal());

        $command = $application->find('setup:local');
        $commandTester = new CommandTester($command);

        $commandTester->execute([
            'command' => $command->getName(),
        ]);

        $this->assertRegExp('/Configuration file.*does not exist/', $commandTester->getDisplay());
    }

    /**
     * @test
     * it should throw if specified configuration file does not exist
     */
    public function it_should_throw_if_specified_configuration_file_does_not_exist()
    {
        $dir = vfsStream::setup();

        $application = new Application();
        $application->add(new SetupLocal());

        $command = $application->find('setup:local');
        $commandTester = new CommandTester($command);

        $commandTester->execute([
            'command' => $command->getName(),
            '--config' => $dir->url() . '/conf.yaml'
        ]);

        $this->assertRegExp('/Configuration file.*does not exist/', $commandTester->getDisplay());
    }

    /**
     * @test
     * it should allow specifying a variable in the configuration file
     */
    public function it_should_allow_specifying_a_variable_in_the_configuration_file()
    {
        $dir = vfsStream::setup();
        $configFile = new vfsStreamFile('conf.yaml');
        $configFileContent = <<< YAML
foo:
    var:
        name: first
        question: first var value?
        default: 23
    message: Var value is \$first
YAML;

        $configFile->setContent($configFileContent);
        $dir->addChild($configFile);

        $application = new Application();
        $application->add(new SetupLocal());

        $command = $application->find('setup:local');
        $commandTester = new CommandTester($command);
        $helper = $command->getHelper('question');
        $helper->setInputStream($this->getInputStream("Test\n"));

        $commandTester->execute([
            'command' => $command->getName(),
            '--config' => $dir->url() . '/conf.yaml'
        ]);

        $display = $commandTester->getDisplay();
        $this->assertContains('Configuring "foo"', $display);
        $this->assertContains('first var value? (23)', $display);
        $this->assertContains('Var value is Test', $display);
    }

    /**
     * @test
     * it should allow running commands from the configuration file
     */
    public function it_should_allow_running_commands_from_the_configuration_file()
    {
        $dir = vfsStream::setup();
        $sourceFile = new vfsStreamFile('source.txt');
        $outputFile = new vfsStreamFile('out.txt');
        $sourceFile->setContent('foo baz bar');

        $sourceFilePath = $dir->url() . '/source.txt';
        $outputFilePath = $dir->url() . '/out.txt';

        $configFile = new vfsStreamFile('conf.yaml');
        $configFileContent = <<< YAML
foo:
    var:
        name: first
        question: first var value?
        default: 23
    command: search-replace foo \$first $sourceFilePath $outputFilePath
YAML;

        $configFile->setContent($configFileContent);
        $dir->addChild($configFile);
        $dir->addChild($sourceFile);
        $dir->addChild($outputFile);

        $application = new Application();
        $application->add(new SetupLocal());

        $command = $application->find('setup:local');
        $application->add(new SearchReplace());
        $commandTester = new CommandTester($command);
        $helper = $command->getHelper('question');
        $helper->setInputStream($this->getInputStream("hello\n"));

        $commandTester->execute([
            'command' => $command->getName(),
            '--config' => $dir->url() . '/conf.yaml'
        ]);

        $display = $commandTester->getDisplay();
        $this->assertContains('Configuring "foo"', $display);
        $this->assertContains('first var value? (23)', $display);

        $this->assertStringEqualsFile($outputFilePath, 'hello baz bar');
    }

    /**
     * @test
     * it should allow executing scripts
     */
    public function it_should_allow_executing_scripts()
    {
        $dir = vfsStream::setup();
        $configFile = new vfsStreamFile('conf.yaml');
        $outputFile = __DIR__ . DIRECTORY_SEPARATOR . 'out.txt';
        $configFileContent = <<< YAML
foo:
    exec: touch {$outputFile}
YAML;

        $configFile->setContent($configFileContent);
        $dir->addChild($configFile);

        $application = new Application();
        $application->add(new SetupLocal());

        $command = $application->find('setup:local');
        $commandTester = new CommandTester($command);

        $commandTester->execute([
            'command' => $command->getName(),
            '--config' => $dir->url() . '/conf.yaml'
        ]);

        $this->assertFileExists($outputFile);
        unlink($outputFile);
    }

    /**
     * @test
     * it should allow for vars in scripts
     */
    public function it_should_allow_for_vars_in_scripts()
    {
        $dir = vfsStream::setup();
        $configFile = new vfsStreamFile('conf.yaml');
        $outputFile = __DIR__ . DIRECTORY_SEPARATOR . 'out.txt';
        $configFileContent = <<< YAML
foo:
    var:
        name: first
        question: first var value?
        default: 12
    exec: touch {$outputFile} && echo "\$first" > {$outputFile}
YAML;

        $configFile->setContent($configFileContent);
        $dir->addChild($configFile);

        $application = new Application();
        $application->add(new SetupLocal());

        $command = $application->find('setup:local');
        $commandTester = new CommandTester($command);
        $helper = $command->getHelper('question');
        $helper->setInputStream($this->getInputStream("hello\n"));

        $commandTester->execute([
            'command' => $command->getName(),
            '--config' => $dir->url() . '/conf.yaml'
        ]);

        $this->assertFileExists($outputFile);
        // let's take the simulated input aberration into account
        $this->assertStringEqualsFile($outputFile, "hello\n");
        unlink($outputFile);
    }

    private function getInputStream($input)
    {
        $stream = fopen('php://memory', 'r+', false);
        fputs($stream, $input);
        rewind($stream);

        return $stream;
    }
}

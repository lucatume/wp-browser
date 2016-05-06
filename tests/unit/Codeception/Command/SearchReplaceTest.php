<?php

use Codeception\Command\SearchReplace;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamFile;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class SearchReplaceTest extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     * it should require old string
     */
    public function it_should_require_old_string()
    {
        $application = new Application();
        $application->add(new SearchReplace());

        $command = $application->find('search-replace');
        $commandTester = new CommandTester($command);

        $this->expectException('Symfony\Component\Console\Exception\RuntimeException');

        $commandTester->execute(['command' => $command->getName()]);
    }

    /**
     * @test
     * it should require new string
     */
    public function it_should_require_new_string()
    {
        $application = new Application();
        $application->add(new SearchReplace());

        $command = $application->find('search-replace');
        $commandTester = new CommandTester($command);

        $this->expectException('Symfony\Component\Console\Exception\RuntimeException');

        $commandTester->execute([
            'command' => $command->getName(),
            'old' => 'foo'
        ]);
    }

    /**
     * @test
     * it should require source file
     */
    public function it_should_require_source_file()
    {
        $application = new Application();
        $application->add(new SearchReplace());

        $command = $application->find('search-replace');
        $commandTester = new CommandTester($command);

        $this->expectException('Symfony\Component\Console\Exception\RuntimeException');

        $commandTester->execute([
            'command' => $command->getName(),
            'old' => 'foo',
            'new' => 'replacement'
        ]);
    }

    /**
     * @test
     * it should display error if source file does not exist
     */
    public function it_should_display_error_if_source_file_does_not_exist()
    {
        $application = new Application();
        $application->add(new SearchReplace());

        $command = $application->find('search-replace');
        $commandTester = new CommandTester($command);

        $commandTester->execute([
            'command' => $command->getName(),
            'old' => 'foo',
            'new' => 'replacement',
            'file' => '/some/file.sql'
        ]);

        $this->assertRegExp('/File.*does not exist/', $commandTester->getDisplay());
    }

    /**
     * @test
     * it should display error if source file is not readable
     */
    public function it_should_display_error_if_source_file_is_not_readable()
    {
        $root = vfsStream::setup('source');
        $root->addChild(new vfsStreamFile('dump.sql', 0333));

        $application = new Application();
        $application->add(new SearchReplace());

        $command = $application->find('search-replace');
        $commandTester = new CommandTester($command);

        $commandTester->execute([
            'command' => $command->getName(),
            'old' => 'foo',
            'new' => 'replacement',
            'file' => $root->url() . '/dump.sql'
        ]);

        $this->assertRegExp('/File.*is not readable/', $commandTester->getDisplay());
    }

    /**
     * @test
     * it should display error if no output file specified and source file not writeable
     */
    public function it_should_display_error_if_no_output_file_specified_and_source_file_not_writeable()
    {
        $root = vfsStream::setup('source');
        $root->addChild(new vfsStreamFile('dump.sql', 0555));

        $application = new Application();
        $application->add(new SearchReplace());

        $command = $application->find('search-replace');
        $commandTester = new CommandTester($command);

        $commandTester->execute([
            'command' => $command->getName(),
            'old' => 'foo',
            'new' => 'replacement',
            'file' => $root->url() . '/dump.sql'
        ]);

        $this->assertRegExp('/File.*is not writeable/', $commandTester->getDisplay());
    }

    /**
     * @test
     * it should display error if output could not be written
     */
    public function it_should_display_error_if_output_could_not_be_written()
    {
        $root = vfsStream::setup('source');
        $file = new vfsStreamFile('dump.sql');
        $file->setContent('some content');
        $outFile = new vfsStreamFile('out.sql', 0555);
        $root->addChild($file);
        $root->addChild($outFile);

        $application = new Application();
        $application->add(new SearchReplace());

        $command = $application->find('search-replace');
        $commandTester = new CommandTester($command);

        $commandTester->execute([
            'command' => $command->getName(),
            'old' => 'foo',
            'new' => 'replacement',
            'file' => $root->url() . '/dump.sql',
            'output' => $root->url() . '/out.sql'
        ]);

        $this->assertRegExp('/Could not write to.*/', $commandTester->getDisplay());
    }

    /**
     * @test
     * it should display information if output was written
     */
    public function it_should_display_information_if_output_was_written()
    {
        $root = vfsStream::setup('source');
        $file = new vfsStreamFile('dump.sql');
        $file->setContent('some content');
        $root->addChild($file);

        $application = new Application();
        $application->add(new SearchReplace());

        $command = $application->find('search-replace');
        $commandTester = new CommandTester($command);

        $commandTester->execute([
            'command' => $command->getName(),
            'old' => 'foo',
            'new' => 'replacement',
            'file' => $root->url() . '/dump.sql',
            'output' => $root->url() . '/out.sql'
        ]);

        $this->assertRegExp('/Modified contents written to.*/', $commandTester->getDisplay());
    }

    /**
     * @test
     * it should replace old with new in output
     */
    public function it_should_replace_old_with_new_in_output()
    {
        $root = vfsStream::setup('source');
        $file = new vfsStreamFile('dump.sql');
        $file->setContent('foo baz bar foo baz bar');
        $root->addChild($file);

        $application = new Application();
        $application->add(new SearchReplace());

        $command = $application->find('search-replace');
        $commandTester = new CommandTester($command);

        $outputFile = $root->url() . '/out.sql';
        $commandTester->execute([
            'command' => $command->getName(),
            'old' => 'foo',
            'new' => 'replacement',
            'file' => $root->url() . '/dump.sql',
            'output' => $outputFile
        ]);

        $this->assertStringEqualsFile($outputFile, 'replacement baz bar replacement baz bar');
    }

    /**
     * @test
     * it should skip missing source files if --skip-missing option is set
     */
    public function it_should_skip_missing_source_files_if_skip_missing_option_is_set()
    {
        $root = vfsStream::setup('source');

        $application = new Application();
        $application->add(new SearchReplace());

        $command = $application->find('search-replace');
        $commandTester = new CommandTester($command);

        $outputFile = $root->url() . '/out.sql';
        $commandTester->execute([
            'command' => $command->getName(),
            'old' => 'foo',
            'new' => 'replacement',
            'file' => $root->url() . '/dump.sql',
            'output' => $outputFile,
            '--skip-if-missing' => true,
        ]);

        $this->assertRegExp('/Skipped as source file.*is missing/', $commandTester->getDisplay());
    }
}

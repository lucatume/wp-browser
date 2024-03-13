<?php

namespace lucatume\WPBrowser\Lib\Generator;

use Codeception\Test\Unit;
use lucatume\WPBrowser\Adapters\Symfony\Component\Process\Process;
use lucatume\WPBrowser\Command\GenerateWPAjax;
use lucatume\WPBrowser\Command\GenerateWPCanonical;
use lucatume\WPBrowser\Command\GenerateWPRestApi;
use lucatume\WPBrowser\Command\GenerateWPRestController;
use lucatume\WPBrowser\Command\GenerateWPRestPostTypeController;
use lucatume\WPBrowser\Command\GenerateWPUnit;
use lucatume\WPBrowser\Command\GenerateWPXML;
use lucatume\WPBrowser\Command\GenerateWPXMLRPC;
use lucatume\WPBrowser\Utils\Filesystem as FS;

/**
 * @group slow
 */
class GenerationCommandsTest extends Unit
{
    /**
     * @var string
     */
    private static $suite = 'wploadersuite';
    /**
     * @var string|null
     */
    private $testCaseFile = null;

    /**
     * @after
     */
    public function removeTestCaseFile(): void
    {
        if (!file_exists($this->testCaseFile)) {
            return;
        }

        if (!unlink($this->testCaseFile)) {
            throw new \RuntimeException('Cannot remove test case file.');
        }
    }

    /**
     * @return array<string,{0: string, 1: string}>
     */
    public function commandsProvider(): array
    {
        return [
            GenerateWPAjax::class => ['GenerateWPAjax', GenerateWPAjax::getCommandName()],
            GenerateWPCanonical::class => ['GenerateWPCanonical', GenerateWPCanonical::getCommandName()],
            GenerateWPRestApi::class => ['GenerateWPRestApi', GenerateWPRestApi::getCommandName()],
            GenerateWPRestController::class => ['GenerateWPRestController', GenerateWPRestController::getCommandName()],
            GenerateWPRestPostTypeController::class => [
                'GenerateWPRestPostTypeController',
                GenerateWPRestPostTypeController::getCommandName()
            ],
            GenerateWPUnit::class => ['GenerateWPUnit', GenerateWPUnit::getCommandName()],
            GenerateWPXML::class => ['GenerateWPXML', GenerateWPXML::getCommandName()],
            GenerateWPXMLRPC::class => ['GenerateWPXMLRPC', GenerateWPXMLRPC::getCommandName()],
        ];
    }

    /**
     * @dataProvider commandsProvider
     */
    public function test_testcase_generation(string $commandClass, string $commandName): void
    {
        $codeceptionBin = FS::realpath('vendor/bin/codecept');

        $suite = static::$suite;

        // Generate the test example.
        (new Process([PHP_BINARY, $codeceptionBin, $commandName, $suite, $commandClass]))->mustRun();

        $testCaseFileRelativePath = "tests/{$suite}/{$commandClass}Test.php";
        $testCaseFile = codecept_root_dir($testCaseFileRelativePath);

        $this->assertFileExists($testCaseFile);

        $this->testCaseFile = $testCaseFile;

        $runProcess = new Process(
            [PHP_BINARY, $codeceptionBin, 'codeception:run', $testCaseFileRelativePath]
        );
        $runProcess->run();
        $exitCode = $runProcess->getExitCode();

        if ($exitCode !== 0) {
            $this->testCaseFile = null;
        }

        $this->assertEquals(0, $exitCode, $this->formatRunProcessOutput($runProcess));
    }

    private function formatRunProcessOutput(Process $runProcess): string
    {
        return sprintf(
            "\nSTDOUT\n---\n%s\nSTDERR\n---\n%s\n",
            preg_replace('/^/mu', '>  ', $runProcess->getOutput()),
            preg_replace('/^/mu', '>  ', $runProcess->getErrorOutput())
        );
    }
}

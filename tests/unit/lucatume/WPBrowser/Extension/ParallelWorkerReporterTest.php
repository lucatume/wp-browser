<?php

namespace Unit\lucatume\WPBrowser\Extension;

use Codeception\Test\Unit;
use lucatume\WPBrowser\Utils\Filesystem as FS;
use Symfony\Component\Process\Process;

/**
 * @group slow
 */
class ParallelWorkerReporterTest extends Unit
{
    public function test_emits_one_char_per_test_event_to_the_event_file(): void
    {
        $fixtureRoot = $this->scaffoldFixture();
        $eventFile   = $fixtureRoot . '/events.bin';
        touch($eventFile);

        $this->runFixtureWithExtension($fixtureRoot, [
            'WPBROWSER_PARALLEL_EVENT_FILE' => $eventFile,
        ]);

        $events = (string)file_get_contents($eventFile);
        $this->assertNotSame('', $events);

        $tally = count_chars($events, 1);
        $this->assertSame(1, $tally[ord('.')] ?? 0, 'one pass → one dot');
        $this->assertSame(1, $tally[ord('F')] ?? 0, 'one failure → one F');
        $this->assertSame(1, $tally[ord('S')] ?? 0, 'one skip → one S');
        $this->assertSame(1, $tally[ord('I')] ?? 0, 'one incomplete → one I');

        $vocab = ['.', 'F', 'E', 'S', 'I', 'W', 'U'];
        foreach (str_split($events) as $ch) {
            $this->assertContains($ch, $vocab, "Unexpected char '{$ch}' in event file");
        }
    }

    public function test_is_a_noop_when_event_file_env_var_is_not_set(): void
    {
        $fixtureRoot = $this->scaffoldFixture();

        $process = $this->runFixtureWithExtension($fixtureRoot, []);

        // The extension must not crash the run when its env var is absent.
        $this->assertNotSame(
            '',
            $process->getOutput(),
            "Codeception produced no output, suggesting the extension crashed startup"
        );
        $this->assertStringNotContainsString('Fatal error', $process->getOutput() . $process->getErrorOutput());
        $this->assertStringNotContainsString('Uncaught', $process->getOutput() . $process->getErrorOutput());
    }

    public function test_is_a_noop_when_event_file_path_is_unwritable(): void
    {
        $fixtureRoot  = $this->scaffoldFixture();
        $readOnlyDir  = $fixtureRoot . '/ro';
        mkdir($readOnlyDir, 0555);
        $unwritable = $readOnlyDir . '/events.bin';

        $process = $this->runFixtureWithExtension($fixtureRoot, [
            'WPBROWSER_PARALLEL_EVENT_FILE' => $unwritable,
        ]);

        chmod($readOnlyDir, 0755);

        $this->assertFileDoesNotExist($unwritable);
        $this->assertStringNotContainsString('Fatal error', $process->getOutput() . $process->getErrorOutput());
        $this->assertStringNotContainsString('Uncaught', $process->getOutput() . $process->getErrorOutput());
    }

    /**
     * @param array<string,string> $extraEnv
     */
    private function runFixtureWithExtension(string $fixtureRoot, array $extraEnv): Process
    {
        $codecept = codecept_root_dir() . '/vendor/bin/codecept';
        $cmd = [
            PHP_BINARY,
            $codecept,
            'run',
            'unit',
            '--ext',
            'lucatume\\WPBrowser\\Extension\\ParallelWorkerReporter',
            '--no-colors',
            '--no-artifacts',
        ];
        $env = array_merge([
            'PATH'                          => getenv('PATH') ?: '/usr/bin:/bin',
            'HOME'                          => getenv('HOME') ?: '/tmp',
            'WPBROWSER_PARALLEL_EVENT_FILE' => '',
            'WPBROWSER_PARALLEL_LOG_FILE'   => '',
        ], $extraEnv);

        $process = new Process($cmd, $fixtureRoot, $env, null, 60);
        $process->run();
        return $process;
    }

    private function scaffoldFixture(): string
    {
        $root = FS::tmpDir('pwr-fixture-');
        $projectRoot = codecept_root_dir();

        file_put_contents($root . '/codeception.yml', <<<YAML
paths:
    tests: tests
    output: tests/_output
    support: tests/_support
    data: tests/_data
settings:
    shuffle: false
    colors: false
bootstrap: _bootstrap.php
YAML
        );

        mkdir($root . '/tests');
        mkdir($root . '/tests/unit');
        mkdir($root . '/tests/_output');
        mkdir($root . '/tests/_support');
        mkdir($root . '/tests/_data');
        file_put_contents($root . '/tests/_bootstrap.php', "<?php\nrequire '" . addslashes($projectRoot) . "vendor/autoload.php';\n");
        file_put_contents($root . '/tests/unit/_bootstrap.php', "<?php\n");
        file_put_contents($root . '/tests/unit.suite.yml', <<<YAML
suite_namespace: FixtureUnit
actor: UnitTester
modules:
    enabled:
        - Asserts
YAML
        );

        file_put_contents($root . '/tests/_support/UnitTester.php', <<<'PHP'
<?php
class UnitTester extends \Codeception\Actor {}
PHP
        );

        file_put_contents($root . '/tests/unit/AaaFixtureTest.php', <<<'PHP'
<?php
class AaaFixtureTest extends \Codeception\Test\Unit
{
    public function test_aaa_pass(): void { $this->assertTrue(true); }
    public function test_bbb_fail(): void { $this->assertTrue(false, 'intentional'); }
    public function test_ccc_skip(): void { $this->markTestSkipped('intentional'); }
    public function test_ddd_incomplete(): void { $this->markTestIncomplete('intentional'); }
}
PHP
        );

        return $root;
    }
}

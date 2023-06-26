<?php namespace Codeception\Template;

use lucatume\WPBrowser\Tests\Traits\TmpFilesCleanup;
use lucatume\WPBrowser\Utils\Filesystem as FS;
use Symfony\Component\Process\Process;
use tad\Codeception\SnapshotAssertions\SnapshotAssertions;

class WpbrowserTest extends \Codeception\Test\Unit
{
    use TmpFilesCleanup;
    use SnapshotAssertions;

    public function test_plugin_project_scaffold(): void
    {
        $projectDir = FS::tmpDir('project_factory_', [
            'plugin_89' => [
                'plugin.php' => "<?php\n/* Plugin Name: Plugin 89 */"
            ]
        ]);

        $command = [
            PHP_BINARY,
            codecept_root_dir("vendor/bin/codecept"),
            'init',
            'wpbrowser',
            '--path=' . Fs::relativePath(codecept_root_dir(), $projectDir . '/plugin_89'),
        ];
        $process = new Process($command);

        // Answer "yes" to the question about the project type.
        $process->setInput([
            "yes", // Confirm the project type.
            "yes", // Use the SQLite, PHP, Chrome setup.
        ]);

        $process->mustRun();

        // Remove the generated files that are not needed for the snapshot.
        FS::rrmdir($projectDir . '/plugin_89/tests/Support/_generated');
        $this->assertMatchesDirectorySnapshot($projectDir);
    }

    public function test_theme_project_scaffold(): void
    {
        $projectDir = FS::tmpDir('project_factory_', [
            'theme_23' => [
                'style.css' => "/* Theme Name: Theme 23 */"
            ]
        ]);

        $command = [
            PHP_BINARY,
            codecept_root_dir("vendor/bin/codecept"),
            'init',
            'wpbrowser',
            '--path=' . Fs::relativePath(codecept_root_dir(), $projectDir . '/theme_23'),
        ];
        $process = new Process($command);

        // Answer "yes" to the question about the project type.
        $process->setInput("y\n");

        $process->mustRun();

        // Remove the generated files that are not needed for the snapshot.
        FS::rrmdir($projectDir . '/theme_23/tests/Support/_generated');
        $this->assertMatchesDirectorySnapshot($projectDir);
    }

    public function test_site_project_scaffold(): void
    {
        $projectDir = FS::tmpDir('project_factory_', [
            'site_2389' => []
        ]);

        $command = [
            PHP_BINARY,
            codecept_root_dir("vendor/bin/codecept"),
            'init',
            'wpbrowser',
            '--path=' . Fs::relativePath(codecept_root_dir(), $projectDir . '/site_2389'),
        ];
        $process = new Process($command);

        // Answer "yes" to the question about the project type.
        $process->setInput("y\n");

        $process->mustRun();

        // Remove the generated files that are not needed for the snapshot.
        FS::rrmdir($projectDir . '/site_2389/tests/Support/_generated');
        $this->assertMatchesDirectorySnapshot($projectDir);
    }
}

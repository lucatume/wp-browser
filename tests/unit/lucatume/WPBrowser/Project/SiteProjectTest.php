<?php


namespace lucatume\WPBrowser\Project;

use lucatume\WPBrowser\Exceptions\RuntimeException;
use lucatume\WPBrowser\Tests\Traits\TmpFilesCleanup;
use lucatume\WPBrowser\Utils\Filesystem as FS;
use lucatume\WPBrowser\WordPress\Installation;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Process\Exception\ProcessFailedException;

class SiteProjectTest extends \Codeception\Test\Unit
{
    use TmpFilesCleanup;


    /**
     * It should throw if trying scaffold on site that is empty
     *
     * @test
     */
    public function should_throw_if_trying_scaffold_on_site_that_is_empty(): void
    {
        $composerFileCode = <<< EOT
{
  "name": "acme/site-project",
  "require": {},
  "require-dev": {}
}
EOT;

        $projectDir = FS::tmpDir('project_factory_', [
            'site' => [
                'composer.json' => $composerFileCode
            ]
        ]);

        $this->expectException(RuntimeException::class);

        new SiteProject(new ArrayInput([]), new NullOutput(), $projectDir);
    }

    /**
     * It should throw if trying to scaffol on site that is not configured
     *
     * @test
     */
    public function should_throw_if_trying_to_scaffol_on_site_that_is_not_configured(): void
    {
        $composerFileCode = <<< EOT
{
  "name": "acme/site-project",
  "require": {},
  "require-dev": {}
}
EOT;

        $projectDir = FS::tmpDir('project_factory_', [
            'site' => [
                'composer.json' => $composerFileCode
            ]
        ]);
        Installation::scaffold($projectDir);

        $this->expectException(RuntimeException::class);

        new SiteProject(new ArrayInput([]), new NullOutput(), $projectDir);
    }
}

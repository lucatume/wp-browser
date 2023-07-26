<?php
/**
 * ${CARET}
 *
 * @since   TBD
 *
 * @package lucatume\WPBrowser\Project;
 */

namespace lucatume\WPBrowser\Project;

use Codeception\InitTemplate;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ThemeProject extends InitTemplate implements ProjectInterface
{
    private string $themeDir;

    public function __construct(InputInterface $input, OutputInterface $output, protected string $workDir)
    {
        parent::__construct($input, $output);
        $this->themeDir = basename($workDir);
    }

    public function getType(): string
    {
        return 'theme';
    }

    public function getThemeString(): string
    {
        return $this->themeDir;
    }

    public function setup(): void
    {
        // TODO: Implement setup() method.
    }

    public function getTestEnv(): ?TestEnvironment
    {
        return new TestEnvironment();
    }
}

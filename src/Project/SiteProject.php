<?php

namespace lucatume\WPBrowser\Project;

use Codeception\InitTemplate;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SiteProject extends InitTemplate implements ProjectInterface
{
    public function __construct(InputInterface $input, OutputInterface $output, protected string $workDir)
    {
        parent::__construct($input, $output);
    }

    public function getType(): string
    {
        return 'site';
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

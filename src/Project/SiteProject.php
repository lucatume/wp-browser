<?php

namespace lucatume\WPBrowser\Project;

class SiteProject implements ProjectInterface
{
    private string $workDir;

    public function __construct(string $workDir)
    {
        $this->workDir = $workDir;
    }

    public function getType(): string
    {
        return 'site';
    }
}

<?php

namespace lucatume\WPBrowser\Project;

class SiteProject implements ProjectInterface
{
    public function __construct(string $workDir)
    {
        $this->workDir = $workDir;
    }

    public function getType(): string
    {
        return 'site';
    }
}

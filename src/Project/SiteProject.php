<?php

namespace lucatume\WPBrowser\Project;

class SiteProject implements ProjectInterface
{
    public function getType(): string
    {
        return 'site';
    }
}

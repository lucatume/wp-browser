<?php

namespace lucatume\WPBrowser\Project;

interface ProjectInterface
{
    public function getType(): string;

    public function getTestEnv(): ?TestEnvironment;

    public function setup();
}

<?php

namespace tad\WPBrowser\Filesystem;

interface PathFinder
{
    public function getWpRootFolder();

    public function getWPContentFolder();

    public function getWPThemesFolder();

    public function getWPMuPluginsFolder();

    public function getWpPluginsFolder();

    public function getRootFolder();
}
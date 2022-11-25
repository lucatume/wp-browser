<?php

namespace lucatume\WPBrowser\WordPress\InstallationState;

use lucatume\WPBrowser\WordPress\Version;

trait ScaffoldedStateTrait
{
    private ?Version $version = null;

    public function getVersion(): Version
    {
        return $this->version;
    }
}

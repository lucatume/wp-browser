<?php

namespace lucatume\WPBrowser\WordPress\InstallationState;

use lucatume\WPBrowser\WordPress\Version;

trait ScaffoldedStateTrait
{
    private Version $version;

    public function getVersion(): Version
    {
        return $this->version;
    }
}

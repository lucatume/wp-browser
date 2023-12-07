<?php

namespace lucatume\WPBrowser\WordPress\InstallationState;

use lucatume\WPBrowser\WordPress\Version;

trait ScaffoldedStateTrait
{
    /**
     * @var \lucatume\WPBrowser\WordPress\Version
     */
    private $version;

    public function getVersion(): Version
    {
        return $this->version;
    }
}

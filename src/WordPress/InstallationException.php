<?php

namespace lucatume\WPBrowser\WordPress;

use Exception;

class InstallationException extends Exception
{
    public const ROOT_DIR_NOT_FOUND = 0;
    public const WP_CONFIG_FILE_NOT_FOUND = 1;
    public const ROOT_DIR_NOT_RW = 2;
    public const VERSION_FILE_NOT_FOUND = 3;
    public const VERSION_FILE_MISSING_INFO = 4;
    public const STATE_EMPTY = 5;
    public const WP_SETTINGS_FILE_NOT_FOUND = 7;
    public const STATE_CONFIGURED = 9;
    public const STATE_SCAFFOLDED = 10;
    public const WP_CONFIG_SAMPLE_FILE_NOT_FOUND = 11;
    public const CONFIGURATION_INVALID = 12;
    public const INSTALLATION_FAIL = 13;
    public const INVALID_URL = 14;
    public const INVALID_ADMIN_USERNAME = 15;
    public const INVALID_ADMIN_PASSWORD = 16;
    public const INVALID_ADMIN_EMAIL = 17;
    public const INVALID_TITLE = 18;
    public const STATE_SINGLE = 19;
    public const STATE_MULTISITE = 20;
    public const WRITE_ERROR = 21;
    public const WP_CONFIG_FILE_MISSING_PLACEHOLDER = 22;
    public const DELETE_ERROR = 23;
}

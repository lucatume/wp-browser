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
    public const LOAD_FAIL = 24;
    public const NOT_INSTALLED = 25;
    public const MULTISITE_SUBDOMAIN_NOT_INSTALLED = 26;
    public const MULTISITE_SUBFOLDER_NOT_INSTALLED = 27;
    public const ERROR_DURING_LOADING = 28;
    public const TABLE_PREFIX_NOT_STRING = 29;
    public const CONST_NOT_STRING = 31;
    public const NO_ADMIN_USER_FOUND = 32;
    public const RELATIVE_PATH_ROOT_NOT_FOUND = 33;
    public const SQLITE_DROPIN_COPY_FAILED = 34;
    public const SQLITE_PLUGIN_COPY_FAILED = 35;
    public const SQLITE_PLUGIN_DB_COPY_READ_FAILED = 36;
    public const SQLITE_PLUGIN_NOT_FOUND = 37;
    public const DB_DROPIN_ALREADY_EXISTS = 38;
    public const WORDPRESS_NOT_FOUND = 39;
    public const COMMAND_DID_NOT_FINISH_PROPERLY = 40;

    public static function becauseWordPressFailedToLoad(string $bodyContent): self
    {
        return new self(
            'WordPress failed to load for the following reason: ' . lcfirst(rtrim($bodyContent, '.') . '.'),
            self::LOAD_FAIL
        );
    }

    public static function becauseWordPressIsNotInstalled(): self
    {
        return new self(
            'WordPress is not installed.',
            self::NOT_INSTALLED
        );
    }

    public static function becauseWordPressMultsiteIsNotInstalled(bool $isSubdomainInstall): self
    {
        if ($isSubdomainInstall) {
            return new self(
                'WordPress multisite (sub-domain) is not installed.',
                self::MULTISITE_SUBDOMAIN_NOT_INSTALLED
            );
        }

        return new self('WordPress multisite (sub-folder) is not installed.', self::MULTISITE_SUBFOLDER_NOT_INSTALLED);
    }

    public static function becauseCodeceptionCommandDidNotFinish(): self
    {
        return new self(
            "The current Codeception command did not finish properly. WordPress exited early while loading. "
            ."A plugin, theme, or WP-CLI package may have exited before the wp_loaded action could be fired. " .
            "If there is error output above, it may provide clues.",
            self::COMMAND_DID_NOT_FINISH_PROPERLY
        );
    }
}

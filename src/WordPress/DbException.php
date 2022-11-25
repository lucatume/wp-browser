<?php

namespace lucatume\WPBrowser\WordPress;

class DbException extends \Exception
{
    public const INVALID_DB_NAME = 1;
    public const FAILED_QUERY = 2;
    public const INVALID_CONNECTION_PARAMETERS = 3;
    public const MISSING_DB_CREDENTIALS = 3;
}

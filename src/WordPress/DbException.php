<?php

namespace lucatume\WPBrowser\WordPress;

use Exception;

class DbException extends Exception
{
    public const INVALID_DB_NAME = 1;
    public const FAILED_QUERY = 2;
    public const INVALID_CONNECTION_PARAMETERS = 3;
    public const MISSING_DB_CREDENTIALS = 3;
    public const DUMP_FILE_NOT_EXIST = 6;
    public const DUMP_FILE_NOT_READABLE = 7;
    public const TABLE_PREFIX_NOT_FOUND = 8;
    public const FAILED_DUMP = 9;
    public const PREPARE_FAILED = 10;
}

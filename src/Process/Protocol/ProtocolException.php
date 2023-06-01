<?php

namespace lucatume\WPBrowser\Process\Protocol;

use Exception;

class ProtocolException extends Exception
{
    public const EMPTY_INPUT = 0;
    public const MISSING_START_CHAR = 1;
    public const NON_NUMERIC_LENGTH = 2;
    public const MISMATCHING_LENGTH = 3;
    public const MISSING_ENDING_CRLF = 4;
    public const INCORRECT_ENCODING = 5;
    public const DECODE_NEGATIVE_OFFSET = 6;
    public const AUTLOAD_FILE_NOT_FOUND = 7;
    public const REQUIRED_FILE_NOT_FOUND = 8;
    public const CODECEPTION_ROOT_DIR_NOT_FOUND = 9;
    public const CWD_NOT_FOUND = 10;
    public const COMPOSER_AUTOLOAD_FILE_NOT_FOUND = 11;
    public const COMPOSER_BIN_DIR_NOT_FOUND = 12;
}

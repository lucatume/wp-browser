<?php

namespace lucatume\WPBrowser\WordPress;

use Exception;

class WpConfigFileException extends Exception
{
    public const CONSTANT_UNDEFINED = 1;
    public const VARIABLE_UNDEFINED = 2;
    public const TABLE_PREFIX_NOT_STRING = 3;
}

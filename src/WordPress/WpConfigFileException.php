<?php

namespace lucatume\WPBrowser\WordPress;

class WpConfigFileException extends \Exception
{
    public const CONSTANT_UNDEFINED = 1;
    public const VARIABLE_UNDEFINED = 2;
}
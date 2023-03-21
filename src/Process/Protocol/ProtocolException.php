<?php

namespace lucatume\WPBrowser\Process\Protocol;

class ProtocolException extends \Exception
{
    public const EMPTY_INPUT = 0;
    public const MISSING_START_CHAR = 1;
    public const NON_NUMERIC_LENGTH = 2;
    public const MISMATCHING_LENGTH = 3;
    public const MISSING_ENDING_CRLF = 4;
    public const INCORRECT_ENCODING = 5;
    public const DECODE_NEGATIVE_OFFSET = 6;
}

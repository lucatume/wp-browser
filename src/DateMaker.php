<?php

namespace tad\test\wordpress\generator;


class DateMaker
{
    public static function now()
    {
        return date('Y-m-d G:i:s');
    }

    public static function zero()
    {
        return '0000-00-00 00:00:00';
    }
} 
<?php

namespace {

    // PHPUnit 6 compat
    if (class_exists('PHPUnit\Framework\TestCase')) {
        if ( ! class_exists('PHPUnit_Util_Getopt') && class_exists('PHPUnit\Util\Getopt')) {
            class_alias('PHPUnit\Util\Getopt', 'PHPUnit_Util_Getopt');
        }
    }
}

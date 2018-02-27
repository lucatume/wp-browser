<?php

namespace {

    // PHPUnit 6 compat
    if (class_exists('PHPUnit\Framework\TestCase')) {
		class_alias('PHPUnit\Framework\TestCase', 'PHPUnit_Framework_TestCase');
		class_alias('PHPUnit\Framework\Exception', 'PHPUnit_Framework_Exception');
        if ( ! class_exists('PHPUnit_Util_Getopt') && class_exists('PHPUnit\Util\Getopt')) {
            class_alias('PHPUnit\Util\Getopt', 'PHPUnit_Util_Getopt');
        }
    }
}

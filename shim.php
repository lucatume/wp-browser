<?php
namespace {
    // PHPUnit 6 compat
    if (!class_exists('PHPUnit_Framework_TestCase') && class_exists('PHPUnit\Framework\TestCase')) {
        class_alias('PHPUnit\Util\Getopt', 'PHPUnit_Util_Getopt');
    }
}

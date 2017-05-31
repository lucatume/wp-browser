<?php

namespace {

    // PHPUnit 6 compat
    $shims = [
        'PHPUnit_Util_Getopt' => 'PHPUnit\Util\Getopt',
    ];
    foreach ($shims as $original => $alias) {
        if ( ! class_exists($alias) && class_exists($original)) {
            class_alias($original, $alias);
        }
    }
}

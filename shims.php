<?php

namespace {

    // PHPUnit 6 compat
    if (class_exists('PHPUnit\Framework\TestCase')) {
		$aliases = [
			'PHPUnit\Framework\Test'                 => 'PHPUnit_Framework_Test',
			'PHPUnit\Framework\TestSuite'            => 'PHPUnit_Framework_TestSuite',
			'PHPUnit\Framework\TestCase'             => 'PHPUnit_Framework_TestCase',
			'PHPUnit\Framework\Assert'               => 'PHPUnit_Framework_Assert',
			'PHPUnit\Framework\Exception'            => 'PHPUnit_Framework_Exception',
			'PHPUnit\Framework\Warning'              => 'PHPUnit_Framework_Warning',
			'PHPUnit\Framework\TestListener'         => 'PHPUnit_Framework_TestListener',
			'PHPUnit\Framework\AssertionFailedError' => 'PHPUnit_Framework_AssertionFailedError',
			'PHPUnit\Util\Getopt'                    => 'PHPUnit_Util_Getopt',
		];

		foreach ($aliases as $new => $old) {
			if (!class_exists($old) && class_exists($new)) {
				class_alias($new, $old);
			}
		}
    }
}

<?php

namespace {

    // PHPUnit 6 compat
    if (class_exists('PHPUnit\Framework\TestCase')) {
		$aliases = [
			'PHPUnit\Framework\Test'                       => 'PHPUnit_Framework_Test',
			'PHPUnit\Framework\TestSuite'                  => 'PHPUnit_Framework_TestSuite',
			'PHPUnit\Framework\TestCase'                   => 'PHPUnit_Framework_TestCase',
			'PHPUnit\Framework\Assert'                     => 'PHPUnit_Framework_Assert',
			'PHPUnit\Framework\Exception'                  => 'PHPUnit_Framework_Exception',
			'PHPUnit\Framework\ExpectationFailedException' => 'PHPUnit_Framework_ExpectationFailedException',
			'PHPUnit\Framework\Warning'                    => 'PHPUnit_Framework_Warning',
			'PHPUnit\Framework\Error\Notice'               => 'PHPUnit_Framework_Error_Notice',
			'PHPUnit\Framework\Error\Warning'              => 'PHPUnit_Framework_Error_Warning',
			'PHPUnit\Framework\TestListener'               => 'PHPUnit_Framework_TestListener',
			'PHPUnit\Framework\AssertionFailedError'       => 'PHPUnit_Framework_AssertionFailedError',
			'PHPUnit\Util\Getopt'                          => 'PHPUnit_Util_Getopt',
			'PHPUnit\Util\GlobalState'                     => 'PHPUnit_Util_GlobalState',
		];

		foreach ($aliases as $new => $old) {
			if (!class_exists($old) && class_exists($new)) {
				class_alias($new, $old);
			}
		}
    }
}

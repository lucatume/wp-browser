<?php

namespace {

	// PHPUnit 6+ compat
	if (class_exists('PHPUnit\Framework\TestCase')) {
		$aliases = [
			'PHPUnit\Util\Getopt' => 'PHPUnit_Util_Getopt',
		];

		foreach ($aliases as $original => $alias) {
			if (!class_exists($alias) && class_exists($original)) {
				class_alias($original, $alias);
			}
		}
	}

	$inverseAliases = [
		'PHPUnit_Runner_Version' => 'PHPUnit\Runner\Version',
		'PHPUnit_Framework_TestResult' => 'PHPUnit\Framework\TestResult',
		'PHPUnit_Framework_Test' => 'PHPUnit\Framework\Test',
	];

	foreach ($inverseAliases as $original => $alias) {
		class_alias($original, $alias);
	}
}

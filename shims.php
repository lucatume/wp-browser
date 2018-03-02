<?php

namespace {

	// PHPUnit 6+ compat
	if (class_exists('PHPUnit\Framework\TestCase')) {
		$aliases = [
			'PHPUnit\Util\Getopt' => 'PHPUnit_Util_Getopt',
			// inverse aliases
			'PHPUnit_Runner_Version' => 'PHPUnit\Runner\Version',
		];

		foreach ($aliases as $original => $alias) {
			if (!class_exists($alias) && class_exists($original)) {
				class_alias($original, $alias);
			}
		}
	}
}

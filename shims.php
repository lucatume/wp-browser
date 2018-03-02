<?php

namespace {

	// PHPUnit 6+ compat
	if (class_exists('PHPUnit\Framework\TestCase')) {
		$aliases = [
			'PHPUnit\Framework\Assert' => 'PHPUnit_Framework_Assert',
			'PHPUnit\Util\Getopt' => 'PHPUnit_Util_Getopt',

			// inverse aliases
			'PHPUnit_Runner_Version' => 'PHPUnit\Runner\Version',
		];

		foreach ($aliases as $new => $old) {
			if (!class_exists($old) && class_exists($new)) {
				class_alias($new, $old, false);
			}
		}
	}
}

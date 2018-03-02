<?php

namespace {

	// PHPUnit 6+ compat
	if (class_exists('PHPUnit\Framework\TestCase')) {
		$aliases = [
			'PHPUnit\Framework\Assert' => 'PHPUnit_Framework_Assert',
			'PHPUnit\Util\Getopt' => 'PHPUnit_Util_Getopt',
		];

		foreach ($aliases as $new => $old) {
			if (!class_exists($old) && class_exists($new)) {
				class_alias($new, $old);
			}
		}
	}

	$testcaseAliases = [
		'Codeception\TestCase\WpTestCase' => 'WP_UnitTestCase',
		'Codeception\TestCase\WPRestApiTestCase' => 'WP_Test_REST_TestCase',
		'Codeception\TestCase\WPXMLRPCTestCase' => 'WP_XMLRPC_UnitTestCase',
		'Codeception\TestCase\WPAjaxTestCase' => 'WP_Ajax_UnitTestCase',
		'Codeception\TestCase\WPCanonicalTestCase' => 'WP_Canonical_UnitTestCase',
		'Codeception\TestCase\WPRestControllerTestCase' => 'WP_Test_REST_Controller_Testcase',
		'Codeception\TestCase\WPRestPostTypeControllerTestCase' => 'WP_Test_REST_Post_Type_Controller_Testcase',
	];

	foreach ($testcaseAliases as $old => $new) {
		class_alias($new, $old);
	}
}

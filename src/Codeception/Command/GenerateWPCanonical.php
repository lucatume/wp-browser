<?php

namespace Codeception\Command;


use Codeception\Lib\Generator\WPUnit;

class GenerateWPCanonical extends GenerateWPUnit {
	const SLUG = 'generate:wpcanonical';

	protected function getGenerator( $config, $class ) {
		return new WPUnit( $config, $class, '\\Codeception\\TestCase\\WPCanonicalTestCase' );
	}

	public function getDescription() {
		return 'Generates a WPCanonicalTestCase: a WP_Canonical_UnitTestCase extension with Codeception additions.';
	}
}
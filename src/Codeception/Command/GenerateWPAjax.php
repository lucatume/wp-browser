<?php

namespace Codeception\Command;


use Codeception\Lib\Generator\WPUnit;

class GenerateWPAjax extends GenerateWPUnit {

	const SLUG = 'generate:wpajax';

	protected function getGenerator( $config, $class ) {
		return new WPUnit( $config, $class, '\\Codeception\\TestCase\\WPAjaxTestCase' );
	}

	public function getDescription() {
		return 'Generates a WPAjaxTestCase: a WP_Ajax_UnitTestCase extension with Codeception additions.';
	}
}
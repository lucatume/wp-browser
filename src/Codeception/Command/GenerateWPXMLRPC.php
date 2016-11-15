<?php

namespace Codeception\Command;


use Codeception\Lib\Generator\WPUnit;

class GenerateWPXMLRPC extends GenerateWPUnit
{
	use Shared\FileSystem;
	use Shared\Config;

	const SLUG = 'generate:wpxmlrpc';

	public function getDescription()
	{
		return 'Generates a WPXMLRPCTestCase: a WP_XMLRPC_UnitTestCase extension with Codeception additions.';
	}

	protected function getGenerator($config, $class)
	{
		return new WPUnit($config, $class, '\\Codeception\\TestCase\\WPXMLRPCTestCase');
	}
}
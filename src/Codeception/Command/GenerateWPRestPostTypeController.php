<?php

namespace Codeception\Command;


use Codeception\Lib\Generator\WPUnit;

class GenerateWPRestPostTypeController extends GenerateWPUnit
{
	use Shared\FileSystem;
	use Shared\Config;

	const SLUG = 'generate:wprestposttypecontroller';

	public function getDescription()
	{
		return 'Generates a WPRestApiTestCase: a WP_Test_REST_Post_Type_Controller_Testcase extension with Codeception additions.';
	}

	protected function getGenerator($config, $class)
	{
		return new WPUnit($config, $class, '\\Codeception\\TestCase\\WPRestPostTypeControllerTestCase');
	}
}
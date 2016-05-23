<?php

namespace Codeception\Module;


use Codeception\Lib\ModuleContainer;
use Codeception\Module;
use tad\WPBrowser\Environment\Constants;

class WPQueries extends Module {

	/**
	 * @var Constants
	 */
	private $constants;

	public function __construct( ModuleContainer $moduleContainer, $config, Constants $constants = null ) {
		$this->constants = $constants ? $constants : new Constants();
		parent::__construct( $moduleContainer, $config );
	}

		
}
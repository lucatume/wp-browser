<?php

namespace Codeception\Module;

use Codeception\Exception\ModuleConfigException;
use Codeception\Module;

/**
 * Class WPBootstrapper
 *
 * Bootstraps a WordPress installation to access its functions.
 *
 * @package Codeception\Moduleb
 */
class WPBootstrapper extends Module {

	protected $requiredFields = [ 'wpRootFolder' ];

	public function _initialize() {
		$wpRootFolder = $this->config['wpRootFolder'];
		if ( ! is_dir( $wpRootFolder ) ) {
			throw new ModuleConfigException( __CLASS__, 'WordPress root folder is not a folder' );
		}
		if ( ! is_readable( $wpRootFolder ) ) {
			throw new ModuleConfigException( __CLASS__, 'WordPress root folder is not readable' );
		}
		$wpLoad = $wpRootFolder . DIRECTORY_SEPARATOR . 'wp-load.php';
		if ( ! file_exists( $wpLoad ) ) {
			throw new ModuleConfigException( __CLASS__, 'WordPress root folder does not contain a wp-load.php file' );
		}
		if ( ! is_readable( $wpLoad ) ) {
			throw new ModuleConfigException( __CLASS__, 'wp-load.php file is not readable' );
		}
		$this->config['wpLoadPath'] = $wpLoad;
	}

	public function bootstrapWp() {
		include_once( $this->config['wpLoadPath'] );
	}

}
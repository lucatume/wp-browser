<?php

namespace Codeception\Module;

use Codeception\Lib\ModuleContainer;
use Codeception\Module;
use tad\WPBrowser\Services\WP\Bootstrapper;

class WPNonces extends WPBootstrapper {

	/**
	 * @var Bootstrapper
	 */
	protected $wp;

	public function __construct( ModuleContainer $moduleContainer, $config, Bootstrapper $wpBootstrapper = null ) {
		parent::__construct( $moduleContainer, $config );
		$this->wp = $wpBootstrapper ? $wpBootstrapper : new Bootstrapper( );
	}

	public function _initialize() {
		parent::_initialize();
		$this->wp->setLoadPath($this->wpLoadPath);
	}

	/**
	 * Generates a nonce for the given user and action.
	 * 
	 * @param string $action
	 * @param int    $user
	 * @param bool   $willVerify
	 */
	public function createNonce( $action, $user = 0 ) {
		$nonce = $this->wp->createNonce( $action, $user );
		if ( empty( $nonce ) ) {
			throw new \RuntimeException( static::class . ': could not generate nonce for action [' . $action . '] and user [' . $user . ']' );
		}

		return $nonce;
	}
}
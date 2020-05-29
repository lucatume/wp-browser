<?php
/**
 * Provides methods to detect the correct configuration of separate process testing in tests.
 *
 * The PHP 7.0+ version.
 *
 * @package tad\WPBrowser\Traits
 */

//phpcs:ignoreFile

namespace tad\WPBrowser\Traits;

use Codeception\Exception\TestRuntimeException;

/**
 * Trait WithSeparateProcessChecks
 *
 * @since   TBD
 *
 * @package tad\WPBrowser\Traits
 */
trait WithSeparateProcessChecks {
	/**
	 * Overrides the base test case implementation to check the separate process configuration for the test
	 * case is correct.
	 *
	 * @param bool $runTestInSeparateProcess Whether the test should run in a separate process or not.
	 *
	 * @throws TestRuntimeException If the test method, or test case, is configured to run in a separate process
	 *                              preserving the global state.
	 */
	public function setRunTestInSeparateProcess( $runTestInSeparateProcess ) {
		parent::setRunTestInSeparateProcess( $runTestInSeparateProcess );
		$this->checkSeparateProcessConfiguration();
	}
}

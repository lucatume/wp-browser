<?php
/**
 * Provides methods to detect the correct configuration of separate process testing in tests.
 *
 * A different version of the trait is loaded depending on the PHP version.
 *
 * @package tad\WPBrowser\Traits
 */

//phpcs:ignoreFile

namespace tad\WPBrowser\Traits;

use Codeception\Exception\TestRuntimeException;
use function tad\WPBrowser\phpunitVersion;

if ( version_compare(phpunitVersion(),'7.0.0','>=') ) {
	/**
	 * Trait WithSeparateProcessChecks
	 *
	 * @since   TBD
	 *
	 * @package tad\WPBrowser\Traits
	 */
	trait WithSeparateProcessChecks
	{
		/**
		 * Overrides the base test case implementation to check the separate process configuration for the test
		 * case is correct.
		 *
		 * @param bool $runTestInSeparateProcess Whether the test should run in a separate process or not.
		 *
		 * @throws TestRuntimeException If the test method, or test case, is configured to run in a separate process
		 *                              preserving the global state.
		 */
		public function setRunTestInSeparateProcess(bool $runTestInSeparateProcess): void
		{
			parent::setRunTestInSeparateProcess($runTestInSeparateProcess);
			$this->checkSeparateProcessConfiguration();
		}
	}
} else {
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
}

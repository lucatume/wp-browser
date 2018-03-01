<?php

namespace tad\WPBrowser\Snapshot;

use tad\WPBrowser\Snapshot\WPHtmlOutputDriver as Driver;

class WPHtmlOutputDriverTest extends \Codeception\Test\Unit {

	public $currentUrl = 'http://example.com';

	public $examplesUrl = 'http://example.com';

	/**
	 * @var \UnitTester
	 */
	protected $tester;

	/**
	 * @test
	 * it should be instantiatable
	 */
	public function it_should_be_instantiatable() {

		$sut = $this->make_instance();

		$this->assertInstanceOf(Driver::class, $sut);
	}

	/**
	 * @return Driver
	 */
	private function make_instance() {
		return new Driver($this->currentUrl);
	}

	/**
	 * It should match two identical HTML documents
	 *
	 * @test
	 */
	public function should_match_two_identical_html_documents() {
		$file = 'html-1';
		$one  = $two = $this->getSourceFileContents($file);

		$driver = $this->make_instance();

		$driver->match($one, $driver->evalCode($two));
	}

	protected function getSourceFileContents($file) {
		return file_get_contents(codecept_data_dir('snapshots/' . $file . '.php'));
	}

	/**
	 * It should match two identical docs differing by nonce
	 *
	 * @test
	 */
	public function should_match_two_identical_docs_differing_by_nonce() {
		$file   = 'html-2';
		$one    = $two = $this->getSourceFileContents($file);
		$driver = $this->make_instance();

		$driver->match($one, $driver->evalCode($two));
	}

	/**
	 * It should match two identical docs differing by URLs
	 *
	 * @test
	 */
	public function should_match_two_identical_docs_differing_by_urls() {
		$file = 'html-3';
		$one  = $two = $this->getSourceFileContents($file);

		$this->currentUrl = 'http://www.theaveragedev.com';
		$two              = $this->replaceExampleUrlIn($two);
		$driver           = $this->make_instance();

		$driver->match($one, $driver->evalCode($two));
	}

	protected function replaceExampleUrlIn($input) {
		return str_replace($this->examplesUrl, $this->currentUrl, $input);
	}

	/**
	 * It should match two identical docs differing in URL and scheme
	 *
	 * @test
	 */
	public function should_match_two_identical_docs_differing_in_url_and_scheme() {
		$file = 'html-3';
		$one  = $two = $this->getSourceFileContents($file);

		$this->currentUrl = 'https://www.theaveragedev.com';
		$two              = $this->replaceExampleUrlIn($two);
		$driver           = $this->make_instance();

		$driver->match($one, $driver->evalCode($two));
	}

	/**
	 * It should match two identical docs differing in URL, scheme and port
	 *
	 * @test
	 */
	public function should_match_two_identical_docs_differing_in_url_scheme_and_port() {
		$file = 'html-3';
		$one  = $two = $this->getSourceFileContents($file);

		$this->currentUrl = 'https://www.theaveragedev.com:8080';
		$two              = $this->replaceExampleUrlIn($two);
		$driver           = $this->make_instance();

		$driver->match($one, $driver->evalCode($two));
	}

	/**
	 * It should match two identical docs differing in url, scheme, port and path
	 *
	 * @test
	 */
	public function should_match_two_identical_docs_differing_in_url_scheme_port_and_path() {
		$file = 'html-3';
		$one  = $two = $this->getSourceFileContents($file);

		$this->currentUrl = 'https://www.theaveragedev.com:8080/some/path';
		$two              = $this->replaceExampleUrlIn($two);
		$driver           = $this->make_instance();

		$driver->match($one, $driver->evalCode($two));
	}

	/**
	 * It should allow comparing HTML fragments
	 *
	 * @test
	 */
	public function should_allow_comparing_html_fragments() {
		$file = 'html-4';
		$one  = $two = $this->getSourceFileContents($file);

		$this->currentUrl = 'https://www.theaveragedev.com:8080/some/path';
		$two              = $this->replaceExampleUrlIn($two);
		$driver           = $this->make_instance();

		$driver->match($one, $driver->evalCode($two));
	}

	/**
	 * It should allow defining the snapshot URL to only replace that
	 *
	 * @test
	 */
	public function should_allow_defining_the_snapshot_url_to_only_replace_that() {
		$template = $this->getSourceFileContents('html-5');

		$currentUrl  = 'https://www.theaveragedev.com:8080/some/path';
		$snapshotUrl = 'http://example.com';
		$expected    = $this->replaceUrlInTemplate($snapshotUrl, $template);
		$actual      = $this->replaceUrlInTemplate($currentUrl, $template);
		$driver      = new Driver($currentUrl, $snapshotUrl);

		$driver->match($expected, $driver->evalCode($actual));
	}

	protected function replaceUrlInTemplate($replacementUrl, $template) {
		return str_replace('{{url}}', $replacementUrl, $template);
	}

	/**
	 * It should allow setting tolerable differences
	 *
	 * @test
	 */
	public function should_allow_setting_tolerable_differences() {
		$template = $this->getSourceFileContents('html-6');

		$driver = new Driver();

		$actual   = str_replace(['{{one}}', '{{two}}'], ['23', '89'], $template);
		$expected = str_replace(['{{one}}', '{{two}}'], ['foo', 'bar'], $template);

		$driver->setTolerableDifferences(['23', '89']);

		$driver->match($expected, $driver->evalCode($actual));
	}

	/**
	 * It should allow setting prefixes for tolerable differences
	 *
	 * @test
	 */
	public function should_allow_setting_prefixes_for_tolerable_differences() {
		$template = $this->getSourceFileContents('html-7');

		$driver = new Driver();

		$actual   = str_replace(['{{one}}', '{{two}}'], ['23', '89'], $template);
		$expected = str_replace(['{{one}}', '{{two}}'], ['foo', 'bar'], $template);

		$driver->setTolerableDifferences(['23', '89']);
		$driver->setTolerableDifferencesPostfixes(['prefix-', 'another_prefix-']);

		$driver->match($expected, $driver->evalCode($actual));
	}

	/**
	 * It should allow setting postfixes for tolerable differences
	 *
	 * @test
	 */
	public function should_allow_setting_postfixes_for_tolerable_differences() {
		$template = $this->getSourceFileContents('html-8');

		$driver = new Driver();

		$actual   = str_replace(['{{one}}', '{{two}}'], ['23', '89'], $template);
		$expected = str_replace(['{{one}}', '{{two}}'], ['foo', 'bar'], $template);

		$driver->setTolerableDifferences(['23', '89']);
		$driver->setTolerableDifferencesPostfixes(['-postfix', '-another_postfix']);

		$driver->match($expected, $driver->evalCode($actual));
	}

	/**
	 * It should allow setting prefixes and postfixes for tolerable differences
	 *
	 * @test
	 */
	public function should_allow_setting_prefixes_and_postfixes_for_tolerable_differences() {
		$template = $this->getSourceFileContents('html-9');

		$driver = new Driver();

		$actual   = str_replace(['{{one}}', '{{two}}'], ['23', '89'], $template);
		$expected = str_replace(['{{one}}', '{{two}}'], ['foo', 'bar'], $template);

		$driver->setTolerableDifferences(['23', '89']);
		$driver->setTolerableDifferencesPostfixes(['prefix-', 'another_prefix-']);
		$driver->setTolerableDifferencesPostfixes(['-postfix', '-another_postfix']);

		$driver->match($expected, $driver->evalCode($actual));
	}

	protected function setUp() {
		return parent::setUp();
	}

	protected function maybeSkip() {
		if (!class_exists('Spatie\\Snapshots\\Drivers\\VarDriver')) {
			$this->markTestSkipped('Only run on PHP 7.0+');
		}
	}
}
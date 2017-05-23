<?php

use Step\Cli\Codecept as Tester;

class BootstrapCest {

	/**
	 * @var string
	 */
	protected $path = '';

	public function _before(Tester $I) {
		$this->path = codecept_output_dir('/temp');
		@mkdir($this->path, 0777, true);
	}

	public function _after(Tester $I) {
		$I->deleteDir($this->path);
	}

	public function _failed(Tester $I) {
		$I->deleteDir($this->path);
	}

	/**
	 * It should scaffold a codeception config file
	 *
	 * @test
	 */
	public function it_should_scaffold_a_codeception_config_file(Tester $I) {
		$I->amInPath($this->path);
		$I->runCodecept('init wpbrowser');

		$I->seeFileFound('codeception.yml');
	}
}

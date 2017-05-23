<?php

namespace Step\Cli;

class Codecept extends \CliTester {

	public function runCodecept($command) {
		$codecept = codecept_root_dir('vendor/bin/codecept');
		$command = "{$codecept} {$command}";
		$this->runShellCommand($command);
	}

}
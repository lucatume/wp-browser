<?php

namespace cli\Test;

use Step\Cli\CodeceptionCommand as CliTester;

class WpceptRedirectionCest extends BaseCommandCest {

    /**
     * It should not fail when calling the `wpcept` command
     *
     * @test
     */
    public function it_should_not_fail_when_calling_the_wpcept_command(CliTester $I) {
        $I->runWpcept('list');
    }
}

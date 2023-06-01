<?php

namespace cli\Test;

use Step\Cli\CodeceptionCommand;

class BaseCommandCest
{
    public function _before(CodeceptionCommand $I): void
    {
        $I->deleteSandbox();
        $I->createSandbox();
        $I->amInSandbox();
    }

    public function _after(CodeceptionCommand $I): void
    {
        $I->deleteSandbox();
    }
}

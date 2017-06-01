<?php

use Step\Cli\CodeceptionCommand as CliTester;

class GenerateCommandsCest {

    public function _before(CliTester $I) {
        $I->deleteSandbox();
        $I->createSandbox();
        $I->amInSandbox();
    }

    public function _after(CliTester $I) {
        $I->deleteSandbox();
    }

    /**
     * It should scaffold wpunit-like tests
     *
     * @example ["wpunit"]
     * @example ["wpajax"]
     * @example ["wpcanonical"]
     * @example ["wprest"]
     * @example ["wprestcontroller"]
     * @example ["wprestposttypecontroller"]
     * @example ["wpxmlrpc"]
     *
     * @test
     */
    public function it_should_scaffold_wpunit_like_tests(CliTester $I, \Codeception\Example $data) {
        $I->runCodecept("generate:{$data[0]} unit Some");

        $I->seeFileFound('tests/unit/SomeTest.php');
    }
}

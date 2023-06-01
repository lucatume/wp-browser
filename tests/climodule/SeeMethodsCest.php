<?php
use ClimoduleTester as Tester;

class SeeMethodsCest
{
    public function it_should_see_correct_output(Tester $I): void
    {
        $I->cli(['eval','echo \'hi\';']);
        $I->seeInShellOutput('hi');
    }

    public function it_should_not_see_correct_output(Tester $I): void
    {
        $I->cli(['eval','echo \'hi\';']);
        $I->dontSeeInShellOutput('hello');
    }

    public function it_should_see_correct_output_matches(Tester $I): void
    {
        $I->cli(['eval','echo \'hi\';']);
        $I->seeShellOutputMatches('/\w+/');
    }

    public function it_should_not_see_correct_output_matches(Tester $I): void
    {
        $I->cli(['eval','echo \'hi\';']);
        $I->dontSeeShellOutputMatches('/\d+/');
    }

    public function it_should_see_correct_result_code(Tester $I): void
    {
        $I->cli(['eval','echo \'hi\';']);
        $I->seeResultCodeIs(0);
    }

    public function it_should_not_see_correct_result_code(Tester $I): void
    {
        $I->cli(['eval','echo \'hi\';']);
        $I->seeResultCodeIsNot(1);
    }
}

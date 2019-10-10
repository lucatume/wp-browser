<?php

class SeeMethodsCest
{
    public function it_should_see_correct_output(Wpcli_moduleTester $I)
    {
        $I->cli('eval "echo \'hi\';"');
        $I->seeInShellOutput( 'hi' );
    }

    public function it_should_not_see_correct_output(Wpcli_moduleTester $I)
    {
        $I->cli('eval "echo \'hi\';"');
        $I->dontSeeInShellOutput( 'hello' );
    }

    public function it_should_see_correct_output_matches(Wpcli_moduleTester $I)
    {
        $I->cli('eval "echo \'hi\';"');
        $I->seeShellOutputMatches( '\w+' );
    }

    public function it_should_see_correct_result_code(Wpcli_moduleTester $I)
    {
        $I->cli('eval "echo \'hi\';"');
        $I->seeResultCodeIs(0);
    }

    public function it_should_not_see_correct_result_code(Wpcli_moduleTester $I)
    {
        $I->cli('eval "echo \'hi\';"');
        $I->seeResultCodeIsNot(1);
    }
}

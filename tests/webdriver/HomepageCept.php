<?php
$I = new AcceptanceTester($scenario);
$I->wantTo('access the homepage of the site');
$I->amOnPage('/');
$I->seeElement('body.home');

<?php
$I = new AcceptanceTester($scenario);
$I->wantTo('access the homepage of a subdomain site');
$I->amOnSubdomain('test1');
$I->amOnPage('/');
$I->seeElement('body.home');
$I->seeInTitle('Test Subdomain 1');
$I->amOnSubdomain('test2');
$I->amOnPage('/');
$I->seeElement('body.home');
$I->seeInTitle('Test Subdomain 2');

<?php
$I = new AcceptanceTester($scenario);
$I->wantTo('access the homepage of a subdomain site');
$I->amOnSubdomain(getenv('WP_SUBDOMAIN_1') ?: 'test1');
$I->amOnPage('/');
$I->seeElement('body.home');
$I->seeInTitle(getenv('WP_SUBDOMAIN_1_TITLE') ?: 'Test Subdomain 1');
$I->amOnSubdomain(getenv('WP_SUBDOMAIN_1') ?: 'test2');
$I->amOnPage('/');
$I->seeElement('body.home');
$I->seeInTitle(getenv('WP_SUBDOMAIN_2_TITLE') ?: 'Test Subdomain 2');

<?php

$I = new AcceptanceTester($scenario);
$I->wantTo('access the homepage of a subdomain site');

$I->useBlog(2);
$post2Id = $I->havePostInDatabase(['post_title' => 'Post test1']);

$I->amOnUrl($I->grabBlogUrl(2));
$I->amOnPage('/');

$I->seeElement('body.home');
$I->see('Post test1');

$I->useBlog(3);
$I->havePostInDatabase(['post_title' => 'Post test2']);

$I->amOnUrl($I->grabBlogUrl(3));
$I->amOnPage('/');

$I->seeElement('body.home');
$I->see('Post test2');

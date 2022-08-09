<?php
/** @var Codeception\Scenario $scenario */
$I = new EventsTester($scenario);
$I->wantTo('add posts and clean them up using the Events API');

/*
 * Use WordPress methods, thanks to the `WPLoader` module, to use WordPress, or our own, API to insert posts.
 * This will prevent, but, `WPDb` from removing the inserted rows and clean up, so we remove the posts and meta
 * with an event and our custom clean-up function.
 */
$ids = $I->factory()->post->create_many(3, [ 'post_type' => 'some_post_type' ]);

lucatume\WPBrowser\dispatch('test-event-1/setup-posts', __FILE__, [
    'ids' => $ids,
    'db'  => $I
]);

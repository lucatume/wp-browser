<?php

$registerPostsCleanup = static function (tad\WPBrowser\Events\WpbrowserEvent $event) {
    $ids = $event->get('ids', []);
    /** @var \EventsTester $db */
    $db = $event->get('db');

    // When tests are done, then remove all the posts we've created at the start of the test, if any.
    tad\WPBrowser\addListener(
        Codeception\Events::TEST_AFTER,
        static function () use ($ids, $db) {
            foreach ($ids as $id) {
                $db->dontHavePostInDatabase([ 'ID' => $id ], true);
                // Ensure the clean up did happen correctly.
                $db->dontSeePostInDatabase([ 'ID' => $id ]);
                $db->dontSeePostMetaInDatabase([ 'post_id' => $id ]);
            }
        }
    );
};

// Listen for this event to register the posts to remove, along with their custom fields, after the test.
tad\WPBrowser\addListener('test-event-1/setup-posts', $registerPostsCleanup);

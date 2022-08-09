## Events API

Codeception [comes with a set of events modules and extensions can subscribe to][1].  

Codeception Events API is, but, only available to Modules and Extensions, and while that might be good for most cases, it might not cover a number of edge cases.  

Similarly to WordPress `add_action` function, `wp-browser` provides the `tad\WPBrowser\addListener` function:

```php
function addListener($eventName, callable $listener, $priority = 0);
``` 

> The priority works the reverse way as it does in WordPress: highest number will be processed first!

Again similarly to WordPress `do_action` function, the `tad\WPBrowser\dispatch` function:

```php
function dispatch($eventName, $origin = null, array $context = []);
```

This is the kind of API that is better shown with an example, though.

### Example

In this example I'm writing acceptance tests and would like to avoid the performance hit that the `cleanup` configuration parameter of the `Db`, or `WPDb`, module implies.  
The `cleanup` parameter will trigger the drop of all tables in the test database and the re-import of the SQL dump file, or files, between each test.  
This will ensure a clean starting fixture between tests, but for larger setup fixtures this might be a long operation that wastes precious seconds when, say, the only change is the addition of 3 posts, as in this example.

The Events API allows implementing a tailored clean-up procedure that can avoid costly clean ups between tests.  

In the suite bootstrap file, e.g. `tests/acceptance/_bootstrap.php`, I add a listener on the `my-plugin-test/setup-posts` event.  
The event will contain information about what post IDs I've set up in the tests and will provide an instance of the tester object to handle database manipulation.  
With that information, the costly `cleanup` procedure can be avoided.

```php
<?php

$registerPostsCleanup = static function (lucatume\WPBrowser\Events\WpbrowserEvent $event) {
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
```

In this simple test I'm adding 3 posts [using the `factory` provided by the `WPLoader` module in `loadOnly` mode][2] and want to make sure those, and the relative meta, are removed at the end of the tests.
The `WPDb` module, extending the `Db` module from Codeception, will remove the inserted rows, but will not take care of modified rows, or rows not inserted by the `WPDb` module.

Mirroring the requirement of the clean up function I've defined above, I'm passing the post IDs of the posts I've created and the current tester to provide the clean up function with database handling capabilities.

```php
<?php
/** @var Codeception\Scenario $scenario */
$I = new AcceptanceTester($scenario);
$I->wantTo('add posts and clean them up using the Events API');

/*
 * Use WordPress methods, thanks to the `WPLoader` module, to use WordPress, or our own, API to insert posts.
 * This will prevent, but, `WPDb` from removing the inserted rows and clean up, so we remove the posts and meta
 * with an event and our custom clean-up function.
 */
$ids = $I->factory()->post->create_many(3, [ 'post_type' => 'some_post_type' ]);

tad\WPBrowser\dispatch('test-event-1/setup-posts', __FILE__, [
    'ids' => $ids,
    'db'  => $I
]);
```

[1]: https://codeception.com/docs/08-Customization#Events

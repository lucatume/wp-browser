## Events API

Codeception [comes with a set of events modules and extensions can subscribe to][1].  

Codeception Events API is, but, only available to Modules and Extensions, and while that might be good for most cases, it might not cover a number of edge cases.  

Similarly to WordPress `add_action` function, `wp-browser` provides the `tad\WPBrowser\addListener` function and, again similarly to WordPress `do_action` function, the `tad\WPBrowser\dispatch` function.

### Example

In this example I'm writing acceptance tests and would like to avoid the performance hit that the `cleanup` configuration parameter of the `Db`, or `WPDb`, module implies.  
The `cleanup` method will drop all tables in the test database and re-import the SQL dump file, or files, between each test.  

For larger setup fixtures this might be a long operation that wastes precious seconds when, say, the only change is the addition of 3 posts, as in this example.

In the suite bootstrap file, e.g. `tests/acceptance/_bootstrap.php`, I add a listener on the `my-plugin-test/setup-posts` event.  
The event will contain information about what post IDs I've set up in the tests and will provide an instance of the database module, in this case the `WPDb` one, that is handling database manipulation in the tests.  
With that information I will remove only the posts I've created in the tests and avoid a full, and costly, clean-up. 

```php
<?php
// In the suite bootstrap file, e.g. `tests/acceptance/_bootstrap.php`.

// Avoid using `cleanup` between tests by just rolling back the posts we've created in tests. 
$prepareForCleanup = static function(tad\WPBrowser\Events\WpbrowserEvent $event) {
    $ids = $event->get('ids', []);
    /** @var \Codeception\Module\WPDb $db */
    $db = $event->get('dbModule', null);

    if($db === null || empty($ids)){
        return;
    }
    
    // When tests are done, then remove all the posts we've created at the start of the test, if any.
    tad\WPBrowser\addListener(Codeception\Events::TEST_END, static function() use($ids,$db) {
        codecept_debug('Removing posts and meta after test...');

        foreach ($ids as $id){
            $db->dontHavePostInDatabase(['ID' => $id], true);
        }
    });
};

tad\WPBrowser\addListener( 'my-plugin-test/setup-posts', $prepareForCleanup);
```

In this simple test I'm adding 3 posts and want to make sure those will be removed, cleaned up, after the test.  
Mirroring the requirement of the clean up function I've defined above, I'm passing the post IDs of the posts I've created and the current instance of the `WPDb` module.

```php
<?php
$I = new AcceptanceTester($scenario);
$I->wantTo('create some posts and see them on the homepage');

$ids = [];
$ids[] = $I->havePostInDatabase(['post_title' => 'Test One']);
$ids[] = $I->havePostInDatabase(['post_title' => 'Test Two']);
$ids[] = $I->havePostInDatabase(['post_title' => 'Test Three']);

tad\WPBrowser\dispatch('my-plugin-test/setup-posts', __FILE__, [
    'ids' => $ids,
    'dbModule' => $this->getModule('WPDb')
]);

$I->amOnPage('/');
$I->seeElement('body.home');
$I->see('Test One');
$I->see('Test Two');
$I->see('Test Three');
```

[1]: https://codeception.com/docs/08-Customization#Events

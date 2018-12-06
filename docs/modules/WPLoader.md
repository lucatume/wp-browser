
### Class: \Codeception\Module\WPLoader

> Class WPLoader Loads a WordPress installation for testing purposes. The class is a Codeception adaptation of WordPress automated testing suite, see [here](http://make.wordpress.org/core/handbook/automated-testing/), and takes care of configuring and installing a WordPress installation. To work properly the \WP_UnitTestCase should be used to run the tests in a PHPUnit-like behaviour.

<table style="width: 100%;">
        <thead>
        <tr>
            <th>Method</th>
            <th>Example</th>
        </tr>
        </thead>
<tr><td><strong>activatePlugins()</strong> : <em>void</em></td><td></td></tr>
<tr><td><strong>bootstrapActions()</strong> : <em>void</em><br /><br /><em>Calls a list of user-defined actions needed in tests.</em></td><td></td></tr>
<tr><td><strong>factory()</strong> : <em>\tad\WPBrowser\Module\WPLoader\FactoryStore</em><br /><br /><em>Accessor method to get the object storing the factories for things. Example usage: $postId = $I->factory()->post->create();</em></td><td></td></tr>
<tr><td><strong>loadPlugins()</strong> : <em>mixed</em><br /><br /><em>Loads the plugins required by the test.</em></td><td></td></tr>
<tr><td><strong>switchTheme()</strong> : <em>void</em></td><td></td></tr></table>

*This class extends \Codeception\Module*


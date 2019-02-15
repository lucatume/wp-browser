<!--doc-->

### Class: \Codeception\Module\WPLoader

> Class WPLoader Loads a WordPress installation for testing purposes. The class is a Codeception adaptation of WordPress automated testing suite, see [here](http://make.wordpress.org/core/handbook/automated-testing/), and takes care of configuring and installing a WordPress installation. To work properly the \WP_UnitTestCase should be used to run the tests in a PHPUnit-like behaviour.

<h3>Methods</h3><nav><ul><li><a href="#activatePlugins">activatePlugins</a></li><li><a href="#bootstrapActions">bootstrapActions</a></li><li><a href="#factory">factory</a></li><li><a href="#loadPlugins">loadPlugins</a></li><li><a href="#switchTheme">switchTheme</a></li></ul></nav><h4 id="activatePlugins">activatePlugins</h4>
- - -

<h4 id="bootstrapActions">bootstrapActions</h4>
- - -
Calls a list of user-defined actions needed in tests.
<h4 id="factory">factory</h4>
- - -
Accessor method to get the object storing the factories for things. Example usage: $postId = $I->factory()->post->create();
<h4 id="loadPlugins">loadPlugins</h4>
- - -
Loads the plugins required by the test.
<h4 id="switchTheme">switchTheme</h4>
- - -</br>

*This class extends \Codeception\Module*

<!--/doc-->

# WPCLI module
This module should be used in acceptance and functional tests to setup, or verify, tests pre and post conditions.  
This module allows invoking any supported [WP-CLI](https://wp-cli.org/) command, refer to the official site for more information.  
The module will use **its own** version of wp-cli, not the one installed in the machine running the tests to grant isolation from local settings.  

## Detecting requests coming from this module 
When it runs this module will set the `WPBROWSER_HOST_REQUEST` environment variable.  
You can detect and use that information to, as an example, use the correct database in your test site `wp-config.php` file:
```php
<?php
if ( 
    // Custom header.
    isset( $_SERVER['HTTP_X_TESTING'] )
    // Custom user agent.
    || ( isset( $_SERVER['HTTP_USER_AGENT'] ) && $_SERVER['HTTP_USER_AGENT'] === 'wp-browser' )
    // The env var set by the WPClIr or WordPress modules.
    || getenv( 'WPBROWSER_HOST_REQUEST' )
) {
    // Use the test database if the request comes from a test.
    define( 'DB_NAME', 'wordpress_test' );
} else {
    // Else use the default one.
    define( 'DB_NAME', 'wordpress' );
}
```

## Configuration

* `path` *required* - the absolute, or relative, path to the WordPress root folder. This will be mapped to the `--path` argument of the wp-cli binary.  
* `throw` - defaults to `true` to throw an exception when a wp-cli command does not return an exit status of `0`; if set to `false` then the exit status of the commands will be returned as is.

### Example configuration
```yaml
modules:
    enabled:
        - WPCLI
    config:
        WPCLI:
            path: /Users/Luca/Sites/wp
            throw: true
```

<!--doc-->


## Public API
<nav>
	<ul>
		<li>
			<a href="#cli">cli</a>
		</li>
		<li>
			<a href="#clitoarray">cliToArray</a>
		</li>
	</ul>
</nav>

<h3>cli</h3>

<hr>

<p>Executes a wp-cli command targeting the test WordPress installation.</p>
<pre><code class="language-php">    // Activate a plugin via wp-cli in the test WordPress site.
    $I-&gt;cli('plugin activate my-plugin');
    // Change a user password.
    $I-&gt;cli('user update luca --user_pass=newpassword');</code></pre>
<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$userCommand</strong> - The string of command and parameters as it would be passed to wp-cli minus <code>wp</code>.</li></ul>
  

<h3>cliToArray</h3>

<hr>

<p>Returns the output of a wp-cli command as an array optionally allowing a callback to process the output.</p>
<pre><code class="language-php">    // Return a list of inactive themes, like ['twentyfourteen', 'twentyfifteen'].
    $inactiveThemes = $I-&gt;cliToArray('theme list --status=inactive --field=name');
    // Get the list of installed plugins and only keep the ones starting with "foo".
    $fooPlugins = $I-&gt;cliToArray('plugin list --field=name', function($output){
         return array_filter(explode(PHP_EOL, $output), function($name){
                 return strpos(trim($name), 'foo') === 0;
         });
    });</code></pre>
<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$userCommand</strong> - The string of command and parameters as it would be passed to wp-cli minus <code>wp</code>.</li>
<li><code>\callable</code> <strong>$splitCallback</strong> - An optional callback function in charge of splitting the results array.</li></ul>


*This class extends \Codeception\Module*

<!--/doc-->

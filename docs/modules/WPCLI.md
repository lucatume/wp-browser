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
* `timeout` - defaults to `60` (seconds) to set each process execution timeout to a certain value; set to `null`, `false` or `0` to disable timeout completely.

Additionally the module configuration will forward any configuration parameter to `wp-cli` as a flag or option.
In the example configuration below the `allow-root` flag and the `some-option` option will be passed to `wp-cli` directly **and prepended to the command as global options**.

> Note: these extract configuration flags and options will be prepended to **all** commands executed by wp-cli!

### Environment configuration

The wp-cli binary supports [a set of enviroment variables to modify its behavior](https://make.wordpress.org/cli/handbook/config/#environment-variables).   

These environment variables can be set on the commands ran by the `WPCLI` module using the optional `env` array in the module configuration.  
The example configuration below shows all of them with some **example** values.  
Most of the times you won't need any of these, but they are there for more fine-grained control over the module operations.  

> The module is not validating the environment variables in any way! Those values will be evaluated by wp-cli at runtime and might generate errors if not correctly configured.

### Example configuration
```yaml
modules:
    enabled:
        - WPCLI
    config:
        WPCLI:
            path: /Users/Luca/Sites/wp
            throw: true
            timeout: 60
            # This will be prepended to the command, `wp --allow-root <command>`.
            allow-root: true
            # This will be prepended to the command, `wp --some-option=some-value <command>`.
            some-option: some-value
            env:
                # Any one of these, if provided, will be set as environment variable for the the cli command process. 
                # See https://make.wordpress.org/cli/handbook/config/#environment-variables for information.
                # Equivalent to `WP_CLI_STRICT_ARGS_MODE=1 wp <command>'.
                strict-args: true
                # Equivalent to `WP_CLI_CACHE_DIR=/tmp/wp-cli-cache wp <command>'.
                cache-dir: '/tmp/wp-cli-cache'
                # Equivalent to `WP_CLI_CONFIG_PATH=/app/public wp <command>'.
                config-path: '/app/public'
                # Equivalent to `WP_CLI_CUSTOM_SHELL=/bin/zsh wp <command>'.
                custom-shell: '/bin/zsh'
                # Equivalent to `WP_CLI_DISABLE_AUTO_CHECK_UPDATE=1 wp <command>'.
                disable-auto-update: true
                # Equivalent to `WP_CLI_PACKAGES_DIR=/wp-cli/packages wp <command>'.
                packages-dir: '/wp-cli/packages'
                # Equivalent to `WP_CLI_PHP=/usr/local/bin/php/7.2/php wp <command>'.
                php: '/usr/local/bin/php/7.2/php'
                # Equivalent to `WP_CLI_PHP_ARGS='foo=bar some=23' wp <command>'.
                php-args: 'foo=bar some=23'
```

<!--doc-->


## Public API
<nav>
	<ul>
		<li>
			<a href="#buildfullcommand">buildFullCommand</a>
		</li>
		<li>
			<a href="#cli">cli</a>
		</li>
		<li>
			<a href="#clitoarray">cliToArray</a>
		</li>
		<li>
			<a href="#clitostring">cliToString</a>
		</li>
		<li>
			<a href="#dontseeinshelloutput">dontSeeInShellOutput</a>
		</li>
		<li>
			<a href="#seeinshelloutput">seeInShellOutput</a>
		</li>
		<li>
			<a href="#seeresultcodeis">seeResultCodeIs</a>
		</li>
		<li>
			<a href="#seeresultcodeisnot">seeResultCodeIsNot</a>
		</li>
		<li>
			<a href="#seeshelloutputmatches">seeShellOutputMatches</a>
		</li>
	</ul>
</nav>

<h3>buildFullCommand</h3>

<hr>

<p>Builds the full command to run including the PHP binary and the wp-cli boot file path.</p>
<pre><code class="language-php">    // This method is defined in the WithWpCli trait.
        // Set the wp-cli path, `$this` is a test case.
    $this-&gt;setUpWpCli( '/var/www/html' );
        // Builds the full wp-cli command, including the `path` variable.
    $fullCommand =  $this-&gt;buildFullCommand(['core', 'version']);
        // The full command can then be used to run it with another process handler.
    $wpCliProcess = new \Symfony\Component\Process\Process($fullCommand);
    $wpCliProcess-&gt;run();</code></pre>
<h4>Parameters</h4>
<ul>
<li><code>array/string</code> <strong>$command</strong> - The command to run.</li></ul>
  

<h3>cli</h3>

<hr>

<p>Executes a wp-cli command targeting the test WordPress installation. For back-compatibility purposes you can still pass the commandline as a string, but the array format is the preferred and supported method.</p>
<pre><code class="language-php">    // Activate a plugin via wp-cli in the test WordPress site.
    $I-&gt;cli(['plugin', 'activate', 'my-plugin']);
    // Change a user password.
    $I-&gt;cli(['user', 'update', 'luca', '--user_pass=newpassword']);</code></pre>
<h4>Parameters</h4>
<ul>
<li><code>string/string/array</code> <strong>$userCommand</strong> - The string of command and parameters as it would be passed to wp-cli minus <code>wp</code>.</li></ul>
  

<h3>cliToArray</h3>

<hr>

<p>Returns the output of a wp-cli command as an array optionally allowing a callback to process the output. <code>wp</code>. For back-compatibility purposes you can still pass the commandline as a string, but the array format is the preferred and supported method.</p>
<pre><code class="language-php">    // Return a list of inactive themes, like ['twentyfourteen', 'twentyfifteen'].
    $inactiveThemes = $I-&gt;cliToArray(['theme', 'list', '--status=inactive', '--field=name']);
    // Get the list of installed plugins and only keep the ones starting with "foo".
    $fooPlugins = $I-&gt;cliToArray(['plugin', 'list', '--field=name'], function($output){
         return array_filter(explode(PHP_EOL, $output), function($name){
                 return strpos(trim($name), 'foo') === 0;
         });
    });</code></pre>
<h4>Parameters</h4>
<ul>
<li><code>string/string/array</code> <strong>$userCommand</strong> - The string of command and parameters as it would be passed to wp-cli minus</li>
<li><code>\callable</code> <strong>$splitCallback</strong> - An optional callback function in charge of splitting the results array.</li></ul>
  

<h3>cliToString</h3>

<hr>

<p>Returns the output of a wp-cli command as a string. For back-compatibility purposes you can still pass the commandline as a string, but the array format is the preferred and supported method.</p>
<pre><code class="language-php">    // Return the current site administrator email, using string command format.
    $adminEmail = $I-&gt;cliToString('option get admin_email');
    // Get the list of active plugins in JSON format.
    $activePlugins = $I-&gt;cliToString(['wp','option','get','active_plugins','--format=json']);</code></pre>
<h4>Parameters</h4>
<ul>
<li><code>string/array</code> <strong>$userCommand</strong> - The string of command and parameters as it would be passed to wp-cli minus <code>wp</code>.</li></ul>
  

<h3>dontSeeInShellOutput</h3>

<hr>

<p>Checks that output from last command doesn't contain text.</p>
<pre><code class="language-php">    // Return the current site administrator email, using string command format.
    $I-&gt;cli('plugin list --status=active');
    $I-&gt;dontSeeInShellOutput('my-inactive/plugin.php');</code></pre>
<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$text</strong> - The text to assert is not in the output.</li></ul>
  

<h3>seeInShellOutput</h3>

<hr>

<p>Checks that output from last command contains text.</p>
<pre><code class="language-php">
    // Return the current site administrator email, using string command format.
    $I-&gt;cli('option get admin_email');</code></pre>
<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$text</strong> - The text to assert is in the output.</li></ul>
  

<h3>seeResultCodeIs</h3>

<hr>

<p>Checks the result code from the last command.</p>
<pre><code class="language-php">    // Return the current site administrator email, using string command format.
    $I-&gt;cli('option get admin_email');
    $I-&gt;seeResultCodeIs(0);</code></pre>
<h4>Parameters</h4>
<ul>
<li><code>int</code> <strong>$code</strong> - The desired result code.</li></ul>
  

<h3>seeResultCodeIsNot</h3>

<hr>

<p>Checks the result code from the last command.</p>
<pre><code class="language-php">    // Return the current site administrator email, using string command format.
    $I-&gt;cli('invalid command');
    $I-&gt;seeResultCodeIsNot(0);</code></pre>
<h4>Parameters</h4>
<ul>
<li><code>int</code> <strong>$code</strong> - The result code the command should not have exited with.</li></ul>
  

<h3>seeShellOutputMatches</h3>

<hr>

<p>Checks that output from the last command matches a given regular expression.</p>
<pre><code class="language-php">
    // Return the current site administrator email, using string command format.
    $I-&gt;cli('option get admin_email');</code></pre>
<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$regex</strong> - The regex pattern, including delimiters, to assert the output matches against.</li></ul>


*This class extends \Codeception\Module*

<!--/doc-->

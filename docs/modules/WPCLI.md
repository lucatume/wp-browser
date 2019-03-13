# WPCLI module
This module should be used in acceptance and functional tests to setup, or verify, tests pre and post conditions.  
This module allows invoking any supported [WP-CLI](https://wp-cli.org/) command, refer to the official site for more information.  
The module will use **its own** version of wp-cli, not the one installed in the machine running the tests to grant isolation from local settings.  

## Configuration

* `path` *required* - the absolute, or relative, path to the WordPress root folder. This will be mapped to the `--path` argument of the wp-cli binary.  
* `throw` - defaults to `true` to throw an exception when a wp-cli command does not return an exit status of `0`; if set to `false` then the exit status of the commands will be returned as is.
<!--doc-->


<h2>Public API</h2>
<nav>
	<ul>
		<li>
			<a href="#cli">cli</a>
		</li>
		<li>
			<a href="#cliToArray">cliToArray</a>
		</li>
	</ul>
</nav>

<h4 id="cli">cli</h4>

***

Executes a wp-cli command targeting the test WordPress installation.
<pre><code class="language-php">    // Activate a plugin via wp-cli in the test WordPress site.
    $I-&gt;cli('plugin activate my-plugin');
    // Change a user password.
    $I-&gt;cli('user update luca --user_pass=newpassword');</code></pre>
<h5>Parameters</h5><ul>
<li><em>string</em> <strong>$userCommand</strong> = <em>`'core version'`</em> - The string of command and parameters as it would be passed to wp-cli minus <code>wp</code>.</li></ul>
<h4 id="cliToArray">cliToArray</h4>

***

Returns the output of a wp-cli command as an array.
<pre><code class="language-php">    // Return a list of inactive themes, like ['twentyfourteen', 'twentyfifteen'].
    $inactiveThemes = $I-&gt;cliToArray('theme list --status=inactive --field=name');
    // Get the list of installed plugins and only keep the ones starting with "foo".
    $fooPlugins = $I-&gt;cliToArray('plugin list --field=name', function($output){
         return array_filter(explode(PHP_EOL, $output), function($name){
                 return strpos(trim($name), 'foo') === 0;
         });
    });</code></pre>
<h5>Parameters</h5><ul>
<li><em>string</em> <strong>$userCommand</strong> = <em>`'post list --format=ids'`</em> - The string of command and parameters as it would be passed to wp-cli minus <code>wp</code>.</li>
<li><em>\callable</em> <strong>$splitCallback</strong> = <em>null</em> - An optional callback function in charge of splitting the results array.</li></ul>
</br>

*This class extends \Codeception\Module*

<!--/doc-->

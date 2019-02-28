# WPCLI module
This module should be used in acceptance and functional tests to setup, or verify, tests pre and post conditions.  
This module allows invoking any supported [WP-CLI](https://wp-cli.org/) command, refer to the official site for more information.  
The module will use **its own** version of wp-cli, not the one installed in the machine running the tests to grant isolation from local settings.  
<!--doc-->


<h2>Public API</h2><nav><ul><li><a href="#cli">cli</a></li><li><a href="#cliToArray">cliToArray</a></li></ul></nav><h4 id="cli">cli</h4>
- - -
Executes a wp-cli command. The method is a wrapper around isolated calls to the wp-cli tool. The library will use its own wp-cli version to run the commands. e.g. a terminal call like `wp core version` becomes `core version` omitting the call to wp-cli script.
<h5>Parameters</h5><ul>
<li><em>string</em> <strong>$userCommand</strong> = <em>`'core version'`</em> - The string of command and parameters as it would be passed to wp-cli</li></ul>
<h4 id="cliToArray">cliToArray</h4>
- - -
Returns the output of a wp-cli command as an array. This method should be used in conjunction with wp-cli commands that will return lists. E.g. $inactiveThemes = $I->cliToArray('theme list --status=inactive --field=name'); The above command could return an array like ['twentyfourteen', 'twentyfifteen'] No check will be made on the command the user inserted for coherency with a split-able output.
<h5>Parameters</h5><ul>
<li><em>string</em> <strong>$userCommand</strong> = <em>`'post list --format=ids'`</em></li>
<li><em>\callable</em> <strong>$splitCallback</strong> = <em>null</em> - A optional callback function in charge of splitting the results array.</li></ul></br>

*This class extends \Codeception\Module*

<!--/doc-->


### Class: \Codeception\Module\WPCLI

> Class WPCLI Wraps calls to the wp-cli tool.

<table style="width: 100%;">
        <thead>
        <tr>
            <th>Method</th>
            <th>Example</th>
        </tr>
        </thead>
<tr><td><strong>cli(</strong><em>string</em> <strong>$userCommand=`'core version'`</strong>)</strong> : <em>int wp-cli exit value for the command</em><br /><br /><em>Executes a wp-cli command. The method is a wrapper around isolated calls to the wp-cli tool. The library will use its own wp-cli version to run the commands. e.g. a terminal call like `wp core version` becomes `core version` omitting the call to wp-cli script.</em><p><strong>Parameters:</strong><ul>string <strong>$userCommand</strong>: The string of command and parameters as it would be passed to wp-cli</ul></p></td><td></td></tr>
<tr><td><strong>cliToArray(</strong><em>string</em> <strong>$userCommand=`'post list --format=ids'`</strong>, <em>\callable</em> <strong>$splitCallback=null</strong>)</strong> : <em>array An array containing the output of wp-cli split into single elements.</em><br /><br /><em>Returns the output of a wp-cli command as an array. This method should be used in conjunction with wp-cli commands that will return lists. E.g. $inactiveThemes = $I->cliToArray('theme list --status=inactive --field=name'); The above command could return an array like ['twentyfourteen', 'twentyfifteen'] No check will be made on the command the user inserted for coherency with a split-able output.</em><p><strong>Parameters:</strong><ul>string <strong>$userCommand</strong>: 
\callable <strong>$splitCallback</strong>: A optional callback function in charge of splitting the results array.</ul></p></td><td></td></tr></table>

*This class extends \Codeception\Module*


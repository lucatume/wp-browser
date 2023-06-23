##  Migrating projects from version 2 of wp-browser to version 3

Version 3 of wp-browser removed, to allow for broader compatibility with PHP and Composer versions, some of its 
dependencies and modified some of its methods.  
Here is a list of changes and the suggested courses of action:

* Removed `symfony/process` to launch and manage external processes; re-add it your project development 
requirements using `composer require --dev symfony/process`.
* Removed the `wp-cli/wp-cli-bundle` dependency; if you were relying on non-core
 packages, then re-add it to your project development requirements using `composer require --dev wp-cli/wp-cli-bundle`.  
* Removed the `WithWpCli::executeBackgroundWpCliCommand` trait method, and, as a consequence, the 
`WPCLI::executeBackgroundWpCliCommand` module method; you could have used the latter, if this was the case, then 
require the `symfony/process` as explained above and launch processes in background using its API; [find out more][1].
* Refactored the `WPCLI` module to build and escape string command lines differently; the handling of command-line arguments
for the `WPCLI` module has been modified to make it a bit more consistent and robust; as a consequence, you might experience
some breakages in string commands that used to work correctly before; should this be the case then either modify
your code ot provide the command in array format (taking care of the correct escaping in your code), or make sure to 
pass a correctly structured command string to the `WPCLI` module.

[1]: https://symfony.com/doc/current/components/process.html

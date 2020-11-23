##  Migrating projects from version 2 of wp-browser to version 3

- Removed the `WithWpCli::executeBackgroundWpCliCommand` method, and, as a consequence, the `WPCLI::executeBackgroundWpCliCommand` method.
- Removed the `symfony/process` dependency and replaced it with the `mikehaertl/php-shellcommand` one; refactor methods that use and accept shell commands.
- Refactor the `WPCLI` module to build and escape string command lines differently.
- Removed the `wp-cli/wp-cli-bundle` dependency and replaced it with the `wp-cli/wp-cli` one.

[1mNAME[0m

  wp

[1mDESCRIPTION[0m

  Manage WordPress through the command-line.

[1mSYNOPSIS[0m

  wp <command>

[1mSUBCOMMANDS[0m

  cache                 Adds, removes, fetches, and flushes the WP Object Cache object.
  cap                   Adds, removes, and lists capabilities of a user role.
  cli                   Reviews current WP-CLI info, checks for updates, or views defined aliases.
  comment               Creates, updates, deletes, and moderates comments.
  config                Generates and reads the wp-config.php file.
  core                  Downloads, installs, updates, and manages a WordPress installation.
  cron                  Tests, runs, and deletes WP-Cron events; manages WP-Cron schedules.
  db                    Performs basic database operations using credentials stored in wp-config.php.
  embed                 Inspects oEmbed providers, clears embed cache, and more.
  eval                  Executes arbitrary PHP code.
  eval-file             Loads and executes a PHP file.
  export                Exports WordPress content to a WXR file.
  help                  Gets help on WP-CLI, or on a specific command.
  i18n                  Provides internationalization tools for WordPress projects.
  import                Imports content from a given WXR file.
  language              Installs, activates, and manages language packs.
  maintenance-mode      Activates, deactivates or checks the status of the maintenance mode of a site.
  media                 Imports files as attachments, regenerates thumbnails, or lists registered image sizes.
  menu                  Lists, creates, assigns, and deletes the active theme's navigation menus.
  network               Perform network-wide operations.
  option                Retrieves and sets site options, including plugin and WordPress settings.
  package               Lists, installs, and removes WP-CLI packages.
  plugin                Manages plugins, including installs, activations, and updates.
  post                  Manages posts, content, and meta.
  post-type             Retrieves details on the site's registered post types.
  rewrite               Lists or flushes the site's rewrite rules, updates the permalink structure.
  role                  Manages user roles, including creating new roles and resetting to defaults.
  scaffold              Generates code for post types, taxonomies, plugins, child themes, etc.
  search-replace        Searches/replaces strings in the database.
  server                Launches PHP's built-in web server for a specific WordPress installation.
  shell                 Opens an interactive PHP console for running and testing PHP code.
  sidebar               Lists registered sidebars.
  site                  Creates, deletes, empties, moderates, and lists one or more sites on a multisite installation.
  super-admin           Lists, adds, or removes super admin users on a multisite installation.
  taxonomy              Retrieves information about registered taxonomies.
  term                  Manages taxonomy terms and term meta, with create, delete, and list commands.
  theme                 Manages themes, including installs, activations, and updates.
  transient             Adds, gets, and deletes entries in the WordPress Transient Cache.
  user                  Manages users, along with their roles, capabilities, and meta.
  widget                Manages widgets, including adding and moving them within sidebars.



[1mGLOBAL PARAMETERS[0m

  --path=<path>
      Path to the WordPress files.

  --url=<url>
      Pretend request came from given URL. In multisite, this argument is how the target site is specified.

  --ssh=[<scheme>:][<user>@]<host|container>[:<port>][<path>]
      Perform operation against a remote server over SSH (or a container using scheme of "docker", "docker-compose", "docker-compose-run", "vagrant").

  --http=<http>
      Perform operation against a remote WordPress installation over HTTP.

  --user=<id|login|email>
      Set the WordPress user.

  --skip-plugins[=<plugins>]
      Skip loading all plugins, or a comma-separated list of plugins. Note: mu-plugins are still loaded.

  --skip-themes[=<themes>]
      Skip loading all themes, or a comma-separated list of themes.

  --skip-packages
      Skip loading all installed packages.

  --require=<path>
      Load PHP file before running the command (may be used more than once).

  --exec=<php-code>
      Execute PHP code before running the command (may be used more than once).

  --context=<context>
      Load WordPress in a given context.

  --[no-]color
      Whether to colorize the output.

  --debug[=<group>]
      Show all PHP errors and add verbosity to WP-CLI output. Built-in groups include: bootstrap, commandfactory, and help.

  --prompt[=<assoc>]
      Prompt the user to enter values for all command arguments, or a subset specified as comma-separated values.

  --quiet
      Suppress informational messages.

  Run 'wp help <command>' to get more information on a specific command.


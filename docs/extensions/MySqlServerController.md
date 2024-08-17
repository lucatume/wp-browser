This extension will start and stop the MySQL server before and after the tests run.  
The extension will take care of downloading the MySQL Community Server archive from
the [MySQL Community Server](https://dev.mysql.com/downloads/mysql/) site, place it in the `_mysql-server`
directory under the codeception output directory and initialize the server in the same directory.

The aim of this extension is to allow running integration tests against a real MySQL server, without having to
install and configure a MySQL server on the machine.

!!! warning

    Currently the MySQL Community Server version installed by this extension (8.4.2 LTS) **is not available for Windows on ARM.**  

    If you are running Windows on ARM, you can either:  
    - Use a custom binary, see the [configuration examples](#configuration-examples) below  
    - Use the [Docker controller extension](DockerComposeController.md) to run the database from a Docker container.

### Configuration

The extension can be configured with the following parameters:

* required
    * `port` - the localhost port to use for the MySQL server, defaults to `8906`.
    * `database` - the database that will be created when starting the server, defaults to `wordpress`.
    * `user` - the user that will be created when starting the server, defaults to `wordpress`. The user will be granted
      all privileges on the database specified by the `database` parameter. If the user is `root`, no further user will
      be created.
    * `password` - the password to use for the user specified by the `user` parameter, defaults to `wordpress`. If the
      user is `root`, the root user will be set to the password specified by this parameter.
* optional
    * `suites` - an array of Codeception suites to run the server for; if not set the server will be started for all the
      suites.
    * `binary` - the path to the MySQL server binary to use, defaults to `mysqld`, defaults to `null` to download and
      initialize the correct version of MySQL server for the current platform and architecture.
    * `shareDir` - the path to the directory to use for the MySQL server share, defaults to `null`. **This is required
      when providing a custom binary**.

### Configuration Examples

Example configuration starting the server for all suites:

```yaml
extensions:
  enabled:
    - "lucatume\WPBrowser\Extension\MySqlServerController":
      port: 8906
      database: wordpress
      user: wordpress
      password: wordpress
```

The extension can access environment variables defined in the tests configuration file:

```yaml
extensions:
  enabled:
    - "lucatume\WPBrowser\Extension\MySqlServerController":
      port: '%MYSQL_SERVER_PORT%'
      database: '%MYSQL_SERVER_DATABASE%'
      user: '%MYSQL_SERVER_USER%'
      password: '%MYSQL_SERVER_PASSWORD%'
```

Example configuration using the `root` user:

```yaml
extensions:
  enabled:
    - "lucatume\WPBrowser\Extension\MySqlServerController":
      port: 33446
      database: wordpress
      user: root
      password: secret
```

### Using a custom MySQL server binary

The extension can be configured to use a custom MySQL server binary by setting the `binary` configuration parameter to
the absolute path to the binary:

```yaml
extensions:
  enabled:
    - "lucatume\WPBrowser\Extension\MySqlServerController":
      port: 33446
      database: wordpress
      user: root
      password: secret
      binary: /usr/local/mysql/bin/mysqld
```

### This is a service extension

This is a service extension that will be started and stopped by [the `dev:start`](../commands.md#devstart)
and [`dev:stop`](../commands.md#devstop) commands.

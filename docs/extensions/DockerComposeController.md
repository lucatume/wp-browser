## DockerCompose Controller Extension

This extension will start and stop [a `docker compose` stack][1] before and after the tests are run.

### Configuration

The extension can be configured with the following parameters:

* required
    * `compose-file` - the path to the `docker compose` file to use; it can be either an absolute path or a path
      relative to the Codeception root directory.
* optional
    * `env-file`- the path to the environment file to use; it can be either an absolute path or a path.

### Configuration Examples

Example configuration starting the server for all suites:

```yaml
extensions:
  enabled:
    - "lucatume\\WPBrowser\\Extension\\DockerComposeController"
  config:
    "lucatume\\WPBrowser\\Extension\\DockerComposeController":
      compose-file: /var/www/html/docker-compose.yml
      env-file: /var/www/html/.env
```

The extension can access environment variables defined in the tests configuration file:

```yaml
extensions:
  enabled:
    - "lucatume\\WPBrowser\\Extension\\DockerComposeController"
  config:
    "lucatume\\WPBrowser\\Extension\\DockerComposeController":
      compose-file: '%DOCKER_COMPOSE_FILE%'
      env-file: '%DOCKER_COMPOSE_ENV_FILE%'
```

### This is a service extension

This is a service extension that will be started and stopped by [the `dev:start`](commands.md#devstart)
and [`wp:dev-stop`](commands.md#devstop) commands.

[1]: https://docs.docker.com

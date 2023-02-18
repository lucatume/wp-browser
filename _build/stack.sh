#!/usr/bin/env bash

function print_help() {
  echo "Usage: $0 [-p PHP_VERSION] [-c CODECEPTION_VERSION] [-d] [-h] [--] [COMMAND]"
  echo "  -p<PHP_VERSION>         The PHP version to use. Default: 5.6"
  echo "  -<CODECEPTION_VERSION> The Codeception version to use. Default: 4"
  echo "  -d                      Enable debug mode."
  echo "  -h                      Display this help message."
  echo "COMMAND can be one of:"
  echo "  build            Build the images for the specified PHP version."
  echo "  clean            Remove the containers and images, remove the wordpress installation."
  echo "  composer_update  Update the composer dependencies for the specified PHP and Codeception version."
  echo "  config           Show the docker-compose configuration for the specified PHP version."
  echo "  exec             Execute a command in the WordPress/php container for the specified PHP version."
  echo "  logs             Show the logs of the containers for the specified PHP version."
  echo "  ps               Show the status of the containers for the specified PHP version."
  echo "  ssh              SSH into the WordPress/php container for the specified PHP version."
  echo "  xdebug-on        Activates XDebug in the WordPress/php container."
  echo "  xdebug-off       Deactivates XDebug in the WordPress/php container."
}

# Parse the arguments using getopts
while getopts "p:c:hd" opt; do
  case $opt in
  p)
    PHP_VERSION=$OPTARG
    ;;
  c)
    CODECEPTION_VERSION=$OPTARG
    ;;
  d)
    set -x
    ;;
  h)
    print_help
    exit 0
    ;;
  \?)
    echo "Invalid option: -$OPTARG" >&2
    print_help
    exit 1
    ;;
  :)
    echo "Option -$OPTARG requires an argument." >&2
    print_help
    exit 1
    ;;
  esac
done

shift $((OPTIND - 1))

[ "${1:-}" = "--" ] && shift

SUPPORTED_PHP_VERSIONS=(5.6 7.0 7.1 7.2 7.3 7.4 8.0 8.1)
PHP_VERSION=${PHP_VERSION:-5.6}
# If the PHP version is not supported, exit with an error.
if [[ ! " ${SUPPORTED_PHP_VERSIONS[@]} " =~ " ${PHP_VERSION} " ]]; then
  echo "PHP version ${PHP_VERSION} is not supported."
  echo "Supported PHP versions: ${SUPPORTED_PHP_VERSIONS[@]}"
  exit 1
fi

SUPPORTED_CODECEPTION_VERSIONS=(2 3 4)
CODECEPTION_VERSION=${CODECEPTION_VERSION:-4}
# If the Codeception version is not supported, exit with an error.
if [[ ! " ${SUPPORTED_CODECEPTION_VERSIONS[@]} " =~ " ${CODECEPTION_VERSION} " ]]; then
  echo "Codeception version ${CODECEPTION_VERSION} is not supported."
  echo "Supported Codeception versions: ${SUPPORTED_CODECEPTION_VERSIONS[@]}"
  exit 1
fi

TEST_DATABASES=(wordpress test_subdir test_subdomain test_empty)

export PHP_VERSION=${PHP_VERSION:-5.6}
export CODECEPTION_VERSION=${CODECEPTION_VERSION:-4}
export USER_UID=$(id -u)
export USER_GID=$(id -g)
export USER_NAME=$(id -un)
export PWD="$(pwd)"

function ensure_twentytwenty_theme() {
  if [ -d "$(pwd)/_wordpress/wp-content/themes/twentytwenty" ]; then
    return
  fi

  # Download and install the twentytwenty theme.
  curl -sSL https://downloads.wordpress.org/theme/twentytwenty.2.1.zip -o "$(pwd)/_wordpress/wp-content/themes/twentytwenty.zip" || exit 1
  unzip -q "$(pwd)/_wordpress/wp-content/themes/twentytwenty.zip" -d "$(pwd)/_wordpress/wp-content/themes/" || exit 1
}

function ensure_test_databases() {
  # Create the test databases, use a for loop and exit on failure.
  for database in "${TEST_DATABASES[@]}"; do
    docker compose exec -T database mysql -uroot -ppassword -e "CREATE DATABASE IF NOT EXISTS ${database}" || exit 1
  done
}

function ensure_wordpress_scaffolded() {
  if [ ! -d _wordpress ]; then
    mkdir -p _wordpress || exit 1
  fi

  if [ ! -f _build/wordpress-latest.tar.gz ]; then
    # Download WordPress latest version.
    if ! curl -s -o _build/wordpress-latest.tar.gz https://wordpress.org/latest.tar.gz; then
      echo "Failed to download the latest WordPress version."
      exit 1
    fi
  fi

  # If _wordpress/the wp-config-sample.php file is not found, unzip the latest WordPress version.
  if [ ! -f _wordpress/wp-config-sample.php ]; then
    if ! tar -xzf _build/wordpress-latest.tar.gz -C _wordpress --strip-components=1; then
      echo "Failed to unzip the latest WordPress version."
      exit 1
    fi
  fi
}

function run_wp_cli_command() {
  docker run --rm -v "$(pwd)/_wordpress:/var/www/html" \
    --network "wpbrowser_php_${PHP_VERSION}" \
    -w /var/www/html \
    -u "${USER_UID}:${USER_GID}" \
    "wp-browser-wordpress:php${PHP_VERSION}-apache" \
    wp --allow-root --url=http://wordpress.test --path=/var/www/html "$@"
}

function ensure_wordpress_configured() {
  if [ -f _wordpress/wp-config.php ]; then
    return
  fi

  # If the _wordpress/wp-config.php file is not found, configure WordPress using wp-cli.
  # Configure WordPress using wp-cli.
  if ! docker run --rm -v "$(pwd)/_wordpress:/var/www/html" \
    --network "wpbrowser_php_${PHP_VERSION}" \
    -w /var/www/html \
    -u "${USER_UID}:${USER_GID}" \
    "wp-browser-wordpress:php${PHP_VERSION}-apache" \
    bash -c "wp --allow-root core config --dbname=wordpress --dbuser=root --dbpass=password --dbhost=database --dbprefix=wp_ --extra-php <<PHP
define( 'WP_DEBUG', true );
define( 'WP_DEBUG_LOG', true );
define( 'WP_DEBUG_DISPLAY', false );
define( 'DISABLE_CRON', true );
define( 'WP_HTTP_BLOCK_EXTERNAL', true );
PHP"; then
    echo "Failed to configure WordPress."
    exit 1
  fi
}

function ensure_wordpress_installed() {
  # If WordPress is already installed, then return. Use wp-cli to check if WordPress is installed.
  if run_wp_cli_command core is-installed; then
    return
  fi

  # Install WordPress using wp-cli.
  if ! run_wp_cli_command core install --url=http://wordpress.test --title="TEST" --admin_user=admin --admin_password=password --admin_email=admin@example.com --skip-email; then
    echo "Failed to install WordPress."
    exit 1
  fi

  # Convert the site to multisite using wp-cli.
  if ! run_wp_cli_command core multisite-convert; then
    echo "Failed to convert the site to multisite."
    exit 1
  fi
}

function build() {
  [ ! -d "$(pwd)/.cache/composer" ] && mkdir -p "$(pwd)/.cache/composer"
  docker compose pull --ignore-buildable || exit 1
  docker compose build wordpress || exit 1
  docker compose up -d --force-recreate --wait database || exit 1
  ensure_wordpress_scaffolded
  ensure_wordpress_configured
  ensure_wordpress_installed
  docker compose up -d --wait wordpress
  docker compose up -d --wait chrome
  ensure_test_databases
  ensure_twentytwenty_theme
}

function clean() {
  pre_clean_php_version=${PHP_VERSION}
  # Foreach supported PHP version, remove the containers and images.
  for PHP_VERSION in "${SUPPORTED_PHP_VERSIONS[@]}"; do
    docker compose down -v
    docker compose rm -f
    docker rmi "wp-browser-wordpress:php${PHP_VERSION-apache}"
  done

  rm -rf _wordpress
  export PHP_VERSION=${pre_clean_php_version}
}

function config() {
  # Show the docker-compse configuration for the required PHP version.
  docker compose config
}

function composer_update() {
  # If the Codeception version is 4, then use the composer.codecept-4.json file.
  if [ "${CODECEPTION_VERSION}" == 4 ]; then
    composer_file="composer.codecept-4.json"
  else
    composer_file="composer.json"
  fi

  docker compose exec -u "$(id -u):$(id -g)" -w "$(pwd)" \
    wordpress bash -c "COMPOSER=$composer_file composer update --with codeception/codeception:^${CODECEPTION_VERSION}.0"

}

function run_tests() {
  ensure_test_databases
  xdebug_off
  suites=$(find "$(pwd)/tests" -name '*.suite.dist.yml' -print0 | xargs -0 -n1 basename | cut -d. -f1)
  for suite in $suites; do
    echo ""
    echo "Running tests for suite $suite ... "
    echo "=============================================================================="
    docker compose exec -u "$(id -u):$(id -g)" -w "$(pwd)" \
      wordpress bash -c "vendor/bin/codecept run $suite --ext DotReporter" || exit 1
  done
}

function xdebug_off() {
  docker compose exec -u "$(id -u):$(id -g)" -w "$(pwd)" wordpress bash xdebug-off || exit 1
}

function xdebug_on() {
  docker compose exec -u "$(id -u):$(id -g)" -w "$(pwd)" wordpress bash xdebug-on || exit 1
}

COMMAND=${1:-help}

case $COMMAND in
build)
  build
  ;;
clean)
  clean
  ;;
composer_update)
  composer_update
  ;;
config)
  config
  ;;
exec)
  docker compose exec -u "$(id -u):$(id -g)" -it -w "$(pwd)" wordpress "${@:2}"
  ;;
help)
  print_help
  ;;
logs)
  docker compose logs -f
  ;;
ps)
  docker compose ps
  ;;
ssh)
  docker compose exec -u "$(id -u):$(id -g)" -it -w "$(pwd)" wordpress bash
  ;;
test)
  run_tests
  ;;
xdebug-off)
  xdebug_off
  ;;
xdebug-on)
  xdebug_on
  ;;
*)
  echo "Unknown command: ${COMMAND}"
  print_help
  ;;
esac

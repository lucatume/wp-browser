#!/usr/bin/env sh

SUPPORTED_PHP_VERSIONS=(5.6 7.0 7.1 7.2 7.3 7.4 8.0 8.1)

function setup_docker_compose_env() {
  # The first argument is the PHP version, if not provided use `5.6`.
  PHP_VERSION=${1:-5.6}

  # If the PHP version is not supported, exit with an error.
  if [[ ! " ${SUPPORTED_PHP_VERSIONS[@]} " =~ " ${PHP_VERSION} " ]]; then
    echo "PHP version ${PHP_VERSION} is not supported."
    echo "Supported PHP versions: ${SUPPORTED_PHP_VERSIONS[@]}"
    exit 1
  fi

  export USER_UID=$(id -u)
  export USER_GID=$(id -g)
  export USER_NAME=$(id -un)
  export PWD=$(pwd)
}

function build() {

  setup_docker_compose_env

  # Build the PHP container for the required PHP version using docker-compose.
  docker-compose build --build-arg PHP_VERSION=${PHP_VERSION} wordpress

  # Start the database container and wait for it to be healthy.
  docker-compose up -d --wait database

  if [ ! -d _wordpress ]; then
    mkdir -p _wordpress
  fi

  if [ ! -f _build/wordpress-latest.tar.gz ]; then
    # Download WordPress latest version.
    curl -s -o _build/wordpress-latest.tar.gz https://wordpress.org/latest.tar.gz
  fi

  # If _wordpress/the wp-config-sample.php file is not found, unzip the latest WordPress version.
  if [ ! -f _wordpress/wp-config-sample.php ]; then
    tar -xzf _build/wordpress-latest.tar.gz -C _wordpress --strip-components=1
  fi

  # If the _wordpress/wp-config.php file is not found, configure WordPress using wp-cli.
  if [ ! -f _wordpress/wp-config.php ]; then
    # Configure WordPress using wp-cli.
    docker run --rm -v $(pwd)/_wordpress:/var/www/html \
      --network wpbrowser_php_${PHP_VERSION} \
      -w /var/www/html wp-browser-wordpress:php${PHP_VERSION}-apache \
      bash -c "wp --allow-root core config --dbname=wordpress --dbuser=wordpress --dbpass=wordpress --dbhost=database --dbprefix=wp_ --extra-php <<PHP
define( 'WP_DEBUG', true );
define( 'WP_DEBUG_LOG', true );
define( 'WP_DEBUG_DISPLAY', false );
define( 'DISABLE_CRON', true );
define( 'WP_HTTP_BLOCK_EXTERNAL', true);
PHP"

    # If the configuration failed, then exit.
    if [ $? -ne 0 ]; then
      echo "Failed to configure WordPress."
      exit 1
    fi

    # Install WordPress using wp-cli.
    docker run --rm -v $(pwd)/_wordpress:/var/www/html \
      --network wpbrowser_php_${PHP_VERSION} \
      -w /var/www/html wp-browser-wordpress:php${PHP_VERSION}-apache \
      wp --allow-root core install --url=http://wordpress.test --title="TEST" --admin_user=admin --admin_password=password --admin_email=admin@example.com --skip-email

    # If the installation failed, then exit.
    if [ $? -ne 0 ]; then
      echo "Failed to install WordPress."
      exit 1
    fi

    # Convert the site to multisite using wp-cli.
    docker run --rm -v $(pwd)/_wordpress:/var/www/html \
      --network wpbrowser_php_${PHP_VERSION} \
      -w /var/www/html wp-browser-wordpress:php${PHP_VERSION}-apache \
      wp --allow-root core multisite-convert

    # If the conversion failed, then exit.
    if [ $? -ne 0 ]; then
      echo "Failed to convert the site to multisite."
      exit 1
    fi
  fi

  docker-compose up -d --wait wordpress
  docker-compose up -d --wait chrome

  # Create the test databases, use a for loop and exit on failure.
  for DB in test_subdir test_subdomain test_empty; do
    docker-compose exec -T database mysql -uroot -ppassword -e "CREATE DATABASE IF NOT EXISTS ${DB}"
    if [ $? -ne 0 ]; then
      echo "Failed to create the ${DB} database."
      exit 1
    fi
  done
}

function clean() {
  # Foreach supported PHP version, remove the containers and images.
  for PHP_VERSION in "${SUPPORTED_PHP_VERSIONS[@]}"; do
    setup_docker_compose_env ${PHP_VERSION}

    docker-compose down -v
    docker-compose rm -f
    docker rmi wp-browser-wordpress:php${PHP_VERSION-apache}
  done

  rm -rf _wordpress
}

function config() {
  setup_docker_compose_env

  # Show the docker-compse configuration for the required PHP version.
  docker-compose config
}

function print_help() {
  echo "Usage: stack.sh COMMAND [ARGUMENTS]"
  echo ""
  echo "Commands:"
  echo "  build [PHP_VERSION]  Build the containers for the specified PHP version."
  echo "  config [PHP_VERSION] Show the docker-compose configuration for the required PHP version."
  echo "  help                 Show this help message."
}

# The first argument is the sub-command, if not provide, use `help`.
COMMAND=${1:-help}

# Depending on the sub-command, execute the corresponding function.
case $COMMAND in
help)
  print_help
  ;;
build)
  build $2
  ;;
clean)
  clean
  ;;
config)
  config $2
  ;;
logs)
  setup_docker_compose_env $2
  docker-compose logs -f
  ;;
ssh)
  setup_docker_compose_env $2
  docker-compose exec -u "$(id -u):$(id -g)" -it -w "$(pwd)" wordpress bash
  ;;
*)
  echo "Unknown command: ${COMMAND}"
  print_help
  ;;
esac

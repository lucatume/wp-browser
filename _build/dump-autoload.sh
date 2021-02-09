#!/usr/bin/env bash

if [ "$#" -lt 1 ]; then
  echo -e "\033[1mRegenerates Composer autoload file for a specific PHP version.\033[0m"
  echo ""
  echo -e "\033[32mUsage:\033[0m"
  echo "  dump-autoload.sh <php_version>"
  echo ""
  echo -e "\033[32mExamples:\033[0m"
  echo ""
  echo "  Dump Composer autoload for PHP version 5.6"
  echo -e "  \033[36mdump-autoload.sh 5.6 \033[0m"
  exit 0
fi

php_version="$1"

if [ -z "${php_version}" ]; then
  echo "PHP Version is required, please specify one."
  exit 1;
fi

docker run --rm \
  --user "$(id -u):$(id -g)" \
  -e FIXUID=1 \
  -v "${HOME}/.composer/auth.json:/composer/auth.json" \
  -v "${PWD}:/project" \
  -t \
  lucatume/composer:php"${php_version}" dump-autoload

test -f "${PWD}/vendor/autoload.php" || { echo "${PWD}/vendor/autoload.php file not found."; exit 1; }

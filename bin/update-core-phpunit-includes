#! /usr/bin/env sh

script_dir=$(dirname "$0")
root_dir=$(cd "$script_dir/.." && pwd)
includes_src="https://github.com/WordPress/wordpress-develop/branches/trunk/tests/phpunit/includes"

rm -rf "${root_dir}"/includes/core-phpunit/includes &&
  mkdir -p "${root_dir}"/includes/core-phpunit &&
  cd "${root_dir}"/includes/core-phpunit &&
  svn export $includes_src &&
  git apply "${root_dir}"/config/patches/core-phpunit/abstract-testcase.php.patch

#!/usr/bin/env bash

if [ "$#" -lt 2 ]; then
  echo -e "\033[1mPrepares the vendor directory (or restore it from cache) for a specific PHP and Codeception version combination.\033[0m"
  echo ""
  echo -e "\033[32mUsage:\033[0m"
  echo "  vendor_prepare.sh <php_version> <codeception_version> [<composer_version>] [<composer_cache_dir>]"
  echo ""
  echo -e "\033[32mExamples:\033[0m"
  echo ""
  echo "  Prepare the vendor directory for PHP 5.6, Codeception ^3.0,Composer v1 and cache to _build/.cache/composer"
  echo -e "  \033[36mvendor_prepare.sh 5.6 3.0 1\033[0m"
  echo "  Prepare the vendor directory for PHP 7.1, Codeception ^4.0,Composer v2 and cache to _build/.cache/composer"
  echo -e "  \033[36mvendor_prepare.sh 7.1 4.0 2\033[0m"
  echo ""
  echo "  Prepare the vendor directory for PHP 7.3 and Codeception ^2.5, Composer v2 and cache to /private/temp"
  echo -e "  \033[36mvendor_prepare.sh 7.3 2.5 2 /private/temp\033[0m"
  exit 0
fi

php_version="${1:-5.6}"
codeception_version="${2:-4.0}"
composer_version="${3:-2}"
# Default the Composer version to 2 if not specified. composer_version="${3:-2}"
composer_cache_dir="${4:-"${PWD}/.cache/composer"}"
cwd="$(cd "$(dirname "${BASH_SOURCE[0]}")" >/dev/null 2>&1 && pwd)"

if [ ! -d "${composer_cache_dir}" ]; then
  mkdir -p "${composer_cache_dir}" || {
    echo "Failed to create Composer cache dir ${composer_cache_dir}"
    exit 1
  }
fi

if [ -f "${cwd}/required-packages-${codeception_version}" ]; then
  echo "Reading packages from file ${cwd}/required-packages-${codeception_version}"
  required_packages="$(<"${cwd}/required-packages-${codeception_version}")"
else
  echo "File ${cwd}/required-packages-${codeception_version} not found, requiring codeception/codeception:^${codeception_version}"
  required_packages="codeception/codeception:^${codeception_version}"
fi

rm -rf vendor

docker run --rm -t \
  --user "$(id -u):$(id -g)" \
  -e FIXUID=1 \
  -e COMPOSER_CACHE_DIR=/composer/cache \
  -v "${HOME}/.composer/auth.json:/composer/auth.json" \
  -v "${composer_cache_dir}:/composer/cache" \
  -v "${PWD}:/project" \
  lucatume/composer:php"${php_version}-composer-v${composer_version}" require $required_packages
docker run --rm -t \
  --user "$(id -u):$(id -g)" \
  -e FIXUID=1 \
  -e COMPOSER_CACHE_DIR=/composer/cache \
  -v "${HOME}/.composer/auth.json:/composer/auth.json" \
  -v "${composer_cache_dir}:/composer/cache" \
  -v "${PWD}:/project" \
  lucatume/composer:php"${php_version}-composer-v${composer_version}" show

echo -e "\033[32mVendor directory ready for PHP ${php_version} and Codeception ${codeception_version}.\033[0m"

test -d "${PWD}/vendor" || {
  echo "${PWD}/vendor directory not found."
  exit 1
}

git checkout "${PWD}"/composer.json

echo "${php_version}.cc.${codeception_version}.composer.${composer_version}" >"${PWD}/.ready"

echo -e "\033[32mDone.\033[0m"

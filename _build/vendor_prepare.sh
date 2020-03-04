#!/usr/bin/env bash

if [ "$#" -lt 2 ]; then
  echo -e "\033[1mPrepare the vendor directory (or restore it from cache) for a specific PHP and Codeception version combination.\033[0m"
  echo ""
  echo -e "\033[32mUsage:\033[0m"
  echo "  vendor_prepare.sh <php_version> <codeception_version> [<cache_directory>:/tmp]"
  echo ""
  echo -e "\033[32mExamples:\033[0m"
  echo ""
  echo "  Prepare the vendor directory for PHP 5.6 and Codeception ^3.0 and cache to /tmp"
  echo -e "  \033[36mvendor_prepare.sh 5.6 3.0\033[0m"
  echo ""
  echo "  Prepare the vendor directory for PHP 7.3 and Codeception ^2.5 and cache to /private/temp"
  echo -e "  \033[36mvendor_prepare.sh 7.3 2.5 /private/temp\033[0m"
  exit 0
fi

php_version="$1"
codeception_version="$2"
cache_dir="${3:-/tmp}"
project="$(basename "${PWD}")"

if [ -f .ready ]; then
  echo -e "\033[32m.ready file found in working directory ($(<"${PWD}"/.ready))\033[0m"
  ready=$(<"${PWD}/.ready")
else
  ready=0
fi

if [ ${ready} != 0 ] && [ ${ready} != "${php_version}.cc.${codeception_version}" ] && [ -d "${PWD}/vendor" ]; then
  vendor_cache="${cache_dir}/vendor-${project}-${ready}"

  mv "${PWD}"/vendor "${vendor_cache}" || \
    (echo -e "\033[91mCould not move vendor directory to ${vendor_cache}\033[0m"; exit 1)

  mv "${PWD}"/composer.json "${vendor_cache}/composer.json" || \
    (echo -e "\033[91mCould not move composer.json to ${vendor_cache}/composer.json\033[0m"; exit 1)

  mv "${PWD}"/composer.lock "${vendor_cache}/composer.lock" || \
    (echo -e "\033[91mCould not move composer.lock to ${vendor_cache}/composer.lock\033[0m"; exit 1)

  rm "${PWD}"/composer.lock

  echo -e "\033[32mVendor directory cached to ${vendor_cache}\033[0m"
fi

if [ ! -f "${PWD}/.ready" ] || [ ! -d "${PWD}/vendor" ]; then
  current_vendor_cache="${cache_dir}/vendor-${project}-${php_version}.cc.${codeception_version}"

  if [ -d "${current_vendor_cache}" ]; then
    if mv "${current_vendor_cache}/composer.json" "${PWD}"/composer.json; then
        echo -e "\033[32mcomposer.json restored from cache\033[0m"
    else
      echo -e "\033[91mCould not restore composer.json from cache\033[0m"
    fi

    if mv "${current_vendor_cache}/composer.lock" "${PWD}"/composer.lock; then
        echo -e "\033[32mcomposer.lock restored from cache\033[0m"
    else
      echo -e "\033[91mCould not restore composer.lock from cache\033[0m"
    fi

    if mv "${current_vendor_cache}" "${PWD}"/vendor; then
        echo -e "\033[32mVendor directory restored from cache\033[0m"
    else
      echo -e "\033[91mCould not restore vendor directory from cache\033[0m"
    fi
  else
    echo -e "\033[91mVendor directory cache not found, updating.\033[0m"
    git checkout "${PWD}/composer.json"
    docker run --rm \
      --user "$(id -u)":"$(id -g)" \
      -v "${HOME}/.composer/auth.json:/root/.composer/auth.json" \
      -v "${PWD}:/project" \
      lucatume/composer:php"${php_version}" require codeception/codeception:^"${codeception_version}"

    docker-compose down

    echo -e "\033[32mVendor directory ready for PHP ${php_version} and Codeception ${codeception_version}.\033[0m"
  fi
fi

echo "${php_version}.cc.${codeception_version}" > "${PWD}/.ready"

echo -e "\033[32mDone.\033[0m"

#!/usr/bin/env sh

# Replaces the `extra._hash` string in the composer.json file.

SCRIPT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" >/dev/null 2>&1 && pwd )"
COMPOSER_FILE="${SCRIPT_DIR}/../composer.json"

test -f "${COMPOSER_FILE}" || {
  echo "composer.json file (${COMPOSER_FILE}) does not exist."
  exit 1
}

ORIGINAL_HASH=$(cat "${COMPOSER_FILE}" | grep -e '"_hash"' | cut -d':' -f2 | cut -d'"' -f2)
NEW_HASH=$(date | md5sum | cut -d' ' -f1)
sed -i'.bak' -e "s/${ORIGINAL_HASH}/${NEW_HASH}/g" "${COMPOSER_FILE}"

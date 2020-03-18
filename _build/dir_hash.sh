#!/usr/bin/env bash

if [ "$#" -lt 1 ]; then
  echo -e "\033[1mReturns a directory hash.\033[0m"
  echo ""
  echo -e "\033[32mUsage:\033[0m"
  echo "  dir_hash.sh <dir>"
  echo ""
  echo -e "\033[32mExamples:\033[0m"
  echo ""
  echo "  Return the hash of the src directory"
  echo -e "  \033[36mdir_hash.sh src \033[0m"
  exit 0
fi

echo "$(find "$1" -type f -print0 | sort -z | xargs -0 sha1sum | sha1sum | cut -d' ' -f1)"

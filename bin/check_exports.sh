#!/usr/bin/env bash

PWD=${1:-$(pwd)}

ignored_files=$(git status --ignored --porcelain \
  | grep -E '^!!' \
  | awk -F' ' '{ print $2 }' \
  | sed -E 's#\/$$##g'); \

echo -e "\033[1mIgnored files (local and global .gitignore file)\033[0m"; \
echo -e "\033[1m================================================\033[0m"; \
echo "${ignored_files}"; \

excluded_files=$(echo "${ignored_files}" | tr '\n' ',' | sed -e 's/,[^,]*$//g'); \
ignore_pattern="(\.$|$(echo "${excluded_files}" | sed -e 's/,/|/g' ))"; \
vcs_files=$(ls -a -1 "${PWD}" | grep -Ev "${ignore_pattern}" | sort); \

echo ""; \
echo -e "\033[1mVCS files (these will be pushed to the repository)\033[0m"; \
echo -e "\033[1m==================================================\033[0m"; \
echo "${vcs_files}"; \

export_ignored_files=$(grep -E '\s+export-ignore' ${PWD}/.gitattributes | awk -F' ' '{ print $1 }' | sort); \

echo ""; \
echo -e "\033[1mExport ignored files (export-ignore)\033[0m"; \
echo -e "\033[1m====================================\033[0m"; \
echo "${export_ignored_files}"; \

exported_vcs_files=$(comm -23 <(echo "${vcs_files}") <(echo "${export_ignored_files}")); \

echo ""; \
echo -e "\033[1mThe following files will be exported\033[0m"; \
echo -e "\033[1m====================================\033[0m"; \
echo "${exported_vcs_files}"

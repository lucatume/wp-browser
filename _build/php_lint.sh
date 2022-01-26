#!/usr/bin/env bash
# Kudos: https://akrabat.com/recursive-php-lint/

set -o nounset

# Recursively call `php -l` over the specified directories/files

if [ -z "$1" ] ; then
    printf 'Usage: %s <directory-or-file> ...\n' "$(basename "$0")"
    exit 1
fi

ERROR=false
SAVEIFS=$IFS
IFS=$'\n'
while test $# -gt 0; do
    CURRENT=${1%/}
    shift

    if [ ! -f $CURRENT ] && [ ! -d $CURRENT ] ; then
        echo "$CURRENT cannot be found"
        ERROR=true
        continue
    fi

    for FILE in $(find $CURRENT -type f -name "*.php") ; do
        OUTPUT=$(php -l "$FILE" 2> /dev/null)

        # Remove blank lines from the `php -l` output
        OUTPUT=$(echo -e "$OUTPUT" | awk 'NF')

        if [ "$OUTPUT" != "No syntax errors detected in $FILE" ] ; then
            echo -e "$FILE:"
            echo -e "  ${OUTPUT//$'\n'/\\n  }\n"
            ERROR=true
        fi
    done
done

IFS=$SAVEIFS

if [ "$ERROR" = true ] ; then
    exit 1
fi

echo "No syntax errors found."
exit 0

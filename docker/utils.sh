#!/usr/bin/env bash
# Utility functions to support the stack scripts.

function implode(){
    # Implodes an array with a specified delimiter.
    # Usage: implode ", " "${array[@]}"

    local d=$1
    shift
    echo -n "$1"
    shift
    printf "%s" "${@/#/$d}"
}

function get_xdebug_version(){
    # Returns the version of XDebug to use depending on the PHP version.
    # The returned string is either empty to indicate the latest version
    # or in the format `-<version>` to be used in a Dockerfile as a var.
    # Usage: get_xdebug_version 5.6

    local php_version=$1
    if [[ ${php_version} == '5.6' ]]; then
       echo '-2.5.5'
    fi
}

function get_php_extensions(){
    # Returns the PHP extensions to install depending on the PHP version.
    # The format is a space-separated list of PHP extensions that should
    # be installed in the Dockerfile using the `docker-php-ext-install` command.
    # Usage: get_php_extensions 5.6

    local php_version=$1
    if [[ ${php_version} == '5.6' ]]; then
       echo 'mysql pdo_mysql'
    fi

    echo 'pdo_mysql'
}


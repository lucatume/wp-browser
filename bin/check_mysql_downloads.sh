#!/bin/bash

# List of URLs to check
urls=(
    "https://dev.mysql.com/get/Downloads/MySQL-8.4/mysql-8.4.3-macos14-arm64.tar.gz"
    "https://dev.mysql.com/get/Downloads/MySQL-8.4/mysql-8.4.3-macos14-x86_64.tar.gz"
    "https://dev.mysql.com/get/Downloads/MySQL-8.4/mysql-8.4.3-linux-glibc2.17-aarch64-minimal.tar"
    "https://dev.mysql.com/get/Downloads/MySQL-8.4/file/mysql-8.4.3-linux-glibc2.17-x86_64-minimal.tar"
    "https://dev.mysql.com/get/Downloads/MySQL-8.4/mysql-8.4.3-winx64.zip"
)

# Function to check URL validity
check_url() {
    local url=$1
    local status_code=$(curl -o /dev/null -s -w "%{http_code}\n" "$url")
    if [ "$status_code" -ne 400 ]; then
        echo "URL: $url is valid. Status code: $status_code"
    else
        echo "URL: $url is invalid. Status code: $status_code"
    fi
}

# Loop through each URL and check its validity
for url in "${urls[@]}"; do
    check_url "$url"
done

#! /usr/bin/env bash

set -eu

# Accept an optional set of paths.
destination_dir=$1

if [ -z "$destination_dir" ]; then
    echo "Usage: $0 <destination_dir> [files...]"
    exit 1
fi

files=${@:2}

[ -d ./.build/35 ] && rm -rf ./.build/35
mkdir -p ./.build/35
rsync -av --exclude-from=bin/build-35-exclusions.txt . ./.build/35
cd ./.build/35
rm -rf vendor composer.lock
composer require --dev rector/rector:0.19.8 -W
composer dump-autoload
if [ -z "$files" ]; then
    vendor/bin/rector process --config=config/rector-35.php
else
    vendor/bin/rector process --config=config/rector-35.php $files
fi
rm -rf vendor composer.lock composer.json
cp config/composer-35.json composer.json
rm -rf config
cd -

# Run PHP 7.1 compatibility checks using Docker
echo "Running PHP 7.1 compatibility checks..."

# Run Docker container with PHP 7.1 for compatibility checks
docker run --rm \
    -v "$(pwd)/.build/35:/app" \
    -w /app \
    php:7.1-cli \
    bash -c '
        set -e
        echo "==> Running PHP syntax check on all PHP files..."
        find . -name "*.php" -not -path "./vendor/*" -print0 | xargs -0 -n1 php -l > /dev/null
        echo "✓ All PHP 7.1 compatibility checks passed!"
    ' || {
        echo "✗ PHP 7.1 compatibility check failed!"
        exit 1
    }

vendor/bin/phpcs --standard=config/version-35-compatibility.xml \
  --runtime-set ignore_warnings_on_exit 1 \
  -s \
  ./.build/35/src/**/*.php ./.build/35/includes/**/*.php

# Clean up temporary config
rm -f ./.build/35/phpcs-compat.xml

rsync -av ./.build/35/includes/ "$destination_dir/includes"
rsync -av ./.build/35/src/ "$destination_dir/src"
rsync -av ./.build/35/tests/ "$destination_dir/tests"
vendor/bin/phpcbf --standard=config/phpcs.xml "$destination_dir/src" || true

rm -rf .build

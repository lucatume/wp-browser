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

rsync -av ./.build/35/includes/ "$destination_dir/includes"
rsync -av ./.build/35/src/ "$destination_dir/src"
rsync -av ./.build/35/tests/ "$destination_dir/tests"

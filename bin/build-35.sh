#! /usr/bin/env bash

set -eu

[ -d ./.build/35 ] && rm -rf ./.build/35
mkdir -p ./.build/35
rsync -av --exclude-from=bin/build-35-exclusions.txt . ./.build/35
cd ./.build/35
rm -rf vendor composer.lock
composer require --dev rector/rector:0.19.2 -W
composer dump-autoload
vendor/bin/rector --config=config/rector-35.php
rm -rf vendor composer.lock composer.json
cp config/composer-35.json composer.json
cd -

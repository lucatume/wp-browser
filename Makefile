build:
	composer install
.PHONY: build

test:
	vendor/bin/codecept run
.PHONY: test

clean:
	rm -rf vendor composer.lock var
	mkdir -p var
.PHONY: clean

clean_tmp:
	rm -rf var/_output var/_tmp
.PHONY: clean_tmp

update_core_phpunit_includes:
	bin/update_core_phpunit_includes
.PHONY: update_core_phpunit_includes

package: update_core_phpunit_includes test
	bin/gitattributes-update
.PHONY: package

update_sqlite_plugin:
	bin/update_sqlite_plugin
.PHONY: update_sqlite_plugin

docs_serve:
	mkdocs serve -a 0.0.0.0:8000

docs_api_update:
	php bin/extract-api.php "lucatume\WPBrowser\Module\WPBrowser" "docs/modules/WPBrowser.md"
	php bin/extract-api.php "lucatume\WPBrowser\Module\WPCLI" "docs/modules/WPCLI.md"
	php bin/extract-api.php "lucatume\WPBrowser\Module\WPDb" "docs/modules/WPDb.md"
	php bin/extract-api.php "lucatume\WPBrowser\Module\WPFilesystem" "docs/modules/WPFilesystem.md"
	php bin/extract-api.php "lucatume\WPBrowser\Module\WPLoader" "docs/modules/WPLoader.md"
	php bin/extract-api.php "lucatume\WPBrowser\Module\WPQueries" "docs/modules/WPQueries.md"
	php bin/extract-api.php "lucatume\WPBrowser\Module\WPWebDriver" "docs/modules/WPWebDriver.md"
.PHONY: docs_api_update

wordpress_install:
	php bin/setup-wp.php

clean_procs:
	pgrep -f 'php -S' | xargs kill
	pgrep chromedriver | xargs kill
	rm -f var/_output/*.pid var/_output/*.running
	set -o allexport && source tests/.env && set +o allexport && docker compose down
.PHONY: clean_procs

build_35:
	rm -rf vendor composer.lock
	composer require --dev rector/rector -W
	vendor/bin/rector --config=config/rector-35.php
	rm -rf vendor composer.lock composer.json
	cp config/composer-35.json composer.json
	plink 7.1
	composer update -W
	${MAKE} clean_procs
	vendor/bin/codeception chromedriver:update
	WPBROWSER_VERSION=3.5 vendor/bin/codecept run unit
	plink 7.2
	composer update -W
	${MAKE} clean_procs
	vendor/bin/codeception chromedriver:update
	WPBROWSER_VERSION=3.5 vendor/bin/codecept run unit
	plink 7.3
	composer update -W
	${MAKE} clean_procs
	vendor/bin/codeception chromedriver:update
	WPBROWSER_VERSION=3.5 vendor/bin/codecept run unit
	plink 7.4
	composer update -W
	${MAKE} clean_procs
	vendor/bin/codeception chromedriver:update
	WPBROWSER_VERSION=3.5 vendor/bin/codecept run unit
.PHONY: build_35

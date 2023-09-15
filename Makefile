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

docs_serve:
	mkdocs serve -a 0.0.0.0:8000

wordpress_install:
	php bin/setup-wp.php

clean_procs:
	pgrep -f 'php -S' | xargs kill
	pgrep chromedriver | xargs kill
	rm -f var/_output/*.pid var/_output/*.running
.PHONY: clean_procs

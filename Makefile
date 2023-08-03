SHELL := /bin/bash
PHP_VERSIONS := 8.0 8.1 8.2

build:
	for php_version in $(PHP_VERSIONS); do \
		bin/stack -p$$php_version build || exit 1; \
	done
.PHONY: build

test:
	for php_version in $(PHP_VERSIONS); do \
		bin/stack -p$$php_version composer_update &&\
		bin/stack -p$$php_version phpstan || exit 1; \
		bin/stack -p$$php_version test || exit 1; \
	done
.PHONY: test

clean:
	bin/stack deep_clean
.PHONY: clean

clean_tmp:
	bin/stack clean_tmp
.PHONY: clean_tmp

update_core_phpunit_includes:
	bin/update-core-phpunit-includes
.PHONY: update_core_phpunit_includes

package: update_core_phpunit_includes test
	bin/gitattributes-update
.PHONY: package

update_sqlite_plugin:
	bin/update_sqlite_plugin

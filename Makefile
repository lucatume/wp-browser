SHELL := /bin/bash
PHP_VERSIONS := 8.0 8.1 8.2

build:
	for php_version in $(PHP_VERSIONS); do \
		bin/stack -p$$php_version build || exit 1; \
	done
.PHONY: build

test:
	#todo Run static analysis here
	for php_version in $(PHP_VERSIONS); do \
		bin/stack -p$$php_version composer_update &&\
		bin/stack -p$$php_version test || exit 1; \
	done
.PHONY: test

clean:
	bin/stack deep_clean
.PHONY: clean

package: test
	bin/gitattributes-update
.PHONY: package

update_core_phpunit_includes:
	rm -rf includes/core-phpunit/includes
	mkdir -p includes/core-phpunit
	cd includes/core-phpunit && \
		svn export https://github.com/WordPress/wordpress-develop/branches/trunk/tests/phpunit/includes
	git apply config/patches/core-phpunit/abstract-testcase.php.patch
.PHONY: update_core_phpunit_includes

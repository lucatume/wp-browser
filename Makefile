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

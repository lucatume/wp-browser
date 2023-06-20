build:
	bin/stack -p5.6 build
	bin/stack -p7.0 build
	bin/stack -p7.1 build
	bin/stack -p7.2 build
	bin/stack -p7.3 build
	bin/stack -p7.4 build
	bin/stack -p8.0 build
	bin/stack -p8.1 build
.PHONY: build

lint:
	docker run --rm \
		--volume "$(PWD):$(PWD):ro" \
		--workdir "$(PWD)" \
		lucatume/parallel-lint-56 \
			--colors \
			--exclude /project/src/tad/WPBrowser/Traits/_WithSeparateProcessChecksPHPUnitGte70.php \
			"$(PWD)/src"
.PHONY: lint

phpcs:
	docker run --rm \
		--volume "$(PWD):$(PWD)" \
		--workdir "$(PWD)" \
		cytopia/phpcs \
			--colors \
			-p \
			-s \
			--standard=phpcs.xml \
			--ignore=src/data,src/includes,src/tad/scripts,src/tad/WPBrowser/Compat  \
			src
.PHONY: phpcs

phpcs_fix:
	docker run --rm \
		--volume "$(PWD):$(PWD)" \
		--workdir "$(PWD)" \
		cytopia/phpcbf \
			--colors \
			-p \
			-s \
			--standard=phpcs.xml \
			--ignore=src/data,src/includes,src/tad/scripts,_build \
			src tests
.PHONY: phpcs_fix

phpcs_fix_and_sniff: phpcs_fix phpcs

phpstan:
	docker run --rm \
		--volume "$(PWD):$(PWD):ro" \
		--workdir "$(PWD)" \
		lucatume/wpstan:0.12.42 analyze \
			-l max
phpstan:
	docker run --rm --volume "$(PWD):$(PWD):ro" --workdir "$(PWD)" lucatume/wpstan:0.12.42 analyze -l max
.PHONY: phpstan

static_analysis: lint phpcs phpstan
.PHONE: static_analysis

test: static_analysis
	bin/stack -p5.6 xdebug-off && bin/stack -c2 composer_update && bin/stack -p5.6 test
	bin/stack -p5.6 xdebug-off && bin/stack -c3 composer_update && bin/stack -p5.6 test
	bin/stack -p5.6 xdebug-off && bin/stack -c4 composer_update && bin/stack -p5.6 test
	bin/stack -p7.4 xdebug-off && bin/stack -c2 composer_update && bin/stack -p7.4 test
	bin/stack -p7.4 xdebug-off && bin/stack -c3 composer_update && bin/stack -p7.4 test
	bin/stack -p7.4 xdebug-off && bin/stack -c4 composer_update && bin/stack -p7.4 test
	bin/stack -p8.0 xdebug-off && bin/stack -c4 composer_update && bin/stack -p8.0 test
	bin/stack -p8.1 xdebug-off && bin/stack -c4 composer_update && bin/stack -p8.1 test
.PHONY: test

clean:
	bin/stack clean
.PHONY: clean

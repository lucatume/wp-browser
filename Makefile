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

build_images:
	bin/stack -p5.6 build_images
	bin/stack -p7.0 build_images
	bin/stack -p7.1 build_images
	bin/stack -p7.2 build_images
	bin/stack -p7.3 build_images
	bin/stack -p7.4 build_images
	bin/stack -p8.0 build_images
	bin/stack -p8.1 build_images

build_56_lock_files:
	[ -d config/composer ] || mkdir config/composer
	rm -f config/composer/*5.6*
	cp composer.json config/composer/composer.json.bak

	bin/stack -p5.6 -c2 composer_update
	mv composer.json config/composer/composer-5.6-codeception-2.json
	mv composer.lock config/composer/composer-5.6-codeception-2.lock
	cp config/composer/composer.json.bak composer.json

	bin/stack -p5.6 -c3 composer_update
	mv composer.json config/composer/composer-5.6-codeception-3.json
	mv composer.lock config/composer/composer-5.6-codeception-3.lock
	cp config/composer/composer.json.bak composer.json

	bin/stack -p5.6 -c4 composer_update
	mv composer.json config/composer/composer-5.6-codeception-4.json
	mv composer.lock config/composer/composer-5.6-codeception-4.lock

	mv config/composer/composer.json.bak composer.json

build_74_lock_files:
	[ -d config/composer ] || mkdir config/composer
	cp composer.json config/composer/composer.json.bak

	bin/stack -p7.4 -d -c2 composer_update
	mv composer.json config/composer/composer-7.4-codeception-2.json
	mv composer.lock config/composer/composer-7.4-codeception-2.lock
	cp config/composer/composer.json.bak composer.json

	bin/stack -p7.4 -d -c3 composer_update
	mv composer.json config/composer/composer-7.4-codeception-3.json
	mv composer.lock config/composer/composer-7.4-codeception-3.lock
	cp config/composer/composer.json.bak composer.json

	bin/stack -p7.4 -d -c4 composer_update
	mv composer.json config/composer/composer-7.4-codeception-4.json
	mv composer.lock config/composer/composer-7.4-codeception-4.lock

	mv config/composer/composer.json.bak composer.json

build_80_lock_files:
	[ -d config/composer ] || mkdir config/composer
	cp composer.json config/composer/composer.json.bak

	bin/stack -p8.0 -d -c4 composer_update
	mv composer.json config/composer/composer-8.0-codeception-4.json
	mv composer.lock config/composer/composer-8.0-codeception-4.lock

	mv config/composer/composer.json.bak composer.json

build_81_lock_files:
	[ -d config/composer ] || mkdir config/composer
	cp composer.json config/composer/composer.json.bak

	bin/stack -p8.1 -d -c4 composer_update
	mv composer.json config/composer/composer-8.1-codeception-4.json
	mv composer.lock config/composer/composer-8.1-codeception-4.lock

	mv config/composer/composer.json.bak composer.json

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
			--standard=config/phpcs.xml \
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
			--standard=config/phpcs.xml \
			--ignore=src/data,src/includes,src/tad/scripts,_build \
			src tests
.PHONY: phpcs_fix

phpcs_fix_and_sniff: phpcs_fix phpcs

build_phpstan:
	mv composer.json composer.json.bak
	[ ! -f composer.lock ] || mv composer.lock composer.lock.bak
	cp config/composer/composer-7.4-codeception-4.json composer.json
	cp config/composer/composer-7.4-codeception-4.lock composer.lock
	bin/stack -p7.4 composer_install
	rm -rf composer.json composer.lock
	mv composer.json.bak composer.json
	[ ! -f composer.lock.bak ] || mv composer.lock.bak composer.lock

phpstan:
	docker run --rm \
		--volume "$(PWD):$(PWD):ro" \
		--workdir "$(PWD)" \
		lucatume/wpstan:0.12.42 analyze \
			--configuration=config/phpstan.neon.dist \
			-l max

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

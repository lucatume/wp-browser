# Use bash as shell.
SHELL := /bin/bash

# If you see pwd_unknown showing up, this is why. Re-calibrate your system.
PWD ?= pwd_unknown

# PROJECT_NAME defaults to name of the current directory.
PROJECT_NAME = $(notdir $(PWD))

# Suppress `make` own output.
.SILENT:

# Make `help` the default target to make sure it will display when make is called without a target.
.DEFAULT_GOAL := help

help: ## Show this help message.
	echo -e "\n\033[36mHint\033[0m: if you do not know what target to run first, run the \033[36mbuild\033[0m one.\n"
	@grep -E '^[a-zA-Z0-9\._-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-30s\033[0m %s\n", $$1, $$2}'
.PHONY: help

build: clean docker_pull wp_refresh docker_build_debug ## Builds the project testing environment.
	"${PWD}/_build/vendor-prepare.sh" "5.6" "4.0"
.PHONY: build

composer_vendor_prepare: ## Installs Composer dependencies for a target PHP and Codeception version.
	if [[ "$(PHP_VERSION)" = '' ]] || [[ "$(CODECEPTION_VERSION)" = '' ]] || [[ "$(COMPOSER_VERSION)" = '' ]]; \
		then echo "Usage: make composer_vendor_prepare PHP_VERSION=5.6 CODECEPTION_VERSION=4.0 COMPOSER_VERSION=2"; exit 1; \
	fi
	"${PWD}/_build/vendor-prepare.sh" "$(PHP_VERSION)" "$(CODECEPTION_VERSION)" "$(COMPOSER_VERSION)"
.PHONY: composer_vendor_prepare

docker_pull: ## Pull all the Docker images this repository will use in building images or running processes.
	images=( \
		'cytopia/phpcs' \
		'cytopia/phpcbf' \
		'lucatume/parallel-lint-56' \
		'lucatume/wpstan' \
		'lucatume/codeception' \
		'lucatume/composer:php5.6' \
		'lucatume/composer:php7.0' \
		'lucatume/composer:php7.1' \
		'lucatume/composer:php7.2' \
		'lucatume/composer:php7.3' \
		'lucatume/composer:php7.4' \
	); \
	for image in "$${images[@]}"; do \
		docker pull "$$image"; \
	done;
.PHONY: docker_pull
code_lint: ## Lint the project source files to make sure they are PHP 5.6 compatible.
	docker run --rm -v ${PWD}:/project lucatume/parallel-lint-56 \
		--colors \
		--exclude /project/src/tad/WPBrowser/Traits/_WithSeparateProcessChecksPHPUnitGte70.php \
		/project/src
.PHONY: code_lint

code_sniff: ## Use the PHP Code Sniffer container to sniff the relevant source files.
	docker run --rm -v ${PWD}:/data cytopia/phpcs \
		--colors \
		-p \
		-s \
		--standard=phpcs.xml $(SRC) \
		--ignore=src/data,src/includes,src/tad/scripts,src/tad/WPBrowser/Compat  \
		src
.PHONY: code_sniff

code_fix: ## Use the PHP Code Beautifier container to fix the source and tests code.
	docker run --rm -v ${PWD}:/data cytopia/phpcbf \
		--colors \
		-p \
		-s \
		--standard=phpcs.xml $(SRC) \
		--ignore=src/data,src/includes,src/tad/scripts,_build \
		src tests
.PHONY: code_fix

code_fix_n_sniff: code_fix code_sniff ## Fix the PHP code, then sniff it.
.PHONY: code_fix_n_sniff

PHPSTAN_LEVEL?=max
phpstan: # Use phpstan container to analyze the source code; Configuration will be read from the `phpstan.neon.dist` file.
	docker run --rm -v ${PWD}:/project lucatume/wpstan:0.12.42 analyze -l ${PHPSTAN_LEVEL}
.PHONY: phpstan

docker_clean: ## Clean the project Docker containers, volumes and networks.
	docker stop $$(docker ps -q -f "name=${PROJECT_NAME}*") > /dev/null 2>&1 \
		&& echo "Running containers stopped." \
		|| echo "No running containers".
	docker rm $$(docker ps -qa -f "name=${PROJECT_NAME}*") > /dev/null 2>&1 \
		&& echo "Stopped containers removed." \
		|| echo "No stopped containers".
	docker volume rm -f $$(docker volume ls -q -f "name=${PROJECT_NAME}*") > /dev/null 2>&1 \
		&& echo "Volumes removed." \
		|| echo "No volumes found".
	docker network rm $$(docker network ls -q -f "name=${PROJECT_NAME}*") > /dev/null 2>&1 \
		&& echo "Networks removed." \
		|| echo "No networks found".
.PHONY: docker_clean

docs_rebuild: composer.lock src/Codeception/Module ## Produces the Modules documentation in the docs/modules folder.
	mkdir -p docs/modules
	for file in ${PWD}/src/Codeception/Module/*.php; \
	do \
		name=$$(basename "$${file}" | cut -d. -f1); \
		if	[ $${name} = "WPBrowserMethods" ]; then \
			continue; \
		fi; \
		class="Codeception\\Module\\$${name}"; \
		file=${PWD}/docs/modules/$${name}.md; \
		if [ ! -f $${file} ]; then \
			echo "<!--doc--><!--/doc-->" > $${file}; \
		fi; \
		echo "Generating documentation for module $${class} in file $${file}..."; \
		docker run --rm -v ${PWD}:${PWD} \
			php:7.3-cli \
				${PWD}/docs/bin/wpbdocmd generate \
				--visibility=public \
				--methodRegex="/^[^_]/" \
				--tableGenerator=tad\\WPBrowser\\Documentation\\TableGenerator \
				$${class} > doc.tmp; \
		if [ 0 != $$? ]; then rm doc.tmp && exit 1; fi; \
		echo "${PWD}/doc.tmp $${file}" | xargs php ${PWD}/docs/bin/update_doc.php; \
		rm doc.tmp; \
	done;
	cp ${PWD}/docs/welcome.md ${PWD}/docs/README.md
.PHONY: docs_rebuild

relase_check_exports: ## Prints a list of files that will be exported from the project on package pull.
	bash ./_build/check_exports.sh
.PHONY: check_exports

build_suites: ## Rebuilds the suites from the respective configuration files.
	DOCKER_RUN_USER=$$(id -u) DOCKER_RUN_GROUP=$$(id -g) XDE=0 TEST_SUBNET=27 \
		docker-compose --project-name=${PROJECT_NAME}_build run --rm codeception build
.PHONY: build_suites

test: ## Runs all the tests in the container.
	DOCKER_RUN_USER=$$(id -u) DOCKER_RUN_GROUP=$$(id -g) XDEBUG_DISABLE=1 TEST_SUBNET=128 \
		docker-compose --project-name=${PROJECT_NAME}_acceptance \
		run --rm ccf run acceptance
	cat vendor/wordpress/wordpress/wp-config.php
#	DOCKER_RUN_USER=$$(id -u) DOCKER_RUN_GROUP=$$(id -g) XDEBUG_DISABLE=1 TEST_SUBNET=129 \
#		docker-compose --project-name=${PROJECT_NAME}_cli \
#		run --rm ccf run cli
	DOCKER_RUN_USER=$$(id -u) DOCKER_RUN_GROUP=$$(id -g) XDEBUG_DISABLE=1 TEST_SUBNET=130 \
		docker-compose --project-name=${PROJECT_NAME}_climodule \
		run --rm ccf run tests/climodule/cli/StrictArgsCest.php:should_use_strict_arguments_if_configured_to_do_so --debug
#	DOCKER_RUN_USER=$$(id -u) DOCKER_RUN_GROUP=$$(id -g) XDEBUG_DISABLE=1 TEST_SUBNET=131 \
#		docker-compose --project-name=${PROJECT_NAME}_dbunit \
#		run --rm ccf run dbunit
#	DOCKER_RUN_USER=$$(id -u) DOCKER_RUN_GROUP=$$(id -g) XDEBUG_DISABLE=1 TEST_SUBNET=132 \
#		docker-compose --project-name=${PROJECT_NAME}_functional \
#		run --rm ccf run functional
#	DOCKER_RUN_USER=$$(id -u) DOCKER_RUN_GROUP=$$(id -g) XDEBUG_DISABLE=1 TEST_SUBNET=133 \
#		docker-compose --project-name=${PROJECT_NAME}_muloader \
#		run --rm ccf run muloader
	DOCKER_RUN_USER=$$(id -u) DOCKER_RUN_GROUP=$$(id -g) XDEBUG_DISABLE=1 TEST_SUBNET=134 \
		docker-compose --project-name=${PROJECT_NAME}_unit \
		run --rm ccf run unit --debug
#	DOCKER_RUN_USER=$$(id -u) DOCKER_RUN_GROUP=$$(id -g) XDEBUG_DISABLE=1 TEST_SUBNET=135 \
#		docker-compose --project-name=${PROJECT_NAME}_webdriver \
#		run --rm codeception run webdriver --debug
#	DOCKER_RUN_USER=$$(id -u) DOCKER_RUN_GROUP=$$(id -g) XDEBUG_DISABLE=1 TEST_SUBNET=136 \
#		docker-compose --project-name=${PROJECT_NAME}_wpcli_module \
#		run --rm ccf run wpcli_module
#	DOCKER_RUN_USER=$$(id -u) DOCKER_RUN_GROUP=$$(id -g) XDEBUG_DISABLE=1 TEST_SUBNET=137 \
#		docker-compose --project-name=${PROJECT_NAME}_wpfunctional \
#		run --rm ccf run wpfunctional
#	DOCKER_RUN_USER=$$(id -u) DOCKER_RUN_GROUP=$$(id -g) XDEBUG_DISABLE=1 TEST_SUBNET=138 \
#		docker-compose --project-name=${PROJECT_NAME}_wploader_multisite \
#		run --rm ccf run wploader_multisite
#	DOCKER_RUN_USER=$$(id -u) DOCKER_RUN_GROUP=$$(id -g) XDEBUG_DISABLE=1 TEST_SUBNET=139 \
#		docker-compose --project-name=${PROJECT_NAME}_wploader_wpdb_interaction \
#		run --rm ccf run wploader_wpdb_interaction
#	DOCKER_RUN_USER=$$(id -u) DOCKER_RUN_GROUP=$$(id -g) XDEBUG_DISABLE=1 TEST_SUBNET=140 \
#		docker-compose --project-name=${PROJECT_NAME}_wploadersuite \
#		run --rm ccf run wploadersuite
	DOCKER_RUN_USER=$$(id -u) DOCKER_RUN_GROUP=$$(id -g) XDEBUG_DISABLE=1 TEST_SUBNET=141 \
		docker-compose --project-name=${PROJECT_NAME}_wpmodule \
		run --rm ccf run wpmodule --debug
#	DOCKER_RUN_USER=$$(id -u) DOCKER_RUN_GROUP=$$(id -g) XDEBUG_DISABLE=1 TEST_SUBNET=142 \
#		docker-compose --project-name=${PROJECT_NAME}_events \
#		run --rm ccf run events
#	DOCKER_RUN_USER=$$(id -u) DOCKER_RUN_GROUP=$$(id -g) XDEBUG_DISABLE=1 TEST_SUBNET=143 \
#		docker-compose --project-name=${PROJECT_NAME}_init \
#		run --rm ccf run init
#	DOCKER_RUN_USER=$$(id -u) DOCKER_RUN_GROUP=$$(id -g) XDEBUG_DISABLE=1 TEST_SUBNET=144 \
#		docker-compose --project-name=${PROJECT_NAME}_isolated \
#		run --rm ccf run isolated
.PHONY: test

print_ready: ## Prints the currently ready configuration.
	test -f "${PWD}/.ready" && echo $$(<${PWD}/.ready) || echo "No .ready file found."
.PHONY: print_ready

release_major: ## Prepare a major version.
	_build/release.php major
.PHONY: release_major

release_minor: ## Prepare a minor version.
	_build/release.php minor
.PHONY: release_minor

patch: ## Prepare a patch version.
	_build/release.php patch
.PHONY: patch

composer_hash_bump: ## Bumps the composer.lock hash to trigger an invalidation of caches that might be using ti.
	sh "${PWD}/_build/composer-hash.sh"
.PHONY: composer_hash_bump

pre_commit: code_lint code_fix_n_sniff phpstan docs  ## Run a set of checks on the code before a commit.
.PHONY: pre_commit

test_56: ## Runs all the tests in the PHP 5.6 container.
#	DOCKER_RUN_USER=$$(id -u) DOCKER_RUN_GROUP=$$(id -g) XDEBUG_DISABLE=1 TEST_SUBNET=89 \
#		docker-compose --project-name=${PROJECT_NAME}_acceptance \
#		run --rm cc56 run acceptance
#	DOCKER_RUN_USER=$$(id -u) DOCKER_RUN_GROUP=$$(id -g) XDEBUG_DISABLE=1 TEST_SUBNET=90 \
#		docker-compose --project-name=${PROJECT_NAME}_cli \
#		run --rm cc56 run cli
	DOCKER_RUN_USER=$$(id -u) DOCKER_RUN_GROUP=$$(id -g) XDEBUG_DISABLE=1 TEST_SUBNET=91 \
		docker-compose --project-name=${PROJECT_NAME}_climodule \
		run --rm cc56 run climodule --debug
#	DOCKER_RUN_USER=$$(id -u) DOCKER_RUN_GROUP=$$(id -g) XDEBUG_DISABLE=1 TEST_SUBNET=92 \
#		docker-compose --project-name=${PROJECT_NAME}_dbunit \
#		run --rm cc56 run dbunit
#	DOCKER_RUN_USER=$$(id -u) DOCKER_RUN_GROUP=$$(id -g) XDEBUG_DISABLE=1 TEST_SUBNET=93 \
#		docker-compose --project-name=${PROJECT_NAME}_functional \
#		run --rm cc56 run functional
#	DOCKER_RUN_USER=$$(id -u) DOCKER_RUN_GROUP=$$(id -g) XDEBUG_DISABLE=1 TEST_SUBNET=94 \
#		docker-compose --project-name=${PROJECT_NAME}_muloader \
#		run --rm cc56 run muloader
	DOCKER_RUN_USER=$$(id -u) DOCKER_RUN_GROUP=$$(id -g) XDEBUG_DISABLE=1 TEST_SUBNET=95 \
		docker-compose --project-name=${PROJECT_NAME}_unit \
		run --rm cc56 run unit --debug
#	DOCKER_RUN_USER=$$(id -u) DOCKER_RUN_GROUP=$$(id -g) XDEBUG_DISABLE=1 TEST_SUBNET=96 \
#		docker-compose --project-name=${PROJECT_NAME}_webdriver \
#		run --rm cc56 run webdriver --debug
#	DOCKER_RUN_USER=$$(id -u) DOCKER_RUN_GROUP=$$(id -g) XDEBUG_DISABLE=1 TEST_SUBNET=97 \
#		docker-compose --project-name=${PROJECT_NAME}_wpcli_module \
#		run --rm cc56 run wpcli_module
#	DOCKER_RUN_USER=$$(id -u) DOCKER_RUN_GROUP=$$(id -g) XDEBUG_DISABLE=1 TEST_SUBNET=98 \
#		docker-compose --project-name=${PROJECT_NAME}_wpfunctional \
#		run --rm cc56 run wpfunctional
#	DOCKER_RUN_USER=$$(id -u) DOCKER_RUN_GROUP=$$(id -g) XDEBUG_DISABLE=1 TEST_SUBNET=99 \
#		docker-compose --project-name=${PROJECT_NAME}_wploader_multisite \
#		run --rm cc56 run wploader_multisite
#	DOCKER_RUN_USER=$$(id -u) DOCKER_RUN_GROUP=$$(id -g) XDEBUG_DISABLE=1 TEST_SUBNET=100 \
#		docker-compose --project-name=${PROJECT_NAME}_wploader_wpdb_interaction \
#		run --rm cc56 run wploader_wpdb_interaction
#	DOCKER_RUN_USER=$$(id -u) DOCKER_RUN_GROUP=$$(id -g) XDEBUG_DISABLE=1 TEST_SUBNET=101 \
#		docker-compose --project-name=${PROJECT_NAME}_wploadersuite \
#		run --rm cc56 run wploadersuite
	DOCKER_RUN_USER=$$(id -u) DOCKER_RUN_GROUP=$$(id -g) XDEBUG_DISABLE=1 TEST_SUBNET=102 \
		docker-compose --project-name=${PROJECT_NAME}_wpmodule \
		run --rm cc56 run wpmodule --debug
#	DOCKER_RUN_USER=$$(id -u) DOCKER_RUN_GROUP=$$(id -g) XDEBUG_DISABLE=1 TEST_SUBNET=103 \
#		docker-compose --project-name=${PROJECT_NAME}_events \
#		run --rm cc56 run events
#	DOCKER_RUN_USER=$$(id -u) DOCKER_RUN_GROUP=$$(id -g) XDEBUG_DISABLE=1 TEST_SUBNET=104 \
#		docker-compose --project-name=${PROJECT_NAME}_isolated \
#		run --rm cc56 run isolated
.PHONY: test_56

ssh_dev: ## Open a bash shell in the PHP 5.6 testing container.
	export TEST_SUBNET=105; \
		_build/dc.sh --project-name=${PROJECT_NAME}_dev \
		-f docker-compose.debug.yml \
		run --rm \
		cc56 shell
.PHONY: ssh_dev

ssh_dev_7: ## Open a bash shell in the PHP 7 testing container.
	export TEST_SUBNET=189; \
		_build/dc.sh --project-name=${PROJECT_NAME}_dev_7 \
		-f docker-compose.debug.yml \
		run --rm \
		codeception shell
.PHONY: ssh_dev_7

wp_setup: ## Populate the vendor/wordpres/wordpress directory.
	export TEST_SUBNET=203 && \
		_build/dc.sh --project-name=${PROJECT_NAME}_setup_wordpress \
		-f docker-compose.debug.yml \
		up -d wordpress
.PHONY: wp_setup

wp_refresh: ## Remove the current WordPress installation, if any, and set it up again.
	export TEST_SUBNET=202 && \
	 rm -rf vendor/wordpress/wordpress && \
		_build/dc.sh --project-name=${PROJECT_NAME}_refresh_wp \
		-f docker-compose.yml \
		run --rm cli wp core download
	$(MAKE) wp_setup
.PHONY: wp_refresh

docker_build_debug: ## Builds the debug image.
	docker-compose -f docker-compose.yml -f docker-compose.debug.yml build
.PHONY: docker_build_debug

clean: docker_clean ## Cleans the project removing any dependency and artifact.
	echo "Removing .bak files." && rm -f *.bak
	echo "Emptying tests/_output directory." && rm -rf tests/_output && mkdir tests/_output && echo "*" > tests/_output/.gitignore
	rm -rf vendor
	rm -f .ready
.PHONY: clean

composer_56_update: ## Update the Composer dependencies using the PHP 5.6 container.
	docker run --rm \
	--user "$$(id -u):$$(id -g)" \
	-e FIXUID=1 \
	-v "${HOME}/.composer/auth.json:/composer/auth.json" \
	-v "${PWD}:/project" \
	-t \
	lucatume/composer:php5.6 update
.PHONY: composer_56_update

composer_73_update: ## Update the Composer dependencies using the PHP 7.3 container.
	docker run --rm \
	--user "$$(id -u):$$(id -g)" \
	-e FIXUID=1 \
	-v "${HOME}/.composer/auth.json:/composer/auth.json" \
	-v "${PWD}:/project" \
	-t \
	lucatume/composer:php7.3 update
.PHONY: composer_73_update

composer_dump_autoload: ## Generate Composer autoload files using a specific PHP version.
	if [ "$(PHP_VERSION)" = '' ]; then echo "Usage: make composer_dump_autoload PHP_VERSION=5.6"; exit 1; fi
	"${PWD}/_build/dump-autoload.sh" "$(PHP_VERSION)"
.PHONY: composer_dump_autoload

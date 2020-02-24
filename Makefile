SHELL := /bin/bash

TRAVIS_WP_FOLDER ?= "vendor/wordpress/wordpress"
TRAVIS_WP_URL ?= "http://wp.test"
TRAVIS_WP_DOMAIN ?= "wp.test"
TRAVIS_DB_NAME ?= "test_site"
TRAVIS_TEST_DB_NAME ?= "test"
TRAVIS_WP_TABLE_PREFIX ?= "wp_"
TRAVIS_WP_ADMIN_USERNAME ?= "admin"
TRAVIS_WP_ADMIN_PASSWORD ?= "admin"
TRAVIS_WP_SUBDOMAIN_1 ?= "test1"
TRAVIS_WP_SUBDOMAIN_1_TITLE ?= "Test Subdomain 1"
TRAVIS_WP_SUBDOMAIN_2 ?= "test2"
TRAVIS_WP_SUBDOMAIN_2_TITLE ?= "Test Subdomain 2"
TRAVIS_WP_VERSION ?= "latest"
COMPOSE_FILE ?= docker-compose.yml
CODECEPTION_VERSION ?= "^2.5"

# If you see pwd_unknown showing up, this is why. Re-calibrate your system.
PWD ?= pwd_unknown

# PROJECT_NAME defaults to name of the current directory.
PROJECT_NAME = $(notdir $(PWD))

# Suppress makes own output.
.SILENT:

.PHONY: wp_dump \
	cs_sniff \
	cs_fix  \
	cs_fix_n_sniff  \
	ci_before_install  \
	ci_before_script \
	ci_docker_restart \
	ci_install  \
	ci_local_prepare \
	ci_run  \
	ci_script \
	pre_commit \
	require_codeception_2.5 \
	require_codeception_3 \
	phpstan \
	php56

define wp_config_extra
if ( filter_has_var( INPUT_SERVER, 'HTTP_HOST' ) ) {
	if ( ! defined( 'WP_HOME' ) ) {
		define( 'WP_HOME', 'http://' . \$_SERVER['HTTP_HOST'] );
	}
	if ( ! defined( 'WP_SITEURL' ) ) {
		define( 'WP_SITEURL', 'http://' . \$_SERVER['HTTP_HOST'] );
	}
}
endef

# PUll all the Docker images this repository will use in building images or running processes.
docker_pull:
	images=( \
		'texthtml/phpcs' \
		'composer/composer:master-php5' \
		'phpstan/phpstan' \
		'wordpress:cli' \
		'billryan/gitbook' \
		'mhart/alpine-node:11' \
		'php:5.6' \
		'selenium/standalone-chrome' \
		'mariadb:latest' \
		'wordpress:php5.6' \
		'andthensome/alpine-surge-bash' \
		'martin/wait' \
	); \
	for image in "$${images[@]}"; do \
		docker pull "$$image"; \
	done;

# Builds the Docker-based parallel-lint util.
docker/parallel-lint/id:
	docker build --force-rm --iidfile docker/parallel-lint/id docker/parallel-lint --tag lucatume/parallel-lint:5.6

# Lints the source files with PHP Parallel Lint, requires the parallel-lint:5.6 image to be built.
lint: docker/parallel-lint/id
	docker run --rm -v ${CURDIR}:/app lucatume/parallel-lint:5.6 \
		--colors \
		/app/src

cs_sniff:
	vendor/bin/phpcs --colors -p --standard=phpcs.xml $(SRC) --ignore=src/data,src/includes,src/tad/scripts,src/tad/WPBrowser/Compat -s src

cs_fix:
	vendor/bin/phpcbf --colors -p --standard=phpcs.xml $(SRC) --ignore=src/data,src/includes,src/tad/scripts -s src tests

cs_fix_n_sniff: cs_fix cs_sniff

# Updates Composer dependencies using PHP 5.6.
composer_update: composer.json
	docker run --rm -v ${CURDIR}:/app composer/composer:master-php5 update

ci_setup_db:
	# Start just the database container.
	docker-compose -f docker/${COMPOSE_FILE} up -d db
	# Wait until DB is initialized.
	docker-compose -f docker/${COMPOSE_FILE} run --rm waiter
	# Create the databases that will be used in the tests.
	docker-compose -f docker/${COMPOSE_FILE} exec db bash -c 'mysql -u root -e "create database if not exists test_site"'
	docker-compose -f docker/${COMPOSE_FILE} exec db bash -c 'mysql -u root -e "create database if not exists test"'
	docker-compose -f docker/${COMPOSE_FILE} exec db bash -c 'mysql -u root -e "create database if not exists mu_subdir_test"'
	docker-compose -f docker/${COMPOSE_FILE} exec db bash -c 'mysql -u root -e "create database if not exists mu_subdomain_test"'
	docker-compose -f docker/${COMPOSE_FILE} exec db bash -c 'mysql -u root -e "create database if not exists empty"'

ci_setup_wp:
	# Clone WordPress in the vendor folder if not there already.
	if [ ! -d vendor/wordpress/wordpress ]; then mkdir -p vendor/wordpress && git clone https://github.com/WordPress/WordPress.git vendor/wordpress/wordpress; fi
	# Make sure the WordPress folder is write-able.
	sudo chmod -R 0777 vendor/wordpress

ci_before_install: ci_setup_db ci_setup_wp
	# Start the WordPress container.
	docker-compose -f docker/${COMPOSE_FILE} up -d wp
	# Fetch the IP address of the WordPress container in the containers network.
	# Start the Chromedriver container using that information to have the *.wp.test domain bound to the WP container.
	WP_CONTAINER_IP=`docker inspect -f '{{ .NetworkSettings.Networks.docker_default.IPAddress }}' wpbrowser_wp` \
	docker-compose -f docker/${COMPOSE_FILE} up -d chromedriver

ci_conditionals:
	# Remove phpstan dependencies on lower PHP versions.
	if [[ $${TRAVIS_PHP_VERSION:0:3} < "7.1" ]]; then sed -i.bak '/phpstan/d' composer.json; fi
	# Update Composer using the host machine PHP version.
	composer require codeception/codeception:"${CODECEPTION_VERSION}"

ci_install:
	# Copy over the wp-cli.yml configuration file.
	docker cp docker/wp-cli.yml wpbrowser_wp:/var/www/html/wp-cli.yml
	# Copy over the wp-config.php file.
	docker cp docker/wp-config.php wpbrowser_wp:/var/www/html/wp-config.php
	# Install WordPress in multisite mode.
	docker run -it --rm --volumes-from wpbrowser_wp --network container:wpbrowser_wp wordpress:cli wp core multisite-install \
		--url=${TRAVIS_WP_URL} \
		--base=/ \
		--subdomains \
		--title=Test \
		--admin_user=${TRAVIS_WP_ADMIN_USERNAME} \
		--admin_password=${TRAVIS_WP_ADMIN_PASSWORD} \
		--admin_email=admin@${TRAVIS_WP_DOMAIN} \
		--skip-email \
		--skip-config
	# Copy over the multisite htaccess file.
	docker cp docker/htaccess wpbrowser_wp:/var/www/html/.htaccess
	# Create sub-domain 1.
	docker run -it --rm --volumes-from wpbrowser_wp --network container:wpbrowser_wp wordpress:cli wp site create \
		--slug=${TRAVIS_WP_SUBDOMAIN_1} \
		--title=${TRAVIS_WP_SUBDOMAIN_1_TITLE}
	# Create sub-domain 2.
	docker run -it --rm --volumes-from wpbrowser_wp --network container:wpbrowser_wp wordpress:cli wp site create \
		--slug=${TRAVIS_WP_SUBDOMAIN_2} \
		--title=${TRAVIS_WP_SUBDOMAIN_2_TITLE}
	# Update WordPress database to avoid prompts.
	docker run -it --rm --volumes-from wpbrowser_wp --network container:wpbrowser_wp wordpress:cli wp core update-db \
		--network
	# Empty the main site of all content.
	docker run -it --rm --volumes-from wpbrowser_wp --network container:wpbrowser_wp wordpress:cli wp site empty --yes
	# Install the Airplane Mode plugin to speed up the Driver tests.
	if [ ! -d vendor/wordpress/wordpress/wp-content/plugins/airplane-mode ]; then \
		git clone https://github.com/norcross/airplane-mode.git \
			vendor/wordpress/wordpress/wp-content/plugins/airplane-mode; \
	fi
	docker run -it --rm --volumes-from wpbrowser_wp --network container:wpbrowser_wp wordpress:cli wp plugin activate airplane-mode
	# Make sure everyone can write to the tests/_data folder.
	sudo chmod -R 777 tests/_data
	# Export a dump of the just installed database to the _data folder of the project.
	docker run -it --rm --volumes-from wpbrowser_wp --network container:wpbrowser_wp wordpress:cli wp db export \
		/project/tests/_data/dump.sql

ci_before_script:
	# Build Codeception modules.
	vendor/bin/codecept build

ci_script:
	vendor/bin/codecept run acceptance
	vendor/bin/codecept run cli
	vendor/bin/codecept run climodule
	if [[ $${TRAVIS_PHP_VERSION:0:3} > "5.6" && $${CODECEPTION_VERSION:1:3} > "2.5" ]]; then \
		vendor/bin/codecept run command; \
	fi
	vendor/bin/codecept run functional
	vendor/bin/codecept run muloader
	if [[ $${CODECEPTION_VERSION:1:3} == "2.5" ]]; then \
		rm tests/unit/tad/WPBrowser/Traits/WithEventsTest.php; \
	fi
	vendor/bin/codecept run unit
	vendor/bin/codecept run webdriver
	vendor/bin/codecept run wpfunctional
	vendor/bin/codecept g:wpunit wploadersuite UnitWrapping
	vendor/bin/codecept g:wpunit wploadersuite WPAjaxTestCaseTest
	vendor/bin/codecept g:wpunit wploadersuite WPCanonicalTestCaseTest
	vendor/bin/codecept g:wpunit wploadersuite WPRestControllerTestCaseTest
	vendor/bin/codecept g:wpunit wploadersuite WPXMLRPCTestCaseTest
	vendor/bin/codecept run wploadersuite
	vendor/bin/codecept run wploader_multisite
	vendor/bin/codecept run wpmodule
	vendor/bin/codecept run wploader_wpdb_interaction
	docker-compose -f test_runner.compose.yml run waiter
	docker-compose -f test_runner.compose.yml run test_runner bash -c 'cd /project; vendor/bin/codecept run wpcli_module'
	if [[ $${TRAVIS_PHP_VERSION:0:3} > "7.0" ]]; then \
		STATIC_ANALYSIS=1 vendor/bin/phpstan analyze -l max; \
	fi

# Restarts the project containers.
ci_docker_restart:
	docker-compose -f docker/${COMPOSE_FILE} restart

# Make sure the host machine can ping the WordPress container
ensure_pingable_hosts:
	set -o allexport &&  source .env.testing &&  set +o allexport && \
	echo $${TEST_HOSTS} | \
	sed -e $$'s/ /\\\n/g' | while read host; do echo "\nPinging $${host}" && ping -c 1 "$${host}"; done

ci_prepare: ci_before_install ensure_pingable_hosts ci_install ci_before_script

ci_local_prepare: sync_hosts_entries ci_before_install ensure_pingable_hosts ci_install ci_before_script

ci_run: lint sniff ci_prepare ci_script

# Gracefully stop the Docker containers used in the tests.
down:
	docker-compose -f docker/docker-compose.yml down

# Builds the Docker-based markdown-toc util.
docker/markdown-toc/id:
	docker build --force-rm --iidfile docker/markdown-toc/id docker/markdown-toc --tag lucatume/md-toc:latest

# Re-builds the Readme ToC.
toc: docker/markdown-toc/id
	docker run --rm -it -v ${CURDIR}:/project lucatume/md-toc markdown-toc -i /project/README.md

# Produces the Modules documentation in the docs/modules folder.
module_docs: composer.lock src/Codeception/Module
	mkdir -p docs/modules
	for file in ${CURDIR}/src/Codeception/Module/*.php; \
	do \
		name=$$(basename "$${file}" | cut -d. -f1); \
		if	[ $${name} = "WPBrowserMethods" ]; then \
			continue; \
		fi; \
		class="Codeception\\Module\\$${name}"; \
		file=${CURDIR}/docs/modules/$${name}.md; \
		if [ ! -f $${file} ]; then \
			echo "<!--doc--><!--/doc-->" > $${file}; \
		fi; \
		echo "Generating documentation for module $${class} in file $${file}..."; \
		docs/bin/wpbdocmd generate \
			--visibility=public \
			--methodRegex="/^[^_]/" \
			--tableGenerator=tad\\WPBrowser\\Documentation\\TableGenerator \
			$${class} > doc.tmp; \
		if [ 0 != $$? ]; then rm doc.tmp && exit 1; fi; \
		echo "${CURDIR}/doc.tmp $${file}" | xargs php ${CURDIR}/docs/bin/update_doc.php; \
		rm doc.tmp; \
	done;

docker/gitbook/id:
	docker build --force-rm --iidfile docker/gitbook/id docker/gitbook --tag lucatume/gitbook:latest

duplicate_gitbook_files:
	cp ${CURDIR}/docs/welcome.md ${CURDIR}/docs/README.md

gitbook_install: docs/node_modules
	docker run --rm -v "${CURDIR}/docs:/gitbook" lucatume/gitbook gitbook install

gitbook_serve: docker/gitbook/id duplicate_gitbook_files module_docs gitbook_install
	docker run --rm -v "${CURDIR}/docs:/gitbook" -p 4000:4000 -p 35729:35729 lucatume/gitbook gitbook serve --live

gitbook_build: docker/gitbook/id duplicate_gitbook_files module_docs gitbook_install
	docker run --rm -v "${CURDIR}/docs:/gitbook" lucatume/gitbook gitbook build . /site
	rm -rf ${CURDIR}/docs/site/bin

remove_hosts_entries:
	echo "Removing project ${PROJECT_NAME} hosts entries (and backing up /etc/hosts to /etc/hosts.orig...)"
	sudo sed -i.orig '/^## ${PROJECT_NAME} project - Start ##/,/## ${PROJECT_NAME} project - End ##$$/d' /etc/hosts

sync_hosts_entries: remove_hosts_entries
	echo "Adding project ${project} hosts entries..."
	set -o allexport &&  source .env.testing &&  set +o allexport && \
	sudo -- sh -c "echo '## ${PROJECT_NAME} project - Start ##' >> /etc/hosts" && \
	sudo -- sh -c "echo '127.0.0.1 $${TEST_HOSTS}' >> /etc/hosts" && \
	sudo -- sh -c "echo '## ${PROJECT_NAME} project - End ##' >> /etc/hosts"

# Export a dump of WordPressdatabase to the _data folder of the project.
wp_dump:
	docker run -it --rm --volumes-from wpbrowser_wp --network container:wpbrowser_wp wordpress:cli wp db export \
		/project/tests/_data/dump.sql

pre_commit: lint cs_sniff

require_codeception_2.5:
	mv vendor/wordpress _wordpress
	rm -rf composer.lock vendor && composer require codeception/codeception:^2.5
	mv _wordpress vendor/wordpress

require_codeception_3:
	mv vendor/wordpress _wordpress
	rm -rf composer.lock vendor && composer require codeception/codeception:^3.0
	mv _wordpress vendor/wordpress

require_phpunit_8:
	mv vendor/wordpress _wordpress
	rm -rf composer.lock vendor && composer require codeception/codeception:^3.0 phpunit/phpunit:^8.0
	mv _wordpress vendor/wordpress

phpstan:
	vendor/bin/phpstan analyze -l max

clean:
	rm -rf *.bak
	rm -rf *.ready

5.6.cc.3.0.ready:
	# Backup the current vendor and Composer files.
	test -d vendor && mv vendor vendor.bak ||  echo "No vendor to backup."
	test -f composer.lock && mv composer.lock composer.lock.bak || echo "No composer.lock to backup."
	test -f composer.json && cp composer.json composer.json.bak || (echo "composer.json file not found, stopping."; exit 1)
	# Remove scripts entries.
	docker run --rm -v "${PWD}:/project" stedolan/jq 'del(.scripts)' /project/composer.json > composer.json.tmp
	rm composer.json && mv composer.json.tmp composer.json
	# Remove phpstan dependencies on lower PHP versions.
	sed -i.bak '/phpstan/d' composer.json
	# Update composer dependencies using PHP 5.6.
	docker run --rm  \
		--user $$(id -u):$$(id -g) \
		-v "$${HOME}/.composer/auth.json:/root/.composer/auth.json" \
		-v "${PWD}:/project" \
		lucatume/composer:php5.6 require codeception/codeception:^3.0
	test -d vendor/wordpress/wordpress || mkdir -p vendor/wordpress/wordpress
	test $(find . -name *.ready) && rm *.ready || echo "No .ready files found."
	docker-compose --project-name php_5.6_cc_3.0 down
	touch 5.6.cc.3.0.ready

5.6.cc.2.5.ready:
	# Backup the current vendor and Composer files.
	test -d vendor && mv vendor vendor.bak ||  echo "No vendor to backup."
	test -f composer.lock && mv composer.lock composer.lock.bak || echo "No composer.lock to backup."
	test -f composer.json && cp composer.json composer.json.bak || (echo "composer.json file not found, stopping."; exit 1)
	# Remove scripts entries.
	docker run --rm -v "${PWD}:/project" stedolan/jq 'del(.scripts)' /project/composer.json > composer.json.tmp
	rm composer.json && mv composer.json.tmp composer.json
	# Remove phpstan dependencies on lower PHP versions.
	sed -i.bak '/phpstan/d' composer.json
	# Update composer dependencies using PHP 5.6.
	docker run --rm  \
		--user $$(id -u):$$(id -g) \
		-v "$${HOME}/.composer/auth.json:/root/.composer/auth.json" \
		-v "${PWD}:/project" \
		lucatume/composer:php5.6 require codeception/codeception:^3.0
	test -d vendor/wordpress/wordpress || mkdir -p vendor/wordpress/wordpress
	test $(find . -name *.ready) && rm *.ready || echo "No .ready files found."
	docker-compose --project-name php_5.6_cc_2.5 down
	touch 5.6.cc.2.5.ready

php_5.6_cc_3.0:
	docker-compose --project-name php_5.6_cc_3.0 run --rm codeception run wploader_wpdb_interaction --debug
	docker-compose --project-name php_5.6_cc_3.0 run --rm codeception run acceptance --debug

php_5.6_cc_2.5:
	docker-compose --project-name php_5.6_cc_2.5 run --rm codeception run wploader_wpdb_interaction --debug
	docker-compose --project-name php_5.6_cc_2.5 run --rm codeception run acceptance --debug

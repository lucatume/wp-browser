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

docker/parallel-lint/id:
	# Builds the images required by the Docker-based utils like parallel-lint and so on.
	docker build --iidfile ./docker/parallel-lint/id ./docker/parallel-lint --tag parallel-lint:5.6

lint: docker/parallel-lint/id src
	# Lints the source files with PHP Parallel Lint, requires the parallel-lint:5.6 image to be built.
	docker run --rm -v ${CURDIR}:/app parallel-lint:5.6 --colors /app/src

fix: src
	# Fix the source files code style using PHP_CodeSniffer and PSR-2 standards.
	docker run --rm -v ${CURDIR}/src:/scripts/ texthtml/phpcs phpcbf \
		--standard=/scripts/phpcs.xml \
		--ignore=data,includes,tad/scripts \
		/scripts

sniff: fix src
	# Sniff the source files code style using PHP_CodeSniffer and PSR-2 standards.
	docker run --rm -v ${CURDIR}/src:/scripts/ texthtml/phpcs phpcs \
		--standard=/scripts/phpcs.xml \
		--ignore=data,includes,tad/scripts \
		/scripts -s

composer_update: composer.json
	# Updates Composer dependencies using PHP 5.6.
	docker run --rm -v ${CURDIR}:/app composer/composer:master-php5 update
phpstan: src
	# Runs phpstan on the source files.
	docker run --rm -v ${CURDIR}:/app phpstan/phpstan analyse -l 5 /app/src/Codeception /app/src/tad

travis_before_install:
	# Clone WordPress in the vendor folder if not there already.
	if [ ! -d vendor/wordpress/wordpress ]; then mkdir -p vendor/wordpress && git clone https://github.com/WordPress/WordPress.git vendor/wordpress/wordpress; fi
	# Make sure the WordPress folder is write-able.
	sudo chmod -R 0777 vendor/wordpress
	# Start just the database container.
	docker-compose -f docker/${COMPOSE_FILE} up -d db
	# Give the DB container some time to initialize.
	sleep 10
	# Create the databases that will be used in the tests.
	docker-compose -f docker/${COMPOSE_FILE} exec db bash -c 'mysql -u root -e "create database if not exists test_site"'
	docker-compose -f docker/${COMPOSE_FILE} exec db bash -c 'mysql -u root -e "create database if not exists test"'
	# Start the WordPress container.
	docker-compose -f docker/${COMPOSE_FILE} up -d wp
	# Fetch the IP address of the WordPress container in the containers network.
	# Start the Chromedriver container using that information to have the *.wp.test domain bound to the WP container.
	WP_CONTAINER_IP=`docker inspect -f '{{ .NetworkSettings.Networks.docker_default.IPAddress }}' wpbrowser_wp` \
	docker-compose -f docker/${COMPOSE_FILE} up -d chromedriver
	# Make sure the Chromedriver container can ping the WordPress one.
	docker-compose -f docker/${COMPOSE_FILE} exec chromedriver ping -c 1 wp.test
	docker-compose -f docker/${COMPOSE_FILE} exec chromedriver ping -c 1 test1.wp.test
	docker-compose -f docker/${COMPOSE_FILE} exec chromedriver ping -c 1 test2.wp.test
	# Make sure the host machine can ping the WordPress container
	ping -c 1 wp.test
	ping -c 1 test1.wp.test
	ping -c 1 test2.wp.test

travis_install:
	# Update Composer using the host machine PHP version.
	composer update --prefer-dist
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
	sudo chmod -R 775 tests/_data
	# Export a dump of the just installed database to the _data folder of the project.
	docker run -it --rm --volumes-from wpbrowser_wp --network container:wpbrowser_wp wordpress:cli wp db export \
		/project/tests/_data/dump.sql

travis_before_script:
	# Build Codeception modules.
	codecept build

travis_script:
	codecept run acceptance
	codecept run cli
	codecept run climodule
	codecept run functional
	codecept run muloader/airplane-mode
	codecept run unit
	codecept run webdriver
	codecept run wpfunctional
	codecept run wploadersuite
	codecept run wpmodule

travis_prepare: travis_before_install travis_install travis_before_script

travis_run: travis_prepare travis_script

down:
	# Gracefully stop the Docker containers.
	docker-compose -f docker/docker-compose.yml down

.SILENT:
SHELL := /bin/bash

# Vars
CONTAINERS_VERSION = 4.0.0-dev
PROJECT_NAME = $(notdir $(PWD))
PHP_VERSION ?= 8.0
TTY_FLAG := $(shell [ -t 0 ] && echo '-t')
WORDPRESS_VERSION ?= latest
WORDPRESS_BASE_IMAGE ?= wordpress:apache
WORDPRESS_LOCALHOST_PORT ?= 3380
UID ?= $(shell id -u)
GID ?= $(shell id -g)
CHROME_LOCALHOST_PORT ?= 34444
CHROME_LOCALHOST_VNC_PORT ?= 37900
DB_LOCALHOST_PORT ?= 33306
ROOT ?= 0

ifeq ($(ROOT),1)
USER_OPTION ?= --user "0:0"
else
USER_OPTION ?= --user "$(shell id -u):$(shell id -g)"
endif

UNAME_S := $(shell uname -s)
ifeq ($(UNAME), Linux)
define host_ip
$(shell docker run --rm --entrypoint sh busybox -c '/bin/ip route | awk "/default/ { print $$3 }" | cut -d" " -f3')
endef
else
define host_ip
host.docker.internal
endef
endif


.PHONY: build
build: _build/_container/php/iidfile _build/_container/wordpress/iidfile up build_db healthcheck .env.testing

define ENV_TESTING_FILE_CONTENTS
WORDPRESS_ROOT_DIR=vendor/wordpress/wordpress
WORDPRESS_URL=http://wordpress.test
WORDPRESS_DOMAIN=wordpress.test
WORDPRESS_ADMIN_USER=admin
WORDPRESS_ADMIN_PASSWORD=admin
WORDPRESS_DB_HOST=db
WORDPRESS_DB_NAME=test
WORDPRESS_DB_USER=test
WORDPRESS_DB_PASSWORD=test
WORDPRESS_TABLE_PREFIX=wp_
WORDPRESS_SUBDOMAIN_URL=http://sub1.wordpresss.test
WORDPRESS_SUBDOMAIN_DB_NAME=subdomain_test
WORDPRESS_SUBDIR_URL=http://wordpress.test/test-1
WORDPRESS_SUBDIR_DB_NAME=subdir_test
WORDPRESS_EMPTY_DB_NAME=empty
CHROMEDRIVER_HOST=chrome
CHROMEDRIVER_PORT=4444
endef
export ENV_TESTING_FILE_CONTENTS

.env.testing:
	echo "$${ENV_TESTING_FILE_CONTENTS}" > .env.testing

_build/_container/php/iidfile:
	docker build \
		--build-arg PHP_VERSION=$(PHP_VERSION) \
		--label "project=wp-browser" \
		--label "service=php" \
		--iidfile $(PWD)/_build/_container/php/iidfile \
		--tag lucatume/wp-browser_php_$(PHP_VERSION):latest \
		--tag lucatume/wp-browser_php_$(PHP_VERSION):$(CONTAINERS_VERSION) \
		$(PWD)/_build/_container/php

.PHONY: php_container_build
php_container_build: _build/_container/php/iidfile

_build/_container/wordpress/iidfile:
	docker build \
		--build-arg WORDPRESS_BASE_IMAGE=$(WORDPRESS_BASE_IMAGE) \
		--label "project=wp-browser" \
		--label "service=wordpress" \
		--iidfile $(PWD)/_build/_container/wordpress/iidfile \
		--tag lucatume/wp-browser_wordpress:latest \
		--tag lucatume/wp-browser_wordpress:$(CONTAINERS_VERSION) \
		$(PWD)/_build/_container/wordpress

.PHONY: wordpress_container_build
wordpress_container_build: _build/_container/wordpress/iidfile

.PHONY: network_up
network_up:
	$(if \
		$(shell docker network ls -q --filter name=wp-browser), \
			, \
			docker network create \
				--attachable \
				--label "project=wp-browser" \
				wp-browser \
	)

.PHONY: database_up
database_up: network_up
	$(if \
		$(shell docker ps -q --filter "name=wp-browser_db"), \
		, \
		$(if $(shell docker ps -aq --filter "name=wp-browser_db"), \
			docker restart wp-browser_db, \
			docker run --detach --name wp-browser_db \
				--network wp-browser \
				--network-alias db \
				--label "project=wp-browser" \
				--label "service=mysql" \
				--env MYSQL_USER=test \
				--env MYSQL_PASSWORD=test \
				--env MYSQL_ROOT_PASSWORD=password \
				--env MYSQL_DATABASE=test \
				--publish "$(DB_LOCALHOST_PORT):3306" \
				--health-cmd 'mysqlshow -uroot -ppassword test' \
				--health-interval 1s \
				--health-retries 30 \
				--health-timeout 1s \
				mariadb:latest \
		 ) \
	)
	T=$$(($$(date +"%s") + 30)); until [ "$$(docker inspect -f {{.State.Health.Status}} wp-browser_db)" = "healthy" ]; do \
		sleep 2; \
		echo "Waiting for database ready ..."; \
		if [ $$(date +"%s") -gt $${T} ]; then echo "Database timed out"; exit 1; fi; \
	done;
	docker exec wp-browser_db mysql -uroot -ppassword -e "create database if not exists subdir_test"
	docker exec wp-browser_db mysql -uroot -ppassword -e "create database if not exists subdomain_test"

.PHONY: php_container_up
php_container_up: _build/_container/php/iidfile network_up
	mkdir -p $(PWD)/.cache/composer
	$(if \
		$(shell docker ps -q --filter "name=wp-browser_php_$(PHP_VERSION)"), \
		, \
		$(if $(shell docker ps -aq --filter "name=wp-browser_php_$(PHP_VERSION)"), \
			docker restart wp-browser_php_$(PHP_VERSION), \
			docker run --detach --name wp-browser_php_$(PHP_VERSION) \
				--label "project=wp-browser" \
				--label "service=php" \
				--label "php_version=$(PHP_VERSION)" \
				--network wp-browser \
				--network-alias php_$(PHP_VERSION) \
				--volume "$(PWD):$(PWD)" \
				--workdir "$(PWD)" \
				--user "$(UID):$(GID)" \
				--env XDEBUG_CONFIG="idekey=wp-browser client_host=$(call host_ip) client_port=9003 start_with_request=yes log_level=0" \
				--env COMPOSER_CACHE_DIR="$(PWD)/.cache/composer" \
				lucatume/wp-browser_php_$(PHP_VERSION) \
		) \
	)

.PHONY: up
up: network_up database_up php_container_up wordpress_up chromedriver_up

.PHONY: down
down:
	$(if $(shell docker ps -aq --filter "label=project=wp-browser"), \
		docker rm --force $$(docker ps -aq --filter "label=project=wp-browser"))
	$(if $(shell docker network ls -q --filter label=project=wp-browser), \
		docker network rm $$(docker network ls -q --filter label=project=wp-browser))

.PHONY: clean_output
clean_output:
	rm -rf tests/_output && mkdir tests/_output

.PHONY: clean_tmp
clean_tmp:
	rm -rf tests/_output/tmp && mkdir tests/_output/tmp

.PHONY: clean
clean: down clean_output
	rm -f _build/_container/php/iidfile
	rm -f _build/_container/wordpress/iidfile
	rm -rf vendor/wordpress/wordpress
	rm -f .env.testing

.PHONY: config
config:
	echo "CONTAINERS_VERSION => $(CONTAINERS_VERSION)"
	echo "PROJECT_NAME => $(PROJECT_NAME)"
	echo "PHP_VERSION => $(PHP_VERSION)"
	echo "TTY_FLAG => $(TTY_FLAG)"
	echo "WORDPRESS_VERSION => $(WORDPRESS_VERSION)"
	echo "WORDPRESS_BASE_IMAGE => $(WORDPRESS_BASE_IMAGE)"
	echo "WORDPRESS_LOCALHOST_PORT => $(WORDPRESS_LOCALHOST_PORT)"
	echo "UID => $(UID)"
	echo "GID => $(GID)"
	echo "host IP from container => $(call host_ip)"
	echo "CHROME_LOCALHOST_PORT => $(CHROME_LOCALHOST_PORT)"
	echo "CHROME_LOCALHOST_VNC_PORT => $(CHROME_LOCALHOST_VNC_PORT)"
	echo "DB_LOCALHOST_PORT => $(DB_LOCALHOST_PORT)"
	echo "ROOT => $(ROOT)"
	echo "USER_OPTION => $(USER_OPTION)"

.PHONY: ssh
ssh: php_container_up
	docker exec -it $(USER_OPTION) wp-browser_php_$(PHP_VERSION) bash

.PHONY: composer_version
composer_version: network_up php_container_up
	docker exec $(TTY_FLAG) -u "$(shell id -u):$(shell id -g)" wp-browser_php_$(PHP_VERSION) composer --version

.PHONY: composer_install
composer_install: network_up php_container_up
	docker exec $(TTY_FLAG) -u "$(shell id -u):$(shell id -g)" wp-browser_php_$(PHP_VERSION) composer install

.PHONY: composer_update
composer_update: network_up php_container_up
	docker exec $(TTY_FLAG) -u "$(shell id -u):$(shell id -g)" wp-browser_php_$(PHP_VERSION) composer update

.PHONY: wp_cli_version
wp_cli_version:
	docker exec $(TTY_FLAG) -u "$(shell id -u):$(shell id -g)" wp-browser_php_$(PHP_VERSION) wp --version

define wordpress_container_ip
$(shell docker inspect --format='{{range .NetworkSettings.Networks}}{{.IPAddress}}{{end}}' wp-browser_wordpress)
endef

define HTACCESS_CONTENTS
# BEGIN WordPress Multisite
# Using subfolder network type: https://wordpress.org/support/article/htaccess/#multisite

RewriteEngine On
RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]
RewriteBase /
RewriteRule ^index\.php$$ - [L]

# add a trailing slash to /wp-admin
RewriteRule ^([_0-9a-zA-Z-]+/)?wp-admin$$ $$1wp-admin/ [R=301,L]

RewriteCond %{REQUEST_FILENAME} -f [OR]
RewriteCond %{REQUEST_FILENAME} -d
RewriteRule ^ - [L]
RewriteRule ^([_0-9a-zA-Z-]+/)?(wp-(content|admin|includes).*) $$2 [L]
RewriteRule ^([_0-9a-zA-Z-]+/)?(.*\.php)$$ $$2 [L]
RewriteRule . index.php [L]

# END WordPress Multisite
endef
export HTACCESS_CONTENTS

.PHONY: wordpress_up
wordpress_up: network_up php_container_up database_up
	if [ ! -f vendor/wordpress/wordpress/wp-load.php ]; then \
		mkdir -p vendor/wordpress/wordpress; \
		docker exec $(TTY_FLAG) -u "$(UID):$(GID)" \
			--workdir "$(PWD)/vendor/wordpress/wordpress" \
			wp-browser_php_$(PHP_VERSION) \
			wp core download --version=$(WORDPRESS_VERSION); \
	fi
	if [ ! -f vendor/wordpress/wordpress/wp-config.php ]; then \
		docker exec $(TTY_FLAG) -u "$(UID):$(GID)" \
			--workdir "$(PWD)/vendor/wordpress/wordpress" \
			wp-browser_php_$(PHP_VERSION) \
			wp config create \
				--dbname=test \
				--dbuser=test \
				--dbpass=test \
				--dbhost=db \
				--dbprefix=wp_; \
	fi
	docker exec $(TTY_FLAG) -u "$(UID):$(GID)" \
		--workdir "$(PWD)/vendor/wordpress/wordpress" \
		wp-browser_php_$(PHP_VERSION) \
		bash -c 'if ! wp core is-installed --network; then \
			wp core multisite-install --url=http://wordpress.test \
			--title=Test --admin_user=admin --admin_password=admin \
			--admin_email=admin@wordpress.test --skip-email; \
			fi'
	echo "$${HTACCESS_CONTENTS}" > "$(PWD)/vendor/wordpress/wordpress/.htaccess"
	$(if \
		$(shell docker ps -q --filter "name=wp-browser_wordpress"), \
		, \
		$(if \
			$(shell docker ps -aq --filter "name=wp-browser_wordpress"), \
				docker restart wp-browser_wordpress, \
				docker run --detach --name wp-browser_wordpress \
					--label "project=wp-browser" \
					--label "service=apache" \
					--label "php_version=$(PHP_VERSION)" \
					--network wp-browser \
					--network-alias apache_php_$(PHP_VERSION) \
					--volume "$(PWD)/vendor/wordpress/wordpress:/var/www/html" \
					--user "$(UID):$(GID)" \
					--publish "$(WORDPRESS_LOCALHOST_PORT):80" \
					--env XDEBUG_CONFIG="idekey=wp-browser-apache client_host=$(call host_ip) client_port=9004 log_level=0" \
					lucatume/wp-browser_wordpress:latest \
		) \
	)
	export _IP=$$(docker inspect --format='{{range .NetworkSettings.Networks}}{{.IPAddress}}{{end}}' wp-browser_wordpress) && \
		docker exec -u 0 wp-browser_php_$(PHP_VERSION) bash -c "echo '$${_IP} wordpress.test' >> /etc/hosts" && \
		docker exec -u 0 wp-browser_php_$(PHP_VERSION) bash -c "echo '$${_IP} test1.wordpress.test' >> /etc/hosts" && \
		docker exec -u 0 wp-browser_php_$(PHP_VERSION) bash -c "echo '$${_IP} test2.wordpress.test' >> /etc/hosts" && \
		docker exec -u 0 wp-browser_php_$(PHP_VERSION) bash -c "echo '$${_IP} sub1.wordpress.test' >> /etc/hosts" && \
		docker exec -u 0 wp-browser_php_$(PHP_VERSION) bash -c "echo '$${_IP} blog0.wordpress.test' >> /etc/hosts" && \
		docker exec -u 0 wp-browser_php_$(PHP_VERSION) bash -c "echo '$${_IP} blog1.wordpress.test' >> /etc/hosts" && \
		docker exec -u 0 wp-browser_php_$(PHP_VERSION) bash -c "echo '$${_IP} blog2.wordpress.test' >> /etc/hosts" && \
		docker exec -u 0 wp-browser_wordpress bash -c "echo '$${_IP} wordpress.test' >> /etc/hosts" && \
		docker exec -u 0 wp-browser_wordpress bash -c "echo '$${_IP} test1.wordpress.test' >> /etc/hosts" && \
		docker exec -u 0 wp-browser_wordpress bash -c "echo '$${_IP} test2.wordpress.test' >> /etc/hosts" && \
		docker exec -u 0 wp-browser_wordpress bash -c "echo '$${_IP} sub1.wordpress.test' >> /etc/hosts" && \
		docker exec -u 0 wp-browser_wordpress bash -c "echo '$${_IP} blog0.wordpress.test' >> /etc/hosts" && \
		docker exec -u 0 wp-browser_wordpress bash -c "echo '$${_IP} blog1.wordpress.test' >> /etc/hosts" && \
		docker exec -u 0 wp-browser_wordpress bash -c "echo '$${_IP} blog2.wordpress.test' >> /etc/hosts"

.PHONY: chromedriver_up
chromedriver_up: wordpress_up
	$(if \
		$(shell docker ps -q --filter "name=wp-browser_chrome"), \
		, \
		$(if \
			$(shell docker ps -aq --filter "name=wp-browser_chrome" ), \
				docker restart wp-browser_chrome, \
				docker run --detach \
					--name wp-browser_chrome \
					--label "project=wp-browser" \
					--label "service=chrome" \
					--add-host "wordpress.test:$(call wordpress_container_ip)" \
					--network wp-browser \
					--network-alias chrome \
					--publish "$(CHROME_LOCALHOST_PORT):4444" \
					--publish "$(CHROME_LOCALHOST_VNC_PORT):7900" \
					--shm-size 3g \
					seleniarm/standalone-chromium:101.0 \
		) \
	)
	export _IP=$$(docker inspect --format='{{range .NetworkSettings.Networks}}{{.IPAddress}}{{end}}' wp-browser_wordpress) && \
		docker exec -u 0 wp-browser_chrome bash -c "echo '$${_IP} wordpress.test' >> /etc/hosts" && \
		docker exec -u 0 wp-browser_chrome bash -c "echo '$${_IP} test1.wordpress.test' >> /etc/hosts" && \
		docker exec -u 0 wp-browser_chrome bash -c "echo '$${_IP} test2.wordpress.test' >> /etc/hosts" && \
		docker exec -u 0 wp-browser_chrome bash -c "echo '$${_IP} sub1.wordpress.test' >> /etc/hosts" && \
		docker exec -u 0 wp-browser_chrome bash -c "echo '$${_IP} blog0.wordpress.test' >> /etc/hosts" && \
		docker exec -u 0 wp-browser_chrome bash -c "echo '$${_IP} blog1.wordpress.test' >> /etc/hosts" && \
		docker exec -u 0 wp-browser_chrome bash -c "echo '$${_IP} blog2.wordpress.test' >> /etc/hosts"

.PHONY: ps
ps:
	docker ps -a --filter label=project=wp-browser --filter status=running

.PHONY: build_db
build_db:
	docker exec wp-browser_php_$(PHP_VERSION) bash -c "mysql -uroot -ppassword -hdb -e \"CREATE DATABASE IF NOT EXISTS test\""
	docker exec wp-browser_php_$(PHP_VERSION) bash -c "mysql -uroot -ppassword -hdb -e \"CREATE DATABASE IF NOT EXISTS subdomain_test\""
	docker exec wp-browser_php_$(PHP_VERSION) bash -c "mysql -uroot -ppassword -hdb -e \"CREATE DATABASE IF NOT EXISTS subdir_test\""
	docker exec wp-browser_php_$(PHP_VERSION) bash -c "mysql -uroot -ppassword -hdb -e \"CREATE DATABASE IF NOT EXISTS empty\""
	docker exec wp-browser_php_$(PHP_VERSION) bash -c "mysql -uroot -ppassword -hdb -e \"GRANT ALL ON *.* TO 'test'@'%'\""

.PHONY: healthcheck
healthcheck:
	echo -n "PHP container can reach WordPress container at wordpress.test ... "
	docker exec wp-browser_php_$(PHP_VERSION) bash -c 'curl -Ifs http://wordpress.test > /dev/null'
	echo "yes"
	echo -n "PHP container can reach WordPress container at test1.wordpress.test ... "
	docker exec wp-browser_php_$(PHP_VERSION) bash -c 'curl -Ifs http://test1.wordpress.test > /dev/null'
	echo "yes"
	echo -n "PHP container can reach WordPress container at test2.wordpress.test ... "
	docker exec wp-browser_php_$(PHP_VERSION) bash -c 'curl -Ifs http://test2.wordpress.test > /dev/null'
	echo "yes"
	echo -n "PHP container can reach WordPress container at sub1.wordpress.test ... "
	docker exec wp-browser_php_$(PHP_VERSION) bash -c 'curl -Ifs http://sub1.wordpress.test > /dev/null'
	echo "yes"
	echo -n "PHP container can reach WordPress container at blog0.wordpress.test ... "
	docker exec wp-browser_php_$(PHP_VERSION) bash -c 'curl -Ifs http://blog0.wordpress.test > /dev/null'
	echo "yes"
	echo -n "PHP container can reach WordPress container at blog1.wordpress.test ... "
	docker exec wp-browser_php_$(PHP_VERSION) bash -c 'curl -Ifs http://blog1.wordpress.test > /dev/null'
	echo "yes"
	echo -n "PHP container can reach WordPress container at blog2.wordpress.test ... "
	docker exec wp-browser_php_$(PHP_VERSION) bash -c 'curl -Ifs http://blog2.wordpress.test > /dev/null'
	echo "yes"
	echo -n "WordPress container can reach WordPress container at wordpress.test ... "
	docker exec wp-browser_wordpress bash -c 'curl -Ifs http://wordpress.test > /dev/null'
	echo "yes"
	echo -n "WordPress container can reach WordPress container at test1.wordpress.test ... "
	docker exec wp-browser_wordpress bash -c 'curl -Ifs http://test1.wordpress.test > /dev/null'
	echo "yes"
	echo -n "WordPress container can reach WordPress container at test2.wordpress.test ... "
	docker exec wp-browser_wordpress bash -c 'curl -Ifs http://test2.wordpress.test > /dev/null'
	echo "yes"
	echo -n "WordPress container can reach WordPress container at sub1.wordpress.test ... "
	docker exec wp-browser_wordpress bash -c 'curl -Ifs http://sub1.wordpress.test > /dev/null'
	echo "yes"
	echo -n "WordPress container can reach WordPress container at blog0.wordpress.test ... "
	docker exec wp-browser_wordpress bash -c 'curl -Ifs http://blog0.wordpress.test > /dev/null'
	echo "yes"
	echo -n "WordPress container can reach WordPress container at blog1.wordpress.test ... "
	docker exec wp-browser_wordpress bash -c 'curl -Ifs http://blog1.wordpress.test > /dev/null'
	echo "yes"
	echo -n "WordPress container can reach WordPress container at blog2.wordpress.test ... "
	docker exec wp-browser_wordpress bash -c 'curl -Ifs http://blog2.wordpress.test > /dev/null'
	echo "yes"
	echo -n "Chrome  container can reach WordPress container at wordpress.test ... "
	docker exec wp-browser_chrome bash -c 'curl -Ifs http://wordpress.test > /dev/null'
	echo "yes"
	echo -n "Chrome container can reach WordPress container at test1.wordpress.test ... "
	docker exec wp-browser_chrome bash -c 'curl -Ifs http://test1.wordpress.test > /dev/null'
	echo "yes"
	echo -n "Chrome container can reach WordPress container at test2.wordpress.test ... "
	docker exec wp-browser_chrome bash -c 'curl -Ifs http://test2.wordpress.test > /dev/null'
	echo "yes"
	echo -n "Chrome container can reach WordPress container at sub1.wordpress.test ... "
	docker exec wp-browser_chrome bash -c 'curl -Ifs http://sub1.wordpress.test > /dev/null'
	echo "yes"
	echo -n "Chrome container can reach WordPress container at blog0.wordpress.test ... "
	docker exec wp-browser_chrome bash -c 'curl -Ifs http://blog0.wordpress.test > /dev/null'
	echo "yes"
	echo -n "Chrome container can reach WordPress container at blog1.wordpress.test ... "
	docker exec wp-browser_chrome bash -c 'curl -Ifs http://blog1.wordpress.test > /dev/null'
	echo "yes"
	echo -n "Chrome container can reach WordPress container at blog2.wordpress.test ... "
	docker exec wp-browser_chrome bash -c 'curl -Ifs http://blog2.wordpress.test > /dev/null'
	echo "yes"
	echo -n "PHP container can reach database test... "
	docker exec wp-browser_php_$(PHP_VERSION) bash -c "mysql -utest -ptest -hdb -e \"show databases like 'test'\" | grep 'test' > /dev/null"
	echo "yes"
	echo -n "PHP container can reach database subdomain_test... "
	docker exec wp-browser_php_$(PHP_VERSION) bash -c "mysql -utest -ptest -hdb -e \"show databases like 'subdomain_test'\" | grep 'subdomain_test' > /dev/null"
	echo "yes"
	echo -n "PHP container can reach database subdir_test... "
	docker exec wp-browser_php_$(PHP_VERSION) bash -c "mysql -utest -ptest -hdb -e \"show databases like 'subdir_test'\" | grep 'subdir_test' > /dev/null"
	echo "yes"
	echo -n "PHP container can reach database empty... "
	docker exec wp-browser_php_$(PHP_VERSION) bash -c "mysql -utest -ptest -hdb -e \"show databases like 'empty'\" | grep 'empty' > /dev/null"
	echo "yes"
	echo -n "WordPress container can reach database test... "
	docker exec wp-browser_wordpress bash -c 'php -r "new mysqli(\"db\", \"test\", \"test\", \"test\");" > /dev/null'
	echo "yes"
	echo -n "WordPress container can reach database subdomain_test... "
	docker exec wp-browser_wordpress bash -c 'php -r "new mysqli(\"db\", \"test\", \"test\", \"subdomain_test\");" > /dev/null'
	echo "yes"
	echo -n "WordPress container can reach database subdir_test... "
	docker exec wp-browser_wordpress bash -c 'php -r "new mysqli(\"db\", \"test\", \"test\", \"subdir_test\");" > /dev/null'
	echo "yes"
	echo -n "WordPress container can reach database empty... "
	docker exec wp-browser_wordpress bash -c 'php -r "new mysqli(\"db\", \"test\", \"test\", \"empty\");" > /dev/null'
	echo "yes"

.PHONY: test_host_ip
test_host_ip:
	echo "Host IP: $(call host_ip)"

.PHONY: update_core_phpunit_includes
update_core_phpunit_includes:
	rm -rf includes/core-phpunit/includes
	mkdir -p includes/core-phpunit
	cd includes/core-phpunit && \
		svn export https://github.com/WordPress/wordpress-develop/branches/trunk/tests/phpunit/includes
	git apply includes/core-phpunit/_patches/abstract-testcase.php.patch

.PHONY: ssh_mysql
ssh_mysql:
	docker exec -it wp-browser_db bash -c "mysql -utest -ptest"

.PHONY: test
test:
	docker exec wp-browser_php_$(PHP_VERSION) bash -c "vendor/bin/codecept run acceptance"
	docker exec wp-browser_php_$(PHP_VERSION) bash -c "vendor/bin/codecept run cli"
	docker exec wp-browser_php_$(PHP_VERSION) bash -c "vendor/bin/codecept run climodule"
	docker exec wp-browser_php_$(PHP_VERSION) bash -c "vendor/bin/codecept run dbunit"
	docker exec wp-browser_php_$(PHP_VERSION) bash -c "vendor/bin/codecept run functional"
	docker exec wp-browser_php_$(PHP_VERSION) bash -c "vendor/bin/codecept run init"
	docker exec wp-browser_php_$(PHP_VERSION) bash -c "vendor/bin/codecept run muloader"
	docker exec wp-browser_php_$(PHP_VERSION) bash -c "vendor/bin/codecept run unit"
	docker exec wp-browser_php_$(PHP_VERSION) bash -c "vendor/bin/codecept run webdriver"
	docker exec wp-browser_php_$(PHP_VERSION) bash -c "vendor/bin/codecept run wpcli_module"
	docker exec wp-browser_php_$(PHP_VERSION) bash -c "vendor/bin/codecept run wpfunctional"
	docker exec wp-browser_php_$(PHP_VERSION) bash -c "vendor/bin/codecept run wploader_multisite"
	docker exec wp-browser_php_$(PHP_VERSION) bash -c "vendor/bin/codecept run wplaoder_wpdb_interaction"
	docker exec wp-browser_php_$(PHP_VERSION) bash -c "vendor/bin/codecept run wploadersuite"
	docker exec wp-browser_php_$(PHP_VERSION) bash -c "vendor/bin/codecept run wpmodule"
	# docker exec wp-browser_php_$(PHP_VERSION) bash -c "vendor/bin/codecept run isolated"

.PHONY: test_fast
test_fast:
	docker exec wp-browser_php_$(PHP_VERSION) bash -c "vendor/bin/codecept run acceptance -f"
	docker exec wp-browser_php_$(PHP_VERSION) bash -c "vendor/bin/codecept run cli -f"
	docker exec wp-browser_php_$(PHP_VERSION) bash -c "vendor/bin/codecept run climodule -f"
	docker exec wp-browser_php_$(PHP_VERSION) bash -c "vendor/bin/codecept run dbunit -f"
	docker exec wp-browser_php_$(PHP_VERSION) bash -c "vendor/bin/codecept run functional -f"
	docker exec wp-browser_php_$(PHP_VERSION) bash -c "vendor/bin/codecept run init -f"
	docker exec wp-browser_php_$(PHP_VERSION) bash -c "vendor/bin/codecept run muloader -f"
	docker exec wp-browser_php_$(PHP_VERSION) bash -c "vendor/bin/codecept run unit -f"
	docker exec wp-browser_php_$(PHP_VERSION) bash -c "vendor/bin/codecept run webdriver -f"
	docker exec wp-browser_php_$(PHP_VERSION) bash -c "vendor/bin/codecept run wpcli_module -f"
	docker exec wp-browser_php_$(PHP_VERSION) bash -c "vendor/bin/codecept run wpfunctional -f"
	docker exec wp-browser_php_$(PHP_VERSION) bash -c "vendor/bin/codecept run wploader_multisite -f"
	docker exec wp-browser_php_$(PHP_VERSION) bash -c "vendor/bin/codecept run wplaoder_wpdb_interaction -f"
	docker exec wp-browser_php_$(PHP_VERSION) bash -c "vendor/bin/codecept run wploadersuite -f"
	docker exec wp-browser_php_$(PHP_VERSION) bash -c "vendor/bin/codecept run wpmodule -f"
	# docker exec wp-browser_php_$(PHP_VERSION) bash -c "vendor/bin/codecept run isolated -f"

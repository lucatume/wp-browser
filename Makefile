#.SILENT:
SHELL := /bin/bash
PROJECT_NAME = $(notdir $(PWD))
REBUILD ?=0
ROOT ?= 0
MYSQL_ROOT_PASSWORD ?= root
MYSQL_LOCALHOST_PORT ?= 9306
MYSQL_IMAGE ?= mariadb:latest
MYSQL_CONTAINER_NAME ?= $(PROJECT_NAME)_db
WORDPRESS_PARENT_DIR ?= $(PWD)/_wordpress
WORDPRESS_DOMAIN ?= wordpress.test
WORDPRESS_URL ?= http://wordpress.test
WORDPRESS_ROOT_DIR ?= $(WORDPRESS_PARENT_DIR)/wordpress
WORDPRESS_DB_USER ?= $(PROJECT_NAME)
WORDPRESS_DB_PASSWORD ?= $(PROJECT_NAME)
WORDPRESS_DB_HOST ?= $(MYSQL_CONTAINER_NAME)
WORDPRESS_DB_PORT ?= 9306
WORDPRESS_DB_NAME ?= $(PROJECT_NAME)
WORDPRESS_TABLE_PREFIX ?= wp_
WORDPRESS_ADMIN_USER ?= admin
WORDPRESS_ADMIN_PASSWORD ?= admin
WORDPRESS_LOCALHOST_PORT ?= 9380
WORDPRESS_SUBDIR_URL ?= http://$(WORDPRESS_DOMAIN)/subdir-one
WORDPRESS_SUBDIR_DB_NAME ?= test_subdir
WORDPRESS_SUBDOMAIN_DOMAIN ?= one.$(WORDPRESS_DOMAIN)
WORDPRESS_SUBDOMAIN_URL ?= http://$(WORDPRESS_SUBDOMAIN_DOMAIN)
WORDPRESS_SUBDOMAIN_DB_NAME ?= test_subdomain
WORDPRESS_EMPTY_DB_NAME ?= test_empty
PHP_VERSION ?= 5.6
PHP_CONTAINER_NAME = $(PROJECT_NAME)_php_$(PHP_VERSION)
COMPOSER_VERSION ?= 2
COMPOSER_CACHE_DIR ?= $(PWD)/.cache/composer
XDEBUG_REMOTE_PORT ?= 9003
ifeq "$(findstring 'Linux',$(OS))" ""
XDEBUG_REMOTE_HOST ?= host.docker.internal
else
XDEBUG_REMOTE_HOST ?= $(shell docker run --rm --entrypoint sh busybox -c '/bin/ip route | awk "/default/ { print $$3 }" | cut -d" " -f3')
endif
HOST_IP ?= $(XDEBUG_REMOTE_HOST)
CHROMEDRIVER_HOST ?= $(PROJECT_NAME)_chrome
CHROMEDRIVER_PORT ?= 4444
CHROMEDRIVER_LOCALHOST_PORT ?= 9344
CHROMEDRIVER_VNC_PORT ?= 5993
CHROMEDRIVER_VERSION ?= latest
CODECEPTION_MAJOR_VERSION ?= 4

ifeq (1, $(ROOT))
DOCKER_USER ?= "0:0"
else
DOCKER_USER ?= "$(shell id -u):$(shell id -g)"
endif

ifeq (4, $(CODECEPTION_MAJOR_VERSION))
COMPOSER_JSON_FILE = "$(PWD)/composer.codecept-4.json"
else
COMPOSER_JSON_FILE = "$(PWD)/composer.json"
endif

build: up composer_update .env.testing.docker

test: codecept_run

clean: wp_destroy php_container_destroy db_destroy chromedriver_destroy
	rm -f .env.testing.docker

up: db_up wp_up chromedriver_up

define MYSQL_CONFIG
[mysqld]
bind_address=*
collation_server=utf8_unicode_ci
character-set-server=utf8

[client]
default-character-set=utf8
endef
export MYSQL_CONFIG

define DB_SETUP_QUERY
CREATE USER IF NOT EXISTS '$(PROJECT_NAME)'@'localhost' IDENTIFIED BY '$(PROJECT_NAME)';
CREATE USER IF NOT EXISTS '$(PROJECT_NAME)'@'%' IDENTIFIED BY '$(PROJECT_NAME)';
CREATE DATABASE IF NOT EXISTS `$(PROJECT_NAME)`;
GRANT ALL ON *.* TO '$(PROJECT_NAME)'@'localhost';
GRANT ALL ON *.* TO '$(PROJECT_NAME)'@'%';
FLUSH PRIVILEGES;
endef
export DB_SETUP_QUERY

db_up:
	mkdir -p $(WORDPRESS_PARENT_DIR)
	if [ ! -f "$(WORDPRESS_PARENT_DIR)/my.cnf" ]; then echo -e "$${MYSQL_CONFIG}" > "$(WORDPRESS_PARENT_DIR)/my.cnf"; fi
	if [ -z "$$(docker ps -aq --filter name=$(PROJECT_NAME)_db)" ]; then \
	  	echo "Starting db ..."; \
		docker run --name $(PROJECT_NAME)_db -e MYSQL_ROOT_PASSWORD=$(MYSQL_ROOT_PASSWORD) \
			--publish "$(WORDPRESS_DB_PORT):3306" \
			--volume "$(WORDPRESS_PARENT_DIR)/my.cnf:/etc/mysql/conf.d/docker.cnf" \
			--health-cmd='mysqladmin ping --silent' \
			--label $(PROJECT_NAME).service=mysql \
			--detach $(MYSQL_IMAGE); \
	elif [ ! $$(docker ps -q --filter name=$(PROJECT_NAME)_db) ]; then \
	  	echo "Restarting db ..."; \
	  	docker restart $(PROJECT_NAME)_db; \
	fi
	echo -n "Waiting for DB ready ..."
	export C=0 && \
	until [ "$$(docker inspect --format "{{.State.Health.Status}}" $(PROJECT_NAME)_db)" == "healthy" ]; \
		do echo -n '.' &&  sleep 1 && ((C=C+1)) && ([ $$C -le 30 ] || exit 1); \
	done
	echo " done"
	docker exec -i $(PROJECT_NAME)_db mysql -uroot -p$(MYSQL_ROOT_PASSWORD) -e "$${DB_SETUP_QUERY}"

db_down:
	-docker stop "$(PROJECT_NAME)_db"
	rm -f "$(PWD)/my.cnf"

db_cli:
	docker exec -it $(PROJECT_NAME)_db mysql -uroot -p$(MYSQL_ROOT_PASSWOR)

db_destroy: db_down
	-docker stop $$(docker ps -aq --filter label=$(PROJECT_NAME).service=mysql)
	-docker rm --volumes $$(docker ps -aq --filter label=$(PROJECT_NAME).service=mysql)
	rm -rf "$(WORDPRESS_PARENT_DIR)/my.cnf"

define QENV_FN
function qenv(\$$key, \$$default) {\n\treturn (\$$value = getenv(\$$key)) === false ? \$$default : \$$value;\n}
endef

wp_setup:
	mkdir -p "$(WORDPRESS_PARENT_DIR)"
	if [ ! -f "$(WORDPRESS_PARENT_DIR)/wordpress.zip" ]; then curl https://wordpress.org/latest.zip -o "$(WORDPRESS_PARENT_DIR)/wordpress.zip"; fi
	if [ ! -d "$(WORDPRESS_PARENT_DIR)/wordpress" ]; then unzip -u "$(WORDPRESS_PARENT_DIR)/wordpress.zip" -d "$(WORDPRESS_PARENT_DIR)"; fi

define WP_CONFIG_EXTRAS
define( 'WP_ALLOW_MULTISITE', true );
define( 'MULTISITE', true );
define( 'SUBDOMAIN_INSTALL', false );
$$base = '/';
define( 'DOMAIN_CURRENT_SITE', '$(WORDPRESS_DOMAIN)' );
define( 'PATH_CURRENT_SITE', '/' );
define( 'SITE_ID_CURRENT_SITE', 1 );
define( 'BLOG_ID_CURRENT_SITE', 1 );
endef
export WP_CONFIG_EXTRAS

define wp_salt
	awk '/put your unique phrase here/ && ++count==1{sub(/put your unique phrase here/,"$(shell LC_ALL=C tr -dc A-Za-z0-9 </dev/urandom | head -c 64)")} 1'
endef

wp_config: wp_setup
	echo "$${WP_CONFIG_EXTRAS}" > wp_config_extras.tmp
	php -r 'echo preg_replace("/^\\R/m", "\n$(QENV_FN)\n\n", file_get_contents("$(WORDPRESS_PARENT_DIR)/wordpress/wp-config-sample.php"),1);' \
	| sed "s/'database_name_here'/qenv('WORDPRESS_DB_NAME', '$(WORDPRESS_DB_NAME)')/g" \
	| sed "s/'username_here'/qenv('WORDPRESS_DB_USER', '$(WORDPRESS_DB_USER)')/g" \
	| sed "s/'password_here'/qenv('WORDPRESS_DB_PASSWORD', '$(WORDPRESS_DB_PASSWORD)')/g" \
	| sed "s/'localhost'/qenv('WORDPRESS_DB_HOST', '$(WORDPRESS_DB_HOST)') . \':\' . qenv('WORDPRESS_DB_PORT', '3306')/g" \
	| sed '/Happy publishing/r wp_config_extras.tmp' \
	| $(call wp_salt) | $(call wp_salt) | $(call wp_salt) | $(call wp_salt) \
	| $(call wp_salt) | $(call wp_salt) | $(call wp_salt) | $(call wp_salt) \
	> "$(WORDPRESS_PARENT_DIR)/wordpress/wp-config.php";
	rm -f wp_config_extras.tmp

wp_up: db_up php_container wp_config php_container_up
	echo -n "Waiting for WP ready ..."
	export C=0 && \
	until [ "$$(curl -fs http://localhost:$(WORDPRESS_LOCALHOST_PORT); echo $$?)" == 0 ]; \
		do echo -n '.' &&  sleep 1 && ((C=C+1)) && ([ $$C -le 10 ] || exit 1); \
	done
	echo " done"
	echo "Server address: http://localhost:$(WORDPRESS_LOCALHOST_PORT)"

wp_down:
	-docker stop $(PROJECT_NAME)_php_$(PHP_VERSION)
	-docker rm $(PROJECT_NAME)_php_$(PHP_VERSION)
	rm -rf "$(WORDPRESS_PARENT_DIR)/wordpress/wp-content/server.log"
	rm -rf "$(WORDPRESS_PARENT_DIR)/wordpress/wp-content/debug.log"

wp_destroy: wp_down
	rm -f $(WORDPRESS_PARENT_DIR)/wordpress.zip
	rm -rf $(WORDPRESS_PARENT_DIR)/wordpress

wp_logs:
	tail -f "$(WORDPRESS_PARENT_DIR)/wordpress/wp-content/*.log"

php_container:
	if [ $(REBUILD) = 1 ] || [ -z "$$(docker images $(PROJECT_NAME)_php:$(PHP_VERSION) -q)" ]; then \
		docker build _build/_container/php \
			--build-arg USER_UID=$$(id -u) \
			--build-arg USER_GID=$$(id -g) \
			--build-arg USER_UNAME=$$(whoami) \
			--build-arg PHP_VERSION=$(PHP_VERSION) \
			--build-arg CONTAINER_NAME=$(PROJECT_NAME)_php_$(PHP_VERSION) \
			--tag $(PROJECT_NAME)_php:$(PHP_VERSION) \
			--label $(PROJECT_NAME).service=php; \
	fi

php_container_up:
	if [ -z "$$(docker ps -aq --filter name=$(PHP_CONTAINER_NAME))" ]; then \
		docker run --detach --name $(PHP_CONTAINER_NAME) \
			--add-host=$(WORDPRESS_DOMAIN):127.0.0.1 \
			--add-host=$(WORDPRESS_SUBDOMAIN_DOMAIN):127.0.0.1 \
			--add-host=test1.$(WORDPRESS_DOMAIN):127.0.0.1 \
			--add-host=test2.$(WORDPRESS_DOMAIN):127.0.0.1 \
			--add-host=testsite1.$(WORDPRESS_DOMAIN):127.0.0.1 \
			--add-host=testsite2.$(WORDPRESS_DOMAIN):127.0.0.1 \
			--add-host=blog0.$(WORDPRESS_DOMAIN):127.0.0.1 \
			--add-host=blog1.$(WORDPRESS_DOMAIN):127.0.0.1 \
			--add-host=blog2.$(WORDPRESS_DOMAIN):127.0.0.1 \
			-e WORDPRESS_DB_USER=$(WORDPRESS_DB_USER) \
			-e WORDPRESS_DB_PASSWORD=$(WORDPRESS_DB_PASSWORD) \
			-e WORDPRESS_DB_HOST=$(WORDPRESS_DB_HOST) \
			-e WORDPRESS_DB_PORT=3306 \
			-e WORDPRESS_DB_NAME=$(WORDPRESS_DB_NAME) \
			-e WORDPRESS_LOCALHOST_PORT=$(WORDPRESS_LOCALHOST_PORT) \
			--label $(PROJECT_NAME).service=php \
			--link $(MYSQL_CONTAINER_NAME) \
			--volume "$(PWD):$(PWD)" \
			--volume "$(COMPOSER_JSON_FILE):$(PWD)/composer.json" \
			--workdir "$(PWD)" \
			--publish "$(WORDPRESS_LOCALHOST_PORT):80" \
			$(PROJECT_NAME)_php:$(PHP_VERSION) \
			php -t "$(PWD)/_wordpress/wordpress" -S 0.0.0.0:80; \
	elif [ -z "$$(docker ps -q --filter name=$(PHP_CONTAINER_NAME))" ]; then \
	  docker restart $$(docker ps -aq --filter name=$(PHP_CONTAINER_NAME)); \
  	fi

php_container_down:
	-docker stop $$(docker ps -aq --filter label=$(PROJECT_NAME).service=php)
	-docker rm --volumes $$(docker ps -aq --filter label=$(PROJECT_NAME).service=php)

php_container_destroy:
	-docker image rm $$(docker images $(PROJECT_NAME)_php -q)

ifeq "7.2" "$(word 1, $(sort 7.2 $(PHP_VERSION)))"
# PHP Version >= 7.2 -> XDebug 3
php_container_shell:
	docker exec --interactive --tty \
      --user $(DOCKER_USER) \
	  --workdir "$(PWD)" \
      -e COMPOSER_CACHE_DIR=$(COMPOSER_CACHE_DIR) \
	  -e MYSQL_ROOT_PASSWORD=$(MYSQL_ROOT_PASSWORD) \
	  -e MYSQL_DATABASE=$(PROJECT_NAME) \
	  -e CHROMEDRIVER_HOST=$(shell docker inspect -f '{{range.NetworkSettings.Networks}}{{.IPAddress}}{{end}}' $(PROJECT_NAME)_chrome) \
	  -e CHROMEDRIVER_PORT=$(CHROMEDRIVER_PORT) \
	  -e WORDPRESS_DB_NAME=$(WORDPRESS_DB_NAME) \
	  -e WORDPRESS_DB_HOST=$(WORDPRESS_DB_HOST) \
	  -e WORDPRESS_DB_USER=$(WORDPRESS_DB_USER) \
	  -e WORDPRESS_DB_PASSWORD=$(WORDPRESS_DB_PASSWORD) \
	  -e XDEBUG_MODE=develop,debug \
	  -e XDEBUG_CONFIG="idekey=$(PROJECT_NAME) client_port=$(XDEBUG_REMOTE_PORT) client_host=$(XDEBUG_REMOTE_HOST)" \
	  $(PHP_CONTAINER_NAME) \
	  bash
else
# PHP Version < 7.2 -> XDebug 2
php_container_shell:
	docker exec --interactive --tty \
      --user $(DOCKER_USER) \
	  --workdir "$(PWD)" \
      -e COMPOSER_CACHE_DIR=$(COMPOSER_CACHE_DIR) \
	  -e MYSQL_ROOT_PASSWORD=$(MYSQL_ROOT_PASSWORD) \
	  -e MYSQL_DATABASE=$(PROJECT_NAME) \
	  -e CHROMEDRIVER_HOST=$(shell docker inspect -f '{{range.NetworkSettings.Networks}}{{.IPAddress}}{{end}}' $(PROJECT_NAME)_chrome) \
	  -e CHROMEDRIVER_PORT=$(CHROMEDRIVER_PORT) \
	  -e WORDPRESS_DB_NAME=$(WORDPRESS_DB_NAME) \
	  -e WORDPRESS_DB_HOST=$(WORDPRESS_DB_HOST) \
	  -e WORDPRESS_DB_USER=$(WORDPRESS_DB_USER) \
	  -e WORDPRESS_DB_PASSWORD=$(WORDPRESS_DB_PASSWORD) \
	  -e XDEBUG_CONFIG="idekey=$(PROJECT_NAME) remote_enable=1 remote_port=$(XDEBUG_REMOTE_PORT) remote_host=$(XDEBUG_REMOTE_HOST)" \
	  $(PHP_CONTAINER_NAME) \
	  bash
endif

composer_update: composer.json
	docker exec --interactive \
      --user "$$(id -u):$$(id -g)" \
	  --workdir "$(PWD)" \
      -e COMPOSER_CACHE_DIR=$(COMPOSER_CACHE_DIR) \
	  $(PHP_CONTAINER_NAME) \
	  composer update --lock

composer_install:
	docker exec --interactive \
      --user "$$(id -u):$$(id -g)" \
	  --workdir "$(PWD)" \
      -e COMPOSER_CACHE_DIR=$(COMPOSER_CACHE_DIR) \
	  $(PHP_CONTAINER_NAME) \
	  composer install

codecept_run:
	docker exec --interactive \
      --user "$$(id -u):$$(id -g)" \
	  --workdir "$(PWD)" \
	  -e MYSQL_ROOT_PASSWORD=$(MYSQL_ROOT_PASSWORD) \
	  -e MYSQL_DATABASE=$(PROJECT_NAME) \
	  -e CHROMEDRIVER_HOST=$(CHROMEDRIVER_HOST) \
	  -e CHROMEDRIVER_PORT=$(CHROMEDRIVER_PORT) \
	  -e WORDPRESS_DB_NAME=$(WORDPRESS_DB_NAME) \
	  -e WORDPRESS_DB_HOST=$(WORDPRESS_DB_HOST) \
	  -e WORDPRESS_DB_USER=$(WORDPRESS_DB_USER) \
	  -e WORDPRESS_DB_PASSWORD=$(WORDPRESS_DB_PASSWORD) \
	  $(PHP_CONTAINER_NAME) \
	  vendor/bin/codecept run unit

define TEST_ENV_FILE_CONTENTS
CHROMEDRIVER_HOST=$(shell docker inspect -f '{{range.NetworkSettings.Networks}}{{.IPAddress}}{{end}}' $(PROJECT_NAME)_chrome)
CHROMEDRIVER_PORT=$(CHROMEDRIVER_PORT)
WORDPRESS_DOMAIN=$(WORDPRESS_DOMAIN)
WORDPRESS_URL=$(WORDPRESS_URL)
WORDPRESS_ROOT_DIR=$(WORDPRESS_ROOT_DIR)
WORDPRESS_DB_NAME=$(WORDPRESS_DB_NAME)
WORDPRESS_DB_HOST=$(WORDPRESS_DB_HOST)
WORDPRESS_DB_USER=$(WORDPRESS_DB_USER)
WORDPRESS_DB_PASSWORD=$(WORDPRESS_DB_PASSWORD)
WORDPRESS_TABLE_PREFIX=$(WORDPRESS_TABLE_PREFIX)
WORDPRESS_ADMIN_USER=$(WORDPRESS_ADMIN_USER)
WORDPRESS_ADMIN_PASSWORD=$(WORDPRESS_ADMIN_PASSWORD)
WORDPRESS_SUBDIR_URL=$(WORDPRESS_SUBDIR_URL)
WORDPRESS_SUBDIR_DB_NAME=$(WORDPRESS_SUBDIR_DB_NAME)
WORDPRESS_SUBDOMAIN_URL=$(WORDPRESS_SUBDOMAIN_URL)
WORDPRESS_SUBDOMAIN_DB_NAME=$(WORDPRESS_SUBDOMAIN_DB_NAME)
WORDPRESS_EMPTY_DB_NAME=$(WORDPRESS_EMPTY_DB_NAME)
endef
export TEST_ENV_FILE_CONTENTS

.env.testing.docker:
	echo "$${TEST_ENV_FILE_CONTENTS}" > .env.testing.docker

chromedriver_up:
	if [ -z "$$(docker ps -aq --filter name=$(PROJECT_NAME)_chrome)" ]; then \
		docker run --detach \
			--name $(PROJECT_NAME)_chrome \
			--publish $(CHROMEDRIVER_LOCALHOST_PORT):4444 \
			--publish $(CHROMEDRIVER_VNC_PORT):5900 \
			--link $(PHP_CONTAINER_NAME):$(WORDPRESS_DOMAIN) \
			--link $(PHP_CONTAINER_NAME):test1.$(WORDPRESS_DOMAIN) \
			--link $(PHP_CONTAINER_NAME):test2.$(WORDPRESS_DOMAIN) \
			--shm-size="2g" \
			--label $(PROJECT_NAME).service=chrome \
			seleniarm/standalone-chromium:$(CHROMEDRIVER_VERSION); \
	elif [ -z "$$(docker ps -q --filter name=$(PROJECT_NAME)_chrome)" ]; then \
	  	docker restart $$(docker ps -aq --filter name=$(PROJECT_NAME)_chrome); \
	fi
	echo -n "Waiting for Chromedriver ready ..."
	export C=0 && \
	until [ $$(curl --silent 'http://localhost:$(CHROMEDRIVER_LOCALHOST_PORT)/wd/hub/status' 2>/dev/null | grep --quiet  -e 'ready.*true'; echo $$?) == 0 ]; \
		do echo -n '.' &&  sleep 1 && ((C=C+1)) && ([ $$C -le 30 ] || exit 1); \
	done
	echo "done"

chromedriver_down:
	-docker stop $$(docker ps -aq --filter label=$(PROJECT_NAME).service=chrome)

chromedriver_shell:
	docker exec --interactive --tty \
      --user $(DOCKER_USER) \
	  $(PROJECT_NAME)_chrome \
	  bash

chromedriver_destroy: chromedriver_down
	-docker rm --volumes $$(docker ps -aq --filter label=$(PROJECT_NAME).service=chrome)

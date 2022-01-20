#.SILENT:
SHELL := /bin/bash

# Functions
define _host_ip_from_container
$(shell docker run --rm --entrypoint sh busybox -c '/bin/ip route | awk "/default/ { print $$3 }" | cut -d" " -f3')
endef

define _host_ip
$(if $(findstring 'Linux',$(OS)),$(call _host_ip_from_container),host.docker.internal)
endef

define _db_setup_conf
mkdir -p $(WORDPRESS_PARENT_DIR)
if [ ! -f "$(WORDPRESS_PARENT_DIR)/my.cnf" ]; then echo -e "$${MYSQL_CONFIG}" > "$(WORDPRESS_PARENT_DIR)/my.cnf"; fi
endef

define _db_container_is_running
$(shell docker ps -q --filter name=$(PROJECT_NAME)_db)
endef

define _db_container_exists
$(shell docker ps -aq --filter name=$(PROJECT_NAME)_db)
endef

define _db_container_restart
docker restart $(PROJECT_NAME)_db
endef

define _db_container_start
docker run --name $(PROJECT_NAME)_db -e MYSQL_ROOT_PASSWORD=$(MYSQL_ROOT_PASSWORD) \
	--publish "$(WORDPRESS_DB_LOCALHOST_PORT):3306" \
	--volume "$(WORDPRESS_PARENT_DIR)/my.cnf:/etc/mysql/conf.d/docker.cnf" \
	--health-cmd='mysqladmin ping --silent' \
	--label $(PROJECT_NAME).service=mysql \
	--detach $(MYSQL_IMAGE)
endef

define _db_healthcheck
echo -n "Waiting for db ready ..."
export C=0 && \
until [ "$$(docker inspect --format "{{.State.Health.Status}}" $(PROJECT_NAME)_db)" == "healthy" ]; \
	do echo -n '.' &&  sleep 1 && ((C=C+1)) && ([ $$C -le 30 ] || exit 1); \
done
echo " ready"
endef

define _db_setup_query
docker exec -i $(PROJECT_NAME)_db mysql -uroot -p$(MYSQL_ROOT_PASSWORD) -e "$${DB_SETUP_QUERY}"
endef

define _db_container_stop
-docker stop "$(PROJECT_NAME)_db"
endef

define _db_container_remove
-docker rm --volumes $$(docker ps -aq --filter label=$(PROJECT_NAME).service=mysql)
rm -rf "$(WORDPRESS_PARENT_DIR)/my.cnf"
endef

define _db_container_ip
$(shell docker inspect -f '{{range.NetworkSettings.Networks}}{{.IPAddress}}{{end}}' $(PROJECT_NAME)_db)
endef

define _wp_salt
awk '/put your unique phrase here/ && ++count==1{sub(/put your unique phrase here/,"$(shell LC_ALL=C tr -dc A-Za-z0-9 </dev/urandom | head -c 64)")} 1'
endef

define _wp_download
mkdir -p "$(WORDPRESS_PARENT_DIR)"
[ -f  "$(WORDPRESS_PARENT_DIR)/wordpress.zip" ] || curl https://wordpress.org/latest.zip -o "$(WORDPRESS_PARENT_DIR)/wordpress.zip"
endef

define _wp_unzip
[ -d "$(WORDPRESS_ROOT_DIR)" ] || unzip -u "$(WORDPRESS_PARENT_DIR)/wordpress.zip" -d "$(WORDPRESS_PARENT_DIR)"
endef

define _wp_config
echo "$${WP_CONFIG_EXTRAS}" > wp_config_extras.tmp
php -r 'echo preg_replace("/^\\R/m", "\n$(QENV_FN)\n\n", file_get_contents("$(WORDPRESS_PARENT_DIR)/wordpress/wp-config-sample.php"),1);' \
| sed "s/'database_name_here'/qenv('WORDPRESS_DB_NAME', '$(WORDPRESS_DB_NAME)')/g" \
| sed "s/'username_here'/qenv('WORDPRESS_DB_USER', '$(WORDPRESS_DB_USER)')/g" \
| sed "s/'password_here'/qenv('WORDPRESS_DB_PASSWORD', '$(WORDPRESS_DB_PASSWORD)')/g" \
| sed "s/'localhost'/qenv('WORDPRESS_DB_HOST', '$(call _db_container_ip)') . \':\' . qenv('WORDPRESS_DB_PORT', '3306')/g" \
| sed '/Happy publishing/r wp_config_extras.tmp' \
| $(call _wp_salt) | $(call _wp_salt) | $(call _wp_salt) | $(call _wp_salt) \
| $(call _wp_salt) | $(call _wp_salt) | $(call _wp_salt) | $(call _wp_salt) \
> "$(WORDPRESS_PARENT_DIR)/wordpress/wp-config.php";
rm -f wp_config_extras.tmp
endef

define _php_container_is_running
$(shell docker ps -q --filter name=$(PHP_CONTAINER_NAME))
endef

define _php_container_exists
$(shell docker ps -aq --filter name=$(PHP_CONTAINER_NAME))
endef

define _php_container_start
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
	-e WORDPRESS_DB_HOST=$(call _db_container_ip) \
	-e WORDPRESS_DB_PORT=3306 \
	-e WORDPRESS_DB_NAME=$(WORDPRESS_DB_NAME) \
	-e WORDPRESS_LOCALHOST_PORT=$(WORDPRESS_LOCALHOST_PORT) \
	--label $(PROJECT_NAME).service=php \
	--volume "$(PWD):$(PWD)" \
	--volume "$(COMPOSER_JSON_FILE):$(PWD)/composer.json" \
	--workdir "$(PWD)" \
	--publish "$(WORDPRESS_LOCALHOST_PORT):80" \
	$(PROJECT_NAME)_php:$(PHP_VERSION) \
	php -t "$(PWD)/_wordpress/wordpress" -S 0.0.0.0:80
endef

define _php_container_restart
docker restart $$(docker ps -aq --filter name=$(PHP_CONTAINER_NAME));
endef

define _php_container_healthcheck
echo -n "Waiting for PHP ready ..."
export C=0 && \
until [ "$$(curl -s http://localhost:$(WORDPRESS_LOCALHOST_PORT) && echo "$$?")" == 0 ]; \
	do echo -n '.' &&  sleep 1 && ((C=C+1)) && ([ $$C -le 30 ] || exit 1); \
done
echo " ready"
endef

define _composer_container_exec
docker run --rm --interactive --name $(PHP_CONTAINER_NAME)_composer_$(COMPOSER_VERSION) \
	--label $(PROJECT_NAME).service=composer \
	--volume "$(PWD):$(PWD)" \
	--volume "$(COMPOSER_JSON_FILE):$(PWD)/composer.json" \
    --user "$$(id -u):$$(id -g)" \
    -e COMPOSER_CACHE_DIR=$(COMPOSER_CACHE_DIR) \
	--workdir "$(PWD)" \
	$(PROJECT_NAME)_php:$(PHP_VERSION) composer $(1)
endef

define _chromedriver_container_is_running
$(shell docker ps -q --filter name=$(PROJECT_NAME)_chrome)
endef

define _chromedriver_container_exists
$(shell docker ps -aq --filter name=$(PROJECT_NAME)_chrome)
endef

define _chromedriver_container_restart
docker restart $(PROJECT_NAME)_chrome
endef

define _chromedriver_container_stop
-docker stop $$(docker ps -aq --filter label=$(PROJECT_NAME).service=chrome)
endef

define _chromedriver_container_remove
-docker rm --volumes $$(docker ps -aq --filter label=$(PROJECT_NAME).service=chrome)
endef

ARCHITECTURE=$(shell uname -p)
define _chromedriver_image
$(if $(ARCHITECTURE) == 'arm',\
	$(shell echo 'seleniarm/standalone-chromium'),\
	$(shell echo 'selenium/standalone-chrome')\
)
endef

define _chromedriver_container_start
docker run --detach \
	--name $(PROJECT_NAME)_chrome \
	--publish $(CHROMEDRIVER_LOCALHOST_PORT):4444 \
	--publish $(CHROMEDRIVER_VNC_PORT):5900 \
	--link $(PHP_CONTAINER_NAME):$(WORDPRESS_DOMAIN) \
	--link $(PHP_CONTAINER_NAME):test1.$(WORDPRESS_DOMAIN) \
	--link $(PHP_CONTAINER_NAME):test2.$(WORDPRESS_DOMAIN) \
	--shm-size="2g" \
	--label $(PROJECT_NAME).service=chrome \
	$(call _chromedriver_image):$(CHROMEDRIVER_VERSION);
endef

define _chromedriver_container_healthcheck
echo -n "Waiting for Chromedriver ready ..."
export C=0 && \
until [ $$(curl --silent 'http://localhost:$(CHROMEDRIVER_LOCALHOST_PORT)/wd/hub/status' 2>/dev/null | grep --quiet  -e 'ready.*true'; echo $$?) == 0 ]; \
	do echo -n '.' &&  sleep 1 && ((C=C+1)) && ([ $$C -le 30 ] || exit 1); \
done
echo " ready"
endef

define _chromedriver_container_ip
$(shell docker inspect -f '{{range.NetworkSettings.Networks}}{{.IPAddress}}{{end}}' $(PROJECT_NAME)_chrome)
endef

define _xdebug_2_config
idekey=$(PROJECT_NAME) remote_enable=1 remote_port=$(XDEBUG_REMOTE_PORT) remote_host=$(call _host_ip)
endef

define _xdebug_3_config
idekey=$(PROJECT_NAME) client_port=$(XDEBUG_REMOTE_PORT) client_host=$(call _host_ip)
endef

define _xdebug_config
$(shell [ '7.2' = '$(word 1, $(sort 7.2 $(PHP_VERSION)))' ] \
	&& echo '$(call _xdebug_3_config)' \
	|| echo '$(call _xdebug_2_config)';)
endef

define _codecept_run
docker exec --interactive \
  --user "$$(id -u):$$(id -g)" \
  --workdir "$(PWD)" \
  -e MYSQL_ROOT_PASSWORD=$(MYSQL_ROOT_PASSWORD) \
  -e MYSQL_DATABASE=$(PROJECT_NAME) \
  -e CHROMEDRIVER_HOST=$(call _chromedriver_container_ip) \
  -e CHROMEDRIVER_PORT=$(CHROMEDRIVER_PORT) \
  -e WORDPRESS_DB_NAME=$(WORDPRESS_DB_NAME) \
  -e WORDPRESS_DB_HOST=$(call _db_container_ip) \
  -e WORDPRESS_DB_USER=$(WORDPRESS_DB_USER) \
  -e WORDPRESS_DB_PASSWORD=$(WORDPRESS_DB_PASSWORD) \
  $(PHP_CONTAINER_NAME) \
  vendor/bin/codecept run $(1)
endef

# Vars
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
WORDPRESS_DB_LOCALHOST_PORT ?= 9306
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
CHROMEDRIVER_PORT ?= 4444
CHROMEDRIVER_LOCALHOST_PORT ?= 9344
CHROMEDRIVER_VNC_PORT ?= 5993
CHROMEDRIVER_VERSION ?= latest
CODECEPTION_MAJOR_VERSION ?= 4
PHPSTAN_LEVEL ?= max

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

# Definitions
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

define QENV_FN
function qenv(\$$key, \$$default) {\n\treturn (\$$value = getenv(\$$key)) === false ? \$$default : \$$value;\n}
endef

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

define TEST_ENV_FILE_CONTENTS
CHROMEDRIVER_PORT=$(CHROMEDRIVER_PORT)
WORDPRESS_DOMAIN=$(WORDPRESS_DOMAIN)
WORDPRESS_URL=$(WORDPRESS_URL)
WORDPRESS_ROOT_DIR=$(notdir $(WORDPRESS_PARENT_DIR))/wordpress
WORDPRESS_DB_NAME=$(WORDPRESS_DB_NAME)
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

build: db_up wp_up chromedriver_up composer_update test_env_file phpstan_container

lint:
	docker run --rm \
		--volume "$(PWD):$(PWD):ro" \
		--workdir "$(PWD)" \
		lucatume/parallel-lint-56 \
			--colors \
			--exclude /project/src/tad/WPBrowser/Traits/_WithSeparateProcessChecksPHPUnitGte70.php \
			"$(PWD)/src"

phpcs_fix:
	docker run --rm \
		--volume "$(PWD):$(PWD):ro" \
		--workdir "$(PWD)" \
		cytopia/phpcs \
			--colors \
			-p \
			-s \
			--standard=phpcs.xml $(SRC) \
			--ignore=src/data,src/includes,src/tad/scripts,src/tad/WPBrowser/Compat  \
			src

phpcs:
	docker run --rm \
		--volume "$(PWD):$(PWD)" \
		--workdir "$(PWD)" \
		cytopia/phpcbf \
			--colors \
			-p \
			-s \
			--standard=phpcs.xml $(SRC) \
			--ignore=src/data,src/includes,src/tad/scripts,_build \
			src tests

phpcs_fix_and_sniff: phpcs_fix phpcs

phpstan:
	docker run --rm \
		--volume "$(PWD):$(PWD):ro" \
		--workdir "$(PWD)" \
		lucatume/wpstan:0.12.42 analyze \
			-l $(PHPSTAN_LEVEL)

test: lint phpstan
	$(call _codecept_run,unit)
	$(call _codecept_run,dbunit)
	$(call _codecept_run,acceptance)
	$(call _codecept_run,cli)
	$(call _codecept_run,climodule)
	$(call _codecept_run,events)
	$(call _codecept_run,functional)
	$(call _codecept_run,init)
	$(call _codecept_run,isolated)
	$(call _codecept_run,muloader)
	$(call _codecept_run,webdriver)
	$(call _codecept_run,wpcli_module)
	$(call _codecept_run,wpfunctional)
	$(call _codecept_run,wploader_multisite)
	$(call _codecept_run,wploader_wpdb_interaction)
	$(call _codecept_run,wploadersuite)
	$(call _codecept_run,wpmodule)

clean: wp_remove php_container_remove php_container_image_remove db_remove chromedriver_remove
	rm -f .env.testing.docker

state:
	docker ps -a --filter label=wp-browser.service --format="table {{.ID}}\t{{.Names}}\t{{.Image}}\t{{.Ports}}"

phpstan_container:
	docker build \
		--tag $(PROJECT_NAME)_phpstan:latest \
		--label $(PROJECT_NAME).service=phpstan \
		_build/_container/phpstan

db_up:
	$(call _db_setup_conf)
	$(if\
		$(call _db_container_is_running),\
		,\
		$(if\
			$(call _db_container_exists),\
			$(call _db_container_restart),\
			$(call _db_container_start)\
		)\
	)
	$(call _db_healthcheck)
	$(call _db_setup_query)

db_stop:
	$(if $(call _db_container_is_running),$(call _db_container_stop))

db_cli:
	docker exec -it $(PROJECT_NAME)_db mysql -uroot -p$(MYSQL_ROOT_PASSWOR)

db_remove: db_stop
	$(if $(call _db_container_exists),$(call _db_container_remove))

wp_config:
	$(call _wp_download)
	$(call _wp_unzip)
	$(call _wp_config)

wp_up: db_up php_container wp_config php_container_up
	echo "Server address: http://localhost:$(WORDPRESS_LOCALHOST_PORT)"

wp_stop:
	-docker stop $(PROJECT_NAME)_php_$(PHP_VERSION)
	-docker rm $(PROJECT_NAME)_php_$(PHP_VERSION)
	rm -rf "$(WORDPRESS_PARENT_DIR)/wordpress/wp-content/server.log"
	rm -rf "$(WORDPRESS_PARENT_DIR)/wordpress/wp-content/debug.log"

wp_remove: wp_stop
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
	$(if\
		$(call _php_container_is_running),\
		,\
		$(if\
			$(call _php_container_exists),\
			$(call _php_container_restart),\
			$(call _php_container_start)\
		)\
	)
	$(call _php_container_healthcheck)

php_container_stop:
	-docker stop $$(docker ps -aq --filter label=$(PROJECT_NAME).service=php)

php_container_remove: php_container_stop
	-docker rm --volumes $$(docker ps -aq --filter label=$(PROJECT_NAME).service=php)

php_container_image_remove:
	-docker image rm $$(docker images $(PROJECT_NAME)_php -q)

php_container_shell: chromedriver_up
	docker exec --interactive --tty \
      --user $(DOCKER_USER) \
	  --workdir "$(PWD)" \
      -e COMPOSER_CACHE_DIR=$(COMPOSER_CACHE_DIR) \
	  -e MYSQL_ROOT_PASSWORD=$(MYSQL_ROOT_PASSWORD) \
	  -e MYSQL_DATABASE=$(PROJECT_NAME) \
	  -e CHROMEDRIVER_HOST=$(call _chromedriver_container_ip) \
	  -e CHROMEDRIVER_PORT=$(CHROMEDRIVER_PORT) \
	  -e WORDPRESS_DB_NAME=$(WORDPRESS_DB_NAME) \
	  -e WORDPRESS_DB_HOST=$(call _db_container_ip) \
	  -e WORDPRESS_DB_USER=$(WORDPRESS_DB_USER) \
	  -e WORDPRESS_DB_PASSWORD=$(WORDPRESS_DB_PASSWORD) \
	  -e XDEBUG_MODE=develop,debug \
	  -e XDEBUG_CONFIG='$(call _xdebug_config)' \
	  $(PHP_CONTAINER_NAME) \
	  bash

composer_update: composer.json
	$(call _composer_container_exec,update --lock)

composer_install: composer.json
	$(call _composer_container_exec,install)

test_env_file:
	echo "$${TEST_ENV_FILE_CONTENTS}" > .env.testing.docker
	echo "WORDPRESS_DB_HOST=$(call _db_container_ip)" >> .env.testing.docker
	echo "CHROMEDRIVER_HOST=$(call _chromedriver_container_ip)" >> .env.testing.docker

chromedriver_up:
	$(if\
		$(call _chromedriver_container_is_running),\
		,\
		$(if\
			$(call _chromedriver_container_exists),\
			$(call _chromedriver_container_restart),\
			$(call _chromedriver_container_start)\
		)\
	)
	$(call _chromedriver_container_healthcheck)

chromedriver_stop:
	$(if $(call _chromedriver_container_is_running),$(call _chromedriver_container_stop))

chromedriver_shell:
	docker exec --interactive --tty \
      --user $(DOCKER_USER) \
	  $(PROJECT_NAME)_chrome \
	  bash

chromedriver_remove: chromedriver_stop
	$(if $(call _chromedriver_container_exists),$(call _chromedriver_container_remove))

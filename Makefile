.SILENT:
SHELL := /bin/bash

# Vars
CONTAINERS_VERSION = 4.0.0-dev
PROJECT_NAME = $(notdir $(PWD))
PHP_VERSION ?= 8.0
TTY_FLAG := $(shell [ -t 0 ] && echo '-t')

_build/_container/php/iidfile:
	docker build \
		--build-arg PHP_VERSION=$(PHP_VERSION) \
		--label "project=wp-browser" \
		--label "service=php" \
		--iidfile $(PWD)/_build/_container/php/iidfile \
		--tag lucatume/wp-browser_php_$(PHP_VERSION):latest \
		--tag lucatume/wp-browser_php_$(PHP_VERSION):$(CONTAINERS_VERSION) \
		$(PWD)/_build/_container/php

php_container_build: _build/_container/php/iidfile

network_up:
	$(if \
		$(shell docker network ls -q --filter name=wp-browser), \
			, \
			docker network create \
				--attachable \
				--label "project=wp-browser" \
				wp-browser \
	)

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

php_container_up: _build/_container/php/iidfile network_up
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
				--user "$(shell id -u):$(shell id -g)" \
				lucatume/wp-browser_php_$(PHP_VERSION) \
		) \
	)

up: network_up database_up php_container_up

down:
	$(if $(shell docker ps -aq --filter "label=project=wp-browser"), \
		docker rm --force $$(docker ps -aq --filter "label=project=wp-browser"))
	$(if $(shell docker network ls -q --filter label=project=wp-browser), \
		docker network rm $$(docker network ls -q --filter label=project=wp-browser))

build: _build/_container/php/iidfile up

clean: down
	rm -f _build/_container/php/iidfile

test_config:
	echo "CONTAINERS_VERSION => $(CONTAINERS_VERSION)"
	echo "PROJECT_NAME => $(PROJECT_NAME)"
	echo "PHP_VERSION => $(PHP_VERSION)"
	echo "TTY_FLAG => $(TTY_FLAG)"

ssh: php_container_up
	docker exec -it -u "$(shell id -u):$(shell id -g)" wp-browser_php_$(PHP_VERSION) bash

composer_version: network_up php_container_up
	docker exec $(TTY_FLAG) -u "$(shell id -u):$(shell id -g)" wp-browser_php_$(PHP_VERSION) composer --version

composer_install: network_up php_container_up
	docker exec $(TTY_FLAG) -u "$(shell id -u):$(shell id -g)" wp-browser_php_$(PHP_VERSION) composer install

composer_update: network_up php_container_up
	docker exec $(TTY_FLAG) -u "$(shell id -u):$(shell id -g)" wp-browser_php_$(PHP_VERSION) composer update

wp_cli_version:
	docker exec $(TTY_FLAG) -u "$(shell id -u):$(shell id -g)" wp-browser_php_$(PHP_VERSION) wp --version

wordpress_up: php_container_up database_up
	docker exec $(TTY_FLAG) -u "$(shell id -u):$(shell id -g)" wp-browser_php_$(PHP_VERSION) wordpress_up


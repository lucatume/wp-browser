# Use bash as shell.
SHELL := /bin/bash

# If you see pwd_unknown showing up, this is why. Re-calibrate your system.
PWD ?= pwd_unknown

# PROJECT_NAME defaults to name of the current directory.
PROJECT_NAME = $(notdir $(PWD))

# Suppress `make` own output.
.SILENT:

# Define what targets should always run.
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

# PUll all the Docker images this repository will use in building images or running processes.
docker_pull:
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

# Lint the project source files to make sure they are PHP 5.6 compatible.
lint:
	docker run --rm -v ${PWD}:/project lucatume/parallel-lint-56 --colors /project/src

# Use the PHP Code Sniffer container to sniff the relevant source files.
sniff:
	docker run --rm -v ${PWD}:/data cytopia/phpcs \
		--colors \
		-p \
		-s \
		--standard=phpcs.xml $(SRC) \
		--ignore=src/data,src/includes,src/tad/scripts,src/tad/WPBrowser/Compat  \
		src

# Use the PHP Code Beautifier container to fix the source and tests code.
fix:
	docker run --rm -v ${PWD}:/data cytopia/phpcbf \
		--colors \
		-p \
		-s \
		--standard=phpcs.xml $(SRC) \
		--ignore=src/data,src/includes,src/tad/scripts \
		src tests

# Fix the PHP code, then sniff it.
fix_n_sniff: fix sniff

# Use phpstan container to analyze the source code.
# Configuration will be read from the phpstan.neon.dist file.
phpstan:
	docker run --rm -v ${PWD}:/project lucatume/wpstan analyze -l max

# Run a set of checks on the code before commit.
pre_commit: lint sniff phpstan

# Clean the project Docker containers, volumes and networks.
clean:
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
	rm *.bak
	rm .ready

# Produces the Modules documentation in the docs/modules folder.
docs: composer.lock src/Codeception/Module
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
		docs/bin/wpbdocmd generate \
			--visibility=public \
			--methodRegex="/^[^_]/" \
			--tableGenerator=tad\\WPBrowser\\Documentation\\TableGenerator \
			$${class} > doc.tmp; \
		if [ 0 != $$? ]; then rm doc.tmp && exit 1; fi; \
		echo "${PWD}/doc.tmp $${file}" | xargs php ${PWD}/docs/bin/update_doc.php; \
		rm doc.tmp; \
	done;
	cp ${PWD}/docs/welcome.md ${PWD}/docs/README.md

php_7.2_cc_3.0_prepare:
	${PWD}/_build/vendor_prepare.sh 7.2 3.0

php_7.2_cc_3.0_run: php_7.2_cc_3.0_prepare
	docker-compose run codeception run acceptance --debug
	docker-compose run codeception run cli --debug
	docker-compose run codeception run climodule --debug
	docker-compose run codeception run command --debug
	docker-compose run codeception run dbunit --debug
	docker-compose run codeception run functional --debug
	docker-compose run codeception run muloader --debug
	docker-compose run codeception run unit --debug
	docker-compose run codeception run webdriver --debug
	docker-compose run codeception run wpcli_module --debug
	docker-compose run codeception run wpfunctional --debug
	docker-compose run codeception run wploader_multisite --debug
	docker-compose run codeception run wploader_wpdb_interaction --debug
	docker-compose run codeception run wploadersuite --debug
	docker-compose run codeception run wpmodule --debug

test:
	docker-compose run --rm codeception run unit --debug

# Prins a list of files that will be exported from the project on package pull.
check_exports:
	bash ./_build/check_exports.sh

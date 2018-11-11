build_images:
	# Builds the images required by the Docker-based utils like parallel-lint and so on.
	docker build ./docker/parallel-lint --tag parallel-lint:5.6
lint:
	# Lints the source files with PHP Parallel Lint, requires the parallel-lint:5.6 image to be built.
	docker run --rm -v ${CURDIR}:/app parallel-lint:5.6 --colors /app/src
sniff:
	# Sniff the source files code style using PHP_CodeSniffer and PSR-2 standards.
	docker run --rm -v ${CURDIR}/src:/scripts/ texthtml/phpcs phpcs \
		--standard=/scripts/phpcs.xml \
		--ignore=data,includes,tad/scripts \
		/scripts -s
fix:
	# Fix the source files code style using PHP_CodeSniffer and PSR-2 standards.
	docker run --rm -v ${CURDIR}/src:/scripts/ texthtml/phpcs phpcbf \
		--standard=/scripts/phpcs.xml \
		--ignore=data,includes,tad/scripts \
		/scripts
composer_update:
	# Updates Composer dependencies using PHP 5.6.
	docker run --rm -v ${CURDIR}:/app composer/composer:master-php5 update
phpstan:
	# Runs phpstan on the source files.
	docker run --rm -v ${CURDIR}:/app phpstan/phpstan analyse -l 5 /app/src/Codeception /app/src/tad
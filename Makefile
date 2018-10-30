build_images:
	# Builds the images required by the Docker-based utils like parallel-lint and so on.
	docker build ./docker/parallel-lint --tag parallel-lint:5.6
lint:
	# Lints the source files with PHP Parallel Lint, requires the parallel-lint:5.6 image to be built
	# see the build_images task.
	docker run --rm -v ${CURDIR}:/app parallel-lint:5.6 --colors /app/src
composer_update:
	# Updates the Composer dependencies using PHP 5.6.
	# This image is deprecated but it's conevenient to use as it's using PHP 5.6; latest
	# versions are using PHP 7.0+.
	docker run --rm -v ${CURDIR}:/app composer/composer:master-php5 update

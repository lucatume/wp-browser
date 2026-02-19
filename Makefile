build:
	composer install
.PHONY: build

test:
	vendor/bin/codecept run
.PHONY: test

clean:
	rm -rf vendor composer.lock var
	mkdir -p var
.PHONY: clean

clean_tmp:
	rm -rf var/_output var/_tmp
.PHONY: clean_tmp

wordpress_install:
	php bin/setup-wp.php

clean_procs:
	pgrep -f 'php -S' | xargs kill
	pgrep chromedriver | xargs kill
	rm -f var/_output/*.pid var/_output/*.running
	set -o allexport && source tests/.env && set +o allexport && docker compose down
.PHONY: clean_procs

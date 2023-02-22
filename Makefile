build:
	bin/stack -p5.6 build
	bin/stack -p7.0 build
	bin/stack -p7.1 build
	bin/stack -p7.2 build
	bin/stack -p7.3 build
	bin/stack -p7.3 build
	bin/stack -p8.0 build
	bin/stack -p8.1 build
.PHONY: build

test:
	bin/stack -p5.6 xdebug-off && bin/stack -c2 composer_update && bin/stack -p5.6 test
	bin/stack -p5.6 xdebug-off && bin/stack -c3 composer_update && bin/stack -p5.6 test
	bin/stack -p5.6 xdebug-off && bin/stack -c4 composer_update && bin/stack -p5.6 test
	bin/stack -p7.4 xdebug-off && bin/stack -c2 composer_update && bin/stack -p7.4 test
	bin/stack -p7.4 xdebug-off && bin/stack -c3 composer_update && bin/stack -p7.4 test
	bin/stack -p7.4 xdebug-off && bin/stack -c4 composer_update && bin/stack -p7.4 test
	bin/stack -p8.0 xdebug-off && bin/stack -c4 composer_update && bin/stack -p8.0 test
	bin/stack -p8.1 xdebug-off && bin/stack -c4 composer_update && bin/stack -p8.1 test
.PHONY: test

clean:
	bin/stack clean
.PHONY: clean

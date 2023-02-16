build:
	./_build/stack.sh -p5.6 build
	./_build/stack.sh -p7.0 build
	./_build/stack.sh -p7.1 build
	./_build/stack.sh -p7.2 build
	./_build/stack.sh -p7.3 build
	./_build/stack.sh -p7.3 build
	./_build/stack.sh -p8.0 build
	./_build/stack.sh -p8.1 build
.PHONY: build

test:
	./_build/stack.sh -p5.6 xdebug-off && ./_build/stack.sh -c2 composer_update && ./_build/stack.sh -p5.6 test
	./_build/stack.sh -p5.6 xdebug-off && ./_build/stack.sh -c3 composer_update && ./_build/stack.sh -p5.6 test
	./_build/stack.sh -p5.6 xdebug-off && ./_build/stack.sh -c4 composer_update && ./_build/stack.sh -p5.6 test
	./_build/stack.sh -p7.4 xdebug-off && ./_build/stack.sh -c2 composer_update && ./_build/stack.sh -p7.4 test
	./_build/stack.sh -p7.4 xdebug-off && ./_build/stack.sh -c3 composer_update && ./_build/stack.sh -p7.4 test
	./_build/stack.sh -p7.4 xdebug-off && ./_build/stack.sh -c4 composer_update && ./_build/stack.sh -p7.4 test
	./_build/stack.sh -p8.0 xdebug-off && ./_build/stack.sh -c4 composer_update && ./_build/stack.sh -p8.0 test
	./_build/stack.sh -p8.1 xdebug-off && ./_build/stack.sh -c4 composer_update && ./_build/stack.sh -p8.1 test
.PHONY: test

clean:
	./_build/stack.sh clean
.PHONY: clean

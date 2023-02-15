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
	./_build/stack.sh -p5.6 -c2 test
	./_build/stack.sh -p5.6 -c3 test
	./_build/stack.sh -p5.6 -c4 test
	./_build/stack.sh -p7.4 -c2 test
	./_build/stack.sh -p7.4 -c3 test
	./_build/stack.sh -p7.4 -c4 test
	./_build/stack.sh -p8.0 -c4 test
	./_build/stack.sh -p8.1 -c4 test
.PHONY: test

clean:
	./_build/stack.sh clean
.PHONY: clean

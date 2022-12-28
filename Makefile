#!/usr/bin/make -f

.PHONY: check test test-pure coverage

# ---------------------------------------------------------------------

check:
	php vendor/bin/phpcs
	composer validate --no-interaction

test: check
	phpdbg -qrr vendor/bin/phpunit

test-pure:
	phpdbg -qrr vendor/bin/phpunit

coverage: test
	@if [ "`uname`" = "Darwin" ]; then open build/coverage/index.html; fi

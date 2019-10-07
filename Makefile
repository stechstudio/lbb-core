TESTS?=tests
SHELL = /bin/bash

filter: coverage-dir
	vendor/bin/phpunit --dump-xdebug-filter coverage/xdebug-filter.php

stan:
	rm -f coverage/phpstan.html
	echo '<html><body><pre><code>' > coverage/phpstan.html
	-vendor/bin/phpstan analyse --error-format=prettyJson >> coverage/phpstan.html
	echo '</code></pre></body></html>' >> coverage/phpstan.html
	open /tmp/phpstan.html

test: clear keys
	vendor/bin/phpunit --testsuite all --testdox --stop-on-failure

test-coverage: coverage-dir
	-vendor/bin/phpunit --prepend coverage/xdebug-filter.php --coverage-html coverage/coverage-report
	open coverage/index.html

pr: stan test-coverage

coverage-dir:
	mkdir -p coverage/coverage-report
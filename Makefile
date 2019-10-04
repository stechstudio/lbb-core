TESTS?=tests
SHELL = /bin/bash

filter:
	vendor/bin/phpunit --dump-xdebug-filter storage/coverage-report/xdebug-filter.php

stan:
	rm -f /tmp/phpstan.html
	echo '<html><body><pre><code>' > /tmp/phpstan.html
	-vendor/bin/phpstan analyse --error-format=prettyJson >> /tmp/phpstan.html
	echo '</code></pre></body></html>' >> /tmp/phpstan.html
	open /tmp/phpstan.html

test: clear keys
	vendor/bin/phpunit --testsuite all --testdox --stop-on-failure

test-coverage:
	mkdir -p storage/coverage-report
	-vendor/bin/phpunit --prepend storage/coverage-report/xdebug-filter.php --coverage-html /tmp/coverage-report
	open /tmp/coverage-report/index.html

pr: stan test-coverage
RUN = docker compose run --rm php

.PHONY: shell install test coverage infection phpstan ecs rector composer-check qa

shell:
	$(RUN) sh

install:
	$(RUN) composer install --no-interaction --prefer-dist

test:
	$(RUN) vendor/bin/phpunit --testdox

coverage:
	$(RUN) vendor/bin/phpunit --coverage-text --only-summary-for-coverage-text

infection:
	$(RUN) vendor/bin/infection --threads=max --show-mutations=0

phpstan:
	$(RUN) vendor/bin/phpstan --memory-limit=-1

ecs:
	$(RUN) vendor/bin/ecs check

rector:
	$(RUN) vendor/bin/rector process --dry-run

composer-check:
	$(RUN) composer validate --strict --no-check-lock
	$(RUN) composer normalize --dry-run
	$(RUN) composer audit

qa: test coverage infection phpstan ecs rector composer-check

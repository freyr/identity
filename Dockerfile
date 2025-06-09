FROM php:8.4-cli-alpine
ENV COMPOSER_ALLOW_SUPERUSER=1
ENV PHP_CS_FIXER_IGNORE_ENV=1
COPY --from=composer/composer:2-bin /composer /usr/bin/composer
WORKDIR /app
CMD sh -c "vendor/bin/phpunit"
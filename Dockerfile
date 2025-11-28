FROM composer:2.9.2 AS composer

FROM php:8.4-cli-alpine3.22

RUN apk add libpq-dev

RUN set -eux; \
	docker-php-ext-install pdo_pgsql

COPY --from=composer /usr/bin/composer /usr/local/bin/composer

USER 1000:1000

WORKDIR /app

CMD vendor/bin/phpunit

# syntax=docker/dockerfile:1

# Versions
FROM dunglas/frankenphp:1-php8.3-alpine AS frankenphp_upstream

# Base FrankenPHP image
FROM frankenphp_upstream AS frankenphp_base

WORKDIR /app
VOLUME /app/var/

# persistent / runtime deps
RUN set -eux; \
    apk add --no-cache \
        acl \
        fcgi \
        file \
        gettext \
        git \
    ;

RUN set -eux; \
	install-php-extensions \
		@composer \
		apcu \
		intl \
		opcache \
		zip \
        redis \
        pdo_pgsql \
        sysvsem \
	;

# https://getcomposer.org/doc/03-cli.md#composer-allow-superuser
ENV COMPOSER_ALLOW_SUPERUSER=1

ENV PHP_INI_SCAN_DIR=":$PHP_INI_DIR/app.conf.d"

COPY --link frankenphp/conf.d/10-app.ini $PHP_INI_DIR/app.conf.d/
COPY --link --chmod=755 frankenphp/docker-entrypoint.sh /usr/local/bin/docker-entrypoint
COPY --link frankenphp/Caddyfile /etc/caddy/Caddyfile

ENTRYPOINT ["docker-entrypoint"]

HEALTHCHECK --start-period=60s CMD curl -f http://localhost:2019/metrics || exit 1
CMD ["frankenphp", "run", "--config", "/etc/caddy/Caddyfile"]

# Dev FrankenPHP image
FROM frankenphp_base AS frankenphp_dev

ENV APP_ENV=dev XDEBUG_MODE=off

RUN mv "$PHP_INI_DIR/php.ini-development" "$PHP_INI_DIR/php.ini"

RUN set -eux; \
	install-php-extensions \
		xdebug \
	;

CMD (php bin/console sass:build --watch &); \
    (php bin/console typescript:build --watch &); \
    (frankenphp run --config /etc/caddy/Caddyfile --watch);

# Prod FrankenPHP image
FROM frankenphp_base AS frankenphp_prod
LABEL org.opencontainers.image.source="https://github.com/database-playground/app-sf"

ENV APP_ENV=prod
ENV FRANKENPHP_CONFIG="import worker.Caddyfile"

RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

COPY --link frankenphp/conf.d/20-app.prod.ini $PHP_INI_DIR/app.conf.d/
COPY --link frankenphp/worker.Caddyfile /etc/caddy/worker.Caddyfile

# prevent the reinstallation of vendors at every changes in the source code
COPY --link composer.* symfony.* ./
RUN set -eux; \
	composer install --no-cache --prefer-dist --no-dev --no-autoloader --no-scripts --no-progress

# copy sources
COPY --link . ./
RUN rm -Rf frankenphp/

RUN set -eux; \
	mkdir -p var/cache var/log; \
	composer dump-autoload --classmap-authoritative --no-dev; \
	composer dump-env prod; \
	composer run-script --no-dev post-install-cmd; \
	chmod +x bin/console; sync;

# build sass and asset maps
RUN set -eux; \
    chmod +x bin/console; sync; \
    ./bin/console sass:build; \
    ./bin/console typescript:build; \
    ./bin/console asset-map:compile;

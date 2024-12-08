# syntax=docker/dockerfile:1

FROM php:8.3-cli AS base

ADD --chmod=0755 https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/

WORKDIR /app
VOLUME /app/var/

RUN set -eux; \
    apt-get update \
    && apt-get install -y --no-install-recommends \
      acl=* \
      file=* \
      gettext=* \
      git=* \
    && rm -rf /var/lib/apt/lists/* \
    ;

RUN set -eux; \
    install-php-extensions \
        @composer \
        apcu \
        curl \
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

ENTRYPOINT ["docker-entrypoint"]

# Worker
FROM base AS worker
LABEL org.opencontainers.image.source="https://github.com/database-playground/app-sf"

ENV APP_ENV=prod
ENV APP_DEBUG=0

RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

COPY --link frankenphp/conf.d/20-app.prod.ini $PHP_INI_DIR/app.conf.d/

# prevent the reinstallation of vendors at every changes in the source code
COPY --link composer.* symfony.* package.json* ./
RUN set -eux; \
    composer install --no-cache --prefer-dist --no-dev --no-autoloader --no-scripts --no-progress;

# copy sources
COPY --link . ./
RUN rm -Rf frankenphp/ config/packages/debug.php config/packages/web_profiler.php

RUN set -eux; \
    mkdir -p var/cache var/log; \
    composer dump-autoload --classmap-authoritative --no-dev; \
    composer dump-env prod; \
    composer run-script --no-dev post-install-cmd; \
    chmod +x bin/console; sync;

# build route cache, sass and asset maps
RUN set -eux; \
    chmod +x bin/console; sync; \
    ./bin/console cache:clear; \
    ./bin/console cache:warmup;

ENV RUN_MIGRATIONS=false

# Restart the messenger about each 10 minute or when memory limit (300M) is reached
# https://symfony.com/doc/current/messenger.html#deploying-to-production
CMD ["php", "bin/console", "messenger:consume", "--all", "-vv", "--time-limit=600", "--memory-limit=300M"]

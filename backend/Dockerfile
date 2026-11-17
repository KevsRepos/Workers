# syntax=docker/dockerfile:1.7
#
# Multi-stage build for the Symfony backend.
#   base    – PHP-FPM runtime with required extensions and hardened php.ini
#   dev     – base + Composer + tooling (used for local development, tests and CI)
#   vendor  – installs production Composer dependencies
#   prod    – minimal runtime image, runs as www-data, no build tooling

ARG PHP_IMAGE=php:8.4-fpm-trixie
ARG COMPOSER_IMAGE=composer:2

FROM ${COMPOSER_IMAGE} AS composer_bin

# ---------------------------------------------------------------------------
FROM ${PHP_IMAGE} AS base

ENV APP_DIR=/srv/app \
    TZ=Europe/Berlin

RUN set -eux; \
    apt-get update; \
    apt-get install -y --no-install-recommends libicu-dev libfcgi-bin; \
    docker-php-ext-install -j"$(nproc)" intl pdo_mysql opcache; \
    apt-mark manual 'libicu[0-9]*'; \
    apt-get purge -y --auto-remove libicu-dev; \
    rm -rf /var/lib/apt/lists/* /usr/src/php*

COPY docker/php/php.ini /usr/local/etc/php/conf.d/zz-app.ini
COPY docker/php/fpm-pool.conf /usr/local/etc/php-fpm.d/zz-app.conf
COPY docker/php/healthcheck.sh /usr/local/bin/php-fpm-healthcheck
COPY docker/php/entrypoint.sh /usr/local/bin/app-entrypoint
RUN chmod 0755 /usr/local/bin/php-fpm-healthcheck /usr/local/bin/app-entrypoint

WORKDIR ${APP_DIR}

HEALTHCHECK --interval=10s --timeout=3s --start-period=10s --retries=5 CMD php-fpm-healthcheck
ENTRYPOINT ["app-entrypoint"]
CMD ["php-fpm", "-F"]

# ---------------------------------------------------------------------------
FROM base AS dev

RUN set -eux; \
    apt-get update; \
    apt-get install -y --no-install-recommends git unzip; \
    rm -rf /var/lib/apt/lists/*
COPY --from=composer_bin /usr/bin/composer /usr/bin/composer
ENV APP_ENV=dev COMPOSER_ALLOW_SUPERUSER=1
COPY docker/php/php-dev.ini /usr/local/etc/php/conf.d/zz-app-dev.ini

# ---------------------------------------------------------------------------
FROM base AS vendor

COPY --from=composer_bin /usr/bin/composer /usr/bin/composer
ENV COMPOSER_ALLOW_SUPERUSER=1
COPY composer.json composer.lock symfony.lock ./
RUN composer install --no-dev --no-interaction --no-progress --prefer-dist --no-scripts --no-autoloader

# ---------------------------------------------------------------------------
FROM base AS prod

ENV APP_ENV=prod APP_DEBUG=0

COPY --from=vendor ${APP_DIR}/vendor ./vendor
COPY --chown=root:root bin ./bin
COPY --chown=root:root config ./config
COPY --chown=root:root migrations ./migrations
COPY --chown=root:root public ./public
COPY --chown=root:root src ./src
COPY --chown=root:root composer.json composer.lock symfony.lock ./
COPY --from=composer_bin /usr/bin/composer /tmp/composer

# Build-time values only. Real secrets are injected at runtime via environment.
RUN set -eux; \
    printf 'APP_ENV=prod\n' > .env; \
    /tmp/composer dump-autoload --no-dev --classmap-authoritative --no-interaction; \
    /tmp/composer dump-env prod --no-interaction; \
    rm -f /tmp/composer .env; \
    APP_SECRET=build DATABASE_URL='mysql://u:p@localhost:3306/db?serverVersion=mariadb-11.8.0' \
      JWT_PASSPHRASE=build php bin/console cache:warmup --no-interaction; \
    mkdir -p var/log config/jwt; \
    chown -R www-data:www-data var; \
    chmod -R a-w src config public bin

USER www-data

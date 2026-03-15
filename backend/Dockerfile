FROM php:8.4-fpm

RUN apt-get update && DEBIAN_FRONTEND=noninteractive apt-get install -y \
  git \
  unzip \
  libzip-dev \
  libonig-dev \
  zip \
  netcat-openbsd \
  libssl-dev \
  openssl \
  build-essential \
  autoconf \
  pkg-config \
  zlib1g-dev \
 && docker-php-ext-install pdo pdo_mysql zip

# Install composer
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" \
 && php composer-setup.php --install-dir=/usr/local/bin --filename=composer \
 && rm -f composer-setup.php

WORKDIR /var/www/workers/backend

# Install PHP dependencies from composer (copy only composer files for better caching)
COPY composer.json composer.lock* ./
RUN composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader

# Copy application
COPY . ./

# Ensure JWT config dir exists and is writable before attempting generation
RUN mkdir -p config/jwt \
 && chown -R www-data:www-data config/jwt || true

# Generate JWT keys if missing during build (fail loudly if generation fails)
RUN if [ ! -f config/jwt/private.pem ] || [ ! -f config/jwt/public.pem ]; then \
    php bin/console lexik:jwt:generate-keypair --no-interaction; \
  fi

# Create var dirs and set permissions at build time
RUN mkdir -p var/cache var/log var/sessions \
 && chown -R www-data:www-data var || true \
 && chmod -R 775 var || true

# Add entrypoint to ensure permissions at container start (handles host bind-mounts)
EXPOSE 9000
CMD ["php-fpm"]

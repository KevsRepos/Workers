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
# Install dependencies but skip running composer scripts (bin/console not yet copied)
RUN composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader --no-scripts

# Copy application

# Copy application sources
COPY . ./

# Rebuild optimized autoloader now that sources are present
RUN composer dump-autoload --optimize

# Ensure JWT config dir exists and is writable before attempting generation
RUN mkdir -p config/jwt \
 && chown -R www-data:www-data config/jwt

# Debug output: show config dir after ensuring jwt directory
RUN echo '--- JWT: config directory contents (after mkdir/chown) ---' \
 && echo "WORKDIR: $(pwd)" \
 && echo "ABS CONFIG: /var/www/workers/backend/config" \
 && ls -la /var/www/workers/backend/config \
 && echo "www-data uid/gid: $(id -u www-data 2>/dev/null || echo 'n/a')/$(id -g www-data 2>/dev/null || echo 'n/a')" \
 && echo "www-data groups: $(id -Gn www-data 2>/dev/null || echo 'n/a')"

# Create var dirs and set permissions at build time
RUN mkdir -p var/cache var/log var/sessions \
 && chown -R www-data:www-data var \
 && chmod -R 775 var

EXPOSE 9000
CMD ["php-fpm", "-F"]

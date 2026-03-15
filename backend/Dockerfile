FROM php:8.4-fpm

RUN apt-get update && DEBIAN_FRONTEND=noninteractive apt-get install -y \
  git \
  unzip \
  libzip-dev \
  libonig-dev \
  zip \
  netcat-openbsd \
 && docker-php-ext-install pdo pdo_mysql zip

# Install composer
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" \
 && php composer-setup.php --install-dir=/usr/local/bin --filename=composer \
 && rm -f composer-setup.php

WORKDIR /var/www/workers/backend

# Install PHP dependencies from composer (copy only composer files for better caching)
COPY composer.json composer.lock* ./
RUN composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader || true

# Copy application
COPY . ./

# Generate JWT keys if missing (best-effort during build)
RUN if [ ! -f config/jwt/private.pem ] || [ ! -f config/jwt/public.pem ]; then \
    php bin/console lexik:jwt:generate-keypair --no-interaction || true; \
  fi

EXPOSE 9000
CMD ["php-fpm"]

FROM php:8.4-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y \
    libzip-dev zip unzip git mariadb-client nginx \
    && docker-php-ext-install pdo pdo_mysql zip

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY . .

RUN composer install --no-dev --optimize-autoloader

# Copy nginx config for backend
COPY nginx.conf /etc/nginx/conf.d/default.conf

EXPOSE 80
CMD ["sh", "-c", "php-fpm & nginx -g 'daemon off;'"]

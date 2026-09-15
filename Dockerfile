FROM php:8.5-fpm-alpine

# Cài đặt các thư viện hệ thống và extension PHP
RUN apk add --no-cache nginx zip unzip git mariadb-client
RUN docker-php-ext-install pdo pdo_mysql

WORKDIR /var/www/html
COPY . .

# Cài đặt Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
RUN composer install --no-dev --optimize-autoloader

# Phân quyền lưu trữ cho Laravel
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

EXPOSE 8000
CMD php artisan serve --host=0.0.0.0 --port=8000
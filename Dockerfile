FROM php:8.5-fpm-alpine

# Cài đặt các thư viện hệ thống, PHP extension và Node.js/NPM
RUN apk add --no-cache nginx zip unzip git mariadb-client nodejs npm
RUN docker-php-ext-install pdo pdo_mysql

WORKDIR /var/www/html
COPY . .

# Cài đặt Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
RUN composer install --no-dev --optimize-autoloader

# Build giao diện CSS/JS với Vite/Tailwind
RUN npm install && npm run build

# Phân quyền lưu trữ và tạo database.sqlite
RUN mkdir -p /var/www/html/database \
    && touch /var/www/html/database/database.sqlite \
    && chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache /var/www/html/database

EXPOSE 8000
CMD php artisan migrate --force && php artisan serve --host=0.0.0.0 --port=8000
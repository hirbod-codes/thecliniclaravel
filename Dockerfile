FROM php:fpm-buster

RUN apt-get update && \
    apt-get install -y \
    nodejs \
    npm \
    git \
    curl \
    zip \
    unzip \
    && docker-php-ext-install pdo \
    pdo_mysql \
    mysqli \
    && pecl install xdebug \
    && docker-php-ext-enable xdebug \
    curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

COPY --from=composer:latest /usr/bin/composer /usr/bin/

WORKDIR /var/www/html/laravel

COPY . .

RUN composer install && composer update

RUN npm install && npm build

RUN php artisan config:cache && \
    php artisan route:cache

EXPOSE 9000

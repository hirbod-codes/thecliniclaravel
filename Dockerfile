FROM php:8.0-fpm-buster

RUN apt-get update && apt-get install -y \
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

COPY --from=composer:latest /usr/bin/composer /usr/bin/

WORKDIR /var/www/html/laravel

COPY . .

RUN composer install && composer update

EXPOSE 9000

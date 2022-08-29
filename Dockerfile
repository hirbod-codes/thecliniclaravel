FROM php:8.1.9-fpm-buster

USER root

RUN usermod -G root www-data

RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

ENV NODE_VERSION=16.13.0

RUN apt-get update && apt-get install -y \
    git \
    curl \
    zip \
    procps \
    nano \
    unzip \
    && docker-php-ext-install pdo \
    pdo_mysql \
    mysqli \
    && \
    pecl install xdebug && \
    docker-php-ext-enable xdebug  && \
    curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer && \
    curl -o- https://raw.githubusercontent.com/nvm-sh/nvm/v0.39.0/install.sh | bash

COPY --from=composer:latest /usr/bin/composer /usr/bin/

ENV NVM_DIR=/root/.nvm

RUN . "$NVM_DIR/nvm.sh" && nvm install ${NODE_VERSION} && \
    . "$NVM_DIR/nvm.sh" && nvm use v${NODE_VERSION} && \
    . "$NVM_DIR/nvm.sh" && nvm alias default v${NODE_VERSION}

ENV PATH="/root/.nvm/versions/node/v${NODE_VERSION}/bin/:${PATH}"

WORKDIR /var/www/html/laravel

COPY . .
COPY ./docker/php/addon.ini /usr/local/etc/php/conf.d/addon.ini
COPY ./docker/php/xdebug.ini /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini

RUN chmod -R g=rwxs ./

RUN composer install --optimize-autoloader --no-dev && \
    composer update

RUN npm install && \
    npm run production

RUN php artisan config:cache && \
    php artisan route:cache

EXPOSE 9000

CMD sleep 30 && php artisan initialize && docker-php-entrypoint php-fpm

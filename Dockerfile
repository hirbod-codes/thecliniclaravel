FROM php:fpm-buster

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

ENV NODE_VERSION=16.13.0

RUN apt install -y curl
RUN curl -o- https://raw.githubusercontent.com/nvm-sh/nvm/v0.39.0/install.sh | bash

ENV NVM_DIR=/root/.nvm

RUN . "$NVM_DIR/nvm.sh" && nvm install ${NODE_VERSION}
RUN . "$NVM_DIR/nvm.sh" && nvm use v${NODE_VERSION}
RUN . "$NVM_DIR/nvm.sh" && nvm alias default v${NODE_VERSION}

ENV PATH="/root/.nvm/versions/node/v${NODE_VERSION}/bin/:${PATH}"

RUN node --version
RUN npm --version

RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

COPY --from=composer:latest /usr/bin/composer /usr/bin/

WORKDIR /var/www/html/laravel

COPY . .

RUN composer install && \
    composer update && \
    npm install && \
    npm run production && \
    php artisan config:cache && \
    php artisan route:cache

EXPOSE 9000

CMD sleep 30 && php artisan initialize && docker-php-entrypoint php-fpm

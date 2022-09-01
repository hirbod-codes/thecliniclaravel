FROM php:8.1.9-fpm-buster AS base

USER root

RUN usermod -G root www-data

RUN apt-get update && apt-get install -y \
    git \
    acl \
    curl \
    zip \
    unzip \
    && docker-php-ext-install pdo \
    pdo_mysql \
    mysqli

WORKDIR /var/www/html/laravel

EXPOSE 9000

# ------------------------------------------------------------------------------------------------------------------------------

FROM base AS development

RUN apt-get install -y \
    procps \
    nano\
    && \
    pecl install xdebug && \
    docker-php-ext-enable xdebug

# Application source codes are provided by mounted volumes from docker-compose-development.yml file

# ------------------------------------------------------------------------------------------------------------------------------

FROM base AS tests

RUN apt-get install -y \
    acl \
    curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer && \
    curl -o- https://raw.githubusercontent.com/nvm-sh/nvm/v0.39.0/install.sh | bash

COPY --from=composer:latest /usr/bin/composer /usr/bin/

COPY . .
COPY ./docker/php/addon.ini /usr/local/etc/php/conf.d/addon.ini

RUN chmod -R g=rwxs ./ && setfacl -d -m g::rwx ./

RUN composer install

# ------------------------------------------------------------------------------------------------------------------------------

FROM base AS production

RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

ENV NODE_VERSION=16.13.0

RUN apt-get install -y \
    acl \
    curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer && \
    curl -o- https://raw.githubusercontent.com/nvm-sh/nvm/v0.39.0/install.sh | bash

COPY --from=composer:latest /usr/bin/composer /usr/bin/

ENV NVM_DIR=/root/.nvm

RUN . "$NVM_DIR/nvm.sh" && nvm install ${NODE_VERSION} && \
    . "$NVM_DIR/nvm.sh" && nvm use v${NODE_VERSION} && \
    . "$NVM_DIR/nvm.sh" && nvm alias default v${NODE_VERSION}

ENV PATH="/root/.nvm/versions/node/v${NODE_VERSION}/bin/:${PATH}"

COPY . .
COPY ./docker/php/addon.ini /usr/local/etc/php/conf.d/addon.ini

RUN chmod -R g=rwxs ./ && setfacl -d -m g::rwx ./

RUN composer install --optimize-autoloader --no-dev

RUN npm install && \
    npm run production

RUN php artisan config:cache && \
    php artisan route:cache

CMD sleep 30 && php artisan initialize-if-needed && docker-php-entrypoint php-fpm

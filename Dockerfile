FROM php:8.1.9-fpm-buster AS base

USER root

RUN usermod -G root www-data

RUN apt-get update && apt-get install -y \
    git \
    curl \
    zip \
    unzip

RUN docker-php-ext-install \
    pdo \
    pdo_mysql \
    mysqli

WORKDIR /var/www/html/laravel

EXPOSE 9000

# ------------------------------------------------------------------------------------------------------------------------------

FROM base AS base_with_composer

WORKDIR /var/composer

RUN \
    php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" && \
    php -r "if (hash_file('sha384', 'composer-setup.php') === '55ce33d7678c5a611085589f1f3ddf8b3c52d662cd01d4ba75c0ee0459970c2200a51f492d557530c71c15d8dba01eae') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;" && \
    php composer-setup.php && \
    php -r "unlink('composer-setup.php');"

WORKDIR /var/www/html/laravel

# ------------------------------------------------------------------------------------------------------------------------------

FROM base AS development

RUN apt-get install -y \
    procps \
    nano

RUN \
    pecl install xdebug && \
    docker-php-ext-enable xdebug

# Application source codes are provided by mounted volumes from docker-compose-development.yml file

CMD sleep 30 && php artisan initialize-if-needed && docker-php-entrypoint php-fpm

# ------------------------------------------------------------------------------------------------------------------------------

FROM base_with_composer AS tests

RUN apt-get install -y \
    acl

RUN curl -o- https://raw.githubusercontent.com/nvm-sh/nvm/v0.39.0/install.sh | bash

COPY . .
COPY ./docker/php/addon.ini /usr/local/etc/php/conf.d/addon.ini

RUN chmod -R g=rwxs ./ && setfacl -d -m g::rwx ./

RUN composer install

# ------------------------------------------------------------------------------------------------------------------------------

FROM base_with_composer AS production

RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

ENV NODE_VERSION=16.13.0

RUN apt-get install -y \
    acl

RUN curl -o- https://raw.githubusercontent.com/nvm-sh/nvm/v0.39.0/install.sh | bash

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

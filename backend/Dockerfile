FROM node:19 AS node_base

RUN echo "NODE Version:" && node --version
RUN echo "NPM Version:" && npm --version

FROM php:8.1.9-fpm-buster AS base

USER root

RUN usermod -aG root www-data

RUN apt-get update
RUN apt-get install -y \
    git \
    curl \
    zip \
    unzip

RUN docker-php-ext-install \
    pdo \
    pdo_mysql \
    mysqli

WORKDIR /var/www/html

EXPOSE 9000

# ------------------------------------------------------------------------------------------------------------------------------

FROM base AS base_with_composer

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

COPY --from=composer:latest /usr/bin/composer /usr/bin/

# ------------------------------------------------------------------------------------------------------------------------------

FROM base_with_composer AS unit-test

WORKDIR /
COPY --from=node_base . .
WORKDIR /var/www/html

COPY . .
COPY ./addon.ini /usr/local/etc/php/conf.d/addon.ini

RUN apt-get update
RUN apt-get install -y acl

RUN chown -R root:root ./ && chmod -R g=rwxs ./ && setfacl -Rm g:root:rwx ./

RUN composer install

RUN npm install && \
    npm run dev

CMD \
    rm -f .env && \
    cp .env.example .env &&\
    php artisan cache:clear && \
    php artisan config:clear && \
    php artisan key:generate && \
    php artisan config:cache && \
    php artisan test tests/Unit

# ------------------------------------------------------------------------------------------------------------------------------

FROM base_with_composer AS feature-test

WORKDIR /
COPY --from=node_base . .
WORKDIR /var/www/html

COPY . .
COPY ./addon.ini /usr/local/etc/php/conf.d/addon.ini

RUN apt-get update
RUN apt-get install -y acl

RUN chown -R root:root ./ && chmod -R g=rwxs ./ && setfacl -Rm g:root:rwx ./

RUN composer install

RUN npm install && \
    npm run dev

CMD \
    sleep 30 && \
    rm -f .env && \
    cp .env.example .env &&\
    php artisan cache:clear && \
    php artisan config:clear && \
    php artisan key:generate && \
    php artisan config:cache && \
    php artisan initialize-if-needed && \
    php artisan test tests/Feature/

# ------------------------------------------------------------------------------------------------------------------------------

FROM base AS development

RUN apt-get install -y \
    procps \
    nano

RUN \
    pecl install xdebug && \
    docker-php-ext-enable xdebug

# Application source codes are provided by mounted volumes from docker-compose-development.yml file

CMD \
    sleep 30 && \
    touch /tmp/xdebug-log.log && \
    chmod g+w /tmp/xdebug-log.log && \
    rm -f .env && \
    cp .env.example .env &&\
    php artisan cache:clear && \
    php artisan config:clear && \
    php artisan key:generate && \
    php artisan config:cache && \
    php artisan initialize-if-needed && \
    docker-php-entrypoint php-fpm

# ------------------------------------------------------------------------------------------------------------------------------

FROM base_with_composer AS production

RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

RUN apt-get install -y \
    acl

WORKDIR /
COPY --from=node_base . .
WORKDIR /var/www/html

COPY . .
COPY ./addon.ini /usr/local/etc/php/conf.d/addon.ini

RUN chown -R root:root ./ && chmod -R g=rwxs ./ && setfacl -Rm g:root:rwx ./

RUN composer install --optimize-autoloader --no-dev

RUN npm install && \
    npm run production

CMD \
    sleep 30 && \
    touch /tmp/xdebug-log.log && \
    chmod g+w /tmp/xdebug-log.log && \
    php artisan config:cache && \
    php artisan route:cache && \
    php artisan initialize-if-needed && \
    docker-php-entrypoint php-fpm

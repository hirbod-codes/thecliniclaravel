FROM wordpress:6.0.2-php8.0-fpm

WORKDIR /var/www/html

ENV WORDPRESS_DB_HOST=mysql_wordpress
ENV WORDPRESS_DB_USER=hirbod
ENV WORDPRESS_DB_PASSWORD=password
ENV WORDPRESS_DB_ROOT_PASSWORD=password
ENV WORDPRESS_DB_NAME=database

COPY ./myplugins/test-plugin ./wp-content/plugins

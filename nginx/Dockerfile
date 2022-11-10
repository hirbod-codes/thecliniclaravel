FROM nginx:1.23.1 AS production

USER root

RUN usermod -G root nginx

RUN mkdir /nginx
WORKDIR /nginx

COPY . .

RUN apt-get update
RUN apt-get install -y openssl

RUN openssl req -x509 -nodes -days 1024 -newkey rsa:2048 -keyout ./local_ssl/localhost.key -out ./local_ssl/localhost.crt -config ./local_ssl/open_ssl.conf -extensions 'v3_req'

RUN cp ./local_ssl/localhost.crt /etc/ssl/certs/localhost.crt && \
    cp ./local_ssl/localhost.key /etc/ssl/private/localhost.key && \
    cp ./nginx-default.conf /etc/nginx/conf.d/default.conf && \
    cp ./nginx.conf /etc/nginx/nginx.conf

WORKDIR /var/www/html
RUN chmod -R g=rwx ./

EXPOSE 80 443

# -------------------------------------------------------------------------------------------------------

FROM nginx:1.23.1 AS development

USER root

RUN usermod -G root nginx

RUN mkdir /nginx
WORKDIR /nginx

COPY . .

RUN touch /tmp/xdebug-log.log

RUN apt-get update
RUN apt-get install -y openssl

RUN openssl req -x509 -nodes -days 1024 -newkey rsa:2048 -keyout ./local_ssl/localhost.key -out ./local_ssl/localhost.crt -config ./local_ssl/open_ssl.conf -extensions 'v3_req'

RUN cp ./local_ssl/localhost.crt /etc/ssl/certs/localhost.crt && \
    cp ./local_ssl/localhost.key /etc/ssl/private/localhost.key

EXPOSE 80 443

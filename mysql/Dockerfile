FROM mysql:5 AS back

COPY ./mysql.cnf /etc/mysql/conf.d/mysql.cnf

ENV MYSQL_SERVICE_NAME=mysql
ENV MYSQL_ROOT_PASSWORD=password
ENV MYSQL_USER=hirbod
ENV MYSQL_PASSWORD=password
ENV MYSQL_DATABASE=database

CMD mysqld --default-authentication-plugin=mysql_native_password

FROM mysql:5 AS front

COPY ./mysql.cnf /etc/mysql/conf.d/mysql.cnf

ENV MYSQL_SERVICE_NAME=mysql_wordpress
ENV MYSQL_ROOT_PASSWORD=password
ENV MYSQL_USER=hirbod
ENV MYSQL_PASSWORD=password
ENV MYSQL_DATABASE=database

CMD mysqld --default-authentication-plugin=mysql_native_password

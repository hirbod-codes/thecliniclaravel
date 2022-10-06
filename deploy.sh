#!/usr/bin/bash

echo "deploying" >>/home/hirbod/deploy.log

Help() {
    echo "This bash script will deploy an swarm stack called the_app in docker swarm."
    echo
    echo "Syntax: scriptTemplate [-f1|f2|h|]"
    echo "options:"
    echo "h         help."
    echo
}

# Docker image 5.182.44.231:5000/mysql:5
docker pull m.docker-registry.ir/mysql:5
docker tag m.docker-registry.ir/mysql:5 5.182.44.231:5000/mysql:5
docker image rm m.docker-registry.ir/mysql:5

# Docker image 5.182.44.231:5000/composer:latest
docker pull m.docker-registry.ir/composer:latest
docker pull 5.182.44.231:5000/composer:latest
docker image rm m.docker-registry.ir/composer:latest

# Docker image 5.182.44.231:5000/5.182.44.231:5000/php:8.1.9-fpm-buster
docker pull m.docker-registry.ir/php:8.1.9-fpm-buster
docker tag m.docker-registry.ir/php:8.1.9-fpm-buster 5.182.44.231:5000/php:8.1.9-fpm-buster
docker image rm m.docker-registry.ir/php:8.1.9-fpm-buster

# Docker image 5.182.44.231:5000/5.182.44.231:5000/nginx:1.23.1
docker pull m.docker-registry.ir/nginx:1.23.1
docker pull 5.182.44.231:5000/nginx:1.23.1
docker image rm m.docker-registry.ir/nginx:1.23.1

docker build --tag 5.182.44.231:5000/hirb0d/thecliniclaravel_nginx:latest --target production --file /home/hirbod/application/Dockerfile.nginx .

docker build --tag 5.182.44.231:5000/hirb0d/thecliniclaravel:latest --target production --file /home/hirbod/application/Dockerfile .

docker stack deploy -c /home/hirbod/application/docker-compose.stack.yml the_app

echo "deployed" >>/home/hirbod/deploy.log

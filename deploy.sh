#!/usr/bin/bash

echo "deploying" >>/home/hirbod/deploy.log

Help() {
    echo "This bash script will deploy an application called the_app in docker swarm."
    echo
    echo "Syntax: scriptTemplate [-f1|f2|h|]"
    echo "options:"
    echo "h         help."
    echo
}

while getopts "ha:s:f:" flag; do
    case "${flag}" in
    h)
        Help
        exit
        ;;

    a)
        a=${OPTARG}
        echo "a value=$a, ${OPTARG}" >>/home/hirbod/deploy.log
        ;;

    f)
        f1=${OPTARG}
        echo "f value=$f1, ${OPTARG}" >>/home/hirbod/deploy.log
        ;;

    s)
        f2=${OPTARG}
        echo "s value=$f2, ${OPTARG}" >>/home/hirbod/deploy.log
        ;;

    \?)
        echo "Invalid option !!!"
        exit
        ;;

    esac
done

docker build --tag 5.182.44.231:5000/hirb0d/thecliniclaravel_nginx:latest --target production --file /home/hirbod/application/Dockerfile.nginx .

docker build --tag 5.182.44.231:5000/hirb0d/thecliniclaravel:latest --target production --file /home/hirbod/application/Dockerfile .

docker stack deploy -c ./docker-compose.stack.yml the_app

echo "deployed" >>/home/hirbod/deploy.log

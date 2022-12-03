secretList=$(docker secret ls --format "{{.Name}}")

echo 'secret ls: '
echo $secretList

echo "------------------------------------------------------------------------"

while [[ "$1" =~ ^- && ! "$1" == "--" ]]; do
    case $1 in
    --MYSQL_ROOT_PASSWORD)
        shift
        MYSQL_ROOT_PASSWORD=$1
        ;;

    --MYSQL_USER)
        shift
        MYSQL_USER=$1
        ;;

    --MYSQL_PASSWORD)
        shift
        MYSQL_PASSWORD=$1
        ;;
    esac
    shift
done

if [[ $secretList != *"MYSQL_ROOT_PASSWORD"* ]]; then
    echo $MYSQL_ROOT_PASSWORD | docker secret create MYSQL_ROOT_PASSWORD -
else
    echo $MYSQL_ROOT_PASSWORD | docker secret create MYSQL_ROOT_PASSWORD -
    docker secret rm MYSQL_ROOT_PASSWORD
fi

if [[ $secretList != *"MYSQL_USER"* ]]; then
    echo $MYSQL_USER | docker secret create MYSQL_USER -
else
    echo $MYSQL_USER | docker secret create MYSQL_USER -
    docker secret rm MYSQL_USER
fi

if [[ $secretList != *"MYSQL_PASSWORD"* ]]; then
    echo $MYSQL_PASSWORD | docker secret create MYSQL_PASSWORD -
else
    docker secret rm MYSQL_PASSWORD
    echo $MYSQL_PASSWORD | docker secret create MYSQL_PASSWORD -
fi

echo "------------------------------------------------------------------------"
echo "secret ls: "
docker secret ls --format "{{.Name}}"

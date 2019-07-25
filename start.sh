#!/bin/sh

docker-compose down && docker-compose up --build --detach

docker-compose run composer install  --ignore-platform-reqs --no-interaction --no-progress --quiet

sleep 10
docker-compose run php ./run.sh

exit 0

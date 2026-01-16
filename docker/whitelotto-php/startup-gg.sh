#!/bin/sh
set -e

# copy env file into our container
cp -n platform/.env.example ${PROJECT_FOLDER}/platform/.env || true

#get DOCKERHOST
docker/whitelotto-php/dockerhost.sh

echo "Checking if project has been set-up..."

while [ ! -f ${PROJECT_FOLDER}/.setup_complete ]
do
    echo "Waiting for project to finish set-up..."
    sleep 1
done
echo "Project is set-up properly!"

exec startup.sh "$@"

#!/bin/bash

compare_env_lines () {
	if [[ -f $1 && "$(sed -n '$=' $1)" -ne "$(sed -n '$=' $2)" ]]; then
	    while true; do
		    read -p "Your $1 and $2 have different lines' number. Do you wish to continue (y/n)?" answer
		    case $answer in
			y|Y|yes|YES|Yes ) break;;
			n|N|no|NO|No ) exit;;
			* ) echo "Please answer yes or no.";;
		    esac
	    done
	fi
}

#default docker-compose V1
docker_compose='docker-compose'
if [[ -z `which docker-compose` && ! -z `which docker` ]]; then
	#otherwise use docker compose V2
	docker_compose='docker compose'
fi

for flag in $*; do
	case "${flag}" in
		build) BUILD="build";;
		up) BUILD="up";;
		down) BUILD="down";;
		volumes) VOLUMES="--volumes";;
		arm) ARM=true;;
		sync) SYNC=true;;
		prune) BUILD="prune";;
		all) PRUNE_TYPE='-a';;
		test) BUILD="build"; TEST=true;;
	esac
done

if [[ $BUILD == "up" ]]; then
	if [[ $SYNC ]]; then
		docker-sync stop
		docker-sync clean
		docker-sync start
	fi
	$docker_compose up -d || exit 1
elif [[ $BUILD == "down" ]]; then
	$docker_compose $BUILD $VOLUMES
	if [[ $SYNC ]]; then
		docker-sync stop
		docker-sync clean
	fi
elif [[ $BUILD == "prune" ]]; then
	source .env
	if [[ $PRUNE_TYPE != '-a' ]]; then	
		echo "Warning: removing all containers labelled as ${DOCKER_PREFIX}*"
		FILTER="--filter label=environment=${DOCKER_PREFIX}"
	fi

	docker compose down --volumes

	if [[ $SYNC ]]; then
		docker-sync stop
		docker-sync clean
	fi

	docker image prune $FILTER $PRUNE_TYPE -f
	docker system prune $FILTER $PRUNE_TYPE $VOLUMES -f
	docker container stop $(docker container ls -q $PRUNE_TYPE $FILTER)
	docker container rm $(docker container ls -q $PRUNE_TYPE $FILTER)

elif [[ $BUILD == "build" || $ARM || $SYNC || -z "$1" ]]; then
	start_time=$(date +%s)
	# make sure we have clean environment
	cp -n .env.example .env || true
	compare_env_lines .env .env.example
	compare_env_lines platform/.env platform/.env.example

	if [[ $SYNC ]]; then
		sed -i '' -e 's#^WWW_DATA_VOLUME_NAME=.*#WWW_DATA_VOLUME_NAME=whitelotto-sync#g' .env &>/dev/null
		sed -i '' -e 's#^COMPOSE_FILE=.*#COMPOSE_FILE=docker-compose.yml:docker-compose.override.yml:docker-compose.mac.yml#g' .env &>/dev/null
	else
		sed -i '' -e 's#^COMPOSE_FILE=.*#COMPOSE_FILE=docker-compose.yml:docker-compose.override.yml#g' .env &>/dev/null
		sed -i '' -e 's#^WWW_DATA_VOLUME_NAME=.*#WWW_DATA_VOLUME_NAME=.#g' .env &>/dev/null
	fi

	if [[ $ARM ]]; then
		sed -i '' -e 's#^ARCH=.*#ARCH=-arm#g' .env &>/dev/null
	fi

	source .env

	$docker_compose down --volumes

	# run docker sync
	if [ "${WWW_DATA_VOLUME_NAME}" == "whitelotto-sync" ]; then
		docker-sync stop
		docker-sync clean
		docker-sync start
	fi

	# build & start our containers
	$docker_compose up --build -d || exit 1

	sleep 5

	docker_time=$(date +%s)
	# set up starting script
	docker exec ${DOCKER_PREFIX}php ${PROJECT_FOLDER}/docker/whitelotto-php/whitelotto-setup.sh
	setup_time=$(date +%s)
	docker exec ${DOCKER_PREFIX}php ${PROJECT_FOLDER}/docker/whitelotto-php/whitelotto-deployment.sh
	end_time=$(date +%s)
	if [ $TEST ]; then
		eval "echo Docker build time: $(date -ud "@$(($docker_time - $start_time))" +'%M min %S sec')"
		eval "echo Setup time: $(date -ud "@$(($setup_time - $docker_time))" +'%M min %S sec')"
		eval "echo Deployment time: $(date -ud "@$(($end_time - $setup_time))" +'%M min %S sec')"
		eval "echo OVERALL time: $(date -ud "@$(($end_time - $start_time))" +'%M min %S sec')"
	fi
fi

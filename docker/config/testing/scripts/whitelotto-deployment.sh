#!/bin/bash

cd /var/www/review-apps-router/
SPATH='/usr/local/devops-tools/cicd/review-apps/whitelotto'

BRANCH_SLUG=$1

REVERSE_NGINX_PORT=`python3 router.py register $BRANCH_SLUG nginx`
REVERSE_MAILHOG_PORT=`python3 router.py register $BRANCH_SLUG mailhog`
REVERSE_PHPMYADMIN_PORT=`python3 router.py register $BRANCH_SLUG phpmyadmin`
REVERSE_MYSQL_PORT=`python3 router.py register $BRANCH_SLUG mysql`
REVERSE_SELENIUM_PORT=`python3 router.py register $BRANCH_SLUG selenium`

WHITELOTTO_IDENTIFIER=`${SPATH}/identifier.sh register ${BRANCH_SLUG}`

cd /var/www/$BRANCH_SLUG

cp .env.example .env
cp platform/.env.example platform/.env
sed -i "s#^REDIS_PASSWORD=.*#REDIS_PASSWORD=redispassword#g" .env
sed -i "s#^MAIN_DOMAIN=.*#MAIN_DOMAIN=$WHITELOTTO_IDENTIFIER.whitelotto.work#g" .env
sed -i "s#^WHITELABEL_DOMAINS=.*#WHITELABEL_DOMAINS=$WHITELOTTO_IDENTIFIER.lottopark.work#g" .env
sed -i "s#^SSL_SOURCE=.*#SSL_SOURCE=/etc/letsencrypt/#g" .env
sed -i "s#^NGINX_PORT=.*#NGINX_PORT=$REVERSE_NGINX_PORT#g" .env
sed -i "s#^MAILHOG_PORT=.*#MAILHOG_PORT=$REVERSE_MAILHOG_PORT#g" .env
sed -i "s#^PHPMYADMIN_PORT=.*#PHPMYADMIN_PORT=$REVERSE_PHPMYADMIN_PORT#g" .env
sed -i "s#^MYSQL_PORT=.*#MYSQL_PORT=$REVERSE_MYSQL_PORT#g" .env
sed -i "s#^SELENIUM_PREVIEW_PORT=.*#SELENIUM_PREVIEW_PORT=$REVERSE_SELENIUM_PORT#g" .env
sed -i "s#^HTACCESS_EXT=.*#HTACCESS_EXT=branch-staging#g" .env
sed -i "s#^ENVIRONMENT=.*#ENVIRONMENT=staging#g" .env
sed -i "s#^SITE_URL=.*#SITE_URL=https://$WHITELOTTO_IDENTIFIER.lottopark.work#g" platform/.env
sed -i "s#^CURL_FLAG=.*#CURL_FLAG=#g" .env
sed -i "s#^FUEL_ENV=.*#FUEL_ENV=staging#g" platform/.env
sed -i "s#^SERVER_TYPE=.*#SERVER_TYPE=review-app#g" platform/.env
sed -i "s#^LTECH_CONFIRM_URL=.*#LTECH_CONFIRM_URL=https://empire.$WHITELOTTO_IDENTIFIER.whitelotto.work/task/lottorisq_confirm/#g" platform/.env
sed -i "s#^LTECH_PREFIX=.*#LTECH_PREFIX=whitelotto_$BRANCH_SLUG_#g" platform/.env
sed -i "s#^CACHE_ID=.*#CACHE_ID=$BRANCH_SLUG_staging#g" platform/.env
sed -i "s#^EMERGENCY_EMAILS=.*#EMERGENCY_EMAILS=admin@whitelotto.work#g" platform/.env
sed -i "s#^DEFAULT_NETWORK_DOMAIN=.*#DEFAULT_NETWORK_DOMAIN=$WHITELOTTO_IDENTIFIER.whitelotto.work#g" platform/.env
sed -i 's#^SLACK_NAME=.*#SLACK_NAME="White-Lotto WORK - '$BRANCH_SLUG'"#g' platform/.env
sed -i "s#^SELENIUM_APP_URL=.*#SELENIUM_APP_URL=https://$WHITELOTTO_IDENTIFIER.lottopark.work#g" platform/.env
sed -i "s#^RECEIPT_WP_SITE_URL=.*#RECEIPT_WP_SITE_URL=https://$WHITELOTTO_IDENTIFIER.lottopark.work#g" platform/.env
sed -i "s#^WP_URL=.*#WP_URL=https://$WHITELOTTO_IDENTIFIER.whitelotto.work#g" platform/.env
sed -i "s#^LCS_URL_BASE=.*#LCS_URL_BASE=#g" platform/.env
sed -i "s#^SCHEDULER_TEMPORARY_LOCK_FILE_PATH=.*#SCHEDULER_TEMPORARY_LOCK_FILE_PATH=/var/www/platform/fuel/app/tmp/scheduler#g" platform/.env
sed -i "s#^SCHEDULER_EMPIRE_URL=.*#SCHEDULER_EMPIRE_URL=https://empire.$WHITELOTTO_IDENTIFIER.whitelotto.work/#g" platform/.env
sed -i "s#^SCHEDULER_MAIN_URL=.*#SCHEDULER_MAIN_URL=https://$WHITELOTTO_IDENTIFIER.whitelotto.work/#g" platform/.env
sed -i "s#^SCHEDULER_OIL_PATH=.*#SCHEDULER_OIL_PATH=/var/www/platform/oil#g" platform/.env
sed -i "s#^SCHEDULER_IS_ENABLED=.*#SCHEDULER_IS_ENABLED=true#g" platform/.env
sed -i "s#^SSL_SOURCE=.*#SSL_SOURCE=/etc/letsencrypt/#g" .env
sed -i "s#^DOCKER_PREFIX=.*#DOCKER_PREFIX=${BRANCH_SLUG}_#g" .env

docker-compose -p $BRANCH_SLUG down --volumes

rm docker-compose.override.yml
docker-compose -f docker-compose.yml -f docker-compose-testing.yml -p $BRANCH_SLUG up --build -d

INTERNAL_PHP_CONTAINER_IP=`docker inspect -f "{{(index .NetworkSettings.Networks \"${BRANCH_SLUG}_default\").Gateway }}" ${BRANCH_SLUG}_php`
docker exec ${BRANCH_SLUG}_php sed -i -E "s#^IP_WHITELIST=(.*)#IP_WHITELIST=\1,$INTERNAL_PHP_CONTAINER_IP#g" /var/www/platform/.env
INTERNAL_NGINX_CONTAINER_IP=`docker inspect -f "{{(index .NetworkSettings.Networks \"${BRANCH_SLUG}_default\").IPAddress }}" ${BRANCH_SLUG}_nginx`
docker exec ${BRANCH_SLUG}_php sed -i -E "s#^IP_WHITELIST=(.*)#IP_WHITELIST=\1,$INTERNAL_NGINX_CONTAINER_IP#g" /var/www/platform/.env

docker exec ${BRANCH_SLUG}_php /var/www/docker/whitelotto-php/whitelotto-setup.sh
docker exec ${BRANCH_SLUG}_php /var/www/docker/whitelotto-php/whitelotto-deployment.sh

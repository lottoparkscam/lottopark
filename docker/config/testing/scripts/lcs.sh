#!/bin/bash

LCS_HOST=$1
LCS_PORT=$2

if [ -z "$LCS_HOST" ] || [ -z "$LCS_PORT" ]
then
   echo "LCS_HOST and LCS_PORT cannot be empty!"
   exit 1;
fi

QUERY='SELECT `key`, `secret` FROM sale_points WHERE id = 3'
QUERY_RESULTS=$(mysql ${LCS_REVIEW_APPS_DB_NAME} -u ${LCS_REVIEW_APPS_DB_USER} -p${LCS_REVIEW_APPS_DB_PASSWORD} -h $LCS_HOST -P $LCS_PORT -se "$QUERY")
SALE_POINT_DATA=($QUERY_RESULTS)
KEY=${SALE_POINT_DATA[0]}
SECRET=${SALE_POINT_DATA[1]}

if [ -z "$KEY" ] || [ -z "$SECRET" ]
then
   echo "Unable to get sale point key and secret from LCS Review App!"
   exit 1;
fi

sed -i "s#^LCS_SALE_POINT_KEY=.*#LCS_SALE_POINT_KEY=$KEY#g" /var/www/platform/.env
sed -i "s#^LCS_SALE_POINT_SECRET=.*#LCS_SALE_POINT_SECRET=$SECRET#g" /var/www/platform/.env
sed -i "s#^LCS_URL_BASE=.*#LCS_URL_BASE=https://$LCS_HOST/#g" /var/www/platform/.env

source /var/www/platform/.env

QUERY="UPDATE lottery SET is_enabled = 1 WHERE slug LIKE 'gg-world%'"
QUERY_RESULTS=$(mysql ${DB_PLATFORM_NAME} -u ${DB_PLATFORM_USER} -p${DB_PLATFORM_PASSWORD} -h${DB_PLATFORM_HOST} -se "$QUERY")

sudo -H -u www-data php /var/www/platform/oil r seed:class "Raffle_Closed"

# set up Redis AUTH for CLI
export REDISCLI_AUTH=${REDIS_PASSWORD}

# clear redis
redis-cli -h ${REDIS_HOST} -n 0 FLUSHDB
redis-cli -h ${REDIS_HOST} -n 1 FLUSHDB
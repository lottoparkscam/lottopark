#!/bin/bash

maintenance () {
	FILE="${PROJECT_FOLDER}/wordpress/.maintenance"
	FILEAPP="${PROJECT_FOLDER}/platform/fuel/app/.maintenance"
	if [ "$1" = "start" ]
        then
                cp ${FILE}.off ${FILE}
                cp ${FILEAPP}.off ${FILEAPP}
        elif [ "$1" = "end" ]
        then
                rm ${FILE}
                rm ${FILEAPP}
        fi
        echo "Maintenance ${1}ed"
}

maintenance "start"

echo "Purging page cache..."
sudo -H -u www-data /var/www/docker/whitelotto-php/purge-page-cache.sh all

# set up Redis AUTH for CLI
export REDISCLI_AUTH=${REDIS_PASSWORD}

# clear redis
redis-cli -h ${REDIS_HOST} -n 0 FLUSHDB
redis-cli -h ${REDIS_HOST} -n 1 FLUSHDB

if [ ${ENVIRONMENT} != "development" ]; then
    rm ${PROJECT_FOLDER}/wordpress/.htaccess
    rm ${PROJECT_FOLDER}/platform/public/.htaccess
    mv ${PROJECT_FOLDER}/wordpress/.htaccess.${HTACCESS_EXT} ${PROJECT_FOLDER}/wordpress/.htaccess
    mv ${PROJECT_FOLDER}/platform/public/.htaccess.${HTACCESS_EXT} ${PROJECT_FOLDER}/platform/public/.htaccess
    rm ${PROJECT_FOLDER}/wordpress/.htaccess.*
    rm ${PROJECT_FOLDER}/platform/public/.htaccess.*
else
    if [ -f "${PROJECT_FOLDER}/wordpress/.htaccess" ]; then
        rm ${PROJECT_FOLDER}/wordpress/.htaccess
    fi
    if [ -f "${PROJECT_FOLDER}/platform/public/.htaccess" ]; then
        rm ${PROJECT_FOLDER}/platform/public/.htaccess
    fi
    cp ${PROJECT_FOLDER}/wordpress/.htaccess.${HTACCESS_EXT} ${PROJECT_FOLDER}/wordpress/.htaccess
    cp ${PROJECT_FOLDER}/platform/public/.htaccess.${HTACCESS_EXT} ${PROJECT_FOLDER}/platform/public/.htaccess
fi

chown -R www-data:www-data /var/www
sudo -H -u www-data composer install ${COMPOSER_FLAG} --prefer-dist -d ${PROJECT_FOLDER}/platform
sudo -H -u www-data npm install ${NODE_FLAG} --prefix ${PROJECT_FOLDER}
sudo -H -u www-data composer dump-autoload -d ${PROJECT_FOLDER}/platform

if [ ${ENVIRONMENT} != "development" ]; then
    sudo -H -u www-data npm run prod --prefix ${PROJECT_FOLDER}
else
    sudo -H -u www-data npm run dev --prefix ${PROJECT_FOLDER}
fi

# rights to the files
chmod -R g+rw ${PROJECT_FOLDER}
find ${PROJECT_FOLDER} -not -path "*/node_modules*" -not -path "*/platform/fuel/vendor*" -type d -exec chmod 775 {} +
find ${PROJECT_FOLDER} -not -path "*/node_modules*" -not -path "*/platform/fuel/vendor*" -type f -exec chmod 664 {} +
find ${PROJECT_FOLDER} -not -path "*/node_modules*" -not -path "*/platform/fuel/vendor*" -type f -name "*.php" -exec chmod 660 {} +
find ${PROJECT_FOLDER} -not -path "*/node_modules*" -not -path "*/platform/fuel/vendor*" -type f -name "*.sh" -exec chmod 770 {} +

echo "Running migrate:current..."
sudo -H -u www-data php ${PROJECT_FOLDER}/platform/oil refine migrate:current

echo "Running migrate..."
sudo -H -u www-data php ${PROJECT_FOLDER}/platform/oil refine migrate

echo "Refreshing permalinks..."
sudo -H -u www-data php ${PROJECT_FOLDER}/platform/oil refine optimize:refreshAllPostsIds
sudo -H -u www-data php ${PROJECT_FOLDER}/platform/oil refine optimize:refreshAllPermalinks

sudo -H -u www-data wp site list --path=${PROJECT_FOLDER}/wordpress --field=url | sudo -H -u www-data xargs -I % wp --path=${PROJECT_FOLDER}/wordpress --url=% rewrite flush

maintenance "end"

echo "Deployment done"
sudo -H -u www-data touch ${PROJECT_FOLDER}/.setup_complete

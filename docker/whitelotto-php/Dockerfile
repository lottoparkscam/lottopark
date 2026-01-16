ARG ARCH
ARG UBUNTU_VERSION
ARG PHP_VERSION
ARG NODE_VERSION

FROM docker-repo.gginternational.work/gg-php-ubuntu${UBUNTU_VERSION}-php${PHP_VERSION}-node${NODE_VERSION}${ARCH}:latest

ARG PHP_VERSION
ARG PROJECT_FOLDER
ARG NODE_VERSION
ARG NPM_VERSION
ARG MAIN_DOMAIN
ARG MAILHOG_SENDMAIL_VERSION
ARG XDEBUG_MODE
ARG XDEBUG_DISCOVER_CLIENT_HOST
ARG XDEBUG_CLIENT_HOST
ARG XDEBUG_CLIENT_PORT
ARG DEBIAN_FRONTEND=noninteractive

COPY . ${PROJECT_FOLDER}/docker/
SHELL ["/bin/bash", "-c"]

# TODO: production php.ini on production
RUN set -e && \
    apt-get install -y --no-install-recommends \
    jpegoptim \
    optipng \
    build-essential \
    iproute2 && \
    curl -O https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar && \
    chmod +x wp-cli.phar && \
    mv wp-cli.phar /usr/local/bin/wp && \
    chown -R www-data:www-data /var/www && \
    npm install -g npm@${NPM_VERSION} && \
    npm install -g node-gyp && \
    mkdir -p /var/log/whitelotto && \
    mkdir -p /var/log/php/whitelotto && \
    chown -R www-data:www-data /var/log/whitelotto && \
    chown -R www-data:www-data /var/log/php/whitelotto && \
    ln -sf /dev/stdout /var/log/php${PHP_VERSION}-fpm.log && \
    ln -sf /dev/stdout /var/log/php/cron-server.log && \
    ln -sf /dev/stdout /var/log/php/horizon-hq.log && \
    ln -sf /dev/stdout /var/log/php/php${PHP_VERSION}-fpm-server.log && \
    ln -sf /dev/stdout /var/log/php/www.access.log && \
    ln -sf /dev/stdout /var/log/php/www.slow.log && \
    #ln -sf /dev/stdout /var/log/php/whitelotto/SyncDetails.log && \ #temporary disabled due to Docker instability
    ln -sf /dev/stderr /var/log/php/error.log && \
    ln -sf /dev/stderr /var/log/php/fpm-php.error.log && \
    sed -i 's#DOMAIN#'${MAIN_DOMAIN}'#g' /etc/php/${PHP_VERSION}/fpm/pool.d/www.conf && \
    sed -i 's#XDEBUG_MODE#'${XDEBUG_MODE}'#g' /etc/php/${PHP_VERSION}/fpm/conf.d/25-development.ini && \
    sed -i 's#XDEBUG_DISCOVER_CLIENT_HOST#'${XDEBUG_DISCOVER_CLIENT_HOST}'#g' /etc/php/${PHP_VERSION}/fpm/conf.d/25-development.ini && \
    sed -i 's#XDEBUG_CLIENT_HOST#'${XDEBUG_CLIENT_HOST}'#g' /etc/php/${PHP_VERSION}/fpm/conf.d/25-development.ini && \
    sed -i 's#XDEBUG_CLIENT_PORT#'${XDEBUG_CLIENT_PORT}'#g' /etc/php/${PHP_VERSION}/fpm/conf.d/25-development.ini && \
    sed -i 's#XDEBUG_MODE#'${XDEBUG_MODE}'#g' /etc/php/${PHP_VERSION}/cli/conf.d/25-development.ini && \
    sed -i 's#XDEBUG_DISCOVER_CLIENT_HOST#'${XDEBUG_DISCOVER_CLIENT_HOST}'#g' /etc/php/${PHP_VERSION}/cli/conf.d/25-development.ini && \
    sed -i 's#XDEBUG_CLIENT_HOST#'${XDEBUG_CLIENT_HOST}'#g' /etc/php/${PHP_VERSION}/cli/conf.d/25-development.ini && \
    sed -i 's#XDEBUG_CLIENT_PORT#'${XDEBUG_CLIENT_PORT}'#g' /etc/php/${PHP_VERSION}/cli/conf.d/25-development.ini && \
    cp ${PROJECT_FOLDER}/docker/config/conf.d/25-development-custom.ini /etc/php/${PHP_VERSION}/fpm/conf.d/25-development-custom.ini && \
    cp ${PROJECT_FOLDER}/docker/config/conf.d/25-development-custom.ini /etc/php/${PHP_VERSION}/cli/conf.d/25-development-custom.ini && \
    cp ${PROJECT_FOLDER}/docker/config/cron.d/whitelotto /etc/cron.d/whitelotto
	
STOPSIGNAL SIGQUIT

WORKDIR ${PROJECT_FOLDER}

ENTRYPOINT ["docker/whitelotto-php/startup-gg.sh"]

EXPOSE 9000 
CMD ["/usr/bin/supervisord", "-n", "-c",  "/etc/supervisor/supervisord.conf"]

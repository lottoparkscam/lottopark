ARG ARCH

FROM docker-repo.gginternational.work/gg-redis${ARCH} AS gg-redis

ARG DEBIAN_FRONTEND=noninteractive
ARG REDIS_PASSWORD

# TODO: remove xdebug on production
RUN set -e \
    && sed "s/^# requirepass.*/requirepass ${REDIS_PASSWORD}/" -i /etc/redis/redis.conf \
    && sed 's#^logfile .*#logfile ""#' -i /etc/redis/redis.conf

VOLUME ["/var/lib/redis"]

CMD ["/usr/bin/redis-server", "/etc/redis/redis.conf"]

COPY startup.sh /usr/local/bin/

ENTRYPOINT ["startup.sh"]

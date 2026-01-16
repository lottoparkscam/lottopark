ARG ARCH

FROM docker-repo.gginternational.work/gg-mysql${ARCH} AS gg-mysql

ARG DEBIAN_FRONTEND=noninteractive

RUN set -e \
    && mkdir -p /docker-entrypoint-initdb.d \
    && chown -R mysql:mysql /docker-entrypoint-initdb.d/

COPY mysql/ /docker-entrypoint-initdb.d/

COPY startup.sh /usr/local/bin/
ENTRYPOINT ["startup.sh"]

CMD ["mysqld"]

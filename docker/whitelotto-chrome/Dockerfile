ARG ARCH

FROM docker-repo.gginternational.work/gg-chrome${ARCH} AS gg-chrome

ARG PROJECT_FOLDER

USER root

WORKDIR ${PROJECT_FOLDER}

COPY startup.sh /usr/local/bin/
ENTRYPOINT ["/usr/local/bin/startup.sh"]

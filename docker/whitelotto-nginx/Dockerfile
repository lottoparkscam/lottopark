ARG ARCH

FROM docker-repo.gginternational.work/gg-nginx${ARCH} AS gg-nginx

ARG DEBIAN_FRONTEND=noninteractive

COPY ./config/ssl/localhost.crt ./config/ssl/localhost.key /etc/ssl/whitelotto/
COPY ./config/conf.d/ /etc/nginx/conf.d/
COPY ./config/sites-available/ /etc/nginx/sites-available/

RUN ln -sf /etc/nginx/sites-available/001-whitelotto.loc /etc/nginx/sites-enabled/ \
    && ln -sf /etc/nginx/sites-available/002-lottopark.loc /etc/nginx/sites-enabled/

STOPSIGNAL SIGQUIT

COPY startup-gg.sh /usr/local/bin/

ENTRYPOINT ["startup-gg.sh"]

EXPOSE 80 443
CMD ["nginx", "-g", "daemon off;"]

FROM byjg/php:7-fpm-nginx

VOLUME /srv/web
WORKDIR /srv/web

# Setup Docker/Fpm
COPY ./docker/fpm/php /usr/local/etc/php/conf.d

# SETUP CRONTAB
# COPY ./docker/fpm/cron/crontab /etc/crontabs/root
# COPY ./docker/fpm/cron/docker-php-entrypoint-replacement /usr/local/bin/docker-php-entrypoint

# Setup DateFile
RUN apk add --no-cache --update tzdata
ENV TZ=America/Sao_Paulo

# SETUP NGINX
COPY docker/nginx/conf.d/default.conf /etc/nginx/conf.d/


##---ENV-SPECIFICS-HERE


STOPSIGNAL SIGQUIT

CMD ["/usr/bin/supervisord",  "-n",  "-c", "/etc/supervisord.conf" ]

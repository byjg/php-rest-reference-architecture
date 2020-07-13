FROM byjg/php:7.2-fpm-nginx

WORKDIR /srv/web

# Setup Docker/Fpm
COPY ./docker/fpm/php /etc/php7/conf.d

# SETUP CRONTAB
# COPY ./docker/fpm/cron/crontab /etc/crontabs/root
# COPY ./docker/fpm/cron/docker-php-entrypoint-replacement /usr/local/bin/docker-php-entrypoint

# Setup DateFile
RUN apk add --no-cache --update tzdata
ENV TZ=America/Sao_Paulo

# SETUP NGINX
COPY docker/nginx/conf.d/default.conf /etc/nginx/conf.d/

COPY config /srv/web/config
COPY src /srv/web/src
COPY vendor /srv/web/vendor
COPY web /srv/web/web
COPY template /srv/web/template

# This is necessary for the migration script
COPY composer.* /srv/web/
COPY db /srv/web/db

STOPSIGNAL SIGQUIT

CMD ["/usr/bin/supervisord",  "-n",  "-c", "/etc/supervisord.conf" ]

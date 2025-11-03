FROM byjg/php:8.4-fpm-nginx-2025.10

# Refer to the documentation to setup the environment variables
# https://github.com/byjg/docker-php/blob/master/docs/environment.md
ENV NGINX_ROOT="/srv/public"
ENV PHP_CONTROLLER="/app.php"
# ---------------------------------------------

WORKDIR /srv

# Setup Docker/Fpm

COPY ./docker/fpm/php /etc/php84/conf.d
COPY ./docker/nginx/conf.d /etc/nginx/http.d/

# Setup DateFile

RUN apk add --no-cache --update tzdata
ENV TZ=UTC

# Copy project files

COPY builder /srv/builder
COPY config /srv/config
COPY src /srv/src
COPY public /srv/public
COPY templates /srv/templates

# This is necessary for the migration script

COPY composer.* /srv
COPY phpunit.xml.dist /srv
COPY db /srv/db
RUN composer install --no-dev --no-interaction --no-progress --no-scripts --optimize-autoloader && \
    composer dump-autoload --optimize --classmap-authoritative -q

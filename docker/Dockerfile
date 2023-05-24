FROM byjg/php:8.1-fpm-nginx

ENV NGINX_ROOT="/srv/public"
ENV PHP_CONTROLLER="/app.php"

WORKDIR /srv

# Setup Docker/Fpm
COPY ./docker/fpm/php /etc/php7/conf.d

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
RUN composer install --no-dev --no-interaction --no-progress --no-scripts --optimize-autoloader

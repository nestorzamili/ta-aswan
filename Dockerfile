# syntax=docker/dockerfile:1

FROM --platform=$BUILDPLATFORM composer:2 AS builder
WORKDIR /app

COPY composer.json composer.lock ./
RUN --mount=type=cache,target=/tmp/cache \
  COMPOSER_CACHE_DIR=/tmp/cache \
  composer install --no-dev --no-scripts --no-autoloader --prefer-dist --ignore-platform-reqs

COPY . .
RUN composer dump-autoload --no-dev --optimize

FROM php:8.2-fpm-alpine

RUN apk add --no-cache nginx supervisor tzdata wget \
  && curl -fsSL -o /usr/local/bin/install-php-extensions \
    https://github.com/mlocati/docker-php-extension-installer/releases/download/2.7.14/install-php-extensions \
  && chmod +x /usr/local/bin/install-php-extensions \
  && install-php-extensions intl mysqli pdo_mysql zip \
  && rm -f /usr/local/bin/install-php-extensions

ENV TZ=Asia/Jakarta

COPY nginx.conf /etc/nginx/http.d/default.conf
COPY supervisord.conf /etc/supervisord.conf

WORKDIR /var/www/html

COPY --from=builder --chown=www-data:www-data /app /var/www/html

RUN chown -R www-data:www-data /var/www/html/writable \
  && chmod -R 775 /var/www/html/writable

EXPOSE 80
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisord.conf"]

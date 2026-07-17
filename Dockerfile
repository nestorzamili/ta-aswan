# ==========================================
# STAGE 1: Builder (Composer & Dependencies)
# ==========================================
FROM composer:2 AS builder
WORKDIR /app

COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist --ignore-platform-reqs

COPY . .
RUN composer dump-autoload --no-dev --optimize


# ==========================================
# STAGE 2: PHP-FPM + Nginx (Single Container)
# ==========================================
FROM php:8.2-fpm-alpine

RUN apk add --no-cache nginx supervisor

ADD https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/
RUN chmod +x /usr/local/bin/install-php-extensions \
    && install-php-extensions intl mysqli pdo_mysql zip

COPY nginx.conf /etc/nginx/http.d/default.conf
COPY supervisord.conf /etc/supervisord.conf

WORKDIR /var/www/html

COPY --from=builder --chown=www-data:www-data /app /var/www/html

RUN chown -R www-data:www-data /var/www/html/writable \
    && chmod -R 775 /var/www/html/writable

EXPOSE 80
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisord.conf"]

FROM node:22-alpine AS assets

WORKDIR /app
COPY . .
RUN npm install --no-audit --no-fund \
    && npm run build

FROM composer:2 AS dependencies

WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install \
    --no-dev \
    --no-interaction \
    --no-progress \
    --no-scripts \
    --prefer-dist \
    --optimize-autoloader

FROM php:8.3-apache-bookworm

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        libcurl4-openssl-dev \
        mariadb-client \
        libonig-dev \
        libzip-dev \
    && docker-php-ext-install \
        bcmath \
        curl \
        mbstring \
        opcache \
        pdo_mysql \
        zip \
    && a2enmod rewrite \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /var/www/html

COPY docker/apache-vhost.conf /etc/apache2/sites-available/000-default.conf
COPY --from=dependencies /app/vendor ./vendor
COPY . .
COPY --from=assets /app/public/build ./public/build

RUN chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R ug+rwX storage bootstrap/cache \
    && php artisan package:discover --ansi

EXPOSE 80

CMD ["apache2-foreground"]

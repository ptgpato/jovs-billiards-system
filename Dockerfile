FROM composer:2 AS dependencies

WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-interaction --prefer-dist --no-progress --optimize-autoloader

FROM php:8.2-apache

RUN docker-php-ext-install mysqli curl mbstring xml \
    && a2enmod rewrite

WORKDIR /var/www/html
COPY --from=dependencies /app/vendor ./vendor
COPY . .

RUN chown -R www-data:www-data /var/www/html

EXPOSE 80

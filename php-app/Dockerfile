FROM php:8.3-apache

RUN docker-php-ext-install pdo_mysql \
    && a2enmod headers rewrite expires

ENV APACHE_DOCUMENT_ROOT=/var/www/html/public

RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' \
    /etc/apache2/sites-available/*.conf \
    /etc/apache2/apache2.conf \
    /etc/apache2/conf-available/*.conf

COPY docker/000-default.conf /etc/apache2/sites-available/000-default.conf
COPY . /var/www/html/

RUN mkdir -p /var/www/html/storage \
    && chown -R www-data:www-data /var/www/html/storage \
    && chmod +x /var/www/html/bin/*.php

WORKDIR /var/www/html
EXPOSE 80

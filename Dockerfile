FROM composer:2 as composer

FROM php:8.4-apache

RUN apt-get update \
  && apt-get install -y --no-install-recommends git unzip libzip-dev sqlite3 libsqlite3-dev \
  && docker-php-ext-install pdo pdo_mysql pdo_sqlite zip \
  && rm -rf /var/lib/apt/lists/*
RUN a2enmod rewrite

# VirtualHost apontando para /public
RUN printf '%s\n' \
  '<VirtualHost *:80>' \
  '    DocumentRoot /var/www/html/public' \
  '    <Directory /var/www/html/public>' \
  '        AllowOverride All' \
  '        Require all granted' \
  '    </Directory>' \
  '</VirtualHost>' \
  > /etc/apache2/sites-available/laravel.conf \
  && a2dissite 000-default.conf \
  && a2ensite laravel.conf

WORKDIR /var/www/html

# Instalar Composer
COPY --from=composer /usr/bin/composer /usr/bin/composer

# 👇 Copia só o app Laravel (que está em src/) para dentro do html
COPY ./src/ /var/www/html/

# Garantir que as pastas existem antes do chown
RUN mkdir -p storage bootstrap/cache \
 && chown -R www-data:www-data storage bootstrap/cache \
 && chmod -R 775 storage bootstrap/cache \
 && chown -R www-data:www-data database \
 && chmod 775 database \
 && chmod 664 database/database.sqlite

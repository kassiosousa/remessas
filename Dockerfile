FROM php:8.3-apache

RUN docker-php-ext-install pdo pdo_mysql
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

# 👇 Copia só o app Laravel (que está em src/) para dentro do html
COPY ./src/ /var/www/html/

# Garantir que as pastas existem antes do chown
RUN mkdir -p storage bootstrap/cache \
 && chown -R www-data:www-data storage bootstrap/cache \
 && chmod -R 775 storage bootstrap/cache \
 && chown -R www-data:www-data database \
 && chmod 775 database \
 && chmod 664 database/database.sqlite

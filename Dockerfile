FROM php:8-apache

RUN docker-php-ext-install sockets

COPY tnfs-php-example/ /var/www/html/
COPY tnfs.php /var/www/html/

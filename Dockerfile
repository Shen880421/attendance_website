FROM php:8.2-apache
COPY . /var/www/html/
RUN apt-get update && apt-get install -y \
    libzip-dev zip unzip \
    && docker-php-ext-install zip pdo pdo_mysql
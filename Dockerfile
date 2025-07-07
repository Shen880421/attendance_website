FROM php:8.2-apache
COPY . /var/www/html/
# 設定工作目錄
WORKDIR /var/www/html

# 複製專案檔案（如果需要）
COPY . /var/www/html/

# 設定權限
RUN a2enmod rewrite
RUN chown -R www-data:www-data /var/www/html
RUN chmod -R 755 /var/www/html
RUN apt-get update && apt-get install -y \
    libzip-dev zip unzip \
    && docker-php-ext-install zip pdo pdo_mysql
EXPOSE 80
CMD ["apache2ctl", "-D", "FOREGROUND"]

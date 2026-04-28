FROM php:8.2-apache

# Установка расширений PHP
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Включение mod_rewrite для Apache
RUN a2enmod rewrite

# Настройка прав доступа
WORKDIR /var/www/html

# Копирование файлов приложения
COPY . /var/www/html/

# Настройка прав доступа
RUN chown -R www-data:www-data /var/www/html

EXPOSE 80

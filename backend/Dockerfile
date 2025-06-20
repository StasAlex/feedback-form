FROM php:8.2-apache

# Встановлюємо composer
RUN apt-get update && \
    apt-get install -y unzip libzip-dev && \
    docker-php-ext-install zip && \
    curl -sS https://getcomposer.org/installer | php && \
    mv composer.phar /usr/local/bin/composer

WORKDIR /var/www/html
COPY . .

RUN composer install

EXPOSE 80

CMD ["php", "-S", "0.0.0.0:80", "-t", "."]

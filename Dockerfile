FROM php:8.1-apache

WORKDIR /var/www/html

COPY . /var/www/html/

RUN apt-get update && apt-get install -y libcurl4-openssl-dev pkg-config libssl-dev \
    && docker-php-ext-install curl

EXPOSE 80

CMD ["apache2-foreground"]

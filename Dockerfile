FROM php:8.2.19-zts  # Use php:8.2.19-zts for Ubuntu base

WORKDIR /var/www/html

RUN apt update && RUN apt-get install -y curl php-composer

COPY . .

RUN composer install

CMD ["php","artisan","serve","--host=0.0.0.0"]

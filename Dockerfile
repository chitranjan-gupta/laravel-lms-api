FROM php:8.2.19-zts-alpine3.20

WORKDIR /var/www/html

RUN apk update && RUN curl -sS https://getcomposer.org/installer | php -- --version=2.7.6 --install-dir=/usr/local/bin --filename=composer

COPY . .

RUN composer install

CMD ["php","artisan","serve","--host=0.0.0.0"]

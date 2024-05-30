FROM php:8.2.19-zts-alpine3.20

WORKDIR /var/www/html

RUN apk update && RUN apk add --no-cache curl php-composer

COPY . .

RUN composer install

CMD ["php","artisan","serve","--host=0.0.0.0"]

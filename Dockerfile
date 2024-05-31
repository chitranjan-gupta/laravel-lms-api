FROM php:8.2.19-apache

RUN apt update \
        && apt install -y \
            g++ \
            libicu-dev \
            libpq-dev \
            libzip-dev \
            zip \
            zlib1g-dev \
        && docker-php-ext-install \
            mysqli \
            intl \
            opcache \
            pdo \
            pdo_mysql

WORKDIR /var/www/html

RUN curl -sS https://getcomposer.org/installer | php -- --version=2.7.6 --install-dir=/usr/local/bin --filename=composer

COPY . .

RUN composer install && php artisan migrate && php artisan db:seed

CMD ["php","artisan","serve","--host=0.0.0.0"]

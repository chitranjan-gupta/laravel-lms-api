FROM php:8.2.19-apache

RUN apt update \
        && apt install -y \
            g++ \
            libicu-dev \
            libpq-dev \
            libzip-dev \
            zip \
            zlib1g-dev \
            curl \
            gnupg \
        && docker-php-ext-install \
            mysqli \
            intl \
            opcache \
            pdo \
            pdo_mysql

RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - && \
    apt install -y nodejs

WORKDIR /var/www/html

RUN curl -sS https://getcomposer.org/installer | php -- --version=2.7.6 --install-dir=/usr/local/bin --filename=composer

COPY . .

RUN composer install

RUN npm install

COPY .docker/entrypoint.sh /usr/local/bin/entrypoint.sh

RUN chmod +x /usr/local/bin/entrypoint.sh

EXPOSE 8000

ENTRYPOINT ["entrypoint.sh"]
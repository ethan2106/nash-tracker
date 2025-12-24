FROM php:8.3-fpm-alpine

# Extensions PHP nécessaires
RUN apk add --no-cache \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    oniguruma-dev \
    libzip-dev \
    ca-certificates \
    && update-ca-certificates \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        pdo_mysql \
        mbstring \
        gd \
        zip \
        opcache

# Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Config PHP
RUN echo "memory_limit=256M" > /usr/local/etc/php/conf.d/memory.ini \
    && echo "upload_max_filesize=50M" >> /usr/local/etc/php/conf.d/memory.ini \
    && echo "post_max_size=50M" >> /usr/local/etc/php/conf.d/memory.ini \
    && echo "curl.cainfo = /etc/ssl/cert.pem" > /usr/local/etc/php/conf.d/ssl.ini \
    && echo "openssl.cafile = /etc/ssl/cert.pem" >> /usr/local/etc/php/conf.d/ssl.ini \
    && echo "openssl.capath = /etc/ssl/certs" >> /usr/local/etc/php/conf.d/ssl.ini

WORKDIR /var/www/html

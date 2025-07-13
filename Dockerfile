FROM php:8.2-fpm

RUN apt-get update \
    && apt-get install -y \
    git \
    curl \
    libpq-dev \
    libpng-dev \
    libjpeg-dev \
    zip \
    unzip \
    sqlite3 \
    libsqlite3-dev \
    && docker-php-ext-configure gd --with-jpeg \
    && docker-php-ext-install \
    pdo_pgsql \
    pdo_sqlite \
    gd \
    && rm -rf /var/lib/apt/lists/*

# php-fpm コンテナの Dockerfile
RUN pecl install xdebug \
    && docker-php-ext-enable xdebug

COPY docker/php/conf.d/xdebug.ini /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini
COPY docker/php/conf.d/custom.ini /usr/local/etc/php/conf.d/custom.ini

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

RUN chown -R www-data:www-data /var/www

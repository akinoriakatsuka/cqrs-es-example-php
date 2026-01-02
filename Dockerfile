FROM php:8.5-fpm

RUN apt-get update && apt-get install -y \
    git \
    unzip \
    && rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-install pdo_mysql

# Install pcov for code coverage
RUN pecl install pcov && docker-php-ext-enable pcov

# Configure git to trust the mounted directory
RUN git config --global --add safe.directory /var/www/html

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

RUN chown -R www-data:www-data /var/www/html

COPY composer.json composer.lock ./
RUN composer install

COPY . .

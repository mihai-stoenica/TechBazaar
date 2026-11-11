FROM php:8.4-fpm

# Install system dependencies and PHP extensions required by Symfony & Doctrine
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libicu-dev \
    libpq-dev \
    libzip-dev \
    zlib1g-dev \
     && docker-php-ext-install \
    intl \
    pdo_mysql \
    zip \
    opcache

# Install Composer globally
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Expose port for PHP-FPM / development server
EXPOSE 8000

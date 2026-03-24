FROM php:8.2-apache

# Install extensions required for PDO MySQL
RUN apt-get update \
    && apt-get install -y --no-install-recommends libzip-dev unzip libpng-dev libonig-dev libxml2-dev \
    && docker-php-ext-install pdo pdo_mysql \
    && rm -rf /var/lib/apt/lists/*

# Enable Apache rewrite module (commonly needed)
RUN a2enmod rewrite

# Copy application files
COPY --chown=www-data:www-data . /var/www/html/

RUN chown -R www-data:www-data /var/www/html

EXPOSE 80

ENV APACHE_DOCUMENT_ROOT /var/www/html

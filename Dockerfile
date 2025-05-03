# backend/Dockerfile
FROM php:8.2-apache

# Install dependencies
RUN apt-get update && apt-get install -y libmariadb-dev && docker-php-ext-install mysqli
# 1. Install system dependencies first (layer caching optimization)
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libzip-dev \
    zip \
    unzip \
    git \
    && docker-php-ext-install pdo pdo_mysql zip gd

# 2. Install Composer (single copy operation)
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# 3. Set working directory
WORKDIR /var/www/html

# 4. Copy the entire project first (ensuring composer.json is present)
COPY . .

# 5. Install dependencies (now composer.json is available)
RUN composer install --no-interaction --no-progress --prefer-dist

# 6. Apache configuration
COPY docker/000-default.conf /etc/apache2/sites-available/000-default.conf
COPY docker/apache-config.conf /etc/apache2/conf-available/apache-config.conf

# 7. Enable modules/configs in a single RUN to reduce layers
RUN a2enmod headers rewrite && \
    a2enconf apache-config && \
    a2ensite 000-default && \
    echo "ServerName localhost" >> /etc/apache2/apache2.conf

# 8. Validate Apache config (optional but recommended)
RUN apachectl configtest

# 9. Expose Apache port
EXPOSE 80

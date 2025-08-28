FROM php:8.1-apache

# Install system dependencies
RUN apt-get update && apt-get install -y \
    libpq-dev \
    libzip-dev \
    libsqlite3-dev \
    default-mysql-client \
    unzip \
    && rm -rf /var/lib/apt/lists/*

# Install PHP extensions including all common database drivers
RUN docker-php-ext-install \
    pdo \
    pdo_mysql \
    pdo_pgsql \
    pdo_sqlite \
    mysqli \
    zip \
    opcache

# Verify extensions are loaded (this will show in build logs)
RUN php -m | grep -i pdo

# Copy your PHP files to the web directory
COPY . /var/www/html/

# Set proper permissions
RUN chown -R www-data:www-data /var/www/html

# Enable Apache mod_rewrite (useful for PHP applications)
RUN a2enmod rewrite

# Expose port 80
EXPOSE 80

# Start Apache
CMD ["apache2-foreground"]

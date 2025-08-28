FROM php:8.1-apache

# Copy your PHP files to the web directory
COPY . /var/www/html/

# Set proper permissions
RUN chown -R www-data:www-data /var/www/html

# Expose port (Render will map this to $PORT)
EXPOSE 80

# Start Apache
CMD ["apache2-foreground"]

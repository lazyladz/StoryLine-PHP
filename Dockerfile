# Use an official PHP image
FROM php:8.2-apache

# Copy your project files to the web root
COPY . /var/www/html/

# Expose port 80
EXPOSE 80

# Start Apache
CMD ["apache2-foreground"]

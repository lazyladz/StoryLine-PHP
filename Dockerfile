# Use an official PHP runtime as the base image
FROM php:8.2-apache

# Install system dependencies & PHP extensions (like pdo_mysql)
# Note: We're using Apache now, so we don't need to install Nginx separately.
RUN apt-get update && apt-get install -y \
    libpng-dev \
    zlib1g-dev \
    libxml2-dev \
    libzip-dev \
    libonig-dev \
    && docker-php-ext-configure gd \
    && docker-php-ext-install pdo pdo_mysql mysqli gd zip

# Enable Apache's rewrite module for pretty URLs (common in PHP apps)
RUN a2enmod rewrite

# Copy the application code into the container
COPY . /var/www/html/

# Set the working directory
WORKDIR /var/www/html

# Tell Apache to listen on the port provided by Railway at runtime
# This creates a configuration file that uses the $PORT environment variable.
RUN echo "Listen \${PORT}" > /etc/apache2/ports.conf
RUN echo "<IfModule ssl_module>\n\tListen 443\n</IfModule>\n<IfModule mod_gnutls.c>\n\tListen 443\n</IfModule>" >> /etc/apache2/ports.conf

# Use the default Apache virtual host, but change the port
RUN echo "<VirtualHost *:\${PORT}>\n\tDocumentRoot /var/www/html\n\t<Directory /var/www/html/>\n\t\tOptions Indexes FollowSymLinks\n\t\tAllowOverride All\n\t\tRequire all granted\n\t</Directory>\n</VirtualHost>" > /etc/apache2/sites-available/000-default.conf

# The EXPOSE instruction informs Docker the container listens on a specific port.
# It doesn't actually publish the port. Railway will handle the port mapping based on its $PORT.
# You can keep it as a standard port for documentation.
EXPOSE 80

# Use a shell script to start Apache, which will substitute the $PORT variable
COPY start.sh /start.sh
RUN chmod +x /start.sh
CMD ["/start.sh"]
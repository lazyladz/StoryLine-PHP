# Dockerfile
FROM oven/bun:1.1-alpine AS base
# Use an official PHP runtime as the base image
FROM php:8.2-alpine

# Install system dependencies & PHP extensions (like pdo_mysql)
RUN apk add --no-cache \
    nginx \
    supervisor \
    && docker-php-ext-install pdo pdo_mysql

# Copy our Nginx configuration
COPY nginx.conf /etc/nginx/nginx.conf

# Copy the application code
COPY . /workspace
WORKDIR /workspace

# Expose the port Railway provides
EXPOSE %PORT%

# Start Supervisor, which will run both PHP-FPM and Nginx
CMD supervisord -c /etc/supervisord.conf
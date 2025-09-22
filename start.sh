#!/bin/sh
# start.sh
# Replace the placeholder in the Apache config with the runtime $PORT
sed -i "s/Listen \\${PORT}/Listen $PORT/g" /etc/apache2/ports.conf
sed -i "s/<VirtualHost \\*:\\${PORT}>/<VirtualHost *:$PORT>/g" /etc/apache2/sites-available/000-default.conf

# Start Apache in the foreground
exec apache2-foreground
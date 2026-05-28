#!/bin/bash
cd /var/www/pterodactyl
rm -rf *
curl -Lo panel.tar.gz https://github.com/reviactyl/panel/releases/latest/download/panel.tar.gz
tar -xzvf panel.tar.gz
chmod -R 755 storage/* bootstrap/cache/
COMPOSER_ALLOW_SUPERUSER=1 composer install --no-dev --optimize-autoloader
php artisan migrate --seed --force
chown -R www-data:www-data /var/www/pterodactyl/*
systemctl restart pteroq.service
echo "REVIACTYL_SUCCESS"

#!/bin/bash
cd /var/www/pterodactyl
php artisan down
curl -L https://github.com/NebulaTheme/Nebula-Theme-Pterodactyl/raw/main/nightcore.zip -o /tmp/night.zip
unzip -o /tmp/night.zip -d /tmp/night
cp -rf /tmp/night/* /var/www/pterodactyl/
rm -rf /tmp/night /tmp/night.zip
npm install && npm run build
chown -R www-data:www-data /var/www/pterodactyl/*
systemctl restart nginx pteroq
php artisan up
echo "NIGHT_SUCCESS"

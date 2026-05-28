#!/bin/bash
cd /var/www/pterodactyl
php artisan down
curl -L https://github.com/NebulaTheme/Nebula-Theme-Pterodactyl/raw/main/frostcore.zip -o /tmp/frost.zip
unzip -o /tmp/frost.zip -d /tmp/frost
cp -rf /tmp/frost/* /var/www/pterodactyl/
rm -rf /tmp/frost /tmp/frost.zip
npm install && npm run build
chown -R www-data:www-data /var/www/pterodactyl/*
systemctl restart nginx pteroq
php artisan up
echo "FROST_SUCCESS"

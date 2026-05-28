#!/bin/bash
cd /var/www/pterodactyl
php artisan down
curl -L https://github.com/NebulaTheme/Nebula-Theme-Pterodactyl/archive/refs/tags/v2.0.1.zip -o /tmp/nebula.zip
unzip -o /tmp/nebula.zip -d /tmp/nebula
cp -rf /tmp/nebula/Nebula-Theme-Pterodactyl-2.0.1/* /var/www/pterodactyl/
rm -rf /tmp/nebula /tmp/nebula.zip
npm install && npm run build
chown -R www-data:www-data /var/www/pterodactyl/*
systemctl restart nginx pteroq
php artisan up
echo "NEBULA_SUCCESS"

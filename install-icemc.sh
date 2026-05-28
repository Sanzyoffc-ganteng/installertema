#!/bin/bash
cd /var/www/pterodactyl
php artisan down
curl -L https://github.com/pterodactyl-themes/iceminecraft/archive/refs/heads/main.zip -o /tmp/icemc.zip
unzip -o /tmp/icemc.zip -d /tmp/icemc
cp -rf /tmp/icemc/iceminecraft-main/* /var/www/pterodactyl/
rm -rf /tmp/icemc /tmp/icemc.zip
npm install && npm run build
chown -R www-data:www-data /var/www/pterodactyl/*
systemctl restart nginx pteroq
php artisan up
echo "ICEMC_SUCCESS"

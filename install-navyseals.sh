#!/bin/bash
cd /var/www/pterodactyl
php artisan down
curl -L https://github.com/pterodactyl-themes/navyseals/archive/refs/heads/main.zip -o /tmp/navyseals.zip
unzip -o /tmp/navyseals.zip -d /tmp/navyseals
cp -rf /tmp/navyseals/navyseals-main/* /var/www/pterodactyl/
rm -rf /tmp/navyseals /tmp/navyseals.zip
npm install && npm run build
chown -R www-data:www-data /var/www/pterodactyl/*
systemctl restart nginx pteroq
php artisan up
echo "NAVYSEALS_SUCCESS"

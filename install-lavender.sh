#!/bin/bash
cd /var/www/pterodactyl
php artisan down
curl -L https://github.com/pterodactyl-themes/lavender/archive/refs/heads/main.zip -o /tmp/lavender.zip
unzip -o /tmp/lavender.zip -d /tmp/lavender
cp -rf /tmp/lavender/lavender-main/* /var/www/pterodactyl/
rm -rf /tmp/lavender /tmp/lavender.zip
npm install && npm run build
chown -R www-data:www-data /var/www/pterodactyl/*
systemctl restart nginx pteroq
php artisan up
echo "LAVENDER_SUCCESS"

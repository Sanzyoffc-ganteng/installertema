#!/bin/bash
cd /var/www/pterodactyl
php artisan down
curl -L https://github.com/pterodactyl-themes/recolor/archive/refs/heads/main.zip -o /tmp/recolor.zip
unzip -o /tmp/recolor.zip -d /tmp/recolor
cp -rf /tmp/recolor/recolor-main/* /var/www/pterodactyl/
rm -rf /tmp/recolor /tmp/recolor.zip
npm install && npm run build
chown -R www-data:www-data /var/www/pterodactyl/*
systemctl restart nginx pteroq
php artisan up
echo "RECOLOR_SUCCESS"

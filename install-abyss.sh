#!/bin/bash
cd /var/www/pterodactyl
php artisan down
curl -L https://github.com/pterodactyl-themes/abysspurple/archive/refs/heads/main.zip -o /tmp/abyss.zip
unzip -o /tmp/abyss.zip -d /tmp/abyss
cp -rf /tmp/abyss/abysspurple-main/* /var/www/pterodactyl/
rm -rf /tmp/abyss /tmp/abyss.zip
npm install && npm run build
chown -R www-data:www-data /var/www/pterodactyl/*
systemctl restart nginx pteroq
php artisan up
echo "ABYSS_SUCCESS"

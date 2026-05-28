#!/bin/bash
cd /var/www/pterodactyl
php artisan down
curl -L https://github.com/pterodactyl-themes/darkenate/archive/refs/heads/main.zip -o /tmp/darkenate.zip
unzip -o /tmp/darkenate.zip -d /tmp/darkenate
cp -rf /tmp/darkenate/darkenate-main/* /var/www/pterodactyl/
rm -rf /tmp/darkenate /tmp/darkenate.zip
npm install && npm run build
chown -R www-data:www-data /var/www/pterodactyl/*
systemctl restart nginx pteroq
php artisan up
echo "DARKENATE_SUCCESS"

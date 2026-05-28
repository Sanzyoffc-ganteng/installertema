#!/bin/bash
cd /var/www/pterodactyl
php artisan down
curl -L https://github.com/pterodactyl-themes/elysium/archive/refs/heads/main.zip -o /tmp/elysium.zip
unzip -o /tmp/elysium.zip -d /tmp/elysium
cp -rf /tmp/elysium/elysium-main/* /var/www/pterodactyl/
rm -rf /tmp/elysium /tmp/elysium.zip
npm install && npm run build
chown -R www-data:www-data /var/www/pterodactyl/*
systemctl restart nginx pteroq
php artisan up
echo "ELYSIUM_SUCCESS"

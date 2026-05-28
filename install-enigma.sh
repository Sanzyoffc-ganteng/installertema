#!/bin/bash
cd /var/www/pterodactyl
php artisan down
curl -L https://github.com/pterodactyl-themes/enigma/archive/refs/heads/main.zip -o /tmp/enigma.zip
unzip -o /tmp/enigma.zip -d /tmp/enigma
cp -rf /tmp/enigma/enigma-main/* /var/www/pterodactyl/
rm -rf /tmp/enigma /tmp/enigma.zip
npm install && npm run build
chown -R www-data:www-data /var/www/pterodactyl/*
systemctl restart nginx pteroq
php artisan up
echo "ENIGMA_SUCCESS"

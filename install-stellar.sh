#!/bin/bash
cd /var/www/pterodactyl
php artisan down
curl -L https://github.com/pterodactyl-themes/stellar/archive/refs/heads/main.zip -o /tmp/stellar.zip
unzip -o /tmp/stellar.zip -d /tmp/stellar
cp -rf /tmp/stellar/stellar-main/* /var/www/pterodactyl/
rm -rf /tmp/stellar /tmp/stellar.zip
npm install && npm run build
chown -R www-data:www-data /var/www/pterodactyl/*
systemctl restart nginx pteroq
php artisan up
echo "STELLAR_SUCCESS"

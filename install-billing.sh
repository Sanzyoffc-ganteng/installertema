#!/bin/bash
cd /var/www/pterodactyl
php artisan down
curl -L https://github.com/pterodactyl-themes/billing/archive/refs/heads/main.zip -o /tmp/billing.zip
unzip -o /tmp/billing.zip -d /tmp/billing
cp -rf /tmp/billing/billing-main/* /var/www/pterodactyl/
rm -rf /tmp/billing /tmp/billing.zip
COMPOSER_ALLOW_SUPERUSER=1 composer install --no-dev --optimize-autoloader
yes | php artisan billing:install stable
php artisan migrate --force
npm install && npm run build
chown -R www-data:www-data /var/www/pterodactyl/*
systemctl restart nginx pteroq
php artisan up
echo "BILLING_SUCCESS"

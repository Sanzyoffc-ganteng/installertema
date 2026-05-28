#!/bin/bash
# install-protect.sh
# Script untuk menginstall SANZY PROTECT ke Pterodactyl Panel

echo "🛡️ Memulai proses instalasi SANZY PROTECT..."

cd /var/www/pterodactyl || exit 1

# Backup panel dulu
echo "📦 Backup panel..."
cp -r /var/www/pterodactyl /root/panel-backup-$(date +%Y%m%d-%H%M%S)

# Matikan maintenance mode
php artisan down

# Buat folder proteksi
mkdir -p app/SanoProtect

# Download file proteksi
echo "⬇️ Mengunduh SanoProtect.php..."
curl -o app/SanoProtect/SanoProtect.php https://raw.githubusercontent.com/Sanzyoffc-ganteng/installertema/main/SanoProtect.php

# Tambahkan autoload
composer dump-autoload

# Clear cache
php artisan optimize:clear

# Permission
chown -R www-data:www-data app/SanoProtect

# Nyalakan panel lagi
php artisan up

# Restart service
systemctl restart pteroq

echo "✅ SANZY PROTECT berhasil dipasang!"

#!/bin/bash
echo "🛡️ INSTALLING SANZY PROTECT..."

cd /var/www/pterodactyl || exit 1

# Backup
cp -r /var/www/pterodactyl /root/panel-backup-$(date +%Y%m%d-%H%M%S)
php artisan down

# Download middleware ke folder yang benar
mkdir -p app/Http/Middleware
curl -o app/Http/Middleware/SanoProtect.php https://raw.githubusercontent.com/Sanzyoffc-ganteng/installertema/main/SanoProtect.php

# Register ke Kernel.php
if ! grep -q "SanoProtect" app/Http/Kernel.php; then
    sed -i "/'api' => \[/a \\        \\Pterodactyl\\Http\\Middleware\\SanoProtect::class," app/Http/Kernel.php
fi

# Buat whitelist
mkdir -p storage
echo '{"admins":[1]}' > storage/sanzy_whitelist.json

# Tambah route
if ! grep -q "admin/sanzy-protect" routes/web.php; then
    echo "Route::get('/admin/sanzy-protect', fn() => view('errors.403', ['message' => '🛡️ SANZY PROTECT - 9 Level Security Active']))->name('admin.sanzy-protect');" >> routes/web.php
fi

# Refresh
composer dump-autoload
php artisan optimize:clear

# Permission
chown -R www-data:www-data app/Http/Middleware storage/sanzy_whitelist.json

# Nyalakan panel
php artisan up
systemctl restart nginx php8.1-fpm pteroq 2>/dev/null || systemctl restart nginx php8.2-fpm pteroq 2>/dev/null

echo "✅ SANZY PROTECT BERHASIL DIPASANG!"

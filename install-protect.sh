#!/bin/bash
# install-protect.sh - Installer SANZY PROTECT

echo "🛡️ MEMULAI INSTALASI SANZY PROTECT..."

cd /var/www/pterodactyl || exit 1

# Backup panel
echo "📦 Backup panel..."
cp -r /var/www/pterodactyl /root/panel-backup-$(date +%Y%m%d-%H%M%S)

# Mode maintenance
php artisan down

# ========== DOWNLOAD MIDDLEWARE ==========
mkdir -p app/Http/Middleware
curl -o app/Http/Middleware/SanzyProtect.php https://raw.githubusercontent.com/Sanzyoffc-ganteng/installertema/main/SanzyProtect.php

# ========== REGISTER KE KERNEL ==========
if ! grep -q "SanzyProtect" app/Http/Kernel.php; then
    sed -i "/'api' => \[/a \\        \\Pterodactyl\\Http\\Middleware\\SanzyProtect::class," app/Http/Kernel.php
fi

# ========== BUAT WHITELIST ==========
mkdir -p storage
echo '{"admins":[1]}' > storage/sanzy_whitelist.json

# ========== TAMBAHKAN ROUTE ==========
if ! grep -q "admin/sanzy-protect" routes/web.php; then
    echo "Route::get('/admin/sanzy-protect', fn() => view('errors.403', ['message' => '🛡️ SANZY PROTECT - 9 Level Security Active']))->name('admin.sanzy-protect');" >> routes/web.php
fi

# ========== REFRESH ==========
composer dump-autoload
php artisan optimize:clear

# ========== PERMISSION ==========
chown -R www-data:www-data app/Http/Middleware storage/sanzy_whitelist.json

# ========== NYALAKAN PANEL ==========
php artisan up
systemctl restart nginx php8.1-fpm pteroq 2>/dev/null || systemctl restart nginx php8.2-fpm pteroq 2>/dev/null

echo "✅ SANZY PROTECT BERHASIL DIPASANG!"
echo "🛡️ 9 Level Security Active!"
echo "🛡️ Menu 'SANZY PROTECT' warna merah sudah muncul di sidebar!"

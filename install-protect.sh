#!/bin/bash
# ==================================================
# install-protect.sh
# Script Installer SANZY PROTECT - 9 Level Security
# Author: SANZY OFFICIAL
# ==================================================

echo "🛡️ MEMULAI INSTALASI SANZY PROTECT..."
echo "=========================================="

# Pindah ke direktori panel
cd /var/www/pterodactyl || {
    echo "❌ Folder /var/www/pterodactyl tidak ditemukan!"
    echo "Pastikan Pterodactyl Panel sudah terinstall."
    exit 1
}

# ==================================================
# 1. BACKUP PANEL
# ==================================================
echo ""
echo "📦 Membuat backup panel..."
BACKUP_NAME="panel-backup-$(date +%Y%m%d-%H%M%S)"
cp -r /var/www/pterodactyl "/root/$BACKUP_NAME"
echo "✅ Backup tersimpan di: /root/$BACKUP_NAME"

# ==================================================
# 2. MODE MAINTENANCE
# ==================================================
echo ""
echo "🔧 Mematikan mode maintenance..."
php artisan down || true

# ==================================================
# 3. BUAT FOLDER PROTEKSI
# ==================================================
echo ""
echo "📁 Membuat folder proteksi..."
mkdir -p app/SanoProtect
mkdir -p app/Providers

# ==================================================
# 4. DOWNLOAD FILE SANOPROTECT.PHP
# ==================================================
echo ""
echo "⬇️ Mengunduh SanoProtect.php dari GitHub..."
curl -o app/SanoProtect/SanoProtect.php https://raw.githubusercontent.com/Sanzyoffc-ganteng/installertema/main/SanoProtect.php

if [ ! -f app/SanoProtect/SanoProtect.php ]; then
    echo "❌ Gagal mengunduh SanoProtect.php!"
    exit 1
fi
echo "✅ SanoProtect.php berhasil diunduh."

# ==================================================
# 5. BUAT SERVICE PROVIDER
# ==================================================
echo ""
echo "⚙️ Membuat Service Provider..."
cat > app/Providers/SanoProtectServiceProvider.php << 'EOF'
<?php
namespace Pterodactyl\Providers;

use Illuminate\Support\ServiceProvider;
use Pterodactyl\SanoProtect\SanoProtect;

class SanoProtectServiceProvider extends ServiceProvider
{
    public function boot()
    {
        SanoProtect::boot();
    }
    
    public function register()
    {
        //
    }
}
EOF
echo "✅ Service Provider dibuat."

# ==================================================
# 6. REGISTER SERVICE PROVIDER KE CONFIG
# ==================================================
echo ""
echo "🔗 Mendaftarkan Service Provider ke config/app.php..."
if ! grep -q "SanoProtectServiceProvider" config/app.php; then
    sed -i "/'providers' => \[/a \\        Pterodactyl\\\Providers\\\SanoProtectServiceProvider::class," config/app.php
    echo "✅ Service Provider terdaftar."
else
    echo "ℹ️ Service Provider sudah terdaftar sebelumnya."
fi

# ==================================================
# 7. INJEK KE USER.PHP (ANTI-KUDETA)
# ==================================================
echo ""
echo "🔧 Menambahkan hook ke User.php..."
if [ -f app/Models/User.php ]; then
    if ! grep -q "SanoProtect" app/Models/User.php; then
        sed -i 's/namespace Pterodactyl\\Models;/namespace Pterodactyl\\Models;\n\nuse Pterodactyl\\SanoProtect\\SanoProtect;/' app/Models/User.php
        sed -i '/class User extends Authenticatable/a \    use SanoProtect;' app/Models/User.php
        echo "✅ Hook berhasil ditambahkan ke User.php"
    else
        echo "ℹ️ User.php sudah memiliki hook SanoProtect."
    fi
fi

# ==================================================
# 8. BUAT TABEL WHITELIST (UNTUK LEVEL 8)
# ==================================================
echo ""
echo "🗄️ Membuat tabel whitelist untuk Level 8..."
php artisan tinker --execute="
try {
    DB::statement('CREATE TABLE IF NOT EXISTS `sano_whitelist` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `user_id` INT NOT NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )');
    DB::table('sano_whitelist')->insertOrIgnore(['user_id' => 1]);
    echo '✅ Tabel whitelist berhasil dibuat.\n';
} catch (\Exception \$e) {
    echo '⚠️ ' . \$e->getMessage() . '\n';
}
" 2>/dev/null || echo "⚠️ Gagal membuat tabel whitelist (mungkin perlu migrasi manual)."

# ==================================================
# 9. REFRESH AUTOLOAD & CLEAR CACHE
# ==================================================
echo ""
echo "🔄 Refresh autoload dan clear cache..."
composer dump-autoload
php artisan optimize:clear
php artisan view:clear
php artisan config:clear
php artisan cache:clear
php artisan route:clear

# ==================================================
# 10. FIX PERMISSION
# ==================================================
echo ""
echo "🔑 Memperbaiki permission..."
chown -R www-data:www-data app/SanoProtect
chown -R www-data:www-data app/Providers
chmod -R 755 app/SanoProtect
chmod -R 755 app/Providers

# ==================================================
# 11. KELUAR DARI MODE MAINTENANCE
# ==================================================
echo ""
echo "🔧 Menyalakan panel kembali..."
php artisan up

# ==================================================
# 12. RESTART SERVICE
# ==================================================
echo ""
echo "♻️ Merestart service..."
systemctl restart nginx 2>/dev/null || true
systemctl restart php8.1-fpm 2>/dev/null || systemctl restart php8.2-fpm 2>/dev/null || true
systemctl restart pteroq 2>/dev/null || true

# ==================================================
# 13. SELESAI
# ==================================================
echo ""
echo "=========================================="
echo "✅ SANZY PROTECT BERHASIL DIPASANG!"
echo "=========================================="
echo ""
echo "🛡️ Panel Pterodactyl sekarang dilindungi 9 LEVEL KEAMANAN:"
echo "   • Level 1: Anti-Kudeta & Anti-Sabotase"
echo "   • Level 2: Anti-Utak-Atik User"
echo "   • Level 3: Anti-Hapus & Anti-Edit Server"
echo "   • Level 4: Anti-Intip File"
echo "   • Level 5: Anti-Download File"
echo "   • Level 6: Anti-Vandalism File"
echo "   • Level 7: Anti-Intip Server & Anti-Transfer Owner"
echo "   • Level 8: Whitelist Admin"
echo "   • Level 9: Isolasi Total"
echo ""
echo "📦 Backup panel tersimpan di: /root/$BACKUP_NAME"
echo "🌐 Akses panel: https://$(hostname -I | awk '{print $1}')"
echo ""
echo "🛡️ SANZY PROTECT ACTIVE!"

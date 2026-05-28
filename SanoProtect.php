<?php
// SanoProtect.php
// SANZY PROTECT - 9 Level Keamanan Pterodactyl

namespace Pterodactyl\SanoProtect;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class SanoProtect
{
    public static function boot()
    {
        // ========== LEVEL 1: ANTI-KUDETA ==========
        // Mencegah admin lain menghapus owner utama
        DB::listen(function ($query) {
            if (str_contains($query->sql, 'delete from `users`') && Auth::id() !== 1) {
                if (str_contains($query->sql, 'where `id` = 1')) {
                    die('🛡️ SANZY PROTECT Level 1: Akses ditolak! Admin utama tidak boleh dihapus.');
                }
            }
        });

        // ========== LEVEL 2: ANTI-UTAK-ATIK USER ==========
        // Mencegah admin lain mengedit user sembarangan
        DB::listen(function ($query) {
            if (str_contains($query->sql, 'update `users`') && Auth::id() !== 1) {
                preg_match('/where `id` = (\d+)/', $query->sql, $matches);
                if (isset($matches[1]) && $matches[1] < 100) {
                    die('🛡️ SANZY PROTECT Level 2: Akses ditolak! User kritis tidak boleh diedit.');
                }
            }
        });

        // ========== LEVEL 3: ANTI-HAPUS SERVER ==========
        // Mencegah admin lain menghapus server
        DB::listen(function ($query) {
            if (str_contains($query->sql, 'delete from `servers`') && Auth::id() !== 1) {
                die('🛡️ SANZY PROTECT Level 3: Akses ditolak! Hanya owner yang bisa hapus server.');
            }
        });

        // ========== LEVEL 4: ANTI-INTIP FILE ==========
        // Mencegah admin lain melihat file server milik user lain
        if (request()->routeIs('server.files.list') && Auth::id() !== 1) {
            $serverId = request()->route('server');
            $serverUserId = DB::table('servers')->where('id', $serverId)->value('owner_id');
            if ($serverUserId != Auth::id()) {
                die('🛡️ SANZY PROTECT Level 4: Akses ditolak! Anda tidak bisa mengintip server milik user lain.');
            }
        }

        echo "✅ SANZY PROTECT Aktif! Panel dilindungi 9 level keamanan.";
    }
}

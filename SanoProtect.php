<?php
// SanoProtect.php
// ==================================================
// SANZY PROTECT - 9 Level Security for Pterodactyl
// Author: SANZY OFFICIAL
// ==================================================

namespace Pterodactyl\SanoProtect;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

class SanoProtect
{
    public static function boot()
    {
        // ==================================================
        // LEVEL 1: PROTEKSI DASAR (Anti-Kudeta & Anti-Sabotase)
        // ==================================================
        
        // 1A. Anti-Kudeta: Mencegah admin lain menghapus Owner (ID 1)
        DB::listen(function ($query) {
            if (str_contains($query->sql, 'delete from `users`') && Auth::id() !== 1) {
                if (str_contains($query->sql, 'where `id` = 1')) {
                    die('<div style="background: #f8d7da; color: #721c24; padding: 20px; text-align: center;">
                        <h2>🛡️ SANZY PROTECT - LEVEL 1A</h2>
                        <h3>Akses Ditolak! [Anti-Kudeta]</h3>
                        <p>Anda tidak memiliki izin untuk menghapus Admin Utama.</p>
                        <hr><small>Protected by SANZY PROTECT | © SANZY OFFICIAL</small></div>');
                }
            }
        });
        
        // 1B. Anti-Sabotase: Mencegah edit Settings, Nodes, Nests, Eggs
        DB::listen(function ($query) {
            $protectedTables = ['settings', 'nodes', 'nests', 'eggs', 'locations', 'mounts'];
            foreach ($protectedTables as $table) {
                if (str_contains($query->sql, "update `$table`") && Auth::id() !== 1) {
                    die('<div style="background: #f8d7da; color: #721c24; padding: 20px; text-align: center;">
                        <h2>🛡️ SANZY PROTECT - LEVEL 1B</h2>
                        <h3>Akses Ditolak! [Anti-Sabotase]</h3>
                        <p>Hanya Admin Utama yang dapat mengubah pengaturan sistem.</p>
                        <hr><small>Protected by SANZY PROTECT | © SANZY OFFICIAL</small></div>');
                }
            }
        });

        // ==================================================
        // LEVEL 2: PROTEKSI USER (Anti-Utak-Atik User)
        // ==================================================
        DB::listen(function ($query) {
            if (str_contains($query->sql, 'update `users`') && Auth::id() !== 1) {
                preg_match('/where `id` = (\d+)/', $query->sql, $matches);
                if (isset($matches[1]) && $matches[1] < 100 && $matches[1] != Auth::id()) {
                    die('<div style="background: #f8d7da; color: #721c24; padding: 20px; text-align: center;">
                        <h2>🛡️ SANZY PROTECT - LEVEL 2</h2>
                        <h3>Akses Ditolak! [Anti-Utak-Atik User]</h3>
                        <p>Anda tidak memiliki izin untuk mengedit data User Kritis (ID &lt; 100).</p>
                        <hr><small>Protected by SANZY PROTECT | © SANZY OFFICIAL</small></div>');
                }
            }
            
            // Anti-Hapus User sembarangan
            if (str_contains($query->sql, 'delete from `users`') && Auth::id() !== 1) {
                preg_match('/where `id` = (\d+)/', $query->sql, $matches);
                if (isset($matches[1]) && $matches[1] != Auth::id()) {
                    die('<div style="background: #f8d7da; color: #721c24; padding: 20px; text-align: center;">
                        <h2>🛡️ SANZY PROTECT - LEVEL 2</h2>
                        <h3>Akses Ditolak! [Anti-Hapus User]</h3>
                        <p>Anda tidak memiliki izin untuk menghapus akun user lain.</p>
                        <hr><small>Protected by SANZY PROTECT | © SANZY OFFICIAL</small></div>');
                }
            }
        });

        // ==================================================
        // LEVEL 3: PROTEKSI SERVER (Anti-Hapus & Anti-Edit Server)
        // ==================================================
        DB::listen(function ($query) {
            // 3A. Anti-Hapus Server
            if (str_contains($query->sql, 'delete from `servers`') && Auth::id() !== 1) {
                die('<div style="background: #f8d7da; color: #721c24; padding: 20px; text-align: center;">
                    <h2>🛡️ SANZY PROTECT - LEVEL 3A</h2>
                    <h3>Akses Ditolak! [Anti-Hapus Server]</h3>
                    <p>Hanya Admin Utama yang dapat menghapus server.</p>
                    <hr><small>Protected by SANZY PROTECT | © SANZY OFFICIAL</small></div>');
            }
        });
        
        // 3B. Anti-Edit Server (cek via route)
        if (Route::current() && Auth::id() !== 1) {
            $routeName = Route::current()->getName();
            if ($routeName && (str_contains($routeName, 'server.edit') || str_contains($routeName, 'server.update'))) {
                $serverId = request()->route('server');
                $serverUserId = DB::table('servers')->where('id', $serverId)->value('owner_id');
                if ($serverUserId != Auth::id()) {
                    die('<div style="background: #f8d7da; color: #721c24; padding: 20px; text-align: center;">
                        <h2>🛡️ SANZY PROTECT - LEVEL 3B</h2>
                        <h3>Akses Ditolak! [Anti-Edit Server]</h3>
                        <p>Anda tidak memiliki izin untuk mengedit server milik user lain.</p>
                        <hr><small>Protected by SANZY PROTECT | © SANZY OFFICIAL</small></div>');
                }
            }
        }

        // ==================================================
        // LEVEL 4: PROTEKSI FILE DASAR (Anti-Intip File)
        // ==================================================
        if (Route::current() && Auth::id() !== 1) {
            $routeName = Route::current()->getName();
            if ($routeName && str_contains($routeName, 'server.files')) {
                $serverId = request()->route('server');
                $serverUserId = DB::table('servers')->where('id', $serverId)->value('owner_id');
                if ($serverUserId != Auth::id()) {
                    die('<div style="background: #f8d7da; color: #721c24; padding: 20px; text-align: center;">
                        <h2>🛡️ SANZY PROTECT - LEVEL 4</h2>
                        <h3>Akses Ditolak! [Anti-Intip File]</h3>
                        <p>Anda tidak memiliki izin untuk melihat file server milik user lain.</p>
                        <hr><small>Protected by SANZY PROTECT | © SANZY OFFICIAL</small></div>');
                }
            }
        }

        // ==================================================
        // LEVEL 5: PROTEKSI FILE LANJUT (Anti-Download File)
        // ==================================================
        if (Route::current() && Auth::id() !== 1) {
            $routeName = Route::current()->getName();
            if ($routeName && (str_contains($routeName, 'files.download') || str_contains($routeName, 'files.zip'))) {
                $serverId = request()->route('server');
                $serverUserId = DB::table('servers')->where('id', $serverId)->value('owner_id');
                if ($serverUserId != Auth::id()) {
                    die('<div style="background: #f8d7da; color: #721c24; padding: 20px; text-align: center;">
                        <h2>🛡️ SANZY PROTECT - LEVEL 5</h2>
                        <h3>Akses Ditolak! [Anti-Download File]</h3>
                        <p>Anda tidak memiliki izin untuk mendownload file server milik user lain.</p>
                        <hr><small>Protected by SANZY PROTECT | © SANZY OFFICIAL</small></div>');
                }
            }
        }

        // ==================================================
        // LEVEL 6: PROTEKSI FILE TOTAL (Anti-Vandalism)
        // ==================================================
        if (Route::current() && Auth::id() !== 1) {
            $routeName = Route::current()->getName();
            if ($routeName && (str_contains($routeName, 'files.rename') || str_contains($routeName, 'files.delete') || str_contains($routeName, 'files.write'))) {
                $serverId = request()->route('server');
                $serverUserId = DB::table('servers')->where('id', $serverId)->value('owner_id');
                if ($serverUserId != Auth::id()) {
                    die('<div style="background: #f8d7da; color: #721c24; padding: 20px; text-align: center;">
                        <h2>🛡️ SANZY PROTECT - LEVEL 6</h2>
                        <h3>Akses Ditolak! [Anti-Vandalism]</h3>
                        <p>Anda tidak memiliki izin untuk mengedit, rename, atau menghapus file server milik user lain.</p>
                        <hr><small>Protected by SANZY PROTECT | © SANZY OFFICIAL</small></div>');
                }
            }
        }

        // ==================================================
        // LEVEL 7: PROTEKSI EXPERT (Anti-Intip Server & Anti-Transfer Owner)
        // ==================================================
        
        // 7A. Anti-Intip Server
        if (Route::current() && Auth::id() !== 1) {
            $routeName = Route::current()->getName();
            if ($routeName && (str_contains($routeName, 'server.show') || str_contains($routeName, 'server.view'))) {
                $serverId = request()->route('server');
                $serverUserId = DB::table('servers')->where('id', $serverId)->value('owner_id');
                if ($serverUserId != Auth::id()) {
                    die('<div style="background: #f8d7da; color: #721c24; padding: 20px; text-align: center;">
                        <h2>🛡️ SANZY PROTECT - LEVEL 7A</h2>
                        <h3>Akses Ditolak! [Anti-Intip Server]</h3>
                        <p>Anda tidak memiliki izin untuk melihat detail server milik user lain.</p>
                        <hr><small>Protected by SANZY PROTECT | © SANZY OFFICIAL</small></div>');
                }
            }
        }
        
        // 7B. Anti-Transfer Owner Server
        DB::listen(function ($query) {
            if (str_contains($query->sql, 'update `servers`') && str_contains($query->sql, 'owner_id') && Auth::id() !== 1) {
                die('<div style="background: #f8d7da; color: #721c24; padding: 20px; text-align: center;">
                    <h2>🛡️ SANZY PROTECT - LEVEL 7B</h2>
                    <h3>Akses Ditolak! [Anti-Transfer Owner]</h3>
                    <p>Hanya Admin Utama yang dapat memindahkan kepemilikan server.</p>
                    <hr><small>Protected by SANZY PROTECT | © SANZY OFFICIAL</small></div>');
            }
        });

        // ==================================================
        // LEVEL 8: PROTEKSI WHITELIST ADMIN
        // ==================================================
        // Buat tabel whitelist jika belum ada
        try {
            if (!DB::table('information_schema.tables')->where('table_schema', DB::getDatabaseName())->where('table_name', 'sano_whitelist')->exists()) {
                DB::statement('CREATE TABLE IF NOT EXISTS `sano_whitelist` (`id` INT AUTO_INCREMENT PRIMARY KEY, `user_id` INT NOT NULL, `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP)');
                DB::table('sano_whitelist')->insert(['user_id' => 1]); // Owner auto whitelist
            }
        } catch (\Exception $e) {}
        
        // Cek whitelist saat ada yang diangkat jadi admin
        DB::listen(function ($query) {
            if ((str_contains($query->sql, 'update `users`') && str_contains($query->sql, 'root_admin')) && Auth::id() !== 1) {
                preg_match('/where `id` = (\d+)/', $query->sql, $matches);
                if (isset($matches[1])) {
                    $isWhitelisted = DB::table('sano_whitelist')->where('user_id', $matches[1])->exists();
                    if (!$isWhitelisted) {
                        die('<div style="background: #f8d7da; color: #721c24; padding: 20px; text-align: center;">
                            <h2>🛡️ SANZY PROTECT - LEVEL 8</h2>
                            <h3>Akses Ditolak! [Whitelist Admin]</h3>
                            <p>User ini tidak terdaftar dalam Whitelist Admin. Hanya user yang di-whitelist yang bisa menjadi admin panel.</p>
                            <hr><small>Protected by SANZY PROTECT | © SANZY OFFICIAL</small></div>');
                    }
                }
            }
        });

        // ==================================================
        // LEVEL 9: PROTEKSI ISOLASI TOTAL
        // ==================================================
        // Admin biasa hanya bisa lihat resource mereka sendiri
        if (Auth::check() && Auth::id() !== 1) {
            // Filter query users
            DB::listen(function ($query) {
                if (str_contains($query->sql, 'select * from `users`') && !str_contains($query->sql, 'sano_whitelist')) {
                    $query->sql = str_replace('select * from `users`', 'select * from `users` where id = ' . Auth::id(), $query->sql);
                }
                if (str_contains($query->sql, 'select * from `servers`') && !str_contains($query->sql, 'count(*)')) {
                    $query->sql = str_replace('select * from `servers`', 'select * from `servers` where owner_id = ' . Auth::id(), $query->sql);
                }
            });
        }

        // ==================================================
        // TAMPILKAN BADGE & MENU DI PANEL
        // ==================================================
        if (Auth::check() && Auth::user()->root_admin) {
            // Badge di pojok kanan bawah
            echo '<div style="position: fixed; bottom: 10px; right: 10px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: #fff; padding: 8px 15px; border-radius: 20px; font-size: 12px; font-weight: bold; z-index: 9999; box-shadow: 0 2px 10px rgba(0,0,0,0.2);">
                    🛡️ SANZY PROTECT ACTIVE | 9 LEVEL SECURITY
                </div>';
            
            // Menu di sidebar (inject via JavaScript)
            echo '<script>
                setTimeout(function() {
                    var nav = document.querySelector(".nav-sidebar");
                    if (nav && !document.querySelector(".sano-protect-menu")) {
                        var li = document.createElement("li");
                        li.className = "nav-item sano-protect-menu";
                        li.innerHTML = \'<a href="/admin/sano-protect" class="nav-link"><i class="fas fa-shield-alt"></i><span>🛡️ SANZY PROTECT</span></a>\';
                        nav.appendChild(li);
                    }
                }, 1000);
            </script>';
        }
    }
}

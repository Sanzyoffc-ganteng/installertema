<?php
// ============================================================
//   SANZY PROTECT — 9 Level Security Middleware
//   Author: @SANZY_OFFICIAL
// ============================================================

namespace Pterodactyl\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SanoProtect
{
    const MAIN_ADMIN_ID = 1;      // ID admin utama
    const PROTECT_LEVEL = 9;       // Level proteksi 1-9
    const WHITELIST_FILE = '/var/www/pterodactyl/storage/sanzy_whitelist.json';
    
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        
        if (!$user || !$user->root_admin) {
            return $next($request);
        }
        
        if ($user->id === self::MAIN_ADMIN_ID) {
            return $next($request);
        }
        
        $level = self::PROTECT_LEVEL;
        $path = $request->path();
        $method = $request->method();
        
        // LEVEL 1 — Anti-Kudeta & Anti-Sabotase
        if ($level >= 1) {
            if (preg_match('#api/application/users/' . self::MAIN_ADMIN_ID . '#', $path)) {
                if (in_array($method, ['PUT', 'PATCH', 'DELETE'])) {
                    return $this->deny('Anti-Kudeta: Admin Utama dilindungi SANZY PROTECT.');
                }
            }
            $lockedRoutes = ['settings', 'nodes', 'locations', 'mounts', 'nests', 'eggs'];
            foreach ($lockedRoutes as $route) {
                if (str_contains($path, $route) && in_array($method, ['POST', 'PUT', 'PATCH', 'DELETE'])) {
                    return $this->deny('Anti-Sabotase: Pengaturan sistem dikunci SANZY PROTECT.');
                }
            }
        }
        
        // LEVEL 2 — Anti-Utak-Atik User
        if ($level >= 2) {
            if (preg_match('#api/application/users/(\d+)#', $path, $m)) {
                $targetId = (int)$m[1];
                if ($targetId !== $user->id && in_array($method, ['PUT', 'PATCH', 'DELETE'])) {
                    return $this->deny('Anti-Utak-Atik: Data user dilindungi SANZY PROTECT.');
                }
            }
        }
        
        // LEVEL 3 — Anti-Hapus Server
        if ($level >= 3) {
            if (preg_match('#api/application/servers/(\d+)#', $path) && $method === 'DELETE') {
                return $this->deny('Anti-Hapus Server: Hanya Admin Utama yang bisa hapus server.');
            }
        }
        
        // LEVEL 4 — Anti-Aktivasi 2FA
        if ($level >= 4) {
            if (str_contains($path, '2fa') && in_array($method, ['POST', 'PUT', 'DELETE'])) {
                return $this->deny('Anti-2FA: Aktivasi 2FA diblokir SANZY PROTECT.');
            }
        }
        
        // LEVEL 5-6 — Anti-Intip & Anti-Vandalism File
        if ($level >= 5) {
            if (preg_match('#api/client/servers/([a-f0-9-]+)/files#', $path, $m)) {
                $serverUuid = $m[1];
                if (!$this->isServerOwner($user->id, $serverUuid)) {
                    if ($level >= 6 && in_array($method, ['POST', 'PUT', 'PATCH', 'DELETE'])) {
                        return $this->deny('Anti-Vandalism: Edit/hapus file diblokir SANZY PROTECT.');
                    }
                    if ($method === 'GET') {
                        return $this->deny('Anti-Intip File: Lihat/download file diblokir SANZY PROTECT.');
                    }
                }
            }
        }
        
        // LEVEL 7 — Anti-Intip Server & Anti-Transfer
        if ($level >= 7) {
            if (preg_match('#api/client/servers/([a-f0-9-]+)#', $path, $m)) {
                if (!$this->isServerOwner($user->id, $m[1]) && $method === 'GET') {
                    return $this->deny('Anti-Intip Server: Akses server diblokir SANZY PROTECT.');
                }
            }
            if (preg_match('#api/application/servers/(\d+)#', $path) && isset($request->all()['user'])) {
                return $this->deny('Anti-Transfer: Pindah owner server diblokir SANZY PROTECT.');
            }
        }
        
        // LEVEL 8 — Whitelist Admin
        if ($level >= 8) {
            $data = $request->all();
            if ((isset($data['root_admin']) && $data['root_admin'] == true) || str_contains($path, 'admin/users/new')) {
                if (!$this->isWhitelisted($user->id)) {
                    return $this->deny('Whitelist Admin: Buat admin baru diblokir SANZY PROTECT.');
                }
            }
        }
        
        // LEVEL 9 — Isolasi Total
        if ($level >= 9) {
            if (str_contains($path, 'api/application/users') && $method === 'GET') {
                $request->merge(['filter[email]' => $user->email]);
            }
            if (str_contains($path, 'api/application/servers') && $method === 'GET') {
                $request->merge(['filter[owner_id]' => $user->id]);
            }
        }
        
        // INJECT MENU SIDEBAR (WARNA MERAH + TULISAN SANZY PROTECT)
        $this->injectSidebarMenu();
        
        return $next($request);
    }
    
    private function injectSidebarMenu()
    {
        echo '
        <style>
            .sanzy-protect-menu a {
                background: linear-gradient(135deg, #dc2626 0%, #991b1b 100%) !important;
                color: white !important;
                border-radius: 10px !important;
                margin: 8px 12px !important;
                font-weight: bold !important;
            }
            .sanzy-protect-menu a i { color: #fca5a5 !important; }
            .sanzy-protect-menu a:hover {
                background: linear-gradient(135deg, #b91c1c 0%, #7f1d1d 100%) !important;
                transform: translateX(5px);
            }
        </style>
        <script>
            (function addMenu() {
                var nav = document.querySelector(".nav-sidebar");
                if (nav && !document.querySelector(".sanzy-protect-menu")) {
                    var li = document.createElement("li");
                    li.className = "nav-item sanzy-protect-menu";
                    li.innerHTML = \'<a href="/admin/sanzy-protect" class="nav-link"><i class="fas fa-shield-alt"></i><span>🛡️ SANZY PROTECT</span></a>\';
                    nav.appendChild(li);
                } else if (!nav) setTimeout(addMenu, 500);
            })();
        </script>';
    }
    
    private function deny(string $message)
    {
        if (request()->expectsJson()) {
            return response()->json(['errors' => [[
                'code' => 'SanoProtect', 'status' => '403',
                'detail' => '🛡️ ' . $message
            ]]], 403);
        }
        return response()->view('errors.403', ['message' => '🛡️ ' . $message], 403);
    }
    
    private function isServerOwner(int $adminId, string $serverUuid): bool
    {
        try {
            $server = \Pterodactyl\Models\Server::where('uuid', $serverUuid)->first();
            return $server && $server->owner_id === $adminId;
        } catch (\Exception $e) {
            return false;
        }
    }
    
    private function isWhitelisted(int $adminId): bool
    {
        if (!file_exists(self::WHITELIST_FILE)) return false;
        $data = json_decode(file_get_contents(self::WHITELIST_FILE), true);
        return in_array($adminId, $data['admins'] ?? []);
    }
}

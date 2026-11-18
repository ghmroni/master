<?php

use App\Models\Menu;

// if (!function_exists('getMenus')) {
//     function getMenus()
//     {
//         return Menu::where('status', 'ENABLE')
//             ->whereNull('submenu')
//             ->with('subMenus')
//             ->with('Akses')
//             ->with('subMenus.Akses')
//             ->with('subMenus.Parent')
//             ->with('subMenus.Parent.Akses')
//             ->orderBy('sort', 'ASC')
//             ->get()
//             ->toArray();

//         // return MenuSidebar::with('subMenus')->whereNull('submenu')->get();

//     }
// }

if (!function_exists('getMenus')) {
    function getMenus($user)
    {
        ## Ambil semua permission yang dimiliki user (dari Spatie Permission)
        $userPermissions = $user->getAllPermissions()->pluck('id');

        ## Ambil menu utama
        $menus = Menu::where('status', 'ENABLE')
            ->where(function ($query) use ($userPermissions) {
                // Filter: menu yang punya permission sesuai user
                $query->whereHas('permissions', function ($q) use ($userPermissions) {
                    $q->whereIn('permissions.id', $userPermissions);
                })
                    // ATAU menu tanpa permission (public)
                    ->orWhereDoesntHave('permissions');
            })

            ## RELASI KE SUBMENU
            ->with(['subMenus' => function ($q) use ($userPermissions) {
                // Ambil submenu
                $q->where('status', 'ENABLE')
                    ->where(function ($query) use ($userPermissions) {
                        // Filter: submenu yang punya permission sesuai user
                        $query->whereHas('permissions', function ($p) use ($userPermissions) {
                            $p->whereIn('permissions.id', $userPermissions);
                        })
                            // ATAU submenu tanpa permission (public)
                            ->orWhereDoesntHave('permissions');
                    })
                    // Urutkan submenu berdasarkan kolom sort
                    ->orderBy('sort');
            }])
            // Urutkan menu utama berdasarkan kolom sort
            ->orderBy('sort')
            ->get();

        // Return hasil berupa koleksi menu + submenu yang sesuai permission user
        return $menus;
    }
}

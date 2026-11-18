<?php

namespace App\Http\Controllers\Menu;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use App\Models\SubMenu;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Spatie\Permission\Models\Permission;
use Yajra\DataTables\DataTables;

class MenuController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware(PermissionMiddleware::using('lihat-menu'), only: ['index']),
            new Middleware(PermissionMiddleware::using('edit-menu|tambah-menu'), only: ['menu_akses', 'store_menu', 'store_submenu']),
        ];
    }

    public function index(Request $request)
    {
        if ($request->ajax()) {
            $menus = Menu::with([
                'permissions',
                'submenus.permissions'
            ])
                ->orderBy('sort')
                ->get()
                ->toArray();

            return DataTables::of($menus)

                ->addColumn('nama', function ($row) {
                    return strtoupper($row['name']) .
                        ($row['status'] == "ENABLE"
                            ? '<span class="badge text-success">Aktif</span>'
                            : '<span class="badge text-danger">Tidak Aktif</span>'
                        );
                })

                ->addColumn('permissions', function ($row) {
                    if (!empty($row['permissions']) && count($row['permissions']) > 0) {
                        $badges = '';
                        foreach ($row['permissions'] as $perm) {
                            $badges .= '<span class="badge bg-label-dark me-1">' . $perm['name'] . '</span>';
                        }
                        return $badges;
                    }
                })

                ->addColumn('action', function ($row) {

                    $payload = [
                        'id'  => $row['id'],
                        'exp' => time() + 3600  // optional, expired 1 jam
                    ];

                    $token = JWT::encode($payload, config('jwt.secret'), 'HS256');


                    $button = '<div class="btn-group">';
                    if ($row['url'] == null && count($row['permissions']) == 0) {
                        $button .= '<button class="btn btn-sm btn-dark addSub" data-token=' . $token . '><i class="fas fa-plus me-1"></i>Submenu</button>';
                    }
                    $button .= '<button class="btn btn-sm btn-primary editMenu" data-token=' . $token . '><i class="fas fa-edit me-1"></i>Edit</button>';
                    $button .= '</div>';

                    return $button;
                })

                ->rawColumns([
                    'permissions' => 'permissions',
                    'nama'        => 'nama',
                    'action'      => 'action'
                ])
                ->addIndexColumn()
                ->toJson();
        }

        return view('menus.menu-list', [
            'judul' => 'Kelola Menu'
        ]);
    }

    public function menu_akses(Request $request)
    {
        if ($request->ajax()) {
            $data = Permission::orderBy('module')->get()->groupBy('module');
            return response()->json($data, 200);
        }
    }

    public function edit_menu(Request $request)
    {
        if ($request->ajax()) {
            $token   = $request->query('token');
            $decoded = JWT::decode($token, new Key(config('jwt.secret'), 'HS256'));
            $id      = $decoded->id;

            $data = Menu::where('id', $id)
                ->with('permissions')
                ->first()
                ->toArray();

            return response()->json($data, 200);
        }
    }

    public function store_menu(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'namamenu' => [
                    'required',
                    Rule::unique('menus', 'name')
                        ->ignore($request->idmenu)
                        ->where(function ($query) {
                            $query->whereNull('deleted_at');
                        }),
                ],
                'sort'  => 'required|numeric',
            ],
            [
                'namamenu.required'      => 'Nama Menu masih kosong.',
                'namamenu.unique'        => 'Nama Menu sudah tersedia.',
                // 'permissionsid.required' => 'Permissions masih kosong.',

            ]
        );

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->messages()->first(),
            ], 405);
        } else {

            $url = $request->input('url');

            // kalau url tidak null, cek apakah diawali dengan "/"
            if (!empty($url) && substr($url, 0, 1) !== '/') {
                $url = '/' . $url;
            }

            $menu = Menu::UpdateOrCreate(
                [
                    'id' => $request->idmenu,
                ],
                [
                    'name'   => trim(strtoupper($request->namamenu)),
                    'url'    => $url,
                    'icon'   => $request->icon,
                    'sort'   => $request->sort,
                    'status' => $request->status,
                ]
            );
            $menu->permissions()->sync($request->permissionsid);

            return response()->json([
                'message' => 'Data Berhasil Disimpan.'
            ], 200);
        }
    }

    public function list_submenus(Request $request)
    {
        if ($request->ajax()) {
            $submenus = SubMenu::with(['permissions', 'menu'])
                ->orderBy('menu_id', 'asc')
                ->orderBy('name', 'asc')
                ->get()
                ->toArray();


            return DataTables::of($submenus)

                ->addColumn('sub', function ($row) {
                    return ($row['menu']['name'] ? '<span class="text-light badge">' . $row['menu']['name'] . '</span>' : '') . '<br><span class="fw-bold ms-2">' . strtoupper($row['name']) . '</span>' .
                        ($row['status'] == "ENABLE"
                            ? '<span class="badge text-success">Aktif</span>'
                            : '<span class="badge text-danger">Tidak Aktif</span>'
                        );
                })

                ->addColumn('permissions', function ($row) {
                    if (!empty($row['permissions']) && count($row['permissions']) > 0) {
                        $badges = '';
                        foreach ($row['permissions'] as $perm) {
                            $badges .= '<span class="badge bg-label-dark me-1">' . $perm['name'] . '</span>';
                        }
                        return $badges;
                    }
                })

                ->addColumn('action', function ($row) {

                    $payload = [
                        'id'  => $row['id'],
                        'exp' => time() + 3600  // optional, expired 1 jam
                    ];

                    $token = JWT::encode($payload, config('jwt.secret'), 'HS256');

                    return '<div class="btn-group">
                                <button class="btn btn-sm btn-warning editSub" data-token=' . $token . '><i class="fas fa-edit me-1"></i>Edit</button>
                            </div>';
                })

                ->rawColumns([
                    'sub'         => 'sub',
                    'permissions' => 'permissions',
                    'action'      => 'action'
                ])
                ->addIndexColumn()
                ->toJson();
        }

        return view('menus.menu-list', [
            'judul' => 'Kelola Menu'
        ]);
    }

    public function menu_submenus(Request $request)
    {
        if ($request->ajax()) {
            $token   = $request->query('token');
            $decoded = JWT::decode($token, new Key(config('jwt.secret'), 'HS256'));
            $id      = $decoded->id;

            $data = Menu::where('id', $id)->pluck('id', 'name');
            return response()->json($data, 200);
        }
    }

    // public function getWebRoutes(Request $request)
    // {
    //     if ($request->ajax()) {
    //         $routes = collect(Route::getRoutes())->filter(function ($route) {
    //             return in_array('web', $route->middleware());
    //         });

    //         return response()->json($routes->map(function ($route) {
    //             return [
    //                 'method' => implode('|', $route->methods()),
    //                 'uri'    => $route->uri(),
    //                 'name'   => $route->getName(),
    //                 'action' => $route->getActionName(),
    //             ];
    //         }));
    //     }
    // }

    public function edit_submenu(Request $request)
    {
        if ($request->ajax()) {
            $token   = $request->query('token');
            $decoded = JWT::decode($token, new Key(config('jwt.secret'), 'HS256'));
            $id      = $decoded->id;

            $sub = SubMenu::with(['menu', 'permissions'])
                ->where('id', $id)
                ->first();

            $menus = Menu::whereNull('deleted_at')
                ->whereNull('url')
                ->where('status', 'ENABLE')
                ->whereDoesntHave('permissions')
                ->orderBy('name')
                ->pluck('id', 'name');

            return response()->json([
                'sub' => $sub,
                'menus' => $menus,
            ], 200);
        }
    }

    public function store_submenu(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'menuid'        => 'required',
                'namasub'       => 'required',
                'url'           => 'required',
                'sort'          => 'required',
                'idpermissions' => 'required',
                'status'        => 'required',
            ],
            [
                'menuid.required'        => 'Menu utama masih kosong.',
                'namasub.required'       => 'Nama Submenu masih kosong.',
                'url.required'           => 'URL masih kosong.',
                'sort.required'          => 'Nomor urut masih kosong.',
                'idpermissions.required' => 'Permissions atau akses masih kosong.',
                'status.required'        => 'Permissions atau akses masih kosong.',
            ]
        );

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->messages()->first(),
            ], 405);
        } else {
            $url = $request->input('url');
            // kalau url tidak null, cek apakah diawali dengan "/"
            if (!empty($url) && substr($url, 0, 1) !== '/') {
                $url = '/' . $url;
            }

            // cek apakah sudah ada submenu dengan nama sama di menu_id yang sama
            $exists = SubMenu::where('menu_id', $request->menuid)
                ->whereRaw('UPPER(name) = ?', [strtoupper(trim($request->namasub))])
                ->where('status', 'ENABLE')
                ->where('id', '!=', $request->idsub) // abaikan record yang sedang diupdate
                ->exists();

            if ($exists) {
                return response()->json([
                    'errors' => 'Submenu dengan nama tersebut sudah ada di menu ini.'
                ], 422);
            }

            $submenu = SubMenu::UpdateOrCreate(
                [
                    'id' => $request->idsub,
                ],
                [
                    'menu_id'   => $request->menuid,
                    'name'   => trim(strtoupper($request->namasub)),
                    'url'    => $url,
                    'sort'   => $request->sort,
                    'status' => $request->status,
                ]
            );
            $submenu->permissions()->sync($request->idpermissions);

            return response()->json([
                'message' => 'Data Berhasil Disimpan.'
            ], 200);
        }
    }
}

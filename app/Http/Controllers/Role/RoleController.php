<?php

namespace App\Http\Controllers\Role;

use App\Http\Controllers\Controller;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware(PermissionMiddleware::using('lihat-role'), only: ['index']),
            new Middleware(PermissionMiddleware::using(['edit-role', 'tambah-role']), only: ['store_roles']),
        ];
    }

    public function index(Request $request)
    {
        ## Get Data Roles
        ## Ambil roles dengan jumlah user dan daftar user sekaligus
        $roles = Role::when(!Auth::user()->hasRole('SUPER ADMINISTRATOR'), function ($query) {
            $query->where('name', '!=', 'SUPER ADMINISTRATOR');
        })
            ->withCount('users')   ## otomatis bikin field users_count
            ->with(['users' => function ($q) {
                $q->select('users.id', 'users.name', 'users.image');
            }])
            ->orderBy('name', 'asc');

        ## tambahkan filter search di sini
        if ($request->filled('search')) {
            $search = $request->search;
            $roles->where('name', 'like', "%{$search}%");
        }

        ## baru lakukan paginate
        $roles = $roles->paginate(11);

        ## Siapkan array counts dan users
        $counts = [];
        $users  = [];

        foreach ($roles as $role) {
            $counts[$role->name] = $role->users_count;
            $users[$role->name] = $role->users->pluck('image', 'name');
            $payload = [
                'id'  => $role->id,
                'exp' => time() + 3600
            ];
            $role->token = JWT::encode($payload, config('jwt.secret'), 'HS256');
        }

        ## Jika AJAX, kirim partial view
        if ($request->ajax()) {
            return view('roles._list', compact('roles', 'counts', 'users'))->render();
        }

        return view('roles.roles', [
            'judul' => 'Roles / Level',
        ], compact('roles', 'counts', 'users'));
    }

    public function kelola_roles(Request $request, $token = null)
    {
        ## Periksa Token
        $roleId  = null;
        if ($token) {
            $token   = $request->token;
            $decoded = JWT::decode($token, new Key(config('jwt.secret'), 'HS256'));
            $roleId       = $decoded->id;
        }

        ## Permission
        $permissions = Permission::orderBy('module')
            ->when(!Auth::user()->hasRole('SUPER ADMINISTRATOR'), function ($query) {
                # Sembunyikan modul tertentu
                $query->whereNotIn('module', ['Quota', 'Permissions', 'Menus'])
                    # Atau sembunyikan permission spesifik
                    ->whereNotIn('name', ['tambah-role']);
            })
            ->get()
            ->groupBy('module');

        ## Ambil Selected dari blade yang baru di ceklis(kalau ada)
        $selectedPermissions = $request->input('selectedPermissions', []);

        ## APABILA MODE EDIT DAN SEBELUMNYA MEMPUNYAI PERMISSION
        $rolePermissions = ($roleId
            ? Role::find($roleId)->permissions()->pluck('id')->toArray()
            : []
        );

        $rolename = ($roleId
            ? Role::find($roleId)
            : ''
        );
        ## End EDIT

        ## Jika AJAX, kirim partial view
        // if ($request->ajax()) {
        //     return view('roles._listperm', compact('permissions', 'selectedPermissions', 'token', 'rolePermissions', 'roleId', 'rolename'))->render();
        // }

        return view('roles.kelola-roles', [
            'judul' => 'Roles / Level',
        ], compact('permissions', 'selectedPermissions', 'token', 'rolePermissions', 'roleId', 'rolename'));
    }

    public function edit_roles(Request $request)
    {
        if ($request->ajax()) {
            if ($request->id) {
                $role = Role::where('roles.id', $request->id)
                    ->with('permissions')
                    ->first();
                $role->permissions;
            } else {
                $role = '';
            }
            $permissions = Permission::orderBy('module')->get()->groupBy('module');

            return response()->json([
                'role'        => $role,
                'permissions' => $permissions,
            ], 200);
        }
    }

    public function store_roles(Request $request)
    {

        $isSuperAdmin = Auth::user()->hasRole('SUPER ADMINISTRATOR');

        // 1. Aturan validasi dasar
        $rules = [];
        $messages = [];

        // Jika SA, atau jika ini adalah data baru (create), maka nama wajib divalidasi
        if ($isSuperAdmin || empty($request->idrole)) {
            $rules['name'] = [
                'required',
                Rule::unique('roles', 'name')->ignore($request->idrole),
            ];
            $messages = [
                'name.required' => 'Nama Level masih kosong.',
                'name.unique'   => 'Nama Level sudah tersedia.',
            ];
        }

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->messages()->first(),
            ], 405);
        } else {
            $idrol = $request->idrole;

            // 2. Data yang akan disimpan
            $dataToUpdate = [];

            // Hanya update nama jika dia SA atau sedang membuat role baru
            if ($isSuperAdmin || empty($idrol)) {
                $dataToUpdate['name'] = trim(strtoupper($request->name));
            }

            $role = Role::updateOrCreate(
                ['id' => $idrol],
                $dataToUpdate
            );

            $role->permissions()->sync($request->permissions ?? []);

            return response()->json([
                'message' => 'Data Berhasil Disimpan.'
            ], 200);
        }
    }
}

<?php

namespace App\Http\Controllers\Permission;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Yajra\DataTables\DataTables;

class PermissionController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware(PermissionMiddleware::using('lihat-permission'), only: ['index']),
            new Middleware(PermissionMiddleware::using('edit-permission|tambah-permission'), only: ['store_permissions']),
        ];
    }

    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = Permission::orderBy('created_at', 'ASC')
                ->orderBy('module', 'ASC')
                ->get()
                ->toArray();

            return DataTables::of($data)
                ->addColumn('action', function ($row) {
                    return '<div class="btn-group">
                        <button class="btn btn-sm btn-primary Edit" data-id=' . $row['id'] . '><i class="far fa-edit me-1"></i>Edit</button>
                    </div>';
                })

                ->rawColumns(['action' => 'action'])
                ->addIndexColumn()
                ->toJson();
        }

        return view('permissions.list', [
            'judul' => 'Permissions / Hak Akses'
        ]);
    }

    public function permissions_edit(Request $request)
    {
        if ($request->ajax()) {
            $query = Permission::findOrFail($request->id);
            return response()->json($query, 200);
        }
    }

    public function permissions_groups(Request $request)
    {
        if ($request->ajax()) {
            $query = Permission::orderBy('module', 'ASC')->pluck('id', 'module');
            return response()->json($query, 200);
        }
    }

    public function store_permissions(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'name' => [
                    'required',
                    Rule::unique('permissions', 'name')->ignore($request->id),
                ],
                'module' => 'required',
            ],
            [
                'name.required'   => 'Nama Hak Akses masih kosong.',
                'name.unique'     => 'Nama Hak Akses sudah digunakan.',
                'module.required' => 'Nama Modul masih kosong.',
            ]
        );

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->messages()->first(),
            ], 405);
        } else {
            if (!$request->id) {
                $permission = Permission::create([
                    'name'   => trim(strtolower($request->name)),
                    'module' => trim(ucfirst($request->module)),
                ]);
            } else {
                // Update Permission
                $permission = Permission::findById($request->id);
                $permission->update([
                    'name'   => trim(strtolower($request->name)),
                    'module' => trim(ucfirst($request->module)),
                ]);
                // Cek Role yang mempunyai nama permission sebelumnya
                $permissionName = $request->oldname;
                $roles = Role::whereHas('permissions', function ($query) use ($permissionName) {
                    $query->where('name', $permissionName);
                })->get();

                // Update Role dengan permission baru
                $newPermissions = trim(strtolower($request->name));
                foreach ($roles as $role) {
                    $role->hasPermissionTo($newPermissions);
                }

                // Cek User Direct Permission
                $users = User::whereHas('permissions', function ($query) use ($permissionName) {
                    $query->where('name', $permissionName);
                })->get();

                foreach ($users as $user) {
                    $user->hasPermissionTo($newPermissions);
                }
            }

            return response()->json([
                'message' => 'Data Berhasil Disimpan.'
            ], 200);
        }
    }
}

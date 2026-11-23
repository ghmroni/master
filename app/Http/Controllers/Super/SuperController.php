<?php

namespace App\Http\Controllers\Super;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Email\EmailController;
use App\Models\User;
use App\Services\QuotaService;
use Firebase\JWT\JWT;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class SuperController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware(PermissionMiddleware::using('lihat-user'), only: ['users']),
            new Middleware(PermissionMiddleware::using('edit-user|tambah-user'), only: ['store_user', 'store_direct']),
        ];
    }

    public function users(Request $request)
    {
        if ($request->ajax()) {
            $data = User::select('*')
                ->with('roles', function ($q) {
                    $q->select('name');
                })
                ->orderBy('name', 'ASC')
                ->get();

            return DataTables::of($data)
                ->addColumn('status', function ($row) {
                    if ($row->status == 'ENABLE') {
                        $status = '<span class="badge rounded-pill bg-label-success">AKTIF</span>';
                    } else {
                        $status = '<span class="badge rounded-pill bg-label-danger">TIDAK AKTIF</span>';
                    }
                    return $status;
                })

                ->addColumn('level', function ($row) {
                    if ($row->roles) {
                        $level = $row->roles()->first()->name;
                    } else {
                        $level = '';
                    }
                    return $level;
                })

                ->addColumn('action', function ($row) {
                    $payload = [
                        'id'  => $row->id,
                        'exp' => time() + 3600  // optional, expired 1 jam
                    ];

                    $token = JWT::encode($payload, config('jwt.secret'), 'HS256');

                    $button = '<div class="btn-group">
                        <button class="btn btn-sm btn-warning Edit" data-token=' . $token . '><i class="far fa-edit me-1"></i>Edit</button>
                        <button class="btn btn-sm btn-dark AdditionalAkses" data-token=' . $token . '><i class="fas fa-key me-1"></i>Akses Tambahan</button>
                    </div>';

                    return $button;
                })

                ->rawColumns(['level' => 'level', 'status' => 'status', 'action' => 'action'])
                ->addIndexColumn()
                ->toJson();
        }

        return view('admin.users-list', [
            'judul' => 'Daftar Pengguna'
        ]);
    }

    public function store_user(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'name'   => 'required',
                'email'          => [
                    'required',
                    'string',
                    'email:rfc,dns',
                    Rule::unique('users', 'email')
                        ->ignore($request->id)
                        ->where(function ($query) {
                            $query->whereNull('deleted_at')
                                ->where('status', 'ENABLE');
                        }),
                ],
                'roles'  => 'required',
                'status' => 'required',
            ],
            [
                'name.required'   => 'Nama Lengkap masih kosong',
                'email.required'  => 'Alamat Email  masih kosong',
                'email.unique'    => 'Oops! Alamat Email sudah tersedia.',
                'roles.required'  => 'Level  masih kosong',
                'status.required' => 'Status  masih kosong',
            ]
        );

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->messages()->first(),
            ], 405);
        } else {
            $selectedRole = Role::findOrFail($request->roles);
            $existingUser = $request->id ? User::findOrFail($request->id) : null;


            # Jika yang login BUKAN SUPER ADMINISTRATOR, jalankan pengamanan & cek kuota
            if (!Auth::user()->hasRole('SUPER ADMINISTRATOR')) {

                # Cegah manipulasi form (Inspect Element) untuk memilih role SUPER ADMINISTRATOR
                if ($selectedRole->name === 'SUPER ADMINISTRATOR') {
                    abort(403, 'Anda tidak diizinkan menetapkan role Super Administrator.');
                }

                # Cegah Admin mengedit data milik SUPER ADMINISTRATOR
                if ($existingUser && $existingUser->hasRole('SUPER ADMINISTRATOR')) {
                    return response()->json(['message' => 'Anda tidak diizinkan mengubah data Super Administrator.'], 403);
                }

                # Cek apakah ini Create baru ATAU Update yang mengubah role user
                $isRoleChanged = !$existingUser || !$existingUser->hasRole($selectedRole->name);
                if ($isRoleChanged) {
                    #Cek Kuota jika role yang dipilih adalah ADMINISTRATOR
                    if ($selectedRole->name === 'ADMINISTRATOR') {
                        $totalAdmin = User::role('ADMINISTRATOR')->count();

                        if (QuotaService::isExceeded('max_admins', $totalAdmin)) {
                            return response()->json(['message' => 'Kuota akun Administrator sudah penuh.'], 422);
                        }
                    } else {
                        #Cek Kuota untuk role selain SA & Admin (dihitung sebagai Pengguna)
                        $totalUser = User::whereHas('roles', function ($query) {
                            $query->whereNotIn('name', ['SUPER ADMINISTRATOR', 'ADMINISTRATOR']);
                        })->count();

                        if (QuotaService::isExceeded('max_users', $totalUser)) {
                            return response()->json(['message' => 'Kuota akun Pengguna sudah penuh.'], 422);
                        }
                    }
                }
            }

            $userData = [
                'name'     => trim(strtoupper($request->name)),
                'email'    => trim(strtolower($request->email)),
                'status'   => $request->status,
            ];

            $password = Str::random(10);

            if (empty($request->id)) {
                $userData['password'] = Hash::make($password);
            }

            $user = User::updateOrCreate(
                ['id' => $request->id],
                $userData
            );

            if ($user->wasRecentlyCreated) {
                ## Create
                $user->assignRole($selectedRole);

                ## Kirim email hanya saat user baru dibuat
                $mailController = new EmailController;
                $kirim = $mailController->send_new_account([
                    'name'     => trim(strtoupper($request->name)),
                    'email'    => trim(strtolower($request->email)),
                    'password' => $password,
                    'level'    => trim($selectedRole->name),
                    'url'      => URL::to('/login'),
                ]);
            } else {
                # Update
                $user->syncRoles([$selectedRole]);
            }

            return response()->json([
                'message' => 'Data Berhasil Disimpan'
            ], 200);
        }
    }

    public function forceLogout(Request $request)
    {
        $request->validate([
            'user_id' => 'required|integer',
        ]);

        // Hapus sesi pengguna berdasarkan user_id
        DB::table('sessions')->where('user_id', $request->user_id)->delete();

        return response()->json([
            'message' => 'Pengguna Berhasil Dikeluarkan.'
        ]);
    }
}

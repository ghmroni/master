<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Email\EmailController;
use App\Models\Bank;
use App\Models\Kontrakinduk;
use App\Models\KontrakIndukSettingHarga;
use App\Models\MasterGaji;
use App\Models\MasterLembur;
use App\Models\Project;
use App\Models\Ptko;
use App\Models\StatusKontrak;
use App\Models\StatusPerkawinan;
use App\Models\User;
use Carbon\Carbon;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Role;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Spatie\Permission\Models\Permission;

class AdminController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware(PermissionMiddleware::using('lihat-statuskawin'), only: ['statuskawin']),
            new Middleware(PermissionMiddleware::using('tambah-statuskawin|edit-statuskawin'), only: ['store_statuskawin', 'edit_statuskawin']),
            new Middleware(PermissionMiddleware::using('hapus-statuskawin'), only: ['destroy_statuskawin']),
            new Middleware(PermissionMiddleware::using('lihat-bank'), only: ['list_bank']),
            new Middleware(PermissionMiddleware::using('tambah-bank|edit-bank'), only: ['store_bank', 'edit_bank']),
            new Middleware(PermissionMiddleware::using('hapus-bank'), only: ['destroy_bank']),
            new Middleware(PermissionMiddleware::using('lihat-ptko'), only: ['ptko']),
            new Middleware(PermissionMiddleware::using(['tambah-ptko', 'edit-ptko']), only: ['store_ptko']),
            new Middleware(PermissionMiddleware::using('batal-ptko'), only: ['proses_hrapps']),
            new Middleware(PermissionMiddleware::using('setting-penggajian'), only: ['store_settinggaji', 'settinggaji_hapus']),
            new Middleware(PermissionMiddleware::using('setting-lembur'), only: ['store_settinglembur', 'settinglembur_hapus']),
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
                ->get()
                ->toArray();

            return DataTables::of($data)
                ->addColumn('status', function ($row) {
                    if ($row['status'] == 'ENABLE') {
                        $status = '<span class="badge rounded-pill bg-label-success">AKTIF</span>';
                    } else {
                        $status = '<span class="badge rounded-pill bg-label-danger">TIDAK AKTIF</span>';
                    }
                    return $status;
                })

                ->addColumn('level', function ($row) {
                    if ($row['roles']) {
                        $level = $row['roles'][0]['name'];
                    } else {
                        $level = '';
                    }
                    return $level;
                })

                ->addColumn('action', function ($row) {
                    $payload = [
                        'id'  => $row['id'],
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

    public function get_roles(Request $request)
    {
        $data = Role::when(!Auth::user()->hasRole('SUPER ADMINISTRATOR'), function ($query) {
            $query->where('name', '!=', 'SUPER ADMINISTRATOR');
        })
            ->orderBy('name', 'asc')
            ->pluck('id', 'name');
        return response()->json($data, 200);
    }

    public function edit_user(Request $request)
    {
        try {
            $token   = $request->query('token');
            $decoded = JWT::decode($token, new Key(config('jwt.secret'), 'HS256'));
            $id      = $decoded->id;

            $user = User::with('roles')->find($id);
            return response()->json([
                'id'     => $user->id,
                'nama'   => $user->name,
                'email'  => $user->email,
                'status' => $user->status,
                'role'   => $user->roles->pluck('id')->first(),   // hanya satu role
            ], 200);
        } catch (ExpiredException $e) {
            return back()->withErrors(['token_expired' => 'Oops! Token sudah kadaluarsa.']);
        }
    }

    ## DIRECT PERMISSIONS
    public function direct_permission(Request $request, $token)
    {
        try {
            $token   = $request->token;
            $decoded = JWT::decode($token, new Key(config('jwt.secret'), 'HS256'));
            $id      = $decoded->id;

            $user = User::select('id', 'name')
                ->where('id', $id)->first();

            ## Ambil dari Model_has_perm
            $directPermissions = $user->getDirectPermissions()->pluck('id')->toArray();
            ## Ambil Semua Perm
            $rolePermissions = $user->getAllPermissions()->pluck('id')->toArray();
            ## Group Perm
            $permissions = Permission::orderBy('module')->get()->groupBy('module');
            // dd($rolePermissions);

            return view('admin.user-permissions', [
                'judul' => 'Akses Tambahan Pengguna',
            ], compact('user', 'permissions', 'rolePermissions', 'directPermissions'));
        } catch (ExpiredException $e) {
            return back()->withErrors(['token_expired' => 'Oops! Token sudah kadaluarsa.']);
        }
    }

    public function store_direct(Request $request)
    {
        $selectedPermissions = $request->input('permissions', []);

        $user = User::find($request->iduser);

        $permissions = Permission::whereIn('id', $selectedPermissions)->get();
        $user->syncPermissions($permissions);

        return response()->json([
            'message' => 'Data Berhasil Disimpan'
        ], 200);
    }

    public function get_kontrak()
    {
        $query = StatusKontrak::select('*')->pluck('id', 'name');
        return response()->json($query);
    }

    public function statuskawin(Request $request)
    {
        if ($request->ajax()) {
            $data = StatusPerkawinan::select('*')
                ->whereNull('deleted_at')
                ->get()
                ->toArray();

            return DataTables::of($data)
                ->addColumn('nilai', function ($row) {
                    return 'Rp. ' . number_format($row['nilai_ptkp']) . ',-';
                })

                ->addColumn('action', function ($row) {
                    $payload = [
                        'id' => $row['id'],
                        'exp' => time() + 3600 // optional, expired 1 jam
                    ];

                    $token = JWT::encode($payload, config('jwt.secret'), 'HS256');

                    $button = '<div class="btn-group">
                        <button class="btn btn-sm btn-warning Edit" data-id=' . $token . '><i class="far fa-edit me-1"></i>Edit</button>
                        <button class="btn btn-sm btn-danger Hapus" data-id=' . $token . '><i class="fas fa-trash me-1"></i>Hapus</button>
                    </div>';

                    return $button;
                })

                ->rawColumns(['nilai' => 'nilai', 'action' => 'action'])
                ->addIndexColumn()
                ->toJson();
        }

        return view('admin.status-perkawinan', [
            'judul' => 'Status Perkawinan'
        ]);
    }

    public function edit_statuskawin(Request $request)
    {
        if ($request->ajax()) {
            $token = $request->query('token');
            $decoded = JWT::decode($token, new Key(config('jwt.secret'), 'HS256'));
            $id = $decoded->id;
            $query = StatusPerkawinan::where('id', $id)->firstOrFail();
            return response()->json($query, 200);
        }
    }

    public function destroy_statuskawin(Request $request)
    {
        if ($request->ajax()) {
            $token = $request->query('token');
            $decoded = JWT::decode($token, new Key(config('jwt.secret'), 'HS256'));
            $id = $decoded->id;
            $data = StatusPerkawinan::find($id);
            $data->delete();
            return response()->json([
                'message' => 'Data Status Perkawinan Berhasil Dihapus'
            ], 200);
        }
    }

    public function store_statuskawin(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'kode' => [
                    'required',
                    Rule::unique('status_perkawinan', 'kode_status')
                        ->ignore($request->idkawin)
                        ->where(function ($query) {
                            $query->whereNull('deleted_at');
                        }),
                ],
                'keterangan' => 'required',
                'nilai'      => 'required',
                'kategori'   => 'required',
            ],
            [
                'kode.required'       => 'Kode Status Kawin masih kosong',
                'kode.unique'         => 'Kode Status Kawin sudah tersedia',
                'keterangan.required' => 'Keterangan Status Kawin  masih kosong',
                'nilai.required'      => 'Nilai PTKP Status Kawin  masih kosong',
                'kategori.required'   => 'Kategori PTKP Status Kawin  masih kosong',
            ]
        );

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->messages()->first(),
            ], 405);
        } else {
            $idkawin = $request->input('idkawin');

            $data = array(
                'kode_status'   => trim(strtoupper($request->input('kode'))),
                'nama_status'   => trim(strtoupper($request->input('keterangan'))),
                'nilai_ptkp'    => str_replace(',', '', $request->input('nilai')),
                'kategori_ptkp' => trim(strtoupper($request->input('kategori'))),
            );

            if ($idkawin != null) {
                $model = StatusPerkawinan::findOrFail($idkawin);
                $model->update($data);
            } else {
                $save = StatusPerkawinan::create($data);
            }

            return response()->json([
                'message' => 'Data Status Perkawinan Berhasil Disimpan.'
            ], 200);
        }
    }

    public function list_bank(Request $request)
    {
        if ($request->ajax()) {
            $data = Bank::select('*')
                ->whereNull('deleted_at')
                ->orderBy('name', 'ASC')
                ->get()
                ->toArray();

            return DataTables::of($data)
                ->addColumn('logo', function ($row) {
                    $default = '/assets/img/avatars/no-image.png';
                    $url = $default;

                    if (!empty($row['logobank'])) {
                        $path = public_path('storage/hris/bank/' . $row['logobank']);
                        if (File::exists($path)) {
                            $url = '/storage/hris/bank/' . $row['logobank'];
                        }
                    }
                    return '<img src="' . $url . '" width="70" class="img-thumbnail" alt="Logo bank"/>';
                })


                ->addColumn('action', function ($row) {
                    $payload = [
                        'id' => $row['id'],
                        'exp' => time() + 3600 // optional, expired 1 jam
                    ];

                    $token = JWT::encode($payload, config('jwt.secret'), 'HS256');

                    $button = '<div class="btn-group">
                        <button class="btn btn-sm btn-warning Edit" data-id=' . $token . '><i class="far fa-edit me-1"></i>Edit</button>
                        <button class="btn btn-sm btn-danger Hapus" data-id=' . $token . '><i class="fas fa-trash me-1"></i>Hapus</button>
                    </div>';

                    return $button;
                })

                ->rawColumns(['logo' => 'logo', 'action' => 'action'])
                ->addIndexColumn()
                ->toJson();
        }

        return view('admin.bank', [
            'judul' => 'Daftar Bank'
        ]);
    }

    public function edit_bank(Request $request)
    {
        if ($request->ajax()) {
            $token = $request->query('token');
            $decoded = JWT::decode($token, new Key(config('jwt.secret'), 'HS256'));
            $id = $decoded->id;

            $query = Bank::where('id', $id)->firstOrFail();

            $default = '/assets/img/avatars/no-image.png';
            $url = $default;

            if (!empty($query['logobank'])) {
                $path = public_path('storage/hris/bank/' . $query['logobank']);
                if (File::exists($path)) {
                    $url = '/storage/hris/bank/' . $query['logobank'];
                }
            }

            $query['url'] = $url;

            return response()->json($query, 200);
        }
    }

    public function store_bank(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'logobank' => $request->hasFile('logobank')
                    ? 'required|image|mimes:jpeg,jpg,png|dimensions:min_width=400,min_height=200'
                    :  'nullable',
                'nama' => [
                    'required',
                    Rule::unique('bank', 'name')
                        ->ignore($request->bankid)
                        ->where(function ($query) {
                            $query->whereNull('deleted_at');
                        }),
                ],
            ],
            [
                'logobank.required'   => 'Gambar / Logo Bank masih kosong.',
                'logobank.dimensions' => 'Oops! Ukuran Gambar / Logo Bank minimal atau 400 x 200',
                'nama.required'       => 'Nama Bank masih kosong.',
                'nama.unique'         => 'Nama Bank sudah tersedia.',
            ]
        );

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->messages()->first()
            ], 401);
        } else {

            $idbank = $request->bankid;
            $data = array(
                'name'     => trim(strtoupper($request->input('nama'))),
                'codebank' => trim($request->input('kode')),
                // 'logobank'    => trim($request->input('longdesc')),
            );

            // Menentukan Nama Gambar
            if ($request->hasFile('logobank')) {

                $extension = $request->file('logobank')->getClientOriginalExtension();
                $filename  = trim(strtolower(str_replace(' ', '_', $request->input('nama')))) . '.' . $extension;

                Storage::disk('public')->putFileAs('hris/bank/', $request->file('logobank'), $filename);
                $data['logobank'] = $filename;
            } else {
                $data['logobank'] = $request->logoid;
            }

            if ($idbank) {
                $model = Bank::where('id', $idbank);
                $model->update($data);
            } else {
                $simpan = Bank::create($data);
            }

            return response()->json([
                'message' => 'Data Bank Berhasil Disimpan.'
            ]);
        }
    }

    public function destroy_bank(Request $request)
    {
        $token = $request->query('token');
        $decoded = JWT::decode($token, new Key(config('jwt.secret'), 'HS256'));
        $id = $decoded->id;

        $data = Bank::find($id);
        $data->delete();
        return response()->json([
            'message' => 'Data Bank Berhasil Dihapus'
        ], 200);
    }

    public function ptko(Request $request)
    {
        if ($request->ajax()) {
            $query = Ptko::whereNull('deleted_at')
                ->with(['project', 'project.parent.parent.parent.parent.parent'])
                ->orderByRaw('approval_at IS NULL DESC') // NULL duluan
                ->orderByRaw('hrapps IS NULL DESC') // NULL duluan
                ->orderBy('approval_at', 'asc')
                ->orderBy('hrapps', 'asc')
                ->orderBy('id', 'desc')
                ->get()
                ->toArray();

            return DataTables::of($query)
                ->addColumn('lokasi', function ($row) {
                    $lokasi = $row['project']; // Ambil Relasinya yaitu project
                    $parents = [];
                    while ($lokasi) {
                        $parents[] = $lokasi['name'];
                        $lokasi = $lokasi['parent']; // Ambil parent selanjutnya
                    }

                    $color = ['text-success', 'text-warning', 'text-primary', 'text-info', 'text-secondary', 'text-dark'];
                    $lokasi = array_map(
                        fn($item, $class)
                        => "<span class='$class fw-bold d-flex'>$item</span>",
                        array_reverse($parents), // Urutkan agar dari parent → child
                        $color
                    );

                    return implode(' ', $lokasi);
                })

                ->addColumn('tanggal', function ($row) {
                    return Carbon::parse($row['tanggal_ptko'])->translatedFormat('d F Y');
                })

                ->addColumn('status', function ($row) {
                    return ($row['status'] == 'BARU'
                        ? '<span class="badge rounded-pill bg-primary">' . $row['status'] . '</span>'
                        : '<span class="badge rounded-pill bg-warning">' . $row['status'] . '</span>'
                    );
                })

                ->addColumn('catatan', function ($row) {
                    return ($row['approval_at'] == null
                        ? '<span class="badge rounded-pill bg-label-secondary text-uppercase"><i class="fas fa-cart-plus me-1"></i>Belum Digunakan</span>'
                        : '<span class="badge rounded-pill bg-label-success text-uppercase"><i class="fas fa-check-circle me-1"></i>Sudah Digunakan</span><br>
                            <small class="text-light mt-1" style="font-size:9px">' . Carbon::parse($row['approval_at'])->translatedFormat('d F Y H:i:s') . '</small>'
                    );
                })

                ->addColumn('keterangan', function ($row) {
                    return ($row['hrapps'] == null
                        ? '<span class="badge rounded-pill bg-label-danger text-uppercase"><i class="fas fa-pencil-alt me-1"></i>Belum Input</span>'
                        : '<span class="badge rounded-pill bg-label-success text-uppercase"><i class="fas fa-check-circle me-1"></i>Sudah Input</span> <br>
                            <small class="text-light mt-1" style="font-size:9px">' . Carbon::parse($row['hrapps'])->translatedFormat('d F Y H:i:s') . '</small>'
                    );
                })

                ->addColumn('action', function ($row) {

                    $payload = [
                        'id'  => $row['id'],
                        'exp' => time() + 3600  // optional, expired 1 jam
                    ];

                    $token = JWT::encode($payload, config('jwt.secret'), 'HS256');

                    $button = '<div class="btn-group">';

                    if ($row['approval_at'] == null) {
                        if (auth()->user()->can('edit-ptko')) {
                            $button .= '<button class="btn btn-sm btn-warning Edit" data-token=' . $token . '><i class="far fa-edit me-1"></i>Edit</button>';
                        } else {
                            $button .= '<span class="badge rounded-pill bg-label-info"><i class=" fas fa-lock me-1"></i>Tidak ada izin</span>';
                        }
                    } else {
                        if (auth()->user()->can('batal-ptko')) {
                            if ($row['hrapps'] == null) {
                                $button .= '
                                    <button class="btn btn-sm btn-danger Batal" data-id=' . $row['id'] . '><i class="fas fa-retweet me-1"></i>Batal</button>
                                    <button class="btn btn-sm btn-primary Input" data-token=' . $token . ' data-nomor=' . $row['nomor'] . '><i class="fas fa-paper-plane me-1"></i>HRApps</button>
                                ';
                            } else {
                                $button .= '<button class="btn btn-sm btn-danger Cancel" data-token=' . $token . ' data-nomor=' . $row['nomor'] . '><i class="fas fa-retweet me-1"></i>Batalkan HRApps</button>';
                            }
                        }

                        $button .= '<button class="btn btn-sm btn-info Detail" data-token=' . $token . '><i class="fas fa-info-circle me-1"></i>Detail</button>';
                    }

                    $button .= '</div>';

                    return $button;
                })

                ->rawColumns([
                    'lokasi' => 'lokasi',
                    'tanggal' => 'tanggal',
                    'status' => 'status',
                    'catatan' => 'catatan',
                    'keterangan' => 'keterangan',
                    'action' => 'action'
                ])
                ->addIndexColumn()
                ->toJson();
        }

        return view('admin.ptko-list', [
            'judul' => 'Data PTKO'
        ]);
    }

    public function store_ptko(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'project' => 'required',
                'nomor'   => [
                    'required',
                    'regex:/^\S+$/',
                    Rule::unique('ptko', 'nomor')
                        ->ignore($request->idptko)
                        ->where(function ($query) {
                            $query->whereNull('deleted_at');
                        }),
                ],
                'tanggal' => 'required|date',
                'status'  => 'required',
                'posisi'  => 'required',

            ],
            [
                'project.required' => 'Keperluan Project masih kosong.',
                'nomor.required'   => 'Nomor masih kosong.',
                'nomor.regex'      => 'Nomor tidak boleh menggunakan spasi.',
                'nomor.unique'     => 'Nomor sudah pernah diinput.',
                'tanggal.required' => 'Tanggal masih kosong.',
                'status.required'  => 'Status masih kosong.',
                'posisi.required'  => 'Posisi masih kosong.',
            ]
        );

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->messages()->first(),
            ], 405);
        } else {
            $idptko = $request->idptko;

            Ptko::updateOrCreate(
                [
                    'id' => $idptko
                ], // kondisi pencarian
                [
                    'project_id'   => $request->project,
                    'nomor'        => trim(strtoupper($request->nomor)),
                    'posisi'       => $request->posisi,
                    'status'       => $request->status,
                    'tanggal_ptko' => $request->tanggal,
                    'user_id'      => auth()->id(),
                ]
            );

            return response()->json([
                'message' => 'Data PTKO Berhasil Disimpan'
            ], 200);
        }
    }

    public function edit_ptko(Request $request)
    {
        try {
            $token   = $request->query('token');
            $decoded = JWT::decode($token, new Key(config('jwt.secret'), 'HS256'));
            $id      = $decoded->id;

            $query = Ptko::where('id', $id)
                ->whereNull('deleted_at')
                ->firstOrFail();

            if ($request->ajax()) {
                return response()->json($query, 200);
            }
        } catch (ExpiredException $e) {
            return back()->withErrors(['token_expired' => 'Oops! Token sudah kadaluarsa.']);
        }
    }

    public function detail_ptko(Request $request)
    {
        try {
            $token   = $request->query('token');
            $decoded = JWT::decode($token, new Key(config('jwt.secret'), 'HS256'));
            $id      = $decoded->id;

            $query = Ptko::whereNull('deleted_at')
                ->where('id', $id)
                ->with('karyawan', function ($q) {
                    $q->select('id', 'ptko_id', 'nip', 'nama', 'project_detail', 'tanggal_keluar');
                })
                ->firstOrFail();

            if ($request->ajax()) {
                return response()->json($query, 200);
            }
        } catch (ExpiredException $e) {
            return back()->withErrors(['token_expired' => 'Oops! Token sudah kadaluarsa.']);
        }
    }

    public function batal_ptko(Request $request)
    {
        $model = Ptko::find($request->id);
        $model->update([
            'approval_at' => null,
            'hrapps'      => null,
            'keterangan'  => $request->alasan,
        ]);

        return response()->json([
            'message' => 'Penggunaan Nomor PTKO Berhasil Dibatalkan'
        ], 200);
    }

    public function proses_hrapps(Request $request)
    {
        $token   = $request->token;
        $decoded = JWT::decode($token, new Key(config('jwt.secret'), 'HS256'));
        $id      = $decoded->id;
        $hrapps  = strtoupper($request->hrapps);
        $model   = Ptko::find($id);
        $model->update([
            'hrapps' => ($hrapps == "SUDAH" ? now() : null),
        ]);

        return response()->json([
            'message' => ($hrapps == "SUDAH"
                ? 'HRApps Berhasil Diproses.'
                : 'HRApps Berhasil Dibatalkan'
            )
        ], 200);
    }

    public function setting_gaji(Request $request)
    {
        if ($request->ajax()) {
            $query = MasterGaji::whereNull('deleted_at')
                ->orderBy('updated_at', 'DESC')
                ->get()
                ->toArray();

            return DataTables::of($query)
                ->addColumn('lokasi', function ($row) {
                    $color = ['text-success', 'text-warning', 'text-primary', 'text-info', 'text-secondary', 'text-dark'];
                    $lokasi = array_map(
                        fn($item, $class) => "<span class='$class fw-bold d-flex'>$item</span>",
                        $row['lokasi_project'],
                        array_slice($color, 0, count($row['lokasi_project']))
                    );
                    return implode(' ', $lokasi);
                })
                ->addColumn('gapok', function ($row) {
                    return 'Rp ' . number_format($row['gapok']) . ',-';
                })
                ->addColumn('nilai', function ($row) {
                    return 'Rp ' . number_format($row['nilai_pothadir']) . ',-';
                })
                ->addColumn('cutoff', function ($row) {
                    $cutoff = explode(',', $row['cutoff']);
                    return str_pad($cutoff[0], 2, '0', STR_PAD_LEFT) . ' - ' . str_pad($cutoff[1], 2, '0', STR_PAD_LEFT) .
                        '<br><span class="badge bg-label-' . ($cutoff[2] == 'S' ? 'primary' : 'warning') . '">' . ($cutoff[2] == 'S' ? 'BULAN SAMA' : 'BULAN SEBELUM') . '</span>';
                })
                ->addColumn('type', function ($row) {
                    return $row['type_pothadir'] .
                        '<br><span class="badge bg-dark">' . ($row['type_pothadir'] == 'FLAT' ? '' : $row['perhitungan_pothadir']) . '</span>';
                })
                ->addColumn('action', function ($row) {
                    $payload = [
                        'id'  => $row['id'],
                        'exp' => time() + 3600  // optional, expired 1 jam
                    ];
                    $token = JWT::encode($payload, config('jwt.secret'), 'HS256');

                    if (auth()->user()->can('setting-penggajian')) {

                        return '<div class="btn-group">
                            <button class="btn btn-warning btn-xs Edit" data-token ="' . $token . '" ><i class="ti ti-pencil me-1"></i>Edit</button>
                            <button class="btn btn-danger btn-xs Hapus" data-token ="' . $token . '" ><i class="ti ti-trash me-1"></i>Hapus</button>
                        </div>';
                    } else {
                        return '<span class="text-muted">
                        ' . Carbon::parse($row['updated_at'])->translatedFormat('l, d F Y') . '
                        </span>';
                    }
                })

                ->rawColumns([
                    'lokasi' => 'lokasi',
                    'cutoff' => 'cutoff',
                    'gapok'  => 'gapok',
                    'type'   => 'type',
                    'nilai'  => 'nilai',
                    'action' => 'action'
                ])
                ->addIndexColumn()
                ->toJson();
        }

        return view('admin.setting-gaji', [
            'judul' => 'Setting Gaji'
        ]);
    }

    public function tambah_settinggaji(Request $request)
    {
        return view('admin.setttinggaji-tambah', [
            'judul' => 'Tambah Setting Gaji'
        ]);
    }

    public function liss_all_kontrakinduk(Request $request)
    {
        $query = Kontrakinduk::withTrashed()
            ->orderByRaw('deleted_at IS NOT NULL ASC')
            ->orderBy('created_at', 'DESC') // 3. (Opsional) Urutkan data aktif berdasarkan yang terbaru
            ->get();
        return response()->json($query);
    }

    public function liss_setting_kontrakinduk(Request $request)
    {
        $query = KontrakIndukSettingHarga::where('kontrakinduk_id', $request->idkon)
            ->orderByRaw('deleted_at IS NOT NULL ASC')
            ->orderBy('created_at', 'DESC') // 3. (Opsional) Urutkan data aktif berdasarkan yang terbaru
            ->get()
            ->toArray();
        return response()->json($query);
    }

    public function get_data_setting(Request $request)
    {
        $query = KontrakIndukSettingHarga::where('id', $request->idsetting)
            ->with(['biaya' => function ($q) {
                $q->whereIn('jenis_biaya', ['UPAH POKOK', 'GAJI POKOK', 'GAPOK']);
            }])
            ->orderByRaw('deleted_at IS NOT NULL ASC')
            ->orderBy('created_at', 'DESC') // 3. (Opsional) Urutkan data aktif berdasarkan yang terbaru
            ->get()
            ->toArray();
        return response()->json($query);
    }

    public function settinggaji_lokasi(Request $request)
    {
        $user = Auth::user();
        $roles = $user->roles->pluck('id')->toArray();
        if ($request->ajax()) {
            ## Cek Roles
            $query = Project::where('status', 'ENABLE')
                ->with('children.children.children.children.children')
                ->whereNull('parent_id');

            if (in_array(7, $roles)) {
                ## HR PUSAT
                $query->where('id', 1);
            } else {
                $query->where('id', '!=', 1);
            }

            $result = $query->get(['name AS text', 'project.*']);

            return response()->json($result, 200);
        }
    }

    public function store_settinggaji(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'dtproject'   => 'required',
                'project' => [
                    'required',
                    Rule::unique('master_gaji', 'project_id')
                        ->ignore($request->idsetting)
                        ->where(function ($query) {
                            $query->whereNull('deleted_at');
                        }),
                ],
                'gapok'          => 'required',
                'makan'          => 'required',
                'transport'      => 'required',
                'dari'           => 'required',
                'sampai'         => 'required',
                'bulan'          => 'required',
                'harikerja'      => 'required',
                'natura'         => 'required',
                'typepotongan'   => 'required',
                'perhitungan'    => 'required',
                'nilaipotongan'  => 'required',
            ],
            [
                'dtproject.required'     => 'Lokasi Project masih kosong.',
                'project.unique'         => 'Lokasi Project sudah pernah diinput.',
                'gapok.required'         => 'Gaji pokok masih kosong.',
                'makan.required'         => 'Uang makan masih kosong.',
                'transport.required'     => 'Uang transport masih kosong.',
                'dari.required'          => 'Cut off dari masih kosong.',
                'sampai.required'        => 'Cut off sampai masih kosong.',
                'bulan.required'         => 'Cut off bulan masih kosong.',
                'harikerja.required'     => 'Hari kerja masih kosong.',
                'natura.required'        => 'Natura masih kosong.',
                'typepotongan.required'  => 'Type potongan kehadiran masih kosong.',
                'perhitungan.required'   => 'Perhitungan potongan kehadiran masih kosong.',
                'nilaipotongan.required' => 'Nilai potongan kehadiran masih kosong.',
            ]
        );

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->messages()->first(),
            ], 405);
        } else {

            if ($request->typepotongan == "STANDAR") {
                $perhitungan = $request->perhitungan;
            } else if ($request->typepotongan == "FLAT") {
                $perhitungan = str_replace('.', '', $request->perhitungan);
            } else {
                $tagifyJson = $request->perhitungan; // String JSON dari Tagify
                $tags = json_decode($tagifyJson, true);

                $perhitungan = collect($tags)->pluck('value')->implode(' ');
            }

            // Daftar kolom sesuai checkbox
            $columns = ['jkk', 'jkm', 'jp', 'jht', 'jkn'];

            $dataBpjs = [];
            foreach ($columns as $col) {
                // Ambil dari request, kalau tidak ada default ke 0
                $dataBpjs[$col] = $request->input($col, 0);
            }

            $data = [
                'kontrakinduk_id'      => $request->kontrakinduk_id,
                'project_id'           => $request->project,
                'project_detail'       => $request->dtproject,
                'gapok'                => str_replace('.', '', $request->gapok),
                'natura'               => str_replace('.', '', $request->natura),
                'uang_makan'           => str_replace('.', '', $request->makan),
                'uang_transport'       => str_replace('.', '', $request->transport),
                'cutoff'               => implode(',', array(
                    $request->dari,
                    $request->sampai,
                    $request->bulan,
                )),
                'type_pothadir'        => str_replace('.', '', $request->typepotongan),
                'perhitungan_pothadir' => $perhitungan,
                'nilai_pothadir'       => str_replace('.', '', $request->nilaipotongan),
                'hari_kerja'           => $request->harikerja,
            ];

            $datas = array_merge($data, $dataBpjs);

            MasterGaji::updateOrCreate(
                [
                    'id' => $request->idsetting,
                ],
                $datas
            );

            return response()->json([
                'message' => 'Setting Gaji Berhasil Disimpan.'
            ], 200);
        }
    }

    public function settinggaji_detail(Request $request)
    {
        try {
            $token   = $request->query('token');
            $decoded = JWT::decode($token, new Key(config('jwt.secret'), 'HS256'));
            $id      = $decoded->id;

            $details   = MasterGaji::where('id', $id)
                ->with([
                    'kontrakinduk',
                    'kontrakinduk.settingHarga',
                    'kontrakinduk.settingHarga.biaya',
                ])
                ->firstOrFail();
            // $dtproject = explode(',', $details->project_detail);
            $cutoff    = explode(',', $details->cutoff);
            $dtlokasi  = $details->toArray();
            $matchedSettingHarga = $details->kontrakinduk && $details->kontrakinduk->settingHarga
                ? $details->kontrakinduk->settingHarga->firstWhere('project_id', $details->project_id)
                : null;

            // Ambil ID-nya jika ketemu, jika tidak ada set default ke null atau kosong
            $idsettingharga = $matchedSettingHarga ? $matchedSettingHarga->id : null;

            if ($request->ajax()) {
                return response()->json([
                    'kontrakinduk_id' => $details->kontrakinduk_id,
                    'id_settingharga' => $idsettingharga,
                    'project'         => $details->project_id,
                    'dtproject'       => $details->project_detail,
                    'dtlokasi'        => $dtlokasi['lokasi_project'],
                    'typepotongan'    => $details->type_pothadir,
                    'perhitungan'     => $details->perhitungan_pothadir,
                    'nilai'           => $details->nilai_pothadir,
                ], 200);
            }
        } catch (ExpiredException $e) {
            return back()->withErrors(['token_expired' => 'Oops! Token sudah kadaluarsa.']);
        }

        return view('admin.settinggaji-detail', [
            'judul' => 'Detail Setting Gaji'
        ], compact('details', 'cutoff'));
    }

    public function settinggaji_hapus(Request $request)
    {
        try {
            $token   = $request->query('token');
            $decoded = JWT::decode($token, new Key(config('jwt.secret'), 'HS256'));
            $id      = $decoded->id;

            $hapus = MasterGaji::find($id);
            $hapus->delete();

            return response()->json([
                'message' => 'Setting Gaji Berhasil Dihapus.'
            ], 200);
        } catch (ExpiredException $e) {
            return back()->withErrors(['token_expired' => 'Oops! Token sudah kadaluarsa.']);
        }
    }

    public function setting_lembur(Request $request)
    {
        if ($request->ajax()) {
            $query = MasterLembur::whereNull('deleted_at')
                ->orderBy('updated_at', 'DESC')
                ->get()
                ->toArray();
            return DataTables::of($query)
                ->addColumn('lokasi', function ($row) {
                    $color = ['text-success', 'text-warning', 'text-primary', 'text-info', 'text-secondary', 'text-dark'];
                    $lokasi = array_map(
                        fn($item, $class) => "<span class='$class fw-bold d-flex'>$item</span>",
                        $row['lokasi_project'],
                        array_slice($color, 0, count($row['lokasi_project']))
                    );
                    return implode(' ', $lokasi);
                })
                ->addColumn('cutoff', function ($row) {
                    $cutoff = explode(',', $row['cutoff']);
                    return str_pad($cutoff[0], 2, '0', STR_PAD_LEFT) . ' - ' . str_pad($cutoff[1], 2, '0', STR_PAD_LEFT) .
                        '<br><span class="badge bg-label-' . ($cutoff[2] == 'S' ? 'primary' : 'warning') . '">' . ($cutoff[2] == 'S' ? 'BULAN SAMA' : 'BULAN SEBELUM') . '</span>';
                })
                ->addColumn('type', function ($row) {
                    return $row['type_lembur'] .
                        '<br>
                        ' . ($row['type_lembur'] == 'NORMATIVE'
                            ? '<small class="text-muted">Sesuai ketentuan Disnaker</small>'
                            : '<small class="text-muted">Biasa : ' . number_format($row['nilai_biasa']) . ' /' . ucfirst(strtolower($row['nilai_type'])) . '</small><br>
                            <small class="text-muted">Libur : ' . number_format($row['nilai_libur']) . ' /' . ucfirst(strtolower($row['nilai_type'])) . '</small>
                            ') . '
                    ';
                })
                ->addColumn('action', function ($row) {
                    $payload = [
                        'id'  => $row['id'],
                        'exp' => time() + 3600  // optional, expired 1 jam
                    ];
                    $token = JWT::encode($payload, config('jwt.secret'), 'HS256');

                    if (auth()->user()->can('setting-lembur')) {

                        return '<div class="btn-group">
                            <button class="btn btn-warning btn-xs Edit" data-token ="' . $token . '" ><i class="ti ti-pencil me-1"></i>Edit</button>
                            <button class="btn btn-danger btn-xs Hapus" data-token ="' . $token . '" ><i class="ti ti-trash me-1"></i>Hapus</button>
                        </div>';
                    } else {
                        return '<span class="text-muted">
                        ' . Carbon::parse($row['updated_at'])->translatedFormat('l, d F Y') . '
                        </span>';
                    }
                })

                ->rawColumns([
                    'lokasi' => 'lokasi',
                    'cutoff' => 'cutoff',
                    'type'   => 'type',
                    'action' => 'action'
                ])
                ->addIndexColumn()
                ->toJson();
        }
        return view('admin.setting-lembur', [
            'judul' => 'Setting Lembur'
        ]);
    }

    public function settinglembur_tambah(Request $request)
    {
        return view('admin.settinglembur-tambah', [
            'judul' => 'Tambah Setting Lembur'
        ]);
    }

    public function store_settinglembur(Request $request)
    {
        $request->merge([
            'um'         => str_replace('.', '', $request->um),
            'ut'         => str_replace('.', '', $request->ut),
            'uml'        => str_replace('.', '', $request->uml),
            'utl'        => str_replace('.', '', $request->utl),
            'nilaibiasa' => str_replace('.', '', $request->nilaibiasa),
            'nilailibur' => str_replace('.', '', $request->nilailibur),
        ]);
        $validator = Validator::make(
            $request->all(),
            [
                'project'   => 'required',
                'dtproject' => [
                    'required',
                    Rule::unique('master_lembur', 'project_detail')
                        ->ignore($request->idsetting)
                        ->where(function ($query) {
                            $query->whereNull('deleted_at');
                        }),
                ],
                'um'         => 'required|numeric',
                'ut'         => 'required|numeric',
                'uml'        => 'required|numeric',
                'utl'        => 'required|numeric',
                'dari'       => 'required|numeric',
                'sampai'     => 'required|numeric',
                'bulan'      => 'required',
                'harikerja'  => 'required|numeric',
                'typelembur' => 'required|in:FLAT,NORMATIVE',
                'nilaibiasa' => [
                    Rule::requiredIf(fn() => $request->typelembur == "FLAT"),
                    'numeric'
                ],
                'nilailibur'      => [
                    Rule::requiredIf(fn() => $request->typelembur == "FLAT"),
                    'numeric'
                ],
                'typenilai'      => [
                    Rule::requiredIf(fn() => $request->typelembur == "FLAT")
                ],
            ],
            [
                'project.required'    => 'Lokasi Project masih kosong.',
                'dtproject.unique'    => 'Lokasi Project sudah pernah diinput.',
                'um.required'         => 'Uang makan masih kosong.',
                'ut.required'         => 'Uang transport masih kosong.',
                'uml.required'        => 'Uang makan Libur masih kosong.',
                'utl.required'        => 'Uang transport Libur masih kosong.',
                'dari.required'       => 'Cut off dari masih kosong.',
                'sampai.required'     => 'Cut off sampai masih kosong.',
                'bulan.required'      => 'Cut off bulan masih kosong.',
                'harikerja.required'  => 'Hari kerja masih kosong.',
                'typelembur.required' => 'Type Lembur masih kosong.',
                'nilaibiasa.required' => 'Nilai Lembur masih kosong.',
                'nilailibur.required' => 'Nilai Lembur Hari Libur masih kosong.',
            ]
        );

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->messages()->first(),
            ], 405);
        } else {

            $normativeFields = [
                'b1',
                'nb1',
                'b2',
                'nb2',
                'l2',
                'nl2',
                'nln2',
                'l3',
                'nl3',
                'nln3',
                'l4',
                'nl4',
                'nln4'
            ];

            $flatFields = ['nilaibiasa', 'nilailibur'];

            if ($request->typelembur == 'FLAT') {
                foreach ($normativeFields as $field) {
                    $request->merge([$field => 0]); // Merge yaitu Timpa nilai yang dikirim dari form
                }
                foreach ($flatFields as $field) {
                    $request->merge([$field => $request->input($field) ?? 0]);
                }
            } else if ($request->typelembur == 'NORMATIVE') {

                foreach ($flatFields as $field) {
                    $request->merge([$field => 0]);
                }
                foreach ($normativeFields as $field) {
                    $request->merge([$field => $request->input($field) ?? 0]);
                }
            } else {
                foreach ($flatFields as $field) {
                    $request->merge([$field => 0]);
                }
                foreach ($normativeFields as $field) {
                    $request->merge([$field => 0]);
                }
            }

            MasterLembur::updateOrcreate(
                [
                    'id' => $request->idsetting
                ],
                [
                    'project_detail' => $request->dtproject,
                    'cutoff'         => implode(',', array(
                        $request->dari,
                        $request->sampai,
                        $request->bulan,
                    )),
                    'hari_kerja'  => $request->harikerja,
                    'um'          => $request->um,
                    'uml'         => $request->uml,
                    'ut'          => $request->ut,
                    'utl'         => $request->utl,
                    'type_lembur' => $request->typelembur,
                    'nilai_biasa' => $request->nilaibiasa,
                    'nilai_libur' => $request->nilailibur,
                    'nilai_type'  => $request->typenilai,
                    'b1'          => $request->b1,
                    'b2'          => $request->b2,
                    'nb1'         => $request->nb1,
                    'nb2'         => $request->nb2,
                    'l2'          => $request->l2,
                    'l3'          => $request->l3,
                    'l4'          => $request->l4,
                    'nl2'         => $request->nl2,
                    'nl3'         => $request->nl3,
                    'nl4'         => $request->nl4,
                    'nln2'        => $request->nln2,
                    'nln3'        => $request->nln3,
                    'nln4'        => $request->nln4,
                ]
            );

            return response()->json([
                'message' => 'Setting Lembur Berhasil Disimpan.'
            ], 200);
        }
    }

    public function settinglembur_detail(Request $request)
    {
        try {
            $token   = $request->query('token');
            $decoded = JWT::decode($token, new Key(config('jwt.secret'), 'HS256'));
            $id      = $decoded->id;

            $details   = MasterLembur::where('id', $id)->firstOrFail();
            $dtproject = explode(',', $details->project_detail);
            $cutoff    = explode(',', $details->cutoff);
            $dtlokasi  = $details->toArray();

            if ($request->ajax()) {
                return response()->json([
                    'project'      => last($dtproject),
                    'dtproject'    => $details->project_detail,
                    'dtlokasi'     => $dtlokasi['lokasi_project'],
                    'typelembur' => $details->type_lembur,
                    'typenilai' => $details->nilai_type,
                ], 200);
            }
        } catch (ExpiredException $e) {
            return back()->withErrors(['token_expired' => 'Oops! Token sudah kadaluarsa.']);
        }

        return view('admin.settinglembur-detail', [
            'judul' => 'Detail Setting Lembur'
        ], compact('details', 'cutoff'));
    }

    public function settinglembur_hapus(Request $request)
    {
        try {
            $token   = $request->query('token');
            $decoded = JWT::decode($token, new Key(config('jwt.secret'), 'HS256'));
            $id      = $decoded->id;

            $hapus = MasterLembur::find($id);
            $hapus->delete();

            return response()->json([
                'message' => 'Setting Lembur Berhasil Dihapus.'
            ], 200);
        } catch (ExpiredException $e) {
            return back()->withErrors(['token_expired' => 'Oops! Token sudah kadaluarsa.']);
        }
    }
}

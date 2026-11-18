<?php

namespace App\Http\Controllers\Pkwt;

use App\Http\Controllers\Controller;
use App\Models\Karyawan;
use App\Models\Kontrak;
use Carbon\Carbon;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Yajra\DataTables\DataTables;

class PkwtController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware(PermissionMiddleware::using('cetak-pkwt'), only: ['index']),
        ];
    }

    public function index(Request $request)
    {
        $user = Auth::user();
        $roles = $user->roles->pluck('id')->toArray();

        if ($request->ajax()) {
            $query = Karyawan::select('id', 'nip', 'nama', 'project', 'project_detail', 'jabatan')
                ->whereNull('tanggal_keluar')
                ->whereNull('deleted_at')

                ->when(auth()->user()->can('cetak-pkwt'), function ($q) use ($roles) {
                    if (in_array(7, $roles)) {
                        ## HR PUSAT
                        $q->whereIn('project_detail', [1]);
                    } else if (in_array(6, $roles)) {
                        ## PL
                        $q->whereHas('aksesProject', function ($sub) {
                            $sub->where('user_id', auth()->id());
                        });
                    } else {
                        $q->whereNotIn('project_detail', [1]);
                    }
                })

                ->with(
                    [
                        'jabatan:id,name',
                        'kontrakAktif:id,pkwt_id,karyawan_id,statuskontrak_id,nomor_kontrak',
                        'kontrakAktif.statuskontrak:id,name,inisial',
                    ]
                )

                ->whereHas('kontrakAktif', function ($q) {
                    $q->whereNull('deleted_at')
                        ->where('status_kontrak', 'AKTIF');
                })

                ## Urutkan berdasarkan belum pernah cetak
                // ->orderBy(
                //     Kontrak::select('pkwt_id')
                //         ->whereColumn('karyawan_id', 'karyawan.id')
                //         ->whereNull('deleted_at')
                //         ->where('status_kontrak', 'AKTIF')
                //         ->limit(1),
                //     'ASC'
                // )

                ->orderBy('id', 'DESC')
                ->get()
                ->toArray();

            return DataTables::of($query)

                ->addColumn('lokasi', function ($row) {
                    $color = ['text-success', 'text-warning', 'text-primary', 'text-info', 'text-secondary', 'text-dark'];
                    $lokasi = array_map(
                        fn($item, $class) => "<span class='$class fw-bold d-flex'>$item</span>",
                        $row['lokasi_penempatan'],
                        array_slice($color, 0, count($row['lokasi_penempatan']))
                    );
                    return implode(' ', $lokasi);
                })

                ->addColumn('action', function ($row) {
                    $payload = [
                        'id'  => $row['id'],
                        'exp' => time() + 3600  // optional, expired 1 jam
                    ];

                    $token = JWT::encode($payload, config('jwt.secret'), 'HS256');

                    $button = '<div class="btn-group">
                            <button class="btn btn-sm btn-facebook Detail" data-token=' . $token . ' data-nip="' . $row['nip'] . '" data-nama="' . $row['nama'] . '"><i class="fas fa-info-circle me-1"></i>Detail</button>
                        </div>';

                    return $button;
                })

                ->rawColumns([
                    'lokasi' => 'lokasi',
                    'action' => 'action'
                ])
                ->addIndexColumn()
                ->toJson();
        }

        return view('pkwt.list-pkwt', [
            'judul' => 'PKWT'
        ]);
    }

    public function detail_pkwt(Request $request)
    {
        $token   = $request->query('token');
        $decoded = JWT::decode($token, new Key(config('jwt.secret'), 'HS256'));
        $id      = $decoded->id;

        $query = Kontrak::where('karyawan_id', $id)
            ->with(
                [
                    'statuskontrak:id,name,inisial',
                ]
            )
            ->orderBy('id', 'DESC')
            ->get()
            ->toArray();

        return DataTables::of($query)

            ->addColumn('status', function ($row) {
                return '<span class="badge bg-label-' . ($row['status_kontrak'] == "AKTIF" ? 'success' : 'danger') . '">' . $row['statuskontrak']['name'] . '</span>';
            })

            ->addColumn('tanggal', function ($row) {
                return '
                    <span class="badge rounded-pill bg-label-success">' . Carbon::parse($row['kontrak_dari'])->translatedFormat('d F Y') . '</span><br>
                    <small class="text-light mt-1" style="font-size:9px">sampai</small><br>
                    <span class="badge rounded-pill bg-label-danger mt-1">' . Carbon::parse($row['kontrak_sampai'])->translatedFormat('d F Y') . '</span>
                ';
            })

            ->addColumn('kopsurat', function ($row) {
                $payload = [
                    'id'  => $row['id'],
                    'exp' => time() + 3600  // optional, expired 1 jam
                ];
                $token = JWT::encode($payload, config('jwt.secret'), 'HS256');

                // token untuk opsi KOP
                $payloadKop = [
                    'id'   => $row['id'],
                    'type' => 'kop',
                    'exp'  => time() + 3600
                ];
                $tokenKop = JWT::encode($payloadKop, config('jwt.secret'), 'HS256');

                // token untuk opsi TANPA KOP
                $payloadDefault = [
                    'id'   => $row['id'],
                    'type' => 'default',
                    'exp'  => time() + 3600
                ];
                $tokenDefault = JWT::encode($payloadDefault, config('jwt.secret'), 'HS256');

                return '
                    <div class="mb-2 mt-2">
                        <div class="form-check form-check-warning">
                            <label class="form-check-label d-flex gap-1">
                                <input name="kopsurat_' . $token . '" class="form-check-input" type="radio"
                                    value="' . $tokenKop . '" id="kopsurat_' . $row['id'] . '" >
                                KOP
                            </label>
                        </div>
                    </div>
                    <div class="form-check form-check-primary">
                        <label class="form-check-label d-flex gap-1 text-uppercase">
                            <input name="kopsurat_' . $token . '" class="form-check-input" type="radio"
                                value="' . $tokenDefault . '" id="kopsurat_' . $row['id'] . '" checked>
                            TANPA KOP
                        </label>
                    </div>
                ';
            })

            ->addColumn('action', function ($row) {

                $payload = [
                    'id'  => $row['id'],
                    'exp' => time() + 3600  // optional, expired 1 jam
                ];

                $token = JWT::encode($payload, config('jwt.secret'), 'HS256');
                $pkwtId = is_null($row['pkwt_id']) ? 'null' : $row['pkwt_id'];

                ## Tombol Cetak
                $button = '<div class="btn-group">
                            <button class="btn btn-sm btn-facebook Cetak" data-token=' . $token . ' ><i class="fas fa-print me-1"></i>Cetak</button>
                        </div>';

                ## Ceklis Perbaharui apabila sudah pernah cetak
                $button .= is_null($row['pkwt_id'])
                    ? ''
                    : ' <div class="form-check form-check-success mt-3">
                            <label class="form-check-label text-dark">
                                <input class="form-check-input" type="checkbox" value="update" name="update_' . $token . '" />
                                Perbaharui
                            </label>
                        </div>';

                return $button;
            })

            ->rawColumns([
                'status'   => 'status',
                'tanggal'  => 'tanggal',
                'kopsurat' => 'kopsurat',
                'action'   => 'action'
            ])
            ->addIndexColumn()
            ->toJson();
    }

    public function proses_pkwt(Request $request)
    {
        // try {
        ## token idkontrak
        $token     = $request->query('token');
        $decoded   = JWT::decode($token, new Key(config('jwt.secret'), 'HS256'));
        $idkontrak = $decoded->id;
        ## Kop surat
        $kop        = $request->query('kop');
        $decodedKop = JWT::decode($kop, new Key(config('jwt.secret'), 'HS256'));
        $kopsurat   = $decodedKop->type;
        ## Status Perbaharui
        $status = $request->query('status');
        $alasan = $request->query('alasan') ?? null;

        ## Validasi expired
        if (($decoded->exp ?? 0) < time()) {
            return back()->withErrors(['token_expired' => 'Oops! Token sudah kadaluarsa.']);
        }

        $getProject = Kontrak::find($idkontrak);
        $dtProject = explode(',', $getProject->karyawan->project_detail);
        $project    = $dtProject[0];

        # Definisikan mapping beserta data tambahan (jika ada)
        $projectPkwt = [
            14 => [
                'controller' => AutoController::class,
                'extra_data' => [] // Tidak ada data tambahan
            ],
            204 => [
                'controller' => BanjarController::class,
                'extra_data' => [] // Tidak ada data tambahan
            ],
            // 205 => [
            //     'controller' => ProjectLainController::class,
            //     'extra_data' => [
            //         'idkaryawan' => $idkaryawan // Tambah data khusus untuk project 205
            //     ]
            // ],
        ];

        # Cek apakah ID project terdaftar
        if (isset($projectPkwt[$project])) {

            $config = $projectPkwt[$project];
            # Data standar yang selalu dikirim ke semua project
            $baseData = [
                'idkontrak' => $idkontrak,
                'kopsurat'  => $kopsurat,
                'status'    => $status,
                'alasan'    => $alasan,
            ];

            # Gabungkan data standar dengan extra_data milik project tersebut
            $finalData = array_merge($baseData, $config['extra_data']);
            # Masukkan ke request
            request()->merge($finalData);
            # Panggil controllernya
            return app($config['controller'])->index(request()); // index adalah nama function atau method nya

        } else {
            return redirect()->route('pkwt.error')
                ->with('error_session', 'Pkwt untuk Project ini tidak ditemukan. Silahkan ');
        }
    }

    public function error_page(Request $request)
    {
        return view('errors.error-page');
    }
}

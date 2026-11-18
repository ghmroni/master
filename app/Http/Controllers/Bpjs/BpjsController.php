<?php

namespace App\Http\Controllers\Bpjs;

use App\Http\Controllers\Controller;
use App\Models\Bpjs;
use App\Models\Karyawan;
use App\Models\Keluarga;
use App\Models\Project;
use Carbon\Carbon;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Yajra\DataTables\DataTables;
use function Symfony\Component\Clock\now;

class BpjsController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware(PermissionMiddleware::using('lihat-bpjs'), only: ['index']),
            new Middleware(PermissionMiddleware::using('edit-bpjs'), only: ['store_bpjs', 'new_setting_bpjs', 'store_setting_bpjs', 'detail_setting_bpjs']),
            new Middleware(PermissionMiddleware::using('tambah-bpjs'), only: ['store_tambahan']),
            new Middleware(PermissionMiddleware::using('setting-bpjs'), only: ['setting_bpjs']),
        ];
    }

    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = Karyawan::select('id', 'pelamar_id', 'nip', 'project_detail', 'deleted_at', 'nama', 'statuskawin_id', 'tanggal_masuk', 'tanggal_keluar')
                ->withTrashed() // Ambil dengan yang sudah dihapus
                ->with([
                    'keluarga',
                    'statuskawin:id,kode_status,nama_status',
                    'bpjs:id,karyawan_id,nobpjs_tk,nobpjs_kes,status_kepesertaan'
                ])
                ->when(auth()->user()->can('lihat-bpjs'), function ($q) {
                    $akses = auth()->user()->getProjectAccess();
                    $q->whereIn('project', $akses);
                })
                ->orderBy('deleted_at', 'asc')
                ->orderBy('id', 'DESC')
                ->orderBy('deleted_at', 'ASC')
                ->get()
                ->toArray();

            return DataTables::of($query)
                ->addColumn('status', function ($row) {
                    return '<label class="d-flex">' . $row['nip'] . '</label>' .
                        ($row['deleted_at'] == null
                            ? '<span class="badge bg-label-success mt-1">Aktif</span>'
                            : '<span class="badge bg-label-danger mt-1">Tidak Aktif</span>');
                })

                ->addColumn('namalengkap', function ($row) {
                    return '<span class="d-flex">' . $row['nama'] . '</span>' .
                        ($row['deleted_at'] == null
                            ? '<span class="badge bg-label-success mt-1">' . Carbon::parse($row['tanggal_masuk'])->format('d F Y') . '</span>'
                            : '<span class="badge bg-label-danger mt-1">' . Carbon::parse($row['tanggal_keluar'])->format('d F Y') . '</span>');
                })

                ->addColumn('lokasi', function ($row) {
                    $color = ['text-success', 'text-warning', 'text-primary', 'text-info', 'text-secondary', 'text-dark'];
                    $lokasi = array_map(
                        fn($item, $class) => "<span class='$class fw-bold d-flex'>$item</span>",
                        $row['lokasi_penempatan'],
                        array_slice($color, 0, count($row['lokasi_penempatan']))
                    );
                    return implode(' ', $lokasi);
                })

                ->addColumn('bpjskes', function ($row) {
                    $html = '';
                    # BPJS Karyawan

                    $noKesKaryawan = $row['bpjs']['nobpjs_kes'] ?? 'BELUM ADA DATA';
                    $karyawanBpjs = $row['bpjs']['karyawan_id'] ?? null;

                    $html .= '<span class="d-flex text-muted fw-xs">Karyawan : ' . (ucwords(strtolower($row['bpjs']['status_kepesertaan'] ?? null))) . '</span> ' . ($karyawanBpjs
                        ? '<span class="d-flex badge ' . ($row['bpjs']['status_kepesertaan'] == "AKTIF" ? 'bg-success' : ($row['bpjs']['status_kepesertaan'] == null ? 'bg-dark' : 'bg-warning')) . ' ' . ($noKesKaryawan != 'BELUM ADA DATA' ? 'text-black' : '') . ' rounded-pill bg-glow mb-1">' . $noKesKaryawan . '</span>'
                        : '<span class="d-flex badge bg-dark rounded-pill bg-glow mb-1">BELUM ADA DATA</span>');
                    // $html .= 'Karyawan : ' . ($noKesKaryawan
                    //     ? '<span class="d-flex badge bg-success rounded-pill bg-glow mb-1">' . $noKesKaryawan . '</span>'
                    //     : '<span class="d-flex badge bg-dark rounded-pill bg-glow mb-1">BELUM ADA DATA</span>');

                    # BPJS Keluarga
                    $kodeStatus = $row['statuskawin']['kode_status'] ?? 'TK0';

                    // Jika bukan TK, berarti ada pasangan atau anak
                    if ($kodeStatus !== 'TK0' && !empty($row['keluarga'])) {
                        // Ambil angka setelah 'K' (K1 = 1, K2 = 2, K0 = 0)
                        $jumlahAnakMaks = (int) filter_var($kodeStatus, FILTER_SANITIZE_NUMBER_INT);
                        $counterAnak = 0;

                        foreach ($row['keluarga'] as $anggota) {

                            if ($anggota['status_bpjskes'] == 'AKTIF') {
                                $statusAnggota = 'success';
                            } else if ($anggota['status_bpjskes'] == null) {
                                $statusAnggota = 'dark';
                            } else {
                                $statusAnggota = 'warning';
                            }

                            $hubungan = strtolower($anggota['status_hubungan'] ?? '-');
                            $noBpjs = $anggota['no_bpjskes'] ?? 'BELUM ADA DATA';

                            // Tampilkan Pasangan (Istri/Suami)
                            if (str_contains($hubungan, 'istri') || str_contains($hubungan, 'suami')) {
                                $html .= '<small class="d-flex text-muted">' . ucfirst($hubungan) . ' : ' . (ucwords(strtolower($anggota['status_bpjskes'] ?? null))) . '</small><span class="d-flex  badge bg-' . $statusAnggota . ' ' . ($noBpjs != 'BELUM ADA DATA' ? 'text-black' : '') . ' rounded-pill bg-glow mb-1">' . $noBpjs . '</span>';
                            }

                            // Tampilkan Anak dengan batasan kode status
                            elseif (str_contains($hubungan, 'anak')) {
                                if ($counterAnak < $jumlahAnakMaks) {
                                    $counterAnak++;
                                    $html .= '<small class="d-flex text-muted">Anak : ' . (ucwords(strtolower($anggota['status_bpjskes'] ?? null))) . '</small><span class="d-flex badge bg-' . $statusAnggota . ' ' . ($noBpjs != 'BELUM ADA DATA' ? 'text-black' : '') . ' rounded-pill bg-glow mb-1">' . $noBpjs . '</span>';
                                }
                            }
                        }
                    }

                    # TAMBAHAN
                    if (!empty($row['keluarga'])) {
                        $statusHubungan = ['AYAH KANDUNG', 'IBU KANDUNG', 'AYAH MERTUA', 'IBU MERTUA', 'LAINNYA'];
                        foreach ($row['keluarga'] as $tambahan) {
                            # Cek apakah status_hubungan ada di dalam array DAN status_bpjskes AKTIF
                            if (in_array($tambahan['status_hubungan'], $statusHubungan) && $tambahan['status_bpjskes'] == "AKTIF") {
                                $html .= '<small class="text-muted fw-bold">TAMBAHAN :</small><small class="text-muted"> ' . (ucwords(strtolower($tambahan['status_hubungan']))) . '</small><span class="d-flex text-black badge bg-info rounded-pill bg-glow mb-1">' . $tambahan['no_bpjskes'] . '</span>';
                            }
                        }
                    }

                    return $html;
                })

                ->addColumn('bpjstk', function ($row) {
                    if (isset($row['bpjs']) && $row['bpjs']['nobpjs_tk']) {
                        return '<span class="badge rounded-pill text-black bg-success bg-glow">' . $row['bpjs']['nobpjs_tk'] . '</span>';
                    }

                    return '<span class="badge rounded-pill bg-dark bg-glow">BELUM ADA DATA</span>';
                })

                ->addColumn('action', function ($row) {
                    $payload = [
                        'id'  => $row['id'],
                        'exp' => time() + 3600  // optional, expired 1 jam
                    ];

                    $token = JWT::encode($payload, config('jwt.secret'), 'HS256');

                    $button = '<div class="btn-group">
                            <button class="btn btn-sm btn-primary Detail" data-token="' . $token . '"><i class="fas fa-info-circle me-1"></i>Detail</button>
                        ';
                    if (auth()->user()->can('tambah-bpjs') && isset($row['bpjs']) && $row['bpjs']['status_kepesertaan'] == "AKTIF") {
                        $button .= '<button class="btn btn-sm btn-info Tambahan text-dark" data-token="' . $token . '" ><i class="fas fa-user-plus me-1"></i>Tambahan</button>';
                    }

                    $button .= '</div>';
                    return $button;
                })

                ->rawColumns([
                    'status'      => 'status',
                    'namalengkap' => 'namalengkap',
                    'lokasi'      => 'lokasi',
                    'bpjskes'     => 'bpjskes',
                    'bpjstk'      => 'bpjstk',
                    'action'      => 'action'
                ])
                ->addIndexColumn()
                ->toJson();
        }

        return view('bpjs.data-bpjs', [
            'judul' => 'Data BPJS'
        ]);
    }

    public function dashboard_in(Request $request)
    {
        $user = Auth::user();
        $roles = $user->roles->pluck('id')->toArray();
        if ($request->ajax()) {
            $query = Karyawan::where('tanggal_masuk', '>=', Carbon::parse(now())->subMonths(2))
                ->whereNotIn('project_detail', [1])
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
                ->addColumn('tglmasuk', function ($row) {
                    return Carbon::parse($row['tanggal_masuk'])->format('d F Y');
                })

                ->rawColumns([
                    'lokasi'   => 'lokasi',
                    'tglmasuk' => 'tglmasuk',
                ])
                ->addIndexColumn()
                ->toJson();
        }
    }

    public function dashboard_out(Request $request)
    {
        $user = Auth::user();
        $roles = $user->roles->pluck('id')->toArray();
        if ($request->ajax()) {
            $query = Karyawan::onlyTrashed()
                ->with('recordout')
                ->whereHas('recordout', function ($q) {
                    $q->where('tanggal_keluar', '>=', Carbon::parse(now())->subMonths(2));
                })
                ->whereNotIn('project_detail', [1])
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
                ->addColumn('tglkeluar', function ($row) {
                    return Carbon::parse($row['recordout']['tanggal_keluar'])->format('d F Y');
                })
                ->addColumn('alasan', function ($row) {
                    $catatan = $row['recordout']['catatan_keluar'];
                    $shortText = Str::limit($catatan, 25, '...'); // Potong teks untuk tampilan awal

                    if ($row['recordout']['status_out'] == "MENGUNDURKAN DIRI") {
                        $color = 'primary';
                    } else if ($row['recordout']['status_out'] == "HABIS KONTRAK") {
                        $color = 'danger';
                    } else {
                        $color = 'warning';
                    }

                    return '
                        <span class="badge bg-label-' . $color . '">' . $row['recordout']['status_out'] . '</span>
                        <span class="catatan-text d-flex mt-1">
                            ' . $shortText . '
                        </span>
                        ' . (strlen($catatan) > 25 ? '<button class="btn btn-label-success btn-xs read-more mt-1" data-bs-toggle="tooltip" 
                            data-bs-placement="top" 
                            data-bs-custom-class="tooltip-dark" 
                            title="' . e($catatan) . '" 
                            style="cursor: pointer;" data-full="' . e($catatan) . '">Hover me</button>' : '') . '
                    ';
                })

                ->rawColumns([
                    'lokasi'    => 'lokasi',
                    'tglkeluar' => 'tglkeluar',
                    'alasan'    => 'alasan',
                ])
                ->addIndexColumn()
                ->toJson();
        }
    }

    public function detail_bpjs(Request $request)
    {
        $token   = $request->query('token');
        $decoded = JWT::decode($token, new Key(config('jwt.secret'), 'HS256'));
        $id      = $decoded->id;

        $details = Karyawan::select('id', 'pelamar_id', 'ktp', 'nip', 'project_detail', 'deleted_at', 'nama', 'statuskawin_id')
            ->withTrashed()
            ->where('id', $id)
            ->with([
                'keluarga',
                'statuskawin:id,kode_status,nama_status',
                'bpjs' => function ($query) {
                    $query->withTrashed()
                        ->select('id', 'karyawan_id', 'nobpjs_tk', 'nobpjs_kes', 'status_kepesertaan', 'gapok_bpjstk', 'gapok_bpjskes');
                }
            ])
            ->first();

        // Pastikan relasi keluarga sudah di-load di query $details pertama
        $tambahan = $details->keluarga->whereIn('status_hubungan', [
            'AYAH KANDUNG',
            'IBU KANDUNG',
            'AYAH MERTUA',
            'IBU MERTUA',
            'LAINNYA'
        ])->whereNotNull('status_bpjskes');

        $view = view('bpjs._detail_bpjs', compact('details', 'tambahan'))->render();

        $isDeleted = !is_null($details->deleted_at);
        $status = $details->bpjs->status_kepesertaan ?? null;

        return response()->json([
            'view'       => $view,
            'is_deleted' => $isDeleted,
            'status'     => $status
        ]);
    }

    public function store_bpjs(Request $request)
    {
        $request->merge([
            'upahtk'  => str_replace('.', '', $request->upahtk),
            'upahkes' => str_replace('.', '', $request->upahkes),
        ]);

        $validator = Validator::make(
            $request->all(),
            [
                # Validasi Karyawan
                'nobpjskes'      => 'nullable|numeric|digits:13',
                'status_bpjskar' => [
                    'nullable',
                    'string',
                    'required_with:nobpjskes', // Wajib jika nobpjskes diisi
                ],
                'upahtk' => [
                    'nullable',
                    Rule::requiredIf(!empty($request->nobpjstk)),
                    'numeric'
                ],
                'upahkes' => [
                    'nullable',
                    Rule::requiredIf(fn() => $request->status_bpjskar == "AKTIF"),
                    'numeric'
                ],
                # Validasi untuk semua no_bpjskes di dalam array keluarga
                'keluarga.*.no_bpjskes'     => 'nullable|numeric|digits:13',
                'keluarga.*.status_bpjskes' => [
                    'nullable',
                    'string',
                    'required_with:keluarga.*.no_bpjskes',
                ],
            ],
            [
                'nobpjskes.digits'                        => 'Nomor BPJS Kesehatan Karyawan harus 13 angka.',
                'nobpjskes.numeric'                       => 'Nomor BPJS Kesehatan Karyawan hanya boleh berisi angka.',
                'status_bpjskar.required_with'            => 'Status BPJS Kesehatan Karyawan masih kosong.',
                'upahtk.required'                         => 'Upah BPJSTK masih kosong.',
                'upahkes.required'                        => 'Upah BPJS Kesehatan masih kosong.',
                'keluarga.*.no_bpjskes.digits'            => 'Nomor BPJS Kesehatan Keluarga harus 13 angka.',
                'keluarga.*.no_bpjskes.numeric'           => 'Nomor BPJS Kesehatan Keluarga hanya boleh berisi angka.',
                'keluarga.*.status_bpjskes.required_with' => 'Status BPJS Kesehatan Keluarga masih kosong.',
            ]
        );

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->messages()->first(),
            ], 405);
        } else {
            DB::transaction(function () use ($request) {
                // if ($request->idkaryawan) {
                Bpjs::withTrashed()->updateOrCreate(
                    [
                        'karyawan_id' => $request->idkaryawan,
                    ],
                    [
                        'gapok_bpjstk'       => ($request->upahtk == "") ? 0 : $request->upahtk,
                        'gapok_bpjskes'      => ($request->upahkes == "") ? 0 : $request->upahkes,
                        'nobpjs_tk'          => $request->nobpjstk ? trim($request->nobpjstk) : null,
                        'nobpjs_kes'         => $request->nobpjskes ? trim($request->nobpjskes) : null,
                        'status_kepesertaan' => $request->status_bpjskar ?? null,
                        'updated_at'         => now(),
                    ]
                );
                // }

                if ($request->keluarga) {
                    foreach ($request->keluarga as $idAnggota => $data) {
                        Keluarga::where('id', $idAnggota)
                            ->update([
                                'no_bpjskes'     => $data['no_bpjskes'],
                                'status_bpjskes' => $data['status_bpjskes'],
                                'updated_at'     => now()
                            ]);
                    }
                }
            });

            return response()->json([
                'message' => 'Data BPJS Berhasil Disimpan.'
            ], 200);
        }
    }

    public function detail_tambahan(Request $request)
    {
        $token   = $request->query('token');
        $decoded = JWT::decode($token, new Key(config('jwt.secret'), 'HS256'));
        $id      = $decoded->id;

        $karyawan = Karyawan::select('id', 'pelamar_id', 'ktp', 'nip', 'project_detail', 'deleted_at', 'nama', 'statuskawin_id')
            ->where('id', $id)
            ->with([
                'bpjs:id,karyawan_id,nobpjs_tk,nobpjs_kes,status_kepesertaan'
            ])
            ->first();
        $identitasKar = $karyawan->nip . ' | ' . $karyawan->nama;
        // $dtkeluarga = $karyawan->with([
        //     'keluarga'
        // ])
        //     ->whereHas('keluarga', function ($q) {
        //         $q->whereIn('status_hubungan', ['AYAH KANDUNG', 'IBU KANDUNG', 'AYAH MERTUA', 'IBU MERTUA', 'LAINNYA']);
        //     })->first();

        // $keluarga = $dtkeluarga->keluarga ?? null;

        $view = view('bpjs._detail_tambahan', compact('karyawan'))->render();

        return response()->json([
            'html' => $view,
            'identitasKar' => $identitasKar
        ]);
    }

    public function list_tambahan(Request $request)
    {
        if ($request->ajax()) {
            $token   = $request->query('token');
            $decoded = JWT::decode($token, new Key(config('jwt.secret'), 'HS256'));
            $id      = $decoded->id;

            $idpelamar = Karyawan::find($id)->pelamar_id;

            $query = Keluarga::where('pelamar_id', $idpelamar)
                ->whereIn('status_hubungan', ['AYAH KANDUNG', 'IBU KANDUNG', 'AYAH MERTUA', 'IBU MERTUA', 'LAINNYA'])
                // ->whereNot('status_bpjskes', null)
                ->get()
                ->toArray();

            return DataTables::of($query)
                ->addColumn('status', function ($row) {
                    return '<span class="badge bg-label-' . ($row['status_bpjskes'] == "AKTIF" ? 'success' : 'danger') . '">' . $row['status_bpjskes'] . '</span>';
                })
                ->addColumn('action', function ($row) {
                    $button = '<div class="btn-group">
                                <button class="btn btn-xs btn-primary Edit"><i class="ti ti-pencil me-1"></i>Edit</button>
                            </div>
                        ';
                    return $button;
                })
                ->rawColumns([
                    'status'  => 'status',
                    'action'  => 'action'
                ])
                ->addIndexColumn()
                ->toJson();
        }
    }

    public function store_tambahan(Request $request)
    {
        $idpelamar = Karyawan::find($request->idkaryawan)->pelamar_id;

        $validator = Validator::make(
            $request->all(),
            [
                // 'hubungan'    => 'required',
                'hubungan' => [
                    'required',
                    ($request->hubungan !== 'LAINNYA')
                        ? Rule::unique('keluarga', 'status_hubungan')
                        ->where('pelamar_id', $idpelamar)
                        // ->whereNotNull('status_bpjskes')
                        ->ignore($request->idkeluarga ?? 0)
                        : '', // Jika 'LAINNYA', tidak ada rule unik yang diterapkan
                ],
                'namalengkap' => 'required',
                'noktp'       => 'required',
                'nobpjskes'   => 'required',
                'statusbpjs'  => 'required|string',
            ],
            [
                'hubungan.required'    => 'Hubungan Keluarga masih kosong',
                'hubungan.unique'      => 'Hubungan Keluarga sudah pernah diinput',
                'namalengkap.required' => 'Nama lengkap masih kosong',
                'noktp.required'       => 'Nomor KTP masih kosong',
                'nobpjskes.required'   => 'Nomor BPJS masih kosong',
                'statusbpjs.required'  => 'Status BPJS masih kosong',
            ]
        );

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->messages()->first(),
            ], 405);
        } else {
            DB::transaction(function () use ($request, $idpelamar) {
                if ($request->idkaryawan) {

                    Keluarga::updateOrcreate(
                        [
                            'id' => $request->idkeluarga,
                        ],
                        [
                            'pelamar_id'      => $idpelamar,
                            'status_hubungan' => $request->hubungan,
                            'nama_keluarga'   => trim(strtoupper($request->namalengkap)),
                            'ktp_keluarga'    => trim($request->noktp),
                            'no_bpjskes'      => trim($request->nobpjskes),
                            'status_bpjskes'  => $request->statusbpjs,
                            'updated_at'      => now(),
                        ]
                    );
                }
            });

            return response()->json([
                'message' => 'Data BPJS Tambahan Berhasil Disimpan.'
            ], 200);
        }
    }

    public function setting_bpjs(Request $request)
    {
        if ($request->ajax()) {
            $query = Karyawan::select('id', 'nip', 'nama', 'project', 'project_detail', 'deleted_at', 'updated_at')
                ->with('bpjs')
                ->when(auth()->user()->can('setting-bpjs'), function ($q) {
                    $akses = auth()->user()->getProjectAccess();
                    $q->whereIn('project', $akses);
                })
                ->orderBy('id', 'DESC')
                ->orderBy('deleted_at', 'ASC')
                ->get()
                ->toArray();
            return DataTables::of($query)
                ->addColumn('status', function ($row) {
                    return '<label class="d-flex">' . $row['nip'] . '</label>' .
                        ($row['deleted_at'] == null
                            ? '<span class=" badge bg-label-success mt-1">Aktif</span>'
                            : '<span class="badge bg-label-danger mt-1">Tidak Aktif</span>');
                })

                ->addColumn('lokasi', function ($row) {
                    $color = ['text-success', 'text-warning', 'text-primary', 'text-info', 'text-secondary', 'text-dark'];
                    $lokasi = array_map(
                        fn($item, $class) => "<span class='$class fw-bold d-flex'>$item</span>",
                        $row['lokasi_penempatan'],
                        array_slice($color, 0, count($row['lokasi_penempatan']))
                    );
                    return implode(' ', $lokasi);
                })

                ->addColumn('programtk', function ($row) {
                    $program = ['JKK', 'JKM', 'JHT', 'JP'];
                    $programValue = array_map(function ($item) use ($row) {
                        $column = strtolower($item);
                        // Cek kondisi bpjs utama dulu
                        if (!empty($row['bpjs'])) {
                            return ($row['bpjs'][$column] == 0
                                ? '<i class="fas fa-ban text-danger me-1"></i>' . $item
                                : '<i class="fas fa-shield-alt text-info me-1"></i>' . $item);
                        } else {
                            // Jika bpjs tidak ada/null, otomatis semua ikon merah
                            return '<i class="fas fa-ban text-danger me-1"></i>' . $item;
                        }
                    }, $program);
                    return implode('<br>', $programValue);
                })

                ->addColumn('upahtk', function ($row) {
                    return 'Rp ' . number_format($row['bpjs']['gapok_bpjstk'] ?? 0) . ',-';
                })
                ->addColumn('programkes', function ($row) {
                    $program = '<i class="fas fa-ban text-danger me-1"></i> JKN';
                    if (!empty($row['bpjs'])) {
                        $program = ($row['bpjs']['jkn'] == 0
                            ? '<i class="fas fa-ban text-danger me-1"></i> JKN'
                            : '<i class="fas fa-shield-alt text-success me-1"></i> JKN - KIS');
                    }
                    return $program;
                })
                ->addColumn('upahkes', function ($row) {
                    return 'Rp ' . number_format($row['bpjs']['gapok_bpjskes'] ?? 0) . ',-';
                })

                ->addColumn('action', function ($row) {
                    $payload = [
                        'id'  => $row['id'],
                        'exp' => time() + 3600  // optional, expired 1 jam
                    ];

                    $token = JWT::encode($payload, config('jwt.secret'), 'HS256');
                    if (auth()->user()->can('edit-bpjs')) {
                        $button = '<div class="btn-group">
                                    <button class="btn btn-sm btn-primary Detail" data-token="' . $token . '"><i class="fas fa-info-circle me-1"></i>Detail</button>
                                </div>
                        ';
                    } else {
                        $updatedAt = $row['bpjs']['updated_at'] ?? null;

                        $timeLabel = $updatedAt
                            ? Carbon::parse($updatedAt)->diffForHumans()
                            : 'Kosong';

                        $button = 'Diperbaharui : <small class="text-muted d-flex">' . $timeLabel . '</small>';
                    }
                    return $button;
                })

                ->rawColumns([
                    'status'     => 'status',
                    'lokasi'     => 'lokasi',
                    'programtk'  => 'programtk',
                    'upahtk'     => 'upahtk',
                    'programkes' => 'programkes',
                    'upahkes'    => 'upahkes',
                    'action'     => 'action'
                ])
                ->addIndexColumn()
                ->toJson();
        }
        return view('bpjs.setting-bpjs', [
            'judul' => 'Setting BPJS'
        ]);
    }

    public function detail_setting_bpjs(Request $request)
    {
        try {

            $token   = $request->query('token');
            $decoded = JWT::decode($token, new Key(config('jwt.secret'), 'HS256'));
            $id      = $decoded->id;

            $karyawan = Karyawan::select('id', 'project', 'project_detail', 'deleted_at', 'nama')
                ->where('id', $id)
                ->with('bpjs')
                ->first();

            $bpjs = $karyawan->bpjs ?? null;

            $view = view('bpjs._detail_setting', compact('karyawan', 'bpjs'))->render();

            return response()->json([
                'html' => $view,
                'penempatan' => $karyawan->lokasi_penempatan->toArray(),
            ]);
        } catch (ExpiredException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Sesi Anda telah habis atau token tidak valid.'
            ], 403); // 401 Unauthorized
        }
    }

    public function new_setting_bpjs(Request $request)
    {
        $karyawan = [
            'idkaryawan' => null,
            'project' => null,
            'project_detail' => null,
        ];

        $bpjs = [
            'gapok_bpjstk' => 0,
            'gapok_bpjskes' => 0,
        ];

        $lokasi_penempatan = []; // Kosong karena ini data baru
        $view = view('bpjs._detail_setting', compact('karyawan', 'bpjs'))->render();

        return response()->json([
            'html' => $view,
            // Pastikan penempatan dikirim sebagai array agar loop $.each di jQuery jalan
            'penempatan' => $lokasi_penempatan,
        ]);
    }

    public function setting_lokasi_project(Request $request)
    {
        $user = Auth::user();
        $roles = $user->roles->pluck('id')->toArray();
        if ($request->ajax()) {
            ## Cek Roles
            $data = Project::where('status', 'ENABLE')
                ->with('children.children.children.children.children')
                ->whereNull('parent_id')

                ## HO
                ->when(auth()->user()->can('setting-bpjs'), function ($q) use ($roles) {
                    if (in_array(7, $roles)) {
                        ## HR PUSAT
                        $q->where('id', 1);
                    } else if (in_array(11, $roles)) {
                        ## ADMIN BPJS
                        $q->where('id', '!=', 1);
                    }
                })

                ->get(['project.name AS text', 'project.*']);
            // $result = $data->get(['project.name AS text', 'project.*']);

            if (!auth()->user()->can('setting-bpjs')) {
                $data = null;
            }

            return response()->json($data, 200);
        }
    }

    public function store_setting_bpjs(Request $request)
    {
        $checkAll = $request->checkAll ?? 0;
        // Daftar kolom sesuai checkbox
        $columns = ['jkk', 'jkm', 'jp', 'jht', 'jkn'];

        $data = [];
        foreach ($columns as $col) {
            // Ambil dari request, kalau tidak ada default ke 0
            $data[$col] = $request->input($col, 0);
        }
        $data['gapok_bpjstk']  = str_replace('.', '', $request->upahtk);
        $data['gapok_bpjskes'] = str_replace('.', '', $request->upahkes);
        $data['updated_at']    = now();

        if ($checkAll) {
            $dtproject = $request->dtproject;
            $values = explode(',', $dtproject);

            $query = Karyawan::query();
            foreach ($values as $id) {
                $query->whereRaw("FIND_IN_SET(?, project_detail)", [trim($id)]);
            }
            # Mengambil semua ID karyawan yang cocok ke dalam satu array
            $ids = $query->pluck('id')->toArray();

            if (!empty($ids)) {
                # Jalankan semua dalam 1 Query dengan Upsert
                $dataToUpdate = [];
                foreach ($ids as $id) {
                    # Gabungkan data inputan dengan ID karyawan masing-masing
                    $dataToUpdate[] = array_merge($data, ['karyawan_id' => $id]);
                }

                // Eksekusi massal (Insert jika belum ada, Update jika sudah ada)
                // Pastikan karyawan_id di table 'bpjs' adalah UNIQUE index
                Bpjs::upsert(
                    $dataToUpdate,
                    ['karyawan_id'], // Kriteria unik
                    ['jkk', 'jkm', 'jp', 'jht', 'jkn', 'gapok_bpjstk', 'gapok_bpjskes', 'updated_at'] // Kolom yang diupdate
                );
            }
        } else {
            Bpjs::updateOrcreate(
                [
                    'karyawan_id' => $request->idkaryawan,
                ],
                $data
            );
        }

        return response()->json([
            'message' => 'Setting Bpjs Berhasil Disimpan.'
        ], 200);
    }
}

<?php

namespace App\Http\Controllers\Karyawan;

use App\Exports\KaryawanDownload;
use App\Http\Controllers\Controller;
use App\Models\Bank;
use App\Models\Bpjs;
use App\Models\Gajiekstra;
use App\Models\GradeGolongan;
use App\Models\Kabkot;
use App\Models\Karyawan;
use App\Models\Kecamatan;
use App\Models\Keluarga;
use App\Models\Kelurahan;
use App\Models\Kontrak;
use App\Models\Kontrakinduk;
use App\Models\MasterGaji;
use App\Models\MutasiKaryawan;
use App\Models\Pelamar;
use App\Models\Pemanggilan;
use App\Models\Project;
use App\Models\Provinsi;
use App\Models\Ptko;
use App\Models\RecordOut;
use App\Models\SimcSaldo;
use App\Models\StatusKontrak;
use App\Models\StatusPerkawinan;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;
use Milon\Barcode\Facades\DNS2DFacade;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Yajra\DataTables\DataTables;
use function Symfony\Component\Clock\now;

class KaryawanController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware(PermissionMiddleware::using('karyawan-aktif'), only: ['index']),
            new Middleware(PermissionMiddleware::using('karyawan-keluar'), only: ['karyawan_keluar']),
            new Middleware(PermissionMiddleware::using('edit-karyawan'), only: ['update_karyawan']),
            new Middleware(PermissionMiddleware::using('download-karyawan'), only: ['download_karyawan']),
            new Middleware(PermissionMiddleware::using('approve-karyawan-in'), only: ['request_in', 'batal_request_in', 'approval_request_in', 'proses_approval']),
            new Middleware(PermissionMiddleware::using('mutasi-karyawan'), only: ['request_mutasi', 'store_mutasikaryawan']),
            new Middleware(PermissionMiddleware::using('approve-mutasi'), only: ['approval_mutasi', 'proses_approval_mutasi']),
            new Middleware(PermissionMiddleware::using('karyawan-reset'), only: ['reset_device', 'reset_password']),
        ];
    }

    public function dashboard(Request $request)
    {
        if ($request->ajax()) {
            $akses = Auth::user()->getProjectAccess();
            $query = Karyawan::withTrashed()
                ->whereNull('tanggal_keluar')
                ->whereIn('project', $akses);

            return response()->json([
                'aktif' => (clone $query)->whereNull('deleted_at')->whereNull('tanggal_keluar')->count(),
                'keluar' => (clone $query)->whereNotNull('deleted_at')->whereNotNull('tanggal_keluar')->count(),
            ]);
        }
    }

    public function index(Request $request)
    {
        if ($request->ajax()) {
            $akses = Auth::user()->getProjectAccess();
            $query = Karyawan::whereNull('deleted_at')
                ->whereNull('tanggal_keluar')
                ->whereIn('project', $akses)
                ->with('jabatan')
                ->orderBy('percepatan', 'DESC')
                ->orderBy('id', 'DESC')
                ->orderBy('nama', 'ASC')
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
                    return date('d-M-Y', strtotime($row['tanggal_masuk']));
                })

                ->addColumn('action', function ($row) {
                    $payload = [
                        'id'  => $row['id'],
                        'exp' => time() + 3600  // optional, expired 1 jam
                    ];

                    $token = JWT::encode($payload, config('jwt.secret'), 'HS256');

                    $button = '<div class="btn-group">
                                <button class="btn btn-icon btn-sm btn-primary Detail" data-token=' . $token . ' data-bs-toggle="tooltip" data-bs-placement="top" title="Detail Karyawan"><span class="fas fa-info"></span></button>
                            ';
                    if (auth()->user()->can('karyawan-reset')) {
                        $button .= '<button class="btn btn-icon btn-sm btn-warning resetDevice text-black" data-nama="' . $row['nama'] . '" data-token=' . $token . ' data-bs-toggle="tooltip" data-bs-placement="top" title="Reset Device"><i class="fas fa-mobile-alt"></i></button>';
                        $button .= '<button class="btn btn-icon btn-sm btn-danger resetPassword" data-nama="' . $row['nama'] . '" data-token=' . $token . ' data-bs-toggle="tooltip" data-bs-placement="top" title="Reset Password"><i class="fas fa-unlock-alt"></i></button>';
                    }
                    $button .= '</div>';
                    return $button;
                })

                ->rawColumns(['lokasi' => 'lokasi', 'tglmasuk' => 'tglmasuk', 'action' => 'action'])
                ->addIndexColumn()
                ->toJson();
        }

        return view('karyawan.karyawan-aktif', [
            'judul' => 'Karyawan Aktif',
        ]);
    }

    public function karyawan_keluar(Request $request)
    {
        if ($request->ajax()) {

            $query = Karyawan::onlyTrashed()
                ->whereHas('recordout', function ($q) {
                    $q->where('status', 'SUCCESS')
                        ->whereNull('deleted_at');
                })
                ->when(auth()->user()->can('karyawan-keluar'), function ($q) {
                    $akses = auth()->user()->getProjectAccess();
                    $q->whereIn('project', $akses);
                })
                ->with([
                    'recordout:id,karyawan_id,tanggal_keluar,jabatan,status_out,catatan_keluar',
                ])
                ->orderBy('nip', 'DESC')
                ->orderBy('nama', 'ASC')
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

                ->addColumn('tglout', function ($row) {
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

                ->addColumn('action', function ($row) {
                    $payload = [
                        'id'  => $row['id'],
                        'exp' => time() + 3600  // optional, expired 1 jam
                    ];
                    $token = JWT::encode($payload, config('jwt.secret'), 'HS256');

                    $payloadPaklaring = [
                        'id'  => $row['recordout']['id'],
                        'exp' => time() + 3600  // optional, expired 1 jam
                    ];
                    $tokenPaklaring = JWT::encode($payloadPaklaring, config('jwt.secret'), 'HS256');

                    $button = '<div class="btn-group">
                                <button class="btn btn-sm btn-dark Detail" data-token=' . $token . '><i class="fas fa-info-circle me-1"></i>Detail</button>
                            ';
                    if (auth()->user()->can('paklaring-karyawan')) {
                        $button .= '<button class="btn btn-sm btn-primary Paklaring" data-token=' . $tokenPaklaring . '><i class="fas fa-file-alt me-1"></i>Paklaring</button>';
                    }
                    $button .= '</div>';
                    return $button;
                })

                ->rawColumns([
                    'lokasi' => 'lokasi',
                    'tglout' => 'tglout',
                    'alasan' => 'alasan',
                    'action' => 'action'
                ])
                ->addIndexColumn()
                ->toJson();
        }

        return view('karyawan.karyawan-keluar', [
            'judul' => 'Karyawan Keluar',
        ]);
    }

    public function download_karyawan(Request $request)
    {
        return view('karyawan.download', [
            'judul' => 'Download Karyawan'
        ]);
    }

    public function proses_download(Request $request)
    {
        $selectedId = $request->input('id');
        $ids = Project::find($selectedId)
            ->descendantsAndSelf()
            ->pluck('id')
            ->toArray();

        $query = Karyawan::whereIn('project', $ids)
            ->when($request->status === 'A', function ($q) {
                $q->whereNull('tanggal_keluar');
            }, function ($q) {
                $q->whereNotNull('tanggal_keluar');
            })
            ->whereNull('deleted_at')
            ->orderBy('project', 'ASC')
            ->get()
            ->toArray();

        return Excel::download(new KaryawanDownload($query), 'karyawan.xlsx');
    }

    public function detail_karyawan(Request $request)
    {
        try {
            $role    = auth()->user()->roles()->first()?->id;
            $token   = $request->query('token');
            $decoded = JWT::decode($token, new Key(config('jwt.secret'), 'HS256'));
            $id      = $decoded->id;

            $karyawan = Karyawan::withTrashed()
                ->where('id', $id)
                ->when(auth()->user()->can('edit-karyawan') && $role === 6, function ($q) {
                    // $q->where('user_id', auth()->id())
                    $q->whereHas('aksesProject', function ($q) {
                        $q->where('user_id', auth()->id());
                    });
                })
                ->with(
                    [
                        'pelamar:id_pelamar,file_kk,file_ktp',
                        'pelamar.keluarga',
                        'kontrak' => function ($q) {
                            $q->orderBy('id', 'desc');
                        },
                        'kontrak.statuskontrak',
                        'gradeGolongan'
                    ]
                )
                ->firstOrFail();

            $keluarga = $karyawan->pelamar->keluarga;
            $kontrak  = $karyawan->kontrak;
            $pelamar  = $karyawan->pelamar;

            if ($request->ajax()) {
                return response()->json([
                    'project'        => $karyawan->project,
                    'dtproject'      => $karyawan->project_detail,
                    'provinsi'       => $karyawan->provinsi_id,
                    'kabupaten'      => $karyawan->kabkot_id,
                    'kecamatan'      => $karyawan->kecamatan_id,
                    'desa'           => $karyawan->kelurahan_id,
                    'jabatan'        => $karyawan->jabatan,
                    'unitkerja'      => $karyawan->unitkerja,
                    'grade'          => $karyawan->grade,
                    'kelamin'        => $karyawan->kelamin,
                    'statuskawin_id' => $karyawan->statuskawin_id,
                    'bank_id'        => $karyawan->bank_id,
                ], 200);
            }
        } catch (ExpiredException $e) {
            return back()->withErrors(['token_expired' => 'Oops! Token sudah kadaluarsa.']);
        }

        return view('karyawan.detail-karyawan', [
            'judul' => 'Detail Karyawan'
        ], compact('karyawan', 'keluarga', 'kontrak', 'pelamar'));
    }

    public function lokasi_recursive(Request $request)
    {
        if ($request->ajax()) {
            $project = Project::with('allChildren')->findOrFail($request->project);
            return response()->json([$this->formatTree($project)]);
        }
    }

    protected function formatTree($project)
    {
        return [
            'id'       => $project->id,
            'text'     => $project->name,
            'children' => $project->allChildren->map(function ($child) {
                return $this->formatTree($child);
            })->toArray()
        ];
    }


    public function update_karyawan(Request $request)
    {
        $token   = $request->id;
        $decoded = JWT::decode($token, new Key(config('jwt.secret'), 'HS256'));
        $idkar      = $decoded->id;

        $validator = Validator::make(
            $request->all(),
            [
                // BIODATA
                'kk' => [
                    'required',
                    'numeric',
                    'digits:16',
                    Rule::unique('karyawan', 'kk')
                        ->where(function ($query) {
                            $query->whereNull('tanggal_keluar')
                                ->whereNull('deleted_at');
                        })
                        ->ignore($idkar),
                ],
                'ktp' => [
                    'required',
                    'numeric',
                    'digits:16',
                    Rule::unique('karyawan', 'ktp')
                        ->where(function ($query) {
                            $query->whereNull('tanggal_keluar')
                                ->whereNull('deleted_at');
                        })
                        ->ignore($idkar),
                ],

                'project' => 'required',
                'jabatan' => 'required',

                'unitkerja'     => Rule::requiredIf(function () use ($request) {
                    return isset($request->dtproject[0]) && $request->dtproject[0] == 149; // CGK
                }),
                'grade'     => Rule::requiredIf(function () use ($request) {
                    return isset($request->dtproject[0]) && $request->dtproject[0] == 149; // CGK
                }),

                'nama'          => 'required',
                'kelamin'       => 'required',
                'tempat_lahir'  => 'required',
                'tanggal_lahir' => 'required|date',
                'alamat_ktp'    => 'required',
                'agama'         => 'required',
                'hp'            => 'required|numeric',
                'email'         => [
                    'required',
                    'string',
                    'email:rfc,dns',
                    Rule::unique('karyawan', 'email')
                        ->where(function ($query) {
                            $query->whereNull('tanggal_keluar')
                                ->whereNull('deleted_at');
                        })
                        ->ignore($idkar),
                ],

                'provinsi_id'  => 'required',
                'kabkot_id'    => 'required',
                'kecamatan_id' => 'required',
                'desa_id'      => 'required',
                'kodepos'      => 'required|numeric|digits:5',
                'baju'         => 'required',
                'celana'       => 'required',
                'tb'           => 'required',
                'bb'           => 'required',
                'sepatu'       => 'required',

                // KELUARGA
                'kawin' => 'required|in:1,2,3,4,5',

                // PENDIDIKAN
                'pendidikan'  => 'required',
                'namasekolah' => 'required',
                'jurusan'     => 'required',
                'tahunlulus'  => 'required|numeric|digits:4',
                'nilai'       => 'required',

            ],
            [
                'nokk.unique'    => 'Nomor Kartu Keluarga sudah tersedia',
                'noktp.unique'   => 'Nomor KTP sudah tersedia',
                'email.unique' => 'Alamat Email sudah digunakan',
            ]
        );

        # kalau user punya izin edit-bank, field bank wajib
        $validator->sometimes('bankid', 'required|numeric', function ($input) use ($request) {
            return $request->user()->can('edit-bank');
        });

        $validator->sometimes('namabank', 'required|string|max:100', function ($input) use ($request) {
            return $request->user()->can('edit-bank');
        });

        $validator->sometimes('norek', 'required|numeric', function ($input) use ($request) {
            return $request->user()->can('edit-bank');
        });


        // pasangan wajib jika kawin = 2
        $validator->sometimes('ktpsuis', [
            'required',
            'numeric',
            'digits:16',
            // Rule::unique('keluarga', 'ktp_keluarga')->whereNull('deleted_at'),
        ], function ($input) {
            return $input->kawin == 2;
        });
        $validator->sometimes(['namasuis', 'lahirsuis', 'tglsuis', 'kelaminsuis'], 'required', function ($input) {
            return $input->kawin == 2;
        });

        // Jika kawin = 3,4,5 (K1, K2, K3), maka data anak wajib
        $validator->sometimes('ktpanak1', [
            'required',
            'numeric',
            'digits:16',
            // Rule::unique('keluarga', 'ktp_keluarga')->whereNull('deleted_at'),
        ], function ($input) {
            return in_array($input->kawin, [3, 4, 5]);
        });
        $validator->sometimes(['namaanak1', 'lahiranak1', 'tglanak1', 'kelaminanak1'], 'required', function ($input) {
            return in_array($input->kawin, [3, 4, 5]);
        });

        $validator->sometimes('ktpanak2', [
            'required',
            'numeric',
            'digits:16',
            // Rule::unique('keluarga', 'ktp_keluarga')->whereNull('deleted_at'),
        ], function ($input) {
            return in_array($input->kawin, [4, 5]);
        });
        $validator->sometimes(['namaanak2', 'lahiranak2', 'tglanak2', 'kelaminanak2'], 'required', function ($input) {
            return in_array($input->kawin, [4, 5]); // anak kedua wajib jika K2 atau K3
        });

        $validator->sometimes('ktpanak3', [
            'required',
            'numeric',
            'digits:16',
            // Rule::unique('keluarga', 'ktp_keluarga')->whereNull('deleted_at'),
        ], function ($input) {
            return $input->kawin == 5; // anak ketiga wajib jika K3
        });
        $validator->sometimes(['namaanak3', 'lahiranak3', 'tglanak3', 'kelaminanak3'], 'required', function ($input) {
            return $input->kawin == 5; // anak ketiga wajib jika K3
        });


        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->messages()->first(),
            ], 405);
        } else {
            DB::transaction(function () use ($request, $idkar) {
                $karyawan = Karyawan::findOrFail($idkar);

                $karyawan->update([
                    'kk'              => trim($request->kk),
                    'ktp'             => trim($request->ktp),
                    'nama'            => trim(strtoupper($request->nama)),
                    'email'           => trim(strtolower($request->email)),
                    'tempat_lahir'    => trim(strtoupper($request->tempat_lahir)),
                    'tanggal_lahir'   => $request->tanggal_lahir,
                    'alamat_ktp'      => trim(strtoupper($request->alamat_ktp)),
                    'alamat_domisili' => trim(strtoupper($request->alamat_domisili)),
                    'provinsi_id'     => $request->provinsi_id,
                    'kabkot_id'       => $request->kabkot_id,
                    'kecamatan_id'    => $request->kecamatan_id,
                    'kelurahan_id'    => $request->desa_id,
                    'kodepos'         => trim($request->kodepos),
                    'statuskawin_id'  => $request->kawin,
                    'kelamin'         => trim(strtoupper($request->kelamin)),
                    'agama'           => trim(strtoupper($request->agama)),
                    'handphone'       => trim($request->hp),
                    'project'         => trim($request->project),
                    'project_detail'  => trim($request->dtproject),
                    'jabatan'         => $request->jabatan,
                    'unitkerja'       => $request->unitkerja,
                    'ukuranbaju'      => trim(strtoupper($request->baju)),
                    'ukurancelana'    => trim($request->celana),
                    'tinggibadan'     => trim($request->tb),
                    'beratbadan'      => trim($request->bb),
                    'nomorsepatu'     => trim($request->sepatu),
                    'pendidikan'      => trim(strtoupper($request->pendidikan)),
                    'namasekolah'     => trim(strtoupper($request->namasekolah)),
                    'jurusan'         => trim(strtoupper($request->jurusan)),
                    'tahunlulus'      => trim($request->tahunlulus),
                    'nilai'           => trim($request->nilai),
                    // 'bank_id'         => trim($request->bankid),
                    // 'namabank'        => trim(strtoupper($request->namabank)),
                    // 'norek'           => trim($request->norek),
                    // 'percepatan'      => 0,
                    'bank_id'         => ($request->bankid ? trim($request->bankid) : $request->bank_id),
                    'namabank'        => ($request->namabank ? trim(strtoupper($request->namabank)) : null),
                    'norek'           => ($request->norek ? trim($request->norek) : null),
                    'percepatan' => (!empty($request->bankid) || !empty($request->bank_id)) ? false : true,
                    'updated_at'      => now(),
                ]);

                $pelamarId = $karyawan->pelamar_id;

                ## ===============================================================================================================  GRADE GOLONGAN
                if ($request->grade) {
                    GradeGolongan::updateOrCreate([
                        [
                            'karyawan_id'   => $idkar
                        ],
                        [
                            'grade'         => $request->grade,
                            'updated_at'    => now(),
                        ]
                    ]);
                }
                ## ===============================================================================================================  KELUARGA
                // PASANGAN
                if ($request->statussuis && $request->namasuis) {
                    Keluarga::updateOrCreate(
                        [
                            'pelamar_id'      => $pelamarId,
                            'status_hubungan' => $request->statussuis,   // ISTRI / SUAMI
                        ],
                        [
                            'nama_keluarga'      => $request->namasuis,
                            'ktp_keluarga'       => $request->ktpsuis,
                            'tmptlahir_keluarga' => $request->lahirsuis,
                            'tgllahir_keluarga'  => $request->tglsuis,
                            'kelamin_keluarga'   => $request->kelaminsuis,
                            'created_at'         => now(),
                            'updated_at'         => now(),
                        ]
                    );
                } else {
                    // kalau form kosong → soft delete pasangan lama
                    Keluarga::where('pelamar_id', $pelamarId)
                        ->whereIn('status_hubungan', ['ISTRI', 'SUAMI'])
                        ->delete(); // soft delete kalau model pakai SoftDeletes
                }


                // ANAK
                $formIds = []; // kumpulkan semua id anak dari form

                for ($i = 1; $i <= 3; $i++) {
                    $idanak  = $request->input("idanak{$i}");
                    $status  = $request->input("statusanak{$i}");
                    $nama    = $request->input("namaanak{$i}");
                    $ktp     = $request->input("ktpanak{$i}");
                    $lahir   = $request->input("lahiranak{$i}");
                    $tgl     = $request->input("tglanak{$i}");
                    $kelamin = $request->input("kelaminanak{$i}");

                    if ($status && $nama) {
                        $anak = Keluarga::updateOrCreate(
                            [
                                'id'              => $idanak,
                                'pelamar_id'      => $pelamarId,
                            ],
                            [
                                'status_hubungan'    => $status,    // ANAK
                                'nama_keluarga'      => $nama,
                                'ktp_keluarga'       => $ktp,
                                'tmptlahir_keluarga' => $lahir,
                                'tgllahir_keluarga'  => $tgl,
                                'kelamin_keluarga'   => $kelamin,
                                'created_at'         => now(),
                                'updated_at'         => now(),
                            ]
                        );

                        // simpan id anak yang diproses
                        $formIds[] = $anak->id;
                    }
                }

                // setelah loop, soft delete anak yang tidak ada di form
                Keluarga::where('pelamar_id', $pelamarId)
                    ->where('status_hubungan', 'ANAK')
                    ->whereNotIn('id', $formIds)
                    ->delete(); // ini soft delete kalau model pakai SoftDeletes
            });

            return response()->json([
                'message' => 'Data Karyawan Berhasil Disimpan.'
            ], 200);
        }
    }

    public function get_provinsi()
    {
        $query = Provinsi::select('*')->orderBy('nama_provinsi', 'ASC')->pluck('id_provinsi', 'nama_provinsi');
        return response()->json($query);
    }

    public function get_kabkot(Request $request)
    {
        $query = Kabkot::where('provinsi_id', $request->provId)->orderBy('nama_kabkot', 'ASC')->pluck('id_kabkot', 'nama_kabkot');
        return response()->json($query);
    }

    public function get_kecamatan(Request $request)
    {
        $query = Kecamatan::where('kabkot_id', $request->kabId)->orderBy('nama_kecamatan', 'ASC')->pluck('id_kecamatan', 'nama_kecamatan');
        return response()->json($query);
    }

    public function get_kelurahan(Request $request)
    {
        $query = Kelurahan::where('kecamatan_id', $request->kecId)->orderBy('nama_kelurahan', 'ASC')->pluck('id_kelurahan', 'nama_kelurahan');
        return response()->json($query);
    }

    public function get_statusperkawinan()
    {
        $query = StatusPerkawinan::select('*')->pluck('id', 'nama_status');
        return response()->json($query);
    }

    public function get_bank(Request $request)
    {
        $query = Bank::select('*')->whereNull('deleted_at')->orderBy('name', 'ASC')->pluck('id', 'name');
        return response()->json($query);
    }

    // public function change_lokasi(Request $request)
    // {
    //     if ($request->ajax()) {
    //         ## Cek Roles
    //         $data = Project::where('status', 'ENABLE')
    //             ->with('children.children.children.children.children')
    //             ->whereNull('parent_id')

    //             ## HO dan TKO
    //             ->when(auth()->user()->can('approve-karyawan-in-ho') && auth()->user()->can('approve-karyawan-in-tko'), function ($q) {
    //                 // gabungan HO + TKO
    //                 $q->where(function ($sub) {
    //                     $sub->where('id', 1)
    //                         ->orWhere('id', '!=', 1);
    //                 });
    //             })
    //             ## HO
    //             ->when(auth()->user()->can('approve-karyawan-in-ho') && !auth()->user()->can('approve-karyawan-in-tko'), function ($q) {
    //                 $q->where('id', 1);
    //             })
    //             ##TKO
    //             ->when(!auth()->user()->can('approve-karyawan-in-ho') && auth()->user()->can('approve-karyawan-in-tko'), function ($q) {
    //                 $q->where('id', '!=', 1);
    //             })

    //             ->get(['project.name AS text', 'project.*']);
    //         // $result = $data->get(['project.name AS text', 'project.*']);

    //         if (!auth()->user()->can('approve-karyawan-in-ho') && !auth()->user()->can('approve-karyawan-in-tko')) {
    //             $data = null;
    //         }

    //         return response()->json($data, 200);
    //     }
    // }

    public function cek_kontrakinduk(Request $request)
    {
        if ($request->ajax()) {
            $token = $request->query('token');
            // Decode dari Base64
            $decoded = base64_decode($token);
            $ids = explode(',', $decoded);

            ## Untuk GHM Abaikan
            if (isset($ids[0]) && $ids[0] == 1) {
                $data = true;
            } else {
                ## Selain GHM
                $data = Kontrakinduk::whereNull('deleted_at')
                    ->whereHas('settingHarga', function ($q) use ($ids) {
                        $q->whereIn('project_id', $ids);
                    })
                    ->exists();
            }

            return response()->json($data);
        }
    }

    public function cek_ptko(Request $request)
    {
        if ($request->ajax()) {
            $token = $request->query('token');
            // Decode dari Base64
            $id = base64_decode($token);
            $data = Ptko::select('id', 'nomor',)
                ->where('project_id', $id)
                ->whereNull('approval_at')
                ->whereNull('deleted_at')
                ->get();
            return response()->json($data);
        }
    }

    public function request_in(Request $request)
    {
        if ($request->ajax()) {

            $akses = auth()->user()->getProjectAccess(); // ambil child project IDs
            $rootIds = Project::whereIn('id', $akses)
                ->get()
                ->map(fn($p) => $p->businessRoot()->id)
                ->unique()
                ->values();

            $query = Pemanggilan::whereNotNull('approval_tanggal')
                ->whereNull('deleted_at')
                ->whereHas('lowonganApply.pelamar', function ($q) {
                    $q->where('status', 'PROGRESS');
                })
                ->with([
                    'lowonganApply.pelamar:id_pelamar,nama,file_cv,file_kk,file_ktp',
                    'lowonganApply.lowongan.Project:id,name',
                ])
                ->where('status', 'HRD')
                ->when(auth()->user()->can('approve-karyawan-in'), function ($q) use ($rootIds) {
                    $q->whereHas('lowonganApply.lowongan.project', function ($sub) use ($rootIds) {
                        $sub->whereIn('id', $rootIds);
                    });
                })

                ->get()->toArray();


            return DataTables::of($query)
                ->addColumn('tanggal', function ($row) {
                    return Carbon::parse($row['user_tanggal'])->translatedFormat('d F Y');
                })

                ->addColumn('lokasi', function ($row) {
                    $color = ['text-success', 'text-warning', 'text-primary', 'text-info', 'text-secondary', 'text-dark'];
                    $lokasi = array_map(
                        fn($item, $class) => "<span class='$class fw-bold d-flex'>$item</span>",
                        $row['lokasi_names'],
                        array_slice($color, 0, count($row['lokasi_names']))
                    );
                    return implode(' ', $lokasi);
                })

                ->addColumn('status', function ($row) {
                    $cv = $row['lowongan_apply']['pelamar']['file_cv'];
                    $kk = $row['lowongan_apply']['pelamar']['file_kk'];
                    $ktp = $row['lowongan_apply']['pelamar']['file_ktp'];
                    return (!empty($cv) && !empty($kk) && !empty($ktp)
                        ? '<span class="badge rounded-pill bg-label-success">Lampiran Sudah Lengkap</span>'
                        : '<span class="badge rounded-pill bg-label-danger">Belum upload lampiran</span>'
                    );
                })

                ->addColumn('action', function ($row) {
                    $cv = $row['lowongan_apply']['pelamar']['file_cv'];
                    $kk = $row['lowongan_apply']['pelamar']['file_kk'];
                    $ktp = $row['lowongan_apply']['pelamar']['file_ktp'];
                    $disabled = (!empty($cv) && !empty($kk) && !empty($ktp)
                        ? ''
                        : ''
                    );

                    $colorBtn = (!empty($cv) && !empty($kk) && !empty($ktp)
                        ? 'info'
                        : 'light'
                    );

                    $payload = [
                        'id' => $row['id'],
                        'exp' => time() + 3600 // optional, expired 1 jam
                    ];

                    $token = JWT::encode($payload, config('jwt.secret'), 'HS256');

                    return '<div class="btn-group">
                        <button class="btn btn-sm btn-' . $colorBtn . ' Proses" data-token=' . $token . ' ' . $disabled . '>Proses</button>
                        <button class="btn btn-sm btn-danger Batal" data-id=' . $row['lowongan_apply']['pelamar']['id_pelamar'] . ' data-nama="' . $row['lowongan_apply']['pelamar']['nama'] . '" data-token=' . $token . '>Batal</button>
                    </div>';
                })

                ->rawColumns(['tanggal' => 'tanggal', 'lokasi' => 'lokasi', 'status' => 'status', 'action' => 'action'])
                ->addIndexColumn()
                ->toJson();
        }

        return view('karyawan.request-in', [
            'judul' => 'Karyawan Masuk'
        ]);
    }

    public function batal_request_in(Request $request)
    {
        $token   = $request->token;
        $decoded = JWT::decode($token, new Key(config('jwt.secret'), 'HS256'));
        $id      = $decoded->id;
        $idpel   = $request->idpel;
        ## Cek Pemanggilan
        $pemanggilan = Pemanggilan::where('id', $id)
            ->whereNull('deleted_at')
            ->whereNull('hr_tanggal')
            ->whereNotNull('approval_tanggal')
            ->whereHas('lowonganApply.pelamar', function ($q) {
                $q->where('status', 'PROGRESS');
            })
            ->firstOrFail();
        ## Update Pemanggilan
        $pemanggilan->update([
            'status' => 'GAGAL',
            'hr_tanggal' => now(),
            'hr_catatan' => $request->alasan,
        ]);
        ## Create Record Pemanggilan
        $pemanggilan->recordPemanggilan()->create([
            'user_id'     => auth()->id(),
            'status'     => 'GAGAL',
            'keterangan'  => $request->alasan . ' : ' . Carbon::parse(now())->translatedFormat('d F Y')
        ]);
        ## Update Pelamar ke Waiting
        $pelamar = Pelamar::findOrFail($idpel);
        $pelamar->update([
            'status' => 'WAITING'
        ]);

        return response()->json([
            'message' => 'Kandidat Berhasil Dibatalkan.'
        ], 200);
    }

    public function approval_request_in(Request $request)
    {
        try {
            $token   = $request->token;
            $decoded = JWT::decode($token, new Key(config('jwt.secret'), 'HS256'));
            $id      = $decoded->id;

            $data = Pemanggilan::where('id', $id)
                ->whereNull('deleted_at')
                ->whereNull('hr_tanggal')
                ->whereNotNull('approval_tanggal')
                ->whereHas('lowonganApply.pelamar', function ($q) {
                    $q->where('status', 'PROGRESS');
                })
                ->with([
                    'lowonganApply.pelamar',
                    'lowonganApply.pelamar.keluarga',
                    'lowonganApply.lowongan.Project:id,name,code,inisial',
                ])

                ->firstOrFail();

            $pemanggilan = $data->id;
            $catatan     = $data->catatan;
            $pelamar     = $data->lowonganApply->pelamar;
            $keluarga    = $data->lowonganApply->pelamar->keluarga;

            if ($request->ajax()) {
                return response()->json([
                    'data'         => $data->lowonganApply->pelamar,
                    'project'      => $data->lowonganApply->lowongan->Project->id,
                    'penempatan'   => $data->lokasi_names,
                    'penempatanId' => $data->lokasi_penempatan,
                ], 200);
            }
        } catch (ExpiredException $e) {
            return back()->withErrors(['token_expired' => 'Oops! Token sudah kadaluarsa.']);
        } catch (ModelNotFoundException $e) {
            return back()->withErrors(['not_found' => 'Data pemanggilan tidak ditemukan atau tidak sesuai akses.']);
        }



        return view('karyawan.approval-karyawan', [
            'judul' => 'Approval Karyawan Baru',
        ], compact('pelamar', 'keluarga', 'pemanggilan', 'catatan'));
    }

    public function getSettingGaji(Request $request)
    {
        $token = $request->query('token');
        // Decode dari Base64
        $ids = base64_decode($token);
        $arrayIds = explode(',', $ids);

        $matchedSetting = MasterGaji::query()
            ->whereRaw("? REGEXP CONCAT('(^|,)', project_detail, '($|,)')", [$ids])
            ->whereHas('kontrakinduk.settingHarga', function ($query) use ($ids) {
                $query->whereRaw("? REGEXP CONCAT('(^|,)', project_detail, '($|,)')", [$ids])
                    ->whereHas('biaya');
            })

            ->with(['kontrakinduk.settingHarga' => function ($query) use ($ids) {
                $query->whereRaw("? REGEXP CONCAT('(^|,)', project_detail, '($|,)')", [$ids])
                    ->with('biaya'); // Ambil data biaya di dalamnya
            }])

            ->orderByRaw("LENGTH(project_detail) DESC")
            ->first();

        if ($matchedSetting && $matchedSetting->kontrakinduk) {

            // 1. Saring hanya settingHarga yang project_detail-nya COCOK dengan silsilah $ids
            $settingHargaCocok = $matchedSetting->kontrakinduk->settingHarga->filter(function ($harga) use ($arrayIds) {
                $detailProjectHarga = explode(',', $harga->project_detail);

                // Memastikan semua ID di detail_project harga ada di dalam silsilah input
                return empty(array_diff($detailProjectHarga, $arrayIds));
            })
                // 2. Urutkan dari yang paling spesifik (jumlah ID paling banyak / string terpanjang)
                ->sortByDesc(function ($harga) {
                    return count(explode(',', $harga->project_detail));
                });

            // 3. Ambil 1 data setting_harga yang paling spesifik
            $settingHargaTerpilih = $settingHargaCocok->first();

            if ($settingHargaTerpilih) {
                $dataBiaya = $settingHargaTerpilih->biaya;

                $biayaCustom = $dataBiaya->reduce(function ($result, $biaya) {
                    $jenisBiaya = strtoupper($biaya->jenis_biaya);
                    $nominal = $biaya->harga;

                    if ($jenisBiaya === "TUNJANGAN KETERAMPILAN") {
                        $result['tunjketerampilan'] = $nominal;
                    } elseif ($jenisBiaya === "TUNJANGAN JABATAN") {
                        $result['tunjab'] = $nominal;
                    } elseif ($jenisBiaya === "TUNJANGAN TETAP") {
                        $result['tuntetap'] = $nominal;
                    } elseif ($jenisBiaya === "TUNJANGAN OPERASIONAL") {
                        $result['tunjoprs'] = $nominal;
                    } elseif ($jenisBiaya === "TUNJANGAN KOMUNIKASI") {
                        $result['tunjkom'] = $nominal;
                    } elseif ($jenisBiaya === "TUNJANGAN MASAKERJA") {
                        $result['tunjmasakerja'] = $nominal;
                    } elseif ($jenisBiaya === "TUNJANGAN MAKAN") {
                        $result['tunjmakan'] = $nominal;
                    } elseif ($jenisBiaya === "TUNJANGAN TRANSPORT") {
                        $result['tunjtransport'] = $nominal;
                    } else {
                        // Jika ada jenis biaya lain yang tidak masuk kondisi, otomatis dibuatkan slug-nya
                        // Contoh: "Tunjangan Jabatan" menjadi "tunjangan_jabatan"
                        $keyOtomatis = Str::slug($biaya->jenis_biaya, '_');
                        $result[$keyOtomatis] = $nominal;
                    }

                    return $result;
                }, []); // Dimulai dari array kosong []

                // 3. Return ke JSON response
                return response()->json([
                    'status' => 'success',
                    'settinggaji' => $matchedSetting,
                    'biaya' => $biayaCustom // Sekarang bentuknya sudah custom object
                ]);
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Tidak ada setting harga yang cocok dengan hierarki project.'
                ], 404);
            }
        }
    }

    public function proses_approval(Request $request)
    {

        $projects = explode(',', $request->dtproject);
        $validator = Validator::make(
            $request->all(),
            [
                // KONTRAK
                'kontrak'       => 'required|in:1,2,3,4,5',
                'project'       => 'required',
                'tglkontrak'    => 'required|date',
                'jabatan'       => 'required',
                'lokasikontrak' => 'required',

                'unitkerja'     => Rule::requiredIf(function () use ($projects) {
                    return in_array(149, $projects); // CGK
                }),
                'grade'     => Rule::requiredIf(function () use ($projects) {
                    return in_array(149, $projects); // CGK
                }),
                'noptko'     => Rule::requiredIf(function () use ($projects) {
                    return in_array(14, $projects); // AUTO
                }),
                'golongan'     => Rule::requiredIf(function () use ($projects) {
                    return in_array(1, $projects); // GHM
                }),
                'tunjgolongan'     => Rule::requiredIf(function () use ($projects) {
                    return in_array(1, $projects); // GHM
                }),

                'tglptko' => 'required|date',
                'ketptko' => 'required',
                'dari'    => 'required|date',
                'sampai'  => 'required|date',

                // BIODATA
                'nokk' => [
                    'required',
                    'numeric',
                    'digits:16',
                    Rule::unique('karyawan', 'kk')
                        ->where(function ($query) {
                            $query->whereNull('tanggal_keluar')
                                ->whereNull('deleted_at');
                        }),
                ],
                'noktp' => [
                    'required',
                    'numeric',
                    'digits:16',
                    Rule::unique('karyawan', 'ktp')
                        ->where(function ($query) {
                            $query->whereNull('tanggal_keluar')
                                ->whereNull('deleted_at');
                        }),
                ],

                'namalengkap'    => 'required',
                'kelamin'        => 'required',
                'tmptlahir'      => 'required',
                'tgllahir'       => 'required|date',
                'alamatktp'      => 'required',
                'alamatdomisili' => 'required',
                'provinsi'       => 'required',
                'kabkot'         => 'required',
                'kecamatan'      => 'required',
                'desa'           => 'required',
                'kodepos'        => 'required|numeric|digits:5',
                'hp'             => 'required|numeric',
                'email'          => [
                    'required',
                    'string',
                    'email:rfc,dns',
                    Rule::unique('karyawan', 'email')
                        ->where(function ($query) {
                            $query->whereNull('tanggal_keluar')
                                ->whereNull('deleted_at');
                        }),
                ],
                'agama'  => 'required',
                'baju'   => 'required',
                'celana' => 'required',
                'tb'     => 'required',
                'bb'     => 'required',
                'sepatu' => 'required',

                // KELUARGA
                'kawin' => 'required|in:1,2,3,4,5',

                // PENDIDIKAN
                'pendidikan'     => 'required',
                'namasekolah'    => 'required',
                'jurusansekolah' => 'required',
                'tahunlulus'     => 'required|numeric|digits:4',
                'nilaisekolah'   => 'required',

                // BANK
                'percepatan' => 'nullable|boolean',
                'bankid'     => Rule::requiredIf(fn() => empty($request->percepatan)),
                'namabank'   => Rule::requiredIf(fn() => empty($request->percepatan)),
                'norek'      => [
                    Rule::requiredIf(fn() => empty($request->percepatan)),
                    'nullable',
                    'numeric',
                ],
                'gapok'            => 'required',
                'tunjab'           => 'required',
                'tunjtetap'        => 'required',
                'tunjoprs'         => 'required',
                'tunjkom'          => 'required',
                'tunjmasakerja'    => 'required',
                'tunjketerampilan' => 'required',
                'tunjtransport'    => 'required',
                'tunjmakan'        => 'required',
            ],
            [
                'nokk.unique'    => 'Nomor Kartu Keluarga sudah tersedia',
                'noktp.unique'   => 'Nomor KTP sudah tersedia',
                'email.unique' => 'Alamat Email sudah digunakan',
            ]
        );

        // pasangan wajib jika kawin = 2
        $validator->sometimes('ktpsuis', [
            'required',
            'numeric',
            'digits:16',
            // Rule::unique('keluarga', 'ktp_keluarga')->whereNull('deleted_at'),
        ], function ($input) {
            return $input->kawin == 2;
        });
        $validator->sometimes(['namasuis', 'lahirsuis', 'tglsuis', 'kelaminsuis'], 'required', function ($input) {
            return $input->kawin == 2;
        });

        // Jika kawin = 3,4,5 (K1, K2, K3), maka data anak wajib
        $validator->sometimes('ktpanak1', [
            'required',
            'numeric',
            'digits:16',
            // Rule::unique('keluarga', 'ktp_keluarga')->whereNull('deleted_at'),
        ], function ($input) {
            return in_array($input->kawin, [3, 4, 5]);
        });
        $validator->sometimes(['namaanak1', 'lahiranak1', 'tglanak1', 'kelaminanak1'], 'required', function ($input) {
            return in_array($input->kawin, [3, 4, 5]);
        });

        $validator->sometimes('ktpanak2', [
            'required',
            'numeric',
            'digits:16',
            // Rule::unique('keluarga', 'ktp_keluarga')->whereNull('deleted_at'),
        ], function ($input) {
            return in_array($input->kawin, [4, 5]);
        });
        $validator->sometimes(['namaanak2', 'lahiranak2', 'tglanak2', 'kelaminanak2'], 'required', function ($input) {
            return in_array($input->kawin, [4, 5]); // anak kedua wajib jika K2 atau K3
        });

        $validator->sometimes('ktpanak3', [
            'required',
            'numeric',
            'digits:16',
            // Rule::unique('keluarga', 'ktp_keluarga')->whereNull('deleted_at'),
        ], function ($input) {
            return $input->kawin == 5; // anak ketiga wajib jika K3
        });
        $validator->sometimes(['namaanak3', 'lahiranak3', 'tglanak3', 'kelaminanak3'], 'required', function ($input) {
            return $input->kawin == 5; // anak ketiga wajib jika K3
        });


        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->messages()->first(),
            ], 405);
        } else {

            DB::transaction(function () use ($request) {
                # PERANCANGAN NIP
                $project = Project::findOrFail($request->dtproject[0], ['id', 'code', 'inisial']);
                $statuskontrak = StatusKontrak::findOrFail($request->kontrak, ['id', 'inisial']);

                // parsing tanggal dengan Carbon
                $tgl = Carbon::parse($request->tglkontrak);
                $month = $tgl->format('m');
                $years = $tgl->year;

                ## Proses Perancangan NIP Karyawan
                $lahir = Carbon::parse($request->tgllahir);
                $lastUrut = Karyawan::whereRaw('UPPER(nama) = ?', [strtoupper(trim($request->namalengkap))])
                    ->whereDate('tanggal_lahir', $request->tgllahir)
                    ->whereMonth('tanggal_masuk', Carbon::parse($request->dari)->month)
                    ->whereYear('tanggal_masuk', Carbon::parse($request->dari)->year)
                    ->lockForUpdate()
                    ->max(DB::raw('RIGHT(nip, 2)'));

                $urut = ($lastUrut ?? 0) + 1;

                do {
                    $nourut = str_pad($urut, 2, '0', STR_PAD_LEFT);

                    $nip = $project->code           // Kode Project
                        . $tgl->format('y')         // tahun kontrak 2 digit
                        . $month                    // bulan kontrak 2 digit
                        . $lahir->year              // tahun lahir 4 digit
                        . $lahir->format('m')       // bulan lahir 2 digit
                        . ($request->kelamin == "L" ? 1 : 2) // kode Kelamin
                        . $nourut;                  // nomor urut 2 digit

                    $urut++;
                } while (Karyawan::where('nip', $nip)->exists());

                ## =============================================================================================================== KARYAWAN
                $dtkaryawan = array(
                    'nip'             => $nip,
                    'pelamar_id'      => trim($request->pelamarid),
                    'kk'              => trim($request->nokk),
                    'ktp'             => trim($request->noktp),
                    'nama'            => trim(strtoupper($request->namalengkap)),
                    'email'           => trim(strtolower($request->email)),
                    'tempat_lahir'    => trim(strtoupper($request->tmptlahir)),
                    'tanggal_lahir'   => $request->tgllahir,
                    'alamat_ktp'      => trim(strtoupper($request->alamatktp)),
                    'alamat_domisili' => trim(strtoupper($request->alamatdomisili)),
                    'kabkot_id'       => $request->kabkot,
                    'provinsi_id'     => $request->provinsi,
                    'kecamatan_id'    => $request->kecamatan,
                    'kelurahan_id'    => $request->desa,
                    'kodepos'         => trim($request->kodepos),
                    'statuskawin_id'  => $request->kawin,
                    'kelamin'         => trim(strtoupper($request->kelamin)),
                    'agama'           => trim(strtoupper($request->agama)),
                    'handphone'       => trim($request->hp),
                    'project'         => trim($request->project),
                    'project_detail'  => trim($request->dtproject),
                    'ptko_id'         => $request->noptko,
                    'jabatan'         => $request->jabatan,
                    'unitkerja'       => $request->unitkerja,
                    'ukuranbaju'      => trim(strtoupper($request->baju)),
                    'ukurancelana'    => trim($request->celana),
                    'tinggibadan'     => trim($request->tb),
                    'beratbadan'      => trim($request->bb),
                    'nomorsepatu'     => trim($request->sepatu),
                    'pendidikan'      => trim(strtoupper($request->pendidikan)),
                    'namasekolah'     => trim(strtoupper($request->namasekolah)),
                    'jurusan'         => trim(strtoupper($request->jurusansekolah)),
                    'tahunlulus'      => trim($request->tahunlulus),
                    'nilai'           => trim($request->nilaisekolah),
                    'bank_id'         => ($request->percepatan == null ? trim($request->bankid) : null),
                    'namabank'        => ($request->percepatan == null ? trim(strtoupper($request->namabank)) : null),
                    'norek'           => ($request->percepatan == null ? trim($request->norek) : null),
                    'percepatan'      => ($request->percepatan == null ? false : true),
                    'tanggal_masuk'   => $request->dari,
                    'created_at'      => now(),
                    'updated_at'      => now(),
                );

                ## Simpan karyawan kemudian buat variable idkar dari id;
                $idkar = Karyawan::create($dtkaryawan)->id;

                ## =============================================================================================================== KONTRAK
                $kontrakKe = Kontrak::where([
                    'karyawan_id' => $idkar,
                    'statuskontrak_id' => $request->kontrak,
                ])->count();

                // mapping romawi bisa inline
                $romawi = [
                    '01' => 'I',
                    '02' => 'II',
                    '03' => 'III',
                    '04' => 'IV',
                    '05' => 'V',
                    '06' => 'VI',
                    '07' => 'VII',
                    '08' => 'VIII',
                    '09' => 'IX',
                    '10' => 'X',
                    '11' => 'XI',
                    '12' => 'XII',
                ];

                // format id jadi 5 digit
                $idkarFormatted = str_pad($idkar, 5, '0', STR_PAD_LEFT);

                $nokontrak = sprintf(
                    '%s/GHM%s/%s/%s/%s',
                    $idkarFormatted,                       // %s pertama
                    $request->kontrak == 5 ? '' : '-' . $project->inisial, // %s kedua
                    $romawi[$month],                       // %s ketiga
                    $years,                                // %s keempat
                    $request->kontrak == 2 ? 'K' . ($kontrakKe + 1) : $statuskontrak->inisial                // %s kelima
                );

                Kontrak::create([
                    'karyawan_id'      => $idkar,
                    'statuskontrak_id' => $request->kontrak,
                    'nomor_kontrak'    => $nokontrak,
                    'kontrak_dari'     => $request->dari,
                    'kontrak_sampai'   => $request->sampai,
                    'kontrak_ke'       => ($request->kontrak == 2 ? 'K' . ($kontrakKe + 1) : ($kontrakKe + 1)),
                    'lokasi_kontrak'   => $request->lokasikontrak,
                    'tanggal_kontrak'  => $request->tglkontrak,
                    'tanggal_ptko'     => $request->tglptko,
                    'keterangan_ptko'  => $request->ketptko,
                    'created_at'       => now(),
                    'updated_at'       => now(),
                ]);

                ## ===============================================================================================================  PTKO
                if (isset($request->noptko)) {
                    $ptko = Ptko::whereNull('approval_at')
                        ->whereNull('deleted_at')
                        ->find($request->noptko);
                    $ptko->update([
                        'approval_at' => now(),
                    ]);
                }

                ## ===============================================================================================================  GRADE GOLONGAN
                GradeGolongan::create([
                    'karyawan_id'   => $idkar,
                    'grade'         => $request->grade,
                    'tunj_grade'    => ($request->tunjgrade ? str_replace(',', '', $request->tunjgrade) : 0),
                    'golongan'      => $request->golongan ?? 0,
                    'tunj_golongan' => ($request->tunjgolongan ? str_replace(',', '', $request->tunjgolongan) : 0),
                    'created_at'    => now(),
                    'updated_at'    => now(),
                ]);

                ## ===============================================================================================================  GAJI EKSTRA
                $dataTunjangan = [
                    'tunjangan_jabatan'      => $request->tunjab ? str_replace('.', '', $request->tunjab) : 0,
                    'tunjangan_tetap'        => $request->tunjtetap ? str_replace('.', '', $request->tunjtetap) : 0,
                    'tunjangan_operasional'  => $request->tunjoprs ? str_replace('.', '', $request->tunjoprs) : 0,
                    'tunjangan_komunikasi'   => $request->tunjkom ? str_replace('.', '', $request->tunjkom) : 0,
                    'tunjangan_masakerja'    => $request->tunjmasakerja ? str_replace('.', '', $request->tunjmasakerja) : 0,
                    'tunjangan_keterampilan' => $request->tunjketerampilan ? str_replace('.', '', $request->tunjketerampilan) : 0,
                    'tunjangan_transport'    => $request->tunjtransport ? str_replace('.', '', $request->tunjtransport) : 0,
                    'tunjangan_makan'        => $request->tunjmakan ? str_replace('.', '', $request->tunjmakan) : 0,
                ];

                if ($request->has('group-tunjangan')) {
                    foreach ($request->input('group-tunjangan') as $item) {
                        // Pastikan nama tunjangan dan nilainya tidak kosong
                        if (!empty($item['nmtunjangan']) && !empty($item['nilaitunjangan'])) {

                            // Mengubah nama tunjangan menjadi format slug/snake_case agar rapi di JSON
                            // Contoh: "Tunjangan Lembur" -> "tunjangan_lembur", "A" -> "a"
                            $keyDinamis = Str::slug($item['nmtunjangan'], '_');

                            // Bersihkan titik ribuan dari Cleave.js (Contoh: "50.000" -> 50000)
                            $nilaiBersih = str_replace('.', '', $item['nilaitunjangan']);

                            // Masukkan ke dalam array $dataTunjangan
                            $dataTunjangan[$keyDinamis] = (int) $nilaiBersih;
                        }
                    }
                }

                $gapokBersih = $request->gapok ? preg_replace('/[^0-9]/', '', $request->gapok) : 0;

                Gajiekstra::create([
                    'karyawan_id'     => $idkar,
                    'gapok'           => $gapokBersih,
                    'tunjangan' => $dataTunjangan,
                    'created_at'      => now(),
                    'updated_at'      => now(),
                ]);
                ## ===============================================================================================================  BPJS
                $columns = ['jkk', 'jkm', 'jp', 'jht', 'jkn'];

                $programBpjs = [];
                foreach ($columns as $col) {
                    // Ambil dari request, kalau tidak ada default ke 0
                    $programBpjs[$col] = $request->input($col, 0);
                }

                $gapokBpjs = [
                    'gapok_bpjstk'  => 0,
                    'gapok_bpjskes' => 0,
                    'created_at'    => now(),
                    'updated_at'    => now(),
                ];

                $datas = array_merge($gapokBpjs, $programBpjs);

                Bpjs::updateOrcreate(
                    [
                        'karyawan_id' => $idkar,
                    ],
                    $datas
                );
                ## ===============================================================================================================  KELUARGA
                // PASANGAN
                if ($request->statussuis && $request->namasuis) {
                    Keluarga::updateOrCreate(
                        [
                            'pelamar_id'      => $request->pelamarid,
                            'status_hubungan' => $request->statussuis,   // ISTRI / SUAMI
                        ],
                        [
                            'nama_keluarga'      => $request->namasuis,
                            'ktp_keluarga'       => $request->ktpsuis,
                            'tmptlahir_keluarga' => $request->lahirsuis,
                            'tgllahir_keluarga'  => $request->tglsuis,
                            'kelamin_keluarga'   => $request->kelaminsuis,
                            'created_at'         => now(),
                            'updated_at'         => now(),
                        ]
                    );
                } else {
                    // kalau form kosong → soft delete pasangan lama
                    Keluarga::where('pelamar_id', $request->pelamarid)
                        ->whereIn('status_hubungan', ['ISTRI', 'SUAMI'])
                        ->delete(); // soft delete kalau model pakai SoftDeletes
                }


                // ANAK
                $formIds = []; // kumpulkan semua id anak dari form

                for ($i = 1; $i <= 3; $i++) {
                    $idanak  = $request->input("idanak{$i}");
                    $status  = $request->input("statusanak{$i}");
                    $nama    = $request->input("namaanak{$i}");
                    $ktp     = $request->input("ktpanak{$i}");
                    $lahir   = $request->input("lahiranak{$i}");
                    $tgl     = $request->input("tglanak{$i}");
                    $kelamin = $request->input("kelaminanak{$i}");

                    if ($status && $nama) {
                        $anak = Keluarga::updateOrCreate(
                            [
                                'id'              => $idanak,
                                'pelamar_id'      => $request->pelamarid,
                            ],
                            [
                                'status_hubungan'    => $status,    // ANAK
                                'nama_keluarga'      => $nama,
                                'ktp_keluarga'       => $ktp,
                                'tmptlahir_keluarga' => $lahir,
                                'tgllahir_keluarga'  => $tgl,
                                'kelamin_keluarga'   => $kelamin,
                                'created_at'         => now(),
                                'updated_at'         => now(),
                            ]
                        );

                        // simpan id anak yang diproses
                        $formIds[] = $anak->id;
                    }
                }

                // setelah loop, soft delete anak yang tidak ada di form
                Keluarga::where('pelamar_id', $request->pelamarid)
                    ->where('status_hubungan', 'ANAK')
                    ->whereNotIn('id', $formIds)
                    ->delete(); // ini soft delete kalau model pakai SoftDeletes

                ## ===============================================================================================================  PELAMAR DAN PEMANGGILAN
                $pemanggilan = Pemanggilan::findOrFail($request->idpemanggilan);
                $pemanggilan->update([
                    'status' => 'DITERIMA'
                ]);

                $pemanggilan->recordPemanggilan()->create([
                    'user_id'    => auth()->id(),
                    'status'     => 'DITERIMA',
                    'keterangan' => 'Selamat anda sudah menjadi bagian dari PT Grha Humanindo Manajemen',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                Pelamar::where('id_pelamar', $request->pelamarid)->update([
                    'status' => 'COMPLETE'
                ]);

                ## ===============================================================================================================  UPDATE NOTIF
                $idnotif = null;
                foreach (auth()->user()->unreadNotifications as $key => $value) {
                    if ($request->idpemanggilan == $value->data[0]['id']) {
                        $idnotif = $value->id;
                    }
                }

                if (!empty($idnotif)) {
                    $notif = DatabaseNotification::findOrFail($idnotif);
                    $notif->update(['read_at' => now()]);
                }

                ## =============================================================================================================== SALDO CUTI
                SimcSaldo::updateOrCreate([
                    'karyawan_id' => $idkar,
                    'tahun'       => date('Y'),
                ]);
            });

            return response()->json([
                'message' => 'Proses pengaktifan karyawan berhasil dilakukan.'
            ], 200);
        }
    }

    public function request_mutasi(Request $request)
    {
        if ($request->ajax()) {
            $akses = auth()->user()->getProjectAccess();
            $query = MutasiKaryawan::whereNull('deleted_at')
                ->with('karyawan:id,nip,nama')
                ->when(!empty($akses), function ($q) use ($akses) {
                    $q->whereNotNull('project_sebelumnya')
                        ->whereRaw("
                            CAST(SUBSTRING_INDEX(project_sebelumnya, ',', -1) AS UNSIGNED) 
                            IN (" . implode(',', $akses) . ")
                        ");
                })

                ->orderByRaw("CASE 
                                WHEN status_mutasi = 'PENDING' THEN 1
                                WHEN status_mutasi = 'SELESAI' THEN 2
                                WHEN status_mutasi = 'DITOLAK' THEN 3
                                ELSE 4 END")
                // setelah itu urutkan berdasarkan created_at terbaru
                ->orderBy('created_at', 'DESC')
                ->get()
                ->toArray();

            return DataTables::of($query)

                ->addColumn('sebelumnya', function ($row) {
                    $color = ['text-light', 'text-light', 'text-secondary'];
                    $lokasi = array_map(
                        fn($item, $class) => "<span class='$class fw-bold d-flex'>$item</span>",
                        $row['project_old'],
                        array_slice($color, 0, count($row['project_old']))
                    );
                    return implode(' ', $lokasi);
                })

                ->addColumn('tujuan', function ($row) {
                    $color = ['text-info', 'text-warning', 'text-success', 'text-primary', 'text-secondary', 'text-dark'];
                    $lokasi = array_map(
                        fn($item, $class) => "<span class='$class fw-bold d-flex'>$item</span>",
                        $row['project_new'],
                        array_slice($color, 0, count($row['project_new']))
                    );
                    return implode(' ', $lokasi);
                })

                ->addColumn('tglefektif', function ($row) {
                    return Carbon::parse($row['tanggal_efektif'])->translatedFormat('d F Y');
                })

                ->addColumn('status', function ($row) {
                    if ($row['status_mutasi'] == 'PENDING') {
                        return '<span class="badge bg-label-warning">Menunggu Persetujuan</span>';
                    } else if ($row['status_mutasi'] == 'SELESAI') {
                        return '<span class="badge bg-label-success">Disetujui</span><small class="d-flex badge text-muted">' . Carbon::parse($row['updated_at'])->format('d-M-Y H:i:s') . '</small>';
                    } else {
                        return '<span class="badge bg-label-danger">Ditolak</span>';
                    }
                })

                ->addColumn('action', function ($row) {
                    $payload = [
                        'id'  => $row['id'],
                        'exp' => time() + 3600  // optional, expired 1 jam
                    ];

                    $token = JWT::encode($payload, config('jwt.secret'), 'HS256');
                    $btn = '';
                    if ($row['status_mutasi'] == "PENDING") {
                        $btn = '<button class="btn btn-sm btn-danger Batal" data-token=' . $token . '><i class="fas fa-trash me-1"></i>Batal</button>';
                    }
                    $button = '<div class="btn-group">
                            <button class="btn btn-sm btn-primary Detail" data-token=' . $token . '><i class="fas fa-info-circle me-1"></i>Detail</button>
                            ' . $btn . '
                        </div>';

                    return $button;
                })

                ->rawColumns([
                    'tglefektif'    => 'tglefektif',
                    'sebelumnya'    => 'sebelumnya',
                    'tujuan'        => 'tujuan',
                    'status'        => 'status',
                    'action'        => 'action'
                ])
                ->addIndexColumn()
                ->toJson();
        }

        return view('karyawan.request-mutasi', [
            'judul' => 'Mutasi Karyawan',
        ]);
    }

    public function list_karyawan(Request $request)
    {
        $user = Auth::user();
        $roles = $user->roles->pluck('id')->toArray();

        if ($request->ajax()) {
            $akses = auth()->user()->getProjectAccess();
            $query = Karyawan::whereNull('deleted_at')
                ->whereIn('project', $akses)
                ->orderBy('nama', 'asc')
                ->get(['id', 'nip', 'nama', 'project_detail']);

            return response()->json($query);
        }
    }

    // public function lokasi_mutasi(Request $request)
    // {
    //     if ($request->ajax()) {
    //         $data = Project::where('status', 'ENABLE')
    //             ->with('children.children.children.children.children')
    //             ->whereNull('parent_id')
    //             ->where('id', $request->project)
    //             ->get(['project.name AS text', 'project.*']);

    //         return response()->json($data, 200);
    //     }
    // }

    public function store_mutasikaryawan(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'karyawanid' => 'required',
                'projectold' => 'required',
                'project'    => 'required',
                'alasan'     => 'required',
            ],
            [
                'karyawanid.required' => 'Karyawan belum dipilih.',
                'projectold.required' => 'Project Lama masih kosong.',
                'project.required'    => 'Project Tujuan masih kosong.',
                'alasan.required'     => 'Keterangan masih kosong.',
            ]
        );

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->messages()->first(),
            ], 405);
        } else {
            // cek apakah sudah ada mutasi pending untuk karyawan ini
            $exists = MutasiKaryawan::where('karyawan_id', $request->karyawanid)
                ->where('status_mutasi', 'PENDING')
                ->exists();

            if ($exists) {
                throw new \Exception('Karyawan sudah pernah diajukan mutasi dengan status Menunggu Persetujuan');
            }

            $projectOld  = explode(',', $request->projectold);
            $lastId = end($projectOld);
            $projectBaru = $request->project;

            if ($lastId == $projectBaru) {
                throw new \Exception('Lokasi Penempatan masih sama dengan sebelumnya.');
            }

            MutasiKaryawan::create([
                'karyawan_id'        => $request->karyawanid,
                'project_sebelumnya' => $request->projectold,
                'project_tujuan'     => $request->dtproject,
                'alasan_mutasi'      => $request->alasan,
                'tanggal_efektif'    => Carbon::parse($request->tglefektif)->format('Y-m-d'),
                'users_id'           => auth()->id(),
                'status_mutasi'      => 'PENDING',
                'created_at'         => now(),
            ]);

            return response()->json([
                'message' => 'Pengajuan Mutasi Karyawan Berhasil Diproses.',
            ], 200);
        }
    }

    public function approval_mutasi(Request $request)
    {
        $user = Auth::user();
        $roles = $user->roles->pluck('id')->toArray();

        if ($request->ajax()) {
            $akses = auth()->user()->getProjectAccess();
            $query = MutasiKaryawan::whereNull('deleted_at')
                ->with('karyawan:id,nip,nama')

                ->where(function ($q) use ($akses) {
                    $q->where(function ($sub) use ($akses) {
                        ## ambil lastId dari project_detail (string)
                        $sub->whereRaw("
                            CAST(
                                SUBSTRING_INDEX(project_tujuan, ',', -1) AS UNSIGNED
                            ) IN (" . implode(',', $akses) . ")
                        ");
                    });
                })

                // urutan status: PENDING dulu, lalu SELESAI, lalu DITOLAK
                ->orderByRaw("CASE 
                    WHEN status_mutasi = 'PENDING' THEN 1
                    WHEN status_mutasi = 'SELESAI' THEN 2
                    WHEN status_mutasi = 'DITOLAK' THEN 3
                    ELSE 4 END")
                // setelah itu urutkan berdasarkan created_at terbaru
                ->orderBy('tanggal_efektif', 'ASC')
                ->get()
                ->toArray();

            return DataTables::of($query)

                ->addColumn('sebelumnya', function ($row) {
                    $color = ['text-light', 'text-light', 'text-secondary'];
                    $lokasi = array_map(
                        fn($item, $class) => "<span class='$class fw-bold d-flex'>$item</span>",
                        $row['project_old'],
                        array_slice($color, 0, count($row['project_old']))
                    );
                    return implode(' ', $lokasi);
                })

                ->addColumn('tujuan', function ($row) {
                    $color = ['text-info', 'text-warning', 'text-success', 'text-primary', 'text-secondary', 'text-dark'];
                    $lokasi = array_map(
                        fn($item, $class) => "<span class='$class fw-bold d-flex'>$item</span>",
                        $row['project_new'],
                        array_slice($color, 0, count($row['project_new']))
                    );
                    return implode(' ', $lokasi);
                })

                ->addColumn('tglefektif', function ($row) {
                    return Carbon::parse($row['tanggal_efektif'])->translatedFormat('d F Y');
                })

                ->addColumn('status', function ($row) {
                    if ($row['status_mutasi'] == 'PENDING') {
                        return '<span class="badge bg-label-warning">Menunggu Persetujuan</span>';
                    } else if ($row['status_mutasi'] == 'SELESAI') {
                        return '<span class="badge bg-label-success">Disetujui</span><small class="d-flex badge text-muted">' . Carbon::parse($row['updated_at'])->format('d-M-Y H:i:s') . '</small>';
                    } else {
                        return '<span class="badge bg-label-danger">Dibatalkan</span><br><small class="text-xs text-light">' . $row['alasan_ditolak'] . '</small>';
                    }
                })

                ->addColumn('action', function ($row) {
                    $payload = [
                        'id'  => $row['id'],
                        'exp' => time() + 3600  // optional, expired 1 jam
                    ];

                    $token = JWT::encode($payload, config('jwt.secret'), 'HS256');
                    $btn = '';
                    if ($row['status_mutasi'] == "PENDING") {
                        $btn = '<button class="btn btn-sm btn-danger Batal" data-token=' . $token . '><i class="fas fa-trash me-1"></i>Batal</button>';
                    }
                    $button = '<div class="btn-group">
                            <button class="btn btn-sm btn-primary Detail" data-token=' . $token . '><i class="fas fa-info-circle me-1"></i>Detail</button>
                            ' . $btn . '
                        </div>';

                    return $button;
                })

                ->rawColumns([
                    'tglefektif'    => 'tglefektif',
                    'sebelumnya'    => 'sebelumnya',
                    'tujuan'        => 'tujuan',
                    'status'        => 'status',
                    'action'        => 'action'
                ])
                ->addIndexColumn()
                ->toJson();
        }
        return view('karyawan.approval-mutasi', [
            'judul' => 'Approval Mutasi Karyawan'
        ]);
    }

    public function detail_mutasikaryawan(Request $request)
    {
        if ($request->ajax()) {
            $token   = $request->query('token');
            $decoded = JWT::decode($token, new Key(config('jwt.secret'), 'HS256'));
            $id      = $decoded->id;

            $query = MutasiKaryawan::with('karyawan:id,nip,nama')->where('id', $id)->first();
            $query->tanggal = Carbon::parse($query->tanggal_efektif)->format('d F Y');
            return response()->json($query->toArray());
        }
    }

    public function proses_approval_mutasi(Request $request)
    {
        $query = MutasiKaryawan::where([
            'id'            => $request->idmutasi,
            'status_mutasi' => 'PENDING'
        ])->exists();

        if ($query) {
            DB::transaction(function () use ($request) {
                $data = MutasiKaryawan::find($request->idmutasi);
                $details = explode(',', $data->project_tujuan);
                $project = end($details);
                # Update Mutasi
                $data->update([
                    'status_mutasi' => 'SELESAI',
                    'approval_id'   => auth()->id(),
                    'updated_at'    => now(),
                ]);
                # Update Karyawan
                $karyawan = Karyawan::find($data->karyawan_id);
                $karyawan->update([
                    'project'        => $project,
                    'project_detail' => $data->project_tujuan,
                    'updated_at'     => now(),
                ]);
            });

            return response()->json([
                'message' => 'Mutasi Karyawan Berhasil Diproses.'
            ], 200);
        } else {
            return response()->json([
                'errors' => 'Data tidak ditemukan, silahkan reload halaman.',
            ], 405);
        }
    }

    public function proses_pembatalan_mutasi(Request $request)
    {
        if ($request->ajax()) {

            $token   = $request->token;
            $decoded = JWT::decode($token, new Key(config('jwt.secret'), 'HS256'));
            $id      = $decoded->id;

            $query = MutasiKaryawan::where([
                'id'            => $id,
                'status_mutasi' => 'PENDING'
            ])->exists();

            if ($query) {
                $batal = MutasiKaryawan::find($id);
                $batal->update([
                    'status_mutasi' => 'DITOLAK',
                    'alasan_ditolak' => $request->alasan,
                    'approval_id'   => auth()->id(),
                ]);

                return response()->json([
                    'message' => 'Mutasi Karyawan Berhasil Dibatalkan.'
                ], 200);
            } else {
                return response()->json([
                    'errors' => 'Data tidak ditemukan',
                ], 405);
            }
        }
    }

    public function paklaring_karyawan(Request $request)
    {
        $token   = $request->input('token');
        $decoded = JWT::decode($token, new Key(config('jwt.secret'), 'HS256'));
        $id      = $decoded->id;

        $data = RecordOut::find($id)->toArray();

        $barcode = DNS2DFacade::getBarcodePNG($data['barcode'], 'QRCODE');
        $data['periode'] = ucwords(strtolower(Carbon::parse($data['tanggal_masuk'])->format('d F Y'))) . ' s/d ' . ucwords(strtolower(Carbon::parse($data['tanggal_keluar'])->format('d F Y')));
        $data['tanggal'] = ucwords(strtolower(Carbon::parse(now())->format('d F Y')));

        if ($data['status_out'] == 'MENGUNDURKAN DIRI') {
            $alasan = 'Mengundurkan Diri';
        } else if ($data['status_out'] == 'HABIS KONTRAK') {
            $alasan = 'Telah berakhir masa kontrak kerja';
        } else if ($data['status_out'] == 'MENINGGAL DUNIA') {
            $alasan = 'Meninggal Dunia';
        } else {
            $alasan = ucwords(strtolower($data['status_out']));
        }

        // $pdf = Pdf::loadView('karyawan.paklaring-karyawan', compact('data', 'alasan', 'barcode'))
        //     ->setPaper('A4', 'portrait');

        // return $pdf->stream($data['nama'] . '.pdf');

        $pdf = Pdf::loadView('karyawan.paklaring-karyawan', [
            'data'    => $data,
            'barcode' => $barcode,
            'alasan'  => $alasan,
        ]);

        $filename = 'PAKLARING - ' . $data['nama'] . '.pdf';

        return response($pdf->output(), 200)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }

    public function get_penempatan(Request $request)
    {
        $idkar = decrypt($request->token);
        if ($request->ajax()) {
            $query = Karyawan::select('id', 'nip', 'nama', 'project_detail')
                ->whereNull('deleted_at')
                ->whereNull('tanggal_keluar')
                ->where('id', $idkar)
                ->first();

            return response()->json([
                'penempatan' => $query->lokasi_penempatan,
                'data' => $query,
            ]);
        }
    }

    public function reset_device(Request $request)
    {
        $token   = $request->input('token');
        $decoded = JWT::decode($token, new Key(config('jwt.secret'), 'HS256'));
        $id      = $decoded->id;

        $karyawan = Karyawan::findOrFail($id);
        $karyawan->update([
            'fcm_id'        => null,
            'ios_id'        => null,
            'imei'          => null,
            'token_apps'    => null,
            'reset_token'   => null,
            'register_apps' => null,
        ]);

        return response()->json([
            'message' => 'Device Berhasil Direset.'
        ], 200);
    }

    public function reset_password(Request $request)
    {
        $token   = $request->input('token');
        $decoded = JWT::decode($token, new Key(config('jwt.secret'), 'HS256'));
        $id      = $decoded->id;

        $karyawan = Karyawan::findOrFail($id);
        $karyawan->update([
            'fcm_id'        => null,
            'ios_id'        => null,
            'imei'          => null,
            'token_apps'    => null,
            'reset_token'   => null,
        ]);

        return response()->json([
            'message' => 'Password Berhasil Direset.'
        ], 200);
    }
}

<?php

namespace App\Http\Controllers\Pemanggilan;

use App\Http\Controllers\Controller;
use App\Models\Lowongan;
use App\Models\LowonganApply;
use App\Models\Pelamar;
use App\Models\Pemanggilan;
use App\Models\PemanggilanRecord;
use App\Models\Project;
use App\Services\NotificationService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Firebase\JWT\JWT;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Yajra\DataTables\DataTables;

class PemanggilanController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware(PermissionMiddleware::using('lihat-pemanggilan'), only: ['index']),
            new Middleware(PermissionMiddleware::using('tambah-pemanggilan'), only: ['panggil_pelamar', 'store_pemanggilan_pelamar', 'store_invite']),
            new Middleware(PermissionMiddleware::using('edit-pemanggilan'), only: ['edit_tanggal_panggil', 'update_tanggal_panggil', 'print_absensi', 'mulai_test', 'store_nilai', 'kirim_user']),
            new Middleware(PermissionMiddleware::using('banksdm'), only: ['bank_sdm', 'store_banksdm', 'store_sdm_resend']),
            new Middleware(PermissionMiddleware::using('approve-banksdm'), only: ['persetujuan_user', 'store_persetujuanuser', 'store_persetujuanuser_one']),
        ];
    }

    public function index(Request $request)
    {
        // Cek Roles
        $role = auth()->user()->roles()->first()?->id;

        $query = Pemanggilan::with([
            'user:id,name',
            'lowonganApply.lowongan.project' => function ($q) {
                $q->select('id', 'name', 'logo');
            }
        ])
            ->whereHas('lowonganApply.lowongan.project', function ($q) {
                $q->where('status', 'ENABLE');
            })
            ->whereNull('deleted_at')
            ->when(
                auth()->user()->can('lihat-pemanggilan') && $role === 6, //'PROJECT LEADER'
                fn($q) => $q->where('user_id', auth()->user()->id)
            )
            ->where(function ($q) {
                $q->whereNull('hadir')
                    ->orWhere('hadir', 'YA');
                // ->orWhere('hadir', 'TIDAK');
            })
            ->whereNotIn('status', ['DITERIMA', 'GAGAL']);

        // Tambahkan filter search untuk tgl_panggil
        if ($request->filled('search')) {
            $search = strtolower(trim($request->search));

            $bulanMap = [
                'januari'   => 1,
                'februari'  => 2,
                'maret'     => 3,
                'april'     => 4,
                'mei'       => 5,
                'juni'      => 6,
                'juli'      => 7,
                'agustus'   => 8,
                'september' => 9,
                'oktober'   => 10,
                'november'  => 11,
                'desember'  => 12
            ];

            $hariMap = [
                'minggu' => 1,   // MySQL DAYOFWEEK: Minggu=1
                'senin'  => 2,
                'selasa' => 3,
                'rabu'   => 4,
                'kamis'  => 5,
                'jumat'  => 6,
                'sabtu'  => 7,
            ];

            $matched = false;

            // cek bulan
            foreach ($bulanMap as $namaBulan => $angka) {
                if (Str::startsWith($namaBulan, $search)) {
                    $query->whereMonth('tgl_panggil', $angka);
                    $matched = true;
                    break;
                }
            }

            // cek hari
            if (!$matched) {
                foreach ($hariMap as $namaHari => $angka) {
                    if (Str::startsWith($namaHari, $search)) {
                        $query->whereRaw('DAYOFWEEK(tgl_panggil) = ?', [$angka]);
                        $matched = true;
                        break;
                    }
                }
            }

            // fallback lain
            if (!$matched) {
                if (preg_match('/^\d{4}-\d{2}$/', $search)) {
                    $query->where('tgl_panggil', 'like', "{$search}%");
                } elseif (strtotime($search)) {
                    $tanggal = \Carbon\Carbon::parse($search)->format('Y-m-d');
                    $query->whereDate('tgl_panggil', $tanggal);
                } else {
                    // $query->where('tgl_panggil', 'like', "%{$search}%");
                    $query->where(function ($q) use ($search) {
                        $q->where('tgl_panggil', 'like', "%{$search}%")
                            ->orWhereHas('user', function ($sub) use ($search) {
                                $sub->where('name', 'like', '%' . $search . '%');
                            })
                            ->orWhereHas('lowonganApply.pelamar', function ($sub) use ($search) {
                                $sub->where('nama', 'like', '%' . $search . '%');
                            });
                    });
                }
            }
        }

        $query = $query->select(
            'user_id',
            'tgl_panggil',
            DB::raw('COUNT(*) as total'),
            DB::raw('MIN(status) as status')
        )
            ->groupBy('user_id', 'tgl_panggil');

        // pagination
        $pemanggilans = $query

            ->orderByRaw("
                CASE 
                    WHEN MIN(status) = 'PENDING' 
                        AND DATE(tgl_panggil) = CURDATE() 
                    THEN 0
                    WHEN MIN(status) = 'PROSES' THEN 1
                    WHEN MIN(status) = 'PENDING' 
                        AND DATE(tgl_panggil) <= CURDATE() 
                    THEN 4
                    WHEN MIN(status) = 'USER' THEN 2
                    WHEN MIN(status) = 'HRD' THEN 3
                    ELSE 5
                END
            ")
            ->orderByRaw("ABS(DATEDIFF(tgl_panggil, CURDATE())) ASC") // semakin dekat ke hari ini semakin atas
            ->orderBy('tgl_panggil', 'desc')
            ->paginate(6);

        // Jika AJAX, kirim partial view
        if ($request->ajax()) {
            return view('pemanggilan._list', compact('pemanggilans'))->render();
        }

        return view('pemanggilan.data-pemanggilan', [
            'judul' => 'Data Pemanggilan Pelamar'
        ], compact('pemanggilans'));
    }

    public function detail_pemanggilan(Request $request)
    {
        if ($request->ajax() && $request->datatable) {
            $tanggal = $request->tgl_panggil;
            $user = $request->user;

            $collection = Pemanggilan::with([
                'lowonganApply.pelamar',
                'lowonganApply.lowongan.project'
            ])
                ->whereDate('tgl_panggil', $tanggal)
                ->whereNotIn('status', ['DITERIMA', 'GAGAL'])
                ->where('user_id', $user)
                ->whereNull('deleted_at')
                ->get();
            // ->toArray();

            $status = $request->status;
            $total  = $collection->count();

            // cek apakah semua filed hasil_test sudah terisi
            $kirim = $collection->every(function ($item) {
                return !is_null($item->hasil_test);
            });

            $kumpulanStatusUnique = $collection->pluck('status')->unique()->toArray();

            if (in_array('PENDING', $kumpulanStatusUnique)) {
                // Prioritas 1: Jika ada satu saja peserta berstatus PENDING, anggap hari ini masih PENDING
                $statusUtama = 'PENDING';
            } elseif (in_array('PROSES', $kumpulanStatusUnique)) {
                // Prioritas 2: Jika tidak ada PENDING tetapi ada PROSES, setel menjadi PROSES
                $statusUtama = 'PROSES';
            } elseif (in_array('USER', $kumpulanStatusUnique)) {
                $statusUtama = 'USER';
            } elseif (in_array('HRD', $kumpulanStatusUnique)) {
                $statusUtama = 'HRD';
            } elseif (in_array('DITERIMA', $kumpulanStatusUnique)) {
                $statusUtama = 'DITERIMA';
            } else {
                // Jika isinya murni HRD semua tanpa campuran PENDING/PROSES, baru setel HRD
                $statusUtama = 'HRD';
            }

            // convert ke array untuk DataTables
            $query = $collection->toArray();

            return DataTables::of($query)
                ->addColumn('nama', function ($row) {
                    return $row['lowongan_apply']['pelamar']['nama'];
                })

                ->addColumn('lokasi', function ($row) {
                    return $row['lowongan_apply']['lowongan']['project']['inisial'] ?? $row['lowongan_apply']['lowongan']['project']['name'];
                })

                ->addColumn('dilamar', function ($row) {
                    return $row['lowongan_apply']['lowongan']['pekerjaan_dilamar'];
                })

                ->addColumn('ceklis', function ($row) {
                    $isNilai = !is_null($row['hasil_test']);

                    return '
                        <i class="fas fa-check-square fa-2x ' . ($isNilai ? 'text-warning' : 'text-secondary') . '"></i>
                    ';
                })

                ->addColumn('action', function ($row) {
                    $payload = [
                        'id'  => $row['id'],
                        'exp' => time() + 3600  // optional, expired 1 jam
                    ];

                    $token = JWT::encode($payload, config('jwt.secret'), 'HS256');

                    // Jika punya permission edit-pemanggilan
                    if (auth()->user()->can('edit-pemanggilan')) {
                        // JIKA STATUSNYA PENDING
                        if ($row['status'] == "PENDING") {
                            if ($row['hadir'] == null) {
                                return '<button class="btn btn-icon btn-warning btn-sm text-black editTanggal" data-id="' . $row['id'] . '"><i class="ti ti-calendar"></i></button>';
                            } else if ($row['hadir'] == 'YA') {
                                // Jika sudah kelewat waktu tidak di jalankan mulai test
                                if (Carbon::parse($row['tgl_panggil'])->lt(Carbon::today())) {
                                    return '<button class="btn btn-icon btn-info btn-sm text-black mulaiTest" data-tgl="' . $row['tgl_panggil'] . '" data-user="' . $row['user_id'] . '"><i class="ti ti-palette"></i></button>';
                                } else {
                                    return '<i class="fas fa-calendar-check text-success ti-sm"></i>';
                                }
                            } else {
                                return '<button class="btn btn-icon btn-danger btn-sm text-black editTanggal" data-id="' . $row['id'] . '"><i class="ti ti-calendar"></i></button><br><small class="text-danger">' . $row['catatan'] . '</small>';
                            }

                            // JIKA STATUSNYA PROSES
                        } else if ($row['status'] == "PROSES") {

                            $isNilai = !is_null($row['hasil_test']);

                            $html = '<div class="btn-group" role="group">';

                            if (auth()->user()->can('psikotest-input')) {
                                $html .= (is_null($row['cfit_id'])
                                    ? '<button class="btn btn-icon btn-facebook btn-sm testBtn" data-bs-toggle="tooltip" data-bs-placement="top" title="CFIT" data-token="' . $token . '" data-url="/cfit-input/">C</button>'
                                    : ''
                                );
                                $html .= (is_null($row['mbti_id'])
                                    ? '<button class="btn btn-icon btn-success btn-sm testBtn" data-bs-toggle="tooltip" data-bs-placement="top" title="MBTI" data-bs-custom-class="tooltip-success" data-token="' . $token . '" data-url="/mbti-input/">M</button>'
                                    : ''
                                );
                                $html .= (is_null($row['papi_id'])
                                    ? '<button class="btn btn-icon btn-dark btn-sm testBtn" data-bs-toggle="tooltip" data-bs-placement="top" title="PAPI" data-bs-custom-class="tooltip-dark" data-token="' . $token . '" data-url="/papi-input/">P</button>'
                                    : ''
                                );
                                $html .= (is_null($row['disc_id'])
                                    ? '<button class="btn btn-icon btn-primary btn-sm testBtn" data-bs-toggle="tooltip" data-bs-placement="top" title="DISC" data-bs-custom-class="tooltip-primary" data-token="' . $token . '" data-url="/disc-input/">D</button>'
                                    : ''
                                );
                            }

                            $html .= (is_null($row['wawancara_id'])
                                ? '<button class="btn btn-icon btn-secondary btn-sm text-black wawancara" data-token="' . $token . '" data-bs-toggle="tooltip" data-bs-placement="top" title="Wawancara">
                                        <i class="ti ti-microphone"></i>
                                        </button>'
                                : ''
                            );

                            $html .= '<button class="btn btn-icon btn-info btn-sm text-black addNilai" data-id="' . $row['id'] . '" data-bs-toggle="tooltip" data-bs-placement="top" title="Input Nilai">
                                        <i class="ti ti-receipt"></i>
                                        </button>
                                    ';

                            // Jika semua nilai sudah terisi, tambahkan tombol ekstra
                            if ($isNilai) {
                                $html .= '<button class="btn btn-icon btn-warning btn-sm text-black kirimUser" data-id="' . $row['id'] . '" data-bs-toggle="tooltip" data-bs-placement="top" title="Kirim User">
                                            <i class="far fa-paper-plane"></i>
                                        </button>
                                    ';
                            }

                            $html .= '</div>';

                            return $html;

                            // JIKA STATUSNYA USER
                        } else if ($row['status'] == "USER") {
                            if ($row['user_tanggal'] == null) {
                                return '<span class="badge bg-label-info">Bank SDM</span>';
                            }
                            return '<span class="badge bg-label-info">Menunggu Approval User</span>';
                        } else if ($row['status'] == "HRD") {
                            return '<span class="badge bg-label-success">Verifikasi Berkas</span>';
                        }
                    } else {
                        return '<i class="fas fa-ban text-danger ti-sm"></i>';
                    }
                })

                ->rawColumns(['nama' => 'nama', 'lokasi' => 'lokasi', 'dilamar' => 'dilamar', 'ceklis' => 'ceklis', 'action' => 'action'])
                ->addIndexColumn()
                ->with([
                    'status'  => $statusUtama,
                    'tanggal' => $tanggal,
                    'user'    => $user,
                    'total'   => $total,
                    'kirim'   => $kirim,
                ])

                ->toJson();
        }

        $tanggal = $request->tgl_panggil;
        return view('pemanggilan._detail', compact('tanggal'));
    }

    public function invite_pelamar(Request $request)
    {
        if ($request->ajax()) {
            // Cek Roles
            $role = auth()->user()->roles()->first()?->id;
            //Ambil semua akses project user
            $aksesProjects = Project::whereHas('aksesProject', function ($q) {
                $q->where('user_id', auth()->id());
            })->get();

            // ambil id paling atas dari parent_id
            $rootIds = $aksesProjects
                ->map(fn($p) => $p->businessRoot()->id)
                ->unique()
                ->values();

            $lowongan = Lowongan::select('id', 'judul', 'project_id')
                ->with(['project' => function ($q) {
                    $q->select('id', 'name', 'inisial') // hanya ambil kolom tertentu
                        ->where('status', 'ENABLE');
                }])
                ->whereNull('deleted_at')

                ->where('end', '>=', Carbon::today())
                ->when(
                    $role == 6,
                    fn($q) => $q->whereIn('project_id', $rootIds)
                )
                ->get();

            $query = Pelamar::where('status', 'WAITING')
                ->where(function ($q) {
                    // Kondisi 1: belum punya lowonganApply
                    $q->doesntHave('lowonganApply')
                        // Kondisi 2: punya lowonganApply dengan pemanggilan status DITERIMA/GAGAL
                        ->orWhereHas('lowonganApply.pemanggilan', function ($q2) {
                            $q2->whereIn('status', ['DITERIMA', 'GAGAL']);
                        });
                })
                ->get()
                ->toArray();

            return DataTables::of($query)
                ->addColumn('ceklis', function ($row) {
                    return '
                        <div class="form-check-warning">
                          <input class="form-check-input check-invite" type="checkbox" value="' . $row['id_pelamar'] . '" />
                        </div>
                    ';
                })
                ->rawColumns(['ceklis' => 'ceklis'])
                ->addIndexColumn()
                ->with([
                    'keperluan' => $lowongan
                ])
                ->toJson();
        }
    }

    public function store_invite(Request $request)
    {
        $lowonganid = $request->input('lowonganid');
        $noregs = $request->input('noreg'); // array of selected IDs

        $validator = Validator::make(
            $request->all(),
            [
                'lowonganid' => 'required',
            ],
            [
                'lowonganid.required' => 'Keperluan project / lowongan belum dipilih.',
            ]
        );

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->messages()->first(),
            ], 405);
        } else {

            if (!$noregs || count($noregs) === 0) {
                return response()->json([
                    'errors' => 'Tidak ada pelamar yang dipilih.'
                ], 405);
            }
            // Dengan ini, kalau salah satu step gagal, semuanya rollback. karena dibungkus dengan transaction
            DB::transaction(function () use ($noregs, $lowonganid) {
                foreach ($noregs as $noreg) {
                    // 1. Insert ke Lowongan Apply
                    $invitePelamar = LowonganApply::create([
                        'no_registrasi' => 'INVITE-' . now()->format('Ymd-His') . '-' . strtoupper(Str::random(4)),
                        'pelamar_id'    => $noreg,
                        'lowongan_id'   => $lowonganid,
                        'created_at'    => now(),
                        'updated_at'    => now(),
                    ]);
                }
            });

            return response()->json([
                'message' => 'Pelamar Berhasil Ditambahkan.',
            ], 200);
        }
    }

    public function destroy_invite(Request $request)
    {
        $cek = LowonganApply::where('no_registrasi', $request->noreg)
            ->whereDoesntHave('pemanggilan')
            ->first();

        // langsung hapus instance yang sudah ditemukan
        $cek->delete();

        return response()->json([
            'message' => 'Kandidat Berhasil Dihapus.'
        ]);
    }

    public function panggil_pelamar(Request $request)
    {
        if ($request->ajax()) {
            // Cek Roles
            $role = auth()->user()->roles()->first()?->id;

            //Ambil semua akses project user
            $aksesProjects = Project::whereHas('aksesProject', function ($q) {
                $q->where('user_id', auth()->id());
            })->get();

            // ambil id paling atas dari parent_id
            $rootIds = $aksesProjects
                ->map(fn($p) => $p->businessRoot()->id)
                ->unique()
                ->values();

            $query = LowonganApply::WhereDoesntHave('pemanggilan')
                ->whereHas('pelamar', function ($q) {
                    $q->where('status', 'WAITING');
                })
                ->with([
                    'pelamar:id_pelamar,nama,jenis_kelamin,email,tinggi_badan,jenjang_pendidikan,nama_sekolah,jurusan_sekolah,tahun_lulus,nilai_ijazah',
                    'lowongan:id,judul,pekerjaan_dilamar,penempatan,project_id',
                    'lowongan.Project:id,name,inisial'
                ])
                ->with('lowongan', function ($q) {
                    $q->select('id', 'judul', 'pekerjaan_dilamar', 'penempatan', 'project_id');
                })

                // Jika punya akses tambah-pemanggilan dan ROles nya PL
                ->when(
                    auth()->user()->can('tambah-pemanggilan') && $role === 6, // PROJECT LEADER
                    fn($q) => $q->whereHas('lowongan', fn($q2) => $q2->whereIn('project_id', $rootIds))
                )
                ->orderBy('lowongan_id')
                ->get()
                ->toArray();

            return DataTables::of($query)
                ->addColumn('status', function ($row) {
                    $status = Str::before($row['no_registrasi'], '-');
                    if (strtolower($status) === 'apply') {
                        return '<span class="badge bg-label-primary">APPLY</span>';
                    }
                    return '
                            <span class="badge bg-label-warning">' . strtoupper($status) . '</span> 
                            <i class="fas fa-trash-alt text-danger ms-1 cancelInvite" data-reg="' . $row['no_registrasi'] . '" data-nama="' . $row['pelamar']['nama'] . '" style="cursor:pointer"></i>
                        ';
                })

                ->addColumn('judul', function ($row) {
                    return '
                            <label>' . $row['lowongan']['judul'] . '</label>
                            <br>
                            <label class="text-info">' . ucwords(strtolower($row['lowongan']['penempatan'])) . '</label>
                            <small class="text-muted">( ' . ucwords(strtolower($row['lowongan']['pekerjaan_dilamar'])) . ' )</small><br>
                            <small class="text-warning">' . $row['lowongan']['project']['inisial'] . '</small>
                        ';
                })

                ->addColumn('ceklis', function ($row) {
                    return '
                        <div class="form-check-warning">
                          <input class="form-check-input check-panggil" type="checkbox" value="' . $row['no_registrasi'] . '" id="customCheckWarning" />
                        </div>
                    ';
                })

                ->rawColumns(['status' => 'status', 'judul' => 'judul', 'ceklis' => 'ceklis'])
                ->addIndexColumn()
                ->toJson();
        }

        return view('pemanggilan.pemanggilan', [
            'judul' => 'Pemanggilan Pelamar'
        ]);
    }

    public function store_pemanggilan_pelamar(Request $request)
    {
        $tanggal = date('Y-m-d', strtotime($request->input('tgl')));
        $noregs = $request->input('noreg'); // array of selected IDs

        $validator = Validator::make(
            $request->all(),
            [
                'tgl' => 'required|date|after_or_equal:today',
            ],
            [
                'tgl.required'       => 'Tanggal pemanggilan harus diisi.',
                'tgl.after_or_equal' => 'Tanggal pemanggilan tidak boleh kurang dari tanggal sekarang.',
            ]
        );

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->messages()->first(),
            ], 405);
        } else {

            if (!$noregs || count($noregs) === 0) {
                return response()->json([
                    'errors' => 'Tidak ada pelamar yang dipilih.'
                ], 405);
            }

            // Dengan ini, kalau salah satu step gagal, semuanya rollback. karena dibungkus dengan transaction
            DB::transaction(function () use ($noregs, $tanggal) {
                foreach ($noregs as $noreg) {
                    $statusReg = Str::before($noreg, '-');
                    // 1. Insert ke pemanggilan
                    $pemanggilan = Pemanggilan::create([
                        'no_registrasi' => $noreg,
                        'tgl_panggil'   => $tanggal,
                        'user_id'       => auth()->id(),
                        'created_at'    => now(),
                        'updated_at'    => now(),
                    ]);

                    // 2. Ambil data dari lowongan_apply
                    $apply = DB::table('lowongan_apply')
                        ->where('no_registrasi', $noreg)
                        ->first();

                    // 3a. Insert pemanggilan record pertama (dari lowongan_apply)
                    $pemanggilan->recordPemanggilan()->create([
                        'user_id'    => auth()->id(),
                        'status'     => $statusReg,
                        'keterangan' => $statusReg . ' Lowongan',
                        'created_at' => $apply
                            ? $apply->created_at
                            :  null,
                        'updated_at' => now(),
                    ]);

                    // 3b. Insert record kedua (pemanggilan biasa)
                    $pemanggilan->recordPemanggilan()->create([
                        'user_id'    => auth()->id(),
                        'status'     => 'PEMANGGILAN',
                        'keterangan' => 'Dilakukan pemanggilan untuk tanggal : ' . Carbon::parse($tanggal)->translatedFormat('d F Y')
                    ]);
                }

                // 4. Update status pelamar
                Pelamar::whereIn('id_pelamar', function ($query) use ($noregs) {
                    $query->select('pelamar_id')
                        ->from('lowongan_apply')
                        ->whereIn('no_registrasi', $noregs);
                })->update(['status' => 'PROGRESS']);
            });


            return response()->json([
                'message' => 'Pemanggilan Pelamar Berhasil Diproses.',
            ], 200);
        }
    }

    public function edit_tanggal_panggil(Request $request)
    {
        if ($request->ajax()) {

            $data = Pemanggilan::with([
                'lowonganApply.pelamar',
                'lowonganApply.lowongan.project'
            ])
                ->whereNotIn('status', ['DITERIMA', 'GAGAL'])
                ->whereNull('deleted_at')
                ->where('status', 'PENDING')
                ->where('id', $request->id)
                ->first();

            if (!$data) {
                return response()->json([
                    'errors' => 'Data pemanggilan tidak ditemukan'
                ], 404);
            }

            $data->tgl_panggil = Carbon::parse($data->tgl_panggil)->translatedFormat('d F Y');
            return response()->json($data);
        }

        // Error Page
        return response()->view('errors.404', [
            'judul' => 'Unauthorized!',
            'exception' => 'Data tidak ditemukan',
            'status' => 404
        ], 404);
    }

    public function update_tanggal_panggil(Request $request)
    {

        $idpanggil = $request->idpanggil;
        $validator = Validator::make(
            $request->all(),
            [
                'tglpanggil' => 'required|date|after_or_equal:today',
            ],
            [
                'tglpanggil.required'       => 'Tanggal pemanggilan harus diisi.',
                'tglpanggil.after_or_equal' => 'Tanggal pemanggilan tidak boleh kurang dari tanggal sekarang.',
            ]
        );

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->messages()->first(),
            ], 405);
        } else {
            if ($idpanggil) {
                $pemanggilan = Pemanggilan::find($idpanggil);
                $pemanggilan->update([
                    'tgl_panggil'   => date('Y-m-d', strtotime($request->input('tglpanggil'))),
                    'hadir'         => null,
                    'create_hadir' => null,
                ]);

                $pemanggilan->recordPemanggilan()->create([
                    'user_id'    => auth()->id(),
                    'status'     => 'RESCHEDULE',
                    'keterangan' => 'Penjadawalan ulang pemanggilan menjadi tanggal : ' . Carbon::parse($request->input('tgl_panggil'))->translatedFormat('d F Y')
                ]);
            } else {
                $data = Pemanggilan::select('id')
                    ->whereNull('deleted_at')
                    ->where('status', 'PENDING')
                    ->where([
                        'tgl_panggil' => $request->tglsebelum,
                        'user_id' => $request->user,
                    ])
                    ->get();

                foreach ($data as $val) {
                    $pemanggilan = Pemanggilan::find($val->id);
                    $pemanggilan->update([
                        'tgl_panggil' => date('Y-m-d', strtotime($request->input('tglpanggil')))
                    ]);

                    $pemanggilan->recordPemanggilan()->create([
                        'user_id'    => auth()->id(),
                        'status'     => 'RESCHEDULE',
                        'keterangan' => 'Penjadawalan ulang pemanggilan menjadi tanggal : ' . Carbon::parse($request->input('tgl_panggil'))->translatedFormat('d F Y')
                    ]);
                }
            }

            return response()->json([
                'message' => 'Perubahan Tanggal Pemanggilan Berhasil Diproses.',
            ], 200);
        }
    }

    public function print_absensi(Request $request)
    {
        $datas = Pemanggilan::whereNull('deleted_at')
            ->with('lowonganApply.pelamar', function ($q) {
                $q->select('id_pelamar', 'nama')->where('status', 'PROGRESS');
            })
            ->where('status', 'PENDING')
            ->where([
                'tgl_panggil' => $request->tgl,
                'user_id' => $request->user,
            ])
            ->where(function ($q) {
                $q->where('hadir', 'YA');
            })
            ->get();

        $tanggal = Carbon::parse($request->tgl)->translatedFormat('l, d F Y');

        $pdf = Pdf::loadView('pemanggilan.absensi', compact('datas', 'tanggal'));
        // Tampilkan di browser
        return $pdf->stream('Absensi.pdf');
        // Langsung Download
        // return $pdf->download('laporan.pdf');

    }

    public function mulai_test(Request $request)
    {
        $query = Pemanggilan::select('id', 'no_registrasi', 'hadir')
            ->whereNull('deleted_at')
            ->with('lowonganApply.pelamar', function ($q) {
                $q->select('id_pelamar', 'nama')->where('status', 'PROGRESS');
            })
            ->where('status', 'PENDING')
            ->where([
                'tgl_panggil' => $request->tgl,
                'user_id' => $request->user,
            ])
            ->get();

        if ($query->isNotEmpty()) {
            foreach ($query as $val) {
                $pemanggilan = Pemanggilan::findOrFail($val->id);

                // tentukan status berdasarkan hadir
                if ($val->hadir === 'YA') {
                    $newStatus = 'PROSES';
                    $keterangan = 'Proses Pelaksanaan Test pada ' . Carbon::parse($request->tgl)->translatedFormat('l d F Y');
                    $catatan = '';
                } else {
                    // jika TIDAK atau NULL
                    $newStatus = 'GAGAL';
                    $keterangan = 'Peserta tidak hadir untuk melakukan test pada ' . Carbon::parse($request->tgl)->translatedFormat('l d F Y');
                    $catatan = 'Peserta tidak hadir pada saat test';

                    // Update status di tabel pelamar
                    if ($val->lowonganApply && $val->lowonganApply->pelamar) { // Cek Relasi
                        $pelamar = $val->lowonganApply->pelamar;
                        $pelamar->update(['status' => 'WAITING']);
                    }
                }

                // update status pemanggilan
                $pemanggilan->update([
                    'status'  => $newStatus,
                    'catatan' => $catatan,
                ]);

                $pemanggilan->recordPemanggilan()->create([
                    'user_id'    => auth()->id(),
                    'status'     => $newStatus,
                    'keterangan' => $keterangan
                ]);
            }

            return response()->json([
                'message' => 'Proses test sudah siap dilaksanakan.',
            ], 200);
        }

        return response()->json([
            'message' => 'Tidak ada data pemanggilan untuk diproses.',
        ], 404);
    }

    public function input_nilai(Request $request)
    {
        if ($request->ajax()) {

            $query = Pemanggilan::whereNull('deleted_at')
                ->with('lowonganApply.pelamar', function ($q) {
                    $q->select('id_pelamar', 'nama')->where('status', 'PROGRESS');
                })
                ->with('lowonganApply.lowongan.Project', function ($q) {
                    $q->select('id', 'inisial');
                })
                ->where('status', 'PROSES');

            // cek apakah id ada dan tidak kosong
            if ($request->filled('id')) {
                $query->where('id', $request->id);
            } else {
                $query->where([
                    'tgl_panggil' => $request->tgl,
                    'user_id'     => $request->user,
                ]);
            }


            $query = $query->get()
                ->sortBy(fn($item) => optional($item->lowonganApply->pelamar)->nama)
                ->values();

            if ($query->isNotEmpty()) {
                return response()->json($query);
            }
        }
    }

    public function store_nilai(Request $request)
    {
        $idpanggil = $request->idpanggil;
        $hasil     = $request->hasil;
        $catatan   = $request->catatan;

        // Dengan transaksi: kalau salah satu operasi gagal, semua perubahan dibatalkan (rollback). Jadi tidak ada data “setengah jalan”.
        DB::transaction(function () use ($idpanggil, $hasil, $catatan) {
            foreach ($idpanggil as $index => $id) {
                $statusNilai = 'PROSES';

                if ($hasil[$index] == 0) {
                    $statusNilai = 'GAGAL';
                }

                $pemanggilan = Pemanggilan::findOrFail($id);
                $pemanggilan->update([
                    'hasil_test'    => $hasil[$index],
                    'hasil_catatan' => $catatan[$index],
                    'status'        => $statusNilai,
                    'hasil_tanggal' => now(),
                ]);

                if ($statusNilai === 'GAGAL' && $pemanggilan->lowonganApply && $pemanggilan->lowonganApply->pelamar) {
                    $pemanggilan->lowonganApply->pelamar->update([
                        'status' => 'WAITING'
                    ]);
                }

                $record = PemanggilanRecord::where('pemanggilan_id', $id)
                    ->where('keterangan', 'like', '%Input Nilai %')
                    ->first();

                $dataRecord = [
                    'status'     => $statusNilai,
                    'keterangan' => 'Input Nilai Pemanggilan dengan hasil : '
                        . ($hasil[$index] == 0 ? 'TIDAK LULUS' : 'LULUS')
                        . ' catatan : ' . $catatan[$index],
                    'user_id'    => auth()->id(),
                ];

                if ($record) {
                    $record->update($dataRecord);
                } else {
                    PemanggilanRecord::create($dataRecord + ['pemanggilan_id' => $id]);
                }
            }
        });

        return response()->json([
            'message' => 'Nilai Psikotes Berhasil Disimpan.',
        ], 200);
    }

    public function kirim_user(Request $request)
    {
        if ($request->ajax()) {
            $query = Pemanggilan::where('status', 'PROSES')
                ->with(['lowonganApply.pelamar' => function ($q) {
                    $q->select('id_pelamar', 'nama', 'status')
                        ->where('status', 'PROGRESS');
                }])
                ->whereNull('deleted_at')
                ->whereNotNull('hasil_test');

            // cek apakah id ada dan tidak kosong
            if ($request->filled('id')) {
                $query->where('id', $request->id);
            } else {
                $query->where([
                    'tgl_panggil' => $request->tgl,
                    'user_id'     => $request->user,
                ]);
            }

            $listkirim = $query->get();

            // Dengan transaksi: kalau salah satu operasi gagal, semua perubahan dibatalkan (rollback). Jadi tidak ada data “setengah jalan”.
            DB::transaction(function () use ($listkirim) {
                foreach ($listkirim as $pemanggilan) {
                    // Update tabel pemanggilan
                    $pemanggilan->update([
                        'status' => 'USER',
                    ]);

                    // Create record pemanggilan (relasi otomatis isi pemanggilan_id)
                    $pemanggilan->recordPemanggilan()->create([
                        'user_id'    => auth()->id(),
                        'status'     => 'USER',
                        'keterangan' => 'Menunggu jadwal test oleh user.',
                    ]);
                }
            });

            // setelah transaksi, cek status keseluruhan
            $allUser = $listkirim->every(function ($item) {
                return $item->status === 'USER';
            });


            return response()->json([
                'message' => 'Berhasil mengirim ke user',
                'status'  => $allUser ? 'USER' : 'PROSES',
            ], 200);
        }
    }

    public function bank_sdm(Request $request)
    {
        if ($request->ajax()) {
            $akses = auth()->user()->getProjectAccess();
            $rootIds = Project::whereIn('id', $akses)
                ->get()
                ->map(fn($p) => $p->businessRoot()->id)
                ->unique()
                ->values();

            // Ambil ID lowongan sesuai dengan akses project
            $lowonganIds = Lowongan::select('id', 'project_id')
                ->whereNull('deleted_at')
                ->whereIn('project_id', $rootIds)
                ->whereHas('project', function ($q) {
                    $q->where('status', 'ENABLE');
                })
                ->pluck('id');

            // QUERY Pemanggilan yang lowongan apply nya sesuai id lowongan diatas
            $query = Pemanggilan::where('status', 'USER')
                // Filter sesuai akses project yang ditampilkan.
                ->whereHas('lowonganApply', function ($q) use ($lowonganIds) {
                    $q->whereIn('lowongan_id', $lowonganIds);
                })
                // End Filter sesuai akses project yang ditampilkan.
                ->with(['lowonganApply.pelamar' => function ($q) {
                    $q->select('id_pelamar', 'nama', 'status', 'tgllahir', 'tinggi_badan', 'jenjang_pendidikan', 'nama_sekolah', 'jurusan_sekolah')
                        ->where('status', 'PROGRESS');
                }])
                ->with('lowonganApply.lowongan.project')

                ->whereNull('deleted_at')
                // 1) user_tanggal NULL duluan
                ->orderByRaw('user_tanggal IS NULL DESC')
                // 2) user_tanggal ASC (untuk yang tidak NULL)
                ->orderBy('user_tanggal', 'asc')
                // 3) approval_tanggal NULL duluan
                ->orderByRaw('approval_tanggal IS NULL DESC')
                // 4) approval_tanggal ASC (untuk yang tidak NULL)
                ->orderBy('approval_tanggal', 'asc')
                // 5) terakhir tgl_panggil ASC
                ->orderBy('tgl_panggil', 'asc')

                ->get()
                ->toArray();

            return DataTables::of($query)
                ->addColumn('status', function ($row) {
                    $status = Str::before($row['no_registrasi'], '-');
                    if (strtolower($status) === 'apply') {
                        return '<span class="badge bg-label-primary">APPLY</span>';
                    } else if (strtolower($status) === 'invite') {
                        return '<span class="badge bg-label-warning">INVITE</span>';
                    } else {
                        return '<span class="badge bg-label-info">' . strtoupper($status) . '</span>';
                    }
                })

                ->addColumn('panggil', function ($row) {
                    return Carbon::parse($row['tgl_panggil'])->translatedFormat('d F Y');
                })

                ->addColumn('usia', function ($row) {
                    $tglLahir = Carbon::parse($row['lowongan_apply']['pelamar']['tgllahir']);
                    $usia = $tglLahir->age; // otomatis hitung umur dari tanggal lahir

                    return $usia . ' tahun';
                })

                ->addColumn('ceklis', function ($row) {
                    if ($row['user_tanggal'] == null) {
                        return '
                            <div class="form-check-info">
                              <input class="form-check-input check-kirim" type="checkbox" value="' . $row['no_registrasi'] . '" >
                            </div>
                        ';
                    }
                    return '<span class="badge bg-label-success"><i class="fas fa-check-circle me-1"></i>Menunggu Approval user</span>';
                })

                ->rawColumns(['status' => 'status', 'panggil' => 'panggil', 'usia' => 'usia', 'ceklis' => 'ceklis'])
                ->addIndexColumn()
                ->toJson();
        }

        return view('pemanggilan.bank-sdm', [
            'judul' => 'Data Bank SDM',
        ]);
    }

    public function store_banksdm(Request $request)
    {
        $noregs = $request->input('noreg');

        if (!$noregs || count($noregs) === 0) {
            return response()->json([
                'errors' => 'Tidak ada pelamar yang dipilih.'
            ], 405);
        }

        try {
            // Dengan ini, kalau salah satu step gagal, semuanya rollback. karena dibungkus dengan transaction
            DB::transaction(function () use ($noregs, $request) {
                foreach ($noregs as $noreg) {
                    // 1. Cek dan Update
                    $pemanggilan = Pemanggilan::whereNull('user_tanggal')
                        ->where([
                            'no_registrasi' => $noreg,
                            'status'        => 'USER',
                        ])
                        ->firstOrFail();

                    $pemanggilan->update([
                        'user_tanggal' => now(),
                        'lokasi_penempatan' => $request->keperluan,
                        'request_id'   => auth()->id(),
                        'updated_at'   => now(),
                    ]);
                    // 2 Insert record kedua (pemanggilan biasa)
                    $pemanggilan->recordPemanggilan()->create([
                        'user_id'    => auth()->id(),
                        'status'     => 'PROSES USER',
                        'keterangan' => 'Berkas sudah dikirim ke user pada tanggal : ' . Carbon::parse(now())->translatedFormat('d F Y')
                    ]);
                }
            });

            return response()->json([
                'message' => 'Kandidat Berhasil Dikirim.',
            ], 200);
        } catch (ModelNotFoundException $e) {
            // tangkap error kalau firstOrFail tidak ketemu
            return response()->json([
                'message' => 'Data pemanggilan tidak ditemukan. Silahkan refresh halaman anda.',
            ], 404);
        }
    }

    public function bank_sdm_resend(Request $request)
    {
        if ($request->ajax()) {
            $akses = auth()->user()->getProjectAccess();
            $rootIds = Project::whereIn('id', $akses)
                ->get()
                ->map(fn($p) => $p->businessRoot()->id)
                ->unique()
                ->values();

            // Ambil ID lowongan sesuai dengan akses project
            $lowonganIds = Lowongan::select('id', 'project_id')
                ->with('project')
                ->whereNull('deleted_at')
                ->whereIn('project_id', $rootIds)
                ->pluck('id');

            $dataRecall = LowonganApply::whereIn('lowongan_id', $lowonganIds)
                ->whereHas('pemanggilan', function ($q) {
                    $q->whereNotNull('approval_tanggal')
                        ->where('status', 'GAGAL');
                })
                ->whereHas('pelamar', function ($q) {
                    $q->where('status', 'WAITING');
                })
                ->with(
                    [
                        'pemanggilan:no_registrasi,lokasi_penempatan',
                        'pelamar:id_pelamar,nama,status,tgllahir,tinggi_badan,jenjang_pendidikan,nama_sekolah,jurusan_sekolah',
                        'lowongan.Project:id,name,inisial',
                    ]
                )
                ->get();
            // groupBy pelamar_id
            $grouped = $dataRecall->groupBy('pelamar_id');

            $query = $grouped->map(function ($items, $pelamarId) {
                $lokasiIds = explode(',', $items->first()->pemanggilan->lokasi_penempatan);
                $lokasiNames = Project::whereIn('id', $lokasiIds)->pluck('name')->toArray();

                $pelamar = $items->first()->pelamar;
                // filter hanya item dengan no_registrasi diawali "RESEND"
                $resendItems = $items->filter(function ($i) {
                    return Str::startsWith($i->no_registrasi, 'RESEND');
                });
                // ambil record terbaru berdasarkan created_at
                $latest = $items->sortByDesc('created_at')->first();

                return [
                    'pelamar_id'   => $pelamarId,
                    'nama'         => $pelamar->nama,
                    'status'       => $pelamar->status,
                    'tgllahir'     => $pelamar->tgllahir,
                    'tinggi_badan' => $pelamar->tinggi_badan,
                    'pendidikan'   => $pelamar->jenjang_pendidikan,
                    'sekolah'      => $pelamar->nama_sekolah,
                    'jurusan'      => $pelamar->jurusan_sekolah,
                    'catatan'      => $latest->catatan,
                    // jumlah apply hanya yang semua bukan hanya RESEND*
                    'jumlah_resend' => $items->count(),
                    'tgl_resend'    => $items->pluck('created_at')
                        ->map(fn($tgl) => "<span class='badge rounded-pill bg-label-dark mb-1'>" .  Carbon::parse($tgl)->format('d-m-Y') . "</span>")
                        ->implode('<br>'),

                    // gabungkan semua no_registrasi yang RESEND*
                    'no_registrasi' => optional($latest)->no_registrasi,
                    // project info (semua project yang terkait)
                    'projects' => $items->map(fn($i) => $i->lowongan->project->name)->implode(', '),
                    'penempatan'   => $lokasiNames
                ];
            })->values(); // reset index jadi array numerik

            return DataTables::of($query)
                ->addColumn('tanggal', function ($row) {
                    return ($row['jumlah_resend'] == 0 ? '-' : '<span class="badge bg-primary mb-1">' . $row['jumlah_resend'] . 'x kirim</span>') . '<br>' . $row['tgl_resend'];
                })

                ->addColumn('ceklis', function ($row) {
                    return '
                        <div class="form-check-primary">
                          <input class="form-check-input check-resend" type="checkbox" value="' . $row['no_registrasi'] . '" >
                        </div>
                    ';
                })

                ->addColumn('lokasi', function ($row) {
                    $color = ['text-success', 'text-warning', 'text-primary', 'text-info', 'text-secondary', 'text-dark'];
                    $lokasi = array_map(
                        fn($item, $class) => "<span class='$class fw-bold d-flex'>$item</span>",
                        $row['penempatan'],
                        array_slice($color, 0, count($row['penempatan']))
                    );
                    return implode(' ', $lokasi);
                })

                ->rawColumns(['tanggal' => 'tanggal', 'lokasi' => 'lokasi', 'ceklis' => 'ceklis'])
                ->addIndexColumn()
                ->toJson();
        }
    }

    public function store_sdm_resend(Request $request)
    {
        $catatan = $request->input('catatan');
        $noregs = $request->input('noreg'); // array of selected IDs

        $validator = Validator::make(
            $request->all(),
            [
                'noreg'     => 'required|array|min:1',
                'noreg.*'   => 'required',
                'catatan'   => 'required|array|min:1',
                'catatan.*' => 'required',
            ],
            [
                'noreg.required'     => 'Tidak ada kandidat yang dipilih.',
                'noreg.array'        => 'Format data kandidat tidak valid.',
                'noreg.min'          => 'Tidak ada kandidat yang dipilih.',
                'noreg.*.required'   => 'kandidat tidak boleh kosong.',
                'catatan.required'   => 'Tidak ada kandidat yang dipilih.',
                'catatan.array'      => 'Format data kandidat tidak valid.',
                'catatan.min'        => 'Tidak ada kandidat yang dipilih.',
                'catatan.*.required' => 'Catatan kandidat tidak boleh kosong.',
            ]
        );

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->messages()->first(),
            ], 405);
        } else {

            DB::transaction(function () use ($noregs, $catatan) {
                foreach ($noregs as $noreg) {
                    $apply = LowonganApply::findOrFail($noreg);
                    // ambil bagian setelah tanda "-"
                    $suffix = Str::afterLast($apply->no_registrasi, '-');
                    // clone record lama
                    $newApply = LowonganApply::create([
                        'no_registrasi' => 'RESEND-' . now()->format('Ymd-His') . '-' . $suffix,
                        'pelamar_id'    => $apply->pelamar_id,
                        'lowongan_id'   => $apply->lowongan_id,
                        'created_at'    => now(),
                        'updated_at'    => now(),
                    ]);

                    $pemanggilan = Pemanggilan::where('no_registrasi', $noreg)->firstOrFail();
                    $newPemanggilan = Pemanggilan::create([
                        'no_registrasi'     => $newApply->no_registrasi,
                        'tgl_panggil'       => date('Y-m-d'),
                        'status'            => 'USER',
                        'catatan'           => $catatan[$noreg],
                        'user_id'           => auth()->id(),
                        'create_hadir'      => $pemanggilan->create_hadir,
                        'hadir'             => $pemanggilan->hadir,
                        'hasil_test'        => $pemanggilan->hasil_test,
                        'hasil_catatan'     => $pemanggilan->hasil_catatan,
                        'hasil_tanggal'     => $pemanggilan->hasil_tanggal,
                        'lokasi_penempatan' => $pemanggilan->lokasi_penempatan,
                        'data_result'       => $pemanggilan->data_result,
                        'request_id'        => auth()->id(),
                        'user_tanggal'      => now(),
                        'created_at'        => now(),
                        'updated_at'        => now(),
                    ]);

                    $newPemanggilan->recordPemanggilan()->create([
                        'user_id'       => auth()->id(),
                        'status'        => 'RESEND',
                        'keterangan'    => 'Dilakukan pengiriman ulang untuk test user.',
                        'created_at'    => now(),
                        'updated_at'    => now(),
                    ]);

                    // 4. Update status pelamar
                    $updatePelamar = Pelamar::where([
                        'id_pelamar' => $apply->pelamar_id,
                        'status'     => 'WAITING'
                    ])->update(['status' => 'PROGRESS']);
                }
            });

            return response()->json([
                'message' => 'Kandidat Berhasil Dikirim Ulang.',
            ], 200);
        }
    }

    public function persetujuan_user(Request $request)
    {
        if ($request->ajax()) {

            $akses = auth()->user()->getProjectAccess(); // ambil child project IDs
            $rootIds = Project::whereIn('id', $akses)
                ->get()
                ->map(fn($p) => $p->businessRoot()->id)
                ->unique()
                ->values();

            $query = Pemanggilan::where('status', 'USER')
                ->whereNotNull('user_tanggal')
                ->with([
                    'lowonganApply.pelamar' => function ($q) {
                        $q->select('id_pelamar', 'nama', 'status', 'tgllahir', 'jenjang_pendidikan')
                            ->where('status', 'PROGRESS');
                    },
                    'lowonganApply.lowongan.project' => function ($q) {
                        $q->select('id', 'name', 'status', 'inisial')
                            ->where('status', 'ENABLE');
                    }
                ])
                ->when(auth()->user()->can('approve-banksdm'), function ($q) use ($rootIds) {
                    $q->whereHas('lowonganApply.lowongan.project', function ($sub) use ($rootIds) {
                        $sub->whereIn('id', $rootIds);
                    });
                })
                ->whereNull(['deleted_at', 'approval_tanggal'])
                ->orderBy('tgl_panggil', 'asc')
                ->get()
                ->toArray();

            return DataTables::of($query)
                ->addColumn('panggil', function ($row) {
                    $status = Str::before($row['no_registrasi'], '-');
                    $catatan = $row['hasil_catatan'];
                    if (strtolower($status) === 'resend') {
                        $catatan = '
                            <span class="badge bg-label-info">RESEND</span>
                            <small class="text-warning">( ' . $row['catatan'] . ' )</small>
                        ';
                    }

                    return Carbon::parse($row['user_tanggal'])->translatedFormat('d F Y') . '<br>' . $catatan;
                })

                ->addColumn('usia', function ($row) {
                    $tglLahir = Carbon::parse($row['lowongan_apply']['pelamar']['tgllahir']);
                    $usia = $tglLahir->age; // otomatis hitung umur dari tanggal lahir

                    return $usia . ' tahun';
                })

                ->addColumn('penempatan', function ($row) {
                    $color = ['text-success', 'text-warning', 'text-primary', 'text-info', 'text-secondary', 'text-dark'];
                    $lokasi = array_map(
                        fn($item, $class) => "<span class='$class fw-bold d-flex'>$item</span>",
                        $row['lokasi_names'],
                        array_slice($color, 0, count($row['lokasi_names']))
                    );
                    return implode(' ', $lokasi);
                })

                ->addColumn('ceklis', function ($row) {
                    return '
                        <div class="btn-group">
                            <div class="form-check-success me-1">
                                <input class="form-check-input check-user" type="checkbox" value="' . $row['no_registrasi'] . '" >
                            </div>
                            <button type="button" class="btn btn-icon btn-outline-primary btn-sm approvalOne" data-id="' . $row['id'] . '" data-nama="' . $row['lowongan_apply']['pelamar']['nama'] . '">
                                <i class="fas fa-paper-plane text-dark"></i>
                            </button>
                        </div>
                    ';
                })

                ->rawColumns(['panggil' => 'panggil', 'usia' => 'usia', 'penempatan' => 'penempatan', 'ceklis' => 'ceklis'])
                ->addIndexColumn()
                ->toJson();
        }

        return view('pemanggilan.persetujuan-user', [
            'judul' => 'Data Calon Karyawan',
        ]);
    }

    public function store_persetujuanuser(Request $request)
    {
        $akses = auth()->user()->getProjectAccess(); // ambil child project IDs
        $rootIds = Project::whereIn('id', $akses)
            ->get()
            ->map(fn($p) => $p->businessRoot()->id)
            ->unique()
            ->values();

        $noregs = $request->input('noreg', []);

        // Ambil semua data USER yang relevan
        $query = Pemanggilan::where('status', 'USER')
            ->whereNotNull('user_tanggal')
            ->with(
                'lowonganApply.pelamar',
                function ($q) {
                    $q->select('id_pelamar', 'nama', 'status', 'tgllahir', 'jenjang_pendidikan')
                        ->where('status', 'PROGRESS');
                }
            )
            ->when(auth()->user()->can('approve-banksdm'), function ($q) use ($rootIds) {
                $q->whereHas('lowonganApply.lowongan.project', function ($sub) use ($rootIds) {
                    $sub->whereIn('id', $rootIds);
                });
            })
            ->whereNull('deleted_at')
            ->whereNull('approval_tanggal')
            ->get();

        if ($query->isEmpty()) {
            return response()->json([
                'errors' => 'Data tidak ditemukan.'
            ], 405);
        }

        DB::transaction(function () use ($query, $noregs) {
            foreach ($query as $row) {
                if (in_array($row->no_registrasi, $noregs)) {
                    # Pelamar dicentang → status HRD
                    $row->update([
                        'status'           => 'HRD',
                        'approval_tanggal' => now(),
                        'updated_at'       => now(),
                    ]);

                    $row->recordPemanggilan()->create([
                        'user_id'    => auth()->id(),
                        'status'     => 'VERIFIKASI',
                        'keterangan' => 'Berkas diteruskan ke HRD',
                    ]);

                    ## ===============================================================================================================  KIRIM NOTIFIKASI
                    $notificationService = new NotificationService();
                    $notificationService->ApprovalKaryawanBaru([
                        'id'      => $row->id,
                        'nama'    => $row->lowonganApply->pelamar->nama,
                        'project' => $row->lokasi_penempatan,
                        'url'     => '/approvalIn',
                        'judul'   => 'Approval karyawan baru.',
                        'pesan'   => 'Persetujuan karyawan untuk Project ' . ucwords(strtolower($row->toArray()['lokasi_names'][0] ?? 'Project')),
                    ]);
                } else {
                    #  Pelamar tidak dicentang → status GAGAL
                    $row->update([
                        'status'           => 'GAGAL',
                        'approval_tanggal' => now(),
                        'updated_at'       => now(),
                    ]);

                    $row->recordPemanggilan()->create([
                        'user_id'    => auth()->id(),
                        'status'     => 'GAGAL',
                        'keterangan' => 'Tidak lolos test oleh user',
                    ]);

                    # Update tabel pelamar → WAITING
                    $row->lowonganApply->pelamar()->update([
                        'status' => 'WAITING',
                    ]);
                }
            }
        });


        return response()->json([
            'message' => 'Kandidat berhasil diproses.',
        ], 200);
    }

    public function store_persetujuanuser_one(Request $request)
    {
        try {
            $data = Pemanggilan::where('id', $request->id)
                ->whereNotNull('user_tanggal')
                ->whereNull(['deleted_at', 'approval_tanggal'])
                ->with([
                    'lowonganApply.pelamar:id_pelamar,nama'
                ])
                ->firstOrFail();

            $data->update([
                'status'           => 'HRD',
                'approval_tanggal' => now()
            ]);

            $data->recordPemanggilan()->create([
                'user_id'    => auth()->id(),
                'status'     => 'VERIFIKASI',
                'keterangan' => 'Berkas diteruskan ke HRD',
            ]);

            ## ===============================================================================================================  KIRIM NOTIFIKASI
            $notificationService = new NotificationService();
            $notificationService->ApprovalKaryawanBaru([
                'id'      => $data->id,
                'nama'    => $data->lowonganApply->pelamar->nama,
                'project' => $data->lokasi_penempatan,
                'url'     => '/approvalIn',
                'judul'   => 'Approval karyawan baru.',
                'pesan'   => 'Persetujuan karyawan untuk Project ' . ucwords(strtolower($data->toArray()['lokasi_names'][0])),
            ]);

            return response()->json([
                'message' => 'Kandidat berhasil diproses untuk selanjutnya dilakukan pemberkasan oleh HRD.',
            ], 200);
        } catch (ModelNotFoundException $e) {
            // tangkap error kalau firstOrFail tidak ketemu
            return response()->json([
                'message' => 'Calon Kandidat gagal diproses. Silahkan refresh halaman anda.',
            ], 404);
        }
    }
}

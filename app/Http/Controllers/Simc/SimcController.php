<?php

namespace App\Http\Controllers\Simc;

use App\Http\Controllers\Controller;
use App\Models\CutiBersama;
use App\Models\Karyawan;
use App\Models\RekapKehadiran;
use App\Models\Simc;
use App\Models\SimcDetail;
use App\Models\SimcSaldo;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Str;

class SimcController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware(PermissionMiddleware::using('simc-sakit'), only: ['index']),
            new Middleware(PermissionMiddleware::using('simc-approval-sakit'), only: ['proses_tambah_sakit', 'proses_approval_sakit', 'proses_reject_simc']),
            new Middleware(PermissionMiddleware::using('simc-izin'), only: ['izin']),
            new Middleware(PermissionMiddleware::using('simc-approval-izin'), only: ['proses_tambah_izin', 'proses_approval_izin', 'destroy_izin', 'proses_reject_simc']),
            new Middleware(PermissionMiddleware::using('simc-cuti'), only: ['cuti']),
            new Middleware(PermissionMiddleware::using('simc-approval-cuti'), only: ['proses_tambah_cuti', 'proses_approval_cuti', 'destroy_cuti', 'proses_reject_simc']),
            new Middleware(PermissionMiddleware::using('simc-sisa-cuti'), only: ['sisa_cuti']),
            new Middleware(PermissionMiddleware::using('libur-nasional'), only: ['store_libur_nasional']),
        ];
    }

    public function getDataSimc($type)
    {
        $akses = Auth::user()->getProjectAccess();
        $query = Simc::with('karyawan:id,nip,nama,project,project_detail')
            ->with('user:id,name')
            ->whereHas('karyawan', function ($q) use ($akses) {
                $q->whereIn('project', $akses);
            })
            ->whereNull('deleted_at')
            ->where('type_simc', $type)
            ->orderBy('tanggal_dari', 'ASC')
            ->get();

        return $query;
    }

    public function getDataRecycle($type)
    {
        $akses = Auth::user()->getProjectAccess();
        $query = Simc::onlyTrashed()
            ->with('karyawan:id,nip,nama,project,project_detail')
            ->with('user:id,name')
            ->whereHas('karyawan', function ($q) use ($akses) {
                $q->whereIn('project', $akses);
            })
            ->where('type_simc', $type)
            ->orderBy('deleted_at', 'DESC')
            ->get();

        return $query;
    }

    public function index(Request $request)
    {
        $query = $this->getDataSimc('SAKIT');

        if ($request->ajax()) {

            return DataTables::of($query->where('status', '!=', 'PENDING')->toArray())
                ->addColumn('tanggal', function ($row) {
                    return Carbon::parse($row['tanggal_dari'])->translatedFormat('d-M-Y') . '<br>s/d<br>' . Carbon::parse($row['tanggal_sampai'])->translatedFormat('d-M-Y');
                })

                ->addColumn('penempatan', function ($row) {
                    $items = $row['karyawan']['lokasi_penempatan'] ?? [];
                    if (empty($items)) {
                        return '-';
                    }
                    $color = ['text-success', 'text-warning', 'text-primary', 'text-info', 'text-secondary', 'text-dark'];
                    $penempatan = array_map(
                        fn($item, $class) => "<span class='$class fw-bold d-flex'>$item</span>",
                        $items,
                        array_slice($color, 0, count($items))
                    );
                    return implode(' ', $penempatan);
                })

                ->addColumn('status', function ($row) {
                    $status = $row['status'] == 'APPROVED'
                        ? '<span class="badge bg-label-success">Disetujui</span>'
                        : '<span class="badge bg-label-danger">Ditolak</span>';

                    return $status . '<br><span style="font-size: 8px; color: #666;">' . ($row['tanggal_approve'] ?? '') . '</span><br>
                    <span style="font-size: 8px; color: #666;">' . ($row['user']['name'] ?? '') . '</span>'
                        . ($row['status'] == "REJECTED"
                            ? '<br> <span style="font-size: 9px; color: #EA5455;">' . ($row['alasan_ditolak'] ?? '') . '</span>'
                            : '');
                })
                ->addColumn('action', function ($row) {
                    $payload = [
                        'id'  => $row['id'],
                        'exp' => time() + 3600  // optional, expired 1 jam
                    ];

                    $token = JWT::encode($payload, config('jwt.secret'), 'HS256');

                    $button = ($row['status'] == "APPROVED"
                        ? '<div class="btn-group">
                            <button class="btn btn-sm btn-danger Hapus" data-token=' . $token . ' data-nama= "' . e($row['karyawan']['nama']) . '"><i class="fas fa-trash-alt me-1"></i>Hapus</button>
                        </div>'
                        : '<span style="font-size: 8px; color: #666;">' . ($row['tanggal_approve'] ?? '') . '</span>');

                    return $button;
                })

                ->rawColumns([
                    'penempatan' => 'penempatan',
                    'tanggal'    => 'tanggal',
                    'status'     => 'status',
                    'action'     => 'action',
                ])
                ->addIndexColumn()
                ->toJson();
        }

        $pending = $query->where('status', 'PENDING')->count();
        $deleted = $this->getDataRecycle('SAKIT')->count();

        return view('simc.sakit.data-sakit', [
            'judul' => 'Data Sakit'
        ], compact('pending', 'deleted'));
    }

    public function approval_sakit(Request $request)
    {
        if ($request->ajax()) {

            $query = $this->getDataSimc('SAKIT');
            return DataTables::of($query->where('status', 'PENDING')->toArray())
                ->addColumn('tanggal', function ($row) {
                    return Carbon::parse($row['tanggal_dari'])->translatedFormat('d-M-Y') . '<br>s/d<br>' . Carbon::parse($row['tanggal_sampai'])->translatedFormat('d-M-Y');
                })

                ->addColumn('penempatan', function ($row) {
                    $items = $row['karyawan']['lokasi_penempatan'] ?? [];
                    if (empty($items)) {
                        return '-';
                    }
                    $color = ['text-success', 'text-warning', 'text-primary', 'text-info', 'text-secondary', 'text-dark'];
                    $penempatan = array_map(
                        fn($item, $class) => "<span class='$class fw-bold d-flex'>$item</span>",
                        $items,
                        array_slice($color, 0, count($items))
                    );
                    return implode(' ', $penempatan);
                })

                ->addColumn('action', function ($row) {
                    $payload = [
                        'id'  => $row['id'],
                        'exp' => time() + 3600
                    ];

                    $token = JWT::encode($payload, config('jwt.secret'), 'HS256');
                    return '
                        <div class="btn-group">
                            <button class="btn btn-sm btn-info approval" data-token=' . $token . '><i class="fas fa-newspaper me-1"></i>Detail</button>
                            <button class="btn btn-sm btn-danger reject" data-token=' . $token . ' data-nama= "' . e($row['karyawan']['nama']) . '"><i class="fas fa-ban me-1"></i>Reject</button>
                        </div>
                    ';
                })

                ->rawColumns([
                    'penempatan' => 'penempatan',
                    'tanggal'    => 'tanggal',
                    'action'     => 'action'
                ])
                ->addIndexColumn()
                ->toJson();
        }
        return view('simc.sakit.approval-sakit', [
            'judul' => 'Approval Sakit'
        ]);
    }

    public function detail_approval_sakit(Request $request)
    {
        try {
            $token   = $request->query('token');
            $decoded = JWT::decode($token, new Key(config('jwt.secret'), 'HS256'));
            $id      = $decoded->id;
            $query = Simc::whereNull('deleted_at')
                ->with('karyawan:id,nip,nama,project,project_detail')
                ->where([
                    'id'        => $id,
                    'type_simc' => 'SAKIT',
                    'status'    => 'PENDING',
                ])
                ->firstOrFail()
                ->toArray();

            if ($request->ajax()) {
                return response()->json($query);
            }
        } catch (ExpiredException $e) {
            return back()->withErrors(['token_expired' => 'Oops! Token sudah kadaluarsa.']);
        }
    }

    public function proses_approval_sakit(Request $request)
    {
        $idsakit = $request->input('idsakit');
        $idkar   = $request->input('idkar');
        $dari    = $request->input('dari');
        $sampai  = $request->input('sampai');

        $check = Simc::whereNull('deleted_at')
            ->where('karyawan_id', $idkar)
            ->where(function ($q) use ($dari, $sampai) {
                $q->where('tanggal_dari', '<=', $sampai)
                    ->where('tanggal_sampai', '>=', $dari);
            })
            ->where('status', '!=', 'REJECTED')
            ->where('id', '!=', $idsakit)
            ->first();

        if ($check) {
            return response()->json([
                'message' => 'Anda sudah pernah melakukan pengajuan ' . $check->type_simc . ' pada tanggal tersebut dengan status saat ini ' . $check->status
            ], 409);
        }

        DB::transaction(function () use ($idsakit, $idkar, $dari, $sampai, $request) {
            $period = CarbonPeriod::create($dari, $sampai);
            foreach ($period as $date) {
                $tanggal   = $date->format('Y-m-d');
                $tgl       = date('j', strtotime($tanggal));
                $bulan     = date('n', strtotime($tanggal));
                $tahun     = date('Y', strtotime($tanggal));
                $namaKolom = 'tgl' . $tgl;

                $rekap = RekapKehadiran::firstOrCreate([
                    'karyawan_id' => $idkar,
                    'bulan'       => $bulan,
                    'tahun'       => $tahun,
                ]);

                $rekap->refresh();
                $nilaiSekarang = trim($rekap->$namaKolom);

                if ($nilaiSekarang === null || $nilaiSekarang === '' || $nilaiSekarang === 'L') {
                    $rekap->update([
                        $namaKolom => 'S'
                    ]);
                } else {
                    throw new \Exception("Pengajuan ditolak untuk tanggal {$tanggal}, sudah ada nilai : " . $nilaiSekarang);
                }
            }

            $model = Simc::findOrFail($idsakit);
            $model->update([
                'tanggal_dari'    => $dari,
                'tanggal_sampai'  => $sampai,
                'jumlah_hari'     => $request->hari,
                'status'          => 'APPROVED',
                'tanggal_approve' => now(),
                'approved_by'     => auth()->id(),
            ]);
        });

        return response()->json([
            'message' => 'Data Pengajuan Berhasil Diproses.'
        ], 200);
    }

    public function tambah_sakit(Request $request)
    {
        $akses = Auth::user()->getProjectAccess();
        $karyawans = Karyawan::select('id', 'nip', 'nama')
            ->whereNull('deleted_at')
            ->whereNull('tanggal_keluar')
            ->whereIn('project', $akses)
            ->orderBy('nama', 'ASC')
            ->get()
            ->map(function ($karyawan) {
                // Membuat token enkripsi baru khusus untuk ID
                $karyawan->token_id = encrypt($karyawan->id);
                return $karyawan;
            });

        return view('simc.sakit.tambah-sakit', [
            'judul' => 'Form Sakit'
        ], compact('karyawans'));
    }

    public function proses_tambah_sakit(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'karyawanid' => 'required',
                'dari'       => 'required|date',
                'sampai'     => 'required|date',
                'hari'       => 'required|integer',
                'keterangan' => 'required',
                'gambar'     => 'required|image|mimes:jpeg,jpg,png',
            ],
            [
                'keterangan.required' => 'Keterangan sakit masih kosong.',
                'gambar.required'     => 'Surat sakit masih kosong.'
            ]
        );

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->messages()->first()
            ], 409);
        } else {
            $idkar = decrypt($request->karyawanid);
            $dari = $request->input('dari');
            $sampai = $request->input('sampai');

            $check = Simc::whereNull('deleted_at')
                ->where('karyawan_id', $idkar)
                ->where(function ($q) use ($dari, $sampai) {
                    $q->where('tanggal_dari', '<=', $sampai)
                        ->where('tanggal_sampai', '>=', $dari);
                })
                ->where('status', '!=', 'REJECTED')
                ->first();
            if ($check) {
                return response()->json([
                    'message' => 'Anda sudah pernah melakukan pengajuan ' . $check->type_simc . ' pada tanggal tersebut dengan status saat ini ' . $check->status
                ], 409);
            }

            DB::transaction(function () use ($idkar, $dari, $sampai, $request) {
                $period = CarbonPeriod::create($dari, $sampai);
                foreach ($period as $date) {
                    ## Skip Sabtu/Minggu
                    // if ($date->isWeekend()) {
                    //     continue;
                    // }

                    # Skip Minggu saja
                    // if ($date->dayOfWeek === Carbon::SUNDAY) {
                    //     continue;
                    // }

                    # Skip hari libur nasional
                    // $isLibur = DB::table('hari_libur')
                    //     ->whereDate('tanggal', $date->format('Y-m-d'))
                    //     ->exists();

                    // if ($isLibur) {
                    //     continue;
                    // }

                    $tanggal   = $date->format('Y-m-d');
                    $tgl       = date('j', strtotime($tanggal));
                    $bulan     = date('n', strtotime($tanggal));
                    $tahun     = date('Y', strtotime($tanggal));
                    $namaKolom = 'tgl' . $tgl;

                    $rekap = RekapKehadiran::firstOrCreate([
                        'karyawan_id' => $idkar,
                        'bulan'       => $bulan,
                        'tahun'       => $tahun,
                    ]);

                    $rekap->refresh();
                    $nilaiSekarang = trim($rekap->$namaKolom);

                    if ($nilaiSekarang === null || $nilaiSekarang === '' || $nilaiSekarang === 'L') {
                        $rekap->update([
                            $namaKolom => 'S'
                        ]);
                    } else {
                        throw new \Exception("Sakit ditolak untuk tanggal {$tanggal}, sudah ada nilai : " . $nilaiSekarang);
                    }
                }

                $extension = $request->file('gambar')->getClientOriginalExtension();
                $filename = hash('sha256', Str::random(10) . now()->timestamp) . '.' . $extension;

                Storage::disk('public')->putFileAs('hris/simc/sakit/', $request->file('gambar'), $filename);

                Simc::create([
                    'type_simc'       => 'SAKIT',
                    'karyawan_id'     => $idkar,
                    'tanggal_dari'    => $dari,
                    'tanggal_sampai'  => $sampai,
                    'jumlah_hari'     => $request->hari,
                    'keterangan'      => trim($request->keterangan),
                    'status'          => 'APPROVED',
                    'tanggal_approve' => now(),
                    'approved_by'     => auth()->id(),
                    'lampiran'        => $filename,
                ]);
            });

            return response()->json([
                'message' => 'Data Sakit Berhasil Diproses.'
            ], 200);
        }
    }

    public function proses_reject_simc(Request $request)
    {
        try {
            $token   = $request->input('token');
            $decoded = JWT::decode($token, new Key(config('jwt.secret'), 'HS256'));
            $id      = $decoded->id;

            $model = Simc::findOrFail($id);
            $model->update([
                'status'          => 'REJECTED',
                'tanggal_approve' => now(),
                'approved_by'     => auth()->id(),
                'alasan_ditolak'  => trim($request->alasan),
            ]);

            return response()->json([
                'message' => 'Data Pengajuan Berhasil Ditolak.'
            ], 200);
        } catch (ExpiredException $e) {
            return back()->withErrors(['token_expired' => 'Oops! Token sudah kadaluarsa.']);
        }
    }

    public function destroy_sakit(Request $request)
    {
        try {
            $token   = $request->input('token');
            $decoded = JWT::decode($token, new Key(config('jwt.secret'), 'HS256'));
            $id      = $decoded->id;

            $data = Simc::findOrFail($id);
            $idkar   = $data->karyawan_id;
            $dari    = $data->tanggal_dari;
            $sampai  = $data->tanggal_sampai;

            DB::transaction(function () use ($request, $data, $idkar, $dari, $sampai) {
                $period = CarbonPeriod::create($dari, $sampai);
                foreach ($period as $date) {
                    $tanggal   = $date->format('Y-m-d');
                    $tgl       = date('j', strtotime($tanggal));
                    $bulan     = date('n', strtotime($tanggal));
                    $tahun     = date('Y', strtotime($tanggal));
                    $namaKolom = 'tgl' . $tgl;

                    $rekap = RekapKehadiran::firstOrCreate([
                        'karyawan_id' => $idkar,
                        'bulan'       => $bulan,
                        'tahun'       => $tahun,
                    ]);

                    $rekap->refresh();
                    $nilaiSekarang = trim($rekap->$namaKolom);

                    if ($nilaiSekarang === 'S') {
                        $rekap->update([
                            $namaKolom => null
                        ]);
                    }
                }

                $data->update([
                    'alasan_ditolak'  => 'Alasan dihapus : ' . trim($request->alasan) . ' (' . auth()->user()->name . ')',
                ]);
                $data->delete();
            });

            return response()->json([
                'message' => 'Data Pengajuan Berhasil Dihapus.'
            ], 200);
        } catch (ExpiredException $e) {
            return back()->withErrors(['token_expired' => 'Oops! Token sudah kadaluarsa.']);
        }
    }

    public function recycle_sakit(Request $request)
    {
        $akses = Auth::user()->getProjectAccess();
        $query = Simc::onlyTrashed()
            ->with('karyawan:id,nip,nama,project,project_detail')
            ->whereHas('karyawan', function ($q) use ($akses) {
                $q->whereIn('project', $akses);
            })
            ->where('type_simc', 'SAKIT')
            ->orderBy('deleted_at', 'ASC')
            ->get()
            ->toArray();

        if ($request->ajax()) {

            return DataTables::of($query)
                ->addColumn('tanggal', function ($row) {
                    return Carbon::parse($row['tanggal_dari'])->translatedFormat('d-M-Y') . '<br>s/d<br>' . Carbon::parse($row['tanggal_sampai'])->translatedFormat('d-M-Y');
                })

                ->addColumn('penempatan', function ($row) {
                    $items = $row['karyawan']['lokasi_penempatan'] ?? [];
                    if (empty($items)) {
                        return '-';
                    }
                    $color = ['text-success', 'text-warning', 'text-primary', 'text-info', 'text-secondary', 'text-dark'];
                    $penempatan = array_map(
                        fn($item, $class) => "<span class='$class fw-bold d-flex'>$item</span>",
                        $items,
                        array_slice($color, 0, count($items))
                    );
                    return implode(' ', $penempatan);
                })

                ->addColumn('status', function ($row) {
                    $status = $row['status'] == 'APPROVED'
                        ? '<span class="badge bg-label-success">Disetujui</span>'
                        : '<span class="badge bg-label-danger">Ditolak</span>';

                    return $status . '<br><span style="font-size: 8px; color: #666;">' . ($row['tanggal_approve'] ?? '') . '</span><br>
                    <span style="font-size: 8px; color: #666;">' . ($row['user']['name'] ?? '') . '</span>'
                        . ($row['status'] == "REJECTED"
                            ? '<br> <span style="font-size: 9px; color: #EA5455;">' . ($row['alasan_ditolak'] ?? '') . '</span>'
                            : '');
                })

                ->addColumn('alasan', function ($row) {
                    return '<span style="font-size: 9px; color: #666;">' . Carbon::parse($row['deleted_at'])->translatedFormat('d-m-Y H:i:s') . '</span><br>
                    <span style="font-size: 10px;">' . $row['alasan_ditolak'] . '</span>';
                })


                ->rawColumns([
                    'penempatan' => 'penempatan',
                    'tanggal'    => 'tanggal',
                    'status'     => 'status',
                    'alasan'     => 'alasan',
                ])
                ->addIndexColumn()
                ->toJson();
        }
        return view('simc.sakit.recycle-sakit', [
            'judul' => 'Data Sakit Dihapus'
        ]);
    }

    ## =========================================================================================================================================== IZIN
    public function izin(Request $request)
    {
        $query = $this->getDataSimc('IZIN');

        if ($request->ajax()) {

            return DataTables::of($query->where('status', '!=', 'PENDING')->toArray())
                ->addColumn('tanggal', function ($row) {
                    return Carbon::parse($row['tanggal_dari'])->translatedFormat('d-M-Y') . '<br>s/d<br>' . Carbon::parse($row['tanggal_sampai'])->translatedFormat('d-M-Y');
                })

                ->addColumn('penempatan', function ($row) {
                    $items = $row['karyawan']['lokasi_penempatan'] ?? [];
                    if (empty($items)) {
                        return '-';
                    }
                    $color = ['text-success', 'text-warning', 'text-primary', 'text-info', 'text-secondary', 'text-dark'];
                    $penempatan = array_map(
                        fn($item, $class) => "<span class='$class fw-bold d-flex'>$item</span>",
                        $items,
                        array_slice($color, 0, count($items))
                    );
                    return implode(' ', $penempatan);
                })

                ->addColumn('status', function ($row) {
                    $status = $row['status'] == 'APPROVED'
                        ? '<span class="badge bg-label-success">Disetujui</span>'
                        : '<span class="badge bg-label-danger">Ditolak</span>';

                    return $status . '<br><span style="font-size: 8px; color: #666;">' . ($row['tanggal_approve'] ?? '') . '</span><br>
                    <span style="font-size: 8px; color: #666;">' . ($row['user']['name'] ?? '') . '</span>'
                        . ($row['status'] == "REJECTED"
                            ? '<br> <span style="font-size: 9px; color: #EA5455;">' . ($row['alasan_ditolak'] ?? '') . '</span>'
                            : '');
                })

                ->addColumn('action', function ($row) {
                    $payload = [
                        'id'  => $row['id'],
                        'exp' => time() + 3600  // optional, expired 1 jam
                    ];

                    $token = JWT::encode($payload, config('jwt.secret'), 'HS256');

                    $button = ($row['status'] == "APPROVED"
                        ? '<div class="btn-group">
                            <button class="btn btn-sm btn-danger Hapus" data-token=' . $token . ' data-nama= "' . e($row['karyawan']['nama']) . '"><i class="fas fa-trash-alt me-1"></i>Hapus</button>
                        </div>'
                        : '<span style="font-size: 8px; color: #666;">' . ($row['tanggal_approve'] ?? '') . '</span>');

                    return $button;
                })

                ->rawColumns([
                    'penempatan' => 'penempatan',
                    'tanggal'    => 'tanggal',
                    'status'     => 'status',
                    'action'     => 'action',
                ])
                ->addIndexColumn()
                ->toJson();
        }

        $pending = $query->where('status', 'PENDING')->count();
        $deleted = $this->getDataRecycle('IZIN')->count();

        return view('simc.izin.data-izin', [
            'judul' => 'Data Izin'
        ], compact('pending', 'deleted'));
    }

    public function tambah_izin(Request $request)
    {
        $akses = Auth::user()->getProjectAccess();
        $karyawans = Karyawan::select('id', 'nip', 'nama')
            ->whereNull('deleted_at')
            ->whereNull('tanggal_keluar')
            ->whereIn('project', $akses)
            ->orderBy('nama', 'ASC')
            ->get()
            ->map(function ($karyawan) {
                // Membuat token enkripsi baru khusus untuk ID
                $karyawan->token_id = encrypt($karyawan->id);
                return $karyawan;
            });

        return view('simc.izin.tambah-izin', [
            'judul' => 'Form Izin'
        ], compact('karyawans'));
    }

    public function proses_tambah_izin(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'karyawanid' => 'required',
                'dari'       => 'required|date',
                'sampai'     => 'required|date',
                'hari'       => 'required|integer',
                'keterangan' => 'required',
            ],
            [
                'keterangan.required' => 'Keterangan sakit masih kosong.',
            ]
        );

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->messages()->first()
            ], 409);
        } else {
            $idkar = decrypt($request->karyawanid);
            $dari = $request->input('dari');
            $sampai = $request->input('sampai');

            $check = Simc::whereNull('deleted_at')
                ->where('karyawan_id', $idkar)
                ->where(function ($q) use ($dari, $sampai) {
                    $q->where('tanggal_dari', '<=', $sampai)
                        ->where('tanggal_sampai', '>=', $dari);
                })
                ->where('status', '!=', 'REJECTED')
                ->first();
            if ($check) {
                return response()->json([
                    'message' => 'Anda sudah pernah melakukan pengajuan ' . $check->type_simc . ' pada tanggal tersebut dengan status saat ini ' . $check->status
                ], 409);
            }

            DB::transaction(function () use ($idkar, $dari, $sampai, $request) {
                $period = CarbonPeriod::create($dari, $sampai);
                foreach ($period as $date) {
                    $tanggal   = $date->format('Y-m-d');
                    $tgl       = date('j', strtotime($tanggal));
                    $bulan     = date('n', strtotime($tanggal));
                    $tahun     = date('Y', strtotime($tanggal));
                    $namaKolom = 'tgl' . $tgl;

                    $rekap = RekapKehadiran::firstOrCreate([
                        'karyawan_id' => $idkar,
                        'bulan'       => $bulan,
                        'tahun'       => $tahun,
                    ]);

                    $rekap->refresh();
                    $nilaiSekarang = trim($rekap->$namaKolom);

                    if ($nilaiSekarang === null || $nilaiSekarang === '' || $nilaiSekarang === 'L') {
                        $rekap->update([
                            $namaKolom => 'I'
                        ]);
                    } else {
                        throw new \Exception("Izin ditolak untuk tanggal {$tanggal}, sudah ada nilai : " . $nilaiSekarang);
                    }
                }

                Simc::create([
                    'type_simc'       => 'IZIN',
                    'karyawan_id'     => $idkar,
                    'tanggal_dari'    => $dari,
                    'tanggal_sampai'  => $sampai,
                    'jumlah_hari'     => $request->hari,
                    'keterangan'      => trim($request->keterangan),
                    'status'          => 'APPROVED',
                    'tanggal_approve' => now(),
                    'approved_by'     => auth()->id(),
                ]);
            });

            return response()->json([
                'message' => 'Data Izin Berhasil Diproses.'
            ], 200);
        }
    }

    public function approval_izin(Request $request)
    {
        if ($request->ajax()) {

            $query = $this->getDataSimc('IZIN');
            return DataTables::of($query->where('status', 'PENDING')->toArray())
                ->addColumn('tanggal', function ($row) {
                    return Carbon::parse($row['tanggal_dari'])->translatedFormat('d-M-Y') . '<br>s/d<br>' . Carbon::parse($row['tanggal_sampai'])->translatedFormat('d-M-Y');
                })

                ->addColumn('penempatan', function ($row) {
                    $items = $row['karyawan']['lokasi_penempatan'] ?? [];
                    if (empty($items)) {
                        return '-';
                    }
                    $color = ['text-success', 'text-warning', 'text-primary', 'text-info', 'text-secondary', 'text-dark'];
                    $penempatan = array_map(
                        fn($item, $class) => "<span class='$class fw-bold d-flex'>$item</span>",
                        $items,
                        array_slice($color, 0, count($items))
                    );
                    return implode(' ', $penempatan);
                })

                ->addColumn('action', function ($row) {
                    $payload = [
                        'id'  => $row['id'],
                        'exp' => time() + 3600
                    ];

                    $token = JWT::encode($payload, config('jwt.secret'), 'HS256');
                    return '
                        <div class="btn-group">
                            <button class="btn btn-sm btn-info approval" data-token=' . $token . '><i class="fas fa-newspaper me-1"></i>Detail</button>
                            <button class="btn btn-sm btn-danger reject" data-token=' . $token . ' data-nama= "' . e($row['karyawan']['nama']) . '"><i class="fas fa-ban me-1"></i>Reject</button>
                        </div>
                    ';
                })

                ->rawColumns([
                    'penempatan' => 'penempatan',
                    'tanggal'    => 'tanggal',
                    'action'     => 'action'
                ])
                ->addIndexColumn()
                ->toJson();
        }
        return view('simc.izin.approval-izin', [
            'judul' => 'Approval Izin'
        ]);
    }

    public function detail_approval_izin(Request $request)
    {
        try {
            $token   = $request->query('token');
            $decoded = JWT::decode($token, new Key(config('jwt.secret'), 'HS256'));
            $id      = $decoded->id;
            $query = Simc::whereNull('deleted_at')
                ->with('karyawan:id,nip,nama,project,project_detail')
                ->where([
                    'id'        => $id,
                    'type_simc' => 'IZIN',
                    'status'    => 'PENDING',
                ])
                ->firstOrFail()
                ->toArray();

            if ($request->ajax()) {
                return response()->json($query);
            }
        } catch (ExpiredException $e) {
            return back()->withErrors(['token_expired' => 'Oops! Token sudah kadaluarsa.']);
        }
    }

    public function proses_approval_izin(Request $request)
    {
        $idizin = $request->input('idizin');
        $idkar   = $request->input('idkar');
        $dari    = $request->input('dari');
        $sampai  = $request->input('sampai');

        $check = Simc::whereNull('deleted_at')
            ->where('karyawan_id', $idkar)
            ->where(function ($q) use ($dari, $sampai) {
                $q->where('tanggal_dari', '<=', $sampai)
                    ->where('tanggal_sampai', '>=', $dari);
            })
            ->where('status', '!=', 'REJECTED')
            ->where('id', '!=', $idizin)
            ->first();

        if ($check) {
            return response()->json([
                'message' => 'Anda sudah pernah melakukan pengajuan ' . $check->type_simc . ' pada tanggal tersebut dengan status saat ini ' . $check->status
            ], 409);
        }

        DB::transaction(function () use ($idizin, $idkar, $dari, $sampai, $request) {
            $period = CarbonPeriod::create($dari, $sampai);
            foreach ($period as $date) {
                $tanggal   = $date->format('Y-m-d');
                $tgl       = date('j', strtotime($tanggal));
                $bulan     = date('n', strtotime($tanggal));
                $tahun     = date('Y', strtotime($tanggal));
                $namaKolom = 'tgl' . $tgl;

                $rekap = RekapKehadiran::firstOrCreate([
                    'karyawan_id' => $idkar,
                    'bulan'       => $bulan,
                    'tahun'       => $tahun,
                ]);

                $rekap->refresh();
                $nilaiSekarang = trim($rekap->$namaKolom);

                if ($nilaiSekarang === null || $nilaiSekarang === '' || $nilaiSekarang === 'L') {
                    $rekap->update([
                        $namaKolom => 'I'
                    ]);
                } else {
                    throw new \Exception("Pengajuan ditolak untuk tanggal {$tanggal}, sudah ada nilai : " . $nilaiSekarang);
                }
            }

            $model = Simc::findOrFail($idizin);
            $model->update([
                'tanggal_dari'    => $dari,
                'tanggal_sampai'  => $sampai,
                'jumlah_hari'     => $request->hari,
                'status'          => 'APPROVED',
                'tanggal_approve' => now(),
                'approved_by'     => auth()->id(),
            ]);
        });

        return response()->json([
            'message' => 'Data Pengajuan Berhasil Diproses.'
        ], 200);
    }

    public function destroy_izin(Request $request)
    {
        try {
            $token   = $request->input('token');
            $decoded = JWT::decode($token, new Key(config('jwt.secret'), 'HS256'));
            $id      = $decoded->id;

            $data = Simc::findOrFail($id);
            $idkar   = $data->karyawan_id;
            $dari    = $data->tanggal_dari;
            $sampai  = $data->tanggal_sampai;

            DB::transaction(function () use ($request, $data, $idkar, $dari, $sampai) {
                $period = CarbonPeriod::create($dari, $sampai);
                foreach ($period as $date) {
                    $tanggal   = $date->format('Y-m-d');
                    $tgl       = date('j', strtotime($tanggal));
                    $bulan     = date('n', strtotime($tanggal));
                    $tahun     = date('Y', strtotime($tanggal));
                    $namaKolom = 'tgl' . $tgl;

                    $rekap = RekapKehadiran::firstOrCreate([
                        'karyawan_id' => $idkar,
                        'bulan'       => $bulan,
                        'tahun'       => $tahun,
                    ]);

                    $rekap->refresh();
                    $nilaiSekarang = trim($rekap->$namaKolom);

                    if ($nilaiSekarang === 'I') {
                        $rekap->update([
                            $namaKolom => null
                        ]);
                    }
                }

                $data->update([
                    'alasan_ditolak'  => 'Alasan dihapus : ' . trim($request->alasan) . ' (' . auth()->user()->name . ')',
                ]);
                $data->delete();
            });

            return response()->json([
                'message' => 'Data Pengajuan Berhasil Dihapus.'
            ], 200);
        } catch (ExpiredException $e) {
            return back()->withErrors(['token_expired' => 'Oops! Token sudah kadaluarsa.']);
        }
    }

    public function recycle_izin(Request $request)
    {
        $akses = Auth::user()->getProjectAccess();
        $query = Simc::onlyTrashed()
            ->with('karyawan:id,nip,nama,project,project_detail')
            ->whereHas('karyawan', function ($q) use ($akses) {
                $q->whereIn('project', $akses);
            })
            ->where('type_simc', 'IZIN')
            ->orderBy('deleted_at', 'ASC')
            ->get()
            ->toArray();

        if ($request->ajax()) {

            return DataTables::of($query)
                ->addColumn('tanggal', function ($row) {
                    return Carbon::parse($row['tanggal_dari'])->translatedFormat('d-M-Y') . '<br>s/d<br>' . Carbon::parse($row['tanggal_sampai'])->translatedFormat('d-M-Y');
                })

                ->addColumn('penempatan', function ($row) {
                    $items = $row['karyawan']['lokasi_penempatan'] ?? [];
                    if (empty($items)) {
                        return '-';
                    }
                    $color = ['text-success', 'text-warning', 'text-primary', 'text-info', 'text-secondary', 'text-dark'];
                    $penempatan = array_map(
                        fn($item, $class) => "<span class='$class fw-bold d-flex'>$item</span>",
                        $items,
                        array_slice($color, 0, count($items))
                    );
                    return implode(' ', $penempatan);
                })

                ->addColumn('status', function ($row) {
                    $status = $row['status'] == 'APPROVED'
                        ? '<span class="badge bg-label-success">Disetujui</span>'
                        : '<span class="badge bg-label-danger">Ditolak</span>';

                    return $status . '<br><span style="font-size: 8px; color: #666;">' . ($row['tanggal_approve'] ?? '') . '</span><br>
                    <span style="font-size: 8px; color: #666;">' . ($row['user']['name'] ?? '') . '</span>'
                        . ($row['status'] == "REJECTED"
                            ? '<br> <span style="font-size: 9px; color: #EA5455;">' . ($row['alasan_ditolak'] ?? '') . '</span>'
                            : '');
                })

                ->addColumn('alasan', function ($row) {
                    return '<span style="font-size: 9px; color: #666;">' . Carbon::parse($row['deleted_at'])->translatedFormat('d-m-Y H:i:s') . '</span><br>
                    <span style="font-size: 10px;">' . $row['alasan_ditolak'] . '</span>';
                })


                ->rawColumns([
                    'penempatan' => 'penempatan',
                    'tanggal'    => 'tanggal',
                    'status'     => 'status',
                    'alasan'     => 'alasan',
                ])
                ->addIndexColumn()
                ->toJson();
        }
        return view('simc.izin.recycle-izin', [
            'judul' => 'Data Izin Dihapus'
        ]);
    }

    ## =========================================================================================================================================== CUTI
    public function cuti(Request $request)
    {
        $query = $this->getDataSimc('CUTI');

        if ($request->ajax()) {

            return DataTables::of($query->where('status', '!=', 'PENDING')->toArray())
                ->addColumn('tanggal', function ($row) {
                    return Carbon::parse($row['tanggal_dari'])->translatedFormat('d-M-Y') . '<br>s/d<br>' . Carbon::parse($row['tanggal_sampai'])->translatedFormat('d-M-Y');
                })

                ->addColumn('penempatan', function ($row) {
                    $items = $row['karyawan']['lokasi_penempatan'] ?? [];
                    if (empty($items)) {
                        return '-';
                    }
                    $color = ['text-success', 'text-warning', 'text-primary', 'text-info', 'text-secondary', 'text-dark'];
                    $penempatan = array_map(
                        fn($item, $class) => "<span class='$class fw-bold d-flex'>$item</span>",
                        $items,
                        array_slice($color, 0, count($items))
                    );
                    return implode(' ', $penempatan);
                })

                ->addColumn('status', function ($row) {
                    $status = $row['status'] == 'APPROVED'
                        ? '<span class="badge bg-label-success">Disetujui</span>'
                        : '<span class="badge bg-label-danger">Ditolak</span>';

                    return $status . '<br><span style="font-size: 8px; color: #666;">' . ($row['tanggal_approve'] ?? '') . '</span><br>
                    <span style="font-size: 8px; color: #666;">' . ($row['user']['name'] ?? '') . '</span>'
                        . ($row['status'] == "REJECTED"
                            ? '<br> <span style="font-size: 9px; color: #EA5455;">' . ($row['alasan_ditolak'] ?? '') . '</span>'
                            : '');
                })

                ->addColumn('action', function ($row) {
                    $payload = [
                        'id'  => $row['id'],
                        'exp' => time() + 3600  // optional, expired 1 jam
                    ];

                    $token = JWT::encode($payload, config('jwt.secret'), 'HS256');

                    $button = ($row['status'] == "APPROVED"
                        ? '<div class="btn-group">
                            <button class="btn btn-sm btn-danger Hapus" data-token=' . $token . ' data-nama= "' . e($row['karyawan']['nama']) . '"><i class="fas fa-trash-alt me-1"></i>Hapus</button>
                        </div>'
                        : '<span style="font-size: 8px; color: #666;">' . ($row['tanggal_approve'] ?? '') . '</span>');

                    return $button;
                })

                ->rawColumns([
                    'penempatan' => 'penempatan',
                    'tanggal'    => 'tanggal',
                    'status'     => 'status',
                    'action'     => 'action',
                ])
                ->addIndexColumn()
                ->toJson();
        }

        $pending = $query->where('status', 'PENDING')->count();
        $deleted = $this->getDataRecycle('CUTI')->count();

        return view('simc.cuti.data-cuti', [
            'judul' => 'Data Cuti'
        ], compact('pending', 'deleted'));
    }

    public function tambah_cuti(Request $request)
    {
        $akses = Auth::user()->getProjectAccess();
        $karyawans = Karyawan::select('id', 'nip', 'nama')
            ->whereNull('deleted_at')
            ->whereNull('tanggal_keluar')
            ->whereIn('project', $akses)
            ->orderBy('nama', 'ASC')
            ->get()
            ->map(function ($karyawan) {
                // Membuat token enkripsi baru khusus untuk ID
                $karyawan->token_id = encrypt($karyawan->id);
                return $karyawan;
            });

        if ($request->ajax()) {
            $today = now();
            $idkar = decrypt($request->token);
            $saldoCuti = SimcSaldo::whereNull('deleted_at')
                ->with('karyawan:id,nip,nama,project_detail,tanggal_masuk')
                ->where('karyawan_id', $idkar)
                ->where(function ($q) use ($today) {
                    $q->whereNull('saldo_expired') // quota normal tanpa expiry
                        ->orWhere('saldo_expired', '>=', $today); // carry over yang belum expired
                })
                ->orderBy('tahun', 'desc')
                ->get();

            $totalSaldo = $saldoCuti->sum(function ($balance) {
                return $balance->remaining; // accessor yang sudah kamu buat
            });

            return response()->json([
                'sisaCuti'   => $totalSaldo,
                'nama'       => $saldoCuti->first()->karyawan->nama,
                'penempatan' => $saldoCuti->first()->karyawan->lokasi_penempatan,
            ]);
        }

        return view('simc.cuti.tambah-cuti', [
            'judul' => 'Form Cuti'
        ], compact('karyawans'));
    }

    public function proses_tambah_cuti(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'karyawanid' => 'required',
                'dari'       => 'required|date',
                'sampai'     => 'required|date',
                'hari'       => 'required|integer',
                'keterangan' => 'required',
                'gambar'     => 'required|image|mimes:jpeg,jpg,png',
            ],
            [
                'keterangan.required' => 'Keterangan masih kosong.',
                'gambar.required'     => 'Lampiran masih kosong.'
            ]
        );

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->messages()->first()
            ], 409);
        } else {
            $idkar = decrypt($request->karyawanid);
            $dari = $request->input('dari');
            $sampai = $request->input('sampai');
            $kategori = strtoupper($request->kategori);

            $check = Simc::whereNull('deleted_at')
                ->where('karyawan_id', $idkar)
                ->where(function ($q) use ($dari, $sampai) {
                    $q->where('tanggal_dari', '<=', $sampai)
                        ->where('tanggal_sampai', '>=', $dari);
                })
                ->where('status', '!=', 'REJECTED')
                ->first();
            if ($check) {
                return response()->json([
                    'message' => 'Anda sudah pernah melakukan pengajuan ' . $check->type_simc . ' pada tanggal tersebut dengan status saat ini ' . $check->status
                ], 409);
            }

            $today = now();

            if ($kategori === 'TAHUNAN') {
                $saldoCuti = SimcSaldo::whereNull('deleted_at')
                    ->with('karyawan:id,nip,nama,project_detail')
                    ->where('karyawan_id', $idkar)
                    ->where(function ($q) use ($today) {
                        $q->whereNull('saldo_expired') // quota normal tanpa expiry
                            ->orWhere('saldo_expired', '>=', $today); // carry over yang belum expired
                    })
                    ->orderBy('tahun', 'asc')
                    ->get();

                $totalRemaining = $saldoCuti->sum('remaining');
                if ($totalRemaining < $request->hari) {
                    throw new \Exception("Cuti ditolak, saldo tidak mencukupi. Sisa: {$totalRemaining}, diminta: {$request->hari}");
                }
            } else {
                $saldoCuti = 90;
                if ($saldoCuti < $request->hari) {
                    throw new \Exception("Cuti ditolak, saldo tidak mencukupi. Sisa: {$saldoCuti}, diminta: {$request->hari}");
                }
            }

            DB::transaction(function () use ($idkar, $dari, $sampai, $request, $saldoCuti, $kategori) {
                ## Simpan dulu untuk ambil ID nya
                $extension = $request->file('gambar')->getClientOriginalExtension();
                $filename = hash('sha256', Str::random(10) . now()->timestamp) . '.' . $extension;

                Storage::disk('public')->putFileAs('hris/simc/cuti/', $request->file('gambar'), $filename);

                $cuti = Simc::create([
                    'kategori'        => $request->kategori,
                    'type_simc'       => 'CUTI',
                    'karyawan_id'     => $idkar,
                    'tanggal_dari'    => $dari,
                    'tanggal_sampai'  => $sampai,
                    'tanggal_masuk'   => $request->tglmasuk,
                    'jumlah_hari'     => $request->hari,
                    'keterangan'      => trim($request->keterangan),
                    'status'          => 'APPROVED',
                    'tanggal_approve' => now(),
                    'approved_by'     => auth()->id(),
                    'lampiran'        => $filename,
                ]);

                ## Looping Tanggal Cuti
                $period = CarbonPeriod::create($dari, $sampai);
                foreach ($period as $date) {

                    $tanggal   = $date->format('Y-m-d');
                    $tgl       = date('j', strtotime($tanggal));
                    $bulan     = date('n', strtotime($tanggal));
                    $tahun     = date('Y', strtotime($tanggal));
                    $namaKolom = 'tgl' . $tgl;

                    ## Create Detail dan alokasi saldo
                    if ($kategori === 'TAHUNAN') {
                        foreach ($saldoCuti as $saldo) {
                            if ($saldo->remaining > 0) {
                                SimcDetail::create([
                                    'simc_id'           => $cuti->id,
                                    'tahun_saldo'       => $saldo->tahun,
                                    'tanggal_pengajuan' => $tanggal,
                                    'source_type'       => $saldo->saldo_sebelumnya > 0 ? 'saldo_sebelumnya' : 'saldo_berjalan',
                                ]);

                                // 4. Update saldo di DB
                                $saldo->saldo_terpakai += 1;
                                $saldo->save();

                                break; // keluar loop saldo, lanjut ke tanggal berikutnya
                            }
                        }
                    }

                    # Cek Rekap Kehadiran
                    $rekap = RekapKehadiran::firstOrCreate([
                        'karyawan_id' => $idkar,
                        'bulan'       => $bulan,
                        'tahun'       => $tahun,
                    ]);

                    $rekap->refresh();
                    $nilaiSekarang = trim($rekap->$namaKolom);

                    if ($nilaiSekarang === null || $nilaiSekarang === '' || $nilaiSekarang === 'L') {
                        $rekap->update([
                            $namaKolom => 'C'
                        ]);
                    } else {
                        throw new \Exception("Cuti ditolak untuk tanggal {$tanggal}, sudah ada nilai : " . $nilaiSekarang);
                    }
                }
            });

            return response()->json([
                'message' => 'Data Cuti Berhasil Diproses.'
            ], 200);
        }
    }

    public function destroy_cuti(Request $request)
    {
        try {
            $token   = $request->input('token');
            $decoded = JWT::decode($token, new Key(config('jwt.secret'), 'HS256'));
            $id      = $decoded->id;

            $data = Simc::findOrFail($id);
            $idkar   = $data->karyawan_id;
            $dari    = $data->tanggal_dari;
            $sampai  = $data->tanggal_sampai;

            DB::transaction(function () use ($id, $request, $data, $idkar, $dari, $sampai) {
                $period = CarbonPeriod::create($dari, $sampai);
                foreach ($period as $date) {
                    $tanggal   = $date->format('Y-m-d');
                    $tgl       = date('j', strtotime($tanggal));
                    $bulan     = date('n', strtotime($tanggal));
                    $tahun     = date('Y', strtotime($tanggal));
                    $namaKolom = 'tgl' . $tgl;

                    $rekap = RekapKehadiran::firstOrCreate([
                        'karyawan_id' => $idkar,
                        'bulan'       => $bulan,
                        'tahun'       => $tahun,
                    ]);

                    $rekap->refresh();
                    $nilaiSekarang = trim($rekap->$namaKolom);

                    if ($nilaiSekarang === 'C') {
                        $rekap->update([
                            $namaKolom => null
                        ]);
                    }
                }


                if ($data->kategori == "TAHUNAN") {
                    ## hapus detail
                    SimcDetail::where('simc_id', $id)->delete();
                    ## ambil saldo tahun berjalan
                    $saldo = SimcSaldo::whereNull('deleted_at')
                        ->where([
                            'karyawan_id' => $idkar,
                            'tahun' => date('Y', strtotime($dari)),
                        ])->first();
                    if ($saldo) {
                        ## tambahkan kembali saldo sesuai jumlah hari cuti yang dipakai
                        $saldo->update([
                            'saldo_terpakai' => $saldo->saldo_terpakai - $data->jumlah_hari
                        ]);
                    }
                }

                $data->update([
                    'alasan_ditolak'  => 'Alasan dihapus : ' . trim($request->alasan) . ' (' . auth()->user()->name . ')',
                ]);
                $data->delete();
            });

            return response()->json([
                'message' => 'Data Pengajuan Berhasil Dihapus.'
            ], 200);
        } catch (ExpiredException $e) {
            return back()->withErrors(['token_expired' => 'Oops! Token sudah kadaluarsa.']);
        }
    }

    public function recycle_cuti(Request $request)
    {
        $akses = Auth::user()->getProjectAccess();
        $query = Simc::onlyTrashed()
            ->with('karyawan:id,nip,nama,project,project_detail')
            ->whereHas('karyawan', function ($q) use ($akses) {
                $q->whereIn('project', $akses);
            })
            ->where('type_simc', 'CUTI')
            ->orderBy('deleted_at', 'ASC')
            ->get()
            ->toArray();

        if ($request->ajax()) {

            return DataTables::of($query)
                ->addColumn('tanggal', function ($row) {
                    return Carbon::parse($row['tanggal_dari'])->translatedFormat('d-M-Y') . '<br>s/d<br>' . Carbon::parse($row['tanggal_sampai'])->translatedFormat('d-M-Y');
                })

                ->addColumn('penempatan', function ($row) {
                    $items = $row['karyawan']['lokasi_penempatan'] ?? [];
                    if (empty($items)) {
                        return '-';
                    }
                    $color = ['text-success', 'text-warning', 'text-primary', 'text-info', 'text-secondary', 'text-dark'];
                    $penempatan = array_map(
                        fn($item, $class) => "<span class='$class fw-bold d-flex'>$item</span>",
                        $items,
                        array_slice($color, 0, count($items))
                    );
                    return implode(' ', $penempatan);
                })

                ->addColumn('status', function ($row) {
                    $status = $row['status'] == 'APPROVED'
                        ? '<span class="badge bg-label-success">Disetujui</span>'
                        : '<span class="badge bg-label-danger">Ditolak</span>';

                    return $status . '<br><span style="font-size: 8px; color: #666;">' . ($row['tanggal_approve'] ?? '') . '</span><br>
                    <span style="font-size: 8px; color: #666;">' . ($row['user']['name'] ?? '') . '</span>'
                        . ($row['status'] == "REJECTED"
                            ? '<br> <span style="font-size: 9px; color: #EA5455;">' . ($row['alasan_ditolak'] ?? '') . '</span>'
                            : '');
                })

                ->addColumn('alasan', function ($row) {
                    return '<span style="font-size: 9px; color: #666;">' . Carbon::parse($row['deleted_at'])->translatedFormat('d-m-Y H:i:s') . '</span><br>
                    <span style="font-size: 10px;">' . $row['alasan_ditolak'] . '</span>';
                })


                ->rawColumns([
                    'penempatan' => 'penempatan',
                    'tanggal'    => 'tanggal',
                    'status'     => 'status',
                    'alasan'     => 'alasan',
                ])
                ->addIndexColumn()
                ->toJson();
        }
        return view('simc.cuti.recycle-cuti', [
            'judul' => 'Data Cuti Dihapus'
        ]);
    }

    public function approval_cuti(Request $request)
    {
        if ($request->ajax()) {

            $query = $this->getDataSimc('CUTI');
            return DataTables::of($query->where('status', 'PENDING')->toArray())
                ->addColumn('tanggal', function ($row) {
                    return Carbon::parse($row['tanggal_dari'])->translatedFormat('d-M-Y') . '<br>s/d<br>' . Carbon::parse($row['tanggal_sampai'])->translatedFormat('d-M-Y');
                })

                ->addColumn('penempatan', function ($row) {
                    $items = $row['karyawan']['lokasi_penempatan'] ?? [];
                    if (empty($items)) {
                        return '-';
                    }
                    $color = ['text-success', 'text-warning', 'text-primary', 'text-info', 'text-secondary', 'text-dark'];
                    $penempatan = array_map(
                        fn($item, $class) => "<span class='$class fw-bold d-flex'>$item</span>",
                        $items,
                        array_slice($color, 0, count($items))
                    );
                    return implode(' ', $penempatan);
                })

                ->addColumn('action', function ($row) {
                    $payload = [
                        'id'  => $row['id'],
                        'exp' => time() + 3600
                    ];

                    $token = JWT::encode($payload, config('jwt.secret'), 'HS256');
                    return '
                        <div class="btn-group">
                            <button class="btn btn-sm btn-info detailApproval" data-token=' . $token . '><i class="fas fa-newspaper me-1"></i>Detail</button>
                            <button class="btn btn-sm btn-danger reject" data-token=' . $token . ' data-nama= "' . e($row['karyawan']['nama']) . '"><i class="fas fa-ban me-1"></i>Reject</button>
                        </div>
                    ';
                })

                ->rawColumns([
                    'penempatan' => 'penempatan',
                    'tanggal'    => 'tanggal',
                    'action'     => 'action'
                ])
                ->addIndexColumn()
                ->toJson();
        }
        return view('simc.cuti.approval-cuti', [
            'judul' => 'Approval Cuti'
        ]);
    }

    public function detail_approval_cuti(Request $request)
    {
        try {
            $token   = $request->query('token');
            $decoded = JWT::decode($token, new Key(config('jwt.secret'), 'HS256'));
            $id      = $decoded->id;
            $today = now();

            $query = Simc::whereNull('deleted_at')
                ->with('karyawan:id,nip,nama,project,project_detail')
                ->with(['karyawan.saldoCuti' => function ($q) use ($today) {
                    $q->whereNull('deleted_at')
                        ->where(function ($q) use ($today) {
                            $q->whereNull('saldo_expired')
                                ->orWhere('saldo_expired', '>=', $today);
                        })
                        ->orderBy('tahun', 'desc');
                }])
                ->where([
                    'id'        => $id,
                    'type_simc' => 'CUTI',
                    'status'    => 'PENDING',
                ])
                ->firstOrFail()
                ->toArray();

            $total = collect($query['karyawan']['saldo_cuti'])->sum('remaining');

            if ($request->ajax()) {
                return response()->json([
                    'datas'     => $query,
                    'sisaCuti' => $total,
                ]);
            }
        } catch (ExpiredException $e) {
            return back()->withErrors(['token_expired' => 'Oops! Token sudah kadaluarsa.']);
        }
    }

    public function proses_approval_cuti(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'idcuti'   => 'required',
                'idkar'    => 'required',
                'kategori' => 'required',
                'dari'     => 'required|date',
                'sampai'   => 'required|date',
                'hari'     => 'required|integer',
            ],
            [
                'hari.required' => 'Jumlah Hari masih kosong.',
            ]
        );

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->messages()->first()
            ], 409);
        } else {
            $idcuti = $request->input('idcuti');
            $idkar = $request->input('idkar');
            $dari = $request->input('dari');
            $sampai = $request->input('sampai');
            $kategori = strtoupper($request->kategori);

            $check = Simc::whereNull('deleted_at')
                ->where('karyawan_id', $idkar)
                ->where(function ($q) use ($dari, $sampai) {
                    $q->where('tanggal_dari', '<=', $sampai)
                        ->where('tanggal_sampai', '>=', $dari);
                })
                ->where('status', '!=', 'REJECTED')
                ->first();
            if ($check) {
                return response()->json([
                    'message' => 'Anda sudah pernah melakukan pengajuan '
                        . $check->type_simc
                        . ' pada tanggal '
                        . Carbon::parse($check->tanggal_dari)->format('d-m-Y') . ' s/d ' . Carbon::parse($check->tanggal_sampai)->format('d-m-Y')
                        . ' dengan status saat ini '
                        . $check->status
                ], 409);
            }

            $today = now();

            if ($kategori === 'TAHUNAN') {
                $saldoCuti = SimcSaldo::whereNull('deleted_at')
                    ->with('karyawan:id,nip,nama,project_detail')
                    ->where('karyawan_id', $idkar)
                    ->where(function ($q) use ($today) {
                        $q->whereNull('saldo_expired') // quota normal tanpa expiry
                            ->orWhere('saldo_expired', '>=', $today); // carry over yang belum expired
                    })
                    ->orderBy('tahun', 'asc')
                    ->get();

                $totalRemaining = $saldoCuti->sum('remaining');
                if ($totalRemaining < $request->hari) {
                    throw new \Exception("Cuti ditolak, saldo tidak mencukupi. Sisa: {$totalRemaining}, diminta: {$request->hari}");
                }
            } else {
                $saldoCuti = 90;
                if ($saldoCuti < $request->hari) {
                    throw new \Exception("Cuti ditolak, saldo tidak mencukupi. Sisa: {$saldoCuti}, diminta: {$request->hari}");
                }
            }

            DB::transaction(function () use ($idcuti, $idkar, $dari, $sampai, $request, $saldoCuti, $kategori) {
                ## Simpan dulu untuk ambil ID nya
                $cuti = Simc::findOrFail($idcuti);
                $cuti->update([
                    'kategori'        => $request->kategori,
                    'type_simc'       => 'CUTI',
                    'tanggal_dari'    => $dari,
                    'tanggal_sampai'  => $sampai,
                    'tanggal_masuk'   => $request->tglmasuk,
                    'jumlah_hari'     => $request->hari,
                    'status'          => 'APPROVED',
                    'tanggal_approve' => now(),
                    'approved_by'     => auth()->id(),
                ]);

                ## Looping Tanggal Cuti
                $period = CarbonPeriod::create($dari, $sampai);
                foreach ($period as $date) {

                    $tanggal   = $date->format('Y-m-d');
                    $tgl       = date('j', strtotime($tanggal));
                    $bulan     = date('n', strtotime($tanggal));
                    $tahun     = date('Y', strtotime($tanggal));
                    $namaKolom = 'tgl' . $tgl;

                    ## Create Detail dan alokasi saldo
                    if ($kategori === 'TAHUNAN') {
                        foreach ($saldoCuti as $saldo) {
                            if ($saldo->remaining > 0) {
                                SimcDetail::create([
                                    'simc_id'           => $idcuti,
                                    'tahun_saldo'       => $saldo->tahun,
                                    'tanggal_pengajuan' => $tanggal,
                                    'source_type'       => $saldo->saldo_sebelumnya > 0 ? 'saldo_sebelumnya' : 'saldo_berjalan',
                                ]);

                                // 4. Update saldo di DB
                                $saldo->saldo_terpakai += 1;
                                $saldo->save();

                                break; // keluar loop saldo, lanjut ke tanggal berikutnya
                            }
                        }
                    }

                    # Cek Rekap Kehadiran
                    $rekap = RekapKehadiran::firstOrCreate([
                        'karyawan_id' => $idkar,
                        'bulan'       => $bulan,
                        'tahun'       => $tahun,
                    ]);

                    $rekap->refresh();
                    $nilaiSekarang = trim($rekap->$namaKolom);

                    if ($nilaiSekarang === null || $nilaiSekarang === '' || $nilaiSekarang === 'L') {
                        $rekap->update([
                            $namaKolom => 'C'
                        ]);
                    } else {
                        throw new \Exception("Cuti ditolak untuk tanggal {$tanggal}, sudah ada nilai : " . $nilaiSekarang);
                    }
                }
            });

            return response()->json([
                'message' => 'Data Cuti Berhasil Diproses.'
            ], 200);
        }
    }

    public function sisa_cuti(Request $request)
    {
        if ($request->ajax()) {
            $today = now();
            $akses = Auth::user()->getProjectAccess();
            $query = Karyawan::whereNull('deleted_at')
                ->whereNull('tanggal_keluar')
                ->with(['saldoCuti' => function ($q) use ($today) {
                    $q->whereNull('deleted_at')
                        ->where(function ($q) use ($today) {
                            $q->whereNull('saldo_expired')
                                ->orWhere('saldo_expired', '>=', $today);
                        })
                        ->orderBy('tahun', 'desc');
                }])
                ->whereIn('project', $akses)
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

                ->addColumn('tmt', function ($row) {
                    return Carbon::parse($row['tanggal_masuk'])->translatedFormat('d-m-Y');
                })

                ->addColumn('sisaSaldo', function ($row) {
                    $total = collect($row['saldo_cuti'])->sum('remaining');
                    return '<span class="badge bg-label-dark fw-bold">' . $total . ' Hari</span>';
                })

                ->addColumn('action', function ($row) {
                    $payload = [
                        'id'  => $row['id'],
                        'exp' => time() + 3600  // optional, expired 1 jam
                    ];

                    $token = JWT::encode($payload, config('jwt.secret'), 'HS256');

                    $button = '<div class="btn-group">
                            <button class="btn btn-sm btn-primary Detail" data-token=' . $token . '><i class="fas fa-info-circle me-1"></i>Detail</button>
                        </div>';

                    return $button;
                })

                ->rawColumns([
                    'tmt'       => 'tmt',
                    'lokasi'    => 'lokasi',
                    'sisaSaldo' => 'sisaSaldo',
                    'action'    => 'action'
                ])
                ->addIndexColumn()
                ->toJson();
        }

        return view('simc.cuti.sisa-cuti', [
            'judul' => 'Sisa Cuti'
        ]);
    }

    public function detail_record_cuti(Request $request)
    {
        try {
            $token   = $request->query('token');
            $decoded = JWT::decode($token, new Key(config('jwt.secret'), 'HS256'));
            $id      = $decoded->id;

            if ($request->ajax()) {
                $today = now();
                $query = Simc::withTrashed()
                    ->where([
                        'karyawan_id' => $id,
                        'type_simc'   => 'CUTI'
                    ])
                    ->with('karyawan:id,nip,nama,project_detail')
                    ->with(['karyawan.saldoCuti' => function ($q) use ($today) {
                        $q->whereNull('deleted_at')
                            ->where(function ($q) use ($today) {
                                $q->whereNull('saldo_expired')
                                    ->orWhere('saldo_expired', '>=', $today);
                            })
                            ->orderBy('tahun', 'desc');
                    }])
                    ->orderBy('tanggal_dari', 'desc')
                    ->get()
                    ->groupBy(function ($cuti) {
                        // grouping berdasarkan tahun tanggal mulai cuti
                        return Carbon::parse($cuti->tanggal_dari)->year;
                    });

                $result = $query->map(function ($cutis, $tahun) use ($query) {
                    // ambil saldo dari relasi karyawan yang sudah di-load
                    $karyawan = $cutis->first()->karyawan;

                    $cutiFormatted = $cutis->map(function ($c) {
                        return [
                            'id'            => $c->id,
                            'tanggal_dari'  => Carbon::parse($c->tanggal_dari)->format('d-m-Y'),
                            'tanggal_sampai' => Carbon::parse($c->tanggal_sampai)->format('d-m-Y'),
                            'jumlah_hari'   => $c->jumlah_hari,
                            'status'        => $c->status,
                            'type_simc'     => $c->type_simc,
                        ];
                    });

                    $saldo = $karyawan->saldoCuti->map(function ($s) {
                        return [
                            'tahun'      => $s->tahun,
                            'quota'      => $s->saldo,
                            'carry_over' => $s->saldo_sebelumnya,
                            'terpakai'   => $s->saldo_terpakai,
                            'remaining'  => $s->remaining,
                        ];
                    });

                    return [
                        'tahun' => $tahun,
                        'saldo' => $saldo,
                        'cuti'  => $cutiFormatted,
                    ];
                });

                return response()->json($result);
            }
        } catch (ExpiredException $e) {
            return back()->withErrors(['token_expired' => 'Oops! Token sudah kadaluarsa.']);
        }
    }

    // public function generateSaldo(Karyawan $karyawan)
    // {
    //     $today = now();
    //     $masaKerja = $karyawan->tmt_masuk->diffInMonths($today);

    //     $saldoBaru = new SimcSaldo();
    //     $saldoBaru->karyawan_id = $karyawan->id;

    //     if ($masaKerja < 12) {
    //         $saldoBaru->saldo = 0;
    //     } else {
    //         $saldoBaru->saldo = 12; // default cuti tahunan
    //     }

    //     $saldoBaru->saldo_sebelumnya = 0;
    //     $saldoBaru->saldo_expired = $today->copy()->endOfYear();
    //     $saldoBaru->saldo_terpakai = 0;
    //     $saldoBaru->save();
    // }

    public function libur_nasional(Request $request)
    {
        if ($request->ajax()) {
            $query = CutiBersama::whereNull('deleted_at')
                ->orderBy('tahun', 'DESC')
                ->orderBy('tanggal', 'ASC')
                ->get()
                ->toArray();

            return DataTables::of($query)
                ->addColumn('tanggal', function ($row) {
                    return Carbon::parse($row['tanggal'])->translatedFormat('l, d F Y');
                })

                ->addColumn('action', function ($row) {
                    $payload = [
                        'id'  => $row['id'],
                        'exp' => time() + 3600  // optional, expired 1 jam
                    ];

                    $token = JWT::encode($payload, config('jwt.secret'), 'HS256');

                    $button = '<div class="btn-group">';
                    $button .= '<button class="btn btn-sm btn-warning Edit" data-token=' . $token . '><i class="far fa-edit me-1"></i>Edit</button>';
                    $button .= '<button class="btn btn-sm btn-danger Hapus" data-token=' . $token . ' data-tanggal=' . Carbon::parse($row['tanggal'])->format('d-m-Y') . '><i class="ti ti-trash me-1"></i>Hapus</button>';
                    $button .= '</div>';

                    return $button;
                })

                ->rawColumns(['tanggal' => 'tanggal', 'action' => 'action'])
                ->addIndexColumn()
                ->toJson();
        }
        return view('simc.libur-nasional', [
            'judul' => 'Libur Nasional'
        ]);
    }

    public function store_libur_nasional(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'tanggalCuti' => 'required',
                'keterangan'  => 'required',
            ],
            [
                'tanggalCuti.required' => 'Tanggal masih kosong.',
                'keterangan.required'  => 'Keterangan masih kosong.',
            ]
        );

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->messages()->first()
            ], 409);
        } else {
            // dd($request->all());
            DB::transaction(function () use ($request) {

                if ($request->idlibur) {
                    $tanggalInput = Carbon::parse($request->tanggalCuti)->format('Y-m-d');
                    $tahun        = Carbon::parse($tanggalInput)->format('Y');

                    $current = CutiBersama::findOrFail($request->idlibur);

                    $existing = CutiBersama::withTrashed()
                        ->where('tahun', $tahun)
                        ->where('tanggal', $tanggalInput)
                        ->first();

                    if ($existing) {
                        if ($existing->trashed()) {
                            // restore record lama
                            $existing->restore();
                            $existing->update(['keterangan' => $request->keterangan]);

                            // hapus record yang sedang di-edit (tanggal sebelumnya)
                            if ($current->id != $existing->id) {
                                $current->delete();
                            }
                        } else {
                            // kalau record aktif tapi bukan dirinya sendiri → tolak
                            if ($existing->id != $current->id) {
                                throw new \Exception('Tanggal sudah tersedia untuk bulan/tahun tersebut.');
                            } else {
                                // record aktif tapi dirinya sendiri → update biasa
                                $current->update([
                                    'tahun'      => $tahun,
                                    'tanggal'    => $tanggalInput,
                                    'keterangan' => $request->keterangan,
                                ]);
                            }
                        }
                    } else {
                        // belum ada → update record yang sedang di-edit
                        $current->update([
                            'tahun'      => $tahun,
                            'tanggal'    => $tanggalInput,
                            'keterangan' => $request->keterangan,
                        ]);
                    }
                } else {
                    // MODE INSERT
                    $tanggal = explode(',', $request->tanggalCuti);
                    usort($tanggal, fn($a, $b) => strtotime($a) <=> strtotime($b));

                    foreach ($tanggal as $tgl) {
                        $tahun    = Carbon::parse($tgl)->format('Y');
                        $tglFormat = Carbon::parse($tgl)->format('Y-m-d');

                        $cuti = CutiBersama::withTrashed()
                            ->where('tahun', $tahun)
                            ->where('tanggal', $tglFormat)
                            ->first();

                        if ($cuti) {
                            if ($cuti->trashed()) {
                                // restore record lama
                                $cuti->restore();
                                $cuti->update(['keterangan' => $request->keterangan]);
                            } else {
                                // sudah ada aktif → tolak
                                throw new \Exception("Tanggal $tglFormat sudah tersedia untuk bulan/tahun tersebut.");
                            }
                        } else {
                            // belum ada sama sekali → create baru
                            CutiBersama::create([
                                'tahun'      => $tahun,
                                'tanggal'    => $tglFormat,
                                'keterangan' => $request->keterangan,
                            ]);
                        }
                    }
                }
            });

            return response()->json([
                'message' => 'Data Berhasil Disimpan.'
            ], 200);
        }
    }

    public function detail_liburnasioanal(Request $request)
    {
        try {
            $token   = $request->query('token');
            $decoded = JWT::decode($token, new Key(config('jwt.secret'), 'HS256'));
            $id      = $decoded->id;

            if ($request->ajax()) {
                $query = CutiBersama::findOrFail($id);
                return response()->json($query);
            }
        } catch (ExpiredException $e) {
            return back()->withErrors(['token_expired' => 'Oops! Token sudah kadaluarsa.']);
        }
    }

    public function destroy_liburnasional(Request $request)
    {
        try {
            $token   = $request->query('token');
            $decoded = JWT::decode($token, new Key(config('jwt.secret'), 'HS256'));
            $id      = $decoded->id;

            $hapus = CutiBersama::findOrFail($id);
            $hapus->delete();

            return response()->json([
                'message' => 'Data Berhasil Dihapus.'
            ], 200);
        } catch (ExpiredException $e) {
            return back()->withErrors(['token_expired' => 'Oops! Token sudah kadaluarsa.']);
        }
    }
}

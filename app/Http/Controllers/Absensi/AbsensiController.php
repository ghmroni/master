<?php

namespace App\Http\Controllers\Absensi;

use App\Exports\TemplateUpload;
use App\Http\Controllers\Controller;
use App\Models\Absensi;
use App\Models\Karyawan;
use App\Models\LokasiAbsensi;
use App\Models\Monitoring;
use App\Models\Project;
use App\Models\RekapKehadiran;
use App\Models\Schedule;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Yajra\DataTables\DataTables;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;

class AbsensiController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware(PermissionMiddleware::using('absensi-realtime'), only: ['index']),
            new Middleware(PermissionMiddleware::using('absensi-create'), only: ['store_absensi']),
            new Middleware(PermissionMiddleware::using('absensi-perorang'), only: ['absensi_perorang', 'download_perorang']),
            new Middleware(PermissionMiddleware::using('absensi-perlokasi'), only: ['absensi_perlokasi']),
            new Middleware(PermissionMiddleware::using('absensi-rekap-absen'), only: ['rekap_kehadiran', 'proses_download_rekap']),
            new Middleware(PermissionMiddleware::using('absensi-data-schedule'), only: ['data_schedule', 'proses_pergantian_schedule', 'update_schedule']),
            new Middleware(PermissionMiddleware::using('absensi-upload-schedule'), only: ['upload_schedule']),
            new Middleware(PermissionMiddleware::using('absensi-setlokasi'), only: ['store_lokasi_absensi']),
            new Middleware(PermissionMiddleware::using('absensi-rekap-waktu'), only: ['rekap_waktu']),
        ];
    }

    public function index(Request $request)
    {
        if ($request->ajax()) {
            $akses = auth()->user()->getProjectAccess();
            $query = Absensi::with('karyawan:id,nip,nama,project,project_detail')
                ->whereHas('karyawan', function ($q) use ($akses) {
                    $q->whereIn('project', $akses);
                })
                ->whereDate('check_time', Carbon::today())
                ->get()
                ->toArray();

            return DataTables::of($query)
                ->addColumn('waktu', function ($row) {
                    return date('d-m-Y H:i:s', strtotime($row['check_time']));
                })

                ->addColumn('penempatan', function ($row) {
                    $items = $row['penempatan'] ?? [];
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
                    return ($row['check_type'] == "I"
                        ? '<span class="badge bg-label-success"><i class="fas fa-sign-in-alt me-1"></i>Masuk</span>'
                        : '<span class="badge bg-label-danger">Pulang<i class="fas fa-sign-out-alt ms-1"></i></span>'
                    );
                })

                ->addColumn('photo', function ($row) {

                    $url = '/assets/img/avatars/no-image.png';
                    if ($row['foto'] != null) {
                        $url = '/storage/hris/absensi/' . $row['foto'];
                    }

                    return '
                        <a href="' . $url . '" class="glightbox" data-glightbox="type: image; zoomable: true;" data-title="' . $row['karyawan']['nip'] . ' | ' . $row['karyawan']['nama'] . ' | ' . Carbon::parse($row['check_time'])->format('H:i') . '" >
                            <img src="' . $url . '" class="img-thumbnail" alt="image" width="70">
                        </a>
                    ';
                })

                ->addColumn('action', function ($row) {
                    $payload = [
                        'id'  => $row['id'],
                        'exp' => time() + 3600  // optional, expired 1 jam
                    ];

                    $token = JWT::encode($payload, config('jwt.secret'), 'HS256');

                    $button = '<div class="btn-group">
                            <button class="btn btn-sm btn-info text-black liahtMap" data-token=' . $token . '><i class="fas fa-street-view me-1"></i>Map</button>
                        </div>';

                    return $button;
                })

                ->rawColumns([
                    'waktu'      => 'waktu',
                    'penempatan' => 'penempatan',
                    'status'     => 'status',
                    'photo'      => 'photo',
                    'action'     => 'action'
                ])
                ->addIndexColumn()
                ->toJson();
        }
        return view('absensi.realtime', [
            'judul' => 'Absensi Realtime'
        ]);
    }

    public function stream()
    {
        return response()->stream(function () {
            while (true) {
                $akses = auth()->user()->getProjectAccess();
                $query = Absensi::with('karyawan:id,nip,nama,project,project_detail')
                    ->whereHas('karyawan', function ($q) use ($akses) {
                        $q->whereIn('project', $akses);
                    })
                    ->whereDate('check_time', Carbon::today())
                    ->get()
                    ->toArray();

                $data = DataTables::of($query)
                    ->addColumn('waktu', fn($row) => date('d-m-Y H:i:s', strtotime($row['check_time'])))
                    ->addColumn('penempatan', function ($row) {
                        $items = $row['penempatan'] ?? [];
                        if (empty($items)) return '-';
                        $color = ['text-success', 'text-warning', 'text-primary', 'text-info', 'text-secondary', 'text-dark'];
                        $penempatan = array_map(
                            fn($item, $class) => "<span class='$class fw-bold d-flex'>$item</span>",
                            $items,
                            array_slice($color, 0, count($items))
                        );
                        return implode(' ', $penempatan);
                    })
                    ->addColumn('status', fn($row) => $row['check_type'] == "I"
                        ? '<span class="badge bg-label-success"><i class="fas fa-sign-in-alt me-1"></i>Masuk</span>'
                        : '<span class="badge bg-label-danger">Pulang<i class="fas fa-sign-out-alt ms-1"></i></span>')
                    ->addColumn('photo', function ($row) {
                        $url = $row['foto'] ? '/storage/hris/absensi/' . $row['foto'] : '/assets/img/avatars/no-image.png';
                        return '<a href="' . $url . '" class="glightbox" data-glightbox="type: image; zoomable: true;" data-title="' . $row['karyawan']['nip'] . ' | ' . $row['karyawan']['nama'] . ' | ' . Carbon::parse($row['check_time'])->format('H:i') . '" >
                        <img src="' . $url . '" class="img-thumbnail" alt="image" width="70">
                    </a>';
                    })
                    ->addColumn('action', function ($row) {
                        $payload = ['id' => $row['id'], 'exp' => time() + 3600];
                        $token = JWT::encode($payload, config('jwt.secret'), 'HS256');
                        return '<div class="btn-group">
                        <button class="btn btn-sm btn-info text-black liahtMap" data-token=' . $token . '><i class="fas fa-street-view me-1"></i>Map</button>
                    </div>';
                    })
                    ->rawColumns(['waktu', 'penempatan', 'status', 'photo', 'action'])
                    ->addIndexColumn()
                    ->toArray();

                echo "data: " . json_encode($data) . "\n\n";
                ob_flush();
                flush();

                sleep(5);
            }
        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache',
            'Connection' => 'keep-alive',
        ]);
    }


    public function add_absensi(Request $request)
    {
        $akses = auth()->user()->getProjectAccess();
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

        return view('absensi.tambah-absensi', [
            'judul' => 'Tambah Absensi'
        ], compact('karyawans'));
    }

    public function cek_absensi_person(Request $request)
    {
        $idkar = decrypt($request->token);
        if ($request->ajax()) {
            $query = Karyawan::select('id', 'project_detail')
                ->whereNull('deleted_at')
                ->whereNull('tanggal_keluar')
                ->with('absensiTerbaru')
                ->where('id', $idkar)
                ->first();

            return response()->json([
                'penempatan' => $query->lokasi_penempatan,
                'lastAbsensi' => $query->absensiTerbaru,
            ]);
        }
    }

    public function store_absensi(Request $request)
    {
        $idkar = decrypt($request->karyawanid);
        $validator = Validator::make(
            $request->all(),
            [
                'karyawanid'     => 'required',
                'penempatan'     => 'required',
                'tanggalabsensi' => 'required|date',
                'waktuabsensi'   => 'required',
                'typeabsensi'    => 'required|in:I,O',
            ],
            [
                'tanggalabsensi' => 'Tanggal Absensi masih kosong',
                'waktuabsensi'   => 'Waktu Absensi masih kosong',
                'typeabsensi'    => 'Type Absensi masih kosong',
            ]
        );
        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->messages()->first(),
            ], 405);
        } else {
            // $cekAbsensi = Absensi::whereDate('check_time', $request->tanggalabsensi)
            //     ->where([
            //         'karyawan_id' => $idkar,
            //         'check_type' => $request->typeabsensi,
            //     ])->exists();

            // if ($cekAbsensi) {
            //     return response()->json([
            //         'errors' => 'Absensi pada tanggal ' . Carbon::parse($request->tanggalabsensi)->translatedFormat('d F Y') . ' Sudah Tersedia.',
            //     ], 409);
            // }

            $cekAbsensi = Absensi::whereDate('check_time', $request->tanggalabsensi)
                ->where('karyawan_id', $idkar)
                ->get();

            if ($cekAbsensi->isNotEmpty()) {
                // cek apakah sudah ada IN
                $adaMasuk = $cekAbsensi->where('check_type', 'I')->isNotEmpty();

                if ($request->typeabsensi === 'I' && $adaMasuk) {
                    return response()->json([
                        'errors' => 'Absensi masuk pada tanggal '
                            . Carbon::parse($request->tanggalabsensi)->translatedFormat('d F Y')
                            . ' sudah tersedia.',
                    ], 409);
                }
                // kalau type O, abaikan walaupun ada I
            }

            DB::transaction(function () use ($request, $idkar) {
                Absensi::create([
                    'karyawan_id' => $idkar,
                    'check_time'  => $request->tanggalabsensi . ' ' . $request->waktuabsensi,
                    'check_type'  => $request->typeabsensi,
                    'penempatan'  => json_decode($request->penempatan),
                    'mesin'       => 'MANUAL',
                ]);

                if ($request->typeabsensi == "I") {
                    $tgl        = date('j', strtotime($request->tanggalabsensi)); // 'j' menghasilkan 1-31 tanpa nol di depan
                    $bulan      = date('n', strtotime($request->tanggalabsensi)); // 'n' menghasilkan 1-12 tanpa nol di depan
                    $tahun      = date('Y', strtotime($request->tanggalabsensi));
                    $namaKolom  = 'tgl' . $tgl; // Menghasilkan: tgl1, tgl2, dst.

                    $rekap = RekapKehadiran::firstOrCreate([
                        'karyawan_id' => $idkar,
                        'bulan'       => $bulan,
                        'tahun'       => $tahun,
                    ]);

                    $rekap->refresh(); // pastikan ambil nilai terbaru dari DB
                    // 3. Ambil nilai saat ini yang tersimpan di kolom tanggal tersebut
                    $nilaiSekarang = trim($rekap->$namaKolom);
                    // dd($nilaiSekarang);
                    // 4. Cek kondisinya: Jika nilainya masih kosong (null), bernilai '-', atau sudah 'H', maka lakukan update
                    if ($nilaiSekarang === null || $nilaiSekarang === '' || $nilaiSekarang === 'H') {
                        $rekap->update([
                            $namaKolom => 'H'
                        ]);
                    } else {
                        throw new \Exception('Absensi ditolak karena sudah nilai : ' . $nilaiSekarang);
                    }
                }
            });

            return response()->json([
                'message' => 'Absensi karyawan Berhasil Diproses.'
            ], 200);
        }
    }

    public function absensi_perorang(Request $request)
    {
        $akses = auth()->user()->getProjectAccess();
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

        return view('absensi.perorang', [
            'judul' => 'Absensi Perorang'
        ], compact('karyawans'));
    }

    public function getAbsensi_perorang(Request $request)
    {
        $idkar = decrypt($request->karyawanid);
        $start = Carbon::parse($request->dari)->startOfDay();
        $end = Carbon::parse($request->sampai)->endOfDay();
        $query = Absensi::with('karyawan:id,nip,nama')
            ->where('karyawan_id', $idkar)
            ->whereBetween('check_time', [$start, $end])
            ->orderBy('check_time', 'asc')
            ->get();

        $monitoringData = Monitoring::where('karyawan_id', $idkar)
            ->whereBetween('tanggal', [$start, $end])
            ->get()
            ->groupBy(function ($m) {
                return Carbon::parse($m->tanggal)->format('Y-m-d');
            });

        $dataByDate = [];
        $period = CarbonPeriod::create($start, $end);

        foreach ($period as $date) {
            $tanggal = $date->format('Y-m-d');

            # CEK ABSENSI
            $items = $query->filter(function ($row) use ($date) {
                $checkTime = Carbon::parse($row->check_time);
                return $checkTime->between(
                    $date->copy()->startOfDay(),
                    $date->copy()->endOfDay()
                );
            });

            # Absen Masuk ambil yang paling awal
            $masuk = $items->where('check_type', 'I')->sortBy('check_time')->first();
            # Absen Pulang ambil yang paling akhir
            $pulang = $items->where('check_type', 'O')->sortByDesc('check_time')->first();

            # Cari O yang lebih kecil dari I
            $invalidPulang = $items->where('check_type', 'O')
                ->filter(function ($row) use ($masuk) {
                    return $masuk && Carbon::parse($row->check_time)->lt(Carbon::parse($masuk->check_time));
                })
                ->sortByDesc('check_time')
                ->first();

            if ($invalidPulang) {
                // cari ke belakang sampai ketemu tanggal yang punya I tapi belum punya O
                $prevDate = Carbon::parse($tanggal)->subDay();
                while ($prevDate->gte($start)) {
                    $prevTanggal = $prevDate->format('Y-m-d');
                    if (isset($dataByDate[$prevTanggal])) {
                        $prevRow = $dataByDate[$prevTanggal];
                        if ($prevRow['masuk'] !== '-' && $prevRow['pulang'] === '-') {
                            $dataByDate[$prevTanggal]['pulang'] = date('d-m-Y H:i', strtotime($invalidPulang->check_time));
                            break;
                        }
                    }
                    $prevDate->subDay();
                }
                // jangan tampilkan di tanggal sekarang
                if ($pulang && $pulang->check_time == $invalidPulang->check_time) {
                    $pulang = null;
                }
            }

            $firstItem = $items->first();
            $url = $firstItem
                ? ($firstItem->foto != null ? '/storage/hris/absensi/' . $firstItem->foto : '/assets/img/avatars/no-image.png')
                : '';

            $monitoringRow = $monitoringData[$tanggal][0] ?? null;

            $dataByDate[$tanggal] = [
                'hari'               => Carbon::parse($tanggal)->translatedFormat('l'),
                'tanggal'            => Carbon::parse($tanggal)->translatedFormat('d-m-Y'),
                'masuk'              => ($masuk ? date('d-m-Y H:i', strtotime($masuk->check_time)) : '-'),
                'pulang'             => ($pulang ? date('d-m-Y H:i', strtotime($pulang->check_time)) : '-'),
                'photo'              => $url,
                'prioritas'          => $monitoringRow->prioritas ?? null,
                'tanggal_monitoring' => $monitoringRow ? Carbon::parse($monitoringRow->tanggal)->translatedFormat('d F Y H:i') : null,
                'keterangan'         => $monitoringRow->keterangan ?? null,
                'foto_monitoring'    => $monitoringRow ? '/storage/hris/monitoring/' . $monitoringRow->foto : null,
            ];
        }

        return DataTables::of(array_values($dataByDate))
            ->addIndexColumn()
            ->toJson();
    }

    public function download_perorang(Request $request)
    {
        $idkar = decrypt($request->karyawanid);
        $start = Carbon::parse($request->dari)->startOfDay();
        $end = Carbon::parse($request->sampai)->endOfDay();

        $query = Absensi::with('karyawan:id,nip,nama')
            ->where('karyawan_id', $idkar)
            ->whereBetween('check_time', [$start, $end])
            ->orderBy('check_time', 'asc')
            ->get();

        $monitoringData = Monitoring::where('karyawan_id', $idkar)
            ->whereBetween('tanggal', [$start, $end])
            ->get()
            ->groupBy(function ($m) {
                return Carbon::parse($m->tanggal)->format('Y-m-d');
            });

        $karyawan = Karyawan::select('id', 'nip', 'nama', 'project_detail')
            ->where('id', $idkar)
            ->first()
            ->toArray();

        $karyawan['dari']   = Carbon::parse($request->dari)->translatedFormat('d F Y');
        $karyawan['sampai'] = Carbon::parse($request->sampai)->translatedFormat('d F Y');

        $dataByDate = [];
        $period = CarbonPeriod::create($start, $end);

        foreach ($period as $date) {
            $tanggal = $date->format('Y-m-d');

            // Filter absensi untuk tanggal ini
            $items = $query->filter(function ($row) use ($date) {
                $checkTime = Carbon::parse($row->check_time);
                return $checkTime->between(
                    $date->copy()->startOfDay(),
                    $date->copy()->endOfDay()
                );
            });

            # Absen Masuk ambil yang paling awal
            $masuk = $items->where('check_type', 'I')->sortBy('check_time')->first();
            # Absen Pulang ambil yang paling akhir
            $pulang = $items->where('check_type', 'O')->sortByDesc('check_time')->first();

            # Cari O yang lebih kecil dari I
            $invalidPulang = $items->where('check_type', 'O')
                ->filter(function ($row) use ($masuk) {
                    return $masuk && Carbon::parse($row->check_time)->lt(Carbon::parse($masuk->check_time));
                })
                ->sortByDesc('check_time')
                ->first();

            if ($invalidPulang) {
                // cari ke belakang sampai ketemu tanggal yang punya I tapi belum punya O
                $prevDate = Carbon::parse($tanggal)->subDay();
                while ($prevDate->gte($start)) {
                    $prevTanggal = $prevDate->format('Y-m-d');
                    if (isset($dataByDate[$prevTanggal])) {
                        $prevRow = $dataByDate[$prevTanggal];
                        if ($prevRow['masuk'] !== '-' && $prevRow['pulang'] === '-') {
                            $dataByDate[$prevTanggal]['pulang'] = date('d-m-Y H:i', strtotime($invalidPulang->check_time));
                            break;
                        }
                    }
                    $prevDate->subDay();
                }
                // jangan tampilkan di tanggal sekarang
                if ($pulang && $pulang->check_time == $invalidPulang->check_time) {
                    $pulang = null;
                }
            }

            $firstItem = $items->first();
            $url = '';
            if ($firstItem) {
                $url = ($firstItem->foto != null ? '/storage/hris/absensi/' . $firstItem->foto : '/assets/img/avatars/no-image.png');
            }

            $monitoringRow = $monitoringData[$tanggal][0] ?? null;

            $dataByDate[$tanggal] = [
                'hari'               => Carbon::parse($tanggal)->translatedFormat('l'),
                'tanggal'            => Carbon::parse($tanggal)->translatedFormat('d F Y'),
                'masuk'              => ($masuk ? date('d-m-Y H:i', strtotime($masuk->check_time)) : '-'),
                'pulang'             => ($pulang ? date('d-m-Y H:i', strtotime($pulang->check_time)) : '-'),
                'photo'              => $url,
                'prioritas'          => $monitoringRow->prioritas ?? null,
                'tanggal_monitoring' => $monitoringRow ? Carbon::parse($monitoringRow->tanggal)->translatedFormat('d F Y H:i') : null,
                'keterangan'         => $monitoringRow->keterangan ?? null,
                'foto_monitoring'    => $monitoringRow
                    ? '/storage/hris/monitoring/' . $monitoringRow->foto
                    : null,
            ];
        }

        $pdf = Pdf::loadView('absensi.pdf_perorang', [
            'karyawan' => $karyawan,
            'data' => $dataByDate
        ]);

        $filename = 'REKAP - ' . $karyawan['nama'] . '.pdf';

        return response($pdf->output(), 200)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }

    public function absensi_perlokasi(Request $request)
    {
        return view('absensi.perlokasi', [
            'judul' => 'Rekap Absensi Perlokasi'
        ]);
    }

    function getDescendants($projectId)
    {
        $children = Project::where('parent_id', $projectId)->pluck('id')->toArray();
        $all = $children;
        foreach ($children as $childId) {
            $all = array_merge($all, $this->getDescendants($childId));
        }
        return $all;
    }

    public function rekap_kehadiran(Request $request)
    {
        if ($request->ajax()) {
            $useSchedule = $request->schedule;
            $selectedId  = $request->projectid;
            # Masukan id dari form ke allids
            $allIds = [$selectedId];
            # Ambil turunan dari selectedId
            $allIds = array_merge($allIds, $this->getDescendants($selectedId));

            $userAccess = auth()->user()->getProjectAccess();
            # Hasil Filter dengan AKses user
            $accessId = array_intersect($allIds, $userAccess);

            $tglDari   = Carbon::parse($request->dari);
            $tglSampai = Carbon::parse($request->sampai);
            $periode   = CarbonPeriod::create($tglDari, $tglSampai);

            // 3. Jalankan query dengan kondisi bulan dan tahun
            $query = Karyawan::select('id', 'nip', 'nama', 'project', 'project_detail')
                ->whereNull('deleted_at')
                ->whereIn('project', $accessId)
                ->with(['rekapKehadiran' => function ($query) use ($tglDari, $tglSampai) {
                    $query->where(function ($q) use ($tglDari, $tglSampai) {
                        // Memastikan mengambil bulan & tahun yang relevan dengan range form
                        $q->whereBetween('tahun', [$tglDari->year, $tglSampai->year])
                            ->whereBetween('bulan', [$tglDari->month, $tglSampai->month]);
                    });
                }])
                ->orderBy('project', 'ASC')
                ->orderBy('nama', 'ASC')
                ->get();

            # Filter kolom tgl1 sampai tgl31 secara dinamis sesuai range tanggal form
            $result = $query
                ->filter(function ($karyawan) use ($useSchedule) {
                    // Jika pakai schedule, hanya ambil karyawan yang punya schedule
                    if ($useSchedule) {
                        return $karyawan->schedule->isNotEmpty();
                    }
                    return true; // kalau tidak pakai schedule, semua tetap diambil
                })
                ->map(function ($karyawan) use ($periode, $useSchedule) {
                    $row = [
                        'nip'  => $karyawan->nip,
                        'nama' => $karyawan->nama,
                    ];

                    foreach ($periode as $tanggal) {
                        $tahun = $tanggal->year;
                        $bulan = $tanggal->month;
                        $hari  = $tanggal->day;

                        $rekapBulanIni = $karyawan->rekapKehadiran
                            ->where('tahun', $tahun)
                            ->where('bulan', $bulan)
                            ->first();

                        $namaKolom = 'tgl' . $hari;

                        # APABILA BERDASARKAN SCHEDULE
                        if ($useSchedule && $karyawan->schedule->isNotEmpty()) {
                            // Ambil schedule sesuai bulan & tahun
                            $schedule = $karyawan->schedule
                                ->where('tahun', $tahun)
                                ->where('bulan', $bulan)
                                ->first();

                            $valSchedule = $schedule ? $schedule->$namaKolom : null;

                            if ($valSchedule !== null) {
                                if (is_numeric($valSchedule) || preg_match('/^\d{1,2}:\d{2}$/', $valSchedule)) {
                                    // numeric/jam → cek rekap, kalau ada pakai rekap, kalau tidak ada kosong
                                    $statusAbsen = $rekapBulanIni ? $rekapBulanIni->$namaKolom : null;
                                } else {
                                    // alphabet → langsung pakai schedule
                                    $statusAbsen = $valSchedule;
                                }
                            } else {
                                $statusAbsen = null;
                            }
                        } else {
                            // Default: ambil dari rekap
                            $statusAbsen = $rekapBulanIni ? $rekapBulanIni->$namaKolom : null;
                        }

                        $row[$tanggal->format('Y-m-d')] = $statusAbsen ?? '-';
                        # END APABILA BERDASARKAN SCHEDULE
                        // $row[$tanggal->format('Y-m-d')] = $rekapBulanIni ? $rekapBulanIni->$namaKolom : '-';
                    }

                    return $row;
                });

            return response()->json([
                'periode' => collect($periode)->map(function (Carbon $tgl) {
                    return $tgl->format('Y-m-d');
                })->toArray(),
                'data'    => $result
            ]);
        }
        return view('absensi.rekap-kehadiran', [
            'judul' => 'Rekap Kehadiran'
        ]);
    }

    public function lokasi_absensi(Request $request)
    {
        if ($request->ajax()) {
            $akses = auth()->user()->getProjectAccess();
            $query = LokasiAbsensi::whereNull('deleted_at')
                ->where(function ($q) use ($akses) {
                    foreach ($akses as $idProject) {
                        // Mencari apakah ada salah satu ID dari array $akses di dalam string project_detail
                        $q->orWhereRaw("FIND_IN_SET(?, project_detail)", [$idProject]);
                    }
                })
                ->orderBy('updated_at', 'desc')
                ->get()
                ->toArray();

            return DataTables::of($query)
                ->addColumn('penempatan', function ($row) {
                    $items = $row['lokasi_absen'] ?? [];
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
                    $payload = [
                        'id'  => $row['id'],
                        'exp' => time() + 3600  // optional, expired 1 jam
                    ];

                    $token = JWT::encode($payload, config('jwt.secret'), 'HS256');

                    return '<label class="switch switch-' . ($row['status'] == "A" ? 'success' : 'danger') . '">
                                <input type="checkbox" class="switch-input switchStatus " data-token="' . $token . '" data-status = "' . ($row['status'] == "A" ? "T" : "A") . '" data-nama="' . $row['nama_koordinat'] . '" checked />
                                <span class="switch-toggle-slider">
                                    <span class="switch-on">
                                        <i class="ti ti-check"></i>
                                    </span>
                                    <span class="switch-off">
                                        <i class="ti ti-x"></i>
                                    </span>
                                </span>
                            </label>';
                })

                ->addColumn('koordinat', function ($row) {
                    $tagCoordinat = explode(',', $row['tag_location']);
                    return '<span class="text-muted">Lat</span> : ' . ($tagCoordinat[0] ?? '-') . '<br> <span class="text-muted">Lng</span> : ' . ($tagCoordinat[1] ?? '-');
                })

                ->addColumn('action', function ($row) {
                    $payload = [
                        'id'  => $row['id'],
                        'exp' => time() + 3600  // optional, expired 1 jam
                    ];

                    $token = JWT::encode($payload, config('jwt.secret'), 'HS256');

                    $button = '<div class="btn-group">';
                    $button .= '<button class="btn btn-icon btn-dark btn-sm currentLocation" data-bs-toggle="tooltip" data-bs-placement="top" title="Lihat Maps" data-coordinat="' . $row['tag_location'] . '" data-radius="' . $row['radius'] . '"><i class="fas fa-street-view"></i></button>';
                    $button .= '<button class="btn btn-icon btn-primary btn-sm detailLokasi" data-bs-toggle="tooltip" data-bs-placement="top" title="Detail" data-bs-custom-class="tooltip-primary" data-token="' . $token . '"><i class="fas fa-info-circle"></i></button>';
                    if (auth()->user()->can('absensi-setlokasi')) {
                        $button .= '<button class="btn btn-icon btn-danger btn-sm hapusLokasi" data-bs-toggle="tooltip" data-bs-placement="top" title="Hapus" data-bs-custom-class="tooltip-danger" data-token="' . $token . '" data-nama="' . $row['nama_koordinat'] . '"><i class="ti ti-trash"></i></button>';
                    }
                    $button .= '</div>';

                    return $button;
                })

                ->rawColumns([
                    'penempatan' => 'penempatan',
                    'status'     => 'status',
                    'koordinat'  => 'koordinat',
                    'action'     => 'action',
                ])
                ->addIndexColumn()
                ->toJson();
        }

        return view('absensi.lokasi-absensi', [
            'judul' => 'Lokasi Absensi'
        ]);
    }

    public function maps_lokasi_absensi(Request $request)
    {
        if ($request->ajax()) {
            $akses = auth()->user()->getProjectAccess();
            $query = LokasiAbsensi::whereNull('deleted_at')
                // ->where('status', 'A')
                ->where(function ($q) use ($akses) {
                    foreach ($akses as $idProject) {
                        // Mencari apakah ada salah satu ID dari array $akses di dalam string project_detail
                        $q->orWhereRaw("FIND_IN_SET(?, project_detail)", [$idProject]);
                    }
                })
                ->get();

            foreach ($query as $row) {
                $payload = [
                    'id'  => $row->id,
                    'exp' => time() + 3600
                ];
                $row->token = JWT::encode($payload, config('jwt.secret'), 'HS256');
            }

            return response()->json($query)->header('Cache-Control', 'no-cache, no-store, must-revalidate');
        }
    }

    public function store_status_web(Request $request)
    {
        $token   = $request->input('token');
        $decoded = JWT::decode($token, new Key(config('jwt.secret'), 'HS256'));
        $id      = $decoded->id;
        $mesinWeb = LokasiAbsensi::findOrFail($id);

        $mesinWeb->update([
            'status' => ($mesinWeb->status == "A" ? "T" : "A")
        ]);

        return response()->json([
            'message' => 'Status Mesin Web Berhasil Dirubah.'
        ], 200);
    }

    public function add_lokasi_absensi(Request $request)
    {
        return view('absensi.add-lokasi-absensi', [
            'judul' => 'Tambah Lokasi Absensi'
        ]);
    }

    public function detail_lokasi_absensi(Request $request)
    {
        if ($request->has('token')) {
            $token   = $request->query('token');
            $decoded = JWT::decode($token, new Key(config('jwt.secret'), 'HS256'));
            $id      = $decoded->id;

            $data = LokasiAbsensi::findOrFail($id);
            $penempatan = $data->toArray();

            $color = ['bg-success', 'bg-warning', 'bg-primary', 'bg-info', 'bg-secondary', 'bg-dark'];
            $lokasi = array_map(
                fn($item, $class) => "<span class='$class me-1 text-black badge mb-1'>$item</span>",
                $penempatan['lokasi_absen'],
                array_slice($color, 0, count($penempatan['lokasi_absen']))
            );
        }

        return view('absensi.detail-lokasi-absensi', [
            'judul' => 'Detail Lokasi Absensi'
        ], compact('data', 'lokasi'));
    }

    public function generateUrlMaps(Request $request)
    {
        if ($request->ajax()) {
            $url = $request->input('url');
            $coordinat = $this->getLatLngFromUrl($url);

            if ($coordinat) {
                return response()->json($coordinat);
            }
            return response()->json(['error' => 'Tidak bisa ambil coordinat dari URL'], 400);
        }
    }

    public function getLatLngFromUrl($url)
    {
        // Expand shortlink
        if (str_contains($url, 'goo.gl') || str_contains($url, 'maps.app.goo.gl')) {
            $response = Http::withOptions(['allow_redirects' => ['track_redirects' => true]])->get($url);
            $url = $response->effectiveUri();
        }

        // Format ?q=lat,lng
        parse_str(parse_url($url, PHP_URL_QUERY), $query);
        if (isset($query['q']) && preg_match('/(-?\d+\.\d+),(-?\d+\.\d+)/', $query['q'], $matches)) {
            return ['lat' => $matches[1], 'lng' => $matches[2]];
        }

        // Format @lat,lng,zoom
        if (preg_match('/@(-?\d+\.\d+),(-?\d+\.\d+)/', $url, $matches)) {
            return ['lat' => $matches[1], 'lng' => $matches[2]];
        }

        // Format !3dLAT!4dLNG
        if (preg_match('/!3d(-?\d+\.\d+)!4d(-?\d+\.\d+)/', $url, $matches)) {
            return ['lat' => $matches[1], 'lng' => $matches[2]];
        }

        return null;
    }

    public function store_lokasi_absensi(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'url' => [
                    'nullable',
                    'regex:/^(https?:\/\/[^\s]+)$/i', // pastikan format URL
                ],
                'lat' => [
                    'required',
                    'regex:/^-?\d{1,3}\.\d+$/', // angka desimal, bisa negatif
                ],
                'lng' => [
                    'required',
                    'regex:/^-?\d{1,3}\.\d+$/', // angka desimal, bisa negatif
                ],
                'nama' => [
                    'required',
                    Rule::unique('lokasi_absensi', 'nama_koordinat')->ignore($request->idlokasi)->where(function (Builder $query) {
                        $query->where('deleted_at', null);
                    }),
                ],
                'radius'    => 'required|numeric',
                'projectid' => 'required',
                'dtproject' => 'required',
                'status'    => 'required|in:A,T',
            ],
            [
                // 'url.required' => 'URL masih kosong.',
                'url.regex'    => 'Format URL tidak valid.',
                'lat.required' => 'Latitude wajib diisi.',
                'lat.numeric'  => 'Latitude harus berupa angka.',
                'lat.between'  => 'Latitude harus antara -90 sampai 90.',
                'lng.required' => 'Longitude wajib diisi.',
                'lng.numeric'  => 'Longitude harus berupa angka.',
                'lng.between'  => 'Longitude harus antara -180 sampai 180.',
                'nama.unique'  => 'Nama Koordinat sudah pernah diinput.',
            ]
        );
        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->messages()->first(),
            ], 405);
        } else {

            $dtChild = Project::find($request->projectid)
                ->descendantsAndSelf()
                ->pluck('id')
                ->toArray();

            LokasiAbsensi::updateOrCreate(
                [
                    'id' => $request->idlokasi,
                ],
                [
                    'project_detail' => implode(',', $dtChild),
                    'penempatan'     => $request->dtproject,
                    'tag_location'   => implode(',', array($request->lat, $request->lng)),
                    'radius'         => $request->radius,
                    'nama_koordinat' => trim(strtoupper($request->nama)),
                    'url_map'        => $request->url,
                    'status'         => $request->status,
                ]
            );

            return response()->json([
                'message' => 'Lokasi Absensi Berhasil Disimpan.'
            ], 200);
        }
    }

    public function hapus_lokasi_absensi(Request $request)
    {
        try {
            $token   = $request->input('token');
            $decoded = JWT::decode($token, new Key(config('jwt.secret'), 'HS256'));
            $id      = $decoded->id;

            $model = LokasiAbsensi::findOrFail($id);
            $model->delete();
            return response()->json([
                'message' => 'Mesin Web Titik Lokasi Absensi Berhasil Dihapus.'
            ], 200);
        } catch (ExpiredException $e) {
            return back()->withErrors(['token_expired' => 'Oops! Token sudah kadaluarsa.']);
        }
    }

    public function upload_schedule(Request $request)
    {
        return view('absensi.uploads-schedule', [
            'judul' => 'Uploads Schedule'
        ]);
    }

    public function data_schedule(Request $request)
    {
        if ($request->ajax()) {
            $selectedId = $request->projectid;
            # Masukan id dari form ke allids
            $allIds = [$selectedId];
            # Ambil turunan dari selectedId
            $allIds = array_merge($allIds, $this->getDescendants($selectedId));

            $userAccess = auth()->user()->getProjectAccess();
            # Hasil Filter dengan AKses user
            $accessId = array_intersect($allIds, $userAccess);

            // $bulan = Carbon::createFromFormat('Y-m', $request->periode);
            // Ambil periode dari input "Y-m"
            list($year, $month) = explode('-', $request->periode);

            // Buat tanggal aman dengan hari = 1
            $bulan = Carbon::create($year, $month, 1);
            $tglDari   = $bulan->copy()->firstOfMonth();
            $tglSampai = $bulan->copy()->lastOfMonth();

            $periode   = CarbonPeriod::create($tglDari, $tglSampai);
            $query = Schedule::where([
                'bulan' => $tglDari->month,
                'tahun' => $tglDari->year,
            ])
                ->whereHas('karyawan', function ($q) use ($accessId) {
                    $q->whereNull('deleted_at')
                        ->whereIn('project', $accessId);
                })
                ->with(['karyawan' => function ($q) {
                    $q->select('id', 'nip', 'nama', 'project', 'project_detail')
                        ->orderBy('project', 'ASC')
                        ->orderBy('nama', 'ASC');
                }])
                ->get();

            # Filter kolom tgl1 sampai tgl31 secara dinamis sesuai range tanggal form
            $result = $query->map(function ($schedule) use ($periode) {
                $token = ($schedule->id ? encrypt($schedule->id) : 0);

                $row = [
                    'id'   => $token,
                    'nip'  => $schedule->karyawan->nip ?? null,
                    'nama' => $schedule->karyawan->nama ?? null,
                ];

                foreach ($periode as $tanggal) {
                    $tahun = $tanggal->year;
                    $bulan = $tanggal->month;
                    $hari  = $tanggal->day;

                    if ($schedule->tahun == $tahun && $schedule->bulan == $bulan) {
                        $namaKolom = 'tgl' . $hari;
                        $nilai = $schedule->$namaKolom ?? null;
                        $row[$tanggal->format('Y-m-d')] = $nilai !== null ? $nilai : '-';
                    } else {
                        $row[$tanggal->format('Y-m-d')] = '-';
                    }
                }

                return $row;
            });

            $dtHeader = collect($periode)->map(function (Carbon $tgl) {
                return $tgl->format('Y-m-d');
            })->toArray();

            return response()->json([
                'periode' => $result->isNotEmpty() ? $dtHeader : [],
                'data'    => $result
            ]);
        }
        return view('absensi.data-schedule', [
            'judul' => 'Data Schedule'
        ]);
    }

    public function pergantian_schedule(Request $request)
    {
        $id = decrypt($request->token);
        $tahun = date('Y', strtotime($request->tgl));
        $bulan = date('n', strtotime($request->tgl));
        $tgl = date('j', strtotime($request->tgl));
        $namaKolom = 'tgl' . $tgl;

        $query = Schedule::select('id', 'karyawan_id', $namaKolom)
            ->where('id', $id)
            ->with('karyawan:id,nip,nama,project,project_detail')
            ->whereHas('karyawan', function ($q) {
                $q->whereNull('deleted_at');
            })
            ->first();

        if ($query) {
            $query->setAttribute('namaKolom', $namaKolom);
            $query->setAttribute('valKolom', $query->{$namaKolom});

            $karyawans = Karyawan::select('id', 'nip', 'nama')
                ->whereNull('deleted_at')
                ->whereNull('tanggal_keluar')
                ->where('id', '!=', $query->karyawan_id)
                ->where('project', $query->karyawan->project)
                ->whereHas('schedule', function ($q) use ($bulan, $tahun) {
                    $q->where([
                        'bulan' => $bulan,
                        'tahun' => $tahun,
                    ]);
                })
                ->orderBy('nama', 'ASC')
                ->get()
                ->map(function ($karyawan) {
                    // Membuat token enkripsi baru khusus untuk ID
                    $karyawan->token_id = encrypt($karyawan->id);
                    return $karyawan;
                });
        }

        return response()->json([
            'data'      => $query,
            'karyawans' => $karyawans
        ]);
    }

    public function pengganti_schedule(Request $request)
    {
        $id = decrypt($request->token);
        $tahun = date('Y', strtotime($request->tanggal));
        $bulan = date('n', strtotime($request->tanggal));
        $tgl = date('j', strtotime($request->tanggal));
        $namaKolom = 'tgl' . $tgl;

        $query = Schedule::select('id', 'karyawan_id', $namaKolom)
            ->where([
                'karyawan_id' => $id,
                'bulan'       => $bulan,
                'tahun'       => $tahun,
            ])
            ->with('karyawan:id,nip,nama,project,project_detail')
            ->whereHas('karyawan', function ($q) {
                $q->whereNull('deleted_at');
            })
            ->first();

        if ($query) {
            $query->setAttribute('namaKolom', $namaKolom);
            $query->setAttribute('valKolom', $query->{$namaKolom});
        }

        return response()->json($query);
    }

    public function proses_pergantian_schedule(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'fromScLama' => 'required',
                'fromScBaru' => 'required',
                'toScBaru'   => 'required',
                'toScLama'   => 'required',
                'namaKolom'  => 'required',
            ],
            [
                'fromScLama.required' => 'Schedule Sebelumnya masih kosong',
                'fromScBaru.required' => 'Schedule Baru masih kosong',
            ]
        );

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->messages()->first(),
            ], 405);
        } else {

            DB::transaction(function () use ($request) {
                $kolom = $request->namaKolom;

                $asal   = Schedule::findOrFail($request->fromId);
                $tujuan = Schedule::findOrFail($request->toId);

                $asal->update([$kolom => $request->fromScBaru]);
                $tujuan->update([$kolom => $request->toScLama]);
            });

            return response()->json([
                'message' => 'Pergantian Schedule Berhasil Diproses.'
            ], 200);
        }
    }

    public function detail_schedule(Request $request)
    {
        $id = decrypt($request->token);
        $tgl = date('j', strtotime($request->tanggal));
        $namaKolom = 'tgl' . $tgl;

        $query = Schedule::select('id', 'karyawan_id', $namaKolom)
            ->where('id', $id)
            ->with('karyawan:id,nip,nama,project,project_detail')
            ->whereHas('karyawan', function ($q) {
                $q->whereNull('deleted_at');
            })
            ->first();

        if ($query) {
            $query->setAttribute('namaKolom', $namaKolom);
            $query->setAttribute('valKolom', $query->{$namaKolom});
            $query->setAttribute('lokasi', $query->karyawan->lokasi_penempatan);
        }

        return response()->json($query);
    }

    public function update_schedule(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'newSchedule' => 'required',
                'ids'         => 'required',
            ],
            [
                'newSchedule.required' => 'Schedule Baru masih kosong.',
                'ids.required'         => 'Gagal Ambil data.',
            ]
        );

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->messages()->first(),
            ], 405);
        } else {

            $data = Schedule::findOrFail($request->ids);
            $data->update([$request->kolom => trim(strtoupper($request->newSchedule))]);

            return response()->json([
                'message' => 'Perubahan Schedule Berhasil Diproses.'
            ], 200);
        }
    }

    public function proses_download_schedule(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'periode'   => 'required',
                'projectid' => 'required',
            ],
            [
                'periode.required'   => 'Periode Schedule masih kosong.',
                'projectid.required' => 'Lokasi Penempatan masih kosong.',
            ]
        );

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->messages()->first(),
            ], 405);
        } else {

            $selectedId = $request->projectid;
            # Masukan id dari form ke allids
            $allIds = [$selectedId];
            # Ambil turunan dari selectedId
            $allIds = array_merge($allIds, $this->getDescendants($selectedId));

            $userAccess = auth()->user()->getProjectAccess();
            # Hasil Filter dengan AKses user
            $accessId = array_intersect($allIds, $userAccess);

            $namaPeriode = Carbon::parse($request->periode)->translatedFormat('F Y');

            # Ambil periode dari input "Y-m"
            list($year, $month) = explode('-', $request->periode);
            # Buat tanggal aman dengan hari = 1
            $bulan = Carbon::create($year, $month, 1);
            $tglDari   = $bulan->copy()->startOfMonth();
            $tglSampai = $bulan->copy()->endOfMonth();

            $periode   = CarbonPeriod::create($tglDari, $tglSampai);
            $query = Schedule::whereBetween('tahun', [$tglDari->year, $tglSampai->year])
                ->whereBetween('bulan', [$tglDari->month, $tglSampai->month])
                ->whereHas('karyawan', function ($q) use ($accessId) {
                    $q->whereNull('deleted_at')
                        ->whereIn('project', $accessId);
                })
                ->with(['karyawan' => function ($q) {
                    $q->select('id', 'nip', 'nama', 'project', 'project_detail')
                        ->orderBy('project', 'ASC')
                        ->orderBy('nama', 'ASC');
                }])
                ->get();

            # Filter kolom tgl1 sampai tgl31 secara dinamis sesuai range tanggal form
            $result = $query->map(function ($schedule) use ($periode) {
                $row = [
                    'nip'    => $schedule->karyawan->nip ?? null,
                    'nama'   => $schedule->karyawan->nama ?? null,
                    'lokasi' => $schedule->karyawan->lokasi_penempatan ?? null,
                ];

                foreach ($periode as $tanggal) {
                    $tahun = $tanggal->year;
                    $bulan = $tanggal->month;
                    $hari  = $tanggal->day;

                    if ($schedule->tahun == $tahun && $schedule->bulan == $bulan) {
                        $namaKolom = 'tgl' . $hari;
                        $nilai = $schedule->$namaKolom ?? null;
                        $row[$tanggal->format('j')] = $nilai !== null ? $nilai : '-';
                    } else {
                        $row[$tanggal->format('j')] = '-';
                    }
                }

                return $row;
            });

            $dtHeader = collect($periode)->map(function (Carbon $tgl) {
                return $tgl->format('j');
            })->toArray();

            $pdf = Pdf::loadView('absensi.pdf_schedule', [
                'header'  => $result->isNotEmpty() ? $dtHeader : [],
                'periode' => $namaPeriode,
                'data'    => $result
            ]);

            $filename = 'DATA SCHEDULE ' . strtoupper($namaPeriode) . '.pdf';

            return response($pdf->output(), 200)
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
        }
    }

    public function download_template_upload(Request $request)
    {
        $jenis      = $request->jenis;
        $selectedId = $request->projectid;

        if (empty($jenis)) {
            return response()->json(['message' => 'Jenis Template tidak boleh kosong!'], 422);
        }

        $query = [];
        if ($selectedId) {
            # Masukan id dari form ke allids
            $allIds = [$selectedId];
            # Ambil turunan dari selectedId
            $allIds = array_merge($allIds, $this->getDescendants($selectedId));

            $userAccess = auth()->user()->getProjectAccess();
            # Hasil Filter dengan AKses user
            $accessId = array_intersect($allIds, $userAccess);

            $query = Karyawan::select('id', 'nip', 'nama', 'project_detail')
                ->whereIn('project', $accessId)
                ->whereNull('deleted_at')
                ->whereNull('tanggal_keluar')
                ->orderBy('project', 'ASC')
                ->orderBy('nama', 'ASC')
                ->get()
                ->toArray();
        }

        if ($jenis == 'schedule') {
            return Excel::download(new TemplateUpload($query, 'absensi.template_upload_schedule'), 'template_upload_schedule.xls');
        } else if ($jenis == 'absensi') {
            return Excel::download(new TemplateUpload($query, 'absensi.template_upload_absensi'), 'template_upload_absensi.xls');
        }
    }

    public function rekap_waktu(Request $request)
    {
        if ($request->ajax()) {
            $selectedId  = $request->projectid;
            # Masukan id dari form ke allids
            $allIds = [$selectedId];
            # Ambil turunan dari selectedId
            $allIds = array_merge($allIds, $this->getDescendants($selectedId));

            $userAccess = auth()->user()->getProjectAccess();
            # Hasil Filter dengan AKses user
            $accessId = array_intersect($allIds, $userAccess);

            $start = Carbon::parse($request->dari)->startOfDay();
            $end = Carbon::parse($request->sampai)->endOfDay();

            $query = Karyawan::select('id', 'nip', 'nama', 'project', 'project_detail')
                ->whereNull('deleted_at')
                ->whereIn('project', $accessId)
                ->with([
                    'absensi' => function ($q) use ($start, $end) {
                        $q->whereBetween('check_time', [$start, $end])
                            ->orderBy('check_time', 'asc');
                    }
                ])
                ->orderBy('nama', 'ASC')
                ->get();

            $datas = [];
            $dtProject = explode(',', $request->dtproject);
            $namesLokasi = Project::whereIn('id', $dtProject)->pluck('name')->toArray();
            $period = CarbonPeriod::create($start, $end);

            foreach ($query as $karyawan) {
                $dataByDate = [];

                foreach ($period as $date) {
                    $tanggal = $date->format('Y-m-d');

                    // ambil absensi karyawan ini di tanggal tersebut
                    $items = collect($karyawan->absensi)->filter(function ($row) use ($date) {
                        $checkTime = Carbon::parse($row->check_time);
                        return $checkTime->between(
                            $date->copy()->startOfDay(),
                            $date->copy()->endOfDay()
                        );
                    });

                    $masuk = $items->where('check_type', 'I')->sortBy('check_time')->first();
                    $pulang = $items->where('check_type', 'O')->sortByDesc('check_time')->first();

                    // cek O yang lebih kecil dari I
                    $invalidPulang = $items->where('check_type', 'O')
                        ->filter(function ($row) use ($masuk) {
                            return $masuk && Carbon::parse($row->check_time)->lt(Carbon::parse($masuk->check_time));
                        })
                        ->sortByDesc('check_time')
                        ->first();

                    if ($invalidPulang) {
                        $prevDate = Carbon::parse($tanggal)->subDay();
                        while ($prevDate->gte($start)) {
                            $prevTanggal = $prevDate->format('Y-m-d');
                            if (isset($dataByDate[$prevTanggal])) {
                                $prevRow = $dataByDate[$prevTanggal];
                                if ($prevRow['masuk'] !== '-' && $prevRow['pulang'] === '-') {
                                    $dataByDate[$prevTanggal]['pulang'] = date('d-m-Y', strtotime($invalidPulang->check_time)) . '<br>' . date('H:i', strtotime($invalidPulang->check_time));
                                    break;
                                }
                            }
                            $prevDate->subDay();
                        }
                        if ($pulang && $pulang->check_time == $invalidPulang->check_time) {
                            $pulang = null;
                        }
                    }

                    $dataByDate[$tanggal] = [
                        'hari'               => Carbon::parse($tanggal)->translatedFormat('l'),
                        // 'tanggal'            => Carbon::parse($tanggal)->translatedFormat('d F Y'),
                        'tanggal'            => Carbon::parse($tanggal)->translatedFormat('j'),
                        'masuk'              => ($masuk ? date('H:i', strtotime($masuk->check_time)) : '-'),
                        'pulang'             => ($pulang ? date('H:i', strtotime($pulang->check_time)) : '-'),
                    ];
                }

                $datas[] = [
                    'id'        => $karyawan->id,
                    'nip'       => $karyawan->nip,
                    'nama'      => $karyawan->nama,
                    'lokasi'    => $namesLokasi,
                    'kehadiran' => array_values($dataByDate),
                    // 'lokasi'  => $karyawan->lokasi_penempatan->toArray(),
                ];
            }
            return response()->json([
                'periode' => collect($period)->map(function (Carbon $tgl) {
                    return $tgl->format('j');
                })->toArray(),
                'data'    => $datas
            ]);
        }
        return view('absensi.rekap-waktu', [
            'judul' => 'Rekap Waktu'
        ]);
    }


    public function proses_upload_absensi(Request $request)
    {
        dd($request->all());
    }
}

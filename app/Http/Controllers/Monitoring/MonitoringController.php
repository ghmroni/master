<?php

namespace App\Http\Controllers\Monitoring;

use App\Http\Controllers\Controller;
use App\Models\Karyawan;
use App\Models\Monitoring;
use App\Models\Project;
use App\Models\RekapKehadiran;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Str;

class MonitoringController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware(PermissionMiddleware::using('monitoring-realtime'), only: ['index']),
            new Middleware(PermissionMiddleware::using('monitoring-create'), only: ['store_monitoring']),
            new Middleware(PermissionMiddleware::using('monitoring-perorang'), only: ['perorang', 'rekap_perorang']),
            new Middleware(PermissionMiddleware::using('monitoring-rekap'), only: ['proses_rekap_monitoring']),
        ];
    }

    public function index(Request $request)
    {
        if ($request->ajax()) {

            $akses = auth()->user()->getProjectAccess();

            $query = Monitoring::with('karyawan:id,nip,nama,project,project_detail')
                ->whereHas('karyawan', function ($q) use ($akses) {
                    $q->whereIn('project', $akses);
                })
                ->whereDate('tanggal', Carbon::today())
                ->get()
                ->toArray();

            return DataTables::of($query)
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

                ->addColumn('photo', function ($row) {

                    $url = '/assets/img/avatars/no-image.png';
                    if ($row['foto'] != null) {
                        $url = '/storage/hris/monitoring/' . $row['foto'];
                    }

                    return '
                        <a href="' . $url . '" class="glightbox" data-glightbox="type: image; zoomable: true;" data-title="' . $row['karyawan']['nip'] . ' | ' . $row['karyawan']['nama'] . ' | ' . $row['prioritas'] . '" >
                            <img src="' . $url . '" class="img-thumbnail" alt="image" width="70">
                        </a>
                    ';
                })

                ->rawColumns([
                    'penempatan' => 'penempatan',
                    'photo'      => 'photo',
                ])
                ->addIndexColumn()
                ->toJson();
        }

        return view('monitoring.realtime', [
            'judul' => 'Realtime Monitoring'
        ]);
    }

    public function stream()
    {
        return response()->stream(function () {
            while (true) {
                $akses = auth()->user()->getProjectAccess();
                $query = Monitoring::with('karyawan:id,nip,nama,project,project_detail')
                    ->whereHas('karyawan', function ($q) use ($akses) {
                        $q->whereIn('project', $akses);
                    })
                    ->whereDate('tanggal', Carbon::today())
                    ->get()
                    ->toArray();

                $data = DataTables::of($query)
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

                    ->addColumn('photo', function ($row) {

                        $url = '/assets/img/avatars/no-image.png';
                        if ($row['foto'] != null) {
                            $url = '/storage/hris/monitoring/' . $row['foto'];
                        }

                        return '
                        <a href="' . $url . '" class="glightbox" data-glightbox="type: image; zoomable: true;" data-title="' . $row['karyawan']['nip'] . ' | ' . $row['karyawan']['nama'] . ' | ' . $row['prioritas'] . '" >
                            <img src="' . $url . '" class="img-thumbnail" alt="image" width="70">
                        </a>
                    ';
                    })
                    ->rawColumns([
                        'penempatan' => 'penempatan',
                        'photo'      => 'photo',
                    ])
                    ->addIndexColumn()
                    ->toJson();

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

    public function karyawans()
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

        return $karyawans;
    }

    public function add_monitoring(Request $request)
    {
        $karyawans = $this->karyawans();
        return view('monitoring.tambah-monitoring', [
            'judul' => 'Tambah Monitoring'
        ], compact('karyawans'));
    }

    public function store_monitoring(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'karyawanid'        => 'required',
                'penempatan'        => 'required',
                'tanggalmonitoring' => 'required|date',
                'prioritas'         => 'required',
                'keterangan'        => 'required',
                'gambar'            => 'required|image|mimes:jpeg,jpg,png',
            ],
            [
                'keterangan.required' => 'Uraian Kegiatan masih kosong.',
                'gambar.required'     => 'Foto Kegiatan masih kosong.'
            ]
        );

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->messages()->first()
            ], 401);
        } else {
            $idkar = decrypt($request->karyawanid);
            $extension = $request->file('gambar')->getClientOriginalExtension();
            $filename = Str::uuid()->toString() . '_' . time() . '.' . $extension;

            Storage::disk('public')->putFileAs('hris/monitoring/', $request->file('gambar'), $filename);

            Monitoring::create([
                'karyawan_id' => $idkar,
                'tanggal'     => Carbon::parse($request->input('tanggalmonitoring'))
                    ->setTimeFrom(Carbon::now()),
                'prioritas'  => $request->prioritas,
                'penempatan' => json_decode($request->penempatan),
                'keterangan' => trim($request->keterangan),
                'foto'       => $filename,
            ]);

            ## MASUKAN KE REKAP KEHADIRAN
            $tgl        = date('j', strtotime($request->tanggalmonitoring)); // 'j' menghasilkan 1-31 tanpa nol di depan
            $bulan      = date('n', strtotime($request->tanggalmonitoring)); // 'n' menghasilkan 1-12 tanpa nol di depan
            $tahun      = date('Y', strtotime($request->tanggalmonitoring));
            $namaKolom  = 'tgl' . $tgl; // Menghasilkan: tgl1, tgl2, dst.

            $rekap = RekapKehadiran::firstOrCreate([
                'karyawan_id' => $idkar,
                'bulan'       => $bulan,
                'tahun'       => $tahun,
            ]);

            // 3. Ambil nilai saat ini yang tersimpan di kolom tanggal tersebut
            $nilaiSekarang = $rekap->$namaKolom;

            // 4. Cek kondisinya: Jika nilainya masih kosong (null), bernilai '-', atau sudah 'H', maka lakukan update
            if (is_null($nilaiSekarang) || $nilaiSekarang == '-' || $nilaiSekarang == 'H') {
                $rekap->update([
                    $namaKolom => 'H'
                ]);
            }
            ## END REKAP KEHADIRAN
            return response()->json([
                'message' => 'Monitoring Karyawan Berhasil Disimpan.'
            ], 200);
        }
    }

    public function perorang(Request $request)
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

        return view('monitoring.perorang', [
            'judul' => 'Monitoring Perorangan'
        ], compact('karyawans'));
    }

    public function get_perorang(Request $request)
    {
        $idkar = decrypt($request->karyawanid);
        $start = Carbon::parse($request->dari)->startOfDay();
        $end = Carbon::parse($request->sampai)->endOfDay();
        $query = Monitoring::with('karyawan:id,nip,nama')
            ->where('karyawan_id', $idkar)
            ->whereBetween('tanggal', [$start, $end])
            ->orderBy('tanggal', 'asc')
            ->get();

        $dataByDate = [];
        $period = CarbonPeriod::create($start, $end);

        foreach ($period as $date) {
            $tanggal = $date->format('Y-m-d');
            // ambil monitoring untuk tanggal ini
            $monitoringRow = $query->first(function ($row) use ($tanggal) {
                return Carbon::parse($row->tanggal)->isSameDay($tanggal);
            });

            $dataByDate[$tanggal] = [
                'tanggal'            => Carbon::parse($tanggal)->translatedFormat('d F Y'),
                'prioritas'          => $monitoringRow->prioritas ?? null,
                'tanggal_monitoring' => $monitoringRow ? Carbon::parse($monitoringRow->tanggal)->translatedFormat('l d F Y H:i') : null,
                'keterangan'         => $monitoringRow->keterangan ?? null,
                'photo'              => $monitoringRow ? '/storage/hris/monitoring/' . $monitoringRow->foto : null,
            ];
        }

        return DataTables::of(array_values($dataByDate))
            ->addIndexColumn()
            ->toJson();
    }

    public function store_perorang(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'karyawanid' => 'required',
                'dari'       => 'required|date',
                'sampai'     => 'required|date',
            ],
            [
                'karyawanid.required' => 'Karyawan masih kosong.',
                'dari.required'       => 'Tanggal dari masih kosong.',
                'sampai.required'     => 'Tanggal sampai masih kosong.'
            ]
        );

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->messages()->first(),
            ], 405);
        } else {
            $idkar = decrypt($request->karyawanid);
            $start = Carbon::parse($request->dari)->startOfDay();
            $end = Carbon::parse($request->sampai)->endOfDay();

            $karyawan = Karyawan::select('id', 'nip', 'nama', 'project_detail')
                ->where('id', $idkar)
                ->first()
                ->toArray();

            $karyawan['dari']   = Carbon::parse($request->dari)->translatedFormat('d F Y');
            $karyawan['sampai'] = Carbon::parse($request->sampai)->translatedFormat('d F Y');

            $query = Monitoring::with('karyawan:id,nip,nama')
                ->where('karyawan_id', $idkar)
                ->whereBetween('tanggal', [$start, $end])
                ->orderBy('tanggal', 'asc')
                ->get();

            $dataByDate = [];
            $period = CarbonPeriod::create($start, $end);

            foreach ($period as $date) {
                $tanggal = $date->format('Y-m-d');
                // ambil monitoring untuk tanggal ini
                $monitoringRow = $query->first(function ($row) use ($tanggal) {
                    return Carbon::parse($row->tanggal)->isSameDay($tanggal);
                });

                $dataByDate[$tanggal] = [
                    'tanggal'            => Carbon::parse($tanggal)->translatedFormat('d F Y'),
                    'prioritas'          => $monitoringRow->prioritas ?? null,
                    'tanggal_monitoring' => $monitoringRow ? Carbon::parse($monitoringRow->tanggal)->translatedFormat('l d F Y H:i') : null,
                    'keterangan'         => $monitoringRow->keterangan ?? null,
                    'photo'              => $monitoringRow ? '/storage/hris/monitoring/' . $monitoringRow->foto : null,
                ];
            }

            $pdf = Pdf::loadView('monitoring.pdf_perorang', [
                'karyawan' => $karyawan,
                'data' => $dataByDate
            ]);

            $filename = 'REKAP - ' . $karyawan['nama'] . '.pdf';

            return response($pdf->output(), 200)
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
        }
    }

    public function rekap_monitoring(Request $request)
    {
        return view('monitoring.rekap-monitoring', [
            'judul' => 'Rekap Monitoring'
        ]);
    }

    public function proses_rekap_monitoring(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'projectid' => 'required',
                'dari'      => 'required|date',
                'sampai'    => 'required|date',
            ],
            [
                'projectid.required' => 'Lokasi Penempatan masih kosong.',
                'dari.required' => 'Tanggal masih kosong.',
                'sampai.required' => 'Tanggal masih kosong.',
            ]
        );

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->messages()->first(),
            ], 405);
        } else {
            $jobId  = Str::uuid()->toString();

            $user = Auth::user();
            $roles = $user->roles->pluck('id')->toArray();

            $selectedId = $request->projectid;
            $start = Carbon::parse($request->dari)->startOfDay();
            $end = Carbon::parse($request->sampai)->endOfDay();

            # Masukan id dari form ke allids
            $allIds = [$selectedId];
            # Ambil turunan dari selectedId
            $allIds = array_merge($allIds, $this->getDescendants($selectedId));

            $userAccess = auth()->user()->getProjectAccess();
            # Hasil Filter dengan AKses user
            $accessId = array_intersect($allIds, $userAccess);

            $query = Karyawan::select('id', 'nip', 'nama', 'project', 'project_detail')
                ->whereNull('deleted_at')
                ->whereIn('project', $accessId)
                ->with([
                    'monitoring' => function ($q) use ($start, $end) {
                        $q->whereBetween('tanggal', [$start, $end]);
                    },
                ])
                ->orderBy('nama', 'ASC')
                ->get();

            $datas = [];
            foreach ($query as $karyawan) {
                $dataByDate = [];
                $period = CarbonPeriod::create($start, $end);

                foreach ($period as $date) {
                    $tanggal = $date->format('Y-m-d');

                    // ambil monitoring untuk karyawan ini di tanggal tsb
                    $monitoringRow = $karyawan->monitoring->first(function ($row) use ($tanggal) {
                        return Carbon::parse($row->tanggal)->isSameDay($tanggal);
                    });

                    // hanya simpan kalau ada monitoring
                    if ($monitoringRow) {
                        $dataByDate[$tanggal] = [
                            'nip'        => $karyawan->nip,
                            'nama'       => $karyawan->nama,
                            'prioritas'  => $monitoringRow->prioritas,
                            'tanggal'    => Carbon::parse($monitoringRow->tanggal)->translatedFormat('d-m-Y H:i'),
                            'penempatan' => $monitoringRow->penempatan,
                            'keterangan' => $monitoringRow->keterangan,
                            'photo'      => '/storage/hris/monitoring/' . $monitoringRow->foto,
                        ];
                    }
                }

                if (!empty($dataByDate)) {
                    $datas[] = $dataByDate;
                }
            }

            $flatten = [];
            foreach ($datas as $group) {
                foreach ($group as $tanggal => $row) {
                    $flatten[] = $row;
                }
            }

            usort($flatten, function ($a, $b) {
                return strtotime($a['tanggal']) <=> strtotime($b['tanggal']);
            });

            $pdf = Pdf::loadView('monitoring.pdf_rekap-monitoring', [
                'periode' => Carbon::parse($request->dari)->translatedFormat('d F Y') . ' s/d ' . Carbon::parse($request->sampai)->translatedFormat('d F Y'),
                'data'    => $flatten
            ]);

            $filename = 'REKAP MONITORING.pdf';

            return response($pdf->output(), 200)
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
        }
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
}

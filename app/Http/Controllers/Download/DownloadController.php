<?php

namespace App\Http\Controllers\Download;

use App\Http\Controllers\Controller;
use App\Jobs\DownloadJob;
use App\Models\Download;
use App\Models\Karyawan;
use App\Models\Project;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Illuminate\Support\Str;

class DownloadController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware(PermissionMiddleware::using('absensi-perlokasi'), only: ['rekap_perlokasi']),
            new Middleware(PermissionMiddleware::using('absensi-rekap-waktu'), only: ['rekap_waktu']),
            new Middleware(PermissionMiddleware::using('absensi-rekap-absen'), only: ['rekap_kehadiran']),
        ];
    }

    public function status($jobId)
    {
        $download = Download::where('job_id', $jobId)->firstOrFail();
        if (!$download) {
            return response()->json(['status' => 'not_found']);
        }
        return response()->json([
            'status'   => $download->status,
            'progress' => $download->progress,
            'url'      => $download->status === 'done'
                ? route('download.downloadFile', ['jobId' => $jobId]) . '?t=' . $download->filename
                :  null,
        ]);
    }

    public function download_file($jobId)
    {
        $download = Download::where('job_id', $jobId)->firstOrFail();
        if ($download->status !== 'done') {
            abort(404, 'File belum siap');
        }

        $path = Storage::disk('local')->path("exports/{$download->filename}");

        // return response()->download(
        //     $path,
        //     "download.pdf",
        //     [
        //         'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
        //         'Pragma' => 'no-cache',
        //         'Content-Type'  => 'application/pdf',
        //     ]
        // )->deleteFileAfterSend(true);

        // Deteksi ekstensi file
        $extension = pathinfo($download->filename, PATHINFO_EXTENSION);

        // Tentukan Content-Type berdasarkan ekstensi
        $mimeTypes = [
            'pdf'  => 'application/pdf',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'xls'  => 'application/vnd.ms-excel',
            'csv'  => 'text/csv',
        ];

        $contentType = $mimeTypes[$extension] ?? 'application/octet-stream';

        return response()->download(
            $path,
            $download->filename, // nama file sesuai yang disimpan
            [
                'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
                'Pragma'        => 'no-cache',
                'Content-Type'  => $contentType,
            ]
        )->deleteFileAfterSend(true);
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

    public function rekap_perlokasi(Request $request)
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
                    'absensi' => function ($q) use ($start, $end) {
                        $q->whereBetween('check_time', [$start, $end])
                            ->orderBy('check_time', 'asc');
                    }
                ])
                ->orderBy('nama', 'ASC')
                ->get();

            $dtProject = explode(',', $request->dtproject);
            $namesLokasi = Project::whereIn('id', $dtProject)->pluck('name')->toArray();

            $datas = [];
            foreach ($query as $karyawan) {
                $dataByDate = [];
                $period = CarbonPeriod::create($start, $end);

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
                                    $dataByDate[$prevTanggal]['pulang'] = date('d-m-Y H:i', strtotime($invalidPulang->check_time));
                                    break;
                                }
                            }
                            $prevDate->subDay();
                        }
                        if ($pulang && $pulang->check_time == $invalidPulang->check_time) {
                            $pulang = null;
                        }
                    }

                    $monitoringRow = collect($karyawan->monitoring)->firstWhere(function ($row) use ($tanggal) {
                        return Carbon::parse($row->tanggal)->format('Y-m-d') === $tanggal;
                    });

                    $dataByDate[$tanggal] = [
                        'hari'               => Carbon::parse($tanggal)->translatedFormat('l'),
                        'tanggal'            => Carbon::parse($tanggal)->translatedFormat('d F Y'),
                        'masuk'              => ($masuk ? date('d-m-Y H:i', strtotime($masuk->check_time)) : '-'),
                        'pulang'             => ($pulang ? date('d-m-Y H:i', strtotime($pulang->check_time)) : '-'),
                        'prioritas'          => $monitoringRow->prioritas ?? null,
                        'tanggal_monitoring' => $monitoringRow ? Carbon::parse($monitoringRow->tanggal)->translatedFormat('d F Y H:i') : null,
                        'keterangan'         => $monitoringRow->keterangan ?? null,
                        'foto_monitoring'    => $monitoringRow ? '/storage/hris/monitoring/' . $monitoringRow->foto : null,
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

            Download::create([
                'job_id'   => $jobId,
                'user_id'  => $user->id,
                'progress' => 0,
                'status'   => 'queued',
            ]);

            DownloadJob::dispatch($jobId, $user->id, $datas, 'absensi.pdf_perlokasi', 'Rekap-Kehadiran-Perlokasi');

            return response()->json([
                'status' => 'queued',
                'job_id' => $jobId,
            ]);
        }
    }

    public function rekap_waktu(Request $request)
    {
        $jobId  = Str::uuid()->toString();

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
                                $dataByDate[$prevTanggal]['pulang'] = date('d-m-Y H:i', strtotime($invalidPulang->check_time));
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
                'kehadiran' => array_values($dataByDate),
                'lokasi'    => $karyawan->lokasi_penempatan->toArray(),
            ];
        }

        $result = [
            'tanggal' => collect($period)->map(function (Carbon $tgl) {
                return $tgl->format('j');
            })->toArray(),
            'data'    => $datas,
            'periode' => Carbon::parse($request->dari)->translatedFormat('d F Y') . ' s/d ' . Carbon::parse($request->sampai)->translatedFormat('d F Y'),
        ];

        Download::create([
            'job_id'  => $jobId,
            'user_id' => auth()->user()->id,
            'status'  => 'queued',
        ]);

        DownloadJob::dispatch($jobId, auth()->user()->id, $result, 'absensi.pdf_rekap_waktu');

        return response()->json([
            'status' => 'queued',
            'job_id' => $jobId,
        ]);
    }

    public function rekap_kehadiran(Request $request)
    {
        $jobId  = Str::uuid()->toString();

        $useSchedule = $request->has('schedule');

        $selectedId = $request->projectid;
        # Masukan id dari form ke allids
        $allIds = [$selectedId];
        # Ambil turunan dari selectedId
        $allIds = array_merge($allIds, $this->getDescendants($selectedId));

        $userAccess = auth()->user()->getProjectAccess();
        # Hasil Filter dengan AKses user
        $accessId = array_intersect($allIds, $userAccess);

        $tglDari = Carbon::parse($request->dari);
        $tglSampai = Carbon::parse($request->sampai);

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

        $result = $query
            ->filter(function ($karyawan) use ($useSchedule) {
                // Jika pakai schedule, hanya ambil karyawan yang punya schedule
                if ($useSchedule) {
                    return $karyawan->schedule->isNotEmpty();
                }
                return true; // kalau tidak pakai schedule, semua tetap diambil
            })
            ->map(function ($karyawan) use ($tglDari, $tglSampai, $useSchedule) {
                $kehadiranFiltered = [];
                $hariFiltered      = [];


                $currentDate = $tglDari->copy();
                while ($currentDate->lte($tglSampai)) {
                    $tahun = $currentDate->year;
                    $bulan = $currentDate->month;
                    $hari  = $currentDate->day;

                    $rekapBulanIni = $karyawan->rekapKehadiran
                        ->where('tahun', $tahun)
                        ->where('bulan', $bulan)
                        ->first();

                    $namaKolom = 'tgl' . $hari;

                    if ($useSchedule && $karyawan->schedule->isNotEmpty()) {
                        // Cari schedule sesuai bulan & tahun
                        $schedule = $karyawan->schedule
                            ->where('tahun', $tahun)
                            ->where('bulan', $bulan)
                            ->first();

                        $valSchedule = $schedule ? $schedule->$namaKolom : null;

                        if ($valSchedule !== null) {
                            if (is_numeric($valSchedule) || preg_match('/^\d{1,2}:\d{2}$/', $valSchedule)) {
                                // numeric/jam → ambil dari rekap kalau ada
                                // $statusAbsen = $rekapBulanIni ? $rekapBulanIni->$namaKolom : $valSchedule;
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

                    $kehadiranFiltered[$currentDate->format('Y-m-d')] = $statusAbsen;
                    $hariFiltered[$currentDate->format('Y-m-d')]      = $currentDate->translatedFormat('l');
                    $currentDate->addDay();
                }

                return [
                    'nip'            => $karyawan->nip,
                    'nama'           => $karyawan->nama,
                    'lokasi'         => $karyawan->lokasi_penempatan->toArray(),
                    'dari'           => $tglDari->translatedFormat('d F Y'),
                    'sampai'         => $tglSampai->translatedFormat('d F Y'),
                    'hari'           => array_values($hariFiltered),
                    'data_tanggal'   => array_keys($kehadiranFiltered),
                    'data_kehadiran' => array_values($kehadiranFiltered)
                ];
            });

        Download::create([
            'job_id'  => $jobId,
            'user_id' => auth()->user()->id,
            'status'  => 'queued',
        ]);

        DownloadJob::dispatch($jobId, auth()->user()->id, $result, 'absensi.pdf_kehadiran', $useSchedule);

        return response()->json([
            'status' => 'queued',
            'job_id' => $jobId,
        ]);
    }
}
